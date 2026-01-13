<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Assignment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'assignment_id',
        'title',
        'type',
        'description',
        'instructions',
        'academic_year_id',
        'term',
        'subject_id',
        'teacher_id',
        'date_assigned',
        'due_date',
        'due_time',
        'estimated_completion_time',
        'is_recurring',
        'recurring_schedule',
        'submission_type',
        'resubmission_allowed',
        'max_attempts',
        'lock_after_deadline',
        'total_marks',
        'passing_marks',
        'rubric',
        'auto_graded',
        'one_per_subject_per_day',
        'homework_load_limit_per_day',
        'exclude_holidays',
        'exclude_weekends',
        'status',
        'is_active',
        'company_id',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_assigned' => 'date',
        'due_date' => 'date',
        'due_time' => 'datetime:H:i',
        'is_recurring' => 'boolean',
        'recurring_schedule' => 'array',
        'resubmission_allowed' => 'boolean',
        'lock_after_deadline' => 'boolean',
        'total_marks' => 'decimal:2',
        'passing_marks' => 'decimal:2',
        'auto_graded' => 'boolean',
        'one_per_subject_per_day' => 'boolean',
        'exclude_holidays' => 'boolean',
        'exclude_weekends' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the academic year for this assignment.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the subject for this assignment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher for this assignment.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Hr\Employee::class, 'teacher_id');
    }

    /**
     * Get the company that owns the assignment.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the assignment.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the user who created the assignment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who updated the assignment.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Get all submissions for this assignment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Get all attachments for this assignment.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class, 'attachable_id')
            ->where('attachable_type', self::class);
    }

    /**
     * Get all classes assigned to this assignment.
     */
    public function assignmentClasses(): HasMany
    {
        return $this->hasMany(AssignmentClass::class);
    }

    /**
     * Get classes through assignment_classes pivot.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classe::class, 'assignment_classes', 'assignment_id', 'class_id')
            ->withPivot('stream_id', 'extended_due_date', 'extended_due_time')
            ->withTimestamps();
    }

    /**
     * Get streams through assignment_classes pivot.
     */
    public function streams(): BelongsToMany
    {
        return $this->belongsToMany(Stream::class, 'assignment_classes', 'assignment_id', 'stream_id')
            ->withPivot('class_id', 'extended_due_date', 'extended_due_time')
            ->withTimestamps();
    }

    /**
     * Get hashid for route model binding.
     */
    public function getHashidAttribute(): string
    {
        return Hashids::encode($this->id);
    }

    /**
     * Find assignment by hashid.
     */
    public static function findByHashid($hashid, $with = [])
    {
        $decoded = Hashids::decode($hashid);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return null;
        }

        $query = static::with($with);
        return $query->find($id);
    }

    /**
     * Scope a query to only include assignments for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include assignments for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });
    }

    /**
     * Scope a query to only include active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include published assignments.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Check if assignment is overdue.
     */
    public function isOverdue(): bool
    {
        if (!$this->due_date) {
            return false;
        }

        $dueDateTime = $this->due_date;
        if ($this->due_time) {
            $dueDateTime = $this->due_date->setTimeFromTimeString($this->due_time);
        }

        return now()->isAfter($dueDateTime);
    }

    /**
     * Generate unique assignment ID.
     */
    public static function generateAssignmentId(): string
    {
        $prefix = 'ASS';
        $year = date('Y');
        $lastAssignment = static::where('assignment_id', 'like', "{$prefix}-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastAssignment) {
            $lastNumber = (int) substr($lastAssignment->assignment_id, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $newNumber);
    }
}
