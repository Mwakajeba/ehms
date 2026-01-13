<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolGradeScale extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'name',
        'academic_year_id',
        'max_marks',
        'passed_average_point',
        'description',
        'is_active',
    ];

    protected $casts = [
        'max_marks' => 'decimal:2',
        'passed_average_point' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the academic year that owns the grade scale.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\AcademicYear::class);
    }

    /**
     * Get the grades for the grade scale.
     */
    public function grades(): HasMany
    {
        return $this->hasMany(SchoolGrade::class, 'grade_scale_id')->orderBy('sort_order');
    }

    /**
     * Get the grade for a given mark.
     */
    public function getGradeForMark(float $mark): ?SchoolGrade
    {
        return $this->grades()
            ->where('min_marks', '<=', $mark)
            ->where('max_marks', '>=', $mark)
            ->first();
    }

    /**
     * Scope a query to only include active grade scales.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include grade scales for a specific academic year.
     */
    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }
}
