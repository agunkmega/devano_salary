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
        'position_grade',
        'shift_id',
        'full_name',
        'birth_date',
        'gender',
        'religion',
        'phone',
        'email',
        'address',
        'identity_number',
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
        'password',
        'off_days',
        'qr_code',
        'is_active',
        'employee_type',
        'overtime_pay_per_hour',
        'uang_makan_lembur',
        'bpjs_kesehatan_tanggungan',
        'late_penalty_active',
        'cuti_eligible',
        'station_id',
        'full_salary_no_attendance',
        'employment_status',
        'contract_end_date',
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
        'late_penalty_active' => 'boolean',
        'cuti_eligible' => 'boolean',
        'full_salary_no_attendance' => 'boolean',
        'off_days' => 'array',
        'contract_end_date' => 'date',
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

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function compensatoryDays()
    {
        return $this->hasMany(CompensatoryDay::class);
    }

    public function getDpLeaveTypeIdAttribute(): ?int
    {
        return LeaveType::where('code', 'DP')->where('is_active', true)->value('id');
    }

    public function getDpGrantedAttribute(): int
    {
        return (int) $this->compensatoryDays()->sum('days');
    }

    public function getDpUsedAttribute(): int
    {
        $dpTypeId = $this->dp_leave_type_id;
        if (!$dpTypeId) {
            return 0;
        }

        return (int) Leave::where('employee_id', $this->id)
            ->where('leave_type_id', $dpTypeId)
            ->whereIn('status', ['approved'])
            ->sum('total_days');
    }

    public function getDpRemainingAttribute(): int
    {
        return max(0, $this->dp_granted - $this->dp_used);
    }

    public function getTotalSalaryAttribute()
    {
        return $this->base_salary + $this->allowance + $this->allowance_absensi + $this->allowance_transport + $this->allowance_jabatan + $this->allowance_insentif;
    }

    public static function dayLocaleMap(string $locale = 'id'): array
    {
        if ($locale === 'id') {
            return [
                'sunday'    => 'Minggu',
                'monday'    => 'Senin',
                'tuesday'   => 'Selasa',
                'wednesday' => 'Rabu',
                'thursday'  => 'Kamis',
                'friday'    => 'Jumat',
                'saturday'  => 'Sabtu',
            ];
        }

        return [
            'sunday'    => 'Sunday',
            'monday'    => 'Monday',
            'tuesday'   => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday'  => 'Thursday',
            'friday'    => 'Friday',
            'saturday'  => 'Saturday',
        ];
    }

    public function getOffDaysLocaleAttribute(): array
    {
        $map = self::dayLocaleMap('id');
        $days = (array) ($this->off_days ?? []);

        return array_values(array_map(function ($day) use ($map) {
            $key = strtolower(trim((string) $day));
            return $map[$key] ?? ucfirst($key);
        }, $days));
    }

    public function getOffDaysFormattedAttribute(): string
    {
        $days = $this->off_days_locale;
        return !empty($days) ? implode(', ', $days) : '-';
    }

    public function getGenderLabelAttribute(): string
    {
        if ($this->gender === 'L' || strtolower((string)$this->gender) === 'laki-laki') {
            return 'Laki-laki';
        }
        if ($this->gender === 'P' || strtolower((string)$this->gender) === 'perempuan') {
            return 'Perempuan';
        }
        return (string) ($this->gender ?? '-');
    }

    public function getEmploymentStatusLabelAttribute(): string
    {
        return match ($this->employment_status) {
            'permanent'          => 'Karyawan Tetap',
            'contract_year'      => 'Kontrak Tahunan',
            'contract_permanent' => 'Kontrak Menuju Tetap',
            default              => ucfirst(str_replace('_', ' ', (string) ($this->employment_status ?? 'Karyawan Tetap'))),
        };
    }

    public function getEmployeeTypeLabelAttribute(): string
    {
        return match (strtolower((string) $this->employee_type)) {
            'harian'  => 'Pegawai Harian',
            default   => 'Pegawai Bulanan',
        };
    }

    public function getPhotoUrlAttribute(): string
    {
        if (!$this->photo) {
            $name = urlencode($this->full_name ?? 'User');
            return "https://ui-avatars.com/api/?name={$name}&background=1E3A8A&color=fff&size=256";
        }
        if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
            return $this->photo;
        }
        return url("storage/" . ltrim($this->photo, "/"));
    }

    public function getMasaKerjaAttribute(): string
    {
        if (!$this->join_date) {
            return '-';
        }
        $join = \Carbon\Carbon::parse($this->join_date);
        $diff = $join->diff(now());
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = "{$diff->y} Tahun";
        }
        if ($diff->m > 0) {
            $parts[] = "{$diff->m} Bulan";
        }
        return !empty($parts) ? implode(' ', $parts) : ($diff->d > 0 ? "{$diff->d} Hari" : 'Baru Bergabung');
    }
}
