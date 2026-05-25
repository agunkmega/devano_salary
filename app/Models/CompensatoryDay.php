<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompensatoryDay extends Model
{
    protected $fillable = [
        'employee_id',
        'national_holiday_id',
        'earned_date',
        'used_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'earned_date' => 'date:Y-m-d',
            'used_date' => 'date:Y-m-d',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function nationalHoliday()
    {
        return $this->belongsTo(NationalHoliday::class);
    }
}
