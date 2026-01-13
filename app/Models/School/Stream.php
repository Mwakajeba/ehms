<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinkla\Hashids\Facades\Hashids;

class Stream extends Model
{
    protected $fillable = ['name', 'description', 'company_id', 'branch_id', 'is_active'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(Classe::class, 'class_stream', 'stream_id', 'class_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'stream_id');
    }

    public function examClassAssignments()
    {
        return $this->hasMany(\App\Models\ExamClassAssignment::class, 'stream_id');
    }

    public function sections()
    {
        // For now, return an empty collection - sections are managed at class level
        return collect([]);
    }

    public function enrollments()
    {
        return $this->hasManyThrough(
            Enrollment::class,
            Classe::class,
            'id', // Foreign key on class_stream table
            'class_id', // Foreign key on enrollments table
            'id', // Local key on streams table
            'id' // Local key on classes table
        )->join('class_stream', 'classes.id', '=', 'class_stream.class_id')
         ->where('class_stream.stream_id', $this->id);
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
        $decoded = Hashids::decode($value);
        $id = $decoded[0] ?? null;

        return $this->where('id', $id)->firstOrFail();
    }
}
