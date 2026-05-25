<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_paid',
        'max_days_per_year',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'max_days_per_year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
}
