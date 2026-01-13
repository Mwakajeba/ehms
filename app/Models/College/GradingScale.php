<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Branch;
use App\Models\Company;

class GradingScale extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function items(): HasMany
    {
        return $this->hasMany(GradingScaleItem::class)->orderBy('min_marks', 'desc');
    }

    // Helper Methods
    public function getGradeForMarks(float $marks): ?GradingScaleItem
    {
        return $this->items()
            ->where('min_marks', '<=', $marks)
            ->where('max_marks', '>=', $marks)
            ->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
