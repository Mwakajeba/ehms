<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'academic_year_id',
        'semester_id',
        'program_id',
        'course_id',
        'exam_type_id',
        'exam_date',
        'max_marks',
        'pass_marks',
        'notes'
    ];

    protected $casts = [
        'exam_date' => 'date',
        'max_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2'
    ];

    public function academicYear()
    {
        return $this->belongsTo(CollegeAcademicYear::class, 'academic_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(CollegeSemester::class, 'semester_id');
    }

    public function program()
    {
        return $this->belongsTo(CollegeProgram::class, 'program_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function examType()
    {
        return $this->belongsTo(ExamType::class);
    }

    public function registrations()
    {
        return $this->hasMany(ExamRegistration::class);
    }
}
