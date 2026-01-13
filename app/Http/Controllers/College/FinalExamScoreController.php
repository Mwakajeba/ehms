<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\FinalExamScore;
use App\Models\College\FinalExam;
use App\Models\College\ExamSchedule;
use App\Models\College\CourseRegistration;
use App\Models\College\Student;
use App\Models\College\AcademicYear;
use App\Models\College\Semester;
use App\Models\College\Program;
use App\Models\College\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinalExamScoreController extends Controller
{
    /**
     * Display a listing of final exam scores
     */
    public function index(Request $request)
    {
        $branchId = session('branch_id');
        $companyId = session('company_id');
        
        $query = FinalExamScore::with([
            'examSchedule.course',
            'examSchedule.academicYear',
            'examSchedule.semester',
            'student',
            'courseRegistration'
        ]);

        // Apply filters
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('academic_year_id')) {
            $query->whereHas('examSchedule', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            });
        }

        if ($request->filled('semester_id')) {
            $query->whereHas('examSchedule', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id);
            });
        }

        $scores = $query->latest()->paginate(20)->withQueryString();

        // Get filter options
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();
        $courses = Course::orderBy('code')->get();
        $students = Student::orderBy('first_name')->get();

        // Statistics
        $stats = [
            'total' => FinalExamScore::count(),
            'absent' => FinalExamScore::where('status', 'absent')->count(),
            'marked' => FinalExamScore::where('status', 'marked')->count(),
            'published' => FinalExamScore::where('status', 'published')->count(),
        ];

        return view('college.final-exam-scores.index', compact(
            'scores',
            'academicYears',
            'semesters',
            'courses',
            'students',
            'stats'
        ));
    }

    /**
     * Show form to enter final exam scores
     */
    public function create(Request $request)
    {
        $branchId = session('branch_id');
        $companyId = session('company_id');

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();
        $programs = Program::where('branch_id', $branchId)->orderBy('name')->get();
        
        // Get all courses through programs in this branch
        $programIds = $programs->pluck('id');
        $courses = Course::whereIn('program_id', $programIds)
            ->where('status', 'active')
            ->orderBy('code')
            ->get();
        
        // Get all students for the branch
        $students = Student::where('branch_id', $branchId)
            ->orderBy('first_name')
            ->get();
        
        $currentAcademicYear = AcademicYear::where('status', 'active')->first();
        $currentSemester = Semester::where('status', 'active')->first();
        
        // Get exam schedules (final exams) from Exam Schedules
        $examSchedules = ExamSchedule::with('course')
            ->where('branch_id', $branchId)
            ->whereIn('exam_type', ['final', 'midterm', 'supplementary', 'retake'])
            ->orderBy('exam_date', 'desc')
            ->get();

        return view('college.final-exam-scores.create', compact(
            'academicYears',
            'semesters',
            'programs',
            'courses',
            'students',
            'examSchedules',
            'currentAcademicYear',
            'currentSemester'
        ));
    }

    /**
     * Store final exam scores
     */
    public function store(Request $request)
    {
        $examSchedule = ExamSchedule::findOrFail($request->exam_schedule_id);
        $maxScore = $examSchedule->total_marks ?? 100;

        $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'course_registration_id' => 'required|exists:course_registrations,id',
            'score' => 'required|numeric|min:0|max:' . $maxScore,
            'remarks' => 'nullable|string|max:500',
        ], [
            'score.max' => 'Score cannot exceed the maximum marks (' . $maxScore . ')',
        ]);

        $branchId = session('branch_id');
        $companyId = session('company_id');

        $registration = CourseRegistration::findOrFail($request->course_registration_id);
        
        // Check if student has paid fees before allowing exam score entry
        $student = Student::findOrFail($registration->student_id);
        if ($student->hasOutstandingFees()) {
            $outstandingBalance = number_format($student->getOutstandingBalance(), 2);
            return back()
                ->withInput()
                ->with('error', "Cannot enter exam scores for {$student->full_name}. This student has an outstanding fee balance of TZS {$outstandingBalance}. Please ensure the student clears their fees before taking the examination.");
        }

        DB::beginTransaction();
        try {
            // Calculate weighted score
            $weightedScore = null;
            if ($examSchedule->total_marks > 0) {
                $weightedScore = ($request->score / $examSchedule->total_marks) * 100;
            }

            FinalExamScore::updateOrCreate(
                [
                    'exam_schedule_id' => $examSchedule->id,
                    'student_id' => $registration->student_id,
                ],
                [
                    'course_registration_id' => $registration->id,
                    'course_id' => $examSchedule->course_id,
                    'score' => $request->score,
                    'max_marks' => $examSchedule->total_marks ?? 100,
                    'weighted_score' => $weightedScore,
                    'remarks' => $request->remarks,
                    'marked_by' => Auth::id(),
                    'marked_date' => now(),
                    'status' => 'marked',
                ]
            );

            DB::commit();
            return redirect()->route('college.final-exam-scores.index')
                ->with('success', 'Final exam scores saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving scores: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified final exam score
     */
    public function show(FinalExamScore $finalExamScore)
    {
        $finalExamScore->load([
            'examSchedule.course',
            'examSchedule.academicYear',
            'examSchedule.semester',
            'student',
            'courseRegistration',
            'markedBy'
        ]);

        return view('college.final-exam-scores.show', compact('finalExamScore'));
    }

    /**
     * Show form to edit score
     */
    public function edit(FinalExamScore $finalExamScore)
    {
        $finalExamScore->load([
            'examSchedule.course',
            'student'
        ]);

        return view('college.final-exam-scores.edit', compact('finalExamScore'));
    }

    /**
     * Update the specified score
     */
    public function update(Request $request, FinalExamScore $finalExamScore)
    {
        $request->validate([
            'score' => 'nullable|numeric|min:0|max:' . $finalExamScore->max_marks,
            'status' => 'required|in:absent,marked',
            'remarks' => 'nullable|string|max:500',
        ]);

        $finalExam = $finalExamScore->finalExam;
        $weightedScore = null;
        
        if ($request->status === 'marked' && $request->score !== null) {
            $weightedScore = ($request->score / $finalExam->max_marks) * $finalExam->weight_percentage;
        }

        $finalExamScore->update([
            'score' => $request->status === 'marked' ? $request->score : null,
            'weighted_score' => $weightedScore,
            'status' => $request->status,
            'remarks' => $request->remarks,
            'marked_by' => Auth::id(),
            'marked_date' => now(),
        ]);

        return redirect()->route('college.final-exam-scores.index')
            ->with('success', 'Score updated successfully!');
    }

    /**
     * Publish score (make visible to student)
     */
    public function publish(FinalExamScore $finalExamScore)
    {
        $finalExamScore->update([
            'status' => 'published',
            'published_by' => Auth::id(),
            'published_date' => now(),
        ]);

        return back()->with('success', 'Score published successfully! Student can now view this result.');
    }

    /**
     * Bulk publish scores for an exam
     */
    public function bulkPublish(Request $request)
    {
        $request->validate([
            'final_exam_id' => 'required|exists:final_exams,id',
        ]);

        FinalExamScore::where('final_exam_id', $request->final_exam_id)
            ->whereIn('status', ['marked', 'absent'])
            ->update([
                'status' => 'published',
                'published_by' => Auth::id(),
                'published_date' => now(),
            ]);

        return back()->with('success', 'All scores published successfully!');
    }

    /**
     * Bulk entry form for final exam scores
     */
    public function bulkEntry(Request $request)
    {
        $branchId = session('branch_id');
        $companyId = session('company_id');

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();
        $programs = Program::where('branch_id', $branchId)->orderBy('name')->get();
        
        $currentAcademicYear = AcademicYear::where('status', 'active')->first();
        $currentSemester = Semester::where('status', 'active')->first();

        return view('college.final-exam-scores.bulk-entry', compact(
            'academicYears',
            'semesters',
            'programs',
            'currentAcademicYear',
            'currentSemester'
        ));
    }

    /**
     * Store bulk final exam scores
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'final_exam_id' => 'required|exists:final_exams,id',
            'scores' => 'required|array',
            'scores.*.course_registration_id' => 'required|exists:course_registrations,id',
        ]);

        $branchId = session('branch_id');
        $companyId = session('company_id');

        $finalExam = FinalExam::findOrFail($request->final_exam_id);

        DB::beginTransaction();
        try {
            $savedCount = 0;
            $skippedStudents = [];
            
            foreach ($request->scores as $scoreData) {
                if (!isset($scoreData['score']) || $scoreData['score'] === '' || $scoreData['score'] === null) {
                    continue;
                }

                $registration = CourseRegistration::find($scoreData['course_registration_id']);
                if (!$registration) {
                    continue;
                }

                // Check if student has paid fees before allowing exam score entry
                $student = Student::find($registration->student_id);
                if ($student && $student->hasOutstandingFees()) {
                    $skippedStudents[] = $student->full_name;
                    continue; // Skip this student
                }

                // Calculate weighted score
                $weightedScore = ($scoreData['score'] / $finalExam->max_marks) * $finalExam->weight_percentage;

                FinalExamScore::updateOrCreate(
                    [
                        'final_exam_id' => $finalExam->id,
                        'course_registration_id' => $registration->id,
                    ],
                    [
                        'student_id' => $registration->student_id,
                        'course_id' => $finalExam->course_id,
                        'score' => $scoreData['score'],
                        'max_marks' => $finalExam->max_marks,
                        'weighted_score' => $weightedScore,
                        'remarks' => $scoreData['remarks'] ?? null,
                        'marked_by' => Auth::id(),
                        'marked_date' => now(),
                        'status' => $request->publish_all ? 'published' : 'marked',
                        'published_by' => $request->publish_all ? Auth::id() : null,
                        'published_date' => $request->publish_all ? now() : null,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]
                );
                $savedCount++;
            }

            DB::commit();
            
            $message = "{$savedCount} exam scores saved successfully!";
            if (count($skippedStudents) > 0) {
                $message .= " Skipped " . count($skippedStudents) . " student(s) with outstanding fees: " . implode(', ', $skippedStudents);
                return redirect()->route('college.final-exam-scores.index')
                    ->with('warning', $message);
            }
            
            return redirect()->route('college.final-exam-scores.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving scores: ' . $e->getMessage());
        }
    }

    /**
     * Get final exams for a course (API)
     */
    public function getFinalExams(Request $request)
    {
        $exams = FinalExam::where('course_id', $request->course_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->orderBy('exam_date', 'desc')
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'title' => $exam->title ?? 'Final Exam',
                    'exam_type' => $exam->exam_type ?? 'Final',
                    'exam_date' => $exam->exam_date ? $exam->exam_date->format('Y-m-d') : null,
                    'max_score' => $exam->max_marks,
                ];
            });

        return response()->json($exams);
    }

    /**
     * Get students for an exam with existing scores (API)
     */
    public function getStudentsForExam(Request $request)
    {
        $exam = FinalExam::findOrFail($request->final_exam_id);
        
        $registrations = CourseRegistration::with(['student', 'finalExamScores' => function ($q) use ($exam) {
            $q->where('final_exam_id', $exam->id);
        }, 'assessmentScores'])
        ->where('course_id', $exam->course_id)
        ->where('academic_year_id', $exam->academic_year_id)
        ->where('semester_id', $exam->semester_id)
        ->where('status', 'registered')
        ->get()
        ->map(function ($reg) {
            $existingScore = $reg->finalExamScores->first();
            // Calculate CA total
            $caTotal = $reg->assessmentScores->sum('weighted_score');
            
            return [
                'registration_id' => $reg->id,
                'student_id' => $reg->student->student_number,
                'name' => $reg->student->full_name,
                'ca_total' => $caTotal > 0 ? round($caTotal, 2) : null,
                'existing_score' => $existingScore ? $existingScore->score : null,
                'remarks' => $existingScore ? $existingScore->remarks : null,
            ];
        });

        return response()->json($registrations);
    }

    /**
     * Student view - My Final Exam Results
     */
    public function myResults(Request $request)
    {
        $studentId = Auth::user()->student_id ?? null;
        
        if (!$studentId) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        $query = FinalExamScore::with([
            'finalExam.course',
            'finalExam.academicYear',
            'finalExam.semester'
        ])
        ->where('student_id', $studentId)
        ->where('status', 'published');

        if ($request->filled('academic_year_id')) {
            $query->whereHas('finalExam', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            });
        }

        if ($request->filled('semester_id')) {
            $query->whereHas('finalExam', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id);
            });
        }

        $scores = $query->latest()->get();

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();

        return view('college.student-portal.exam-results', compact('scores', 'academicYears', 'semesters'));
    }
}
