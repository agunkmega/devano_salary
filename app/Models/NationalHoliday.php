<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NationalHoliday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'religion',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'is_active' => 'boolean',
        ];
    }
}
