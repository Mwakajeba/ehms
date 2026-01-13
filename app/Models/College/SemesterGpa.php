<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;

class SemesterGpa extends Model
{
    protected $table = 'semester_gpa';

    protected $fillable = [
        'student_id',
        'program_id',
        'academic_year_id',
        'semester_id',
        'total_credits_attempted',
        'total_credits_earned',
        'total_quality_points',
        'semester_gpa',
        'courses_passed',
        'courses_failed',
        'total_courses',
        'status',
        'published_by',
        'published_date',
        'remarks',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'total_quality_points' => 'decimal:2',
        'semester_gpa' => 'decimal:2',
        'published_date' => 'date',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(CollegeStudent::class);
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

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Helper Methods
    public function calculateGPA(): void
    {
        if ($this->total_credits_attempted > 0) {
            $this->semester_gpa = round($this->total_quality_points / $this->total_credits_attempted, 2);
        } else {
            $this->semester_gpa = 0;
        }
    }

    public function getPassRateAttribute(): float
    {
        if ($this->total_courses == 0) {
            return 0;
        }
        return ($this->courses_passed / $this->total_courses) * 100;
    }
}
