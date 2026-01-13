<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamInvigilation extends Model
{
    protected $fillable = [
        'exam_schedule_paper_id',
        'invigilator_id',
        'role',
        'is_subject_teacher',
        'assigned_start_time',
        'assigned_end_time',
        'notes',
    ];

    protected $casts = [
        'is_subject_teacher' => 'boolean',
        'assigned_start_time' => 'datetime:H:i',
        'assigned_end_time' => 'datetime:H:i',
    ];

    /**
     * Get the exam schedule paper that owns this invigilation.
     */
    public function examSchedulePaper(): BelongsTo
    {
        return $this->belongsTo(ExamSchedulePaper::class);
    }

    /**
     * Get the invigilator (employee).
     */
    public function invigilator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Hr\Employee::class, 'invigilator_id');
    }
}

