<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\CourseResult;
use App\Models\College\CourseRegistration;
use App\Models\College\AssessmentScore;
use App\Models\College\FinalExamScore;
use App\Models\College\Student;
use App\Models\College\AcademicYear;
use App\Models\College\Semester;
use App\Models\College\Program;
use App\Models\College\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseResultController extends Controller
{
    /**
     * Weight Configuration for Assessment Types
     * CA (Continuous Assessment) = 40%
     * Final Examination = 60%
     * Total = 100%
     */
    const CA_WEIGHT = 40;        // 40% for Continuous Assessment
    const EXAM_WEIGHT = 60;      // 60% for Final Examination
    const PASS_MARK = 35;        // 35% minimum to pass (Grade E)

    /**
     * Grading Scale Configuration (5-Point GPA System)
     * 
     * Marks (%)    | Grade | Grade Point | Remark
     * -------------|-------|-------------|------------------
     * 70 – 100     | A     | 5           | Excellent
     * 60 – 69      | B     | 4           | Very Good
     * 50 – 59      | C     | 3           | Good
     * 40 – 49      | D     | 2           | Pass
     * 35 – 39      | E     | 1           | Marginal Pass
     * 0 – 34       | F     | 0           | Fail
     * 
     * Classification:
     * 4.50 – 5.00 = First Class Honours
     * 3.50 – 4.49 = Second Class Upper
     * 2.40 – 3.49 = Second Class Lower
     * 1.50 – 2.39 = Third Class
     * 1.00 – 1.49 = Pass
     * Below 1.00  = Fail
     */
    private $gradingScale = [
        ['min' => 70, 'max' => 100, 'grade' => 'A', 'gpa' => 5.0, 'remark' => 'Excellent'],
        ['min' => 60, 'max' => 69.99, 'grade' => 'B', 'gpa' => 4.0, 'remark' => 'Very Good'],
        ['min' => 50, 'max' => 59.99, 'grade' => 'C', 'gpa' => 3.0, 'remark' => 'Good'],
        ['min' => 40, 'max' => 49.99, 'grade' => 'D', 'gpa' => 2.0, 'remark' => 'Pass'],
        ['min' => 35, 'max' => 39.99, 'grade' => 'E', 'gpa' => 1.0, 'remark' => 'Marginal Pass'],
        ['min' => 0, 'max' => 34.99, 'grade' => 'F', 'gpa' => 0.0, 'remark' => 'Fail'],
    ];

    /**
     * Display a listing of course results
     */
    public function index(Request $request)
    {
        // Get company_id and branch_id with fallbacks
        $branchId = session('branch_id') ?? Auth::user()->branch_id ?? 1;
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;
        
        $query = CourseResult::with([
            'student',
            'program',
            'course',
            'academicYear',
            'semester',
            'instructor'
        ]);

        // Only filter by company if set
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->filled('result_status')) {
            $query->where('result_status', $request->result_status);
        }

        if ($request->filled('course_status')) {
            $query->where('course_status', $request->course_status);
        }

        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }

        $results = $query->latest()->paginate(20)->withQueryString();

        // Get filter options
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();
        
        // Get programs with fallback
        $programs = Program::when($branchId, function($q) use ($branchId) {
            return $q->where('branch_id', $branchId);
        })->orderBy('name')->get();
        
        if ($programs->isEmpty()) {
            $programs = Program::orderBy('name')->get();
        }
        
        $courses = Course::orderBy('code')->get();
        
        // Get students with fallback
        $students = Student::when($companyId, function($q) use ($companyId) {
            return $q->where('company_id', $companyId);
        })->orderBy('first_name')->get();
        
        if ($students->isEmpty()) {
            $students = Student::orderBy('first_name')->get();
        }

        // Statistics - with fallback for company_id
        $statsQuery = CourseResult::query();
        if ($companyId) {
            $statsQuery->where('company_id', $companyId);
        }
        
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'draft' => (clone $statsQuery)->where('result_status', 'draft')->count(),
            'published' => (clone $statsQuery)->where('result_status', 'published')->count(),
            'approved' => (clone $statsQuery)->where('result_status', 'approved')->count(),
            'passed' => (clone $statsQuery)->whereIn('course_status', ['Pass', 'passed'])->count(),
            'failed' => (clone $statsQuery)->whereIn('course_status', ['Fail', 'failed'])->count(),
        ];

        return view('college.course-results.index', compact(
            'results',
            'academicYears',
            'semesters',
            'programs',
            'courses',
            'students',
            'stats'
        ));
    }

    /**
     * Generate results for a semester
     */
    public function generate(Request $request)
    {
        $branchId = session('branch_id');
        $companyId = session('company_id');

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();
        
        // Get programs - try branch_id first, then fallback to all if none found
        $programs = Program::when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })->orderBy('name')->get();
        
        // If no programs found with branch filter, get all programs
        if ($programs->isEmpty()) {
            $programs = Program::orderBy('name')->get();
        }
        
        $courses = Course::orderBy('code')->get();
        
        // Get current academic year - check by status or date range, fallback to latest
        $currentAcademicYear = AcademicYear::where('status', 'active')
            ->first()
            ?? AcademicYear::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first()
            ?? AcademicYear::orderBy('start_date', 'desc')->first();
        
        // Get current semester
        $currentSemester = Semester::orderBy('number')->first();

        return view('college.course-results.generate', compact(
            'academicYears',
            'semesters',
            'programs',
            'courses',
            'currentAcademicYear',
            'currentSemester'
        ));
    }

    /**
     * Process result generation
     */
    public function processGeneration(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
            'program_id' => 'nullable|exists:college_programs,id',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        // Get company_id and branch_id with fallbacks
        $branchId = session('branch_id') ?? Auth::user()->branch_id ?? 1;
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;

        DB::beginTransaction();
        try {
            // Get all course registrations for the period
            $query = CourseRegistration::with(['student', 'course', 'program'])
                ->where('academic_year_id', $request->academic_year_id)
                ->where('semester_id', $request->semester_id)
                ->where('status', 'registered');

            if ($request->filled('program_id')) {
                $query->where('program_id', $request->program_id);
            }

            if ($request->filled('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            $registrations = $query->get();
            $generatedCount = 0;

            foreach ($registrations as $registration) {
                // Check if result already exists
                $existingResult = CourseResult::where('course_registration_id', $registration->id)->first();
                if ($existingResult && $existingResult->result_status !== 'draft') {
                    continue; // Skip already published/approved results
                }

                // ============================================================
                // WEIGHTED GRADING CALCULATION
                // CA (Continuous Assessment) = 40% weight
                // Final Examination = 60% weight
                // ============================================================

                // Calculate CA Total (40% weight)
                // Get all assessment scores for this registration
                $caScores = AssessmentScore::where('course_registration_id', $registration->id)
                    ->whereIn('status', ['marked', 'published'])
                    ->with('courseAssessment')
                    ->get();
                
                // Calculate CA percentage (how well they performed across all CAs)
                $totalCaMarksObtained = $caScores->sum('score');
                $totalCaMaxMarks = $caScores->sum('max_marks');
                
                // CA Percentage out of 100
                $caPercentage = $totalCaMaxMarks > 0 ? ($totalCaMarksObtained / $totalCaMaxMarks) * 100 : 0;
                
                // CA Weighted Score (40% of total)
                $caWeightedScore = ($caPercentage / 100) * self::CA_WEIGHT;

                // Get Final Exam Score (60% weight)
                $examScore = FinalExamScore::where('course_registration_id', $registration->id)
                    ->whereIn('status', ['marked', 'published'])
                    ->first();

                // Exam Percentage out of 100
                $examPercentage = 0;
                if ($examScore && $examScore->max_marks > 0) {
                    $examPercentage = ($examScore->score / $examScore->max_marks) * 100;
                }
                
                // Exam Weighted Score (60% of total)
                $examWeightedScore = ($examPercentage / 100) * self::EXAM_WEIGHT;

                // Calculate Total Marks (out of 100)
                // Total = CA (40%) + Exam (60%)
                $totalMarks = $caWeightedScore + $examWeightedScore;
                
                // Ensure total doesn't exceed 100
                $totalMarks = min($totalMarks, 100);

                // Determine Grade and GPA
                $gradeInfo = $this->calculateGrade($totalMarks);

                // Determine Pass/Fail (Pass mark is 40%)
                $courseStatus = $totalMarks >= self::PASS_MARK ? 'passed' : 'failed';

                // Check attempt number
                $attemptNumber = CourseResult::where('student_id', $registration->student_id)
                    ->where('course_id', $registration->course_id)
                    ->count() + 1;

                // Create or Update Result
                CourseResult::updateOrCreate(
                    ['course_registration_id' => $registration->id],
                    [
                        'student_id' => $registration->student_id,
                        'program_id' => $registration->program_id,
                        'course_id' => $registration->course_id,
                        'academic_year_id' => $registration->academic_year_id,
                        'semester_id' => $registration->semester_id,
                        'attempt_number' => $attemptNumber,
                        'credit_hours' => $registration->course->credit_hours ?? 3,
                        'ca_total' => round($caWeightedScore, 2),      // CA weighted out of 40
                        'exam_total' => round($examWeightedScore, 2),  // Exam weighted out of 60
                        'total_marks' => round($totalMarks, 2),        // Total out of 100
                        'grade' => $gradeInfo['grade'],
                        'gpa_points' => $gradeInfo['gpa'],
                        'remark' => $gradeInfo['remark'],
                        'course_status' => $courseStatus,
                        'is_retake' => $attemptNumber > 1,
                        'result_status' => 'draft',
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]
                );

                $generatedCount++;
            }

            DB::commit();
            return redirect()->route('college.course-results.index')
                ->with('success', "Successfully generated {$generatedCount} course results!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error generating results: ' . $e->getMessage());
        }
    }

    /**
     * Store course results from preview form
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
            'results' => 'required|array',
            'results.*.course_registration_id' => 'required|exists:course_registrations,id',
        ]);

        // Get company_id and branch_id with fallbacks
        $branchId = session('branch_id') ?? Auth::user()->branch_id ?? 1;
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;

        DB::beginTransaction();
        try {
            $course = Course::findOrFail($request->course_id);
            $generatedCount = 0;

            foreach ($request->results as $resultData) {
                $registration = CourseRegistration::with(['student', 'course'])
                    ->findOrFail($resultData['course_registration_id']);

                // ============================================================
                // WEIGHTED GRADING CALCULATION
                // CA (Continuous Assessment) = 40% weight
                // Final Examination = 60% weight
                // ============================================================
                
                // Get CA weighted score (already weighted to 40%)
                $caWeightedScore = floatval($resultData['ca_total'] ?? 0);
                
                // Get Exam weighted score (already weighted to 60%)
                $examWeightedScore = floatval($resultData['exam_score'] ?? 0);
                
                // Total = CA (40%) + Exam (60%) = 100%
                $totalMarks = $caWeightedScore + $examWeightedScore;
                
                // Ensure total doesn't exceed 100
                $totalMarks = min($totalMarks, 100);
                
                // Recalculate grade and GPA based on total marks
                $gradeInfo = $this->calculateGrade($totalMarks);
                $grade = $gradeInfo['grade'];
                $gpa = $gradeInfo['gpa'];
                $remark = $gradeInfo['remark'];

                // Determine Pass/Fail (Pass mark is 40%)
                $courseStatus = $totalMarks >= self::PASS_MARK ? 'passed' : 'failed';

                // Check attempt number
                $attemptNumber = CourseResult::where('student_id', $registration->student_id)
                    ->where('course_id', $registration->course_id)
                    ->where('course_registration_id', '!=', $registration->id)
                    ->count() + 1;

                // Determine initial status
                $resultStatus = 'draft';
                if ($request->has('publish_results')) {
                    $resultStatus = 'published';
                }
                if ($request->has('auto_approve')) {
                    $resultStatus = 'approved';
                }

                // Create or Update Result
                CourseResult::updateOrCreate(
                    ['course_registration_id' => $registration->id],
                    [
                        'student_id' => $registration->student_id,
                        'program_id' => $registration->program_id,
                        'course_id' => $registration->course_id,
                        'academic_year_id' => $request->academic_year_id,
                        'semester_id' => $request->semester_id,
                        'attempt_number' => $attemptNumber,
                        'credit_hours' => $course->credit_hours ?? 3,
                        'ca_total' => round($caWeightedScore, 2),      // CA weighted out of 40
                        'exam_total' => round($examWeightedScore, 2),  // Exam weighted out of 60
                        'total_marks' => round($totalMarks, 2),        // Total out of 100
                        'grade' => $grade,
                        'gpa_points' => $gpa,
                        'remark' => $remark,
                        'course_status' => $courseStatus,
                        'is_retake' => $attemptNumber > 1,
                        'result_status' => $resultStatus,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]
                );

                $generatedCount++;
            }

            DB::commit();
            return redirect()->route('college.course-results.index')
                ->with('success', "Successfully saved {$generatedCount} course results!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving results: ' . $e->getMessage());
        }
    }

    /**
     * Get remark from grade
     */
    private function getRemarkFromGrade($grade)
    {
        $remarks = [
            'A+' => 'Excellent', 'A' => 'Excellent', 'A-' => 'Very Good',
            'B+' => 'Good', 'B' => 'Good', 'B-' => 'Above Average',
            'C+' => 'Average', 'C' => 'Average', 'C-' => 'Below Average',
            'D+' => 'Poor', 'D' => 'Poor',
            'F' => 'Fail',
        ];
        return $remarks[$grade] ?? 'N/A';
    }

    /**
     * Generate all results for a semester (AJAX)
     */
    public function generateAll(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
        ]);

        // Get company_id and branch_id with fallbacks
        $branchId = session('branch_id') ?? Auth::user()->branch_id ?? 1;
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;

        DB::beginTransaction();
        try {
            // Get all course registrations for the period
            $registrations = CourseRegistration::with(['student', 'course', 'program', 'assessmentScores', 'finalExamScore'])
                ->where('academic_year_id', $request->academic_year_id)
                ->where('semester_id', $request->semester_id)
                ->where('status', 'registered')
                ->get();

            $generatedCount = 0;

            foreach ($registrations as $registration) {
                // ============================================================
                // WEIGHTED GRADING CALCULATION
                // CA (Continuous Assessment) = 40% weight
                // Final Examination = 60% weight
                // ============================================================

                // Calculate CA Weighted Score (40% of total)
                $caScores = $registration->assessmentScores
                    ->whereIn('status', ['marked', 'published']);
                    
                $totalCaMarksObtained = $caScores->sum('score');
                $totalCaMaxMarks = $caScores->sum('max_marks');
                
                $caPercentage = $totalCaMaxMarks > 0 ? ($totalCaMarksObtained / $totalCaMaxMarks) * 100 : 0;
                $caWeightedScore = ($caPercentage / 100) * self::CA_WEIGHT;

                // Calculate Exam Weighted Score (60% of total)
                $examScore = $registration->finalExamScore;
                $examPercentage = 0;
                if ($examScore && in_array($examScore->status, ['marked', 'published']) && $examScore->max_marks > 0) {
                    $examPercentage = ($examScore->score / $examScore->max_marks) * 100;
                }
                $examWeightedScore = ($examPercentage / 100) * self::EXAM_WEIGHT;

                // Calculate Total Marks (out of 100)
                $totalMarks = min($caWeightedScore + $examWeightedScore, 100);

                // Skip if no scores at all
                if ($caWeightedScore == 0 && $examWeightedScore == 0) {
                    continue;
                }

                // Determine Grade and GPA
                $gradeInfo = $this->calculateGrade($totalMarks);

                // Create or update course result
                CourseResult::updateOrCreate(
                    [
                        'course_registration_id' => $registration->id,
                    ],
                    [
                        'student_id' => $registration->student_id,
                        'program_id' => $registration->program_id,
                        'course_id' => $registration->course_id,
                        'academic_year_id' => $request->academic_year_id,
                        'semester_id' => $request->semester_id,
                        'credit_hours' => $registration->course->credit_hours ?? 3,
                        'ca_total' => round($caWeightedScore, 2),      // CA weighted out of 40
                        'exam_total' => round($examWeightedScore, 2),  // Exam weighted out of 60
                        'total_marks' => round($totalMarks, 2),        // Total out of 100
                        'grade' => $gradeInfo['grade'],
                        'gpa_points' => $gradeInfo['gpa'],
                        'remark' => $gradeInfo['remark'],
                        'course_status' => $totalMarks >= self::PASS_MARK ? 'passed' : 'failed',
                        'result_status' => 'draft',
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]
                );

                $generatedCount++;
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Successfully generated {$generatedCount} course results!"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified result
     */
    public function show(CourseResult $courseResult)
    {
        $courseResult->load([
            'student',
            'program',
            'course',
            'academicYear',
            'semester',
            'instructor',
            'courseRegistration'
        ]);

        // Get assessment breakdown
        $assessmentScores = AssessmentScore::with('courseAssessment.assessmentType')
            ->where('course_registration_id', $courseResult->course_registration_id)
            ->get();

        // Get exam score
        $examScore = FinalExamScore::with('finalExam')
            ->where('course_registration_id', $courseResult->course_registration_id)
            ->first();

        return view('college.course-results.show', compact('courseResult', 'assessmentScores', 'examScore'));
    }

    /**
     * Publish result
     */
    public function publish(CourseResult $courseResult)
    {
        $courseResult->update([
            'result_status' => 'published',
            'published_by' => Auth::id(),
            'published_date' => now(),
        ]);

        return back()->with('success', 'Result published successfully! Student can now view the final result.');
    }

    /**
     * Approve result
     */
    public function approve(CourseResult $courseResult)
    {
        $courseResult->update([
            'result_status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_date' => now(),
        ]);

        return back()->with('success', 'Result approved successfully!');
    }

    /**
     * Bulk publish results
     */
    public function bulkPublish(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
        ]);

        $branchId = session('branch_id') ?? Auth::user()->branch_id ?? 1;
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;

        $count = CourseResult::where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('result_status', 'draft')
            ->where('company_id', $companyId)
            ->update([
                'result_status' => 'published',
                'published_by' => Auth::id(),
                'published_date' => now(),
            ]);

        if ($count > 0) {
            return redirect()->route('college.course-results.index', [
                'academic_year_id' => $request->academic_year_id,
                'semester_id' => $request->semester_id
            ])->with('success', "{$count} results published successfully! Students can now view their results.");
        } else {
            return back()->with('warning', 'No draft results found to publish for the selected period.');
        }
    }

    /**
     * Bulk approve results
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
        ]);

        $branchId = session('branch_id') ?? Auth::user()->branch_id ?? 1;
        $companyId = session('company_id') ?? Auth::user()->company_id ?? 1;

        $count = CourseResult::where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('result_status', 'published')
            ->where('company_id', $companyId)
            ->update([
                'result_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_date' => now(),
            ]);

        if ($count > 0) {
            return redirect()->route('college.course-results.index', [
                'academic_year_id' => $request->academic_year_id,
                'semester_id' => $request->semester_id
            ])->with('success', "{$count} results approved successfully! These results are now official.");
        } else {
            return back()->with('warning', 'No published results found to approve for the selected period.');
        }
    }

    /**
     * Student view - My Final Results
     */
    public function myResults(Request $request)
    {
        $studentId = Auth::user()->student_id ?? null;
        
        if (!$studentId) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $query = CourseResult::with([
            'course',
            'academicYear',
            'semester',
            'program'
        ])
        ->where('student_id', $studentId)
        ->whereIn('result_status', ['published', 'approved']);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }

        $results = $query->orderBy('academic_year_id', 'desc')
            ->orderBy('semester_id')
            ->get()
            ->groupBy(function ($result) {
                return $result->academicYear->name . ' - ' . $result->semester->name;
            });

        // Calculate semester GPA and CGPA
        $semesterStats = [];
        $totalQualityPoints = 0;
        $totalCreditHours = 0;

        foreach ($results as $period => $periodResults) {
            $periodQualityPoints = 0;
            $periodCreditHours = 0;

            foreach ($periodResults as $result) {
                $periodQualityPoints += $result->gpa_points * $result->credit_hours;
                $periodCreditHours += $result->credit_hours;
            }

            $semesterGpa = $periodCreditHours > 0 ? round($periodQualityPoints / $periodCreditHours, 2) : 0;
            
            $semesterStats[$period] = [
                'gpa' => $semesterGpa,
                'credit_hours' => $periodCreditHours,
                'courses_passed' => $periodResults->where('course_status', 'Pass')->count(),
                'courses_failed' => $periodResults->where('course_status', 'Fail')->count(),
            ];

            $totalQualityPoints += $periodQualityPoints;
            $totalCreditHours += $periodCreditHours;
        }

        $cgpa = $totalCreditHours > 0 ? round($totalQualityPoints / $totalCreditHours, 2) : 0;

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();

        return view('college.student-portal.final-results', compact(
            'results',
            'semesterStats',
            'cgpa',
            'totalCreditHours',
            'academicYears',
            'semesters'
        ));
    }

    /**
     * Student Transcript
     */
    public function transcript(Request $request, $studentId = null)
    {
        // If studentId is not provided, use logged-in student
        if (!$studentId) {
            $studentId = Auth::user()->student_id ?? null;
        }

        if (!$studentId) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $student = Student::with('program')->findOrFail($studentId);

        $results = CourseResult::with([
            'course',
            'academicYear',
            'semester'
        ])
        ->where('student_id', $studentId)
        ->whereIn('result_status', ['published', 'approved'])
        ->orderBy('academic_year_id')
        ->orderBy('semester_id')
        ->get()
        ->groupBy(function ($result) {
            return $result->academic_year_id . '_' . $result->semester_id;
        });

        // Calculate CGPA using 5-point GPA system
        $totalQualityPoints = 0;
        $totalCreditHours = 0;
        $semesterData = [];
        $passedCourses = 0;
        $failedCourses = 0;

        foreach ($results as $key => $periodResults) {
            $firstResult = $periodResults->first();
            $periodQualityPoints = 0;
            $periodCreditHours = 0;
            $periodPassed = 0;
            $periodFailed = 0;

            foreach ($periodResults as $result) {
                // Quality Points = Credit Hours × GPA Points
                $periodQualityPoints += $result->gpa_points * $result->credit_hours;
                $periodCreditHours += $result->credit_hours;

                // Count passed/failed
                if ($result->course_status === 'passed' || $result->gpa_points >= 1.0) {
                    $periodPassed++;
                    $passedCourses++;
                } else {
                    $periodFailed++;
                    $failedCourses++;
                }
            }

            // Semester GPA = Total Quality Points ÷ Total Credit Hours
            $semesterGpa = $periodCreditHours > 0 ? round($periodQualityPoints / $periodCreditHours, 2) : 0;
            
            $totalQualityPoints += $periodQualityPoints;
            $totalCreditHours += $periodCreditHours;

            $semesterData[$key] = [
                'academic_year' => $firstResult->academicYear->name,
                'semester' => $firstResult->semester->name,
                'results' => $periodResults,
                'gpa' => $semesterGpa,
                'classification' => $this->getGPAClassification($semesterGpa),
                'credit_hours' => $periodCreditHours,
                'quality_points' => round($periodQualityPoints, 2),
                'passed' => $periodPassed,
                'failed' => $periodFailed,
            ];
        }

        // CGPA = Overall Quality Points ÷ Overall Credit Hours
        $cgpa = $totalCreditHours > 0 ? round($totalQualityPoints / $totalCreditHours, 2) : 0;
        $finalClassification = $this->getGPAClassification($cgpa);

        return view('college.student-portal.transcript', compact(
            'student',
            'semesterData',
            'cgpa',
            'finalClassification',
            'totalCreditHours',
            'passedCourses',
            'failedCourses'
        ));
    }

    /**
     * Get GPA Classification
     * 
     * 4.50 – 5.00 = First Class Honours
     * 3.50 – 4.49 = Second Class Upper
     * 2.40 – 3.49 = Second Class Lower
     * 1.50 – 2.39 = Third Class
     * 1.00 – 1.49 = Pass
     * Below 1.00  = Fail
     */
    private function getGPAClassification(float $gpa): array
    {
        $classifications = [
            ['min' => 4.50, 'max' => 5.00, 'class' => 'First Class Honours', 'code' => '1st', 'color' => 'success'],
            ['min' => 3.50, 'max' => 4.49, 'class' => 'Second Class Upper', 'code' => '2.1', 'color' => 'info'],
            ['min' => 2.40, 'max' => 3.49, 'class' => 'Second Class Lower', 'code' => '2.2', 'color' => 'primary'],
            ['min' => 1.50, 'max' => 2.39, 'class' => 'Third Class', 'code' => '3rd', 'color' => 'warning'],
            ['min' => 1.00, 'max' => 1.49, 'class' => 'Pass', 'code' => 'Pass', 'color' => 'secondary'],
            ['min' => 0, 'max' => 0.99, 'class' => 'Fail', 'code' => 'Fail', 'color' => 'danger'],
        ];

        foreach ($classifications as $class) {
            if ($gpa >= $class['min'] && $gpa <= $class['max']) {
                return $class;
            }
        }

        return ['class' => 'Fail', 'code' => 'Fail', 'color' => 'danger', 'min' => 0, 'max' => 0];
    }

    /**
     * Calculate grade from total marks
     */
    private function calculateGrade($totalMarks)
    {
        // Ensure total marks is capped at 100 for grading purposes
        // If raw scores exceed 100, treat as 100 (max grade)
        $normalizedMarks = min($totalMarks, 100);
        
        foreach ($this->gradingScale as $scale) {
            if ($normalizedMarks >= $scale['min'] && $normalizedMarks <= $scale['max']) {
                return $scale;
            }
        }

        return ['grade' => 'F', 'gpa' => 0.0, 'remark' => 'Fail'];
    }

    /**
     * Calculate results preview (API)
     */
    public function calculatePreview(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'academic_year_id' => 'required|exists:college_academic_years,id',
            'semester_id' => 'required|exists:college_semesters,id',
        ]);

        $registrations = CourseRegistration::with(['student', 'assessmentScores', 'finalExamScore'])
            ->where('course_id', $request->course_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->where('status', 'registered')
            ->get();

        $students = [];
        $totals = [];

        foreach ($registrations as $registration) {
            // Calculate CA Total
            $caTotal = $registration->assessmentScores
                ->whereIn('status', ['marked', 'published'])
                ->sum('weighted_score');

            // Get Final Exam Score (using singular relationship)
            $examScore = $registration->finalExamScore;

            $examTotal = ($examScore && in_array($examScore->status, ['marked', 'published'])) 
                ? ($examScore->weighted_score ?? $examScore->score ?? 0) 
                : null;

            // Calculate Total Marks
            $totalMarks = $caTotal + ($examTotal ?? 0);

            // Determine Grade and GPA
            $gradeInfo = $this->calculateGrade($totalMarks);

            $totals[] = $totalMarks;

            $students[] = [
                'registration_id' => $registration->id,
                'student_id' => $registration->student->student_number,
                'name' => $registration->student->full_name,
                'ca_total' => round($caTotal, 2),
                'exam_score' => $examTotal !== null ? round($examTotal, 2) : null,
                'total' => round($totalMarks, 2),
                'grade' => $gradeInfo['grade'],
                'gpa' => $gradeInfo['gpa'],
            ];
        }

        // Calculate statistics
        $total = count($students);
        $passed = count(array_filter($totals, fn($t) => $t >= 40));
        $average = $total > 0 ? array_sum($totals) / $total : 0;
        $highest = $total > 0 ? max($totals) : 0;
        $lowest = $total > 0 ? min($totals) : 0;
        $totalGpa = array_sum(array_column($students, 'gpa'));
        $classGpa = $total > 0 ? $totalGpa / $total : 0;

        return response()->json([
            'students' => $students,
            'stats' => [
                'total' => $total,
                'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
                'average' => $average,
                'highest' => $highest,
                'lowest' => $lowest,
                'class_gpa' => $classGpa,
            ]
        ]);
    }
}
