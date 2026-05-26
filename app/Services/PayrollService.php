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
    public function getTotalMinutes($employeeId, $period, $column): int
    {
        [$year, $month] = explode('-', $period);
        return (int) Attendance::where('employee_id', $employeeId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->sum($column);
    }

    public function getAttendanceDays($employeeId, $period): int
    {
        [$year, $month] = explode('-', $period);
        return Attendance::where('employee_id', $employeeId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->whereNotNull('clock_in')
            ->count();
    }

    public function calculatePayroll(Employee $employee, $period): array
    {
        [$year, $month] = explode('-', $period);
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInPeriod = $startDate->daysInMonth;

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
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->where('late_minutes', '>', 0)
            ->count();

        $paidLeaveDays = \App\Models\Leave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereHas('leaveType', fn($q) => $q->where('is_paid', true))
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->get()
            ->flatMap(fn($leave) => \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date)->toArray())
            ->filter(fn($date) => $date->between($startDate, $endDate) && $date->dayOfWeek !== Carbon::SUNDAY)
            ->unique()
            ->count();

        $effectiveAttendanceDays = $attendanceDays + $paidLeaveDays;

        if ($employee->employee_type === 'harian') {
            $computedBaseSalary = $baseSalary * $effectiveAttendanceDays;
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
            $absentDays = max(0, $totalWorkingDays - $effectiveAttendanceDays);
            $absentPenalty = $dailyRate * $absentDays;
        }

        $overtimePay = $this->calculateOvertimePay($employee->id, $period, $employee);

        [$year, $month] = explode('-', $period);
        $overtimeMealDays = Attendance::where('employee_id', $employee->id)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->where('overtime_minutes', '>', 149)
            ->count();
        $uangMakanLembur = $overtimeMealDays * (float) ($employee->uang_makan_lembur ?? 0);

        $cashAdvanceDeduction = $this->calculateCashAdvanceDeduction($employee->id);
        $bpjsDeduction = $this->calculateBpjsDeduction($employee, $computedBaseSalary);

        $grossSalary = $computedBaseSalary + $totalAllowance + $overtimePay + $uangMakanLembur;
        $totalDeductions = $latePenalty + $latePenaltyPercent + $absentPenalty + $cashAdvanceDeduction + $bpjsDeduction;
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
            'late_penalty' => round($latePenalty + $latePenaltyPercent, 2),
            'absent_penalty' => round($absentPenalty, 2),
            'cash_advance_deduction' => round($cashAdvanceDeduction, 2),
            'bpjs_deduction' => round($bpjsDeduction, 2),
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
        [$year, $month] = explode('-', $period);

        $ratePerHour = (float) Setting::where('key', 'overtime_rate_per_hour')->value('value') ?? 25000;

        if ($employee && ($employee->overtime_pay_per_hour ?? 0) > 0) {
            $ratePerHour = (float) $employee->overtime_pay_per_hour;
        }

        $totalOvertimeMinutes = Attendance::where('employee_id', $employeeId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
            ->sum('overtime_minutes');

        return ($totalOvertimeMinutes / 60) * $ratePerHour;
    }

    public function calculateCashAdvanceDeduction($employeeId): float
    {
        $activeAdvance = CashAdvance::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where('remaining_amount', '>', 0)
            ->first();

        if (!$activeAdvance) {
            return 0;
        }

        return (float) $activeAdvance->installment_amount;
    }

    public function calculateBpjsDeduction(Employee $employee, ?float $baseSalary = null): float
    {
        $total = 0;

        if ($employee->bpjs_ketenagakerjaan_type === 'full') {
            $total += (float) (Setting::where('key', 'bpjs_ketenagakerjaan_full_rate')->value('value') ?? 0);
        } elseif ($employee->bpjs_ketenagakerjaan_type === 'partial') {
            $total += (float) (Setting::where('key', 'bpjs_ketenagakerjaan_partial_rate')->value('value') ?? 0);
        }

        if ($employee->bpjs_kesehatan_active) {
            $total += (float) (Setting::where('key', 'bpjs_kesehatan_rate')->value('value') ?? 0) * (1 + (int) ($employee->bpjs_kesehatan_tanggungan ?? 0));
        }

        if ($employee->iuran_wajib_amount > 0) {
            $total += (float) $employee->iuran_wajib_amount;
        }

        return $total;
    }

    public function calculateLatePenalty($employeeId, $period): float
    {
        [$year, $month] = explode('-', $period);
        $ratePerMinute = (float) Setting::where('key', 'late_penalty_per_minute')->value('value') ?? 2000;

        $totalLateMinutes = Attendance::where('employee_id', $employeeId)
            ->whereYear('attendance_date', $year)
            ->whereMonth('attendance_date', $month)
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
            if ($cursor->dayOfWeek !== Carbon::SUNDAY && !$isHoliday) {
                $count++;
            }
            $cursor->addDay();
        }
        return $count;
    }
}
