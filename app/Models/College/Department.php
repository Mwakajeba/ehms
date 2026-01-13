<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\College\Program;
use App\Models\College\Student;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'college_departments';

    protected $fillable = [
        'name',
        'code',
        'description',
        'head_of_department_id',
        'company_id',
        'branch_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function headOfDepartment()
    {
        return $this->belongsTo(\App\Models\User::class, 'head_of_department_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, Program::class);
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
}