<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TimetableEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'timetable_id',
        'period_id',
        'day_of_week',
        'period_number',
        'subject_id',
        'class_id',
        'stream_id',
        'teacher_id',
        'room_id',
        'is_double_period',
        'is_practical',
        'subject_type',
        'notes',
        'sort_order',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'is_double_period' => 'boolean',
        'is_practical' => 'boolean',
    ];

    /**
     * Get the timetable that owns this entry.
     */
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    /**
     * Get the period for this entry.
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(TimetablePeriod::class, 'period_id');
    }

    /**
     * Get the subject for this entry.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class for this entry.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Get the stream for this entry.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Get the teacher for this entry.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Hr\Employee::class, 'teacher_id');
    }

    /**
     * Get the room for this entry.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(TimetableRoom::class);
    }

    /**
     * Get the company that owns the entry.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the entry.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Scope a query to filter by day of week.
     */
    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    /**
     * Scope a query to filter by period number.
     */
    public function scopeForPeriod($query, $periodNumber)
    {
        return $query->where('period_number', $periodNumber);
    }
}
