<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetablePeriod extends Model
{
    protected $fillable = [
        'timetable_id',
        'day_of_week',
        'period_number',
        'start_time',
        'end_time',
        'duration_minutes',
        'period_type',
        'period_name',
        'is_break',
        'sort_order',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_break' => 'boolean',
    ];

    /**
     * Get the timetable that owns this period.
     */
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    /**
     * Get the entries for this period.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class, 'period_id');
    }

    /**
     * Scope a query to only include regular periods (not breaks).
     */
    public function scopeRegular($query)
    {
        return $query->where('is_break', false);
    }

    /**
     * Scope a query to only include break periods.
     */
    public function scopeBreaks($query)
    {
        return $query->where('is_break', true);
    }

    /**
     * Scope a query to filter by day of week.
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }
}
