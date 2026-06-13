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
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $employee = Employee::with(['department', 'position', 'shift'])->findOrFail(session('portal_employee_id'));

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->orderBy('period', 'desc')
            ->get();

        $selectedPeriod = $request->get('period');

        if ($selectedPeriod) {
            $latestPayroll = $payrolls->firstWhere('period', $selectedPeriod);
        } else {
            $latestPayroll = $payrolls->first();
        }

        if ($latestPayroll) {
            [$year, $month] = explode('-', $latestPayroll->period);
            $month = (int) $month;
            $year = (int) $year;
            $prevMonth = $month > 1 ? $month - 1 : 12;
            $prevYear = $month > 1 ? $year : $year - 1;
            $dateFrom = Carbon::create($prevYear, $prevMonth, 26)->startOfDay();
            $dateTo = Carbon::create($year, $month, 25)->endOfDay();
        } else {
            $latestPayroll = null;
            $dateFrom = now()->startOfMonth();
            $dateTo = now()->endOfMonth();
        }

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', '>=', $dateFrom)
            ->whereDate('attendance_date', '<=', $dateTo)
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

        $recentLeaves = Leave::with('leaveType')
            ->where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->get();

        $leaveBalances = \App\Models\LeaveType::where('is_active', true)
            ->whereNotNull('max_days_per_year')
            ->get()
            ->map(function ($lt) use ($employee) {
                $used = Leave::where('employee_id', $employee->id)
                    ->where('leave_type_id', $lt->id)
                    ->where('status', 'approved')
                    ->get()
                    ->sum(fn($l) => $l->start_date->diffInDays($l->end_date) + 1);
                return (object) [
                    'name' => $lt->name,
                    'code' => $lt->code,
                    'max' => $lt->max_days_per_year,
                    'used' => $used,
                    'remaining' => max(0, $lt->max_days_per_year - $used),
                ];
            });

        $cashAdvances = CashAdvance::where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        return view('portal.dashboard', compact(
            'employee', 'attendanceSummary', 'latestPayroll',
            'recentLeaves', 'leaveTypes', 'cashAdvances', 'dateFrom', 'dateTo',
            'payrolls', 'selectedPeriod', 'leaveBalances'
        ));
    }

    public function updatePhoto(Request $request)
    {
        $employee = Employee::findOrFail(session('portal_employee_id'));

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $path = $request->file('photo')->store('employee-photos', 'public');
        $employee->update(['photo' => $path]);

        return back()->with('success', 'Foto berhasil diperbarui.');
    }
}
