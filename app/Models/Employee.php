<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nik',
        'user_id',
        'department_id',
        'position_id',
        'shift_id',
        'full_name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
        'join_date',
        'status',
        'base_salary',
        'allowance',
        'allowance_type',
        'allowance_absensi',
        'allowance_transport',
        'allowance_jabatan',
        'allowance_insentif',
        'bank_name',
        'bank_account',
        'bank_holder',
        'bpjs_ketenagakerjaan',
        'bpjs_ketenagakerjaan_type',
        'bpjs_kesehatan',
        'bpjs_kesehatan_active',
        'iuran_wajib_amount',
        'photo',
        'qr_code',
        'is_active',
        'employee_type',
        'overtime_pay_per_hour',
        'uang_makan_lembur',
        'bpjs_kesehatan_tanggungan',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'join_date' => 'date',
            'base_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'allowance_absensi' => 'decimal:2',
        'allowance_transport' => 'decimal:2',
        'allowance_jabatan' => 'decimal:2',
        'allowance_insentif' => 'decimal:2',
        'overtime_pay_per_hour' => 'decimal:2',
        'uang_makan_lembur' => 'decimal:2',
        'is_active' => 'boolean',
        'bpjs_kesehatan_active' => 'boolean',
        'bpjs_kesehatan_tanggungan' => 'integer',
        'iuran_wajib_amount' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function cashAdvances()
    {
        return $this->hasMany(CashAdvance::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function getTotalSalaryAttribute()
    {
        return $this->base_salary + $this->allowance + $this->allowance_absensi + $this->allowance_transport + $this->allowance_jabatan + $this->allowance_insentif;
    }
}
