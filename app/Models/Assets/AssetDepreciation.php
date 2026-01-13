<?php

namespace App\Models\Assets;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetDepreciation extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'company_id',
        'branch_id',
        'asset_id',
        'asset_opening_id',
        'type',
        'depreciation_date',
        'depreciation_amount',
        'accumulated_depreciation',
        'book_value_before',
        'book_value_after',
        'description',
        'gl_transaction_id',
        'gl_posted',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'depreciation_date' => 'date',
        'depreciation_amount' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'book_value_before' => 'decimal:2',
        'book_value_after' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function assetOpening()
    {
        return $this->belongsTo(AssetOpening::class, 'asset_opening_id');
    }

    public function glTransactions()
    {
        return $this->hasMany(\App\Models\GlTransaction::class, 'transaction_id')
            ->where('transaction_type', 'asset_depreciation');
    }

    /**
     * Get current book value for an asset
     * This calculates the book value after all depreciations including opening balances
     */
    public static function getCurrentBookValue($assetId, $asOfDate = null, $companyId = null)
    {
        $companyId = $companyId ?? (auth()->user()->company_id ?? 0);
        
        $query = static::where('asset_id', $assetId)
            ->where('company_id', $companyId);
        
        if ($asOfDate) {
            $query->where('depreciation_date', '<=', $asOfDate);
        }
        
        $latest = $query->orderBy('depreciation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        return $latest ? $latest->book_value_after : null;
    }

    /**
     * Get accumulated depreciation for an asset
     */
    public static function getAccumulatedDepreciation($assetId, $asOfDate = null, $companyId = null)
    {
        $companyId = $companyId ?? (auth()->user()->company_id ?? 0);
        
        $query = static::where('asset_id', $assetId)
            ->where('company_id', $companyId);
        
        if ($asOfDate) {
            $query->where('depreciation_date', '<=', $asOfDate);
        }
        
        $latest = $query->orderBy('depreciation_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();
        
        return $latest ? $latest->accumulated_depreciation : 0;
    }
}
