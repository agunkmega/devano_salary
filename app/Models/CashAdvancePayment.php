<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvancePayment extends Model
{
    protected $fillable = [
        'cash_advance_id',
        'payroll_id',
        'amount',
        'payment_date',
        'payment_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
