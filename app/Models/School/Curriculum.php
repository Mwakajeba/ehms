<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Curriculum extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'objectives',
        'learning_outcomes',
        'content',
        'grade_level',
        'academic_year',
        'subject_id',
        'class_id',
        'stream_id',
        'is_active',
        'effective_from',
        'effective_to',
        'company_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get the subject that owns the curriculum.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class that owns the curriculum.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Get the stream that owns the curriculum.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Get the company that owns the curriculum.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the curriculum.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the user who created the curriculum.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scope a query to only include active curricula.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include curricula for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include curricula for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope a query to only include current curricula (effective now).
     */
    public function scopeCurrent($query)
    {
        return $query->where('effective_from', '<=', now())
                    ->where(function ($q) {
                        $q->where('effective_to', '>=', now())
                          ->orWhereNull('effective_to');
                    });
    }

    /**
     * Get the hash ID for the curriculum
     *
     * @return string
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Find a curriculum by its hashid
     *
     * @param string $hashid
     * @return \App\Models\School\Curriculum|null
     */
    public static function findByHashid($hashid)
    {
        $decoded = Hashids::decode($hashid);
        $id = $decoded[0] ?? null;

        return static::find($id);
    }
}
