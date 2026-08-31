<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'snippet',
        'image',
        'is_important',
        'publish_date',
        'is_active',
        'created_by',
    ];

    protected $appends = [
        'image_url',
        'formatted_date',
        'snippet_text',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_important' => 'boolean',
            'publish_date' => 'datetime',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        if (Str::startsWith($this->image, ['http://', 'https://'])) {
            return $this->image;
        }

        return Storage::url($this->image);
    }

    public function getSnippetTextAttribute(): string
    {
        if (!empty($this->snippet)) {
            return $this->snippet;
        }

        return Str::limit(strip_tags($this->content), 120);
    }

    public function getFormattedDateAttribute(): string
    {
        $dt = $this->publish_date ?? $this->created_at ?? now();
        return $dt->translatedFormat('d F Y');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('publish_date')
                  ->orWhere('publish_date', '<=', now());
            });
    }
}