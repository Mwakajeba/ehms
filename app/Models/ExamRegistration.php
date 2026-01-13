<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRegistration extends Model
{
    protected $fillable = [
        'student_id',
        'exam_id',
        'status',
        'reason'
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function examResult()
    {
        return $this->hasOne(ExamResult::class);
    }
}
