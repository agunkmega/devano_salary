<?php
$emp = App\Models\Employee::where('nik','1039')->firstOrFail();
$period = '2026-06';
$dateFrom = '2026-05-26';
$dateTo = '2026-06-25';

$startDate = \Carbon\Carbon::parse($dateFrom)->startOfDay();
$endDate = \Carbon\Carbon::parse($dateTo)->endOfDay();
$daysInPeriod = \Carbon\Carbon::parse($dateFrom)->startOfDay()->diffInDays(\Carbon\Carbon::parse($dateTo)->startOfDay()) + 1;

$baseSalary = (float) ($emp->base_salary ?? 0);
$totalAllowance = (float) ($emp->allowance ?? 0)
    + (float) ($emp->allowance_absensi ?? 0)
    + (float) ($emp->allowance_transport ?? 0)
    + (float) ($emp->allowance_jabatan ?? 0)
    + (float) ($emp->allowance_insentif ?? 0);

$lateMinutes = (int) App\Models\Attendance::where('employee_id', $emp->id)
    ->whereBetween('attendance_date', [$startDate, $endDate])->sum('late_minutes');
$excessBreakMinutes = (int) App\Models\Attendance::where('employee_id', $emp->id)
    ->whereBetween('attendance_date', [$startDate, $endDate])->sum('excess_break_minutes');
$earlyLeaveMinutes = (int) App\Models\Attendance::where('employee_id', $emp->id)
    ->whereBetween('attendance_date', [$startDate, $endDate])->sum('early_leave_minutes');
$totalLateMinutes = $lateMinutes + $excessBreakMinutes + $earlyLeaveMinutes;
$lateDays = (int) App\Models\Attendance::where('employee_id', $emp->id)
    ->whereBetween('attendance_date', [$startDate, $endDate])->where('late_minutes', '>', 0)->count();

$attendanceDays = App\Models\Attendance::where('employee_id', $emp->id)
    ->whereBetween('attendance_date', [$startDate, $endDate])->whereNotNull('clock_in')->count();

$offDays = $emp->off_days ?? ['sunday'];

$paidLeaveDays = App\Models\Leave::where('employee_id', $emp->id)
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

$holidaysByDate = App\Models\NationalHoliday::where('is_active', true)
    ->whereDate('date', '>=', $startDate)->whereDate('date', '<=', $endDate)
    ->get(['date', 'religion'])
    ->groupBy(fn($h) => $h->date->format('Y-m-d'))
    ->map(fn($items) => $items->pluck('religion')->toArray())->toArray();

$totalWorkingDays = 0;
$cursor = $startDate->copy()->startOfDay();
while ($cursor->lte($endDate)) {
    $dateStr = $cursor->format('Y-m-d');
    $isHoliday = false;
    if (isset($holidaysByDate[$dateStr])) {
        $empReligion = $emp->religion;
        $isHoliday = collect($holidaysByDate[$dateStr])->contains(fn($religion) => empty($religion) || $religion === $empReligion);
    }
    if (!in_array(strtolower($cursor->format('l')), $offDays) && !$isHoliday) {
        $totalWorkingDays++;
    }
    $cursor->addDay();
}

$overtimeMinutes = (int) App\Models\Attendance::where('employee_id', $emp->id)
    ->whereBetween('attendance_date', [$startDate, $endDate])->sum('overtime_minutes');
$overtimeMealDays = App\Models\Attendance::where('employee_id', $emp->id)
    ->whereBetween('attendance_date', [$startDate, $endDate])->where('overtime_minutes', '>', 149)->count();

if ($emp->employee_type === 'harian') {
    $computedBaseSalary = $emp->full_salary_no_attendance ? ($baseSalary * $daysInPeriod) : ($baseSalary * $effectiveAttendanceDays);
    $ratePerMinute = $daysInPeriod > 0 ? ($baseSalary / 9 / 60) : 0;
    $latePenalty = $ratePerMinute * $totalLateMinutes;
    $latePenaltyPercent = 0;
    $absentPenalty = 0;
    $absentDays = null;
    $overtimeRate = ($emp->overtime_pay_per_hour ?? 0) > 0 ? (float) $emp->overtime_pay_per_hour : 0;
} else {
    $computedBaseSalary = $baseSalary;
    $totalGaji = $baseSalary + $totalAllowance;
    $dailyRate = $daysInPeriod > 0 ? ($totalGaji / $daysInPeriod) : 0;
    $ratePerMinute = $daysInPeriod > 0 ? ($totalGaji / $daysInPeriod / 9 / 60) : 0;
    $latePenalty = $ratePerMinute * $totalLateMinutes;
    $latePenaltyPercent = $emp->late_penalty_active && $lateDays > 3 ? round($totalGaji * 0.08, 2) : 0;
    $absentDays = $emp->full_salary_no_attendance ? 0 : max(0, $totalWorkingDays - $effectiveAttendanceDays);
    $absentPenalty = $dailyRate * $absentDays;
    $overtimeRate = ($emp->overtime_pay_per_hour ?? 0) > 0 ? (float) $emp->overtime_pay_per_hour : 0;
}

$overtimePay = $overtimeMinutes > 0 ? ($overtimeMinutes / 60) * $overtimeRate : 0;
$uangMakanLembur = $overtimeMealDays * (float) ($emp->uang_makan_lembur ?? 0);

