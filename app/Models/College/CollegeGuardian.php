<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollegeGuardian extends Model
{
    protected $table = 'college_guardians';

    protected $fillable = [
        'name',
        'phone',
        'alt_phone',
        'email',
        'address',
        'occupation',
        'company_id',
        'branch_id'
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'college_student_parent', 'parent_id', 'student_id')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }

    public function studentParents(): HasMany
    {
        return $this->hasMany(CollegeStudentParent::class, 'parent_id');
    }

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
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
