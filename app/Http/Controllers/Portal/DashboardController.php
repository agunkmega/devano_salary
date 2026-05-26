<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\CashAdvance;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = Employee::with(['department', 'position', 'shift'])->findOrFail(session('portal_employee_id'));

        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', '>=', $monthStart)
            ->whereDate('attendance_date', '<=', $monthEnd)
            ->get();

        $attendanceSummary = [
            'hadir' => $attendances->where('status', 'hadir')->count(),
            'terlambat' => $attendances->where('status', 'terlambat')->count(),
            'izin' => $attendances->where('status', 'izin')->count(),
            'sakit' => $attendances->where('status', 'sakit')->count(),
            'cuti' => $attendances->where('status', 'cuti')->count(),
            'alpha' => $attendances->where('status', 'alpha')->count(),
            'total' => $attendances->count(),
        ];

        $latestPayroll = Payroll::where('employee_id', $employee->id)
            ->latest()
            ->first();

        $recentLeaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get();

        $cashAdvances = CashAdvance::where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        return view('portal.dashboard', compact(
            'employee', 'attendanceSummary', 'latestPayroll',
            'recentLeaves', 'leaveTypes', 'cashAdvances'
        ));
    }
}
