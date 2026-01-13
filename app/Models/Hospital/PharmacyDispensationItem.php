<?php

namespace App\Models\Hospital;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyDispensationItem extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'dispensation_id',
        'product_id',
        'quantity_prescribed',
        'quantity_dispensed',
        'dosage_instructions',
        'status',
    ];

    protected $casts = [
        'quantity_prescribed' => 'integer',
        'quantity_dispensed' => 'integer',
    ];

    // Relationships
    public function dispensation()
    {
        return $this->belongsTo(PharmacyDispensation::class, 'dispensation_id');
    }

    public function product()
    {
        return $this->belongsTo(HospitalProduct::class, 'product_id');
    }
}
