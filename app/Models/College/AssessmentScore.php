<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class AssessmentScore extends Model
{
    protected $fillable = [
        'course_assessment_id',
        'course_registration_id',
        'student_id',
        'course_id',
        'score',
        'max_marks',
        'weighted_score',
        'status',
        'remarks',
        'submitted_date',
        'marked_by',
        'marked_date',
        'published_by',
        'published_date',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'weighted_score' => 'decimal:2',
        'marked_date' => 'date',
        'published_date' => 'date',
    ];

    // Relationships
    public function courseAssessment(): BelongsTo
    {
        return $this->belongsTo(CourseAssessment::class);
    }

    public function courseRegistration(): BelongsTo
    {
        return $this->belongsTo(CourseRegistration::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    // Scopes
    public function scopeMarked($query)
    {
        return $query->where('status', 'marked');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Helper Methods
    public function calculateWeightedScore(): float
    {
        if ($this->max_marks == 0) {
            return 0;
        }

        $assessment = $this->courseAssessment;
        $percentage = ($this->score / $this->max_marks) * 100;
        return ($percentage / 100) * $assessment->weight_percentage;
    }

    public function getPercentageAttribute(): float
    {
        if ($this->max_marks == 0) {
            return 0;
        }
        return ($this->score / $this->max_marks) * 100;
    }
}
