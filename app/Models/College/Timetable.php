<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;

class Timetable extends Model
{
    use SoftDeletes;

    protected $table = 'college_timetables';

    protected $fillable = [
        'program_id',
        'academic_year_id',
        'semester_id',
        'year_of_study',
        'name',
        'effective_from',
        'effective_to',
        'notes',
        'status',
        'company_id',
        'branch_id',
        'created_by',
        'updated_by',
        'published_by',
        'published_at',
    ];

    protected $casts = [
        'year_of_study' => 'integer',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED = 'archived';

    const STATUSES = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PUBLISHED => 'Published',
        self::STATUS_ARCHIVED => 'Archived',
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
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

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

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class, 'timetable_id');
    }

    public function activeSlots(): HasMany
    {
        return $this->hasMany(TimetableSlot::class, 'timetable_id')
            ->where('is_active', true);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeForProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForSemester($query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    public function scopeForYearOfStudy($query, $year)
    {
        return $query->where('year_of_study', $year);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_PUBLISHED]);
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('effective_from', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $today);
            });
    }

    // Helper Methods
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            self::STATUS_DRAFT => 'warning',
            self::STATUS_PUBLISHED => 'success',
            self::STATUS_ARCHIVED => 'secondary',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusNameAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Get slots organized by day
     */
    public function getSlotsByDay(): array
    {
        $slotsByDay = [];
        
        foreach (self::DAYS_OF_WEEK as $day) {
            $slotsByDay[$day] = $this->slots()
                ->with(['course', 'venue', 'instructor'])
                ->where('day_of_week', $day)
                ->orderBy('start_time')
                ->get();
        }

        return $slotsByDay;
    }

    /**
     * Get all time slots used in this timetable
     */
    public function getTimeSlots(): array
    {
        $slots = $this->activeSlots()
            ->select('start_time', 'end_time')
            ->distinct()
            ->orderBy('start_time')
            ->get();

        return $slots->map(function ($slot) {
            return [
                'start' => $slot->start_time,
                'end' => $slot->end_time,
                'label' => date('H:i', strtotime($slot->start_time)) . ' - ' . date('H:i', strtotime($slot->end_time)),
            ];
        })->toArray();
    }

    /**
     * Publish the timetable
     */
    public function publish()
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_by' => auth()->id(),
            'published_at' => now(),
        ]);
    }

    /**
     * Archive the timetable
     */
    public function archive()
    {
        $this->update([
            'status' => self::STATUS_ARCHIVED,
        ]);
    }

    /**
     * Duplicate timetable for new semester
     */
    public function duplicate($newAcademicYearId, $newSemesterId, $newName = null)
    {
        $newTimetable = $this->replicate();
        $newTimetable->academic_year_id = $newAcademicYearId;
        $newTimetable->semester_id = $newSemesterId;
        $newTimetable->name = $newName ?? $this->name . ' (Copy)';
        $newTimetable->status = self::STATUS_DRAFT;
        $newTimetable->published_by = null;
        $newTimetable->published_at = null;
        $newTimetable->created_by = auth()->id();
        $newTimetable->updated_by = auth()->id();
        $newTimetable->save();

        // Duplicate slots
        foreach ($this->activeSlots as $slot) {
            $newSlot = $slot->replicate();
            $newSlot->timetable_id = $newTimetable->id;
            $newSlot->save();
        }

        return $newTimetable;
    }

    /**
     * Get courses assigned to this timetable
     */
    public function getCourses()
    {
        return Course::whereIn('id', $this->activeSlots()->pluck('course_id')->unique())
            ->orderBy('code')
            ->get();
    }

    /**
     * Get total hours per week
     */
    public function getTotalHoursPerWeek(): float
    {
        $totalMinutes = 0;

        foreach ($this->activeSlots as $slot) {
            $start = strtotime($slot->start_time);
            $end = strtotime($slot->end_time);
            $totalMinutes += ($end - $start) / 60;
        }

        return round($totalMinutes / 60, 1);
    }
}
