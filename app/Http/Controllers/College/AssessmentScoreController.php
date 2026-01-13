<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\College\AssessmentScore;
use App\Models\College\CourseAssessment;
use App\Models\College\CourseRegistration;
use App\Models\College\Student;
use App\Models\College\AcademicYear;
use App\Models\College\Semester;
use App\Models\College\Program;
use App\Models\College\Course;
use App\Models\College\AssessmentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssessmentScoreController extends Controller
{
    /**
     * Display a listing of assessment scores (CA Results)
     */
    public function index(Request $request)
    {
        $query = AssessmentScore::with([
            'courseAssessment.assessmentType',
            'courseAssessment.course',
            'student',
            'courseRegistration',
            'course'
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
            $query->whereHas('courseAssessment', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            });
        }

        if ($request->filled('semester_id')) {
            $query->whereHas('courseAssessment', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id);
            });
        }

        $scores = $query->latest()->paginate(20)->withQueryString();

        // Get filter options
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();
        $courses = Course::orderBy('code')->get();
        $students = Student::orderBy('first_name')->get();
        $assessmentTypes = AssessmentType::orderBy('name')->get();

        // Statistics
        $stats = [
            'total' => AssessmentScore::count(),
            'pending' => AssessmentScore::where('status', 'draft')->count(),
            'marked' => AssessmentScore::where('status', 'marked')->count(),
            'published' => AssessmentScore::where('status', 'published')->count(),
        ];

        return view('college.assessment-scores.index', compact(
            'scores',
            'academicYears',
            'semesters',
            'courses',
            'students',
            'assessmentTypes',
            'stats'
        ));
    }

    /**
     * Show form to enter assessment scores
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
        
        // Get all assessments (course assessments)
        $assessments = CourseAssessment::with('course')
            ->orderBy('title')
            ->get();
        
        // Get current academic year (use status or date-based fallback)
        $currentAcademicYear = AcademicYear::where('status', 'active')->first()
            ?? AcademicYear::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first()
            ?? AcademicYear::latest('start_date')->first();
        
        // Get current semester (first one by number order)
        $currentSemester = Semester::orderBy('number')->first();

        return view('college.assessment-scores.create', compact(
            'academicYears',
            'semesters',
            'programs',
            'courses',
            'students',
            'assessments',
            'currentAcademicYear',
            'currentSemester'
        ));
    }

    /**
     * Store assessment scores
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_assessment_id' => 'required|exists:course_assessments,id',
            'course_registration_id' => 'required|exists:course_registrations,id',
            'score' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $assessment = CourseAssessment::findOrFail($request->course_assessment_id);
        $registration = CourseRegistration::findOrFail($request->course_registration_id);

        // Validate score doesn't exceed max marks
        if ($request->score > $assessment->max_marks) {
            return back()->withErrors(['score' => 'Score cannot exceed maximum marks (' . $assessment->max_marks . ')'])->withInput();
        }

        // Check if student has paid fees before allowing assessment score entry
        $student = Student::findOrFail($registration->student_id);
        if ($student->hasOutstandingFees()) {
            $outstandingBalance = number_format($student->getOutstandingBalance(), 2);
            return back()
                ->withInput()
                ->with('error', "Cannot enter assessment scores for {$student->full_name}. This student has an outstanding fee balance of TZS {$outstandingBalance}. Please ensure the student clears their fees before taking any examination or assessment.");
        }

        DB::beginTransaction();
        try {
            // Check for existing score
            $existingScore = AssessmentScore::where('course_assessment_id', $request->course_assessment_id)
                ->where('course_registration_id', $request->course_registration_id)
                ->first();

            if ($existingScore) {
                $existingScore->update([
                    'score' => $request->score,
                    'remarks' => $request->remarks,
                    'marked_by' => Auth::id(),
                    'marked_date' => now(),
                    'status' => 'marked',
                ]);
                $score = $existingScore;
            } else {
                $score = AssessmentScore::create([
                    'course_assessment_id' => $request->course_assessment_id,
                    'course_registration_id' => $request->course_registration_id,
                    'student_id' => $registration->student_id,
                    'course_id' => $registration->course_id,
                    'score' => $request->score,
                    'max_marks' => $assessment->max_marks,
                    'weighted_score' => ($request->score / $assessment->max_marks) * $assessment->weight_percentage,
                    'status' => 'marked',
                    'remarks' => $request->remarks,
                    'marked_by' => Auth::id(),
                    'marked_date' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('college.assessment-scores.index')
                ->with('success', 'Assessment score saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to save score: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show assessment score details
     */
    public function show(AssessmentScore $assessmentScore)
    {
        $assessmentScore->load([
            'courseAssessment.assessmentType',
            'courseAssessment.course',
            'courseAssessment.academicYear',
            'courseAssessment.semester',
            'student',
            'courseRegistration',
            'markedBy',
            'publishedBy'
        ]);

        return view('college.assessment-scores.show', compact('assessmentScore'));
    }

    /**
     * Show edit form
     */
    public function edit(AssessmentScore $assessmentScore)
    {
        $assessmentScore->load([
            'courseAssessment.assessmentType',
            'courseAssessment.course',
            'student',
            'courseRegistration'
        ]);

        return view('college.assessment-scores.edit', compact('assessmentScore'));
    }

    /**
     * Update assessment score
     */
    public function update(Request $request, AssessmentScore $assessmentScore)
    {
        $request->validate([
            'score' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $assessment = $assessmentScore->courseAssessment;

        // Validate score doesn't exceed max marks
        if ($request->score > $assessment->max_marks) {
            return back()->withErrors(['score' => 'Score cannot exceed maximum marks (' . $assessment->max_marks . ')'])->withInput();
        }

        $assessmentScore->update([
            'score' => $request->score,
            'weighted_score' => ($request->score / $assessment->max_marks) * $assessment->weight_percentage,
            'remarks' => $request->remarks,
            'marked_by' => Auth::id(),
            'marked_date' => now(),
        ]);

        return redirect()->route('college.assessment-scores.index')
            ->with('success', 'Assessment score updated successfully.');
    }

    /**
     * Delete assessment score
     */
    public function destroy(AssessmentScore $assessmentScore)
    {
        if ($assessmentScore->status === 'published') {
            return back()->withErrors(['error' => 'Cannot delete published scores.']);
        }

        $assessmentScore->delete();

        return redirect()->route('college.assessment-scores.index')
            ->with('success', 'Assessment score deleted successfully.');
    }

    /**
     * Bulk entry form
     */
    public function bulkEntry(Request $request)
    {
        $branchId = session('branch_id');
        $companyId = session('company_id');

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();
        $programs = Program::where('branch_id', $branchId)->orderBy('name')->get();
        
        // Get current academic year (use status or date-based fallback)
        $currentAcademicYear = AcademicYear::where('status', 'active')->first()
            ?? AcademicYear::whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->first()
            ?? AcademicYear::latest('start_date')->first();
        
        // Get current semester (first one by number order)
        $currentSemester = Semester::orderBy('number')->first();

        return view('college.assessment-scores.bulk-entry', compact(
            'academicYears',
            'semesters',
            'programs',
            'currentAcademicYear',
            'currentSemester'
        ));
    }

    /**
     * Store bulk assessment scores
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'course_assessment_id' => 'required|exists:course_assessments,id',
            'scores' => 'required|array',
            'scores.*.registration_id' => 'required|exists:course_registrations,id',
            'scores.*.score' => 'nullable|numeric|min:0',
        ]);

        $branchId = session('branch_id');
        $companyId = session('company_id');

        $assessment = CourseAssessment::findOrFail($request->course_assessment_id);

        DB::beginTransaction();
        try {
            $savedCount = 0;
            $skippedStudents = [];
            
            foreach ($request->scores as $scoreData) {
                if (!isset($scoreData['score']) || $scoreData['score'] === null || $scoreData['score'] === '') {
                    continue;
                }

                $registration = CourseRegistration::findOrFail($scoreData['registration_id']);

                // Check if student has paid fees before allowing assessment score entry
                $student = Student::find($registration->student_id);
                if ($student && $student->hasOutstandingFees()) {
                    $skippedStudents[] = $student->full_name;
                    continue; // Skip this student
                }

                // Check for existing score
                $existingScore = AssessmentScore::where('course_assessment_id', $request->course_assessment_id)
                    ->where('course_registration_id', $scoreData['registration_id'])
                    ->first();

                if ($existingScore) {
                    $existingScore->update([
                        'score' => $scoreData['score'],
                        'weighted_score' => ($scoreData['score'] / $assessment->max_marks) * $assessment->weight_percentage,
                        'remarks' => $scoreData['remarks'] ?? null,
                        'marked_by' => Auth::id(),
                        'marked_date' => now(),
                        'status' => 'marked',
                    ]);
                } else {
                    AssessmentScore::create([
                        'course_assessment_id' => $request->course_assessment_id,
                        'course_registration_id' => $scoreData['registration_id'],
                        'student_id' => $registration->student_id,
                        'score' => $scoreData['score'],
                        'max_marks' => $assessment->max_marks,
                        'weighted_score' => ($scoreData['score'] / $assessment->max_marks) * $assessment->weight_percentage,
                        'status' => 'marked',
                        'remarks' => $scoreData['remarks'] ?? null,
                        'marked_by' => Auth::id(),
                        'marked_date' => now(),
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]);
                }
                $savedCount++;
            }

            DB::commit();
            
            $message = "Successfully saved {$savedCount} assessment scores.";
            if (count($skippedStudents) > 0) {
                $message .= " Skipped " . count($skippedStudents) . " student(s) with outstanding fees: " . implode(', ', $skippedStudents);
                return redirect()->route('college.assessment-scores.index')
                    ->with('warning', $message);
            }
            
            return redirect()->route('college.assessment-scores.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to save scores: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Publish assessment score
     */
    public function publish(AssessmentScore $assessmentScore)
    {
        if ($assessmentScore->status === 'published') {
            return back()->with('info', 'Score is already published.');
        }

        $assessmentScore->update([
            'status' => 'published',
            'marked_by' => Auth::id(),
            'marked_date' => now(),
        ]);

        return back()->with('success', 'Assessment score published successfully.');
    }

    /**
     * Bulk publish assessment scores
     */
    public function bulkPublish(Request $request)
    {
        $request->validate([
            'score_ids' => 'required|array',
            'score_ids.*' => 'exists:assessment_scores,id',
        ]);

        $count = AssessmentScore::whereIn('id', $request->score_ids)
            ->where('status', '!=', 'published')
            ->update([
                'status' => 'published',
                'marked_by' => Auth::id(),
                'marked_date' => now(),
            ]);

        return back()->with('success', "Successfully published {$count} assessment scores.");
    }

    /**
     * Get students for a course
     */
    public function getStudentsForCourse(Request $request)
    {
        $students = CourseRegistration::with('student')
            ->where('course_id', $request->course_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('semester_id', $request->semester_id)
            ->whereIn('registration_status', ['registered', 'approved', 'active'])
            ->get()
            ->filter(function ($reg) {
                return $reg->student !== null;
            })
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'student_id' => $reg->student->student_number ?? $reg->student->admission_number ?? $reg->student_id,
                    'student_name' => $reg->student->full_name ?? ($reg->student->first_name . ' ' . $reg->student->last_name),
                    'status' => $reg->registration_status,
                ];
            })
            ->values();

        return response()->json($students);
    }

    /**
     * Get courses by program (API)
     */
    public function getCoursesByProgram(Request $request)
    {
        $query = Course::query();
        
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        
        $courses = $query->orderBy('code')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'code' => $course->code ?? 'N/A',
                    'name' => $course->name ?? $course->title ?? 'Unnamed Course',
                ];
            });

        return response()->json($courses);
    }

    /**
     * Get course assessments (API)
     */
    public function getCourseAssessments(Request $request)
    {
        $query = CourseAssessment::with('assessmentType')
            ->where('course_id', $request->course_id);
        
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        
        $assessments = $query->orderBy('assessment_date')
            ->get()
            ->map(function ($assessment) {
                return [
                    'id' => $assessment->id,
                    'name' => $assessment->title ?? ($assessment->assessmentType ? $assessment->assessmentType->name : 'Assessment'),
                    'type' => $assessment->assessmentType ? $assessment->assessmentType->name : 'N/A',
                    'max_score' => $assessment->max_marks ?? 100,
                ];
            });

        return response()->json($assessments);
    }

    /**
     * Get registered students for a course (API)
     */
    public function getRegisteredStudents(Request $request)
    {
        $query = CourseRegistration::with('student')
            ->where('course_id', $request->course_id);
        
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        
        if ($request->filled('semester_id')) {
            $query->where('semester_id', $request->semester_id);
        }
        
        // Try with status filter first, if no results, get all
        $students = $query->get()
            ->filter(function ($reg) {
                return $reg->student !== null;
            })
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'student_id' => $reg->student->student_number ?? $reg->student->admission_number ?? $reg->student_id,
                    'student_name' => $reg->student->full_name ?? ($reg->student->first_name . ' ' . $reg->student->last_name),
                    'status' => $reg->status ?? 'registered',
                ];
            })
            ->values();

        return response()->json($students);
    }

    /**
     * Get students for an assessment with existing scores (API)
     */
    public function getStudentsForAssessment(Request $request)
    {
        $assessment = CourseAssessment::findOrFail($request->course_assessment_id);
        
        $registrations = CourseRegistration::with(['student', 'assessmentScores' => function ($q) use ($assessment) {
            $q->where('course_assessment_id', $assessment->id);
        }])
        ->where('course_id', $assessment->course_id)
        ->where('academic_year_id', $assessment->academic_year_id)
        ->where('semester_id', $assessment->semester_id)
        ->whereIn('registration_status', ['registered', 'approved', 'active'])
        ->get()
        ->filter(function ($reg) {
            return $reg->student !== null;
        })
        ->map(function ($reg) {
            $existingScore = $reg->assessmentScores->first();
            return [
                'registration_id' => $reg->id,
                'student_id' => $reg->student->student_number ?? $reg->student->admission_number ?? $reg->student_id,
                'name' => $reg->student->full_name ?? ($reg->student->first_name . ' ' . $reg->student->last_name),
                'existing_score' => $existingScore ? $existingScore->score : null,
                'remarks' => $existingScore ? $existingScore->remarks : null,
            ];
        })
        ->values();

        return response()->json($registrations);
    }

    /**
     * Student view - My CA Results
     */
    public function myResults(Request $request)
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return redirect()->route('college.index')
                ->with('error', 'Student profile not found.');
        }

        $query = AssessmentScore::with([
            'courseAssessment.assessmentType',
            'courseAssessment.course',
            'courseAssessment.academicYear',
            'courseAssessment.semester',
        ])
        ->where('student_id', $student->id)
        ->where('status', 'published');

        if ($request->filled('academic_year_id')) {
            $query->whereHas('courseAssessment', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            });
        }

        if ($request->filled('semester_id')) {
            $query->whereHas('courseAssessment', function ($q) use ($request) {
                $q->where('semester_id', $request->semester_id);
            });
        }

        $scores = $query->get()->groupBy(function ($score) {
            return $score->courseAssessment->course_id;
        });

        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $semesters = Semester::orderBy('number')->get();

        return view('college.student-portal.ca-results', compact(
            'student',
            'scores',
            'academicYears',
            'semesters'
        ));
    }
}
