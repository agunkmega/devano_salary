<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'title',
        'subtitle',
        'start_time',
        'end_time',
        'category',
        'date',
        'is_completed',
        'is_recurring',
        'repeat_type',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_completed' => 'boolean',
            'is_recurring' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
