<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function processTxtImport($filePath, $userId): array
    {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = preg_split('/\t|,/', $line);
            if (count($parts) < 2) {
                $failed++;
                $errors[] = "Line " . ($lineNum + 1) . ": Invalid format";
                continue;
            }

            $pin = trim($parts[0]);
            $timestamp = trim($parts[1]);

            try {
                $dateTime = Carbon::parse($timestamp);
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Line " . ($lineNum + 1) . ": Invalid date/time format '{$timestamp}'";
                continue;
            }

            $employee = Employee::where('nik', $pin)->first();
            if (!$employee) {
                $failed++;
                $errors[] = "Line " . ($lineNum + 1) . ": No employee found with NIK '{$pin}'";
                continue;
            }

            $this->syncAttendance($employee->id, $dateTime->format('Y-m-d'), $dateTime, $userId);
            $imported++;
        }

        return [
            'total' => count($lines),
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function processCsvImport($filePath, $userId): array
    {
        $rows = array_map('str_getcsv', file($filePath));
        $headers = array_shift($rows);
        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $lineNum => $row) {
            if (count($row) < 2) {
                $failed++;
                $errors[] = "Row " . ($lineNum + 1) . ": Invalid format";
                continue;
            }

            $pin = trim($row[0]);
            $timestamp = trim($row[1]);

            try {
                $dateTime = Carbon::parse($timestamp);
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Row " . ($lineNum + 1) . ": Invalid date/time format '{$timestamp}'";
                continue;
            }

            $employee = Employee::where('nik', $pin)->first();
            if (!$employee) {
                $failed++;
                $errors[] = "Row " . ($lineNum + 1) . ": No employee found with NIK '{$pin}'";
                continue;
            }

            $this->syncAttendance($employee->id, $dateTime->format('Y-m-d'), $dateTime, $userId);
            $imported++;
        }

        return [
            'total' => count($rows),
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function syncAttendance($employeeId, $date, ?Carbon $dateTime = null, $editedBy = null): Attendance
    {
        $employee = Employee::findOrFail($employeeId);
        $existing = Attendance::where('employee_id', $employeeId)
            ->where('attendance_date', $date)
            ->firstOrNew(['employee_id' => $employeeId, 'attendance_date' => $date]);

        $shift = $employee->shift ?? $existing->shift ?? Shift::first();

        if (!$dateTime) {
            $dateTime = Carbon::now();
        }

        $attendanceDate = Carbon::parse($date);

        if ($existing->exists) {
            $checkpoint = $this->determineNextCheckpoint($existing);

            switch ($checkpoint) {
                case 'clock_in':
                    $existing->clock_in = $dateTime;
                    if ($shift) {
                        $shiftStart = Carbon::parse($attendanceDate->toDateString() . ' ' . $shift->clock_in_time);
                        $lateMinutes = $this->calculateLateMinutes($dateTime, $shiftStart, $shift->late_tolerance_minutes ?? 0);
                        $existing->late_minutes = $lateMinutes;
                        $existing->status = $lateMinutes > 0 ? 'terlambat' : 'hadir';
                    }
                    break;

                case 'break_out':
                    $existing->break_out = $dateTime;
                    break;

                case 'break_in':
                    $existing->break_in = $dateTime;
                    if ($shift) {
                        $breakStart = $shift->break_start ? Carbon::parse($attendanceDate->toDateString() . ' ' . $shift->break_start) : null;
                        $breakEnd = $shift->break_end ? Carbon::parse($attendanceDate->toDateString() . ' ' . $shift->break_end) : null;
                        $existing->excess_break_minutes = $this->calculateExcessBreakMinutes(
                            $existing->break_out, $dateTime, $breakStart, $breakEnd
                        );
                    }
                    break;

                case 'clock_out':
                    $existing->clock_out = $dateTime;
                    if ($shift) {
                        $saturdayEnd = $shift->saturday_clock_out_time && $attendanceDate->dayOfWeek === Carbon::SATURDAY
                            ? $shift->saturday_clock_out_time
                            : $shift->clock_out_time;
                        $shiftEnd = Carbon::parse($attendanceDate->toDateString() . ' ' . $saturdayEnd);
                        $existing->early_leave_minutes = $this->calculateEarlyLeaveMinutes($dateTime, $shiftEnd);
                    }
                    break;

                case 'overtime_in':
                    $existing->overtime_in = $dateTime;
                    break;

                case 'overtime_out':
                    $existing->overtime_out = $dateTime;
                    $existing->overtime_minutes = $this->calculateOvertimeMinutes(
                        $existing->overtime_in, $dateTime
                    );
                    break;

                default:
                    return $existing;
            }

            $existing->save();

            AttendanceLog::create([
                'employee_id' => $employeeId,
                'attendance_id' => $existing->id,
                'log_date' => $dateTime->toDateString(),
                'log_time' => $dateTime->toTimeString(),
                'type' => 'auto',
                'pin' => $employee->nik,
                'status' => $checkpoint,
            ]);

            return $existing;
        }

        $lateMinutes = 0;
        if ($shift) {
            $shiftStart = Carbon::parse($attendanceDate->toDateString() . ' ' . $shift->clock_in_time);
            $lateMinutes = $this->calculateLateMinutes(
                $dateTime,
                $shiftStart,
                $shift->late_tolerance_minutes ?? 0
            );
        }

        $status = $lateMinutes > 0 ? 'terlambat' : 'hadir';

        $attendance = Attendance::create([
            'employee_id' => $employeeId,
            'shift_id' => $shift?->id,
            'attendance_date' => $date,
            'clock_in' => $dateTime,
            'clock_out' => null,
            'status' => $status,
            'late_minutes' => $lateMinutes,
            'early_leave_minutes' => 0,
            'overtime_minutes' => 0,
        ]);

        AttendanceLog::create([
            'employee_id' => $employeeId,
            'attendance_id' => $attendance->id,
            'log_date' => $dateTime->toDateString(),
            'log_time' => $dateTime->toTimeString(),
            'type' => 'auto',
            'pin' => $employee->nik,
            'status' => 'clock_in',
        ]);

        return $attendance;
    }

    public function determineNextCheckpoint(Attendance $attendance): string
    {
        if (!$attendance->clock_in) return 'clock_in';
        if (!$attendance->break_out) return 'break_out';
        if (!$attendance->break_in) return 'break_in';
        if (!$attendance->clock_out) return 'clock_out';
        if (!$attendance->overtime_in) return 'overtime_in';
        if (!$attendance->overtime_out) return 'overtime_out';
        return 'completed';
    }

    public function importCheckpointFile($filePath, $userId): array
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return ['total' => 0, 'imported' => 0, 'failed' => 1, 'errors' => ['Cannot read file']];
        }

        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }

        $lines = explode("\n", $content);
        $rows = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $rows[] = str_getcsv($line);
        }

        $imported = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $lineNum => $row) {
            $pin = trim($row[0] ?? '');
            $dateRaw = trim($row[6] ?? '');
            $clockInRaw = trim($row[7] ?? '');
            $breakOutRaw = trim($row[8] ?? '');
            $breakInRaw = trim($row[9] ?? '');
            $clockOutRaw = trim($row[10] ?? '');
            $overtimeInRaw = trim($row[11] ?? '');
            $overtimeOutRaw = trim($row[12] ?? '');

            if (empty($pin) || empty($dateRaw)) {
                $failed++;
                $errors[] = "Line " . ($lineNum + 1) . ": Invalid format - missing pin or date";
                continue;
            }

            $employee = Employee::where('nik', $pin)->first();
            if (!$employee) {
                $failed++;
                $errors[] = "Line " . ($lineNum + 1) . ": No employee found with NIK '{$pin}'";
                continue;
            }

            try {
                $date = Carbon::createFromFormat('d-m-Y', $dateRaw)->format('Y-m-d');
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Line " . ($lineNum + 1) . ": Invalid date format '{$dateRaw}'";
                continue;
            }

            $attendance = Attendance::firstOrNew([
                'employee_id' => $employee->id,
                'attendance_date' => $date,
            ]);

            $attendance->employee_id = $employee->id;
            $attendance->attendance_date = $date;
            $attendance->shift_id ??= $employee->shift_id ?? \App\Models\Shift::value('id');

            if (!empty($clockInRaw)) {
                $dt = Carbon::createFromFormat('d-m-Y H:i:s', "$dateRaw $clockInRaw");
                $attendance->clock_in = $dt;

                $shiftForCalc = $employee->shift ?? $attendance->shift ?? Shift::find($attendance->shift_id);
                if ($shiftForCalc) {
                    $shiftStart = Carbon::parse($date . ' ' . $shiftForCalc->clock_in_time);
                    $late = $this->calculateLateMinutes($dt, $shiftStart, $shiftForCalc->late_tolerance_minutes ?? 0);
                    $attendance->late_minutes = $late;
                    $attendance->status = $late > 0 ? 'terlambat' : 'hadir';
                }
            }

            if (!empty($breakOutRaw)) {
                $attendance->break_out = Carbon::createFromFormat('d-m-Y H:i:s', "$dateRaw $breakOutRaw");
            }

            if (!empty($breakInRaw)) {
                $attendance->break_in = Carbon::createFromFormat('d-m-Y H:i:s', "$dateRaw $breakInRaw");

                if ($shiftForCalc && $attendance->break_out) {
                    $breakStart = $shiftForCalc->break_start ? Carbon::parse($date . ' ' . $shiftForCalc->break_start) : null;
                    $breakEnd = $shiftForCalc->break_end ? Carbon::parse($date . ' ' . $shiftForCalc->break_end) : null;
                    $attendance->excess_break_minutes = $this->calculateExcessBreakMinutes(
                        $attendance->break_out, $attendance->break_in, $breakStart, $breakEnd
                    );
                }
            }

            if (!empty($clockOutRaw)) {
                $attendance->clock_out = Carbon::createFromFormat('d-m-Y H:i:s', "$dateRaw $clockOutRaw");
            }

            if (!empty($overtimeInRaw)) {
                $attendance->overtime_in = Carbon::createFromFormat('d-m-Y H:i:s', "$dateRaw $overtimeInRaw");
            }

            if (!empty($overtimeOutRaw)) {
                $attendance->overtime_out = Carbon::createFromFormat('d-m-Y H:i:s', "$dateRaw $overtimeOutRaw");
            }

            $attendance->save();

            AttendanceLog::create([
                'employee_id' => $employee->id,
                'attendance_id' => $attendance->id,
                'log_date' => $date,
                'log_time' => now()->toTimeString(),
                'type' => 'import',
                'pin' => $employee->nik,
                'file_name' => basename($filePath),
                'status' => 'imported',
            ]);

            $imported++;
        }

        return [
            'total' => count($rows),
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    public function calculateLateMinutes(Carbon $clockIn, Carbon $shiftStart, int $tolerance = 0): int
    {
        $clockIn = $clockIn->copy()->startOfMinute();
        $shiftStart = $shiftStart->copy()->startOfMinute();
        $gracePeriodEnd = $shiftStart->copy()->addMinutes($tolerance);
        if ($clockIn->lte($gracePeriodEnd)) {
            return 0;
        }
        return (int) $clockIn->diffInMinutes($shiftStart, true);
    }

    public function calculateEarlyLeaveMinutes(Carbon $clockOut, Carbon $shiftEnd): int
    {
        $clockOut = $clockOut->copy()->startOfMinute();
        $shiftEnd = $shiftEnd->copy()->startOfMinute();
        if ($clockOut->gte($shiftEnd)) {
            return 0;
        }
        return (int) $clockOut->diffInMinutes($shiftEnd, true);
    }

    public function calculateExcessBreakMinutes(?Carbon $breakOut, ?Carbon $breakIn, ?Carbon $shiftBreakStart, ?Carbon $shiftBreakEnd): int
    {
        if (!$breakOut || !$breakIn || !$shiftBreakStart || !$shiftBreakEnd) return 0;
        $breakOut = $breakOut->copy()->startOfMinute();
        $breakIn = $breakIn->copy()->startOfMinute();
        $shiftBreakStart = $shiftBreakStart->copy()->startOfMinute();
        $shiftBreakEnd = $shiftBreakEnd->copy()->startOfMinute();
        $actualBreak = (int) $breakOut->diffInMinutes($breakIn, true);
        $scheduledBreak = (int) $shiftBreakStart->diffInMinutes($shiftBreakEnd, true);
        $excess = $actualBreak - $scheduledBreak;
        return $excess > 0 ? $excess : 0;
    }

    public function calculateOvertimeMinutes(?Carbon $overtimeIn, ?Carbon $overtimeOut): int
    {
        if (!$overtimeIn || !$overtimeOut) {
            return 0;
        }
        $overtimeIn = $overtimeIn->copy()->startOfMinute();
        $overtimeOut = $overtimeOut->copy()->startOfMinute();
        if ($overtimeOut->lte($overtimeIn)) {
            return 0;
        }
        return (int) $overtimeIn->diffInMinutes($overtimeOut, true);
    }

    public function recalculateAttendance(Attendance $attendance): Attendance
    {
        $attendance->loadMissing('employee.shift');
        $shift = $attendance->employee?->shift ?? $attendance->shift ?? Shift::first();
        if (!$shift) return $attendance;

        $attDate = $attendance->attendance_date instanceof Carbon
            ? $attendance->attendance_date
            : Carbon::parse($attendance->attendance_date);
        $dateStr = $attDate->toDateString();

        foreach (['clock_in', 'clock_out', 'break_out', 'break_in', 'overtime_in', 'overtime_out'] as $field) {
            $val = $attendance->$field;
            if ($val && $val->format('Y-m-d') !== $dateStr) {
                $attendance->$field = Carbon::parse($dateStr . ' ' . $val->format('H:i:s'));
            }
        }

        $isSunday = $attDate->dayOfWeek === Carbon::SUNDAY;

        if ($attendance->ignore_late) {
            $attendance->late_minutes = 0;
            if ($attendance->status === 'terlambat') {
                $attendance->status = 'hadir';
            }
        }

        if ($attendance->ignore_early_leave) {
            $attendance->early_leave_minutes = 0;
        }

        if ($attendance->ignore_excess_break) {
            $attendance->excess_break_minutes = 0;
        }

        if (!$attendance->ignore_late && $attendance->clock_in && !$isSunday) {
            $shiftStart = Carbon::parse($dateStr . ' ' . $shift->clock_in_time);
            $lateMinutes = $this->calculateLateMinutes(
                $attendance->clock_in, $shiftStart, $shift->late_tolerance_minutes ?? 0
            );
            $attendance->late_minutes = $lateMinutes;
            $attendance->status = ($lateMinutes > 0 && !$attendance->is_half_day) ? 'terlambat' : 'hadir';
        } else {
            $attendance->late_minutes = 0;
            if ($isSunday) {
                $attendance->status = 'hadir';
            }
        }

        if (!$attendance->ignore_early_leave && $attendance->clock_out && !$isSunday) {
            $saturdayEnd = $shift->saturday_clock_out_time && $attDate->dayOfWeek === Carbon::SATURDAY
                ? $shift->saturday_clock_out_time
                : $shift->clock_out_time;
            $shiftEnd = Carbon::parse($attDate->toDateString() . ' ' . $saturdayEnd);
            $attendance->early_leave_minutes = $this->calculateEarlyLeaveMinutes($attendance->clock_out, $shiftEnd);
            if ($attendance->clock_in) {
                $attendance->overtime_minutes = $this->calculateOvertimeMinutes(
                    $attendance->overtime_in, $attendance->overtime_out
                );
            }
        } else {
            $attendance->early_leave_minutes = 0;
        }
        if ($attendance->overtime_in || $attendance->overtime_out) {
            $attendance->overtime_minutes = $this->calculateOvertimeMinutes(
                $attendance->overtime_in, $attendance->overtime_out
            );
        }

        if (!$attendance->ignore_excess_break && $attendance->break_out && $attendance->break_in) {
            $breakStart = $shift->break_start ? Carbon::parse($dateStr . ' ' . $shift->break_start) : null;
            $breakEnd = $shift->break_end ? Carbon::parse($dateStr . ' ' . $shift->break_end) : null;
            $attendance->excess_break_minutes = $this->calculateExcessBreakMinutes(
                $attendance->break_out, $attendance->break_in, $breakStart, $breakEnd
            );
        }

        $attendance->save();
        return $attendance;
    }
}