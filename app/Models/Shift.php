<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'clock_in_time',
        'clock_out_time',
        'saturday_clock_out_time',
        'break_start',
        'break_end',
        'late_tolerance_minutes',
        'is_night_shift',
        'working_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'late_tolerance_minutes' => 'integer',
            'is_night_shift' => 'boolean',
            'working_days' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
