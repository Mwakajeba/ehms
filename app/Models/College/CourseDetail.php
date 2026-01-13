<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Hr\Employee;
use App\Models\User;

class CourseDetail extends Model
{
    protected $table = 'course_details';

    protected $fillable = [
        'course_id',
        'employee_id',
        'academic_year',
        'semester',
        'date_assigned',
        'status',
        'assigned_by',
        'notes',
    ];

    protected $casts = [
        'date_assigned' => 'datetime',
    ];

    /**
     * Get the course that this detail belongs to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the employee (instructor) assigned to this course
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who made the assignment
     */
    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Scope for active assignments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for archived assignments
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope for current academic year
     */
    public function scopeCurrentYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Scope for specific semester
     */
    public function scopeForSemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }
}
