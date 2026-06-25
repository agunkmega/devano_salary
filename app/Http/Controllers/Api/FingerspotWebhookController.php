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

        $data = $payload['data'] ?? [];
        $pin = $data['pin'] ?? null;
        $scan = $data['scan'] ?? null;
        $cloudId = $payload['cloud_id'] ?? null;

        if ($cloudId) {
            cache()->put('fingerspot_cloud_id', $cloudId, now()->addDays(30));
        }

        if (!$pin || !$scan) {
            Log::warning('Fingerspot webhook missing pin or scan', $payload);
            return response()->json(['status' => 'ignored', 'reason' => 'missing_data']);
        }

        $employee = Employee::with('shift')->where('nik', $pin)->first();

        if (!$employee) {
            Log::warning("Fingerspot webhook: no employee found with NIK $pin");
            return response()->json(['status' => 'ignored', 'reason' => 'employee_not_found']);
        }

        $shiftId = $employee->shift?->id;

        try {
            $scanTime = Carbon::parse($scan);
        } catch (\Exception $e) {
            Log::error("Fingerspot webhook: invalid scan date $scan");
            return response()->json(['status' => 'error', 'reason' => 'invalid_date']);
        }

        $dateStr = $scanTime->format('Y-m-d');

        $attendance = Attendance::firstOrNew(
            ['employee_id' => $employee->id, 'attendance_date' => $dateStr],
            ['shift_id' => $shiftId, 'status' => 'hadir', 'notes' => 'webhook']
        );

        $field = match (true) {
            is_null($attendance->clock_in)    => 'clock_in',
            is_null($attendance->break_out)   => 'break_out',
            is_null($attendance->break_in)    => 'break_in',
            is_null($attendance->clock_out)   => 'clock_out',
            is_null($attendance->overtime_in) => 'overtime_in',
            default                           => 'overtime_out',
        };

        $attendance->$field = $scanTime;
        $attendance->save();

        $action = $attendance->wasRecentlyCreated ? 'Created' : 'Updated';
        Log::info("$action $field for {$employee->nik} on $dateStr");

        return response()->json([
            'status' => strtolower($action),
            'attendance_id' => $attendance->id,
            'field' => $field,
        ]);
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
