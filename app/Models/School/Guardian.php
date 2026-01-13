<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Guardian extends Authenticatable
{
    use Notifiable, HasApiTokens;

    protected $table = 'guardians';

    protected $fillable = [
        'name',
        'phone',
        'alt_phone',
        'email',
        'address',
        'occupation',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
    ];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardians', 'guardian_id', 'student_id')
                ->withPivot('relationship')
                ->withTimestamps();
    }

    public function studentParents(): HasMany
    {
        return $this->hasMany(StudentGuardian::class);
    }
}
