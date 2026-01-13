<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Hr\Employee;

class TimetableSlot extends Model
{
    use SoftDeletes;

    protected $table = 'college_timetable_slots';

    protected $fillable = [
        'timetable_id',
        'course_id',
        'venue_id',
        'instructor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_type',
        'group_name',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const SLOT_TYPES = [
        'lecture' => 'Lecture',
        'tutorial' => 'Tutorial',
        'practical' => 'Practical',
        'lab' => 'Laboratory',
        'seminar' => 'Seminar',
        'workshop' => 'Workshop',
        'exam' => 'Examination',
    ];

    const DAYS_OF_WEEK = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    // Relationships
    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'instructor_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeForCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeForVenue($query, $venueId)
    {
        return $query->where('venue_id', $venueId);
    }

    public function scopeForInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('slot_type', $type);
    }

    // Helper Methods
    public function getSlotTypeNameAttribute(): string
    {
        return self::SLOT_TYPES[$this->slot_type] ?? $this->slot_type;
    }

    public function getDurationAttribute(): int
    {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);
        return ($end - $start) / 60; // Duration in minutes
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration / 60, 1);
    }

    public function getTimeRangeAttribute(): string
    {
        return date('H:i', strtotime($this->start_time)) . ' - ' . date('H:i', strtotime($this->end_time));
    }

    public function getFormattedStartTimeAttribute(): string
    {
        return date('h:i A', strtotime($this->start_time));
    }

    public function getFormattedEndTimeAttribute(): string
    {
        return date('h:i A', strtotime($this->end_time));
    }

    /**
     * Get display label for the slot
     */
    public function getDisplayLabelAttribute(): string
    {
        $label = $this->course->code ?? 'N/A';
        if ($this->group_name) {
            $label .= " ({$this->group_name})";
        }
        return $label;
    }

    /**
     * Check if this slot conflicts with another time range
     */
    public function conflictsWith($startTime, $endTime): bool
    {
        $slotStart = strtotime($this->start_time);
        $slotEnd = strtotime($this->end_time);
        $checkStart = strtotime($startTime);
        $checkEnd = strtotime($endTime);

        // Check for overlap
        return !($checkEnd <= $slotStart || $checkStart >= $slotEnd);
    }

    /**
     * Get slot background color based on course or type
     */
    public function getColorAttribute(): string
    {
        $colors = [
            'lecture' => '#4e73df',
            'tutorial' => '#1cc88a',
            'practical' => '#36b9cc',
            'lab' => '#f6c23e',
            'seminar' => '#e74a3b',
            'workshop' => '#858796',
            'exam' => '#5a5c69',
        ];

        return $colors[$this->slot_type] ?? '#4e73df';
    }

    /**
     * Check if venue is available for this slot
     */
    public function isVenueAvailable(): bool
    {
        if (!$this->venue_id) {
            return true;
        }

        return $this->venue->isAvailable(
            $this->day_of_week,
            $this->start_time,
            $this->end_time,
            $this->id
        );
    }

    /**
     * Check if instructor is available for this slot
     */
    public function isInstructorAvailable(): bool
    {
        if (!$this->instructor_id) {
            return true;
        }

        // Check for other slots with same instructor at same time
        return !self::query()
            ->where('id', '!=', $this->id)
            ->where('instructor_id', $this->instructor_id)
            ->where('day_of_week', $this->day_of_week)
            ->where('is_active', true)
            ->whereHas('timetable', function ($q) {
                $q->where('status', 'published');
            })
            ->where(function ($q) {
                $q->where(function ($inner) {
                    $inner->where('start_time', '<=', $this->start_time)
                          ->where('end_time', '>', $this->start_time);
                })->orWhere(function ($inner) {
                    $inner->where('start_time', '<', $this->end_time)
                          ->where('end_time', '>=', $this->end_time);
                })->orWhere(function ($inner) {
                    $inner->where('start_time', '>=', $this->start_time)
                          ->where('end_time', '<=', $this->end_time);
                });
            })
            ->exists();
    }
}
