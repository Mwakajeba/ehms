<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;

class Venue extends Model
{
    use SoftDeletes;

    protected $table = 'college_venues';

    protected $fillable = [
        'name',
        'code',
        'building',
        'floor',
        'capacity',
        'venue_type',
        'facilities',
        'is_active',
        'company_id',
        'branch_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'facilities' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Venue types
     */
    const VENUE_TYPES = [
        'lecture_hall' => 'Lecture Hall',
        'lab' => 'Laboratory',
        'computer_lab' => 'Computer Lab',
        'seminar_room' => 'Seminar Room',
        'auditorium' => 'Auditorium',
        'classroom' => 'Classroom',
        'workshop' => 'Workshop',
        'library' => 'Library',
        'other' => 'Other',
    ];

    /**
     * Available facilities
     */
    const FACILITIES = [
        'projector' => 'Projector',
        'whiteboard' => 'Whiteboard',
        'smart_board' => 'Smart Board',
        'smartboard' => 'Smart Board',
        'computers' => 'Computers',
        'computer' => 'Computer',
        'air_conditioning' => 'Air Conditioning',
        'wifi' => 'WiFi',
        'internet' => 'Internet/WiFi',
        'video_conferencing' => 'Video Conferencing',
        'audio_system' => 'Audio System',
        'sound_system' => 'Sound System',
        'microphone' => 'Microphone',
        'lab_equipment' => 'Lab Equipment',
    ];

    // Relationships
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function timetableSlots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class, 'venue_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('venue_type', $type);
    }

    public function scopeWithCapacity($query, $minCapacity)
    {
        return $query->where('capacity', '>=', $minCapacity);
    }

    // Helper Methods
    public function getFullNameAttribute(): string
    {
        $name = $this->name;
        if ($this->building) {
            $name .= " - {$this->building}";
        }
        if ($this->floor) {
            $name .= " ({$this->floor})";
        }
        return $name;
    }

    public function getVenueTypeNameAttribute(): string
    {
        return self::VENUE_TYPES[$this->venue_type] ?? $this->venue_type;
    }

    /**
     * Check if venue is available at a specific time
     */
    public function isAvailable($dayOfWeek, $startTime, $endTime, $excludeSlotId = null)
    {
        $query = $this->timetableSlots()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->whereHas('timetable', function ($q) {
                $q->where('status', 'published');
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($inner) use ($startTime, $endTime) {
                    // New slot starts during existing slot
                    $inner->where('start_time', '<=', $startTime)
                          ->where('end_time', '>', $startTime);
                })->orWhere(function ($inner) use ($startTime, $endTime) {
                    // New slot ends during existing slot
                    $inner->where('start_time', '<', $endTime)
                          ->where('end_time', '>=', $endTime);
                })->orWhere(function ($inner) use ($startTime, $endTime) {
                    // New slot completely contains existing slot
                    $inner->where('start_time', '>=', $startTime)
                          ->where('end_time', '<=', $endTime);
                });
            });

        if ($excludeSlotId) {
            $query->where('id', '!=', $excludeSlotId);
        }

        return $query->count() === 0;
    }

    /**
     * Get schedule for a specific day
     */
    public function getScheduleForDay($dayOfWeek, $timetableId = null)
    {
        $query = $this->timetableSlots()
            ->with(['course', 'instructor', 'timetable.program'])
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true);

        if ($timetableId) {
            $query->where('timetable_id', $timetableId);
        }

        return $query->orderBy('start_time')->get();
    }
}
