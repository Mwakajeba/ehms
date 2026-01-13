<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolGrade extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'grade_scale_id',
        'grade_letter',
        'grade_name',
        'min_marks',
        'max_marks',
        'grade_point',
        'remarks',
        'sort_order',
    ];

    protected $casts = [
        'min_marks' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get the grade scale that owns the grade.
     */
    public function gradeScale(): BelongsTo
    {
        return $this->belongsTo(SchoolGradeScale::class, 'grade_scale_id');
    }

    /**
     * Check if a mark falls within this grade range.
     */
    public function containsMark(float $mark): bool
    {
        return $mark >= $this->min_marks && $mark <= $this->max_marks;
    }
}
