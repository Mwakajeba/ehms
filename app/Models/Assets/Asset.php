<?php

namespace App\Models\Assets;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_id',
        'branch_id',
        'asset_category_id',
        'tax_pool_class',
        'code',
        'name',
        'model',
        'manufacturer',
        'description',
        'purchase_date',
        'capitalization_date',
        'purchase_cost',
        'supplier_id',
        'location',
        'building_reference',
        'gps_lat',
        'gps_lng',
        'serial_number',
        'salvage_value',
        'current_nbv',
        'department_id',
        'custodian_user_id',
        'tag',
        'barcode',
        'status',
        'hfs_status',
        'depreciation_stopped',
        'depreciation_stopped_date',
        'depreciation_stopped_reason',
        'current_hfs_id',
        'warranty_months',
        'warranty_expiry_date',
        'insurance_policy_no',
        'insured_value',
        'insurance_expiry_date',
        'attachments',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'capitalization_date' => 'date',
        'warranty_expiry_date' => 'date',
        'insurance_expiry_date' => 'date',
        'depreciation_stopped_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'current_nbv' => 'decimal:2',
        'insured_value' => 'decimal:2',
        'depreciation_stopped' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\Hr\Department::class);
    }

    public function custodian()
    {
        return $this->belongsTo(\App\Models\User::class, 'custodian_user_id');
    }

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function openings()
    {
        return $this->hasMany(AssetOpening::class);
    }

    public function purchaseInvoiceItems()
    {
        return $this->hasMany(\App\Models\Purchase\PurchaseInvoiceItem::class, 'asset_id');
    }

    public function glTransactions()
    {
        return $this->hasMany(\App\Models\GlTransaction::class, 'asset_id');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    public function maintenanceHistory()
    {
        return $this->hasMany(MaintenanceHistory::class);
    }

    public function revaluations()
    {
        return $this->hasMany(AssetRevaluation::class);
    }

    public function latestRevaluation()
    {
        return $this->hasOne(AssetRevaluation::class)->latestOfMany();
    }

    public function impairments()
    {
        return $this->hasMany(AssetImpairment::class);
    }

    public function latestImpairment()
    {
        return $this->hasOne(AssetImpairment::class)->latestOfMany();
    }

    public function revaluationReserves()
    {
        return $this->hasMany(RevaluationReserve::class);
    }

    public function disposals()
    {
        return $this->hasMany(\App\Models\Assets\AssetDisposal::class);
    }

    public function latestDisposal()
    {
        return $this->hasOne(\App\Models\Assets\AssetDisposal::class)->latestOfMany();
    }

    // HFS Relationships
    public function currentHfsRequest()
    {
        return $this->belongsTo(HfsRequest::class, 'current_hfs_id');
    }

    public function hfsAssets()
    {
        return $this->hasMany(HfsAsset::class, 'asset_id');
    }

    public function activeHfsAsset()
    {
        return $this->hasOne(HfsAsset::class, 'asset_id')
            ->where('status', 'classified')
            ->whereHas('hfsRequest', function($query) {
                $query->whereIn('status', ['approved', 'in_review']);
            });
    }

    /**
     * Check if asset can be disposed
     */
    public function canBeDisposed()
    {
        return $this->status === 'active' && !$this->disposals()->where('status', '!=', 'cancelled')->exists();
    }

    /**
     * Check if asset can be classified as HFS
     */
    public function canBeClassifiedAsHfs(): bool
    {
        // Asset must be active
        if ($this->status !== 'active') {
            return false;
        }

        // Asset must not already be classified as HFS
        if (in_array($this->hfs_status, ['pending', 'classified'])) {
            return false;
        }

        // Asset must not already be disposed
        if ($this->disposals()->where('status', '!=', 'cancelled')->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Check if depreciation is stopped
     */
    public function isDepreciationStopped(): bool
    {
        return $this->depreciation_stopped === true;
    }

    /**
     * Stop depreciation (for HFS classification)
     */
    public function stopDepreciation(string $reason = null): void
    {
        $this->update([
            'depreciation_stopped' => true,
            'depreciation_stopped_date' => now(),
            'depreciation_stopped_reason' => $reason ?? 'Classified as Held for Sale',
        ]);
    }

    /**
     * Resume depreciation (for HFS cancellation)
     */
    public function resumeDepreciation(): void
    {
        $this->update([
            'depreciation_stopped' => false,
            'depreciation_stopped_date' => null,
            'depreciation_stopped_reason' => null,
        ]);
    }

    /**
     * Get current carrying amount (considering revaluations and impairments)
     */
    public function getCurrentCarryingAmount()
    {
        if ($this->revalued_carrying_amount !== null) {
            return $this->revalued_carrying_amount;
        }

        $bookValue = AssetDepreciation::getCurrentBookValue($this->id);
        if ($bookValue !== null) {
            return $bookValue;
        }

        return $this->purchase_cost - AssetDepreciation::getAccumulatedDepreciation($this->id);
    }

    /**
     * Get current revaluation reserve balance
     */
    public function getCurrentReserveBalance()
    {
        return RevaluationReserve::getCurrentBalance($this->id, null, $this->company_id);
    }

    // Scopes
    public function scopeForBranch($query, $branchId)
    {
        if ($branchId) {
            return $query->where('branch_id', $branchId);
        }
        return $query;
    }

    public function scopeRevalued($query)
    {
        return $query->where('valuation_model', 'revaluation');
    }

    public function scopeImpaired($query)
    {
        return $query->where('is_impaired', true);
    }

    public function scopeHfsClassified($query)
    {
        return $query->where('hfs_status', 'classified');
    }

    public function scopeHfsPending($query)
    {
        return $query->where('hfs_status', 'pending');
    }

    public function scopeDepreciationStopped($query)
    {
        return $query->where('depreciation_stopped', true);
    }
}


