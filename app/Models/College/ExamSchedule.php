<?php

namespace App\Models\College;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Branch;
use App\Models\User;
use App\Models\Hr\Employee;

class ExamSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'academic_year_id',
        'semester_id',
        'program_id',
        'course_id',
        'level',
        'exam_name',
        'exam_type',
        'description',
        'exam_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'venue',
        'building',
        'capacity',
        'number_of_students',
        'total_marks',
        'pass_marks',
        'invigilator_id',
        'invigilator_name',
        'status',
        'status_remarks',
        'instructions',
        'materials_allowed',
        'is_published',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'total_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
        'materials_allowed' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Exam types available
     */
    const EXAM_TYPES = [
        'continuous_assessment' => 'Continuous Assessment',
        'midterm' => 'Midterm Exam',
        'final' => 'Final Exam',
        'practical' => 'Practical Exam',
        'oral' => 'Oral Exam',
        'supplementary' => 'Special/Supplementary',
        'retake' => 'Retake/Repeat',
        'makeup' => 'Makeup Exam',
        'project' => 'Project/Research',
        'internship' => 'Internship Assessment',
        'online' => 'Online/Take-home Exam',
    ];

    /**
     * Exam type descriptions (Swahili/English)
     */
    const EXAM_TYPE_DESCRIPTIONS = [
        'continuous_assessment' => 'Assignments, Tests, Quizzes, Presentation',
        'midterm' => 'CAT ya katikati ya semester',
        'final' => 'Mtihani wa mwisho wa kozi',
        'practical' => 'Mtihani wa maabara/vitendo',
        'oral' => 'Viva voce',
        'supplementary' => 'Kwa waliofeli au walio na udhuru',
        'retake' => 'Kujaribu tena baada ya kufeli',
        'makeup' => 'Nafasi ya kurekebisha mtihani uliokosekana',
        'project' => 'Dissertation, project defense',
        'internship' => 'Field work / teaching practice',
        'online' => 'Mitihani ya mtandaoni',
    ];

    /**
     * Exam type icons
     */
    const EXAM_TYPE_ICONS = [
        'continuous_assessment' => 'bx-task',
        'midterm' => 'bx-book-open',
        'final' => 'bx-certification',
        'practical' => 'bx-test-tube',
        'oral' => 'bx-microphone',
        'supplementary' => 'bx-plus-medical',
        'retake' => 'bx-revision',
        'makeup' => 'bx-calendar-check',
        'project' => 'bx-folder-open',
        'internship' => 'bx-briefcase',
        'online' => 'bx-laptop',
    ];

    /**
     * Exam type colors
     */
    const EXAM_TYPE_COLORS = [
        'continuous_assessment' => '#3b82f6',
        'midterm' => '#8b5cf6',
        'final' => '#ef4444',
        'practical' => '#10b981',
        'oral' => '#f59e0b',
        'supplementary' => '#ec4899',
        'retake' => '#f97316',
        'makeup' => '#06b6d4',
        'project' => '#6366f1',
        'internship' => '#14b8a6',
        'online' => '#64748b',
    ];

    /**
     * Status options
     */
    const STATUSES = [
        'draft' => 'Draft',
        'scheduled' => 'Scheduled',
        'ongoing' => 'Ongoing',
        'completed' => 'Completed',
        'postponed' => 'Postponed',
        'cancelled' => 'Cancelled',
    ];

    /**
     * Status colors for UI
     */
    const STATUS_COLORS = [
        'draft' => 'secondary',
        'scheduled' => 'primary',
        'ongoing' => 'warning',
        'completed' => 'success',
        'postponed' => 'info',
        'cancelled' => 'danger',
    ];

    /**
     * Academic Levels
     */
    const ACADEMIC_LEVELS = [
        'foundation' => 'Foundation / Pre-University',
        'certificate' => 'Certificate',
        'advanced_certificate' => 'Advanced Certificate',
        'diploma' => 'Diploma',
        'advanced_diploma' => 'Advanced Diploma',
        'higher_diploma' => 'Higher Diploma',
        'postgraduate_diploma' => 'Postgraduate Diploma',
        'bachelor' => 'Bachelor\'s Degree',
        'honours' => 'Honours Degree',
        'masters' => 'Master\'s Degree',
        'phd' => 'PhD / Doctorate',
        'postdoctoral' => 'Postdoctoral',
        'nvta_level_1' => 'NTA Level 4 (NVTA I)',
        'nvta_level_2' => 'NTA Level 5 (NVTA II)',
        'nvta_level_3' => 'NTA Level 6 (NVTA III)',
        'professional' => 'Professional Certification',
    ];

    /**
     * Academic Level Short Names
     */
    const ACADEMIC_LEVEL_SHORT = [
        'foundation' => 'Foundation',
        'certificate' => 'Cert',
        'advanced_certificate' => 'Adv. Cert',
        'diploma' => 'Dip',
        'advanced_diploma' => 'Adv. Dip',
        'higher_diploma' => 'Higher Dip',
        'postgraduate_diploma' => 'PGD',
        'bachelor' => 'BSc/BA',
        'honours' => 'Hons',
        'masters' => 'MSc/MA',
        'phd' => 'PhD',
        'postdoctoral' => 'PostDoc',
        'nvta_level_1' => 'NTA 4',
        'nvta_level_2' => 'NTA 5',
        'nvta_level_3' => 'NTA 6',
        'professional' => 'Prof. Cert',
    ];

    /**
     * Get level display name
     */
    public function getLevelNameAttribute()
    {
        return self::ACADEMIC_LEVELS[$this->level] ?? $this->level ?? 'N/A';
    }

    /**
     * Get level short name
     */
    public function getLevelShortAttribute()
    {
        return self::ACADEMIC_LEVEL_SHORT[$this->level] ?? $this->level ?? '-';
    }

    // ==================== RELATIONSHIPS ====================

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function invigilator()
    {
        return $this->belongsTo(Employee::class, 'invigilator_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ==================== SCOPES ====================

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('exam_date', '>=', now()->toDateString())
                     ->whereIn('status', ['scheduled', 'draft']);
    }

    public function scopePast($query)
    {
        return $query->where('exam_date', '<', now()->toDateString())
                     ->orWhere('status', 'completed');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeBySemester($query, $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('exam_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('exam_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('exam_date', now()->month)
                     ->whereYear('exam_date', now()->year);
    }

    // ==================== ACCESSORS ====================

    public function getExamTypeNameAttribute()
    {
        return self::EXAM_TYPES[$this->exam_type] ?? $this->exam_type;
    }

    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getFormattedDateAttribute()
    {
        return $this->exam_date ? $this->exam_date->format('D, M d, Y') : 'N/A';
    }

    public function getFormattedTimeAttribute()
    {
        $start = $this->start_time ? \Carbon\Carbon::parse($this->start_time)->format('h:i A') : '';
        $end = $this->end_time ? \Carbon\Carbon::parse($this->end_time)->format('h:i A') : '';
        return $start && $end ? "{$start} - {$end}" : 'N/A';
    }

    public function getDurationFormattedAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours} hour" . ($hours > 1 ? 's' : '');
        } else {
            return "{$minutes} minutes";
        }
    }

    public function getFullVenueAttribute()
    {
        $parts = array_filter([$this->venue, $this->building]);
        return implode(', ', $parts) ?: 'TBA';
    }

    public function getInvigilatorDisplayAttribute()
    {
        return $this->invigilator ? $this->invigilator->name : ($this->invigilator_name ?: 'TBA');
    }

    public function getIsUpcomingAttribute()
    {
        return $this->exam_date && $this->exam_date->isFuture();
    }

    public function getIsTodayAttribute()
    {
        return $this->exam_date && $this->exam_date->isToday();
    }

    public function getIsPastAttribute()
    {
        return $this->exam_date && $this->exam_date->isPast();
    }

    public function getDaysUntilAttribute()
    {
        if (!$this->exam_date) return null;
        return now()->startOfDay()->diffInDays($this->exam_date->startOfDay(), false);
    }

    // ==================== METHODS ====================

    public function publish()
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
            'status' => 'scheduled',
        ]);
    }

    public function unpublish()
    {
        $this->update([
            'is_published' => false,
            'published_at' => null,
            'status' => 'draft',
        ]);
    }

    public function markAsOngoing()
    {
        $this->update(['status' => 'ongoing']);
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function postpone($reason = null)
    {
        $this->update([
            'status' => 'postponed',
            'status_remarks' => $reason,
        ]);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'status_remarks' => $reason,
        ]);
    }

    public function reschedule($newDate, $newStartTime = null, $newEndTime = null)
    {
        $data = [
            'exam_date' => $newDate,
            'status' => 'scheduled',
            'status_remarks' => 'Rescheduled from ' . $this->exam_date->format('M d, Y'),
        ];

        if ($newStartTime) $data['start_time'] = $newStartTime;
        if ($newEndTime) $data['end_time'] = $newEndTime;

        $this->update($data);
    }

    /**
     * Check if exam can be edited
     */
    public function canEdit()
    {
        return !in_array($this->status, ['ongoing', 'completed', 'cancelled']);
    }

    /**
     * Check if exam can be deleted
     */
    public function canDelete()
    {
        return !in_array($this->status, ['ongoing', 'completed']);
    }

    /**
     * Get students enrolled for this exam's course
     */
    public function getEnrolledStudentsCount()
    {
        return CourseRegistration::where('course_id', $this->course_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->where('semester_id', $this->semester_id)
            ->where('status', 'approved')
            ->count();
    }
}
