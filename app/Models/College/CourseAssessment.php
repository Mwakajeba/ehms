<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Branch;
use App\Models\Company;
use App\Models\College\Course;
use App\Models\User;

class CourseAssessment extends Model
{
    protected $fillable = [
        'course_id',
        'program_id',
        'academic_year_id',
        'semester_id',
        'assessment_type_id',
        'title',
        'description',
        'weight_percentage',
        'max_marks',
        'assessment_date',
        'due_date',
        'instructor_id',
        'status',
        'published_by',
        'published_date',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'weight_percentage' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'assessment_date' => 'date',
        'due_date' => 'date',
        'published_date' => 'date',
    ];

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function assessmentType(): BelongsTo
    {
        return $this->belongsTo(AssessmentType::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'instructor_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    // Helper Methods
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
