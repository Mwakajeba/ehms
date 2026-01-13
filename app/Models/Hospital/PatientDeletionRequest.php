<?php

namespace App\Models\Hospital;

use App\Models\User;
use App\Models\Company;
use App\Models\Branch;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDeletionRequest extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'patient_id',
        'reason',
        'status',
        'initiated_by',
        'approved_by',
        'approved_at',
        'approval_notes',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
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
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
