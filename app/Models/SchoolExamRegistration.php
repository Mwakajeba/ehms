<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolExamRegistration extends Model
{
    protected $fillable = [
        'exam_class_assignment_id',
        'student_id',
        'academic_year_id',
        'exam_type_id',
        'status',
        'reason',
        'company_id',
        'branch_id',
        'created_by'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the exam class assignment this registration belongs to.
     */
    public function examClassAssignment(): BelongsTo
    {
        return $this->belongsTo(ExamClassAssignment::class);
    }

    /**
     * Get the student this registration belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the company this registration belongs to.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch this registration belongs to.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created this registration.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the status badge for display.
     */
    public function getStatusBadge(): string
    {
        return match($this->status) {
            'registered' => '<span class="badge bg-primary">Registered</span>',
            'exempted' => '<span class="badge bg-warning">Exempted</span>',
            'absent' => '<span class="badge bg-danger">Absent</span>',
            'attended' => '<span class="badge bg-success">Attended</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>'
        };
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });
    }

    /**
     * Scope to get only students who should appear in results (attended or registered).
     */
    public function scopeShouldAppearInResults($query)
    {
        return $query->whereIn('status', ['attended', 'registered']);
    }

    /**
     * Scope to get exempted students.
     */
    public function scopeExempted($query)
    {
        return $query->where('status', 'exempted');
    }

    /**
     * Scope to get absent students.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }
}
