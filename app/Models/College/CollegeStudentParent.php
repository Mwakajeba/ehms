<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollegeStudentParent extends Model
{
    protected $table = 'college_student_parent';

    protected $fillable = ['student_id', 'parent_id', 'relationship'];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(CollegeGuardian::class, 'parent_id');
    }
}
