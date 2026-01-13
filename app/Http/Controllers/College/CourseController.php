<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\AcademicYear;
use App\Models\College\Course;
use App\Models\College\CourseDetail;
use App\Models\College\CourseEnrollment;
use App\Models\College\Program;
use App\Models\College\Semester;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class CourseController extends Controller
{
    /**
     * Display a listing of the courses
     */
    public function index()
    {
        $programs = Program::all();
        return view('college.courses.index', compact('programs'));
    }

    /**
     * Get courses data for DataTable
     */
    public function getCoursesData(Request $request)
    {
        $courses = Course::with(['program', 'createdBy', 'updatedBy'])
            ->withCount(['enrollments as student_count' => function ($query) {
                $query->where('status', 'enrolled');
            }]);

        // Filter by program
        if ($request->filled('program_id')) {
            $courses->where('program_id', $request->program_id);
        }

        // Filter by course name or code
        if ($request->filled('course')) {
            $search = $request->course;
            $courses->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $courses->where('status', $request->status);
        }

        $courses->orderBy('created_at', 'desc');

        return DataTables::of($courses)
            ->addColumn('program_name', fn($row) => $row->program?->name ?? 'N/A')
            ->addColumn('student_count', function ($row) {
                $count = $row->student_count ?? 0;
                $color = $count > 0 ? 'success' : 'secondary';
                return "<span class='badge bg-{$color}'><i class='bx bx-user me-1'></i>{$count}</span>";
            })
            ->addColumn('created_by_name', fn($row) => $row->createdBy?->name ?? 'N/A')
            ->addColumn('updated_by_name', fn($row) => $row->updatedBy?->name ?? 'N/A')
            ->addColumn('status_badge', function ($row) {
                if ($row->status === 'active') {
                    return "<span class='badge bg-success fw-bold' style='padding: 0.5rem 0.75rem; font-size: 0.85rem;'>Active</span>";
                } else {
                    return "<span class='badge bg-danger fw-bold' style='padding: 0.5rem 0.75rem; font-size: 0.85rem;'>Inactive</span>";
                }
            })
            ->addColumn('action', function ($row) {
                return "
                    <div class='btn-group btn-group-sm' role='group'>
                        <a href='" . route('college.courses.show', $row->id) . "' class='btn btn-info' title='View'>
                            <i class='bx bx-show'></i>
                        </a>
                        <a href='" . route('college.courses.edit', $row->id) . "' class='btn btn-warning' title='Edit'>
                            <i class='bx bx-pencil'></i>
                        </a>
                        <button class='btn btn-danger delete-btn' data-id='{$row->id}' title='Delete'>
                            <i class='bx bx-trash'></i>
                        </button>
                    </div>
                ";
            })
            ->rawColumns(['status_badge', 'student_count', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $programs = Program::all();
        $semesters = Semester::orderBy('name')->get();
        return view('college.courses.create', compact('programs', 'semesters'));
    }

    /**
     * Store a newly created course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'code' => 'required|string|max:20|unique:courses,code',
            'name' => 'required|string|max:255',
            'credit_hours' => 'required|integer|min:1|max:12',
            'semester' => 'required|integer|min:1|max:8',
            'level' => 'required|in:Certificate,Diploma,Degree,Masters,PhD',
            'prerequisites' => 'nullable|string',
            'core_elective' => 'required|in:Core,Elective',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $course = Course::create($validated);

        return redirect()->route('college.courses.index')
                        ->with('success', 'Course created successfully!');
    }

    /**
     * Show the form for editing a course
     */
    public function edit(Course $course)
    {
        $programs = Program::all();
        $semesters = Semester::orderBy('name')->get();
        return view('college.courses.edit', compact('course', 'programs', 'semesters'));
    }

    /**
     * Get a specific course
     */
    public function show(Course $course)
    {
        $course->load(['program', 'createdBy', 'updatedBy']);
        
        // Get enrolled students for this course
        $enrolledStudents = CourseEnrollment::with(['student.program.department', 'academicYear', 'semester'])
            ->where('course_id', $course->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get instructor assignment history
        $instructorHistory = CourseDetail::with(['employee', 'assignedByUser'])
            ->where('course_id', $course->id)
            ->orderBy('date_assigned', 'desc')
            ->get();
        
        // Get all current active instructors (multiple instructors can teach the same course)
        $activeInstructors = CourseDetail::with(['employee', 'assignedByUser'])
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->orderBy('date_assigned', 'desc')
            ->get();
        
        // Get employees for the modal dropdown
        $employees = Employee::where('status', 'active')
            ->orderBy('first_name')
            ->get();
        
        // Get academic years for the modal dropdown
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        
        return view('college.courses.show', compact('course', 'enrolledStudents', 'instructorHistory', 'activeInstructors', 'employees', 'academicYears'));
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:college_programs,id',
            'code' => 'required|string|max:20|unique:courses,code,' . $course->id,
            'name' => 'required|string|max:255',
            'credit_hours' => 'required|integer|min:1|max:12',
            'semester' => 'required|integer|min:1|max:8',
            'level' => 'required|in:Certificate,Diploma,Degree,Masters,PhD',
            'prerequisites' => 'nullable|string',
            'core_elective' => 'required|in:Core,Elective',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['updated_by'] = auth()->id();

        $course->update($validated);

        return redirect()->route('college.courses.index')
                        ->with('success', 'Course updated successfully!');
    }

    /**
     * Remove the specified course
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json(['message' => 'Course deleted successfully']);
    }

    /**
     * Bulk delete courses
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->get('ids', []);
        
        if (empty($ids)) {
            return response()->json(['message' => 'No courses selected'], 400);
        }

        Course::whereIn('id', $ids)->delete();

        return response()->json(['message' => count($ids) . ' course(s) deleted successfully']);
    }

    /**
     * Assign instructor to course
     */
    public function assignInstructor(Request $request, Course $course)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'academic_year' => 'required|string|max:20',
            'semester' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if this exact assignment already exists
        $existingAssignment = CourseDetail::where('course_id', $course->id)
            ->where('employee_id', $validated['employee_id'])
            ->where('academic_year', $validated['academic_year'])
            ->where('semester', $validated['semester'])
            ->first();

        if ($existingAssignment) {
            // Reactivate if archived, or update if already exists
            $existingAssignment->update([
                'date_assigned' => now(),
                'status' => 'active',
                'assigned_by' => Auth::id(),
                'notes' => $validated['notes'],
            ]);
        } else {
            // Create new assignment (allows multiple instructors)
            CourseDetail::create([
                'course_id' => $course->id,
                'employee_id' => $validated['employee_id'],
                'academic_year' => $validated['academic_year'],
                'semester' => $validated['semester'],
                'date_assigned' => now(),
                'status' => 'active',
                'assigned_by' => Auth::id(),
                'notes' => $validated['notes'],
            ]);
        }

        return redirect()->route('college.courses.show', $course->id)
            ->with('success', 'Instructor assigned successfully!');
    }

    /**
     * Remove instructor assignment (archive it)
     */
    public function removeInstructor(Course $course, CourseDetail $courseDetail)
    {
        if ($courseDetail->course_id !== $course->id) {
            return redirect()->back()->with('error', 'Invalid assignment.');
        }

        $courseDetail->update(['status' => 'archived']);

        return redirect()->route('college.courses.show', $course->id)
            ->with('success', 'Instructor assignment archived successfully!');
    }

    /**
     * Get courses by program (AJAX endpoint)
     */
    public function byProgram($programId)
    {
        $courses = Course::where('program_id', $programId)
            ->where('status', 'active')
            ->orderBy('semester')
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'semester', 'credit_hours']);

        return response()->json($courses);
    }
}
