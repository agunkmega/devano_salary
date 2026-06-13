<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ImportLog;
use App\Models\Shift;
use App\Services\AttendanceService;
use Carbon\Carbon;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use LogsActivity;
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        $dateFrom = request('date_from');
        $dateTo = request('date_to');
        $employeeSearch = request('employee');

        $employees = Employee::with('department')
            ->where('is_active', true)
            ->when(request('department_id'), function ($q, $deptId) {
                $q->where('department_id', $deptId);
            })
            ->get();

        $departments = Department::where('is_active', true)->get(['id', 'name']);

        $attendancesData = collect();

        if ($dateFrom && $dateTo && $employeeSearch) {
            $filteredEmployees = $employees->filter(fn($emp) =>
                stripos($emp->full_name, $employeeSearch) !== false
            );

            $attendances = Attendance::with(['employee.user', 'employee.department', 'shift'])
                ->whereIn('employee_id', $filteredEmployees->pluck('id'))
                ->whereDate('attendance_date', '>=', $dateFrom)
                ->whereDate('attendance_date', '<=', $dateTo)
                ->get()
                ->each(fn($att) => $this->attendanceService->recalculateAttendance($att))
                ->keyBy(fn($att) => $att->employee_id . '|' . $att->attendance_date->format('Y-m-d'));

            $leaves = \App\Models\Leave::with('leaveType')
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $dateTo)
                ->whereDate('end_date', '>=', $dateFrom)
                ->get()
                ->groupBy('employee_id');

            $holidaysByDate = \App\Models\NationalHoliday::where('is_active', true)
                ->whereDate('date', '>=', $dateFrom)
                ->whereDate('date', '<=', $dateTo)
                ->get(['date', 'religion'])
                ->groupBy(fn($h) => $h->date->format('Y-m-d'))
                ->map(fn($items) => $items->pluck('religion')->toArray())
                ->toArray();

            $period = \Carbon\CarbonPeriod::create($dateFrom, $dateTo);
            $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

            foreach ($period as $date) {
            foreach ($filteredEmployees as $emp) {
                $key = $emp->id . '|' . $date->format('Y-m-d');
                $att = $attendances->get($key);

                $name = $emp->full_name;
                $words = preg_split('/\s+/', trim($name));
                $initials = strtoupper(
                    substr($words[0] ?? '', 0, 1) .
                    substr($words[1] ?? $words[0] ?? '', 0, 1)
                );

                $status = $att ? ucfirst($att->status) : '-';

                $employeeLeaves = $leaves->get($emp->id);
                $leaveTypeName = null;
                if ($employeeLeaves) {
                    foreach ($employeeLeaves as $leave) {
                        if ($date->between($leave->start_date, $leave->end_date)) {
                            $status = 'Cuti';
                            $leaveTypeName = $leave->leaveType?->name;
                            break;
                        }
                    }
                }

                if ($status === '-') {
                    $dateStr = $date->format('Y-m-d');
                    if (isset($holidaysByDate[$dateStr])) {
                        $empReligion = $emp->religion;
                        $isHolidayForEmployee = collect($holidaysByDate[$dateStr])->contains(fn($religion) => empty($religion) || $religion === $empReligion);
                        if ($isHolidayForEmployee) {
                            $status = 'Libur';
                        }
                    }
                }

                $attendancesData->push([
                    'id' => $att?->id,
                    'employee_id' => $emp->id,
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $dayNames[$date->dayOfWeek],
                    'employee' => $name,
                    'initials' => $initials,
                    'clock_in' => $att?->clock_in?->format('H:i'),
                    'break_out' => $att?->break_out?->format('H:i'),
                    'break_in' => $att?->break_in?->format('H:i'),
                    'clock_out' => $att?->clock_out?->format('H:i'),
                    'overtime_in' => $att?->overtime_in?->format('H:i'),
                    'overtime_out' => $att?->overtime_out?->format('H:i'),
                    'status' => $status,
                    'leave_type_name' => $leaveTypeName,
                    'late_minutes' => $att?->late_minutes,
                    'early_leave_minutes' => $att?->early_leave_minutes,
                    'excess_break_minutes' => $att?->excess_break_minutes,
                    'overtime_minutes' => $att?->overtime_minutes,
                    'department' => $emp->department?->name ?? '',
                    'ignore_late' => $att?->ignore_late ?? false,
                    'ignore_early_leave' => $att?->ignore_early_leave ?? false,
                    'ignore_excess_break' => $att?->ignore_excess_break ?? false,
                ]);
            }
        }
        }

        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get(['id', 'name', 'code']);
        return view('attendances.index', compact('attendancesData', 'employees', 'departments', 'dateFrom', 'dateTo', 'leaveTypes'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->get();
        $shifts = Shift::where('is_active', true)->get();

        return view('attendances.create', compact('employees', 'shifts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'break_out' => 'nullable|date_format:H:i',
            'break_in' => 'nullable|date_format:H:i',
            'overtime_in' => 'nullable|date_format:H:i',
            'overtime_out' => 'nullable|date_format:H:i',
            'status' => 'required|in:hadir,terlambat,izin,sakit,cuti,alpha',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $dateStr = $validated['attendance_date'];
        foreach (['clock_in', 'clock_out', 'break_out', 'break_in', 'overtime_in', 'overtime_out'] as $field) {
            if (!empty($validated[$field])) {
                $validated[$field] = $dateStr . ' ' . $validated[$field];
            }
        }

        $data = array_merge($validated, [
            'shift_id' => $employee->shift_id,
            'is_manual' => true,
            'edited_by' => auth()->id(),
        ]);

        Attendance::create($data);

        $this->logActivity('attendance', 'Create', 'Menambahkan absensi ' . $employee->full_name, 'Attendance', $employee->id);

        return redirect()->to(route('admin.attendances.index') . '?' . http_build_query(request()->only(['date_from', 'date_to', 'department_id', 'employee'])))
            ->with('success', 'Manual attendance created successfully.');
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['employee.user', 'employee.department', 'shift', 'logs', 'editor']);

        return view('attendances.show', compact('attendance'));
    }

    public function edit(Attendance $attendance)
    {
        return redirect()->route('admin.attendances.index');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'break_out' => 'nullable',
            'break_in' => 'nullable',
            'overtime_in' => 'nullable',
            'overtime_out' => 'nullable',
            'status' => 'required|in:hadir,terlambat,izin,sakit,cuti,alpha',
            'late_minutes' => 'nullable|integer|min:0',
            'early_leave_minutes' => 'nullable|integer|min:0',
            'overtime_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'manual_reason' => 'nullable|string',
            'ignore_late' => 'nullable|boolean',
            'ignore_early_leave' => 'nullable|boolean',
            'ignore_excess_break' => 'nullable|boolean',
        ]);

        $validated['is_manual'] = true;
        $validated['edited_by'] = auth()->id();
        $validated['ignore_late'] = $request->boolean('ignore_late');
        $validated['ignore_early_leave'] = $request->boolean('ignore_early_leave');
        $validated['ignore_excess_break'] = $request->boolean('ignore_excess_break');

        $dateStr = $validated['attendance_date'];
        foreach (['clock_in', 'clock_out', 'break_out', 'break_in', 'overtime_in', 'overtime_out'] as $field) {
            if (!empty($validated[$field])) {
                $validated[$field] = $dateStr . ' ' . $validated[$field];
            }
        }

        $attendance->update($validated);
        $this->attendanceService->recalculateAttendance($attendance->fresh());

        $emp = $attendance->employee;
        $this->logActivity('attendance', 'Update', 'Mengubah absensi ' . ($emp->full_name ?? '') . ' tanggal ' . $attendance->attendance_date->format('Y-m-d'), 'Attendance', $attendance->id);

        return redirect()->to(route('admin.attendances.index') . '?' . http_build_query(request()->only(['date_from', 'date_to', 'department_id', 'employee'])))
            ->with('success', 'Attendance updated successfully.');
    }

    public function importTxt(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:txt,csv|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $records = file($file->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            $success = 0;
            $failed = 0;
            $errors = [];

            foreach ($records as $line => $record) {
                $parts = preg_split('/\s+/', trim($record));
                if (count($parts) < 2) {
                    $failed++;
                    $errors[] = "Line " . ($line + 1) . ": Invalid format";
                    continue;
                }

                $pin = $parts[0];
                $timestamp = $parts[1] . (isset($parts[2]) ? ' ' . $parts[2] : '');

                $employee = Employee::where('nik', $pin)->first();
                if (!$employee) {
                    $failed++;
                    $errors[] = "Line " . ($line + 1) . ": Employee with NIK/PIN '{$pin}' not found";
                    continue;
                }

                try {
                    $dateTime = Carbon::parse($timestamp);
                    $this->attendanceService->syncAttendance($employee->id, $dateTime->format('Y-m-d'), $dateTime, auth()->id());
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Line " . ($line + 1) . ": " . $e->getMessage();
                }
            }

            ImportLog::create([
                'user_id' => auth()->id(),
                'file_name' => $fileName,
                'file_type' => 'txt',
                'model_type' => 'attendance',
                'total_records' => count($records),
                'success_records' => $success,
                'failed_records' => $failed,
                'errors' => array_slice($errors, 0, 100),
                'status' => $failed > 0 && $success === 0 ? 'failed' : 'completed',
            ]);

            $message = "Imported {$success} records successfully.";
            if ($failed > 0) {
                $message .= " {$failed} records failed.";
            }

            $this->logActivity('attendance', 'Import', 'Import absensi via TXT', 'Attendance');

            return redirect()->route('admin.attendances.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.attendances.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function importCheckpoint(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:txt,csv|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $result = $this->attendanceService->importCheckpointFile($file->getRealPath(), auth()->id());

            ImportLog::create([
                'user_id' => auth()->id(),
                'file_name' => $fileName,
                'file_type' => 'txt',
                'model_type' => 'attendance',
                'total_records' => $result['total'],
                'success_records' => $result['imported'],
                'failed_records' => $result['failed'],
                'errors' => array_slice($result['errors'], 0, 100),
                'status' => $result['failed'] > 0 && $result['imported'] === 0 ? 'failed' : 'completed',
            ]);

            $message = "Imported {$result['imported']} records successfully.";
            if ($result['failed'] > 0) {
                $message .= " {$result['failed']} records failed.";
            }

            return redirect()->route('admin.attendances.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.attendances.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:5120',
        ]);

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $handle = fopen($file->getRealPath(), 'r');
            $headers = fgetcsv($handle);

            $success = 0;
            $failed = 0;
            $errors = [];
            $total = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $total++;
                $data = array_combine($headers, $row);

                $employee = Employee::where('nik', $data['pin'] ?? $data['nik'] ?? '')->first();
                if (!$employee) {
                    $failed++;
                    $errors[] = "Row {$total}: Employee not found";
                    continue;
                }

                try {
                    $dateTime = Carbon::parse(($data['date'] ?? '') . ' ' . ($data['time'] ?? ''));
                    $this->attendanceService->syncAttendance($employee->id, $dateTime->format('Y-m-d'), $dateTime, auth()->id());
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Row {$total}: " . $e->getMessage();
                }
            }

            fclose($handle);

            ImportLog::create([
                'user_id' => auth()->id(),
                'file_name' => $fileName,
                'file_type' => 'csv',
                'model_type' => 'attendance',
                'total_records' => $total,
                'success_records' => $success,
                'failed_records' => $failed,
                'errors' => array_slice($errors, 0, 100),
                'status' => $failed > 0 && $success === 0 ? 'failed' : 'completed',
            ]);

            $message = "Imported {$success} records successfully.";
            if ($failed > 0) {
                $message .= " {$failed} records failed.";
            }

            $this->logActivity('attendance', 'Import', 'Import absensi via CSV', 'Attendance');

            return redirect()->route('admin.attendances.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('admin.attendances.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function history()
    {
        $logs = ImportLog::with('user')
            ->where('model_type', 'attendance')
            ->latest()
            ->paginate(20);

        return view('attendances.history', compact('logs'));
    }
}
