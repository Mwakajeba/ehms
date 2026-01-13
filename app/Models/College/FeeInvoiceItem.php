<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInvoiceItem extends Model
{
    use HasFactory;

    protected $table = 'college_fee_invoice_items';

    protected $fillable = [
        'college_fee_invoice_id',
        'fee_item_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function feeInvoice()
    {
        return $this->belongsTo(FeeInvoice::class, 'college_fee_invoice_id');
    }

    public function feeItem()
    {
        return $this->belongsTo(FeeSettingItem::class, 'fee_item_id');
    }
}