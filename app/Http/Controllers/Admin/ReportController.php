<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Payroll;
use App\Models\Position;
use App\Models\Station;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\LeaveBalanceService;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

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
            ->when(request('position_id'), function ($q, $posId) {
                $q->whereHas('employee', function ($sub) use ($posId) {
                    $sub->where('position_id', $posId);
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
            $emp = $rows->first()?->employee;
            $hadir = $rows->where('status', 'hadir')->count();
            $terlambat = $rows->where('status', 'terlambat')->count();
            $totalHadir = $hadir + $terlambat;

            $totalHari = $periodWorkDays > 0 ? $periodWorkDays : $totalHadir;
            $persentase = $emp?->employee_type === 'bulanan'
                ? round(($totalHadir + $sundayCount) / ($totalHari + $sundayCount) * 100, 1)
                : null;

            return [
                'employee_id' => $empId,
                'employee_type' => $emp->employee_type ?? 'bulanan',
                'nama' => $emp->full_name ?? '-',
                'jabatan' => $emp?->position?->name ?? $emp?->department?->name ?? '-',
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
        $positions = Position::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();

        return view('reports.attendance', compact('employeeSummaries', 'departments', 'positions', 'employees', 'summary', 'periodWorkDays'));
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
            ->when($request->filled('position_id'), function ($q) use ($request) {
                $q->whereHas('employee', fn($sub) => $sub->where('position_id', $request->position_id));
            })
            ->when($request->filled('employee_id'), function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            })
            ->when($request->filled('employee'), function ($q) use ($request) {
                $q->whereHas('employee', fn($sub) => $sub->where('full_name', 'like', '%' . $request->employee . '%'));
            })
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            });

        $attendances = $query->orderBy('attendance_date')->orderBy('employee_id')->get();

        $attendanceNotes = $attendances->filter(fn($a) => !empty($a->admin_note))
            ->groupBy('employee_id')
            ->map(function ($rows) {
                return $rows->map(function ($a) {
                    return [
                        'date' => \Carbon\Carbon::parse($a->attendance_date)->format('d/m/Y'),
                        'note' => $a->admin_note,
                        'editor' => $a->editor?->name ?? 'Admin',
                    ];
                })->values();
            });

        $leaveEmpQuery = \App\Models\Leave::where('status', 'approved')
            ->whereDate('end_date', '>=', $request->date_from ?? $attendances->min('attendance_date'))
            ->whereDate('start_date', '<=', $request->date_to ?? $attendances->max('attendance_date'));
        if ($request->filled('department_id')) {
            $leaveEmpQuery->whereHas('employee', fn($q) => $q->where('department_id', $request->department_id));
        }
        if ($request->filled('position_id')) {
            $leaveEmpQuery->whereHas('employee', fn($q) => $q->where('position_id', $request->position_id));
        }
        if ($request->filled('employee_id')) {
            $leaveEmpQuery->where('employee_id', $request->employee_id);
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

        $leaves = \App\Models\Leave::with('leaveType')->whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->whereDate('end_date', '>=', $dateFrom)
            ->whereDate('start_date', '<=', $dateTo)
            ->get(['employee_id', 'leave_type_id', 'start_date', 'end_date']);

        $leaveDateMap = [];
        foreach ($leaves as $leave) {
            $s = Carbon::parse($leave->start_date);
            $e = Carbon::parse($leave->end_date);
            $statusCode = strtoupper($leave->leaveType?->code ?? '');
            while ($s->lte($e)) {
                $leaveDateMap[$leave->employee_id][$s->format('Y-m-d')] = [
                    'status' => $this->mapLeaveTypeToStatus($statusCode),
                    'leave_type_name' => $leave->leaveType?->name,
                ];
                $s->addDay();
            }
        }

        $holidaysByDate = \App\Models\NationalHoliday::where('is_active', true)
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->get(['date', 'name', 'religion'])
            ->groupBy(fn($h) => $h->date->format('Y-m-d'))
            ->toArray();

        $grouped = $attendances->groupBy('employee_id')->map(function ($rows, $empId) use ($allEmployees, $dateFrom, $dateTo, $holidayDates, $leaveDateMap, $holidaysByDate, $leaves) {
            $emp = $allEmployees->get($empId);
            $attByDate = $rows->keyBy(fn($r) => $r->attendance_date->format('Y-m-d'));
            $fullRows = [];
            $cursor = $dateFrom->copy();
            while ($cursor->lte($dateTo)) {
                $dateStr = $cursor->format('Y-m-d');
                $dayName = $cursor->locale('id')->dayName;
                if (isset($attByDate[$dateStr])) {
                    $att = $attByDate[$dateStr];
                    $this->attendanceService->recalculateAttendance($att);
                    $att->day_name = $dayName;

                    $attStatus = ucfirst($att->status);

                    $manualStatuses = ['Izin', 'Sakit'];
                    if (!in_array($attStatus, $manualStatuses)) {
                        $employeeLeaves = $leaves->where('employee_id', $empId);
                        foreach ($employeeLeaves as $leave) {
                            if ($cursor->between($leave->start_date, $leave->end_date)) {
                                $statusCode = strtoupper($leave->leaveType?->code ?? '');
                                $attStatus = $this->mapLeaveTypeToStatus($statusCode);
                                $att->leave_type_name = $leave->leaveType?->name;
                                break;
                            }
                        }
                    }

                    $att->status = strtolower($attStatus);
                    $fullRows[] = $att;
                } elseif (isset($leaveDateMap[$empId][$dateStr])) {
                    $leaveInfo = $leaveDateMap[$empId][$dateStr];
                    $dummy = $this->makeDummyAtt($dateStr, $dayName, strtolower($leaveInfo['status']));
                    $dummy->leave_type_name = $leaveInfo['leave_type_name'];
                    $fullRows[] = $dummy;
                } elseif ($cursor->dayOfWeek === Carbon::SUNDAY || $this->isHolidayForEmployee($dateStr, $emp, $holidaysByDate)) {
                    $dummy = $this->makeDummyAtt($dateStr, $dayName, 'libur');
                    $dummy->holiday_name = $this->holidayNameForEmployee($dateStr, $emp, $holidaysByDate);
                    $fullRows[] = $dummy;
                } else {
                    $dayLower = strtolower($cursor->format('l'));
                    $offDays = $emp->off_days ?? ['sunday'];
                    if (in_array($dayLower, $offDays)) {
                        $dummy = $this->makeDummyAtt($dateStr, $dayName, 'libur');
                    } else {
                        $dummy = $this->makeDummyAtt($dateStr, $dayName, 'alpha');
                    }
                    $fullRows[] = $dummy;
                }
                $cursor->addDay();
            }
            return [
                'employee_id' => $empId,
                'nama' => $emp?->full_name ?? '-',
                'jabatan' => $emp?->position?->name ?? $emp?->department?->name ?? '-',
                'rows' => collect($fullRows),
            ];
        })->values();

        $employees = $grouped;

        return view('reports.attendance-print', compact('employees', 'dateFrom', 'dateTo', 'attendanceNotes'));
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
            ->when(request('position_id'), function ($q, $posId) {
                $q->whereHas('employee', function ($sub) use ($posId) {
                    $sub->where('position_id', $posId);
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
            ->when(request('position_id'), function ($q, $posId) {
                $q->whereHas('employee', function ($sub) use ($posId) {
                    $sub->where('position_id', $posId);
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
                $q->where('employee_type', $type);
            })
            ->when(request('station_id'), function ($q, $stationId) {
                $q->whereHas('employee', function ($sub) use ($stationId) {
                    $sub->where('station_id', $stationId);
                });
            })
            ->when(request('position_grade'), function ($q, $grade) {
                $q->whereHas('employee', function ($sub) use ($grade) {
                    $sub->where('position_grade', $grade);
                });
            })
            ->when(request('bank_name'), function ($q, $bank) {
                if ($bank === 'cash') {
                    $q->whereHas('employee', function ($sub) {
                        $sub->whereNull('bank_account')->orWhere('bank_account', '');
                    });
                } else {
                    $q->whereHas('employee', function ($sub) use ($bank) {
                        $sub->where('bank_name', $bank);
                    });
                }
            })
            ->latest();

        $all = $query->get();

        $cashAll = $all->filter(fn($p) => empty($p->employee?->bank_account));

        $bpjsKetFull = $all->filter(fn($p) => ($p->employee?->bpjs_ketenagakerjaan_type ?? null) !== 'partial');
        $bpjsKetPartial = $all->filter(fn($p) => ($p->employee?->bpjs_ketenagakerjaan_type ?? null) === 'partial');

        $summary = [
            'count' => $all->count(),
            'total_base_salary' => $all->sum('base_salary'),
            'total_allowance' => $all->sum('allowance'),
            'total_overtime' => $all->sum('overtime_pay'),
            'total_deductions' => $all->sum('total_deductions'),
            'total_net_salary' => $all->sum('net_salary'),
            'total_gaji' => $all->sum('net_salary') + $all->sum('iuran_bulanan_deduction'),
            'total_bpjs' => $all->sum('bpjs_deduction'),
            'total_bpjs_kesehatan' => $all->sum('bpjs_kesehatan_deduction'),
            'total_bpjs_kesehatan_company' => $all->sum('bpjs_kesehatan_company'),
            'total_bpjs_ketenagakerjaan' => $all->sum('bpjs_ketenagakerjaan_deduction'),
            'total_bpjs_ketenagakerjaan_company' => $all->sum('bpjs_ketenagakerjaan_company'),
            'total_bpjs_ketenagakerjaan_full' => $bpjsKetFull->sum('bpjs_ketenagakerjaan_deduction'),
            'total_bpjs_ketenagakerjaan_full_company' => $bpjsKetFull->sum('bpjs_ketenagakerjaan_company'),
            'total_bpjs_ketenagakerjaan_partial' => $bpjsKetPartial->sum('bpjs_ketenagakerjaan_deduction'),
            'total_bpjs_ketenagakerjaan_partial_company' => $bpjsKetPartial->sum('bpjs_ketenagakerjaan_company'),
            'total_iuran_bulanan' => $all->sum('iuran_bulanan_deduction'),
            'total_tax' => $all->sum('tax_amount'),
            'total_cash_advance' => $all->sum('cash_advance_deduction'),
            'cash_count' => $cashAll->count(),
            'cash_total_net_salary' => $cashAll->sum('net_salary'),
            'cash_total_gaji' => $cashAll->sum('net_salary') + $cashAll->sum('iuran_bulanan_deduction'),
        ];

        $payrolls = $query->paginate(50);
        $periods = Payroll::select('period')->distinct()->orderBy('period', 'desc')->pluck('period');
        $departments = Department::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();
        $stations = Station::where('is_active', true)->get();
        $banks = Employee::whereNotNull('bank_name')->where('bank_name', '!=', '')->distinct()->orderBy('bank_name')->pluck('bank_name');
        $grades = Employee::whereNotNull('position_grade')->where('position_grade', '!=', '')->distinct()->orderBy('position_grade')->pluck('position_grade');

        return view('reports.payroll', compact('payrolls', 'periods', 'departments', 'employees', 'stations', 'banks', 'grades', 'summary'));
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
            $query->where('employee_type', $request->employee_type);
        }
        if ($request->filled('station_id')) {
            $query->whereHas('employee', fn($q) => $q->where('station_id', $request->station_id));
        }
        if ($request->filled('position_grade')) {
            $query->whereHas('employee', fn($q) => $q->where('position_grade', $request->position_grade));
        }
        if ($request->filled('bank_name')) {
            if ($request->bank_name === 'cash') {
                $query->whereHas('employee', fn($q) => $q->whereNull('bank_account')->orWhere('bank_account', ''));
            } else {
                $query->whereHas('employee', fn($q) => $q->where('bank_name', $request->bank_name));
            }
        }

        $payrolls = $query->orderBy('period', 'desc')->get();
        $period = $request->period;
        $stationName = $request->filled('station_id') ? Station::find($request->station_id)?->name : null;

        return view('reports.payroll-print', compact('payrolls', 'period', 'stationName'));
    }

    public function payrollExcel(Request $request)
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
            $query->where('employee_type', $request->employee_type);
        }
        if ($request->filled('station_id')) {
            $query->whereHas('employee', fn($q) => $q->where('station_id', $request->station_id));
        }
        if ($request->filled('position_grade')) {
            $query->whereHas('employee', fn($q) => $q->where('position_grade', $request->position_grade));
        }
        if ($request->filled('bank_name')) {
            if ($request->bank_name === 'cash') {
                $query->whereHas('employee', fn($q) => $q->whereNull('bank_account')->orWhere('bank_account', ''));
            } else {
                $query->whereHas('employee', fn($q) => $q->where('bank_name', $request->bank_name));
            }
        }

        $payrolls = $query->orderBy('period', 'desc')->get();

        $grouped = $payrolls->groupBy(fn($p) => $p->employee->bank_name ?? 'Cash (Tanpa Rekening)');

        $rows = collect();
        foreach ($grouped as $bank => $items) {
            $no = 1;
            $total = 0;
            foreach ($items as $p) {
                $total += $p->net_salary;
                $rows->push([
                    'No' => $no++,
                    'Nama' => $p->employee->full_name ?? '-',
                    'No. KTP' => $p->employee->identity_number ?? '-',
                    'Jenis' => ($p->employee_type ?? 'bulanan') === 'harian' ? 'Harian' : 'Bulanan',
                    'Bank' => $p->employee->bank_name ?? 'Cash',
                    'No. Rekening' => $p->employee->bank_account ?? '-',
                    'Nama Rekening' => $p->employee->bank_holder ?? '-',
                    'Gaji Bersih' => (float) $p->net_salary,
                ]);
            }
            $rows->push([
                'No' => '',
                'Nama' => '',
                'No. KTP' => '',
                'Jenis' => '',
                'Bank' => '',
                'No. Rekening' => '',
                'Nama Rekening' => 'Total ' . $bank,
                'Gaji Bersih' => (float) $total,
            ]);
        }

        $headings = ['No', 'Nama', 'No. KTP', 'Jenis', 'Bank', 'No. Rekening', 'Nama Rekening', 'Gaji Bersih'];

        return Excel::download(new class($rows, $headings) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;
            private $headings;
            public function __construct($data, $headings) { $this->data = $data; $this->headings = $headings; }
            public function array(): array { return $this->data->toArray(); }
            public function headings(): array { return $this->headings; }
        }, 'payroll-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function payrollPrintDetail(Request $request)
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
            $query->where('employee_type', $request->employee_type);
        }
        if ($request->filled('station_id')) {
            $query->whereHas('employee', fn($q) => $q->where('station_id', $request->station_id));
        }
        if ($request->filled('position_grade')) {
            $query->whereHas('employee', fn($q) => $q->where('position_grade', $request->position_grade));
        }
        if ($request->filled('bank_name')) {
            if ($request->bank_name === 'cash') {
                $query->whereHas('employee', fn($q) => $q->whereNull('bank_account')->orWhere('bank_account', ''));
            } else {
                $query->whereHas('employee', fn($q) => $q->where('bank_name', $request->bank_name));
            }
        }

        $payrolls = $query->orderBy('period', 'desc')->get();
        $period = $request->period;
        $stationName = $request->filled('station_id') ? Station::find($request->station_id)?->name : null;

        return view('reports.payroll-print-detail', compact('payrolls', 'period', 'stationName'));
    }

    public function payrollExcelDetail(Request $request)
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
            $query->where('employee_type', $request->employee_type);
        }
        if ($request->filled('station_id')) {
            $query->whereHas('employee', fn($q) => $q->where('station_id', $request->station_id));
        }
        if ($request->filled('position_grade')) {
            $query->whereHas('employee', fn($q) => $q->where('position_grade', $request->position_grade));
        }
        if ($request->filled('bank_name')) {
            if ($request->bank_name === 'cash') {
                $query->whereHas('employee', fn($q) => $q->whereNull('bank_account')->orWhere('bank_account', ''));
            } else {
                $query->whereHas('employee', fn($q) => $q->where('bank_name', $request->bank_name));
            }
        }

        $payrolls = $query->orderBy('period', 'desc')->get();

        $headings = ['No', 'Nama', 'Jabatan', 'Jenis', 'Gaji Pokok', 'Tunjangan', 'Lembur', 'Uang Makan', 'Telat', 'Potongan 8%', 'Alpha', 'Kasbon Tunai', 'Kasbon Non-Tunai', 'Pajak', 'Lain-Lain', 'BPJS Kes (Kr)', 'BPJS Kes (Pr)', 'BPJS Ket. Full (Kr)', 'BPJS Ket. Full (Pr)', 'BPJS Ket. Part. (Kr)', 'BPJS Ket. Part. (Pr)', 'Total Gaji', 'Iuran', 'Gaji Bersih', 'Status'];

        $data = collect();

        foreach ($payrolls as $i => $p) {
            $gajiKotor = $p->net_salary + $p->iuran_bulanan_deduction;
            $isPartial = ($p->employee?->bpjs_ketenagakerjaan_type ?? null) === 'partial';
            $data->push([
                $i + 1,
                $p->employee?->full_name ?? '-',
                $p->employee?->position?->name ?? $p->employee?->department?->name ?? '-',
                ($p->employee_type ?? 'bulanan') === 'harian' ? 'Harian' : 'Bulanan',
                $p->base_salary,
                $p->allowance,
                $p->overtime_pay,
                $p->uang_makan_lembur + $p->uang_makan_harian,
                $p->late_penalty,
                $p->late_penalty_percent,
                $p->absent_penalty,
                $p->cash_advance_tunai,
                $p->cash_advance_nontunai,
                $p->tax_amount,
                $p->other_deductions,
                $p->bpjs_kesehatan_deduction,
                $p->bpjs_kesehatan_company,
                $isPartial ? 0 : $p->bpjs_ketenagakerjaan_deduction,
                $isPartial ? 0 : $p->bpjs_ketenagakerjaan_company,
                $isPartial ? $p->bpjs_ketenagakerjaan_deduction : 0,
                $isPartial ? $p->bpjs_ketenagakerjaan_company : 0,
                $gajiKotor,
                $p->iuran_bulanan_deduction,
                $p->net_salary,
                ucfirst($p->status),
            ]);
        }

        if ($data->isNotEmpty()) {
            $sums = array_fill(0, count($headings), 0);
            foreach ($data as $row) {
                foreach (range(4, 23) as $k) {
                    $sums[$k] += $row[$k];
                }
            }
            $totalRow = array_fill(0, count($headings), '');
            $totalRow[1] = 'Total';
            foreach (range(4, 23) as $k) {
                $totalRow[$k] = $sums[$k];
            }
            $data->push($totalRow);
        }

        return Excel::download(new class($data, $headings) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;
            private $headings;
            public function __construct($data, $headings) { $this->data = $data; $this->headings = $headings; }
            public function array(): array { return $this->data->toArray(); }
            public function headings(): array { return $this->headings; }
        }, 'payroll-detail-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function leaveBalance(Request $request)
    {
        $employees = Employee::with(['department', 'position'])
            ->where('is_active', true)
            ->where('employee_type', 'bulanan');

        if ($request->filled('department_id')) {
            $employees->where('department_id', $request->department_id);
        }

        if ($request->filled('employee')) {
            $employees->where('full_name', 'like', '%' . $request->employee . '%');
        }

        $employees = $employees->get();

        [$ctId, $dpId, $ctQuota] = LeaveBalanceService::typeConfig();

        [$leaveYearStart, $leaveYearEnd, $leaveYearLabel] = LeaveBalanceService::leaveYear();

        $balances = $employees->map(
            fn ($emp) => LeaveBalanceService::forEmployee($emp, $leaveYearStart, $leaveYearEnd, $ctId, $dpId, $ctQuota)
        );

        $departments = Department::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->where('employee_type', 'bulanan')->get(['id', 'full_name']);

        return view('reports.leave-balance', compact('balances', 'departments', 'employees', 'leaveYearLabel'));
    }

    public function bpjs()
    {
        $query = $this->bpjsQuery();

        $payrolls = $query->paginate(100);
        $periods = Payroll::select('period')->distinct()->orderBy('period', 'desc')->pluck('period');
        $departments = Department::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();
        $stations = Station::where('is_active', true)->get();

        return view('reports.bpjs', compact('payrolls', 'periods', 'departments', 'employees', 'stations'));
    }

    public function bpjsPrint()
    {
        $payrolls = $this->bpjsQuery()->get();
        return view('reports.bpjs-print', compact('payrolls'));
    }

    public function bpjsPdf()
    {
        $payrolls = $this->bpjsQuery()->get();
        $pdf = Pdf::loadView('reports.bpjs-print', compact('payrolls'));
        return $pdf->download('laporan-bpjs-' . now()->format('Ymd') . '.pdf');
    }

    private function bpjsQuery()
    {
        return Payroll::with(['employee.position', 'employee.department'])
            ->select([
                'payrolls.*',
                'employees.identity_number',
                'employees.full_name',
            ])
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->where(function ($q) {
                $q->where('payrolls.bpjs_kesehatan_deduction', '>', 0)
                  ->orWhere('payrolls.bpjs_kesehatan_company', '>', 0)
                  ->orWhere('payrolls.bpjs_ketenagakerjaan_deduction', '>', 0)
                  ->orWhere('payrolls.bpjs_ketenagakerjaan_company', '>', 0);
            })
            ->when(request('period'), function ($q, $period) {
                $q->where('payrolls.period', $period);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->where('employees.department_id', $deptId);
            })
            ->when(request('employee_id'), function ($q, $empId) {
                $q->where('payrolls.employee_id', $empId);
            })
            ->when(request('bpjs_ket_type'), function ($q, $type) {
                $q->where('employees.bpjs_ketenagakerjaan_type', $type);
            })
            ->when(request('station_id'), function ($q, $stationId) {
                $q->where('employees.station_id', $stationId);
            })
            ->orderBy('employees.full_name');
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
        $dummy->ignore_late = false;
        $dummy->ignore_early_leave = false;
        $dummy->ignore_excess_break = false;
        $dummy->leave_type_name = null;
        $dummy->status = $status;
        return $dummy;
    }

    private function isHolidayForEmployee(string $dateStr, ?\App\Models\Employee $emp, array $holidaysByDate): bool
    {
        return $this->holidayNameForEmployee($dateStr, $emp, $holidaysByDate) !== null;
    }

    private function holidayNameForEmployee(string $dateStr, ?\App\Models\Employee $emp, array $holidaysByDate): ?string
    {
        if (!isset($holidaysByDate[$dateStr])) {
            return null;
        }
        $empReligion = $emp?->religion;
        foreach ($holidaysByDate[$dateStr] as $holiday) {
            $religion = $holiday['religion'] ?? null;
            if (empty($religion) || $religion === $empReligion) {
                return $holiday['name'] ?? null;
            }
        }
        return null;
    }

    private function mapLeaveTypeToStatus(string $code): string
    {
        if (str_starts_with($code, 'IJ') || $code === 'IML') {
            return 'Izin';
        }
        if (str_starts_with($code, 'SK')) {
            return 'Sakit';
        }
        if ($code === 'ALFA') {
            return 'Alpha';
        }
        if ($code === 'PH') {
            return 'Libur';
        }
        return 'Cuti';
    }
}
