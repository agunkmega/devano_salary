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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $employee = Employee::with(['department', 'position', 'shift', 'station'])->findOrFail(session('portal_employee_id'));

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

        $totalHadir = $attendances->where('status', 'hadir')->count();
        $totalTerlambat = $attendances->where('status', 'terlambat')->count();
        $totalIzin = $attendances->where('status', 'izin')->count();
        $totalSakit = $attendances->where('status', 'sakit')->count();
        $totalCuti = $attendances->where('status', 'cuti')->count();

        $attendanceDays = $attendances->whereNotNull('clock_in')->count();
        $paidLeaveDays = \App\Models\Leave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', fn($q) => $q->where('is_paid', true))
            ->whereDate('start_date', '<=', $dateTo)
            ->whereDate('end_date', '>=', $dateFrom)
            ->get()
            ->flatMap(fn($leave) => \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date)->toArray())
            ->filter(fn($date) => $date->between($dateFrom, $dateTo) && $date->dayOfWeek !== Carbon::SUNDAY)
            ->unique()
            ->count();

        $effectiveAttendanceDays = $attendanceDays + $paidLeaveDays;

        $holidaysByDate = \App\Models\NationalHoliday::where('is_active', true)
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->get(['date', 'religion'])
            ->groupBy(fn($h) => $h->date->format('Y-m-d'))
            ->map(fn($items) => $items->pluck('religion')->toArray())
            ->toArray();

        $offDays = $employee->off_days ?? ['sunday'];

        $totalWorkingDays = 0;
        $cursor = $dateFrom->copy()->startOfDay();
        while ($cursor->lte($dateTo)) {
            $dateStr = $cursor->format('Y-m-d');
            $isHoliday = false;
            if (isset($holidaysByDate[$dateStr])) {
                $empReligion = $employee->religion;
                $isHoliday = collect($holidaysByDate[$dateStr])->contains(fn($religion) => empty($religion) || $religion === $empReligion);
            }
            if (!in_array(strtolower($cursor->format('l')), $offDays) && !$isHoliday) {
                $totalWorkingDays++;
            }
            $cursor->addDay();
        }

        $alpha = max(0, $totalWorkingDays - $effectiveAttendanceDays);

        $attendanceSummary = [
            'hadir' => $totalHadir,
            'terlambat' => $totalTerlambat,
            'izin' => $totalIzin,
            'sakit' => $totalSakit,
            'cuti' => $totalCuti,
            'alpha' => $alpha,
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

        $today = Carbon::today();
        $isBirthday = $employee->birth_date
            && $today->day === (int) Carbon::parse($employee->birth_date)->format('d')
            && $today->month === (int) Carbon::parse($employee->birth_date)->format('m');

        return view('portal.dashboard', compact(
            'employee', 'attendanceSummary', 'latestPayroll',
            'recentLeaves', 'leaveTypes', 'cashAdvances', 'dateFrom', 'dateTo',
            'payrolls', 'selectedPeriod', 'leaveBalances', 'isBirthday'
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

    public function changePassword()
    {
        return view('portal.password');
    }

    public function updatePassword(Request $request)
    {
        $employee = Employee::findOrFail(session('portal_employee_id'));

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if (!$employee->password && $employee->birth_date) {
            $birthDate = \Carbon\Carbon::parse($employee->birth_date)->format('Y-m-d');
            if ($request->current_password !== $birthDate) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai'])->withInput();
            }
        } else {
            if (!Hash::check($request->current_password, $employee->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak sesuai'])->withInput();
            }
        }

        $employee->update(['password' => Hash::make($request->password)]);

        return redirect()->route('portal.dashboard')->with('success', 'Password berhasil diubah.');
    }
}
