<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'submission_date',
        'total_days',
        'reason',
        'status',
        'approved_by',
        'approved_by_head',
        'approval_date',
        'attachment',
        'notes',
        'rejection_reason',
    ];

    protected static function booted()
    {
        static::creating(function ($leave) {
            if (empty($leave->submission_date)) {
                $leave->submission_date = now()->toDateString();
            }
            if (empty($leave->total_days) && !empty($leave->start_date) && !empty($leave->end_date)) {
                $start = \Carbon\Carbon::parse($leave->start_date);
                $end = \Carbon\Carbon::parse($leave->end_date);
                $leave->total_days = max(1, $start->diffInDays($end) + 1);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'submission_date' => 'date',
            'approval_date' => 'datetime',
            'total_days' => 'integer',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function headApprover()
    {
        return $this->belongsTo(Employee::class, 'approved_by_head');
    }
}

