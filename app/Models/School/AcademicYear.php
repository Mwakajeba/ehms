<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicYear extends Model
{
    protected $fillable = [
        'year_name',
        'is_current',
        'start_date',
        'end_date',
        'company_id',
        'branch_id',
        'status',
        'description'
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get formatted duration string
     */
    public function getFormattedDurationAttribute()
    {
        return $this->start_date->format('M Y') . ' - ' . $this->end_date->format('M Y');
    }

    /**
     * Get progress percentage for active academic years
     */
    public function getProgressPercentageAttribute()
    {
        if (!$this->isActive()) {
            return 0;
        }

        $now = now();
        $totalDays = $this->start_date->diffInDays($this->end_date);
        $elapsedDays = $this->start_date->diffInDays($now);

        if ($totalDays == 0) {
            return 100;
        }

        $progress = min(100, max(0, ($elapsedDays / $totalDays) * 100));
        return round($progress);
    }

    /**
     * Check if academic year is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Check if academic year can be edited
     */
    public function canBeEdited()
    {
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Check if academic year can be deleted
     */
    public function canBeDeleted()
    {
        return $this->status === 'upcoming' && !$this->is_current;
    }

    /**
     * Mark academic year as completed
     */
    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge()
    {
        $badges = [
            'upcoming' => '<span class="badge bg-warning">Upcoming</span>',
            'active' => '<span class="badge bg-success">Active</span>',
            'completed' => '<span class="badge bg-secondary">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-light">Unknown</span>';
    }

    /**
     * Get current badge HTML
     */
    public function getCurrentBadge()
    {
        return $this->is_current
            ? '<span class="badge bg-primary"><i class="bx bx-star"></i> Current</span>'
            : '';
    }

    /**
     * Generate year name based on start year
     */
    public static function generateYearName($startYear = null)
    {
        $startYear = $startYear ?? date('Y');
        $endYear = $startYear + 1;
        return $startYear . '-' . $endYear;
    }

    /**
     * Get statistics for the academic year
     */
    public function getStats()
    {
        return [
            'total_students' => $this->students()->count(),
            'total_enrollments' => $this->enrollments()->count(),
            'total_classes' => $this->enrollments()->distinct('class_id')->count('class_id'),
            'total_fee_settings' => $this->feeSettings()->count(),
            'progress_percentage' => $this->progress_percentage,
        ];
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the fee settings for this academic year.
     */
    public function feeSettings(): HasMany
    {
        return $this->hasMany(\App\Models\FeeSetting::class);
    }

    /**
     * Get the company that owns the academic year.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Get the branch that owns the academic year.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Scope a query to only include active academic years.
     */
    public function scopeActive($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope a query to only include academic years for a specific company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope a query to only include academic years for a specific branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Get the current academic year.
     */
    public static function current()
    {
        return static::where('is_current', true)->first();
    }

    /**
     * Set this academic year as the current one and unset others.
     */
    public function setAsCurrent()
    {
        // Use transaction to ensure data integrity
        \DB::transaction(function () {
            // First, unset all other academic years as current for the same company and branch
            $query = static::where('company_id', $this->company_id);

            if ($this->branch_id) {
                $query->where(function ($q) {
                    $q->where('branch_id', $this->branch_id)
                      ->orWhereNull('branch_id');
                });
            }

            $query->where('id', '!=', $this->id)
                  ->update(['is_current' => false]);

            // Then set this one as current
            $this->update(['is_current' => true]);
        });
    }
}
