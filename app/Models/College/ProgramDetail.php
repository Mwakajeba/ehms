<?php

namespace App\Models\College;

use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramDetail extends Model
{
    use HasFactory;

    protected $table = 'program_details';

    protected $fillable = [
        'program_id',
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
     * Get the program that owns this detail.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the employee (instructor) for this assignment.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who assigned this instructor.
     */
    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
