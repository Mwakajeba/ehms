<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\College\ExamSchedule;

class FinalExamScore extends Model
{
    protected $fillable = [
        'final_exam_id',
        'exam_schedule_id',
        'course_registration_id',
        'student_id',
        'course_id',
        'score',
        'max_marks',
        'weighted_score',
        'status',
        'remarks',
        'marked_by',
        'marked_date',
        'published_by',
        'published_date',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_marks' => 'decimal:2',
        'weighted_score' => 'decimal:2',
        'marked_date' => 'date',
        'published_date' => 'date',
    ];

    // Relationships
    public function finalExam(): BelongsTo
    {
        return $this->belongsTo(FinalExam::class);
    }

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class);
    }

    public function courseRegistration(): BelongsTo
    {
        return $this->belongsTo(CourseRegistration::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
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
    public function scopeMarked($query)
    {
        return $query->where('status', 'marked');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    // Helper Methods
    public function calculateWeightedScore(): float
    {
        if ($this->max_marks == 0) {
            return 0;
        }

        $exam = $this->finalExam;
        $percentage = ($this->score / $this->max_marks) * 100;
        return ($percentage / 100) * $exam->weight_percentage;
    }

    public function getPercentageAttribute(): float
    {
        if ($this->max_marks == 0) {
            return 0;
        }
        return ($this->score / $this->max_marks) * 100;
    }

    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }
}
