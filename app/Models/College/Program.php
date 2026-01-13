<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'college_programs';

    protected $fillable = [
        'name',
        'code',
        'description',
        'objectives',
        'requirements',
        'department_id',
        'duration_years',
        'level',
        'is_active',
        'company_id',
        'branch_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_years' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function getHashidAttribute()
    {
        return Hashids::encode($this->id);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Get all program details (instructor assignments).
     */
    public function programDetails()
    {
        return $this->hasMany(ProgramDetail::class);
    }

    /**
     * Get active instructors for this program.
     */
    public function activeInstructors()
    {
        return $this->hasMany(ProgramDetail::class)->where('status', 'active');
    }
}