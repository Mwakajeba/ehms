<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'program_id',
        'code',
        'name',
        'credit_hours',
        'semester',
        'level',
        'prerequisites',
        'core_elective',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'credit_hours' => 'integer',
        'semester' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the program that owns the course
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    /**
     * Get the user who created the course
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who last updated the course
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    /**
     * Get the enrollments for the course
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class, 'course_id');
    }

    /**
     * Get the active enrollments for the course
     */
    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class, 'course_id')
            ->where('status', 'enrolled');
    }

    /**
     * Scope to active courses
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by program
     */
    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope to filter by semester
     */
    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    /**
     * Scope to filter by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Get the course details (instructor assignments)
     */
    public function courseDetails(): HasMany
    {
        return $this->hasMany(CourseDetail::class, 'course_id');
    }

    /**
     * Get the active course details (current instructors)
     */
    public function activeInstructors(): HasMany
    {
        return $this->hasMany(CourseDetail::class, 'course_id')
            ->where('status', 'active');
    }

    /**
     * Get the current instructor for specific academic year and semester
     */
    public function currentInstructor($academicYear = null, $semester = null)
    {
        $query = $this->courseDetails()->active()->with('employee');
        
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }
        
        if ($semester) {
            $query->where('semester', $semester);
        }
        
        return $query->latest('date_assigned')->first();
    }
}
