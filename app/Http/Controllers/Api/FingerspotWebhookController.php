<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FingerspotWebhookController extends Controller
{
    public function handle(Request $request)
    {
        if ($request->isMethod('get')) {
            return response()->json(['status' => 'ok', 'message' => 'Webhook endpoint is active']);
        }

        Log::info('Fingerspot webhook received', $request->all());

        $payload = $request->all();

        if (($payload['type'] ?? null) === 'get_userinfo') {
            return $this->handleGetUserInfo($payload);
        }

        if (($payload['type'] ?? null) !== 'attlog') {
            return response()->json(['status' => 'ignored', 'reason' => 'unexpected_type']);
        }

        $cloudId = $payload['cloud_id'] ?? null;
        if ($cloudId) {
            cache()->put('fingerspot_cloud_id', $cloudId, now()->addDays(30));
        }

        $records = $this->extractRecords($payload);

        if (empty($records)) {
            Log::warning('Fingerspot webhook missing pin or scan', $payload);
            return response()->json(['status' => 'ignored', 'reason' => 'missing_data']);
        }

        $results = [];
        foreach ($records as $rec) {
            $results[] = $this->processScan($rec);
        }

        return response()->json(['status' => 'ok', 'results' => $results]);
    }

    protected function extractRecords(array $payload): array
    {
        $data = $payload['data'] ?? [];

        if (is_array($data)) {
            if (isset($data['pin']) || isset($data['scan'])) {
                return [$data];
            }
            if (array_is_list($data)) {
                return $data;
            }
        }

        if (isset($payload['pin']) || isset($payload['scan'])) {
            return [$payload];
        }

        return [];
    }

    protected function processScan(array $data): array
    {
        $pin = $data['pin'] ?? null;
        $scan = $data['scan'] ?? null;

        if (!$pin || !$scan) {
            Log::warning('Fingerspot webhook missing pin or scan in record', $data);
            return ['status' => 'ignored', 'reason' => 'missing_data', 'data' => $data];
        }

        $employee = Employee::with('shift')->where('nik', $pin)->first();

        if (!$employee) {
            Log::warning("Fingerspot webhook: no employee found with NIK $pin");
            return ['status' => 'ignored', 'reason' => 'employee_not_found', 'pin' => $pin];
        }

        $shiftId = $employee->shift?->id;

        if (!$shiftId) {
            $defaultShift = \App\Models\Shift::where('name', 'like', '%pagi%')
                ->orWhere('code', 'like', '%pagi%')
                ->orderBy('id')
                ->first() ?? \App\Models\Shift::orderBy('id')->first();
            $shiftId = $defaultShift?->id;
        }

        try {
            $scanTime = Carbon::parse($scan);
        } catch (\Exception $e) {
            Log::error("Fingerspot webhook: invalid scan date $scan");
            return ['status' => 'error', 'reason' => 'invalid_date', 'pin' => $pin];
        }

        $dateStr = $scanTime->format('Y-m-d');

        $existing = Attendance::where('employee_id', $employee->id)
            ->where('attendance_date', $dateStr)
            ->first();

        if ($existing) {
            foreach (['clock_in', 'break_out', 'break_in', 'clock_out', 'overtime_in', 'overtime_out'] as $f) {
                if ($existing->$f && abs($scanTime->diffInMinutes(Carbon::parse($existing->$f))) < 1) {
                    Log::info("Duplicate scan ignored for {$employee->nik} at $scan (within 1min of $f)");
                    return ['status' => 'ignored', 'reason' => 'duplicate_scan', 'field' => $f];
                }
            }

            $field = match (true) {
                is_null($existing->clock_in)    => 'clock_in',
                is_null($existing->clock_out)   => 'clock_out',
                is_null($existing->break_out)   => 'break_out',
                is_null($existing->break_in)    => 'break_in',
                is_null($existing->overtime_in) => 'overtime_in',
                default                           => 'overtime_out',
            };

            $existing->$field = $scanTime;
            $existing->save();

            Log::info("Updated $field for {$employee->nik} on $dateStr");
            return ['status' => 'updated', 'field' => $field, 'attendance_id' => $existing->id];
        }

        $attendance = new Attendance();
        $attendance->employee_id = $employee->id;
        $attendance->attendance_date = $dateStr;
        $attendance->shift_id = $shiftId;
        $attendance->status = 'hadir';
        $attendance->notes = 'webhook';
        $attendance->clock_in = $scanTime;
        $attendance->save();

        Log::info("Created clock_in for {$employee->nik} on $dateStr");
        return ['status' => 'created', 'field' => 'clock_in', 'attendance_id' => $attendance->id];
    }

    protected function handleGetUserInfo(array $payload): \Illuminate\Http\JsonResponse
    {
        $pin = $payload['pin'] ?? $payload['data']['pin'] ?? null;

        if (!$pin) {
            return response()->json(['status' => 'error', 'reason' => 'missing_pin']);
        }

        $employee = Employee::where('nik', $pin)->first();

        if (!$employee) {
            return response()->json(['status' => 'error', 'reason' => 'employee_not_found']);
        }

        return response()->json([
            'status' => 'ok',
            'data' => [
                'pin' => $employee->nik,
                'name' => $employee->full_name,
            ],
        ]);
    }
}
