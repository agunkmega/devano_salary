<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\CashAdvance;
use App\Models\Announcement;
use App\Models\Payroll;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
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

        $now = now();
        if ($now->month === 12 && $now->day >= 26) {
            $leaveYearStart = \Carbon\Carbon::create($now->year, 12, 26)->startOfDay();
            $leaveYearEnd = \Carbon\Carbon::create($now->year + 1, 12, 25)->endOfDay();
            $leaveYearLabel = $now->year . '/' . ($now->year + 1);
        } else {
            $leaveYearStart = \Carbon\Carbon::create($now->year - 1, 12, 26)->startOfDay();
            $leaveYearEnd = \Carbon\Carbon::create($now->year, 12, 25)->endOfDay();
            $leaveYearLabel = ($now->year - 1) . '/' . $now->year;
        }

        $tenureDays = $employee->join_date ? $employee->join_date->diffInDays(now()) : 0;
        $eligible = $employee->cuti_eligible && $tenureDays >= 365;

        $effectiveCtQuota = 0;
        if ($eligible && $employee->join_date) {
            $anniversary = $employee->join_date->copy()->addYear();
            if ($anniversary->lte($leaveYearStart)) {
                $effectiveCtQuota = 12;
            } elseif ($anniversary->gt($leaveYearEnd)) {
                $effectiveCtQuota = 0;
            } else {
                $effectiveCtQuota = min(12, ($leaveYearEnd->year - $anniversary->year) * 12 + ($leaveYearEnd->month - $anniversary->month) + 1);
            }
        }

        $leaveBalances = \App\Models\LeaveType::where('is_active', true)
            ->whereNotNull('max_days_per_year')
            ->whereIn('code', ['CT', 'CUTI', 'DP', 'PH'])
            ->get()
            ->map(function ($lt) use ($employee, $eligible, $effectiveCtQuota, $leaveYearStart, $leaveYearEnd) {
                $isCutiTahunan = in_array($lt->code, ['CT', 'CUTI']);
                $quota = $eligible ? ($isCutiTahunan ? $effectiveCtQuota : $lt->max_days_per_year) : 0;
                if ($lt->code === 'DP') {
                    return (object) [
                        'name' => $lt->name,
                        'code' => $lt->code,
                        'max' => $employee->dp_granted,
                        'used' => $employee->dp_used,
                        'remaining' => $employee->dp_remaining,
                    ];
                }
                $used = Leave::where('employee_id', $employee->id)
                    ->where('leave_type_id', $lt->id)
                    ->where('status', 'approved')
                    ->whereBetween('start_date', [$leaveYearStart, $leaveYearEnd])
                    ->get()
                    ->sum(fn($l) => $l->start_date->diffInDays($l->end_date) + 1);
                return (object) [
                    'name' => $lt->name,
                    'code' => $lt->code,
                    'max' => $quota,
                    'used' => $used,
                    'remaining' => max(0, $quota - $used),
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

        $deptHeadDept = Department::where('department_head_id', $employee->id)->first();
        $pendingHeadCount = 0;
        if ($deptHeadDept) {
            $pendingHeadCount = Leave::where('status', 'pending')
                ->where('employee_id', '!=', $employee->id)
                ->whereHas('employee', fn($q) => $q->where('department_id', $deptHeadDept->id))
                ->count();
        }

        $announcements = Announcement::active()
            ->latest()
            ->take(5)
            ->get();

        return view('portal.dashboard', compact(
            'employee', 'attendanceSummary', 'latestPayroll',
            'recentLeaves', 'leaveTypes', 'cashAdvances', 'dateFrom', 'dateTo',
            'payrolls', 'selectedPeriod', 'leaveBalances', 'isBirthday',
            'pendingHeadCount', 'leaveYearLabel', 'announcements'
        ));
    }

    public function slipPdf($id)
    {
        $employee = Employee::findOrFail(session('portal_employee_id'));

        $payroll = Payroll::with(['employee.user', 'employee.department', 'employee.position'])
            ->where('employee_id', $employee->id)
            ->findOrFail($id);

        if (!in_array($payroll->status, ['approved', 'paid'])) {
            return back()->withErrors('Slip gaji periode ini belum disetujui sehingga belum dapat diunduh.');
        }

        $companySettings = Setting::where('group', 'company')->get()->keyBy('key');
        $companyName = $companySettings->get('company_name')?->value ?? 'PT. DEVANO SILVER INDONESIA';
        $companyAddress = $companySettings->get('company_address')?->value ?? '';

        $pdf = Pdf::loadView('payroll.slip', compact('payroll', 'companyName', 'companyAddress'));

        return $pdf->download("slip-gaji-{$payroll->employee->nik}-{$payroll->period}.pdf");
    }

    public function attendanceHistory(Request $request)
    {
        $employee = Employee::findOrFail(session('portal_employee_id'));

        $query = Attendance::with('shift')
            ->where('employee_id', $employee->id)
            ->orderBy('attendance_date', 'desc');

        if ($request->filled('period')) {
            [$year, $month] = explode('-', $request->period);
            $month = (int) $month;
            $year = (int) $year;
            $prevMonth = $month > 1 ? $month - 1 : 12;
            $prevYear = $month > 1 ? $year : $year - 1;
            $dateFrom = Carbon::create($prevYear, $prevMonth, 26)->startOfDay();
            $dateTo = Carbon::create($year, $month, 25)->endOfDay();
            $query->whereDate('attendance_date', '>=', $dateFrom)
                ->whereDate('attendance_date', '<=', $dateTo);
        }

        $attendances = $query->paginate(30);

        $rawDates = Attendance::where('employee_id', $employee->id)
            ->pluck('attendance_date');

        $periods = collect($rawDates)->map(function ($date) {
            $d = Carbon::parse($date);
            if ($d->day >= 26) {
                return $d->copy()->addMonth()->format('Y-m');
            }
            return $d->format('Y-m');
        })->unique()->sort()->reverse()->values();

        return view('portal.attendance-history', compact('employee', 'attendances', 'periods'));
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
