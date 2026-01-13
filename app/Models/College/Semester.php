<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $table = 'college_semesters';

    protected $fillable = [
        'name',
        'number',
        'description',
        'status',
    ];

    protected $casts = [
        'number' => 'integer',
    ];
}
