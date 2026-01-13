<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamScheduleSession extends Model
{
    protected $fillable = [
        'exam_schedule_id',
        'session_date',
        'session_name',
        'start_time',
        'end_time',
        'is_half_day',
        'order',
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_half_day' => 'boolean',
    ];

    /**
     * Get the exam schedule that owns this session.
     */
    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    /**
     * Get the papers scheduled in this session.
     */
    public function papers(): HasMany
    {
        return $this->hasMany(ExamSchedulePaper::class);
    }
}

