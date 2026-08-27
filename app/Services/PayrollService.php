<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function getPeriodRange(string $period): array
    {
        [$year, $month] = explode('-', $period);
        $month = (int) $month;
        $year = (int) $year;
        $prevMonth = $month > 1 ? $month - 1 : 12;
        $prevYear = $month > 1 ? $year : $year - 1;
        $start = Carbon::create($prevYear, $prevMonth, 26)->startOfDay();
        $end = Carbon::create($year, $month, 25)->endOfDay();
        return [$start, $end];
    }

    public function getTotalMinutes($employeeId, $period, $column): int
    {
        [$start, $end] = $this->getPeriodRange($period);
        return (int) Attendance::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$start, $end])
            ->sum($column);
    }

    public function getAttendanceDays($employeeId, $period): int
    {
        [$start, $end] = $this->getPeriodRange($period);
        return Attendance::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$start, $end])
            ->whereNotNull('clock_in')
            ->count();
    }

    public function calculatePayroll(Employee $employee, $period): array
    {
        [$start, $end] = $this->getPeriodRange($period);
        $startDate = $start->copy()->startOfDay();
        $endDate = $end->copy()->endOfDay();
        $daysInPeriod = $startDate->diffInDays($endDate->copy()->endOfDay()) + 1;

        $baseSalary = (float) ($employee->base_salary ?? 0);
        $totalAllowance = (float) ($employee->allowance ?? 0)
            + (float) ($employee->allowance_absensi ?? 0)
            + (float) ($employee->allowance_transport ?? 0)
            + (float) ($employee->allowance_jabatan ?? 0)
            + (float) ($employee->allowance_insentif ?? 0);

        $lateMinutes = $this->getTotalMinutes($employee->id, $period, 'late_minutes');
        $excessBreakMinutes = $this->getTotalMinutes($employee->id, $period, 'excess_break_minutes');
        $earlyLeaveMinutes = $this->getTotalMinutes($employee->id, $period, 'early_leave_minutes');
        $totalLateMinutes = $lateMinutes + $excessBreakMinutes + $earlyLeaveMinutes;

        $attendanceDays = $this->getAttendanceDays($employee->id, $period);

        $lateDays = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('late_minutes', '>', 0)
            ->where('is_half_day', false)
            ->count();

        $offDays = $employee->off_days ?? ['sunday'];

        $paidLeaveDays = \App\Models\Leave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', fn($q) => $q->where('is_paid', true))
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->get()
            ->flatMap(fn($leave) => \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date)->toArray())
            ->filter(fn($date) => $date->between($startDate, $endDate) && !in_array(strtolower($date->format('l')), $offDays))
            ->unique()
            ->count();

        $effectiveAttendanceDays = $attendanceDays + $paidLeaveDays;

        if ($employee->employee_type === 'harian') {
            $computedBaseSalary = $employee->full_salary_no_attendance ? ($baseSalary * $daysInPeriod) : ($baseSalary * $effectiveAttendanceDays);
            $ratePerMinute = $daysInPeriod > 0 ? ($baseSalary / 9 / 60) : 0;
            $latePenalty = $ratePerMinute * $totalLateMinutes;
            $latePenaltyPercent = 0;
            $absentPenalty = 0;
            $absentDays = null;
        } else {
            $computedBaseSalary = $baseSalary;
            $totalGaji = $baseSalary + $totalAllowance;
            $dailyRate = $daysInPeriod > 0 ? ($totalGaji / $daysInPeriod) : 0;
            $ratePerMinute = $daysInPeriod > 0 ? ($totalGaji / $daysInPeriod / 9 / 60) : 0;
            $latePenalty = $ratePerMinute * $totalLateMinutes;
            $latePenaltyPercent = $employee->late_penalty_active && $lateDays > 3 ? round($totalGaji * 0.08, 2) : 0;
            $totalWorkingDays = $this->getTotalWorkingDays($period, $startDate, $endDate, $employee);
            $absentDays = $employee->full_salary_no_attendance ? 0 : max(0, $totalWorkingDays - $effectiveAttendanceDays);
            $absentPenalty = $dailyRate * $absentDays;
        }

        $overtimePay = $this->calculateOvertimePay($employee->id, $period, $employee);

        $overtimeMealDays = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('overtime_minutes', '>', 149)
            ->count();
        $uangMakanLembur = $overtimeMealDays * (float) ($employee->uang_makan_lembur ?? 0);

        $cashAdvance = $this->getCashAdvanceBreakdown($employee->id);
        $bpjsBreakdown = $this->calculateBpjsDeduction($employee, $computedBaseSalary);

        $grossSalary = $computedBaseSalary + $totalAllowance + $overtimePay + $uangMakanLembur;
        $totalDeductions = $latePenalty + $latePenaltyPercent + $absentPenalty + $cashAdvance['total'] + $bpjsBreakdown['total'];
        $netSalary = $grossSalary - $totalDeductions;
        $taxAmount = $this->calculateTax($netSalary);

        $netSalary -= $taxAmount;
        $totalDeductions += $taxAmount;

        return [
            'base_salary' => $computedBaseSalary,
            'allowance' => $totalAllowance,
            'bonus' => 0,
            'overtime_pay' => round($overtimePay, 2),
            'uang_makan_lembur' => round($uangMakanLembur, 2),
            'late_penalty' => round($latePenalty, 2),
            'late_penalty_percent' => round($latePenaltyPercent, 2),
            'absent_penalty' => round($absentPenalty, 2),
            'cash_advance_deduction' => round($cashAdvance['total'], 2),
            'cash_advance_tunai' => round($cashAdvance['tunai'], 2),
            'cash_advance_nontunai' => round($cashAdvance['nontunai'], 2),
            'bpjs_deduction' => $bpjsBreakdown['total'],
            'bpjs_kesehatan_deduction' => $bpjsBreakdown['bpjs_kesehatan_deduction'],
            'bpjs_kesehatan_company' => $bpjsBreakdown['bpjs_kesehatan_company'],
            'bpjs_ketenagakerjaan_deduction' => $bpjsBreakdown['bpjs_ketenagakerjaan_deduction'],
            'bpjs_ketenagakerjaan_company' => $bpjsBreakdown['bpjs_ketenagakerjaan_company'],
            'iuran_bulanan_deduction' => $bpjsBreakdown['iuran_bulanan_deduction'],
            'tax_amount' => round($taxAmount, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary' => round(max(0, $netSalary), 2),
            'attendance_days' => $attendanceDays,
            'paid_leave_days' => $paidLeaveDays,
            'absent_days' => $absentDays,
        ];
    }

    public function calculateOvertimePay($employeeId, $period, $employee = null): float
    {
        [$start, $end] = $this->getPeriodRange($period);

        $employeeRate = (float) ($employee->overtime_pay_per_hour ?? 0);
        if ($employeeRate <= 0) {
            return 0;
        }

        $totalOvertimeMinutes = Attendance::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$start, $end])
            ->sum('overtime_minutes');

        return ($totalOvertimeMinutes / 60) * $employeeRate;
    }

    public function calculateCashAdvanceDeduction($employeeId): float
    {
        return $this->getCashAdvanceBreakdown($employeeId)['total'];
    }

    public function getCashAdvanceBreakdown($employeeId): array
    {
        $base = CashAdvance::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('remaining_amount', '>', 0);

        $tunai = (float) (clone $base)->where('type', 'tunai')->sum('installment_amount');
        $nontunai = (float) (clone $base)->whereIn('type', ['nontunai', 'non_tunai'])->sum('installment_amount');

        return [
            'tunai' => $tunai,
            'nontunai' => $nontunai,
            'total' => $tunai + $nontunai,
        ];
    }

    public function calculateBpjsDeduction(Employee $employee, ?float $baseSalary = null): array
    {
        $bpjsKes = 0;
        $bpjsKesCompany = 0;
        if ($employee->bpjs_kesehatan_active) {
            $bpjsKes = (float) (Setting::where('key', 'bpjs_kesehatan_rate')->value('value') ?? 0) * (1 + (int) ($employee->bpjs_kesehatan_tanggungan ?? 0));
            $bpjsKesCompany = (float) (Setting::where('key', 'bpjs_kesehatan_company')->value('value') ?? 0) * (1 + (int) ($employee->bpjs_kesehatan_tanggungan ?? 0));
        }

        $bpjsKetEmployee = 0;
        $bpjsKetCompany = 0;
        if ($employee->bpjs_ketenagakerjaan_type === 'full') {
            $bpjsKetEmployee = (float) (Setting::where('key', 'bpjs_ketenagakerjaan_full_rate')->value('value') ?? 0);
            $bpjsKetCompany = (float) (Setting::where('key', 'bpjs_ket_full_company')->value('value') ?? 0);
        } elseif ($employee->bpjs_ketenagakerjaan_type === 'partial') {
            $bpjsKetEmployee = (float) (Setting::where('key', 'bpjs_ket_partial_employee')->value('value') ?? 0);
            $bpjsKetCompany = (float) (Setting::where('key', 'bpjs_ket_partial_company')->value('value') ?? 0);
        }

        $iuranBulanan = 0;
        if ($employee->iuran_wajib_amount > 0) {
            $iuranBulanan = (float) $employee->iuran_wajib_amount;
        }

        $employeeTotal = $bpjsKes + $bpjsKetEmployee + $iuranBulanan;

        return [
            'bpjs_kesehatan_deduction' => round($bpjsKes, 2),
            'bpjs_kesehatan_company' => round($bpjsKesCompany, 2),
            'bpjs_ketenagakerjaan_deduction' => round($bpjsKetEmployee, 2),
            'bpjs_ketenagakerjaan_company' => round($bpjsKetCompany, 2),
            'iuran_bulanan_deduction' => round($iuranBulanan, 2),
            'total' => round($employeeTotal, 2),
        ];
    }

    public function calculateLatePenalty($employeeId, $period): float
    {
        [$start, $end] = $this->getPeriodRange($period);
        $ratePerMinute = (float) Setting::where('key', 'late_penalty_per_minute')->value('value') ?? 2000;

        $totalLateMinutes = Attendance::where('employee_id', $employeeId)
            ->whereBetween('attendance_date', [$start, $end])
            ->sum('late_minutes');

        return $totalLateMinutes * $ratePerMinute;
    }

    public function calculateTax($netSalary): float
    {
        $threshold = (float) Setting::where('key', 'tax_threshold')->value('value') ?? 4500000;
        $taxRate = (float) Setting::where('key', 'tax_rate')->value('value') ?? 5;

        if ($netSalary <= $threshold) {
            return 0;
        }

        return ($netSalary - $threshold) * ($taxRate / 100);
    }

    public function generatePayroll($employeeId, $period): Payroll
    {
        $employee = Employee::findOrFail($employeeId);
        $data = $this->calculatePayroll($employee, $period);

        return Payroll::create(array_merge($data, [
            'employee_id' => $employeeId,
            'period' => $period,
            'status' => 'draft',
        ]));
    }

    public function generateAllPayroll($period): array
    {
        $employees = Employee::where('is_active', true)->get();
        $generated = [];

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $existing = Payroll::where('employee_id', $employee->id)
                    ->where('period', $period)
                    ->first();

                if ($existing) {
                    continue;
                }

                $this->generatePayroll($employee->id, $period);
                $generated[] = $employee->id;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $generated;
    }

    private function getTotalWorkingDays(string $period, ?Carbon $startDate = null, ?Carbon $endDate = null, ?Employee $employee = null): int
    {
        if (!$startDate || !$endDate) {
            [$year, $month] = explode('-', $period);
            $start = Carbon::createFromDate($year, $month, 1);
            $end = $start->copy()->endOfMonth();
        } else {
            $start = $startDate->copy();
            $end = $endDate->copy();
        }

        $holidaysByDate = \App\Models\NationalHoliday::where('is_active', true)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->get(['date', 'religion'])
            ->groupBy(fn($h) => $h->date->format('Y-m-d'))
            ->map(fn($items) => $items->pluck('religion')->toArray())
            ->toArray();

        $count = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateStr = $cursor->format('Y-m-d');
            $isHoliday = false;
            if (isset($holidaysByDate[$dateStr])) {
                $empReligion = $employee?->religion;
                $isHoliday = collect($holidaysByDate[$dateStr])->contains(fn($religion) => empty($religion) || $religion === $empReligion);
            }
            if (!in_array(strtolower($cursor->format('l')), $employee?->off_days ?? ['sunday']) && !$isHoliday) {
                $count++;
            }
            $cursor->addDay();
        }
        return $count;
    }
}
