<?php

namespace App;

use Carbon\Carbon;
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
        'profile_photo',
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

    public function securityShiftWeeklySchedules()
    {
        return $this->hasMany(SecurityShiftWeeklySchedule::class);
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
        return $this->getShiftByDate(now());
    }

    /**
     * Get shift by specific date.
     * Security users can have a daily shift schedule.
     */
    public function getShiftByDate($date)
    {
        $targetCarbon = null;

        if (empty($date)) {
            $targetDate = now()->toDateString();
            $targetCarbon = now();
        } else {
            $targetCarbon = $date instanceof Carbon ? $date : Carbon::parse($date);
            $targetDate = $targetCarbon->toDateString();
        }

        if ($this->is_security) {
            $dayOfWeek = ($targetCarbon ?: Carbon::parse($targetDate))->dayOfWeek;
            $weeklySchedule = $this->securityShiftWeeklySchedules()
                ->with('shift')
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if ($weeklySchedule) {
                if ($weeklySchedule->is_off) {
                    return null;
                }

                if ($weeklySchedule->shift) {
                    return $weeklySchedule->shift;
                }
            }

            if ($this->shift) {
                return $this->shift;
            }
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

    public function isScheduledOffByDate($date = null)
    {
        if (!$this->is_security) {
            return false;
        }

        if (empty($date)) {
            $targetCarbon = now();
        } else {
            $targetCarbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        }

        return $this->securityShiftWeeklySchedules()
            ->where('day_of_week', $targetCarbon->dayOfWeek)
            ->where('is_off', true)
            ->exists();
    }
}
