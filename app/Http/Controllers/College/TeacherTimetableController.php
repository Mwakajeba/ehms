<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\TimetableSlot;
use App\Models\College\Timetable;
use App\Models\College\AcademicYear;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class TeacherTimetableController extends Controller
{
    /**
     * Display a listing of teachers with their timetables
     */
    public function index(Request $request)
    {
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        $branchId = session('branch_id') ?? Auth::user()->branch_id;

        // Get all instructors who have slots assigned
        $instructors = Employee::where('status', 'active')
            ->whereHas('timetableSlots', function($q) {
                $q->where('is_active', true);
            })
            ->withCount(['timetableSlots' => function($q) {
                $q->where('is_active', true);
            }])
            ->orderBy('first_name')
            ->get();

        // Get academic years for filter
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        // Get current academic year - check by status or date range, fallback to latest
        $currentAcademicYear = AcademicYear::where('status', 'active')
            ->first()
            ?? AcademicYear::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first()
            ?? AcademicYear::orderBy('start_date', 'desc')->first();

        return view('college.teacher-timetables.index', compact('instructors', 'academicYears', 'currentAcademicYear'));
    }

    /**
     * Display the timetable for a specific teacher
     */
    public function show(Request $request, Employee $employee)
    {
        $academicYearId = $request->get('academic_year_id');
        
        // Get academic years
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        // Get current academic year
        if ($academicYearId) {
            $currentAcademicYear = AcademicYear::find($academicYearId);
        } else {
            $currentAcademicYear = AcademicYear::where('status', 'active')
                ->first()
                ?? AcademicYear::whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first()
                ?? AcademicYear::orderBy('start_date', 'desc')->first();
        }

        // Get all slots for this instructor
        $query = TimetableSlot::with(['course', 'venue', 'timetable.program', 'timetable.semester', 'timetable.academicYear'])
            ->where('instructor_id', $employee->id)
            ->where('is_active', true);

        // Filter by academic year if selected
        if ($currentAcademicYear) {
            $query->whereHas('timetable', function($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear->id);
            });
        }

        $slots = $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Group slots by day
        $slotsByDay = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            $slotsByDay[$day] = $slots->where('day_of_week', $day)->values();
        }

        // Calculate statistics
        $totalSlots = $slots->count();
        $totalHours = $slots->sum(function($slot) {
            return \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time)) / 60;
        });
        $uniqueCourses = $slots->pluck('course_id')->unique()->count();
        $uniquePrograms = $slots->pluck('timetable.program_id')->unique()->count();

        // Get course summary
        $courseSummary = $slots->groupBy('course_id')->map(function($courseSlots) {
            $course = $courseSlots->first()->course;
            $hours = $courseSlots->sum(function($slot) {
                return \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time)) / 60;
            });
            return [
                'code' => $course->code,
                'name' => $course->name,
                'sessions' => $courseSlots->count(),
                'hours' => $hours,
                'programs' => $courseSlots->pluck('timetable.program.code')->unique()->implode(', '),
            ];
        })->values();

        // Generate time slots
        $timeSlots = [];
        for ($hour = 7; $hour <= 21; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        return view('college.teacher-timetables.show', compact(
            'employee', 
            'slots', 
            'slotsByDay', 
            'days',
            'timeSlots',
            'totalSlots', 
            'totalHours', 
            'uniqueCourses', 
            'uniquePrograms',
            'courseSummary',
            'academicYears',
            'currentAcademicYear'
        ));
    }

    /**
     * Export teacher timetable to PDF
     */
    public function exportPdf(Request $request, Employee $employee)
    {
        $academicYearId = $request->get('academic_year_id');
        
        if ($academicYearId) {
            $currentAcademicYear = AcademicYear::find($academicYearId);
        } else {
            $currentAcademicYear = AcademicYear::where('status', 'active')
                ->first()
                ?? AcademicYear::whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first()
                ?? AcademicYear::orderBy('start_date', 'desc')->first();
        }

        // Get all slots for this instructor
        $query = TimetableSlot::with(['course', 'venue', 'timetable.program', 'timetable.semester', 'timetable.academicYear'])
            ->where('instructor_id', $employee->id)
            ->where('is_active', true);

        if ($currentAcademicYear) {
            $query->whereHas('timetable', function($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear->id);
            });
        }

        $slots = $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Group slots by day
        $slotsByDay = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            $slotsByDay[$day] = $slots->where('day_of_week', $day)->values();
        }

        // Calculate statistics
        $totalSlots = $slots->count();
        $totalHours = $slots->sum(function($slot) {
            return \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time)) / 60;
        });

        // Get course summary
        $courseSummary = $slots->groupBy('course_id')->map(function($courseSlots) {
            $course = $courseSlots->first()->course;
            $hours = $courseSlots->sum(function($slot) {
                return \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time)) / 60;
            });
            return [
                'code' => $course->code,
                'name' => $course->name,
                'sessions' => $courseSlots->count(),
                'hours' => $hours,
                'programs' => $courseSlots->pluck('timetable.program.code')->unique()->implode(', '),
            ];
        })->values();

        // Generate time slots
        $timeSlots = [];
        for ($hour = 7; $hour <= 21; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        $pdf = Pdf::loadView('college.teacher-timetables.pdf', compact(
            'employee', 
            'slots', 
            'slotsByDay', 
            'days',
            'timeSlots',
            'totalSlots', 
            'totalHours', 
            'courseSummary',
            'currentAcademicYear'
        ));
        
        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'teacher_timetable_' . str_replace(' ', '_', $employee->full_name) . '_' . date('Ymd') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Print view for teacher timetable
     */
    public function print(Request $request, Employee $employee)
    {
        $academicYearId = $request->get('academic_year_id');
        
        if ($academicYearId) {
            $currentAcademicYear = AcademicYear::find($academicYearId);
        } else {
            $currentAcademicYear = AcademicYear::where('status', 'active')
                ->first()
                ?? AcademicYear::whereDate('start_date', '<=', now())
                    ->whereDate('end_date', '>=', now())
                    ->first()
                ?? AcademicYear::orderBy('start_date', 'desc')->first();
        }

        // Get all slots for this instructor
        $query = TimetableSlot::with(['course', 'venue', 'timetable.program', 'timetable.semester', 'timetable.academicYear'])
            ->where('instructor_id', $employee->id)
            ->where('is_active', true);

        if ($currentAcademicYear) {
            $query->whereHas('timetable', function($q) use ($currentAcademicYear) {
                $q->where('academic_year_id', $currentAcademicYear->id);
            });
        }

        $slots = $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Group slots by day
        $slotsByDay = [];
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        foreach ($days as $day) {
            $slotsByDay[$day] = $slots->where('day_of_week', $day)->values();
        }

        // Calculate statistics
        $totalSlots = $slots->count();
        $totalHours = $slots->sum(function($slot) {
            return \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time)) / 60;
        });

        // Get course summary
        $courseSummary = $slots->groupBy('course_id')->map(function($courseSlots) {
            $course = $courseSlots->first()->course;
            $hours = $courseSlots->sum(function($slot) {
                return \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time)) / 60;
            });
            return [
                'code' => $course->code,
                'name' => $course->name,
                'sessions' => $courseSlots->count(),
                'hours' => $hours,
                'programs' => $courseSlots->pluck('timetable.program.code')->unique()->implode(', '),
            ];
        })->values();

        // Generate time slots
        $timeSlots = [];
        for ($hour = 7; $hour <= 21; $hour++) {
            $timeSlots[] = sprintf('%02d:00:00', $hour);
        }

        return view('college.teacher-timetables.print', compact(
            'employee', 
            'slots', 
            'slotsByDay', 
            'days',
            'timeSlots',
            'totalSlots', 
            'totalHours', 
            'courseSummary',
            'currentAcademicYear'
        ));
    }
}
