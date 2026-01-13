<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentGuardian extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'alt_phone',
        'email',
        'address',
        'occupation'
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_parent')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }

    public function studentParents(): HasMany
    {
        return $this->hasMany(StudentParent::class);
    }
}
