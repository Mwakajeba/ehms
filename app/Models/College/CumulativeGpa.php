<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Company;

class CumulativeGpa extends Model
{
    protected $table = 'cumulative_gpa';

    protected $fillable = [
        'student_id',
        'program_id',
        'total_credits_attempted',
        'total_credits_earned',
        'total_quality_points',
        'cgpa',
        'total_courses_passed',
        'total_courses_failed',
        'semesters_completed',
        'class_of_award',
        'academic_standing',
        'current_academic_year_id',
        'current_semester_id',
        'current_level',
        'program_status',
        'last_calculated_date',
        'remarks',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'total_quality_points' => 'decimal:2',
        'cgpa' => 'decimal:2',
        'last_calculated_date' => 'date',
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

    public function currentAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'current_academic_year_id');
    }

    public function currentSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'current_semester_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Helper Methods
    public function calculateCGPA(): void
    {
        if ($this->total_credits_attempted > 0) {
            $this->cgpa = round($this->total_quality_points / $this->total_credits_attempted, 2);
        } else {
            $this->cgpa = 0;
        }

        $this->determineClassOfAward();
        $this->determineAcademicStanding();
    }

    public function determineClassOfAward(): void
    {
        if ($this->cgpa >= 4.50) {
            $this->class_of_award = 'First Class Honours';
        } elseif ($this->cgpa >= 3.50) {
            $this->class_of_award = 'Second Class Upper';
        } elseif ($this->cgpa >= 2.50) {
            $this->class_of_award = 'Second Class Lower';
        } elseif ($this->cgpa >= 2.00) {
            $this->class_of_award = 'Third Class';
        } elseif ($this->cgpa >= 1.00) {
            $this->class_of_award = 'Pass';
        } else {
            $this->class_of_award = 'Fail';
        }
    }

    public function determineAcademicStanding(): void
    {
        if ($this->cgpa >= 3.50) {
            $this->academic_standing = 'Good Standing';
        } elseif ($this->cgpa >= 2.00) {
            $this->academic_standing = 'Satisfactory';
        } elseif ($this->cgpa >= 1.50) {
            $this->academic_standing = 'Probation';
        } else {
            $this->academic_standing = 'Academic Warning';
        }
    }

    public function getOverallPassRateAttribute(): float
    {
        $totalCourses = $this->total_courses_passed + $this->total_courses_failed;
        if ($totalCourses == 0) {
            return 0;
        }
        return ($this->total_courses_passed / $totalCourses) * 100;
    }
}
