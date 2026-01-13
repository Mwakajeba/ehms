<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalDepartment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'is_active',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Constants
    const TYPE_RECEPTION = 'reception';
    const TYPE_CASHIER = 'cashier';
    const TYPE_TRIAGE = 'triage';
    const TYPE_DOCTOR = 'doctor';
    const TYPE_LAB = 'lab';
    const TYPE_ULTRASOUND = 'ultrasound';
    const TYPE_DENTAL = 'dental';
    const TYPE_PHARMACY = 'pharmacy';
    const TYPE_RCH = 'rch';
    const TYPE_FAMILY_PLANNING = 'family_planning';
    const TYPE_VACCINE = 'vaccine';
    const TYPE_INJECTION = 'injection';
    const TYPE_OBSERVATION = 'observation';

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function services()
    {
        return $this->hasMany(HospitalService::class, 'department_id');
    }

    public function visitDepartments()
    {
        return $this->hasMany(VisitDepartment::class, 'department_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
