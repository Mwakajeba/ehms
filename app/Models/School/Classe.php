<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Vinkla\Hashids\Facades\Hashids;

class Classe extends Model
{
    protected $fillable = [
        'name',
        'level',
        'is_active',
        'company_id',
        'branch_id',
        'created_by'
    ];

    public function classSections(): HasMany
    {
        return $this->hasMany(ClassSection::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'class_sections', 'class_id', 'section_id');
    }

    public function streams(): BelongsToMany
    {
        return $this->belongsToMany(Stream::class, 'class_stream', 'class_id', 'stream_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function examClassAssignments(): HasMany
    {
        return $this->hasMany(\App\Models\ExamClassAssignment::class, 'class_id');
    }

    /**
     * Get attendance sessions for this class.
     */
    public function attendanceSessions(): HasMany
    {
        return $this->hasMany(AttendanceSession::class, 'class_id');
    }

    /**
     * Get exam registrations for students in this class.
     */
    public function examRegistrations(): HasMany
    {
        return $this->hasManyThrough(SchoolExamRegistration::class, Student::class, 'class_id', 'student_id');
    }

    /**
     * Get the company that owns the class.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the class.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Scope a query to only include active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include classes for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include classes for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
