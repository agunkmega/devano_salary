<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Setting;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function config(Request $request)
    {
        $enabled = Setting::where('group', 'mobile_api')->where('key', 'mobile_online_attendance_enabled')->value('value') ?? '1';
        $geofence = Setting::where('group', 'mobile_api')->where('key', 'mobile_attendance_geofence_enabled')->value('value') ?? '1';
        $selfie = Setting::where('group', 'mobile_api')->where('key', 'mobile_attendance_selfie_required')->value('value') ?? '1';
        $disabledMsg = Setting::where('group', 'mobile_api')->where('key', 'mobile_attendance_disabled_message')->value('value') ?? 'Presensi online saat ini dinonaktifkan oleh administrator. Silakan lakukan absensi melalui mesin fingerprint kantor.';

        return response()->json([
            'success' => true,
            'data' => [
                'is_online_enabled' => $enabled == '1' || $enabled === 'true',
                'is_geofence_enabled' => $geofence == '1' || $geofence === 'true',
                'is_selfie_required' => $selfie == '1' || $selfie === 'true',
                'disabled_message' => $disabledMsg,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $employee = null;
        if ($user) {
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee && $user->email) {
                $employee = Employee::where('email', $user->email)->first();
            }
        }

        $query = Attendance::with(['employee', 'shift']);

        if ($employee) {
            $query->where('employee_id', $employee->id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('attendance_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('attendance_date', '<=', $request->end_date);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->get();

        $data = $attendances->map(function ($a) {
            $dateStr = $a->attendance_date ? $a->attendance_date->format('Y-m-d') : null;
            $clockInStr = $a->clock_in ? $a->clock_in->format('H:i') : null;
            $clockOutStr = $a->clock_out ? $a->clock_out->format('H:i') : null;

            return [
                'id' => (string) $a->id,
                'date' => $dateStr,
                'clock_in' => $clockInStr,
                'clock_out' => $clockOutStr,
                'status' => $a->status ?? 'hadir',
                'note' => $a->notes ?? $a->admin_note,
                'notes' => $a->notes ?? $a->admin_note,
                'photo_url' => $a->photo_in,
                'selfie_path' => $a->photo_in,
                'location_name' => $a->location_in ?? 'Devano Office',
                'is_within_geofence' => true,
                'ignore_late' => (bool) ($a->ignore_late ?? false),
                'ignore_early_leave' => (bool) ($a->ignore_early_leave ?? false),
                'ignore_excess_break' => (bool) ($a->ignore_excess_break ?? false),
                'late_minutes' => (int) ($a->late_minutes ?? 0),
                'early_leave_minutes' => (int) ($a->early_leave_minutes ?? 0),
                'excess_break_minutes' => (int) ($a->excess_break_minutes ?? 0),
                'overtime_minutes' => (int) ($a->overtime_minutes ?? 0),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function clockIn(Request $request)
    {
        $enabled = Setting::where('group', 'mobile_api')->where('key', 'mobile_online_attendance_enabled')->value('value') ?? '1';
        if ($enabled == '0' || $enabled === 'false') {
            $disabledMsg = Setting::where('group', 'mobile_api')->where('key', 'mobile_attendance_disabled_message')->value('value') ?? 'Presensi online saat ini dinonaktifkan oleh administrator. Silakan lakukan absensi melalui mesin fingerprint kantor.';
            return response()->json([
                'success' => false,
                'message' => $disabledMsg,
            ], 403);
        }

        $user = $request->user();
        $employee = null;
        if ($user) {
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee && $user->email) {
                $employee = Employee::where('email', $user->email)->first();
            }
        }
        if (!$employee) {
            $employee = Employee::first();
        }

        $now = Carbon::now();
        $today = $now->toDateString();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance_photos', 'public');
        } elseif ($request->filled('selfie_path')) {
            $photoPath = $request->input('selfie_path');
        }

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'attendance_date' => $today,
        ]);

        if (!$attendance->exists || !$attendance->clock_in) {
            $attendance->clock_in = $now;
        }
        if ($photoPath) {
            $attendance->photo_in = $photoPath;
        }
        $attendance->location_in = $request->input('location_name', 'Devano Office');
        $attendance->status = $attendance->status ?? 'hadir';
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Clock in berhasil dicatat.',
            'data' => [
                'id' => (string) $attendance->id,
                'date' => $today,
                'clock_in' => $attendance->clock_in ? $attendance->clock_in->format('H:i') : $now->format('H:i'),
                'clock_out' => $attendance->clock_out ? $attendance->clock_out->format('H:i') : null,
                'status' => $attendance->status ?? 'hadir',
                'note' => $attendance->notes,
                'photo_url' => $attendance->photo_in,
                'location_name' => $attendance->location_in,
                'is_within_geofence' => true,
            ],
        ]);
    }

    public function clockOut(Request $request)
    {
        $enabled = Setting::where('group', 'mobile_api')->where('key', 'mobile_online_attendance_enabled')->value('value') ?? '1';
        if ($enabled == '0' || $enabled === 'false') {
            $disabledMsg = Setting::where('group', 'mobile_api')->where('key', 'mobile_attendance_disabled_message')->value('value') ?? 'Presensi online saat ini dinonaktifkan oleh administrator. Silakan lakukan absensi melalui mesin fingerprint kantor.';
            return response()->json([
                'success' => false,
                'message' => $disabledMsg,
            ], 403);
        }

        $user = $request->user();
        $employee = null;
        if ($user) {
            $employee = Employee::where('user_id', $user->id)->first();
            if (!$employee && $user->email) {
                $employee = Employee::where('email', $user->email)->first();
            }
        }
        if (!$employee) {
            $employee = Employee::first();
        }

        $now = Carbon::now();
        $today = $now->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'employee_id' => $employee->id,
                'attendance_date' => $today,
                'status' => 'hadir',
            ]);
        }

        $attendance->clock_out = $now;
        $attendance->location_out = $request->input('location_name', 'Devano Office');
        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => 'Clock out berhasil dicatat.',
            'data' => [
                'id' => (string) $attendance->id,
                'date' => $today,
                'clock_in' => $attendance->clock_in ? $attendance->clock_in->format('H:i') : null,
                'clock_out' => $now->format('H:i'),
                'status' => $attendance->status ?? 'hadir',
                'note' => $attendance->notes,
                'photo_url' => $attendance->photo_out ?? $attendance->photo_in,
                'location_name' => $attendance->location_out ?? $attendance->location_in,
                'is_within_geofence' => true,
            ],
        ]);
    }

    public function push(Request $request)
    {
        $validated = $request->validate([
            'records' => 'required|array',
            'records.*.pin' => 'required|string',
            'records.*.timestamp' => 'required|date_format:Y-m-d H:i:s',
            'machine_sn' => 'nullable|string',
        ]);

        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $record) {
            $employee = Employee::where('nik', $record['pin'])->first();

            if (!$employee) {
                $failed++;
                $errors[] = "Employee with PIN {$record['pin']} not found";
                continue;
            }

            try {
                $dateTime = Carbon::parse($record['timestamp']);

                $this->attendanceService->syncAttendance(
                    $employee->id,
                    $dateTime->format('Y-m-d'),
                    $dateTime
                );

                AttendanceLog::create([
                    'employee_id' => $employee->id,
                    'log_date' => $dateTime->toDateString(),
                    'log_time' => $dateTime->toTimeString(),
                    'type' => 'auto',
                    'pin' => $record['pin'],
                    'machine_sn' => $validated['machine_sn'] ?? null,
                    'raw_data' => json_encode($record),
                    'status' => 'processed',
                ]);

                $imported++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Error processing {$record['pin']}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} records, {$failed} failed",
            'data' => [
                'imported' => $imported,
                'failed' => $failed,
                'errors' => $errors,
            ],
        ]);
    }

    public function logs(Request $request)
    {
        $logs = AttendanceLog::with('employee')
            ->when($request->date_from, fn($q, $d) => $q->whereDate('log_date', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('log_date', '<=', $d))
            ->when($request->pin, fn($q, $p) => $q->where('pin', $p))
            ->latest()
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,csv|max:10240',
        ]);

        $path = $request->file('file')->getRealPath();
        $imported = 0;
        $failed = 0;

        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== false) {
                try {
                    $pin = $data[0] ?? null;
                    $timestamp = $data[1] ?? null;

                    if ($pin && $timestamp) {
                        $employee = Employee::where('nik', $pin)->first();

                        if ($employee) {
                            $dateTime = Carbon::parse($timestamp);

                            $this->attendanceService->syncAttendance(
                                $employee->id,
                                $dateTime->format('Y-m-d'),
                                $dateTime
                            );

                            AttendanceLog::create([
                                'employee_id' => $employee->id,
                                'log_date' => $dateTime->toDateString(),
                                'log_time' => $dateTime->toTimeString(),
                                'type' => 'manual',
                                'pin' => $pin,
                                'raw_data' => json_encode($data),
                                'status' => 'processed',
                            ]);

                            $imported++;
                        } else {
                            $failed++;
                        }
                    }
                } catch (\Exception $e) {
                    $failed++;
                }
            }
            fclose($handle);
        }

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} records, {$failed} failed",
            'data' => [
                'imported' => $imported,
                'failed' => $failed,
            ],
        ]);
    }
}