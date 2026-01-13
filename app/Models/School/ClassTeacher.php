<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinkla\Hashids\Facades\Hashids;

class ClassTeacher extends Model
{
    protected $fillable = [
        'employee_id',
        'class_id',
        'stream_id',
        'academic_year_id',
        'branch_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'hashid';
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $id = Hashids::decode($value)[0] ?? null;
        return $this->where('id', $id)->firstOrFail();
    }

    /**
     * Get the hashid attribute.
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Get the employee (teacher) assigned to this class.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Hr\Employee::class, 'employee_id');
    }

    /**
     * Get the class this teacher is assigned to.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Get the stream this teacher is assigned to.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    /**
     * Get the academic year for this assignment.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\AcademicYear::class, 'academic_year_id');
    }

    /**
     * Get the branch for this assignment.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class, 'branch_id');
    }
}
