<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolExamMark extends Model
{
    protected $fillable = [
        'exam_class_assignment_id',
        'student_id',
        'marks_obtained',
        'max_marks',
        'company_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'max_marks' => 'decimal:2',
    ];

    /**
     * Get the exam class assignment that owns the mark.
     */
    public function examClassAssignment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ExamClassAssignment::class);
    }

    /**
     * Get the student that owns the mark.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Student::class);
    }

    /**
     * Get the company that owns the mark.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the mark.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the user who created the mark.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
