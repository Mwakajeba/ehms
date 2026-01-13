<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Vinkla\Hashids\Facades\Hashids;

class FeeSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'academic_year_id',
        'fee_period',
        'date_from',
        'date_to',
        'is_active',
        'company_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    /**
     * Get the class that owns the fee setting.
     */
    public function classe(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\Classe::class, 'class_id');
    }

    /**
     * Get the academic year that owns the fee setting.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Models\School\AcademicYear::class);
    }

    /**
     * Get the company that owns the fee setting.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch that owns the fee setting.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who created the fee setting.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the fee setting items for this fee setting.
     */
    public function feeSettingItems(): HasMany
    {
        return $this->hasMany(FeeSettingItem::class);
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by branch.
     */
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
        });
    }

    /**
     * Scope to filter by user (company and branch).
     */
    public function scopeForUser($query)
    {
        $user = auth()->user();
        return $query->where('company_id', $user->company_id)
                    ->where(function ($query) use ($user) {
                        $query->where('branch_id', $user->branch_id)
                              ->orWhereNull('branch_id');
                    });
    }

    /**
     * Get fee period options for dropdowns.
     */
    public static function getFeePeriodOptions()
    {
        return [
            'Q1' => 'Quarter 1',
            'Q2' => 'Quarter 2',
            'Q3' => 'Quarter 3',
            'Q4' => 'Quarter 4',
            'Term 1' => 'Term 1',
            'Term 2' => 'Term 2',
            'Annual' => 'Annual',
        ];
    }

    /**
     * Get category options for dropdowns.
     */
    public static function getCategoryOptions()
    {
        return [
            'day' => 'Day Scholar',
            'boarding' => 'Boarding',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Get the route key name for route model binding.
     */
    public function getRouteKeyName()
    {
        return 'fee_setting';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);
        $id = $decoded[0] ?? null;

        return $this->where('id', $id)->firstOrFail();
    }

    /**
     * Get the hashid attribute.
     */
    public function getHashidAttribute()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Find by hashid.
     */
    public static function findByHashid($hashid)
    {
        $decoded = Hashids::decode($hashid);
        return self::where('id', $decoded[0] ?? null)->first();
    }
}
