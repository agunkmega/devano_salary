<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\NationalHoliday;
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
        $dailyChartData = $this->getDailyChartData();

        $calendarMonth = $request->input('cal_month');
        $calendarData = $this->getCalendarData($calendarMonth, $totalEmployees);

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
            'dailyChartData',
            'recentAttendances',
            'cashAdvanceSummary',
            'departmentStats',
            'todayBirthdays',
            'thisMonthBirthdays',
            'calendarData'
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

    private function getDailyChartData()
    {
        $data = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $att = Attendance::where('attendance_date', $day)
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
                'date' => $day->format('d M'),
                'hadir' => (int) ($att->hadir ?? 0),
                'terlambat' => (int) ($att->terlambat ?? 0),
                'izin' => (int) ($att->izin ?? 0),
                'sakit' => (int) ($att->sakit ?? 0),
                'cuti' => (int) ($att->cuti ?? 0),
                'alpha' => (int) ($att->alpha ?? 0),
            ];
        }

        return $data;
    }

    private function getCalendarData(?string $month, int $totalEmployees): array
    {
        $base = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::today();
        $monthStart = $base->copy()->startOfMonth()->startOfDay();
        $monthEnd = $base->copy()->endOfMonth()->endOfDay();

        $holidays = NationalHoliday::where('is_active', true)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get(['date', 'name', 'religion'])
            ->map(fn($h) => [
                'date' => $h->date->format('Y-m-d'),
                'name' => $h->name,
                'religion' => $h->religion,
            ]);

        $leaves = Leave::with(['employee', 'leaveType'])
            ->where('status', 'approved')
            ->where('start_date', '<=', $monthEnd)
            ->where('end_date', '>=', $monthStart)
            ->get();

        $leavesByDate = [];
        foreach ($leaves as $leave) {
            $start = max(Carbon::parse($leave->start_date), $monthStart);
            $end = min(Carbon::parse($leave->end_date), $monthEnd);
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $key = $d->format('Y-m-d');
                $leavesByDate[$key][] = [
                    'name' => $leave->employee?->full_name ?? '-',
                    'type' => $leave->leaveType?->name ?? 'Cuti',
                ];
            }
        }

        $attendance = Attendance::whereBetween('attendance_date', [$monthStart, $monthEnd])
            ->selectRaw("attendance_date,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'cuti' THEN 1 ELSE 0 END) as cuti,
                SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as alpha
            ")
            ->groupBy('attendance_date')
            ->get()
            ->keyBy(fn($a) => $a->attendance_date->format('Y-m-d'));

        return [
            'month' => $base->format('Y-m'),
            'month_label' => $base->translatedFormat('F Y'),
            'today' => Carbon::today()->format('Y-m-d'),
            'total_employees' => $totalEmployees,
            'holidays' => $holidays->keyBy('date')->toArray(),
            'leaves' => $leavesByDate,
            'attendance' => $attendance->map(function ($row) {
                $hadir = (int) $row->hadir + (int) $row->terlambat;
                return [
                    'hadir' => (int) $row->hadir,
                    'terlambat' => (int) $row->terlambat,
                    'izin' => (int) $row->izin,
                    'sakit' => (int) $row->sakit,
                    'cuti' => (int) $row->cuti,
                    'alpha' => (int) $row->alpha,
                    'total_hadir' => $hadir,
                ];
            })->toArray(),
        ];
    }
}
