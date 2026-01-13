<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Models\Inventory\Item;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RCHRecord extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'rch_records';

    protected $fillable = [
        'record_number',
        'visit_id',
        'patient_id',
        'service_id',
        'service_type',
        'service_description',
        'findings',
        'recommendations',
        'counseling_notes',
        'health_education_topics',
        'notes',
        'vitals',
        'status',
        'next_appointment_date',
        'completed_at',
        'performed_by',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'vitals' => 'array',
        'next_appointment_date' => 'date',
        'completed_at' => 'datetime',
    ];

    // Service type constants
    const TYPE_ANTENATAL_CARE = 'antenatal_care';
    const TYPE_POSTNATAL_CARE = 'postnatal_care';
    const TYPE_CHILD_HEALTH = 'child_health';
    const TYPE_FAMILY_PLANNING = 'family_planning';
    const TYPE_IMMUNIZATION = 'immunization';
    const TYPE_GROWTH_MONITORING = 'growth_monitoring';
    const TYPE_HEALTH_EDUCATION = 'health_education';
    const TYPE_COUNSELING = 'counseling';
    const TYPE_OTHER = 'other';

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
        return $this->belongsTo(Item::class, 'service_id');
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

    public function scopeByServiceType($query, $type)
    {
        return $query->where('service_type', $type);
    }

    // Helper methods
    public function getServiceTypeDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->service_type));
    }
}
