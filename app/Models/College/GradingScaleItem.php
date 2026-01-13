<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingScaleItem extends Model
{
    protected $fillable = [
        'grading_scale_id',
        'min_marks',
        'max_marks',
        'grade_letter',
        'remark',
        'gpa_points',
        'pass_status',
    ];

    protected $casts = [
        'min_marks' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'gpa_points' => 'decimal:2',
    ];

    // Relationships
    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }

    // Helper Methods
    public function isPassing(): bool
    {
        return $this->pass_status === 'pass';
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->grade_letter} ({$this->min_marks}-{$this->max_marks})";
    }
}
