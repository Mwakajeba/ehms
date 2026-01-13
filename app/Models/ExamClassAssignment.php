<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ExamClassAssignment extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'exam_type_id',
        'class_id',
        'subject_id',
        'academic_year_id',
        'stream_id',
        'status',
        'assigned_date',
        'due_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Get the company that owns the assignment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the assignment.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the exam type for this assignment.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolExamType::class, 'exam_type_id');
    }

    /**
     * Get the class for this assignment.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Classe::class, 'class_id');
    }

    /**
     * Get the subject for this assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Subject::class, 'subject_id');
    }

    /**
     * Get the academic year for this assignment.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get the stream for this assignment.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Stream::class, 'stream_id');
    }

    /**
     * Get the user who created this assignment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the exam registrations for this assignment.
     */
    public function examRegistrations()
    {
        return $this->hasMany(\App\Models\School\SchoolExamRegistration::class, 'exam_class_assignment_id');
    }

    /**
     * Get the exam marks for this assignment.
     */
    public function examMarks()
    {
        return $this->hasMany(\App\Models\SchoolExamMark::class, 'exam_class_assignment_id');
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany(Builder $query, $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeForBranch(Builder $query, $branchId): Builder
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });
    }

    /**
     * Scope to filter by exam type.
     */
    public function scopeForExamType(Builder $query, $examTypeId): Builder
    {
        return $query->where('exam_type_id', $examTypeId);
    }

    /**
     * Scope to filter by class.
     */
    public function scopeForClass(Builder $query, $classId): Builder
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope to filter by academic year.
     */
    public function scopeForAcademicYear(Builder $query, $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active assignments.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['assigned', 'in_progress']);
    }

    /**
     * Check if assignment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Get status badge HTML.
     */
    public function getStatusBadge(): string
    {
        $badges = [
            'assigned' => '<span class="badge bg-primary">Assigned</span>',
            'in_progress' => '<span class="badge bg-warning">In Progress</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-light">Unknown</span>';
    }

    /**
     * Get display name for the assignment.
     */
    public function getDisplayNameAttribute(): string
    {
        $examTypeName = $this->examType->name ?? 'Unknown Exam Type';
        $className = $this->classe->name ?? 'Unknown Class';
        $subjectName = $this->subject->name ?? 'Unknown Subject';

        return "{$examTypeName} - {$className} - {$subjectName}";
    }
}
