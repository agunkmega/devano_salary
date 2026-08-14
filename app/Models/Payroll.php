<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'employee_type',
        'period',
        'base_salary',
        'attendance_days',
        'paid_leave_days',
        'absent_days',
        'allowance',
        'bonus',
        'overtime_pay',
        'uang_makan_lembur',
        'uang_makan_harian',
        'other_additions',
        'other_deductions',
        'late_penalty',
        'late_penalty_percent',
        'absent_penalty',
        'cash_advance_deduction',
        'bpjs_deduction',
        'bpjs_kesehatan_deduction',
        'bpjs_kesehatan_company',
        'bpjs_ketenagakerjaan_deduction',
        'bpjs_ketenagakerjaan_company',
        'iuran_bulanan_deduction',
        'tax_amount',
        'total_deductions',
        'net_salary',
        'status',
        'approved_by',
        'approval_date',
        'payment_date',
        'payment_method',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'allowance' => 'decimal:2',
            'bonus' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'uang_makan_lembur' => 'decimal:2',
            'uang_makan_harian' => 'decimal:2',
            'other_additions' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'late_penalty' => 'decimal:2',
            'late_penalty_percent' => 'decimal:2',
            'absent_penalty' => 'decimal:2',
            'cash_advance_deduction' => 'decimal:2',
            'bpjs_deduction' => 'decimal:2',
            'bpjs_kesehatan_deduction' => 'decimal:2',
            'bpjs_kesehatan_company' => 'decimal:2',
            'bpjs_ketenagakerjaan_deduction' => 'decimal:2',
            'bpjs_ketenagakerjaan_company' => 'decimal:2',
            'iuran_bulanan_deduction' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'approval_date' => 'datetime',
            'payment_date' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }
}
