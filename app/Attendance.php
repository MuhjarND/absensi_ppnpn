<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_in_photo',
        'clock_out_photo',
        'clock_in_location_id',
        'clock_out_location_id',
        'shift_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clockInLocation()
    {
        return $this->belongsTo(Location::class, 'clock_in_location_id');
    }

    public function clockOutLocation()
    {
        return $this->belongsTo(Location::class, 'clock_out_location_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function scopeToday($query)
    {
        return $query->where('date', now()->toDateString());
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'hadir' => 'success',
            'terlambat' => 'warning',
            'alpha' => 'danger',
            'izin' => 'info',
            'sakit' => 'secondary',
        ];

        return $badges[$this->status] ?? 'light';
    }

    public function getWorkDurationMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        return $this->clock_out->diffInMinutes($this->clock_in);
    }

    public function getFormattedWorkDurationAttribute()
    {
        return static::formatWorkDuration($this->work_duration_minutes);
    }

    public static function formatWorkDuration($minutes)
    {
        $minutes = max(0, (int) $minutes);
        $hours = (int) floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $hours . 'j ' . str_pad((string) $remainingMinutes, 2, '0', STR_PAD_LEFT) . 'm';
    }
}
