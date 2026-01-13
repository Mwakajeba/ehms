<?php

namespace App\Models\Hospital;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitBillItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'bill_id',
        'item_type',
        'service_id',
        'product_id',
        'item_name',
        'quantity',
        'unit_price',
        'discount',
        'total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
    public function bill()
    {
        return $this->belongsTo(VisitBill::class, 'bill_id');
    }

    public function service()
    {
        return $this->belongsTo(HospitalService::class, 'service_id');
    }

    public function product()
    {
        return $this->belongsTo(HospitalProduct::class, 'product_id');
    }

    // Helper methods
    public function calculateTotal()
    {
        $this->total = ($this->unit_price * $this->quantity) - $this->discount;
        $this->save();
    }
}
