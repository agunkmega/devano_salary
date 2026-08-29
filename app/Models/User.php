<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_HR = 'hr';
    const ROLE_MANAGER = 'manager';
    const ROLE_STAFF = 'staff';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'phone',
        'photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function approvalLeaves()
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }

    public function approvalCashAdvances()
    {
        return $this->hasMany(CashAdvance::class, 'approved_by');
    }

    public function approvalPayrolls()
    {
        return $this->hasMany(Payroll::class, 'approved_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isHR(): bool
    {
        return $this->role === self::ROLE_HR;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }
        if (filter_var($this->photo, FILTER_VALIDATE_URL)) {
            return $this->photo;
        }
        return url("storage/" . ltrim($this->photo, "/"));
    }
}