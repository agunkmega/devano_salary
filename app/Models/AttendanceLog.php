<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_id',
        'log_date',
        'log_time',
        'type',
        'pin',
        'machine_sn',
        'file_name',
        'raw_data',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'log_date' => 'date',
            'log_time' => 'datetime',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
