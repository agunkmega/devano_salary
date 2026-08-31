<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MobileAppRelease extends Model
{
    use HasFactory;

    protected $table = 'mobile_app_releases';

    protected $fillable = [
        'version_name',
        'version_code',
        'file_name',
        'file_path',
        'file_size',
        'release_notes',
        'is_mandatory',
        'platform',
        'uploaded_by',
        'checksum',
        'download_count',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'version_code' => 'integer',
        'download_count' => 'integer',
    ];

    protected $appends = ['download_url'];

    public function getDownloadUrlAttribute(): string
    {
        return url(Storage::url($this->file_path));
    }
}