$cashAdvanceDeduction = App\Models\CashAdvance::where('employee_id', $emp->id)
    ->where('status', 'approved')->where('remaining_amount', '>', 0)->sum('installment_amount');

$payrollService = app(\App\Services\PayrollService::class);
$bpjsBreakdown = $payrollService->calculateBpjsDeduction($emp, $computedBaseSalary);

$grossBeforeTax = $computedBaseSalary + $totalAllowance + 0 + $overtimePay + $uangMakanLembur;
$totalDeductions = $latePenalty + $latePenaltyPercent + $absentPenalty + $cashAdvanceDeduction + $bpjsBreakdown['total'];

$netBeforeTax = $grossBeforeTax - $totalDeductions;
$taxThreshold = (float) App\Models\Setting::where('key', 'tax_threshold')->value('value') ?? 4500000;
$taxRate = (float) App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 5;
$taxAmount = $netBeforeTax > $taxThreshold ? (($netBeforeTax - $taxThreshold) * ($taxRate / 100)) : 0;

$netSalary = max(0, $netBeforeTax - $taxAmount);
$totalDeductions += $taxAmount;

echo "\n=== GENERATE PAYROLL ===\n";
echo "Period: $period ({$dateFrom} to {$dateTo})\n";
echo "Days in period: $daysInPeriod\n";
echo "Employee: {$emp->full_name} (type: {$emp->employee_type})\n";
echo "Base salary: $baseSalary\n";
echo "Allowance: $totalAllowance\n";
echo "Attendance days: $attendanceDays\n";
echo "Paid leave days: $paidLeaveDays\n";
echo "Effective attendance: $effectiveAttendanceDays\n";
echo "Total working days: $totalWorkingDays\n";
echo "Absent days: " . ($absentDays ?? 'N/A') . "\n";
echo "Late minutes: $totalLateMinutes\n";
echo "Late days (>0): $lateDays\n";
echo "Late penalty active: " . ($emp->late_penalty_active ? 'yes' : 'no') . "\n";
echo "Overtime minutes: $overtimeMinutes\n";
echo "Overtime meal days: $overtimeMealDays\n";
echo "Cash advance: $cashAdvanceDeduction\n\n";

echo "--- COMPUTED ---\n";
echo "Computed base salary: " . round($computedBaseSalary, 2) . "\n";
echo "Daily rate: " . round($dailyRate ?? 0, 2) . "\n";
echo "Late penalty: " . round($latePenalty, 2) . "\n";
echo "Late penalty percent: " . round($latePenaltyPercent, 2) . "\n";
echo "Absent penalty: " . round($absentPenalty, 2) . "\n";
echo "Overtime pay: " . round($overtimePay, 2) . "\n";
echo "Uang makan lembur: " . round($uangMakanLembur, 2) . "\n";
echo "Gross before tax: " . round($grossBeforeTax, 2) . "\n";
echo "Total deductions: " . round($totalDeductions, 2) . "\n";
echo "BPJS breakdown: " . json_encode($bpjsBreakdown) . "\n";
echo "Net before tax: " . round($netBeforeTax, 2) . "\n";
echo "Tax threshold: $taxThreshold\n";
echo "Tax amount: " . round($taxAmount, 2) . "\n";
echo "Net salary: " . round($netSalary, 2) . "\n\n";

// Check if exists
$existing = App\Models\Payroll::where('employee_id', $emp->id)->where('period', $period)->first();
if ($existing) {
    echo "EXISTING PAYROLL already exists (id={$existing->id}). Net salary: {$existing->net_salary}\n";
} else {
    $data = [
        'employee_id' => $emp->id,
        'period' => $period,
        'base_salary' => round($computedBaseSalary, 2),
        'attendance_days' => $attendanceDays,
        'paid_leave_days' => $paidLeaveDays,
        'absent_days' => $absentDays,
        'allowance' => $totalAllowance,
        'bonus' => 0,
        'overtime_pay' => round($overtimePay, 2),
        'uang_makan_lembur' => round($uangMakanLembur, 2),
        'late_penalty' => round($latePenalty, 2),
        'late_penalty_percent' => round($latePenaltyPercent, 2),
        'absent_penalty' => round($absentPenalty, 2),
        'cash_advance_deduction' => round($cashAdvanceDeduction, 2),
        'bpjs_deduction' => $bpjsBreakdown['total'],
        'bpjs_kesehatan_deduction' => $bpjsBreakdown['bpjs_kesehatan_deduction'],
        'bpjs_kesehatan_company' => $bpjsBreakdown['bpjs_kesehatan_company'],
        'bpjs_ketenagakerjaan_deduction' => $bpjsBreakdown['bpjs_ketenagakerjaan_deduction'],
        'bpjs_ketenagakerjaan_company' => $bpjsBreakdown['bpjs_ketenagakerjaan_company'],
        'iuran_bulanan_deduction' => $bpjsBreakdown['iuran_bulanan_deduction'],
        'tax_amount' => round($taxAmount, 2),
        'total_deductions' => round($totalDeductions, 2),
        'net_salary' => round($netSalary, 2),
        'status' => 'draft',
    ];
    App\Models\Payroll::create($data);
    echo "PAYROLL CREATED successfully!\n";
}
