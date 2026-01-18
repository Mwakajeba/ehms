<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosisExplanation extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'explanation_number',
        'visit_id',
        'patient_id',
        'diagnosis',
        'explanation',
        'notes',
        'created_by',
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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
