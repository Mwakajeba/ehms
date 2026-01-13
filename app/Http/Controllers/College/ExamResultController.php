<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\CourseResult;
use App\Models\College\Program;
use App\Models\College\AcademicYear;
use App\Models\College\Semester;
use App\Models\College\Course;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseResult::with([
            'student.user',
            'course',
            'program',
            'academicYear',
            'semester',
            'instructor'
        ]);

        // Filters
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('result_status')) {
            $query->where('result_status', $request->result_status);
        }

        if ($request->filled('course_status')) {
            $query->where('course_status', $request->course_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student.user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $results = $query->latest()->paginate(20);

        // For filters
        $programs = Program::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->get();

        $academicYears = AcademicYear::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->get();

        $semesters = Semester::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->get();

        $courses = Course::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->get();

        // Statistics
        $stats = [
            'total_results' => CourseResult::where('company_id', auth()->user()->company_id)->count(),
            'passed' => CourseResult::where('company_id', auth()->user()->company_id)
                ->where('course_status', 'passed')->count(),
            'failed' => CourseResult::where('company_id', auth()->user()->company_id)
                ->where('course_status', 'failed')->count(),
            'pending' => CourseResult::where('company_id', auth()->user()->company_id)
                ->where('result_status', 'draft')->count(),
            'approved' => CourseResult::where('company_id', auth()->user()->company_id)
                ->where('result_status', 'approved')->count(),
        ];

        return view('college.exam-results.index', compact(
            'results',
            'programs',
            'academicYears',
            'semesters',
            'courses',
            'stats'
        ));
    }

    public function show($id)
    {
        $result = CourseResult::with([
            'student.user',
            'course',
            'program',
            'academicYear',
            'semester',
            'instructor',
            'courseRegistration.assessmentScores.courseAssessment.assessmentType',
            'courseRegistration.finalExamScore.finalExam'
        ])->findOrFail($id);

        return view('college.exam-results.show', compact('result'));
    }
}
