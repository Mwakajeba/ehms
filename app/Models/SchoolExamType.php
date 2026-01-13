<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SchoolExamType extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'description',
        'weight',
        'is_active',
        'is_published',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
        'is_published' => 'boolean',
    ];

    /**
     * Get the company that owns the exam type.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the exam type.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the exams for this exam type.
     */
    public function exams(): HasMany
    {
        return $this->hasMany(SchoolExam::class, 'exam_type_id');
    }

    /**
     * Get the exam class assignments for this exam type.
     */
    public function examClassAssignments(): HasMany
    {
        return $this->hasMany(ExamClassAssignment::class, 'exam_type_id');
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
     * Scope to get only active exam types.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only published exam types.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Publish the exam type.
     */
    public function publish(): bool
    {
        return $this->update(['is_published' => true]);
    }

    /**
     * Unpublish the exam type.
     */
    public function unpublish(): bool
    {
        return $this->update(['is_published' => false]);
    }
}
