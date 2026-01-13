<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Course;
use App\Models\HrEmployee;
use App\Models\User;

class FinalExam extends Model
{
    protected $fillable = [
        'course_id',
        'program_id',
        'academic_year_id',
        'semester_id',
        'exam_code',
        'exam_date',
        'exam_time',
        'duration_minutes',
        'max_marks',
        'weight_percentage',
        'venue',
        'instructions',
        'invigilator_id',
        'status',
        'published_by',
        'published_date',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'max_marks' => 'decimal:2',
        'weight_percentage' => 'decimal:2',
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

    public function invigilator(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'invigilator_id');
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
        return $this->hasMany(FinalExamScore::class);
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper Methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getExamDateTimeAttribute(): string
    {
        return $this->exam_date->format('M d, Y') . ' at ' . $this->exam_time;
    }
}
