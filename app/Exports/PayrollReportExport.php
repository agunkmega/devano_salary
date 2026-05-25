<?php

namespace App\Exports;

use App\Models\Payroll;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PayrollReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $period;

    public function __construct($period)
    {
        $this->period = $period;
    }

    public function collection()
    {
        return Payroll::with(['employee.user', 'employee.department', 'employee.position'])
            ->where('period', $this->period)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Period',
            'NIK',
            'Employee Name',
            'Department',
            'Position',
            'Base Salary',
            'Allowance',
            'Overtime Pay',
            'Late Penalty',
            'Cash Advance Deduction',
            'BPJS Deduction',
            'Tax',
            'Total Deductions',
            'Net Salary',
            'Status',
        ];
    }

    public function map($payroll): array
    {
        return [
            $payroll->period,
            $payroll->employee?->nik,
            $payroll->employee?->full_name,
            $payroll->employee?->department?->name,
            $payroll->employee?->position?->name,
            $payroll->base_salary,
            $payroll->allowance,
            $payroll->overtime_pay,
            $payroll->late_penalty,
            $payroll->cash_advance_deduction,
            $payroll->bpjs_deduction,
            $payroll->tax_amount,
            $payroll->total_deductions,
            $payroll->net_salary,
            $payroll->status,
        ];
    }
}
