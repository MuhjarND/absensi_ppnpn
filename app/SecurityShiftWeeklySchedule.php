<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SecurityShiftWeeklySchedule extends Model
{
    protected $fillable = [
        'user_id',
        'shift_id',
        'is_off',
        'day_of_week',
        'created_by',
    ];

    protected $casts = [
        'is_off' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
