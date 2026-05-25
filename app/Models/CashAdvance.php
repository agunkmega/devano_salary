<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvance extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'amount',
        'installment_count',
        'installment_amount',
        'remaining_amount',
        'purpose',
        'status',
        'approved_by',
        'approval_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'installment_count' => 'integer',
            'installment_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'approval_date' => 'datetime',
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

    public function payments()
    {
        return $this->hasMany(CashAdvancePayment::class);
    }
}
