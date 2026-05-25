<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'file_type',
        'model_type',
        'total_records',
        'success_records',
        'failed_records',
        'errors',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_records' => 'integer',
            'success_records' => 'integer',
            'failed_records' => 'integer',
            'errors' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
