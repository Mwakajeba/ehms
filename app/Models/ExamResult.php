<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    protected $fillable = [
        'exam_registration_id',
        'cat_score',
        'final_score',
        'practical_score',
        'total_score',
        'grade',
        'remark',
        'is_published',
        'published_at'
    ];

    protected $casts = [
        'cat_score' => 'decimal:2',
        'final_score' => 'decimal:2',
        'practical_score' => 'decimal:2',
        'total_score' => 'decimal:2',
        'is_published' => 'boolean',
        'published_at' => 'datetime'
    ];

    public function examRegistration()
    {
        return $this->belongsTo(ExamRegistration::class);
    }
}
