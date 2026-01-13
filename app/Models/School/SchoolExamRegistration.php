<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

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
        'created_by',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the exam class assignment that owns the registration.
     */
    public function examClassAssignment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\ExamClassAssignment::class);
    }

    /**
     * Get the student that owns the registration.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Student::class);
    }

    /**
     * Get the company that owns the registration.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the registration.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the user who created the registration.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope to filter by current branch (session or user default).
     */
    public function scopeForCurrentBranch($query)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to filter by company and current branch.
     */
    public function scopeForCurrentCompanyAndBranch($query)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        return $query->where('company_id', $companyId)
                    ->where('branch_id', $branchId);
    }

    /**
     * Check if the student is registered for the exam.
     */
    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    /**
     * Check if the student is exempted from the exam.
     */
    public function isExempted(): bool
    {
        return $this->status === 'exempted';
    }

    /**
     * Check if the student was absent from the exam.
     */
    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }

    /**
     * Check if the student attended the exam.
     */
    public function isAttended(): bool
    {
        return $this->status === 'attended';
    }
}