<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class SubjectGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'class_id',
        'is_active',
        'company_id',
        'branch_id',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the subjects for the subject group.
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_subject_group')
            ->withPivot('sort_order')
            ->orderBy('subject_subject_group.sort_order')
            ->orderBy('subjects.name');
    }

    /**
     * Get the class that owns the subject group.
     */
    public function classe()
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Get the company that owns the subject group.
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the subject group.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Scope a query to only include active subject groups.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include subject groups for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include subject groups for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Get the hash ID for the subject group
     *
     * @return string
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Find a subject group by its hashid
     *
     * @param string $hashid
     * @param array $with
     * @return \App\Models\School\SubjectGroup|null
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
