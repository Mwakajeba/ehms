<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentClass extends Model
{
    protected $fillable = [
        'assignment_id',
        'class_id',
        'stream_id',
        'extended_due_date',
        'extended_due_time',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'extended_due_date' => 'date',
        'extended_due_time' => 'datetime:H:i',
    ];

    /**
     * Get the assignment for this class assignment.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the class for this assignment.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Get the stream for this assignment.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Get the company that owns this assignment class.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns this assignment class.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}
