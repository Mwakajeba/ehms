<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimetableSetting extends Model
{
    protected $fillable = [
        'timetable_id',
        'school_start_time',
        'school_end_time',
        'period_duration_minutes',
        'periods_per_day',
        'morning_break_start',
        'morning_break_duration',
        'lunch_break_start',
        'lunch_break_duration',
        'assembly_time',
        'assembly_frequency',
        'assembly_day',
        'games_time',
        'games_day',
        'school_days',
        'half_days',
        'special_days',
        'max_periods_per_day_teacher',
        'max_periods_per_week_teacher',
        'require_free_period_per_day',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'school_start_time' => 'datetime:H:i',
        'school_end_time' => 'datetime:H:i',
        'morning_break_start' => 'datetime:H:i',
        'lunch_break_start' => 'datetime:H:i',
        'assembly_time' => 'datetime:H:i',
        'games_time' => 'datetime:H:i',
        'school_days' => 'array',
        'half_days' => 'array',
        'special_days' => 'array',
        'require_free_period_per_day' => 'boolean',
    ];

    /**
     * Get the timetable that owns this setting.
     */
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    /**
     * Get the company that owns the setting.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the setting.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}
