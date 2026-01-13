<?php

namespace App\Models\School;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Vinkla\Hashids\Facades\Hashids;

class AttendanceSession extends Model
{
    protected $fillable = [
        'session_date',
        'class_id',
        'stream_id',
        'academic_year_id',
        'status',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'session_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classe::class, 'class_id');
    }

    public function stream(): BelongsTo
    {
        return $this->belongsTo(Stream::class, 'stream_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function studentAttendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class, 'attendance_session_id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKey()
    {
        return Hashids::encode($this->getKey());
    }

    /**
     * Get the route key name for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $decoded = Hashids::decode($value);
        $id = $decoded[0] ?? null;

        return $this->where('id', $id)->firstOrFail();
    }

    /**
     * Get attendance statistics for this session
     */
    public function getAttendanceStats()
    {
        $totalStudents = $this->studentAttendances()->count();
        $presentCount = $this->studentAttendances()->where('status', 'present')->count();
        $absentCount = $this->studentAttendances()->where('status', 'absent')->count();
        $lateCount = $this->studentAttendances()->where('status', 'late')->count();
        $sickCount = $this->studentAttendances()->where('status', 'sick')->count();

        return [
            'total_students' => $totalStudents,
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'sick' => $sickCount,
            'attendance_rate' => $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0
        ];
    }
}
