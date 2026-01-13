<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabResult extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'result_number',
        'visit_id',
        'patient_id',
        'service_id',
        'test_name',
        'result_value',
        'unit',
        'reference_range',
        'status',
        'notes',
        'result_status',
        'completed_at',
        'printed_at',
        'performed_by',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'printed_at' => 'datetime',
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

    public function service()
    {
        return $this->belongsTo(HospitalService::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Scopes
    public function scopeReady($query)
    {
        return $query->where('result_status', 'ready');
    }

    public function scopePending($query)
    {
        return $query->where('result_status', 'pending');
    }
}
