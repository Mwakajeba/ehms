<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'chief_complaint',
        'history_of_present_illness',
        'physical_examination',
        'diagnosis',
        'treatment_plan',
        'prescription',
        'notes',
        'doctor_id',
        'company_id',
        'branch_id',
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

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
