<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitBill extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'bill_number',
        'visit_id',
        'patient_id',
        'bill_type',
        'subtotal',
        'discount',
        'tax',
        'total',
        'paid',
        'balance',
        'payment_status',
        'clearance_status',
        'cleared_at',
        'cleared_by',
        'company_id',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'cleared_at' => 'datetime',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function clearedBy()
    {
        return $this->belongsTo(User::class, 'cleared_by');
    }

    public function items()
    {
        return $this->hasMany(VisitBillItem::class, 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany(VisitPayment::class, 'bill_id');
    }

    // Helper methods
    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('total');
        $this->total = $this->subtotal - $this->discount + $this->tax;
        $this->balance = $this->total - $this->paid;
        $this->payment_status = $this->balance <= 0 ? 'paid' : ($this->paid > 0 ? 'partial' : 'pending');
        $this->save();
    }

    public function isCleared()
    {
        return $this->clearance_status === 'cleared';
    }

    public function clear()
    {
        $this->clearance_status = 'cleared';
        $this->cleared_at = now();
        $this->cleared_by = auth()->id();
        $this->save();
    }
}
