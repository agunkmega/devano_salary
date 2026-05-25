<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function attendance()
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
            ->when(request('employee_id'), function ($q, $empId) {
                $q->where('employee_id', $empId);
            })
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(50);

        $departments = Department::where('is_active', true)->get();
        $employees = Employee::where('is_active', true)->get();

        $summary = [
            'total' => $attendances->total(),
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'cuti' => $attendances->where('status', 'cuti')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
        ];

        return view('reports.attendance', compact('attendances', 'departments', 'employees', 'summary'));
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
        $leaves = Leave::with(['employee.user', 'employee.department', 'leaveType', 'approver'])
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
            })
            ->latest()
            ->paginate(50);

        $departments = Department::where('is_active', true)->get();

        $summary = [
            'pending' => $leaves->where('status', 'pending')->count(),
            'approved' => $leaves->where('status', 'approved')->count(),
            'rejected' => $leaves->where('status', 'rejected')->count(),
            'cancelled' => $leaves->where('status', 'cancelled')->count(),
            'total_days' => $leaves->sum('total_days'),
        ];

        return view('reports.leave', compact('leaves', 'departments', 'summary'));
    }

    public function payroll()
    {
        $payrolls = Payroll::with(['employee.user', 'employee.department'])
            ->when(request('period'), function ($q, $period) {
                $q->where('period', $period);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->latest()
            ->paginate(50);

        $periods = Payroll::select('period')->distinct()->orderBy('period', 'desc')->pluck('period');

        $summary = [
            'total_base_salary' => $payrolls->sum('base_salary'),
            'total_allowance' => $payrolls->sum('allowance'),
            'total_overtime' => $payrolls->sum('overtime_pay'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_net_salary' => $payrolls->sum('net_salary'),
            'count' => $payrolls->total(),
        ];

        return view('reports.payroll', compact('payrolls', 'periods', 'summary'));
    }
}
