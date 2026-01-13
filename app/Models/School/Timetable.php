<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Timetable extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'academic_year_id',
        'class_id',
        'stream_id',
        'timetable_type',
        'status',
        'company_id',
        'branch_id',
        'created_by',
        'reviewed_by',
        'approved_by',
        'reviewed_at',
        'approved_at',
        'published_at',
        'is_active',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'published_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the academic year for this timetable.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the class for this timetable.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    /**
     * Get the stream for this timetable.
     */
    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class);
    }

    /**
     * Get the company that owns the timetable.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the timetable.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the user who created the timetable.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who reviewed the timetable.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    /**
     * Get the user who approved the timetable.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get the periods for this timetable.
     */
    public function periods(): HasMany
    {
        return $this->hasMany(TimetablePeriod::class);
    }

    /**
     * Get the entries for this timetable.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class);
    }

    /**
     * Get the settings for this timetable.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(TimetableSetting::class);
    }

    /**
     * Scope a query to only include active timetables.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include timetables for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include timetables for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });
    }

    /**
     * Scope a query to only include published timetables.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Get the hash ID for the timetable
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    /**
     * Find a timetable by its hashid
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
