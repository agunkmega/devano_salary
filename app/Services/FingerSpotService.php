<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Setting;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FingerSpotService
{
    protected string $baseUrl;

    protected string $cloudBaseUrl;

    protected string $token;

    protected ?string $deviceId;

    protected ?string $cloudId;

    public function __construct()
    {
        $settings = Setting::whereIn('key', [
            'fingerspot_api_url',
            'fingerspot_api_token',
            'fingerspot_device_id',
            'fingerspot_cloud_base_url',
            'fingerspot_cloud_id',
        ])
            ->get()
            ->keyBy('key');

        $this->baseUrl = rtrim($settings->get('fingerspot_api_url')?->value ?? 'https://api.fingerspot.io/api/v1', '/');
        $this->cloudBaseUrl = rtrim($settings->get('fingerspot_cloud_base_url')?->value ?? 'https://developer.fingerspot.io', '/');
        $this->token = $settings->get('fingerspot_api_token')?->value ?? '';
        $this->deviceId = $settings->get('fingerspot_device_id')?->value ?? null;
        $this->cloudId = $settings->get('fingerspot_cloud_id')?->value ?? cache()->get('fingerspot_cloud_id');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->token) && ! empty($this->baseUrl);
    }

    public function getAttLogUrl(): string
    {
        return $this->cloudBaseUrl.'/api/get_attlog';
    }

    /**
     * Ambil data absensi tersimpan di server Fingerspot untuk perangkat tertentu.
     * Rentang maksimal 2 hari per request (start_date hingga end_date termasuk).
     *
     * @return array<int, array{pin: string, scan: string, verify?: mixed, status_scan?: mixed}>
     */
    public function fetchAttendanceLogs(string $startDate, string $endDate, ?string $pin = null): array
    {
        if (! $this->cloudId) {
            throw new \Exception('Cloud ID belum diatur. Isi pengaturan Finger Spot terlebih dahulu.');
        }

        $payload = [
            'trans_id' => (string) random_int(100000, 999999),
            'cloud_id' => $this->cloudId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];

        if ($pin) {
            $payload['pin'] = $pin;
        }

        $response = Http::withToken($this->token)
            ->withHeaders(['Accept' => 'application/json'])
            ->asJson()
            ->post($this->getAttLogUrl(), $payload);

        if ($response->failed()) {
            throw new \Exception('Finger Spot get_attlog error: '.$response->body());
        }

        return $this->normalizeAttendanceLogs($response->json() ?? []);
    }

    /**
     * Normalisasi berbagai bentuk respons get_attlog menjadi list record absensi.
     */
    protected function normalizeAttendanceLogs(array $response): array
    {
        $data = $response['data'] ?? $response;

        if (isset($data['attlogs']) && is_array($data['attlogs'])) {
            return $data['attlogs'];
        }

        if (isset($data['records']) && is_array($data['records']) && array_is_list($data['records'])) {
            return $data['records'];
        }

        if (is_array($data)) {
            if (isset($data['pin']) || isset($data['scan'])) {
                return [$data];
            }
            if (array_is_list($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Ekstrak list record pin/scan dari payload webhook.
     */
    public function extractRecords(array $payload): array
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

    /**
     * Proses satu record pemindaian menjadi data kehadiran (dipakai webhook & sinkron).
     */
    public function processScan(array $data): array
    {
        $pin = $data['pin'] ?? null;
        $scan = $data['scan'] ?? null;

        if (! $pin || ! $scan) {
            Log::warning('Fingerspot missing pin or scan in record', $data);

            return ['status' => 'ignored', 'reason' => 'missing_data', 'data' => $data];
        }

        $employee = Employee::with('shift')->where('nik', $pin)->first();

        if (! $employee) {
            Log::warning("Fingerspot: no employee found with NIK $pin");

            return ['status' => 'ignored', 'reason' => 'employee_not_found', 'pin' => $pin];
        }

        $shiftId = $employee->shift?->id;

        if (! $shiftId) {
            $defaultShift = Shift::where('name', 'like', '%pagi%')
                ->orWhere('code', 'like', '%pagi%')
                ->orderBy('id')
                ->first() ?? Shift::orderBy('id')->first();
            $shiftId = $defaultShift?->id;
        }

        try {
            $scanTime = Carbon::parse($scan);
        } catch (\Exception $e) {
            Log::error("Fingerspot: invalid scan date $scan");

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
                is_null($existing->clock_in) => 'clock_in',
                is_null($existing->break_out) => 'break_out',
                is_null($existing->break_in) => 'break_in',
                is_null($existing->clock_out) => 'clock_out',
                is_null($existing->overtime_in) => 'overtime_in',
                default => 'overtime_out',
            };

            $existing->$field = $scanTime;
            $existing->save();

            Log::info("Updated $field for {$employee->nik} on $dateStr");

            return ['status' => 'updated', 'field' => $field, 'attendance_id' => $existing->id];
        }

        $attendance = new Attendance;
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

    /**
     * Ambil log absensi lama (endpoint /attendance/logs) — dipertahankan untuk kompatibilitas.
     */
    public function fetchEmployees(): array
    {
        $response = Http::withToken($this->token)
            ->withHeaders(['Accept' => 'application/json'])
            ->get($this->baseUrl.'/employees', [
                'device_id' => $this->deviceId,
                'limit' => 1000,
            ]);

        if ($response->failed()) {
            throw new \Exception('Finger Spot API error: '.$response->body());
        }

        return $response->json()['data'] ?? $response->json() ?? [];
    }
}
