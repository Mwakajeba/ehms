<?php

namespace App\Models\Hospital;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Receipt;
use App\Models\Sales\SalesInvoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceInvoicePayment extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'sales_invoice_id',
        'patient_id',
        'insurance_type_id',
        'receipt_id',
        'amount',
        'currency',
        'exchange_rate',
        'payment_date',
        'description',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'payment_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function salesInvoice(): BelongsTo
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function insuranceType(): BelongsTo
    {
        return $this->belongsTo(HospitalInsuranceType::class, 'insurance_type_id');
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(Receipt::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
