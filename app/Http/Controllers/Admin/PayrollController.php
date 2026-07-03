<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\CashAdvancePayment;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\Setting;
use App\Models\Station;
use App\Mail\PayslipMail;
use App\Services\FlowkirimService;
use App\Services\PayrollService;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PayrollController extends Controller
{
    use LogsActivity;
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index()
    {
        $dateFrom = request('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = request('date_to', now()->endOfMonth()->format('Y-m-d'));

        $periodFrom = substr($dateFrom, 0, 7);
        $periodTo = substr($dateTo, 0, 7);

        $queryBase = Payroll::with(['employee.user', 'employee.department'])
            ->when(request('period'), function ($q, $period) {
                $q->where('period', $period);
            }, function ($q) use ($periodTo) {
                $q->where('period', $periodTo);
            })
            ->when(request('department_id'), function ($q, $deptId) {
                $q->whereHas('employee', function ($sub) use ($deptId) {
                    $sub->where('department_id', $deptId);
                });
            })
            ->when(request('employee_id'), function ($q, $empId) {
                $q->where('employee_id', $empId);
            })
            ->when(request('employee_type'), function ($q, $type) {
                $q->whereHas('employee', function ($sub) use ($type) {
                    $sub->where('employee_type', $type);
                });
            })
            ->when(request('status'), function ($q, $status) {
                $q->where('status', $status);
            })
            ->when(request('station_id'), function ($q, $stationId) {
                $q->whereHas('employee', function ($sub) use ($stationId) {
                    $sub->where('station_id', $stationId);
                });
            });

        $payrolls = (clone $queryBase)->latest()->paginate(20);

        $summary = (clone $queryBase)->selectRaw('
            COALESCE(SUM(bpjs_kesehatan_deduction),0) as total_bpjs_kesehatan,
            COALESCE(SUM(bpjs_kesehatan_company),0) as total_bpjs_kesehatan_company,
            COALESCE(SUM(bpjs_ketenagakerjaan_deduction),0) as total_bpjs_ketenagakerjaan,
            COALESCE(SUM(bpjs_ketenagakerjaan_company),0) as total_bpjs_ketenagakerjaan_company,
            COALESCE(SUM(iuran_bulanan_deduction),0) as total_iuran_bulanan
        ')->first();

        $periods = Payroll::select('period')->distinct()->orderBy('period', 'desc')->pluck('period');

        $monthNames = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        $periodsData = $periods->map(function ($p) use ($monthNames) {
            [$year, $month] = explode('-', $p);
            $start = Carbon::create((int)$year, (int)$month, 26)->subMonth();
            $end = Carbon::create((int)$year, (int)$month, 25);
            $range = $start->format('d M') . ' - ' . $end->format('d M Y');
            return ['value' => $p, 'label' => ($monthNames[$month] ?? $month) . ' ' . $year, 'range' => $range];
        });

        $departments = Department::where('is_active', true)->get(['id', 'name']);
        $employees = Employee::where('is_active', true)->get(['id', 'full_name']);
        $stations = Station::where('is_active', true)->get();

        return view('payroll.index', compact('payrolls', 'summary', 'periods', 'periodsData', 'departments', 'employees', 'stations', 'dateFrom', 'dateTo'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->get();
        $period = now()->format('Y-m');

        return view('payroll.create', compact('employees', 'period'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|date_format:Y-m',
            'bonus' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $existing = Payroll::where('employee_id', $employee->id)
            ->where('period', $validated['period'])
            ->first();

        if ($existing) {
        return redirect()->route('admin.payrolls.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')])
            ->with('error', 'Payroll already exists for this employee in the selected period.');
        }

        $payroll = $this->calculatePayroll($employee, $validated['period'], null, null, (float) ($validated['bonus'] ?? 0), $validated['notes'] ?? null);

        $payrollModel = Payroll::create($payroll);

        $this->recordCashAdvancePayments($payrollModel);

        $this->logActivity('payroll', 'Create', 'Generate payroll ' . $employee->full_name . ' periode ' . $validated['period'], 'Payroll', $payrollModel->id);

        return redirect()->route('admin.payrolls.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')])
            ->with('success', 'Payroll generated successfully.');
    }

    public function generateAll(Request $request)
    {
        $validated = $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $dateFrom = $validated['date_from'];
        $dateTo = $validated['date_to'];
        $period = substr($dateTo, 0, 7);

        $employees = Employee::where('is_active', true)
            ->when($validated['employee_id'] ?? null, function ($q, $empId) {
                $q->where('id', $empId);
            })
            ->get();
        $generated = 0;
        $skipped = 0;
        $total = $employees->count();

        $progressKey = 'payroll_gen_progress_' . auth()->id();
        Cache::put($progressKey, ['current' => 0, 'total' => $total, 'status' => 'processing'], 600);

        session()->save();

        foreach ($employees as $i => $employee) {
            $existing = Payroll::where('employee_id', $employee->id)
                ->where('period', $period)
                ->first();

            if ($existing) {
                $skipped++;
            } else {
                $payroll = $this->calculatePayroll($employee, $period, $dateFrom, $dateTo);
                $payrollModel = Payroll::create($payroll);
                $this->recordCashAdvancePayments($payrollModel);
                $generated++;
            }

            Cache::put($progressKey, ['current' => $i + 1, 'total' => $total, 'status' => 'processing'], 600);
        }

        Cache::put($progressKey, ['current' => $total, 'total' => $total, 'status' => 'complete'], 60);

        $this->logActivity('payroll', 'Create', 'Generate all payroll periode ' . $period, 'Payroll');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'generated' => $generated, 'skipped' => $skipped]);
        }

        return redirect()->route('admin.payrolls.index', ['date_from' => $dateFrom, 'date_to' => $dateTo])
            ->with('success', "Payroll generated for {$generated} employees. {$skipped} skipped (already exist).");
    }

    public function generationProgress()
    {
        $progress = Cache::get('payroll_gen_progress_' . auth()->id(), ['current' => 0, 'total' => 0, 'status' => 'idle']);
        return response()->json($progress);
    }

    private function calculatePayroll(Employee $employee, string $period, ?string $dateFrom = null, ?string $dateTo = null, float $bonus = 0, ?string $notes = null): array
    {
        if ($dateFrom && $dateTo) {
            $startDate = Carbon::parse($dateFrom)->startOfDay();
            $endDate = Carbon::parse($dateTo)->endOfDay();
            $daysInPeriod = Carbon::parse($dateFrom)->startOfDay()->diffInDays(Carbon::parse($dateTo)->startOfDay()) + 1;
        } else {
            [$year, $month] = explode('-', $period);
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $daysInPeriod = $startDate->daysInMonth;
        }

        $baseSalary = (float) ($employee->base_salary ?? 0);
        $totalAllowance = (float) ($employee->allowance ?? 0)
            + (float) ($employee->allowance_absensi ?? 0)
            + (float) ($employee->allowance_transport ?? 0)
            + (float) ($employee->allowance_jabatan ?? 0)
            + (float) ($employee->allowance_insentif ?? 0);

        $lateMinutes = (int) Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->sum('late_minutes');
        $excessBreakMinutes = (int) Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->sum('excess_break_minutes');
        $earlyLeaveMinutes = (int) Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->sum('early_leave_minutes');
        $totalLateMinutes = $lateMinutes + $excessBreakMinutes + $earlyLeaveMinutes;

        $lateDays = (int) Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('late_minutes', '>', 0)
            ->count();

        $attendanceDays = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->whereNotNull('clock_in')
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

        $holidaysByDate = \App\Models\NationalHoliday::where('is_active', true)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->get(['date', 'religion'])
            ->groupBy(fn($h) => $h->date->format('Y-m-d'))
            ->map(fn($items) => $items->pluck('religion')->toArray())
            ->toArray();

        $totalWorkingDays = 0;
        $cursor = $startDate->copy()->startOfDay();
        while ($cursor->lte($endDate)) {
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

        $overtimeMinutes = (int) Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->sum('overtime_minutes');

        $overtimeMealDays = Attendance::where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->where('overtime_minutes', '>', 149)
            ->count();

        if ($employee->employee_type === 'harian') {
            $computedBaseSalary = $employee->full_salary_no_attendance ? ($baseSalary * $daysInPeriod) : ($baseSalary * $effectiveAttendanceDays);
            $ratePerMinute = $daysInPeriod > 0 ? ($baseSalary / 9 / 60) : 0;
            $latePenalty = $ratePerMinute * $totalLateMinutes;
            $latePenaltyPercent = 0;
            $absentPenalty = 0;
            $absentDays = null;
            $overtimeRate = ($employee->overtime_pay_per_hour ?? 0) > 0
                ? (float) $employee->overtime_pay_per_hour
                : 0;
        } else {
            $computedBaseSalary = $baseSalary;
            $totalGaji = $baseSalary + $totalAllowance;
            $dailyRate = $daysInPeriod > 0 ? ($totalGaji / $daysInPeriod) : 0;
            $ratePerMinute = $daysInPeriod > 0 ? ($totalGaji / $daysInPeriod / 9 / 60) : 0;
            $latePenalty = $ratePerMinute * $totalLateMinutes;
            $latePenaltyPercent = $employee->late_penalty_active && $lateDays > 3 ? round($totalGaji * 0.08, 2) : 0;
            $absentDays = $employee->full_salary_no_attendance ? 0 : max(0, $totalWorkingDays - $effectiveAttendanceDays);
            $absentPenalty = $dailyRate * $absentDays;
            $overtimeRate = ($employee->overtime_pay_per_hour ?? 0) > 0
                ? (float) $employee->overtime_pay_per_hour
                : 0;
        }

        $overtimePay = $overtimeMinutes > 0 ? ($overtimeMinutes / 60) * $overtimeRate : 0;

        $uangMakanLembur = $overtimeMealDays * (float) ($employee->uang_makan_lembur ?? 0);

        $cashAdvanceDeduction = CashAdvance::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where('remaining_amount', '>', 0)
            ->sum('installment_amount');

        $bpjsBreakdown = $this->payrollService->calculateBpjsDeduction($employee, $computedBaseSalary);

        $grossBeforeTax = $computedBaseSalary + $totalAllowance + $bonus + $overtimePay + $uangMakanLembur;
        $totalDeductions = $latePenalty + $latePenaltyPercent + $absentPenalty + $cashAdvanceDeduction + $bpjsBreakdown['total'];

        $netBeforeTax = $grossBeforeTax - $totalDeductions;
        $taxThreshold = (float) \App\Models\Setting::where('key', 'tax_threshold')->value('value') ?? 4500000;
        $taxRate = (float) \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 5;
        $taxAmount = $netBeforeTax > $taxThreshold ? (($netBeforeTax - $taxThreshold) * ($taxRate / 100)) : 0;

        $netSalary = max(0, $netBeforeTax - $taxAmount);
        $totalDeductions += $taxAmount;

        return [
            'employee_id' => $employee->id,
            'period' => $period,
            'base_salary' => round($computedBaseSalary, 2),
            'attendance_days' => $attendanceDays,
            'paid_leave_days' => $paidLeaveDays,
            'absent_days' => $absentDays,
            'allowance' => $totalAllowance,
            'bonus' => $bonus,
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
            'notes' => $notes,
        ];
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['employee.user', 'employee.department', 'employee.position', 'approver', 'details']);

        return view('payroll.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $payroll->load('employee');
        return view('payroll.edit', compact('payroll'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'uang_makan_harian' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'other_additions' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $uangMakanHarian = (float) ($validated['uang_makan_harian'] ?? 0);
        $bonus = (float) ($validated['bonus'] ?? 0);
        $otherAdditions = (float) ($validated['other_additions'] ?? 0);
        $otherDeductions = (float) ($validated['other_deductions'] ?? 0);

        $grossBeforeTax = $payroll->base_salary + $payroll->allowance + $bonus + $otherAdditions + $payroll->overtime_pay + $payroll->uang_makan_lembur + $uangMakanHarian;
        $totalDeductions = $payroll->late_penalty + $payroll->absent_penalty + $otherDeductions + $payroll->cash_advance_deduction + $payroll->bpjs_deduction;
        $netBeforeTax = $grossBeforeTax - $totalDeductions;
        $taxThreshold = (float) \App\Models\Setting::where('key', 'tax_threshold')->value('value') ?? 4500000;
        $taxRate = (float) \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 5;
        $taxAmount = $netBeforeTax > $taxThreshold ? (($netBeforeTax - $taxThreshold) * ($taxRate / 100)) : 0;
        $netSalary = max(0, $netBeforeTax - $taxAmount);
        $totalDeductions += $taxAmount;

        $payroll->update([
            'uang_makan_harian' => $uangMakanHarian,
            'bonus' => $bonus,
            'other_additions' => $otherAdditions,
            'other_deductions' => $otherDeductions,
            'tax_amount' => round($taxAmount, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_salary' => round($netSalary, 2),
            'notes' => $validated['notes'] ?? $payroll->notes,
        ]);

        return redirect()->route('admin.payrolls.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')])
            ->with('success', 'Payroll updated successfully.');
    }

    public function approve($id)
    {
        $payroll = Payroll::findOrFail($id);

        $payroll->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approval_date' => now(),
        ]);

        $this->logActivity('payroll', 'Approve', 'Menyetujui payroll ' . $payroll->employee?->full_name, 'Payroll', $payroll->id);

        return redirect()->route('admin.payrolls.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')])
            ->with('success', 'Payroll approved successfully.');
    }

    public function pay($id)
    {
        $payroll = Payroll::findOrFail($id);

        $payroll->update([
            'status' => 'paid',
            'payment_date' => now(),
        ]);

        $this->logActivity('payroll', 'Pay', 'Membayar payroll ' . $payroll->employee?->full_name, 'Payroll', $payroll->id);

        return redirect()->route('admin.payrolls.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')])
            ->with('success', 'Payroll marked as paid successfully.');
    }

    public function regenerate($id)
    {
        $payroll = Payroll::findOrFail($id);
        $employee = Employee::findOrFail($payroll->employee_id);
        $period = $payroll->period;
        $bonus = (float) ($payroll->bonus ?? 0);
        $uangMakanHarian = (float) ($payroll->uang_makan_harian ?? 0);
        $otherAdditions = (float) ($payroll->other_additions ?? 0);
        $otherDeductions = (float) ($payroll->other_deductions ?? 0);

        [$year, $month] = explode('-', $period);
        $dateFrom = request('date_from', Carbon::createFromDate($year, $month, 1)->startOfMonth()->format('Y-m-d'));
        $dateTo = request('date_to', Carbon::createFromDate($year, $month, 1)->endOfMonth()->format('Y-m-d'));

        $payroll->delete();

        $data = $this->calculatePayroll($employee, $period, $dateFrom, $dateTo, $bonus, $payroll->notes);
        $data['uang_makan_harian'] = $uangMakanHarian;
        $data['other_additions'] = $otherAdditions;
        $data['other_deductions'] = $otherDeductions;

        $grossBeforeTax = $data['base_salary'] + $data['allowance'] + $data['bonus'] + $data['other_additions'] + $data['overtime_pay'] + $data['uang_makan_lembur'] + $uangMakanHarian;
        $totalDeductions = $data['late_penalty'] + $data['absent_penalty'] + $otherDeductions + $data['cash_advance_deduction'] + $data['bpjs_deduction'];
        $netBeforeTax = $grossBeforeTax - $totalDeductions;
        $taxThreshold = (float) \App\Models\Setting::where('key', 'tax_threshold')->value('value') ?? 4500000;
        $taxRate = (float) \App\Models\Setting::where('key', 'tax_rate')->value('value') ?? 5;
        $taxAmount = $netBeforeTax > $taxThreshold ? (($netBeforeTax - $taxThreshold) * ($taxRate / 100)) : 0;
        $netSalary = max(0, $netBeforeTax - $taxAmount);
        $totalDeductions += $taxAmount;

        $data['tax_amount'] = round($taxAmount, 2);
        $data['total_deductions'] = round($totalDeductions, 2);
        $data['net_salary'] = round($netSalary, 2);

        $newPayroll = Payroll::create($data);

        $this->recordCashAdvancePayments($newPayroll);

        $this->logActivity('payroll', 'Update', 'Regenerate payroll ' . $payroll->employee?->full_name, 'Payroll', $newPayroll->id);

        return redirect()->route('admin.payrolls.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')])
            ->with('success', 'Payroll regenerated successfully.');
    }

    public function destroy($id)
    {
        $payroll = Payroll::findOrFail($id);
        $payroll->delete();

        return redirect()->route('admin.payrolls.index', ['date_from' => request('date_from'), 'date_to' => request('date_to')])
            ->with('success', 'Payroll deleted successfully.');
    }

    public function sendWhatsApp($id)
    {
        $payroll = Payroll::with(['employee.user', 'employee.department', 'employee.position'])->findOrFail($id);

        $flowkirim = new FlowkirimService();
        $result = $flowkirim->sendPayslip($payroll);

        if ($result['success']) {
            return redirect()->back()->with('success', 'Slip gaji berhasil dikirim ke WhatsApp.');
        }

        $error = $result['error'] ?? ($result['text_status']['body']['message'] ?? 'Gagal mengirim WhatsApp');
        return redirect()->back()->with('error', $error);
    }

    public function sendEmail($id)
    {
        $payroll = Payroll::with('employee')->findOrFail($id);

        if (!$payroll->employee?->email) {
            return redirect()->back()->with('error', 'Email pegawai tidak ditemukan.');
        }

        Mail::to($payroll->employee->email)->send(new PayslipMail($payroll));

        $this->logActivity('payroll', 'Send Email', 'Mengirim slip gaji email ke ' . $payroll->employee->full_name, 'Payroll', $payroll->id);

        return redirect()->back()->with('success', 'Slip gaji berhasil dikirim ke email ' . $payroll->employee->email);
    }

    private function recordCashAdvancePayments(Payroll $payroll): void
    {
        if ($payroll->cash_advance_deduction <= 0) return;

        $advances = CashAdvance::where('employee_id', $payroll->employee_id)
            ->where('status', 'approved')
            ->where('remaining_amount', '>', 0)
            ->get();

        foreach ($advances as $advance) {
            $deductAmount = min((float) $advance->installment_amount, (float) $advance->remaining_amount);
            if ($deductAmount <= 0) continue;

            CashAdvancePayment::create([
                'cash_advance_id' => $advance->id,
                'payroll_id' => $payroll->id,
                'amount' => $deductAmount,
                'payment_date' => $payroll->created_at ?? now(),
                'payment_number' => 'CAP-' . strtoupper(uniqid()),
            ]);

            $advance->decrement('remaining_amount', $deductAmount);

            if ($advance->remaining_amount <= 0) {
                $advance->update(['status' => 'paid']);
            }
        }
    }

    public function slipPdf($id)
    {
        $payroll = Payroll::with(['employee.user', 'employee.department', 'employee.position'])->findOrFail($id);

        $companySettings = Setting::where('group', 'company')->get()->keyBy('key');
        $companyName = $companySettings->get('company_name')?->value ?? 'PT. DEVANO SILVER INDONESIA';
        $companyAddress = $companySettings->get('company_address')?->value ?? '';

        $pdf = Pdf::loadView('payroll.slip', compact('payroll', 'companyName', 'companyAddress'));

        return $pdf->download("payslip-{$payroll->employee->nik}-{$payroll->period}.pdf");
    }
}
