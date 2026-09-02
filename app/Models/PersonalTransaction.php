<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'title',
        'amount',
        'type',
        'category',
        'date',
        'note',
        'is_hr_salary',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'datetime',
            'is_hr_salary' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
