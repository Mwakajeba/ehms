<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'college_academic_years';

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}
