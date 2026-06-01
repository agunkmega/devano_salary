<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function attendance()
    {
        $query = Attendance::with(['employee.user', 'employee.department', 'employee.position', 'shift'])
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('attendance_date', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('attendance_date', '<=', $date);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('employee_id'), function ($q, $empId) {
                $q->where('employee_id', $empId);
            })
            ->when(request('employee_type'), function ($q, $type) {
                $q->whereHas('employee', function ($sub) use ($type) {
                    $sub->where('employee_type', $type);
                });
            });

        $all = $query->get();

        $summary = [
            'total' => $all->count(),
            'hadir' => $all->where('status', 'hadir')->count(),
            'terlambat' => $all->where('status', 'terlambat')->count(),
            'izin' => $all->where('status', 'izin')->count(),
            'sakit' => $all->where('status', 'sakit')->count(),
            'cuti' => $all->where('status', 'cuti')->count(),
            'alpha' => $all->where('status', 'alpha')->count(),
        ];

        $dateFrom = request('date_from') ? Carbon::parse(request('date_from')) : now()->startOfMonth();
        $dateTo = request('date_to') ? Carbon::parse(request('date_to')) : now()->endOfMonth();

        $holidayDates = \App\Models\NationalHoliday::where('is_active', true)
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $periodWorkDays = 0;
        $sundayCount = 0;
        $cursor = $dateFrom->copy();
        while ($cursor->lte($dateTo)) {
            if ($cursor->dayOfWeek === Carbon::SUNDAY) {
                $sundayCount++;
            } elseif (!in_array($cursor->format('Y-m-d'), $holidayDates)) {
                $periodWorkDays++;
            }
            $cursor->addDay();
        }

        $employeeSummaries = $all->groupBy('employee_id')->map(function ($rows, $empId) use ($periodWorkDays, $sundayCount) {
            $emp = $rows->first()->employee;
            $hadir = $rows->where('status', 'hadir')->count();
            $terlambat = $rows->where('status', 'terlambat')->count();
            $totalHadir = $hadir + $terlambat;

            $totalHari = $periodWorkDays > 0 ? $periodWorkDays : $totalHadir;
            $persentase = $emp->employee_type === 'bulanan'
                ? round(($totalHadir + $sundayCount) / ($totalHari + $sundayCount) * 100, 1)
                : null;

            return [
                'employee_id' => $empId,
                'employee_type' => $emp->employee_type,
                'nama' => $emp->full_name ?? '-',
                'jabatan' => $emp->position->name ?? $emp->department->name ?? '-',
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $rows->where('status', 'izin')->count(),
                'sakit' => $rows->where('status', 'sakit')->count(),
                'cuti' => $rows->where('status', 'cuti')->count(),
                'alpha' => $rows->where('status', 'alpha')->count(),
                'telat_hari' => $rows->where('late_minutes', '>', 0)->count(),
                'telat_menit' => $rows->sum('late_minutes'),
                'pulang_awal_hari' => $rows->where('early_leave_minutes', '>', 0)->count(),
                'pulang_awal_menit' => $rows->sum('early_leave_minutes'),
                'istirahat_lebih_hari' => $rows->where('excess_break_minutes', '>', 0)->count(),
                'istirahat_lebih_menit' => $rows->sum('excess_break_minutes'),
                'persentase' => $persentase,
            ];
        })->values();

        $departments = Department::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();

        return view('reports.attendance', compact('employeeSummaries', 'departments', 'employees', 'summary', 'periodWorkDays'));
    }

    public function attendancePrint(Request $request)
    {
        $query = Attendance::with(['employee.user', 'employee.department', 'employee.position', 'shift'])
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->whereDate('attendance_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->whereDate('attendance_date', '<=', $request->date_to);
            })
            ->when($request->filled('department_id'), function ($q) use ($request) {
                $q->whereHas('employee', fn($sub) => $sub->where('department_id', $request->department_id));
            })
            ->when($request->filled('employee'), function ($q) use ($request) {
                $q->whereHas('employee', fn($sub) => $sub->where('full_name', 'like', '%' . $request->employee . '%'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            });

        $attendances = $query->orderBy('attendance_date')->orderBy('employee_id')->get();

        $leaveEmpQuery = \App\Models\Leave::where('status', 'approved')
            ->whereDate('end_date', '>=', $request->date_from ?? $attendances->min('attendance_date'))
            ->whereDate('start_date', '<=', $request->date_to ?? $attendances->max('attendance_date'));
        if ($request->filled('department_id')) {
            $leaveEmpQuery->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('employee')) {
            $leaveEmpQuery->whereHas('employee', fn($q) => $q->where('full_name', 'like', '%' . $request->employee . '%'));
        }
        $leaveEmpIds = $leaveEmpQuery->pluck('employee_id')->unique();

        $employeeIds = $attendances->pluck('employee_id')->unique()->merge($leaveEmpIds)->unique();
        $allEmployees = Employee::with(['position', 'department'])->whereIn('id', $employeeIds)->get()->keyBy('id');

        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->date_from) : $attendances->min('attendance_date');
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->date_to) : $attendances->max('attendance_date');

        if (!$dateFrom) {
            return view('reports.attendance-print', ['employees' => collect(), 'dateFrom' => null, 'dateTo' => null]);
        }

        $holidayDates = \App\Models\NationalHoliday::where('is_active', true)
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $leaves = \App\Models\Leave::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->whereDate('end_date', '>=', $dateFrom)
            ->whereDate('start_date', '<=', $dateTo)
            ->get(['employee_id', 'start_date', 'end_date']);

        $leaveDateMap = [];
        foreach ($leaves as $leave) {
            $s = Carbon::parse($leave->start_date);
            $e = Carbon::parse($leave->end_date);
            while ($s->lte($e)) {
                $leaveDateMap[$leave->employee_id][$s->format('Y-m-d')] = true;
                $s->addDay();
            }
        }

        $grouped = $attendances->groupBy('employee_id')->map(function ($rows, $empId) use ($allEmployees, $dateFrom, $dateTo, $holidayDates, $leaveDateMap) {
            $emp = $allEmployees->get($empId);
            $attByDate = $rows->keyBy(fn($r) => $r->attendance_date->format('Y-m-d'));
            $fullRows = [];
            $cursor = $dateFrom->copy();
            while ($cursor->lte($dateTo)) {
                $dateStr = $cursor->format('Y-m-d');
                $dayName = $cursor->locale('id')->dayName;
                if (isset($attByDate[$dateStr])) {
                    $att = $attByDate[$dateStr];
                    $att->day_name = $dayName;
                    $fullRows[] = $att;
                } elseif ($cursor->dayOfWeek === Carbon::SUNDAY || in_array($dateStr, $holidayDates)) {
                    $dummy = $this->makeDummyAtt($dateStr, $dayName, 'libur');
                    $fullRows[] = $dummy;
                } elseif (isset($leaveDateMap[$empId][$dateStr])) {
                    $dummy = $this->makeDummyAtt($dateStr, $dayName, 'cuti');
                    $fullRows[] = $dummy;
                } else {
                    $dummy = $this->makeDummyAtt($dateStr, $dayName, 'alpha');
                    $fullRows[] = $dummy;
                }
                $cursor->addDay();
            }
            return [
                'employee_id' => $empId,
                'nama' => $emp->full_name ?? '-',
                'jabatan' => $emp->position->name ?? $emp->department->name ?? '-',
                'rows' => collect($fullRows),
            ];
        })->values();

        $employees = $grouped;

        return view('reports.attendance-print', compact('employees', 'dateFrom', 'dateTo'));
    }

    public function attendanceExcel()
    {
        $attendances = Attendance::with(['employee.user', 'employee.department', 'shift'])
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('attendance_date', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('attendance_date', '<=', $date);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->get();

        $data = $attendances->map(function ($att) {
            return [
                'Date' => $att->attendance_date->format('Y-m-d'),
                'Employee' => $att->employee->full_name ?? 'N/A',
                'Department' => $att->employee->department->name ?? 'N/A',
                'Shift' => $att->shift->name ?? 'N/A',
                'Clock In' => $att->clock_in ? Carbon::parse($att->clock_in)->format('H:i:s') : '-',
                'Clock Out' => $att->clock_out ? Carbon::parse($att->clock_out)->format('H:i:s') : '-',
                'Status' => $att->status,
                'Late (min)' => $att->late_minutes ?? 0,
                'Overtime (min)' => $att->overtime_minutes ?? 0,
            ];
        });

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data->toArray(); }
        }, 'attendance-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function attendancePdf()
    {
        $attendances = Attendance::with(['employee.user', 'employee.department', 'shift'])
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('attendance_date', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('attendance_date', '<=', $date);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->get();

        $pdf = Pdf::loadView('reports.attendance-pdf', compact('attendances'));

        return $pdf->download('attendance-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function lateness()
    {
        $attendances = Attendance::with(['employee.user', 'employee.department'])
            ->whereIn('status', ['terlambat'])
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('attendance_date', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('attendance_date', '<=', $date);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('employee_id'), function ($q, $empId) {
                $q->where('employee_id', $empId);
            })
            ->latest()
            ->paginate(50);

        $departments = Department::where('is_active', true)->get();

        return view('reports.lateness', compact('attendances', 'departments'));
    }

    public function latenessExcel()
    {
        $attendances = Attendance::with(['employee.user', 'employee.department'])
            ->whereIn('status', ['terlambat'])
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('attendance_date', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('attendance_date', '<=', $date);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->get();

        $data = $attendances->map(function ($att) {
            return [
                'Date' => $att->attendance_date->format('Y-m-d'),
                'Employee' => $att->employee->full_name ?? 'N/A',
                'Department' => $att->employee->department->name ?? 'N/A',
                'Clock In' => $att->clock_in ? Carbon::parse($att->clock_in)->format('H:i:s') : '-',
                'Late (min)' => $att->late_minutes ?? 0,
            ];
        });

        return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function array(): array { return $this->data->toArray(); }
        }, 'lateness-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function overtime()
    {
        $attendances = Attendance::with(['employee.user', 'employee.department'])
            ->where('overtime_minutes', '>', 0)
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('attendance_date', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('attendance_date', '<=', $date);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('employee_id'), function ($q, $empId) {
                $q->where('employee_id', $empId);
            })
            ->latest()
            ->paginate(50);

        $departments = Department::where('is_active', true)->get();
        $totalOvertimeMinutes = $attendances->sum('overtime_minutes');
        $totalOvertimePay = 0;

        foreach ($attendances as $att) {
            $hourlyRate = ($att->employee->base_salary ?? 0) / 173;
            $totalOvertimePay += ($att->overtime_minutes / 60) * $hourlyRate * 1.5;
        }

        return view('reports.overtime', compact('attendances', 'departments', 'totalOvertimeMinutes', 'totalOvertimePay'));
    }

    public function leave()
    {
        $query = Leave::with(['employee.user', 'employee.department', 'leaveType', 'approver'])
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when(request('leave_type_id'), function ($q, $typeId) {
                $q->where('leave_type_id', $typeId);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('date_from'), function ($q, $date) {
                $q->whereDate('start_date', '>=', $date);
            })
            ->when(request('date_to'), function ($q, $date) {
                $q->whereDate('end_date', '<=', $date);
            });

        $all = (clone $query)->get();

        $summary = [
            'pending' => $all->where('status', 'pending')->count(),
            'approved' => $all->where('status', 'approved')->count(),
            'rejected' => $all->where('status', 'rejected')->count(),
            'cancelled' => $all->where('status', 'cancelled')->count(),
            'total_days' => $all->sum('total_days'),
        ];

        $leaves = $query->latest()->paginate(50);

        $departments = Department::where('is_active', true)->get();

        return view('reports.leave', compact('leaves', 'departments', 'summary'));
    }

    public function payroll()
    {
        $query = Payroll::with(['employee.user', 'employee.department', 'employee.position'])
            ->when(request('period'), function ($q, $period) {
                $q->where('period', $period);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('employee_id'), function ($q, $empId) {
                $q->where('employee_id', $empId);
            })
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when(request('employee_type'), function ($q, $type) {
                $q->whereHas('employee', function ($sub) use ($type) {
                    $sub->where('employee_type', $type);
                });
            })
            ->latest();

        $all = $query->get();

        $summary = [
            'count' => $all->count(),
            'total_base_salary' => $all->sum('base_salary'),
            'total_allowance' => $all->sum('allowance'),
            'total_overtime' => $all->sum('overtime_pay'),
            'total_deductions' => $all->sum('total_deductions'),
            'total_net_salary' => $all->sum('net_salary'),
            'total_bpjs' => $all->sum('bpjs_deduction'),
            'total_tax' => $all->sum('tax_amount'),
            'total_cash_advance' => $all->sum('cash_advance_deduction'),
        ];

        $payrolls = $query->paginate(50);
        $periods = Payroll::select('period')->distinct()->orderBy('period', 'desc')->pluck('period');
        $departments = Department::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();

        return view('reports.payroll', compact('payrolls', 'periods', 'departments', 'employees', 'summary'));
    }

    public function payrollPrint(Request $request)
    {
        $query = Payroll::with(['employee.position', 'employee.department']);

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }
        if ($request->filled('department_id')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('employee_type')) {
            $query->whereHas('employee', fn($q) => $q->where('employee_type', $request->employee_type));
        }

        $payrolls = $query->orderBy('period', 'desc')->get();
        $period = $request->period;

        return view('reports.payroll-print', compact('payrolls', 'period'));
    }

    public function leaveBalance(Request $request)
    {
        $employees = Employee::with(['department', 'position'])
            ->where('is_active', true)
            ->where('employee_type', 'bulanan');

        if ($request->filled('department_id')) {
            $employees->where('department_id', $request->department_id);
        }

        $employees = $employees->get();

        $ct = LeaveType::whereIn('code', ['CT', 'CUTI'])->first(['id', 'max_days_per_year']);
        $dp = LeaveType::where('code', 'DP')->first(['id', 'max_days_per_year']);
        $ctId = $ct?->id;
        $dpId = $dp?->id;
        $ctQuota = $ct?->max_days_per_year ?? 12;
        $dpQuota = $dp?->max_days_per_year ?? 12;
        $currentYear = now()->year;

        $balances = $employees->map(function ($emp) use ($ctId, $dpId, $ctQuota, $dpQuota, $currentYear) {
            $usedCt = $ctId ? Leave::where('employee_id', $emp->id)
                ->where('leave_type_id', $ctId)
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->sum('total_days') : 0;

            $usedDp = $dpId ? Leave::where('employee_id', $emp->id)
                ->where('leave_type_id', $dpId)
                ->where('status', 'approved')
                ->whereYear('start_date', $currentYear)
                ->sum('total_days') : 0;

            return [
                'employee_id' => $emp->id,
                'nama' => $emp->full_name ?? '-',
                'jabatan' => $emp->position->name ?? $emp->department->name ?? '-',
                'ct_quota' => $ctQuota,
                'ct_used' => $usedCt,
                'ct_remaining' => max(0, $ctQuota - $usedCt),
                'dp_quota' => $dpQuota,
                'dp_used' => $usedDp,
                'dp_remaining' => max(0, $dpQuota - $usedDp),
            ];
        });

        $departments = Department::where('is_active', true)->get();

        return view('reports.leave-balance', compact('balances', 'departments'));
    }

    private function makeDummyAtt(string $dateStr, string $dayName, string $status): \stdClass
    {
        $dummy = new \stdClass();
        $dummy->attendance_date = $dateStr;
        $dummy->day_name = $dayName;
        $dummy->clock_in = null;
        $dummy->clock_out = null;
        $dummy->break_out = null;
        $dummy->break_in = null;
        $dummy->overtime_in = null;
        $dummy->overtime_out = null;
        $dummy->late_minutes = 0;
        $dummy->early_leave_minutes = 0;
        $dummy->excess_break_minutes = 0;
        $dummy->overtime_minutes = 0;
        $dummy->status = $status;
        return $dummy;
    }
}
