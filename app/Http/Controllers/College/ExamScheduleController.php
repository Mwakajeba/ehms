<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\ExamSchedule;
use App\Models\College\AcademicYear;
use App\Models\College\Semester;
use App\Models\College\Program;
use App\Models\College\Course;
use App\Models\College\Venue;
use App\Models\College\Level;
use App\Models\Hr\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ExamScheduleController extends Controller
{
    /**
     * Display a listing of the exam schedules.
     */
    public function index(Request $request)
    {
        $branchId = session('branch_id');
        
        $query = ExamSchedule::with(['academicYear', 'semester', 'program', 'course', 'invigilator'])
            ->forBranch($branchId)
            ->latest('exam_date');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('exam_name', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%")
                  ->orWhereHas('course', function ($cq) use ($search) {
                      $cq->where('course_code', 'like', "%{$search}%")
                         ->orWhere('course_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->filled('semester_id')) {
            $query->bySemester($request->semester_id);
        }

        if ($request->filled('program_id')) {
            $query->byProgram($request->program_id);
        }

        if ($request->filled('course_id')) {
            $query->byCourse($request->course_id);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('exam_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('exam_date', '<=', $request->date_to);
        }

        $schedules = $query->paginate(15)->withQueryString();

        // Get filter options
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();
        $programs = Program::where('branch_id', $branchId)->orderBy('name')->get();
        $courses = Course::orderBy('code')->get();

        // Statistics
        $stats = [
            'total' => ExamSchedule::forBranch($branchId)->count(),
            'upcoming' => ExamSchedule::forBranch($branchId)->upcoming()->count(),
            'today' => ExamSchedule::forBranch($branchId)->today()->count(),
            'completed' => ExamSchedule::forBranch($branchId)->byStatus('completed')->count(),
            'this_week' => ExamSchedule::forBranch($branchId)->thisWeek()->count(),
        ];

        return view('college.exam-schedules.index', compact(
            'schedules',
            'academicYears',
            'semesters',
            'programs',
            'courses',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new exam schedule.
     */
    public function create()
    {
        $branchId = session('branch_id');
        $companyId = session('company_id');

        // Get academic years (no branch_id filter - shared data)
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        // Get semesters (no branch_id filter - shared data)
        $semesters = Semester::orderBy('name')->get();
        
        // Programs have branch_id
        $programs = Program::where('branch_id', $branchId)->orderBy('name')->get();
        
        // Get all courses (filter by program in JavaScript or get all)
        $courses = Course::orderBy('code')->get();
        
        // Get venues for exam halls
        $venues = Venue::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Get employees as invigilators
        $invigilators = Employee::where('branch_id', $branchId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Get academic levels for exam
        $levels = Level::where('branch_id', $branchId)
            ->active()
            ->ordered()
            ->get();

        $examTypes = ExamSchedule::EXAM_TYPES;
        $statuses = ExamSchedule::STATUSES;

        return view('college.exam-schedules.create', compact(
            'academicYears',
            'semesters',
            'programs',
            'courses',
            'venues',
            'invigilators',
            'levels',
            'examTypes',
            'statuses'
        ));
    }

    /**
     * Store a newly created exam schedule.
     */
    public function store(Request $request)
    {
        $branchId = session('branch_id');

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
            'program_id' => 'required|exists:college_programs,id',
            'course_id' => 'required|exists:courses,id',
            'exam_name' => 'required|string|max:255',
            'exam_type' => 'required|in:continuous_assessment,midterm,final,practical,oral,supplementary,retake,makeup,project,internship,online',
            'description' => 'nullable|string|max:1000',
            'exam_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'duration_minutes' => 'required|integer|min:30|max:480',
            'venue_id' => 'nullable|exists:college_venues,id',
            'building' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'number_of_students' => 'nullable|integer|min:1',
            'total_marks' => 'required|numeric|min:1|max:100',
            'pass_marks' => 'required|numeric|min:1|lte:total_marks',
            'invigilator_id' => 'nullable|exists:users,id',
            'invigilator_name' => 'nullable|string|max:255',
            'instructions' => 'nullable|string|max:2000',
            'materials_allowed' => 'nullable|array',
            'materials_allowed.*' => 'string|max:100',
            'status' => 'required|in:draft,scheduled',
            'is_published' => 'boolean',
        ]);

        // Get venue name from selected venue
        if (!empty($validated['venue_id'])) {
            $venue = Venue::find($validated['venue_id']);
            if ($venue) {
                $validated['venue'] = $venue->name . ' (' . $venue->code . ')';
            }
        }
        unset($validated['venue_id']);

        // Validate end_time is after start_time
        $startTime = strtotime($request->start_time);
        $endTime = strtotime($request->end_time);
        if ($endTime <= $startTime) {
            return back()->withErrors(['end_time' => 'The end time must be after the start time.'])->withInput();
        }

        $validated['branch_id'] = $branchId;
        $validated['created_by'] = Auth::id();
        $validated['is_published'] = $request->boolean('is_published');
        
        if ($validated['is_published']) {
            $validated['published_at'] = now();
            $validated['status'] = 'scheduled';
        }

        $schedule = ExamSchedule::create($validated);

        return redirect()
            ->route('college.exam-schedules.show', $schedule)
            ->with('success', 'Exam schedule created successfully.');
    }

    /**
     * Display the specified exam schedule.
     */
    public function show(ExamSchedule $examSchedule)
    {
        $examSchedule->load(['academicYear', 'semester', 'program', 'course', 'invigilator', 'createdBy', 'updatedBy']);
        
        $enrolledStudents = $examSchedule->getEnrolledStudentsCount();

        return view('college.exam-schedules.show', compact('examSchedule', 'enrolledStudents'));
    }

    /**
     * Show the form for editing the specified exam schedule.
     */
    public function edit(ExamSchedule $examSchedule)
    {
        if (!$examSchedule->canEdit()) {
            return redirect()
                ->route('college.exam-schedules.show', $examSchedule)
                ->with('error', 'This exam schedule cannot be edited.');
        }

        $branchId = session('branch_id');
        $companyId = session('company_id');

        // Get academic years (no branch_id filter - shared data)
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        // Get semesters (no branch_id filter - shared data)
        $semesters = Semester::orderBy('name')->get();
        
        // Programs have branch_id
        $programs = Program::where('branch_id', $branchId)->orderBy('name')->get();
        
        // Get all courses
        $courses = Course::orderBy('code')->get();
        
        // Get employees as invigilators
        $invigilators = Employee::where('branch_id', $branchId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Get venues
        $venues = Venue::when($companyId, function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->when($branchId, function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })->where('is_active', true)->orderBy('name')->get();

        // Get academic levels for exam
        $levels = Level::where('branch_id', $branchId)
            ->active()
            ->ordered()
            ->get();

        $examTypes = ExamSchedule::EXAM_TYPES;
        $statuses = ExamSchedule::STATUSES;

        return view('college.exam-schedules.edit', compact(
            'examSchedule',
            'academicYears',
            'semesters',
            'programs',
            'courses',
            'invigilators',
            'venues',
            'levels',
            'examTypes',
            'statuses'
        ));
    }

    /**
     * Update the specified exam schedule.
     */
    public function update(Request $request, ExamSchedule $examSchedule)
    {
        if (!$examSchedule->canEdit()) {
            return redirect()
                ->route('college.exam-schedules.show', $examSchedule)
                ->with('error', 'This exam schedule cannot be edited.');
        }

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
            'program_id' => 'required|exists:college_programs,id',
            'course_id' => 'required|exists:courses,id',
            'level' => 'nullable|string|max:50',
            'exam_name' => 'required|string|max:255',
            'exam_type' => 'required|in:continuous_assessment,midterm,final,practical,oral,supplementary,retake,makeup,project,internship,online',
            'description' => 'nullable|string|max:1000',
            'exam_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'duration_minutes' => 'required|integer|min:30|max:480',
            'venue_id' => 'nullable|exists:college_venues,id',
            'building' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'number_of_students' => 'nullable|integer|min:1',
            'total_marks' => 'required|numeric|min:1|max:100',
            'pass_marks' => 'required|numeric|min:1|lte:total_marks',
            'invigilator_id' => 'nullable|exists:users,id',
            'invigilator_name' => 'nullable|string|max:255',
            'instructions' => 'nullable|string|max:2000',
            'materials_allowed' => 'nullable|array',
            'materials_allowed.*' => 'string|max:100',
            'status' => 'required|in:draft,scheduled,postponed',
            'status_remarks' => 'nullable|string|max:500',
            'is_published' => 'boolean',
        ]);

        // Get venue name from selected venue
        if (!empty($validated['venue_id'])) {
            $venue = Venue::find($validated['venue_id']);
            if ($venue) {
                $validated['venue'] = $venue->name . ' (' . $venue->code . ')';
            }
        }
        unset($validated['venue_id']);

        // Validate end_time is after start_time
        $startTime = strtotime($request->start_time);
        $endTime = strtotime($request->end_time);
        if ($endTime <= $startTime) {
            return back()->withErrors(['end_time' => 'The end time must be after the start time.'])->withInput();
        }

        $validated['updated_by'] = Auth::id();
        $validated['is_published'] = $request->boolean('is_published');
        
        if ($validated['is_published'] && !$examSchedule->is_published) {
            $validated['published_at'] = now();
        } elseif (!$validated['is_published']) {
            $validated['published_at'] = null;
        }

        $examSchedule->update($validated);

        return redirect()
            ->route('college.exam-schedules.show', $examSchedule)
            ->with('success', 'Exam schedule updated successfully.');
    }

    /**
     * Remove the specified exam schedule.
     */
    public function destroy(ExamSchedule $examSchedule)
    {
        if (!$examSchedule->canDelete()) {
            return redirect()
                ->route('college.exam-schedules.index')
                ->with('error', 'This exam schedule cannot be deleted.');
        }

        $examSchedule->delete();

        return redirect()
            ->route('college.exam-schedules.index')
            ->with('success', 'Exam schedule deleted successfully.');
    }

    /**
     * Publish an exam schedule.
     */
    public function publish(ExamSchedule $examSchedule)
    {
        $examSchedule->publish();

        return redirect()
            ->back()
            ->with('success', 'Exam schedule has been published.');
    }

    /**
     * Unpublish an exam schedule.
     */
    public function unpublish(ExamSchedule $examSchedule)
    {
        $examSchedule->unpublish();

        return redirect()
            ->back()
            ->with('success', 'Exam schedule has been unpublished.');
    }

    /**
     * Mark exam as ongoing.
     */
    public function markOngoing(ExamSchedule $examSchedule)
    {
        $examSchedule->markAsOngoing();

        return redirect()
            ->back()
            ->with('success', 'Exam marked as ongoing.');
    }

    /**
     * Mark exam as completed.
     */
    public function markCompleted(ExamSchedule $examSchedule)
    {
        $examSchedule->markAsCompleted();

        return redirect()
            ->back()
            ->with('success', 'Exam marked as completed.');
    }

    /**
     * Postpone an exam.
     */
    public function postpone(Request $request, ExamSchedule $examSchedule)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $examSchedule->postpone($request->reason);

        return redirect()
            ->back()
            ->with('success', 'Exam has been postponed.');
    }

    /**
     * Cancel an exam.
     */
    public function cancel(Request $request, ExamSchedule $examSchedule)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $examSchedule->cancel($request->reason);

        return redirect()
            ->back()
            ->with('success', 'Exam has been cancelled.');
    }

    /**
     * Reschedule an exam.
     */
    public function reschedule(Request $request, ExamSchedule $examSchedule)
    {
        $validated = $request->validate([
            'new_date' => 'required|date|after_or_equal:today',
            'new_start_time' => 'nullable|date_format:H:i',
            'new_end_time' => 'nullable|date_format:H:i|after:new_start_time',
        ]);

        $examSchedule->reschedule(
            $validated['new_date'],
            $validated['new_start_time'] ?? null,
            $validated['new_end_time'] ?? null
        );

        return redirect()
            ->back()
            ->with('success', 'Exam has been rescheduled.');
    }

    /**
     * Get courses by program (AJAX).
     */
    public function getCoursesByProgram(Request $request)
    {
        $programId = $request->program_id;

        $courses = Course::where('program_id', $programId)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        return response()->json($courses);
    }

    /**
     * Calendar view of exam schedules.
     */
    public function calendar(Request $request)
    {
        $branchId = session('branch_id');
        
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $schedules = ExamSchedule::forBranch($branchId)
            ->whereMonth('exam_date', $month)
            ->whereYear('exam_date', $year)
            ->with(['course', 'program'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $programs = Program::where('branch_id', $branchId)->orderBy('name')->get();

        return view('college.exam-schedules.calendar', compact('schedules', 'month', 'year', 'academicYears', 'programs'));
    }

    /**
     * Print exam schedule.
     */
    public function print(ExamSchedule $examSchedule)
    {
        $examSchedule->load(['academicYear', 'semester', 'program', 'course', 'invigilator']);
        
        return view('college.exam-schedules.print', compact('examSchedule'));
    }

    /**
     * Bulk print exam schedules.
     */
    public function bulkPrint(Request $request)
    {
        $branchId = session('branch_id');
        
        $query = ExamSchedule::forBranch($branchId)
            ->with(['academicYear', 'semester', 'program', 'course', 'invigilator']);

        if ($request->filled('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->filled('semester_id')) {
            $query->bySemester($request->semester_id);
        }

        if ($request->filled('program_id')) {
            $query->byProgram($request->program_id);
        }

        $schedules = $query->orderBy('exam_date')->orderBy('start_time')->get();

        return view('college.exam-schedules.bulk-print', compact('schedules'));
    }

    /**
     * Display master examination timetable (combined programs).
     */
    public function masterTimetable(Request $request)
    {
        $branchId = session('branch_id');
        $branch = \App\Models\Branch::find($branchId);
        
        $query = ExamSchedule::forBranch($branchId)
            ->with(['academicYear', 'semester', 'program', 'course', 'invigilator'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->orderBy('program_id');

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->filled('semester_id')) {
            $query->bySemester($request->semester_id);
        }

        if ($request->filled('program_id')) {
            $query->byProgram($request->program_id);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('exam_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('exam_date', '<=', $request->date_to);
        }

        // Filter by published status only if explicitly requested
        if ($request->filled('published_only') && $request->published_only == '1') {
            $query->where('is_published', true);
        }

        $schedules = $query->get();

        // Get filter options
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('name')->get();
        $programs = Program::where('branch_id', $branchId)->orderBy('name')->get();

        // Get current semester and academic year
        $semester = $request->filled('semester_id') 
            ? Semester::find($request->semester_id) 
            : Semester::where('status', 'active')->first();
        
        $academicYear = $request->filled('academic_year_id') 
            ? AcademicYear::find($request->academic_year_id) 
            : AcademicYear::where('status', 'active')->first();

        // Default exam duration
        $examDuration = '2 Hours';

        return view('college.exam-schedules.master-timetable', compact(
            'schedules',
            'academicYears',
            'semesters',
            'programs',
            'branch',
            'semester',
            'academicYear',
            'examDuration'
        ));
    }

    /**
     * Export master timetable as PDF.
     */
    public function masterTimetablePdf(Request $request)
    {
        $branchId = session('branch_id');
        $branch = \App\Models\Branch::find($branchId);
        
        $query = ExamSchedule::forBranch($branchId)
            ->with(['academicYear', 'semester', 'program', 'course', 'invigilator'])
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->orderBy('program_id');

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->byAcademicYear($request->academic_year_id);
        }

        if ($request->filled('semester_id')) {
            $query->bySemester($request->semester_id);
        }

        if ($request->filled('program_id')) {
            $query->byProgram($request->program_id);
        }

        if ($request->filled('exam_type')) {
            $query->where('exam_type', $request->exam_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('exam_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('exam_date', '<=', $request->date_to);
        }

        $schedules = $query->get();

        // Get current semester and academic year
        $semester = $request->filled('semester_id') 
            ? Semester::find($request->semester_id) 
            : Semester::where('status', 'active')->first();
        
        $academicYear = $request->filled('academic_year_id') 
            ? AcademicYear::find($request->academic_year_id) 
            : AcademicYear::where('status', 'active')->first();

        $examDuration = '2 Hours';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('college.exam-schedules.master-timetable-pdf', compact(
            'schedules',
            'branch',
            'semester',
            'academicYear',
            'examDuration'
        ));

        $pdf->setPaper('A4', 'landscape');

        $filename = 'Master_Examination_Timetable_' . now()->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}
