<?php

namespace App\Models\Hospital;

use App\Models\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalInsuranceType extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'is_none',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_none' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'insurance_type_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Ensure default insurance options exist for a company.
     */
    public static function ensureDefaultsForCompany(int $companyId): void
    {
        if (self::forCompany($companyId)->exists()) {
            return;
        }

        $defaults = [
            ['name' => 'None', 'is_none' => true, 'sort_order' => 0],
            ['name' => 'NHIF', 'is_none' => false, 'sort_order' => 1],
            ['name' => 'CHF', 'is_none' => false, 'sort_order' => 2],
            ['name' => 'Jubilee', 'is_none' => false, 'sort_order' => 3],
            ['name' => 'Strategy', 'is_none' => false, 'sort_order' => 4],
        ];

        foreach ($defaults as $item) {
            self::create([
                'company_id' => $companyId,
                'name' => $item['name'],
                'code' => strtoupper(str_replace(' ', '_', $item['name'])),
                'is_none' => $item['is_none'],
                'is_active' => true,
                'sort_order' => $item['sort_order'],
            ]);
        }
    }

    /**
     * Active insurance types for dropdowns (creates defaults if missing).
     */
    public static function optionsForCompany(int $companyId)
    {
        self::ensureDefaultsForCompany($companyId);

        return self::forCompany($companyId)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
