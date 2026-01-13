<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPrepaidAccountTransaction extends Model
{
    protected $fillable = [
        'prepaid_account_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference',
        'fee_invoice_id',
        'payment_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    /**
     * Get the prepaid account that owns the transaction.
     */
    public function prepaidAccount(): BelongsTo
    {
        return $this->belongsTo(StudentPrepaidAccount::class, 'prepaid_account_id');
    }

    /**
     * Get the fee invoice associated with the transaction.
     */
    public function feeInvoice(): BelongsTo
    {
        return $this->belongsTo(\App\Models\FeeInvoice::class, 'fee_invoice_id');
    }

    /**
     * Get the user who created the transaction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}

