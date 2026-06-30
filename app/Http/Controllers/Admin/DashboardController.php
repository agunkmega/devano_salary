<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalEmployees = Employee::where('is_active', true)->count();

        $today = Carbon::today();

        $period = $request->input('period', 'this-month');
        $periodStart = match ($period) {
            'last-month' => (new Carbon('first day of last month'))->startOfDay(),
            'this-year' => Carbon::today()->startOfYear(),
            default => Carbon::today()->startOfMonth(),
        };
        $periodEnd = match ($period) {
            'last-month' => (new Carbon('last day of last month'))->endOfDay(),
            'this-year' => Carbon::today()->endOfYear(),
            default => Carbon::today()->endOfDay(),
        };

        $todayAttendance = Attendance::where('attendance_date', $today)
            ->where('status', 'hadir')
            ->count();

        $lateToday = Attendance::where('attendance_date', $today)
            ->where('status', 'terlambat')
            ->count();

        $onLeaveToday = Leave::where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->count();

        $absentToday = $totalEmployees - Attendance::where('attendance_date', $today)->count();

        $monthlyPayroll = Payroll::where('status', 'paid')
            ->whereBetween('period', [$periodStart->format('Y-m'), $periodEnd->format('Y-m')])
            ->sum('net_salary');

        $chartData = $this->getMonthlyChartData();

        $recentAttendances = Attendance::with('employee.user')
            ->latest()
            ->take(10)
            ->get();

        $cashAdvanceSummary = [
            'total_outstanding' => CashAdvance::where('status', 'approved')
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount'),
            'count' => CashAdvance::where('status', 'approved')
                ->where('remaining_amount', '>', 0)
                ->count(),
        ];

        $departmentStats = Department::withCount(['employees' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $todayBirthdays = Employee::where('is_active', true)
            ->whereRaw('DAYOFMONTH(birth_date) = ?', [$today->day])
            ->whereRaw('MONTH(birth_date) = ?', [$today->month])
            ->get(['id', 'full_name', 'birth_date', 'photo']);

        $thisMonthBirthdays = Employee::where('is_active', true)
            ->whereRaw('MONTH(birth_date) = ?', [$today->month])
            ->whereRaw('DAYOFMONTH(birth_date) >= ?', [$today->day])
            ->orderByRaw('DAYOFMONTH(birth_date)')
            ->take(10)
            ->get(['id', 'full_name', 'birth_date', 'photo']);

        return view('dashboard.index', compact(
            'totalEmployees',
            'todayAttendance',
            'lateToday',
            'onLeaveToday',
            'absentToday',
            'monthlyPayroll',
            'chartData',
            'recentAttendances',
            'cashAdvanceSummary',
            'departmentStats',
            'todayBirthdays',
            'thisMonthBirthdays'
        ));
    }

    private function getMonthlyChartData()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $attendances = Attendance::whereBetween('attendance_date', [$monthStart, $monthEnd])
                ->selectRaw("
                    COUNT(CASE WHEN status = 'hadir' THEN 1 END) as hadir,
                    COUNT(CASE WHEN status = 'terlambat' THEN 1 END) as terlambat,
                    COUNT(CASE WHEN status = 'izin' THEN 1 END) as izin,
                    COUNT(CASE WHEN status = 'sakit' THEN 1 END) as sakit,
                    COUNT(CASE WHEN status = 'cuti' THEN 1 END) as cuti,
                    COUNT(CASE WHEN status = 'alpha' THEN 1 END) as alpha
                ")
                ->first();

            $data[] = [
                'month' => $month->format('M Y'),
                'hadir' => (int) ($attendances->hadir ?? 0),
                'terlambat' => (int) ($attendances->terlambat ?? 0),
                'izin' => (int) ($attendances->izin ?? 0),
                'sakit' => (int) ($attendances->sakit ?? 0),
                'cuti' => (int) ($attendances->cuti ?? 0),
                'alpha' => (int) ($attendances->alpha ?? 0),
            ];
        }

        return $data;
    }
}
