<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Vinkla\Hashids\Facades\Hashids;

class ExamSchedule extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'exam_type_id',
        'academic_year_id',
        'exam_name',
        'term',
        'exam_type_category',
        'start_date',
        'end_date',
        'exam_days',
        'has_half_day_exams',
        'min_break_minutes',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'exam_days' => 'array',
        'has_half_day_exams' => 'boolean',
    ];

    /**
     * Get the company that owns the schedule.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the schedule.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the exam type for this schedule.
     */
    public function examType(): BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolExamType::class, 'exam_type_id');
    }

    /**
     * Get the academic year for this schedule.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get the user who created this schedule.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the sessions for this schedule.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(ExamScheduleSession::class);
    }

    /**
     * Get the papers for this schedule.
     */
    public function papers(): HasMany
    {
        return $this->hasMany(ExamSchedulePaper::class);
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
     * Get status badge HTML.
     */
    public function getStatusBadge(): string
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'scheduled' => '<span class="badge bg-info">Scheduled</span>',
            'published' => '<span class="badge bg-primary">Published</span>',
            'ongoing' => '<span class="badge bg-warning">Ongoing</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-light">Unknown</span>';
    }

    /**
     * Get the hash ID for the exam schedule
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Find an exam schedule by its hashid
     */
    public static function findByHashid($hashid, $with = [])
    {
        $decoded = Hashids::decode($hashid);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return null;
        }

        $query = static::query();
        
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }
}

