<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;

class CourseResult extends Model
{
    protected $fillable = [
        'course_registration_id',
        'student_id',
        'program_id',
        'course_id',
        'academic_year_id',
        'semester_id',
        'attempt_number',
        'credit_hours',
        'ca_total',
        'exam_total',
        'total_marks',
        'grade',
        'gpa_points',
        'remark',
        'course_status',
        'instructor_id',
        'is_retake',
        'result_status',
        'remarks',
        'published_by',
        'published_date',
        'approved_by',
        'approved_date',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'ca_total' => 'decimal:2',
        'exam_total' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'gpa_points' => 'decimal:2',
        'is_retake' => 'boolean',
        'published_date' => 'date',
        'approved_date' => 'date',
    ];

    // Relationships
    public function courseRegistration(): BelongsTo
    {
        return $this->belongsTo(CourseRegistration::class);
    }

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

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
    public function scopePublished($query)
    {
        return $query->where('result_status', 'published');
    }

    public function scopeApproved($query)
    {
        return $query->where('result_status', 'approved');
    }

    public function scopePassed($query)
    {
        return $query->where('course_status', 'passed');
    }

    public function scopeFailed($query)
    {
        return $query->where('course_status', 'failed');
    }

    // Helper Methods
    public function isPassed(): bool
    {
        return $this->course_status === 'passed';
    }

    public function isApproved(): bool
    {
        return $this->result_status === 'approved';
    }

    public function getQualityPointsAttribute(): float
    {
        return $this->credit_hours * $this->gpa_points;
    }

    public function calculateResult(GradingScale $gradingScale): void
    {
        // Calculate total marks
        $this->total_marks = $this->ca_total + $this->exam_total;

        // Get grade from grading scale
        $gradeItem = $gradingScale->getGradeForMarks($this->total_marks);

        if ($gradeItem) {
            $this->grade = $gradeItem->grade_letter;
            $this->gpa_points = $gradeItem->gpa_points;
            $this->remark = $gradeItem->remark;
            $this->course_status = $gradeItem->isPassing() ? 'passed' : 'failed';
        }
    }
}
