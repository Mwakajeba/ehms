<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeSettingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_setting_id',
        'name',
        'category',
        'amount',
        'includes_transport',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'includes_transport' => 'boolean',
    ];

    /**
     * Get the fee setting that owns the item.
     */
    public function feeSetting(): BelongsTo
    {
        return $this->belongsTo(FeeSetting::class);
    }
}
