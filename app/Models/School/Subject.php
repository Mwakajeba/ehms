<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Subject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'short_name',
        'subject_type',
        'requirement_type',
        'sort_order',
        'description',
        'type',
        'credit_hours',
        'theory_hours',
        'practical_hours',
        'passing_marks',
        'total_marks',
        'is_active',
        'subject_group_id',
        'company_id',
        'branch_id',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credit_hours' => 'integer',
        'theory_hours' => 'integer',
        'practical_hours' => 'integer',
        'passing_marks' => 'decimal:2',
        'total_marks' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Get the subject groups this subject belongs to.
     */
    public function subjectGroups(): BelongsToMany
    {
        return $this->belongsToMany(SubjectGroup::class, 'subject_subject_group');
    }

    /**
     * Get the subject group this subject belongs to (for backward compatibility).
     */
    public function subjectGroup(): BelongsTo
    {
        return $this->belongsTo(SubjectGroup::class);
    }

    /**
     * Get the company that owns the subject.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the subject.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the user who created the subject.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the curricula for the subject.
     */
    public function curricula(): HasMany
    {
        return $this->hasMany(Curriculum::class);
    }

    /**
     * Get the subject teachers for this subject.
     */
    public function subjectTeachers(): HasMany
    {
        return $this->hasMany(SubjectTeacher::class, 'subject_id');
    }

    /**
     * Scope a query to only include active subjects.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include subjects for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include subjects for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Get the hash ID for the subject
     *
     * @return string
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Find a subject by its hashid
     *
     * @param string $hashid
     * @param array $with
     * @return \App\Models\School\Subject|null
     */
    public static function findByHashid($hashid, $with = [])
    {
        $decoded = Hashids::decode($hashid);
        $id = $decoded[0] ?? null;

        if (!$id) {
            return null;
        }

        $query = static::query();
        
        if (!empty($with)) {
            $query->with($with);
        }

        return $query->find($id);
    }
}
