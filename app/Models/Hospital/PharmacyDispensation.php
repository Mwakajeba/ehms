<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyDispensation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'dispensation_number',
        'visit_id',
        'patient_id',
        'bill_id',
        'status',
        'instructions',
        'dispensed_by',
        'dispensed_at',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'dispensed_at' => 'datetime',
    ];

    // Relationships
    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function bill()
    {
        return $this->belongsTo(VisitBill::class);
    }

    public function dispensedBy()
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(PharmacyDispensationItem::class, 'dispensation_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDispensed($query)
    {
        return $query->where('status', 'dispensed');
    }
}
