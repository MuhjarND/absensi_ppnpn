<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'nip',
        'phone',
        'position',
        'is_security',
        'shift_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_security' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function isAdmin()
    {
        return $this->role && $this->role->name === 'admin';
    }

    public function isPegawai()
    {
        return $this->role && $this->role->name === 'pegawai';
    }

    public function isMonitoring()
    {
        return $this->role && $this->role->name === 'monitoring';
    }

    public function isSecurity()
    {
        return $this->is_security;
    }

    /**
     * Get the active shift for this user
     * Regular employees get the default shift, security get their assigned shift
     */
    public function getActiveShift()
    {
        if ($this->is_security && $this->shift) {
            return $this->shift;
        }

        return Shift::where('name', 'Reguler')->first();
    }

    /**
     * Get today's attendance
     */
    public function todayAttendance()
    {
        return $this->attendances()->where('date', now()->toDateString())->first();
    }
}
