<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_invoice_id',
        'fee_name',
        'amount',
        'category',
        'includes_transport',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'includes_transport' => 'boolean',
    ];

    /**
     * Get the fee invoice that owns the item.
     */
    public function feeInvoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class);
    }
}
