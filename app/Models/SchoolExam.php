<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class SchoolExam extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'academic_year_id',
        'exam_type_id',
        'subject_id',
        'class_id',
        'stream_id',
        'exam_name',
        'description',
        'exam_date',
        'start_time',
        'end_time',
        'max_marks',
        'pass_marks',
        'weight',
        'status',
        'instructions',
        'created_by',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'max_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
        'weight' => 'decimal:2',
        'status' => 'string',
    ];

    /**
     * Get the company that owns the exam.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the exam.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the academic year for this exam.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get the exam type for this exam.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(SchoolExamType::class, 'exam_type_id');
    }

    /**
     * Get the subject for this exam.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Subject::class, 'subject_id');
    }

    /**
     * Get the class for this exam.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Classe::class, 'class_id');
    }

    /**
     * Get the stream for this exam.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Stream::class, 'stream_id');
    }

    /**
     * Get the user who created this exam.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
     * Scope to filter by academic year.
     */
    public function scopeForAcademicYear(Builder $query, $academicYearId): Builder
    {
        return $query->where('academic_year_id', $academicYearId);
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
     * Scope to filter by subject.
     */
    public function scopeForSubject(Builder $query, $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus(Builder $query, $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active exams.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['draft', 'scheduled', 'ongoing']);
    }

    /**
     * Scope to get upcoming exams.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('exam_date', '>=', now()->toDateString())
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('exam_date')
                    ->orderBy('start_time');
    }

    /**
     * Scope to get today's exams.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->where('exam_date', now()->toDateString())
                    ->where('status', '!=', 'cancelled')
                    ->orderBy('start_time');
    }

    /**
     * Scope to get past exams.
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('exam_date', '<', now()->toDateString())
                    ->orWhere(function ($q) {
                        $q->where('exam_date', now()->toDateString())
                          ->where('end_time', '<', now()->toTimeString());
                    })
                    ->where('status', '!=', 'cancelled');
    }

    /**
     * Check if the exam is currently ongoing.
     */
    public function isOngoing(): bool
    {
        if ($this->exam_date->format('Y-m-d') !== now()->toDateString()) {
            return false;
        }

        if (!$this->start_time || !$this->end_time) {
            return false;
        }

        $now = now()->toTimeString();
        return $now >= $this->start_time->format('H:i:s') && $now <= $this->end_time->format('H:i:s');
    }

    /**
     * Check if the exam is completed.
     */
    public function isCompleted(): bool
    {
        if ($this->status === 'completed') {
            return true;
        }

        if ($this->exam_date->format('Y-m-d') < now()->toDateString()) {
            return true;
        }

        if ($this->exam_date->format('Y-m-d') === now()->toDateString() && $this->end_time) {
            return now()->toTimeString() > $this->end_time->format('H:i:s');
        }

        return false;
    }

    /**
     * Get the duration of the exam in minutes.
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->start_time || !$this->end_time) {
            return null;
        }

        $start = Carbon::createFromTimeString($this->start_time->format('H:i:s'));
        $end = Carbon::createFromTimeString($this->end_time->format('H:i:s'));

        return $start->diffInMinutes($end);
    }

    /**
     * Get the formatted exam time range.
     */
    public function getTimeRangeAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return 'Time not specified';
        }

        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }
}
