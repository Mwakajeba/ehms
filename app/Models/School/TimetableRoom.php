<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class TimetableRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_code',
        'room_name',
        'description',
        'capacity',
        'room_type',
        'assigned_class_id',
        'is_shared',
        'equipment',
        'is_active',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'is_active' => 'boolean',
        'equipment' => 'array',
    ];

    /**
     * Get the class assigned to this room.
     */
    public function assignedClass(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'assigned_class_id');
    }

    /**
     * Get the company that owns the room.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the room.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Get the timetable entries for this room.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TimetableEntry::class, 'room_id');
    }

    /**
     * Scope a query to only include active rooms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include rooms for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include rooms for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });
    }

    /**
     * Get the hash ID for the room
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }
}
