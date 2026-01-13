<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class AssignmentSubmission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'class_id',
        'stream_id',
        'submission_type',
        'submission_content',
        'submitted_at',
        'is_late',
        'attempt_number',
        'is_resubmission',
        'status',
        'marks_obtained',
        'percentage',
        'grade',
        'remarks',
        'teacher_comments',
        'corrections',
        'voice_feedback_path',
        'improvement_suggestions',
        'marked_at',
        'marked_by',
        'extended_due_date',
        'extended_due_time',
        'company_id',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_late' => 'boolean',
        'is_resubmission' => 'boolean',
        'marks_obtained' => 'decimal:2',
        'percentage' => 'decimal:2',
        'marked_at' => 'datetime',
        'extended_due_date' => 'date',
        'extended_due_time' => 'datetime:H:i',
    ];

    /**
     * Get the assignment for this submission.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student for this submission.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the class for this submission.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Get the stream for this submission.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Get the user who marked this submission.
     */
    public function marker(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'marked_by');
    }

    /**
     * Get all attachments for this submission.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(AssignmentAttachment::class, 'attachable_id')
            ->where('attachable_type', self::class);
    }

    /**
     * Get hashid for route model binding.
     */
    public function getHashidAttribute(): string
    {
        return Hashids::encode($this->id);
    }

    /**
     * Find submission by hashid.
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
     * Check if submission is late.
     */
    public function checkIfLate(): bool
    {
        if (!$this->assignment || !$this->submitted_at) {
            return false;
        }

        $dueDateTime = $this->extended_due_date 
            ? $this->extended_due_date->setTimeFromTimeString($this->extended_due_time ?? '23:59:59')
            : $this->assignment->due_date->setTimeFromTimeString($this->assignment->due_time ?? '23:59:59');

        return $this->submitted_at->isAfter($dueDateTime);
    }

    /**
     * Calculate percentage.
     */
    public function calculatePercentage(): ?float
    {
        if (!$this->marks_obtained || !$this->assignment->total_marks) {
            return null;
        }

        return ($this->marks_obtained / $this->assignment->total_marks) * 100;
    }

    /**
     * Determine grade based on percentage.
     */
    public function determineGrade(): ?string
    {
        if (!$this->percentage) {
            return null;
        }

        if ($this->percentage >= 90) return 'A';
        if ($this->percentage >= 80) return 'B';
        if ($this->percentage >= 70) return 'C';
        if ($this->percentage >= 60) return 'D';
        return 'E';
    }
}
