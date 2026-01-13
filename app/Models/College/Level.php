<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Branch;
use App\Models\User;

class Level extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'college_levels';

    protected $fillable = [
        'branch_id',
        'name',
        'short_name',
        'code',
        'category',
        'description',
        'sort_order',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Level categories
     */
    const CATEGORIES = [
        'foundation' => 'Foundation & Basic',
        'certificate' => 'Certificate Programs',
        'diploma' => 'Diploma Programs',
        'degree' => 'Degree Programs',
        'postgraduate' => 'Postgraduate Programs',
        'professional' => 'Professional & Vocational',
    ];

    /**
     * Category colors for UI
     */
    const CATEGORY_COLORS = [
        'foundation' => '#6b7280',
        'certificate' => '#3b82f6',
        'diploma' => '#8b5cf6',
        'degree' => '#10b981',
        'postgraduate' => '#ef4444',
        'professional' => '#f59e0b',
    ];

    // ==================== RELATIONSHIPS ====================

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function examSchedules()
    {
        // Level is stored as code or short_name string in exam_schedules table
        return ExamSchedule::where('level', $this->code)
            ->orWhere('level', $this->short_name);
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== ACCESSORS ====================

    public function getCategoryNameAttribute()
    {
        return self::CATEGORIES[$this->category] ?? $this->category ?? 'N/A';
    }

    public function getCategoryColorAttribute()
    {
        return self::CATEGORY_COLORS[$this->category] ?? '#6b7280';
    }

    public function getDisplayNameAttribute()
    {
        return $this->short_name ? "{$this->name} ({$this->short_name})" : $this->name;
    }
}
