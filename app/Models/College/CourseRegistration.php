<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Branch;
use App\Models\Company;

class CourseRegistration extends Model
{
    protected $fillable = [
        'student_id',
        'program_id',
        'course_id',
        'academic_year_id',
        'semester_id',
        'attempt_number',
        'registration_date',
        'credit_hours',
        'is_retake',
        'status',
        'instructor_id',
        'remarks',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'is_retake' => 'boolean',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function assessmentScores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class);
    }

    public function finalExamScore(): HasOne
    {
        return $this->hasOne(FinalExamScore::class);
    }

    public function courseResult(): HasOne
    {
        return $this->hasOne(CourseResult::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('registration_status', 'registered');
    }

    public function scopeRetakes($query)
    {
        return $query->where('is_retake', true);
    }
}
