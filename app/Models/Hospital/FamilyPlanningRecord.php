<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyPlanningRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'record_number',
        'visit_id',
        'patient_id',
        'item_id',
        'service_type',
        'method_provided',
        'counseling_notes',
        'service_date',
        'next_appointment_date',
        'medical_history',
        'contraindications',
        'notes',
        'status',
        'completed_at',
        'performed_by',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_appointment_date' => 'date',
        'completed_at' => 'datetime',
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

    public function item()
    {
        return $this->belongsTo(\App\Models\Inventory\Item::class, 'item_id');
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
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFollowUpRequired($query)
    {
        return $query->where('status', 'follow_up_required');
    }
}
