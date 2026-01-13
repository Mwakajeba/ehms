<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\SchoolExamRegistration;
use App\Models\SchoolExamMark;
use App\Models\ExamResult;
use App\Models\School\Classe;
use App\Models\School\SchoolStream;
use App\Models\School\AcademicYear;
use App\Models\SchoolExamType;
use App\Models\School\SchoolSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Toastr;

class MarksEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Temporarily commented out for testing
        // $this->middleware('permission:marks-entry-view')->only(['index', 'getStudents', 'enterMarks', 'getExistingMarks']);
        // $this->middleware('permission:marks-entry-create')->only(['saveMarks']);
    }

    /**
     * Display the marks entry selection interface
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get the current academic year
        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_current', true)
            ->first();

        if (!$currentAcademicYear) {
            Toastr::error('No current academic year is set. Please set a current academic year first.');
            return redirect()->back();
        }

        $examTypes = SchoolExamType::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        return view('school.marks-entry.index', compact('currentAcademicYear', 'examTypes'));
    }

    /**
     * Get classes and streams for the selected academic year
     */
    public function getStudents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required|exists:academic_years,id',
            'exam_type_id' => 'required|exists:school_exam_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYearId = $request->academic_year_id;
        $examTypeId = $request->exam_type_id;

        // Get classes that have exam class assignments for this academic year and exam type
        $classes = \App\Models\ExamClassAssignment::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('academic_year_id', $academicYearId)
            ->where('exam_type_id', $examTypeId)
            ->where('status', 'assigned')
            ->with(['classe.streams' => function($query) {
                $query->whereHas('students');
            }])
            ->get()
            ->pluck('classe')
            ->unique('id')
            ->sortBy('name');

        // Debug: Log the query results
        \Log::info('MarksEntry getStudents Debug', [
            'academic_year_id' => $academicYearId,
            'exam_type_id' => $examTypeId,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'classes_count' => $classes->count(),
            'classes' => $classes->pluck('name')->toArray()
        ]);

        return response()->json([
            'classes' => $classes->map(function($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'hash' => Hashids::encode($class->id),
                    'streams' => $class->streams->map(function($stream) {
                        return [
                            'id' => $stream->id,
                            'name' => $stream->name,
                            'hash' => Hashids::encode($stream->id),
                        ];
                    })
                ];
            })
        ]);
    }

    /**
     * Display the marks entry form for selected class/stream/exam
     */
    public function enterMarks($examTypeHash, $classHash, $academicYearHash)
    {
        try {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            $examTypeId = Hashids::decode($examTypeHash)[0] ?? null;
            $classId = Hashids::decode($classHash)[0] ?? null;
            $academicYearId = Hashids::decode($academicYearHash)[0] ?? null;

            if (!$examTypeId || !$classId || !$academicYearId) {
                Toastr::error('Invalid parameters provided');
                return redirect()->route('school.marks-entry.index');
            }

            $examType = SchoolExamType::where('id', $examTypeId)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->first();

            $schoolClass = Classe::where('id', $classId)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->first();

            $academicYear = AcademicYear::where('id', $academicYearId)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->first();

            if (!$examType || !$schoolClass || !$academicYear) {
                Toastr::error('Selected exam type, class, or academic year not found');
                return redirect()->route('school.marks-entry.index');
            }

            // Get subjects assigned to this exam type, class, and academic year
            $subjects = \App\Models\ExamClassAssignment::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('academic_year_id', $academicYearId)
                ->where('exam_type_id', $examTypeId)
                ->where('class_id', $classId)
                ->where('status', 'assigned')
                ->with('subject')
                ->get()
                ->pluck('subject')
                ->filter()
                ->unique('id')
                ->sortBy('name');

            if ($subjects->isEmpty()) {
                Toastr::warning('No subjects found for the selected exam type and class');
                return redirect()->route('school.marks-entry.index');
            }

            // Get registered students for this exam/class/academic year combination
            $registrations = SchoolExamRegistration::where('academic_year_id', $academicYearId)
                ->where('exam_type_id', $examTypeId)
                ->whereHas('student', function($query) use ($classId) {
                    $query->where('class_id', $classId);
                })
                ->where('status', 'registered') // Only registered students
                ->with(['student'])
                ->orderBy('student_id')
                ->get();

            // Get existing marks for these students and subjects
            $existingMarks = SchoolExamMark::whereIn('student_id', $registrations->pluck('student_id'))
                ->whereIn('subject_id', $subjects->pluck('id'))
                ->where('exam_type_id', $examTypeId)
                ->where('academic_year_id', $academicYearId)
                ->get()
                ->keyBy(function($mark) {
                    return $mark->student_id . '-' . $mark->subject_id;
                });

            return view('school.marks-entry.enter-marks', compact(
                'examType', 'schoolClass', 'academicYear', 'registrations',
                'subjects', 'existingMarks', 'examTypeHash', 'classHash', 'academicYearHash'
            ));

        } catch (\Exception $e) {
            Toastr::error('An error occurred while loading the marks entry form');
            return redirect()->route('school.marks-entry.index');
        }
    }

    /**
     * Save marks for students
     */
    public function saveMarks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.subject_id' => 'required|exists:school_subjects,id',
            'marks.*.mark' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            $examTypeId = $request->exam_type_id;
            $academicYearId = $request->academic_year_id;
            $marksData = $request->marks;

            foreach ($marksData as $markData) {
                $studentId = $markData['student_id'];
                $subjectId = $markData['subject_id'];
                $mark = $markData['mark'];

                // Check if mark already exists
                $existingMark = SchoolExamMark::where('student_id', $studentId)
                    ->where('subject_id', $subjectId)
                    ->where('exam_type_id', $examTypeId)
                    ->where('academic_year_id', $academicYearId)
                    ->first();

                if ($existingMark) {
                    // Update existing mark
                    if ($mark !== null && $mark !== '') {
                        $existingMark->update(['mark' => $mark]);
                    } else {
                        // Delete mark if empty
                        $existingMark->delete();
                    }
                } else {
                    // Create new mark if value provided
                    if ($mark !== null && $mark !== '') {
                        SchoolExamMark::create([
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'exam_type_id' => $examTypeId,
                            'academic_year_id' => $academicYearId,
                            'mark' => $mark,
                            'company_id' => $companyId,
                            'branch_id' => $branchId,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Marks saved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving marks'
            ], 500);
        }
    }

    /**
     * Get existing marks for AJAX requests
     */
    public function getExistingMarks($examTypeHash, $classHash, $academicYearHash)
    {
        try {
            $examTypeId = Hashids::decode($examTypeHash)[0] ?? null;
            $classId = Hashids::decode($classHash)[0] ?? null;
            $academicYearId = Hashids::decode($academicYearHash)[0] ?? null;

            if (!$examTypeId || !$classId || !$academicYearId) {
                return response()->json(['error' => 'Invalid parameters'], 400);
            }

            // Get registered students
            $registrations = SchoolExamRegistration::where('academic_year_id', $academicYearId)
                ->where('exam_type_id', $examTypeId)
                ->whereHas('student', function($query) use ($classId) {
                    $query->where('class_id', $classId);
                })
                ->where('status', 'registered')
                ->pluck('student_id');

            // Get existing marks
            $marks = SchoolExamMark::whereIn('student_id', $registrations)
                ->where('exam_type_id', $examTypeId)
                ->where('academic_year_id', $academicYearId)
                ->get()
                ->groupBy(['student_id', 'subject_id'])
                ->map(function($subjectMarks) {
                    return $subjectMarks->first()->mark;
                });

            return response()->json(['marks' => $marks]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}