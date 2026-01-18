<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visit extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'visit_number',
        'patient_id',
        'visit_type',
        'status',
        'chief_complaint',
        'company_id',
        'branch_id',
        'created_by',
        'visit_date',
        'completed_at',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
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

    public function visitDepartments()
    {
        return $this->hasMany(VisitDepartment::class);
    }

    public function bills()
    {
        return $this->hasMany(VisitBill::class);
    }

    public function payments()
    {
        return $this->hasMany(VisitPayment::class);
    }

    public function triageVitals()
    {
        return $this->hasOne(TriageVital::class);
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }

    public function labResults()
    {
        return $this->hasMany(LabResult::class);
    }

    public function ultrasoundResults()
    {
        return $this->hasMany(UltrasoundResult::class);
    }

    public function pharmacyDispensations()
    {
        return $this->hasMany(PharmacyDispensation::class);
    }

    public function dentalRecords()
    {
        return $this->hasMany(DentalRecord::class);
    }

    public function injectionRecords()
    {
        return $this->hasMany(InjectionRecord::class);
    }

    public function vaccinationRecords()
    {
        return $this->hasMany(VaccinationRecord::class);
    }

    public function familyPlanningRecords()
    {
        return $this->hasMany(FamilyPlanningRecord::class);
    }

    public function rchRecords()
    {
        return $this->hasMany(RCHRecord::class);
    }

    public function diagnosisExplanation()
    {
        return $this->hasOne(DiagnosisExplanation::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeWithClearedBills($query)
    {
        return $query->whereHas('bills', function ($q) {
            $q->where('clearance_status', 'cleared');
        });
    }

    // Helper methods
    public function hasClearedBills()
    {
        return $this->bills()->where('clearance_status', 'cleared')->exists();
    }

    public function hasPendingBills()
    {
        return $this->bills()->where('clearance_status', 'pending')->exists();
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
