<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'weight',
        'is_active'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function gradeScales()
    {
        return $this->hasMany(GradeScale::class);
    }
}
