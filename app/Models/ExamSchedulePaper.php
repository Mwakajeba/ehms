<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSchedulePaper extends Model
{
    protected $fillable = [
        'exam_schedule_id',
        'exam_schedule_session_id',
        'exam_class_assignment_id',
        'class_id',
        'stream_id',
        'subject_id',
        'subject_name',
        'subject_code',
        'total_marks',
        'duration_minutes',
        'is_compulsory',
        'paper_type',
        'subject_priority',
        'is_heavy_subject',
        'scheduled_start_time',
        'scheduled_end_time',
        'venue',
        'number_of_students',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_marks' => 'decimal:2',
        'is_compulsory' => 'boolean',
        'is_heavy_subject' => 'boolean',
        'scheduled_start_time' => 'datetime:H:i',
        'scheduled_end_time' => 'datetime:H:i',
    ];

    /**
     * Get the exam schedule that owns this paper.
     */
    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    /**
     * Get the session for this paper.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ExamScheduleSession::class, 'exam_schedule_session_id');
    }

    /**
     * Get the exam class assignment for this paper.
     */
    public function examClassAssignment(): BelongsTo
    {
        return $this->belongsTo(ExamClassAssignment::class);
    }

    /**
     * Get the class for this paper.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Classe::class, 'class_id');
    }

    /**
     * Get the stream for this paper.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Stream::class, 'stream_id');
    }

    /**
     * Get the subject for this paper.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Subject::class, 'subject_id');
    }

    /**
     * Get the invigilations for this paper.
     */
    public function invigilations(): HasMany
    {
        return $this->hasMany(ExamInvigilation::class);
    }
}

