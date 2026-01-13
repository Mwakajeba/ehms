<?php

namespace App\Models\Hospital;

use App\Models\Company;
use App\Models\Branch;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HospitalService extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'price',
        'nhif_eligible',
        'chf_eligible',
        'jubilee_eligible',
        'strategy_eligible',
        'department_id',
        'is_active',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'nhif_eligible' => 'boolean',
        'chf_eligible' => 'boolean',
        'jubilee_eligible' => 'boolean',
        'strategy_eligible' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(HospitalDepartment::class, 'department_id');
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

    public function scopeInsuranceEligible($query, $insuranceType)
    {
        $field = strtolower($insuranceType) . '_eligible';
        return $query->where($field, true);
    }
}
