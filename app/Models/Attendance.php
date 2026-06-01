<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'attendance_date',
        'clock_in',
        'clock_out',
        'break_out',
        'break_in',
        'overtime_in',
        'overtime_out',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'excess_break_minutes',
        'photo_in',
        'photo_out',
        'location_in',
        'location_out',
        'notes',
        'is_manual',
        'manual_reason',
        'edited_by',
        'ignore_late',
        'ignore_early_leave',
        'ignore_excess_break',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
            'break_out' => 'datetime',
            'break_in' => 'datetime',
            'overtime_in' => 'datetime',
            'overtime_out' => 'datetime',
            'late_minutes' => 'integer',
            'early_leave_minutes' => 'integer',
            'overtime_minutes' => 'integer',
            'excess_break_minutes' => 'integer',
            'is_manual' => 'boolean',
            'ignore_late' => 'boolean',
            'ignore_early_leave' => 'boolean',
            'ignore_excess_break' => 'boolean',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
}
