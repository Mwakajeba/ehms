<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\AcademicYear;
use App\Models\SchoolGrade;
use App\Models\SchoolGradeScale;
use App\Models\SchoolExamMark;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AcademicsExaminationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $examTypes = \App\Models\SchoolExamType::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('name')
            ->get();

        // Get counts for Academics section
        $subjectsCount = \App\Models\School\Subject::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $subjectGroupsCount = \App\Models\School\SubjectGroup::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $classTeachersCount = \App\Models\School\ClassTeacher::whereHas('classe', function($query) use ($companyId, $branchId) {
                $query->where('company_id', $companyId);
                if ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    });
                }
            })
            ->count();

        $subjectTeachersCount = \App\Models\School\SubjectTeacher::whereHas('subject', function($query) use ($companyId, $branchId) {
                $query->where('company_id', $companyId);
                if ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    });
                }
            })
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $timetablesCount = \App\Models\School\Timetable::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $assignmentsCount = \App\Models\School\Assignment::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $libraryMaterialsCount = \App\Models\School\LibraryMaterial::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        // Get counts for Exams section
        $gradeScalesCount = SchoolGradeScale::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $examTypesCount = \App\Models\SchoolExamType::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();


        $examClassAssignmentsCount = \App\Models\ExamClassAssignment::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        $marksCount = SchoolExamMark::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        // Get counts for Settings section
        $academicYearsCount = AcademicYear::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->count();

        return view('school.academics-examinations.index', compact(
            'examTypes',
            'subjectsCount',
            'subjectGroupsCount',
            'classTeachersCount',
            'subjectTeachersCount',
            'timetablesCount',
            'assignmentsCount',
            'libraryMaterialsCount',
            'gradeScalesCount',
            'examTypesCount',
            'examClassAssignmentsCount',
            'marksCount',
            'academicYearsCount'
        ));
    }

    /**
     * Show marks entry page
     */
    public function marksEntry(): View
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get current academic year
        $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();

        $examTypesQuery = \App\Models\SchoolExamType::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        // Only show exam types that have been assigned to exams (have exam class assignments)
        if ($currentAcademicYear) {
            $examTypesQuery->whereHas('examClassAssignments', function ($query) use ($currentAcademicYear, $companyId, $branchId) {
                $query->where('academic_year_id', $currentAcademicYear->id)
                      ->where('company_id', $companyId)
                      ->where(function ($subQuery) use ($branchId) {
                          $subQuery->where('branch_id', $branchId)
                                   ->orWhereNull('branch_id');
                      });
            });
        }

        $examTypes = $examTypesQuery->orderBy('name')->get();

        return view('school.academics-examinations.marks-entry', compact('examTypes'));
    }

    /**
     * Display grade scales management page.
     */
    public function gradeScales(): View
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = \App\Models\School\AcademicYear::active()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->get();

        $gradeScales = SchoolGradeScale::with(['academicYear', 'grades'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('school.grade-scales.index', compact('academicYears', 'gradeScales'));
    }

    /**
     * Show the form for creating a new grade scale.
     */
    public function createGradeScale(): View
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = \App\Models\School\AcademicYear::active()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->get();

        // Get the current academic year for this company/branch
        $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();

        return view('school.grade-scales.create', compact('academicYears', 'currentAcademicYear'));
    }

    /**
     * Store a newly created grade scale.
     */
    public function storeGradeScale(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_marks' => 'required|numeric|min:1|max:100',
            'passed_average_point' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'grades' => 'required|array|min:1',
            'grades.*.grade_letter' => 'required|string|max:5',
            'grades.*.grade_name' => 'required|string|max:255',
            'grades.*.min_marks' => 'required|numeric|min:0',
            'grades.*.max_marks' => 'required|numeric|min:0',
            'grades.*.grade_point' => 'nullable|numeric|min:0|max:10',
            'grades.*.remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Additional validation: Check that grade max_marks don't exceed grade scale max_marks
        $gradeScaleMaxMarks = $request->max_marks;
        foreach ($request->grades as $index => $gradeData) {
            if ($gradeData['max_marks'] > $gradeScaleMaxMarks) {
                return redirect()->back()
                    ->withErrors(['grades.' . $index . '.max_marks' => 'Grade max marks (' . $gradeData['max_marks'] . ') cannot exceed grade scale maximum marks (' . $gradeScaleMaxMarks . ').'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request) {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Use provided academic year or default to current
            $academicYearId = $request->academic_year_id;
            if (!$academicYearId) {
                $currentYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
                    ->where('is_current', true)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->first();
                $academicYearId = $currentYear ? $currentYear->id : null;
            }

            $gradeScale = SchoolGradeScale::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'name' => $request->name,
                'academic_year_id' => $academicYearId,
                'max_marks' => $request->max_marks,
                'passed_average_point' => $request->passed_average_point,
                'description' => $request->description,
                'is_active' => true,
            ]);

            foreach ($request->grades as $index => $gradeData) {
                SchoolGrade::create([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'grade_scale_id' => $gradeScale->id,
                    'grade_letter' => $gradeData['grade_letter'],
                    'grade_name' => $gradeData['grade_name'],
                    'min_marks' => $gradeData['min_marks'],
                    'max_marks' => $gradeData['max_marks'],
                    'grade_point' => $gradeData['grade_point'] ?? null,
                    'remarks' => $gradeData['remarks'] ?? null,
                    'sort_order' => $gradeData['sort_order'] ?? $index,
                ]);
            }
        });

        return redirect()->route('school.grade-scales.index')
            ->with('success', 'Grade scale created successfully.');
    }

    /**
     * Display the specified grade scale.
     */
    public function showGradeScale(SchoolGradeScale $gradeScale): View
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Ensure the grade scale belongs to the user's company/branch
        if ($gradeScale->company_id !== $companyId ||
            ($gradeScale->branch_id && $gradeScale->branch_id !== $branchId)) {
            abort(403, 'Unauthorized access to grade scale.');
        }

        $gradeScale->load(['academicYear', 'grades']);

        return view('school.grade-scales.show', compact('gradeScale'));
    }

    /**
     * Show the form for editing the specified grade scale.
     */
    public function editGradeScale(SchoolGradeScale $gradeScale): View
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Ensure the grade scale belongs to the user's company/branch
        if ($gradeScale->company_id !== $companyId ||
            ($gradeScale->branch_id && $gradeScale->branch_id !== $branchId)) {
            abort(403, 'Unauthorized access to grade scale.');
        }

        $academicYears = \App\Models\School\AcademicYear::active()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->get();

        $gradeScale->load('grades');

        return view('school.grade-scales.edit', compact('gradeScale', 'academicYears'));
    }

    /**
     * Update the specified grade scale.
     */
    public function updateGradeScale(Request $request, SchoolGradeScale $gradeScale): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'max_marks' => 'required|numeric|min:1|max:100',
            'passed_average_point' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'grades' => 'required|array|min:1',
            'grades.*.id' => 'nullable|exists:school_grades,id',
            'grades.*.grade_letter' => 'required|string|max:5',
            'grades.*.grade_name' => 'required|string|max:255',
            'grades.*.min_marks' => 'required|numeric|min:0',
            'grades.*.max_marks' => 'required|numeric|min:0',
            'grades.*.grade_point' => 'nullable|numeric|min:0|max:10',
            'grades.*.remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Additional validation: Check that grade max_marks don't exceed grade scale max_marks
        $gradeScaleMaxMarks = $request->max_marks;
        foreach ($request->grades as $index => $gradeData) {
            if ($gradeData['max_marks'] > $gradeScaleMaxMarks) {
                return redirect()->back()
                    ->withErrors(['grades.' . $index . '.max_marks' => 'Grade max marks (' . $gradeData['max_marks'] . ') cannot exceed grade scale maximum marks (' . $gradeScaleMaxMarks . ').'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($request, $gradeScale) {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            $gradeScale->update([
                'name' => $request->name,
                'academic_year_id' => $request->academic_year_id,
                'max_marks' => $request->max_marks,
                'passed_average_point' => $request->passed_average_point,
                'description' => $request->description,
                'is_active' => $request->has('is_active'),
            ]);

            // Delete existing grades not in the request
            $existingGradeIds = collect($request->grades)->pluck('id')->filter()->toArray();
            $gradeScale->grades()->whereNotIn('id', $existingGradeIds)->delete();

            // Update or create grades
            foreach ($request->grades as $index => $gradeData) {
                SchoolGrade::updateOrCreate(
                    [
                        'id' => $gradeData['id'] ?? null,
                        'grade_scale_id' => $gradeScale->id,
                    ],
                    [
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'grade_letter' => $gradeData['grade_letter'],
                        'grade_name' => $gradeData['grade_name'],
                        'min_marks' => $gradeData['min_marks'],
                        'max_marks' => $gradeData['max_marks'],
                        'grade_point' => $gradeData['grade_point'] ?? null,
                        'remarks' => $gradeData['remarks'] ?? null,
                        'sort_order' => $gradeData['sort_order'] ?? $index,
                    ]
                );
            }
        });

        return redirect()->route('school.grade-scales.index')
            ->with('success', 'Grade scale updated successfully.');
    }

    /**
     * Remove the specified grade scale.
     */
    public function destroyGradeScale(SchoolGradeScale $gradeScale): RedirectResponse
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Ensure the grade scale belongs to the user's company/branch
        if ($gradeScale->company_id !== $companyId ||
            ($gradeScale->branch_id && $gradeScale->branch_id !== $branchId)) {
            abort(403, 'Unauthorized access to grade scale.');
        }

        $gradeScale->delete();

        return redirect()->route('school.grade-scales.index')
            ->with('success', 'Grade scale deleted successfully.');
    }

    /**
     * Toggle the active status of the specified grade scale.
     */
    public function toggleGradeScaleStatus(SchoolGradeScale $gradeScale): JsonResponse
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Ensure the grade scale belongs to the user's company/branch
        if ($gradeScale->company_id !== $companyId ||
            ($gradeScale->branch_id && $gradeScale->branch_id !== $branchId)) {
            return response()->json(['error' => 'Unauthorized access to grade scale.'], 403);
        }

        $gradeScale->update([
            'is_active' => !$gradeScale->is_active,
        ]);

        $status = $gradeScale->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Grade scale {$status} successfully.",
            'is_active' => $gradeScale->is_active,
        ]);
    }

    /**
     * Get grade scales for a specific academic year via AJAX.
     */
    public function getGradeScalesByAcademicYear(Request $request): JsonResponse
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $academicYearId = $request->academic_year_id;

        $gradeScales = SchoolGradeScale::active()
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('academic_year_id', $academicYearId)
            ->with('grades')
            ->get();

        return response()->json($gradeScales);
    }

    /**
     * Get grade for a specific mark via AJAX.
     */
    public function getGradeForMark(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'grade_scale_id' => 'required|exists:school_grade_scales,id',
            'marks' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $gradeScale = SchoolGradeScale::where('id', $request->grade_scale_id)
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();

        if (!$gradeScale) {
            return response()->json(['error' => 'Grade scale not found or access denied'], 404);
        }

        $grade = $gradeScale->getGradeForMark($request->marks);

        return response()->json([
            'grade' => $grade,
            'marks' => $request->marks,
            'max_marks' => $gradeScale->max_marks,
        ]);
    }

    /**
     * Get grade scales data for DataTables.
     */
    public function gradeScalesData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = SchoolGradeScale::with(['academicYear', 'grades'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('academic_year_name', function ($gradeScale) {
                return $gradeScale->academicYear ? $gradeScale->academicYear->year_name : 'N/A';
            })
            ->addColumn('grades_count', function ($gradeScale) {
                return $gradeScale->grades->count();
            })
            ->addColumn('actions', function ($gradeScale) {
                $actions = '';

                // View button
                $actions .= '<a href="' . route('school.grade-scales.show', $gradeScale->id) . '" class="btn btn-sm btn-info" title="View">';
                $actions .= '<i class="bx bx-show"></i>';
                $actions .= '</a> ';

                // Edit button
                $actions .= '<a href="' . route('school.grade-scales.edit', $gradeScale->id) . '" class="btn btn-sm btn-warning" title="Edit">';
                $actions .= '<i class="bx bx-edit"></i>';
                $actions .= '</a> ';

                // Deactivate/Activate button
                if ($gradeScale->is_active) {
                    $actions .= '<a href="' . route('school.grade-scales.toggle-status', $gradeScale->id) . '" class="btn btn-sm btn-secondary toggle-status" title="Deactivate" data-name="' . htmlspecialchars($gradeScale->name) . '" data-status="deactivate">';
                    $actions .= '<i class="bx bx-pause-circle"></i>';
                    $actions .= '</a> ';
                } else {
                    $actions .= '<a href="' . route('school.grade-scales.toggle-status', $gradeScale->id) . '" class="btn btn-sm btn-success toggle-status" title="Activate" data-name="' . htmlspecialchars($gradeScale->name) . '" data-status="activate">';
                    $actions .= '<i class="bx bx-play-circle"></i>';
                    $actions .= '</a> ';
                }

                // Delete button
                $actions .= '<a href="' . route('school.grade-scales.destroy', $gradeScale->id) . '" class="btn btn-sm btn-danger delete-grade-scale" title="Delete" data-name="' . htmlspecialchars($gradeScale->name) . '">';
                $actions .= '<i class="bx bx-trash"></i>';
                $actions .= '</a>';

                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Get classes and streams for the selected academic year and exam type (AJAX)
     */
    public function getStudents(Request $request): JsonResponse
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

        // Get classes that have exam registrations for this academic year and exam type
        $studentIds = \App\Models\School\SchoolExamRegistration::where('academic_year_id', $academicYearId)
            ->where('exam_type_id', $examTypeId)
            ->where('status', 'registered')
            ->pluck('student_id')
            ->unique();

        if ($studentIds->isEmpty()) {
            // Fallback: Get all classes with students
            $classes = \App\Models\School\Classe::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->whereHas('students')
                ->with(['streams'])
                ->orderBy('name')
                ->get();
        } else {
            // Get classes that have these students
            $classes = \App\Models\School\Classe::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->whereHas('students', function($query) use ($studentIds) {
                    $query->whereIn('id', $studentIds);
                })
                ->with(['streams' => function($query) use ($studentIds) {
                    $query->whereHas('students', function($subQuery) use ($studentIds) {
                        $subQuery->whereIn('id', $studentIds);
                    });
                }])
                ->orderBy('name')
                ->get();
        }

        return response()->json([
            'classes' => $classes->map(function($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'hash' => \Vinkla\Hashids\Facades\Hashids::encode($class->id),
                    'streams' => $class->streams->map(function($stream) {
                        return [
                            'id' => $stream->id,
                            'name' => $stream->name,
                            'hash' => \Vinkla\Hashids\Facades\Hashids::encode($stream->id),
                        ];
                    })
                ];
            })
        ]);
    }
    /**
     * Get classes and streams for marks entry (AJAX)
     */
    public function getClassesForMarksEntry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $examTypeId = $request->exam_type_id;

        // Get current academic year
        $currentAcademicYear = \App\Models\School\AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->first();

        if (!$currentAcademicYear) {
            return response()->json(['error' => 'No current academic year found'], 404);
        }

        // Get classes that have exam class assignments for this exam type and current academic year
        $classes = \App\Models\School\Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->whereHas('examClassAssignments', function($query) use ($examTypeId, $currentAcademicYear, $companyId, $branchId) {
                $query->where('exam_type_id', $examTypeId)
                      ->where('academic_year_id', $currentAcademicYear->id)
                      ->where('company_id', $companyId)
                      ->where(function ($subQuery) use ($branchId) {
                          $subQuery->where('branch_id', $branchId)
                                   ->orWhereNull('branch_id');
                      });
            })
            ->with(['streams' => function($query) {
                $query->whereHas('students', function($subQuery) {
                    $subQuery->where('status', 'active');
                });
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'classes' => $classes->map(function($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'streams' => $class->streams->map(function($stream) {
                        return [
                            'id' => $stream->id,
                            'name' => $stream->name,
                        ];
                    })
                ];
            }),
            'academic_year_id' => $currentAcademicYear->id
        ]);
    }

    /**
     * Get marks entry data for AJAX (students and subjects)
     */
    public function getMarksEntryData(Request $request): JsonResponse
    {
        \Log::info('getMarksEntryData called', ['request' => $request->all(), 'user' => auth()->id()]);

        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'class_id' => 'nullable|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'stream_id' => 'nullable',
        ]);

        // Validate stream_id only if provided and not empty
        $validator->sometimes('stream_id', 'exists:streams,id', function ($input) {
            return !empty($input->stream_id);
        });

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors());
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $examTypeId = $request->exam_type_id;
        $classId = $request->class_id;
        $academicYearId = $request->academic_year_id;
        $streamId = $request->stream_id;

        \Log::info('Processing marks entry data', [
            'exam_type_id' => $examTypeId,
            'class_id' => $classId,
            'academic_year_id' => $academicYearId,
            'stream_id' => $streamId,
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);

        // Get exam class assignments for this exam type and academic year
        $examClassAssignmentsQuery = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        // Filter by class if specified
        if ($classId) {
            $examClassAssignmentsQuery->where('class_id', $classId);
        }

        $examClassAssignments = $examClassAssignmentsQuery->with('subject')->get();
        \Log::info('Found ' . $examClassAssignments->count() . ' exam class assignments');

        $subjects = collect();
        $examClassAssignmentIds = collect();
        $students = collect();

        if ($examClassAssignments->isEmpty()) {
            \Log::info('No exam assignments found, returning empty data');
        } else {
            // Get subjects from exam class assignments with sort_order from subject groups
            $classIds = $classId ? collect([$classId]) : $examClassAssignments->pluck('class_id')->unique();
            
            // Get primary subject group for each class
            $primarySubjectGroups = \App\Models\School\SubjectGroup::whereIn('class_id', $classIds)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->get()
                ->keyBy('class_id');

            // Get subjects with sort_order from subject groups
            $subjectsWithSortOrder = collect();
            foreach ($examClassAssignments as $assignment) {
                $subject = $assignment->subject;
                if ($subject) {
                    $primaryGroup = $primarySubjectGroups->get($assignment->class_id);
                    $sortOrder = null;
                    
                    if ($primaryGroup) {
                        $pivotRecord = \DB::table('subject_subject_group')
                            ->where('subject_group_id', $primaryGroup->id)
                            ->where('subject_id', $subject->id)
                            ->first();
                        $sortOrder = $pivotRecord ? $pivotRecord->sort_order : null;
                    }
                    
                    $subjectsWithSortOrder->push([
                        'subject' => $subject,
                        'sort_order' => $sortOrder ?? 999 // Default high value for subjects without sort_order
                    ]);
                }
            }
            
            // Sort by sort_order, then by name, and get unique subjects
            $subjects = $subjectsWithSortOrder
                ->sortBy('sort_order')
                ->sortBy(function($item) {
                    return $item['sort_order'] === 999 ? $item['subject']->name : '';
                })
                ->pluck('subject')
                ->unique('id');
            
            \Log::info('Found ' . $subjects->count() . ' subjects');

            // Get exam class assignment IDs
            $examClassAssignmentIds = $examClassAssignments->pluck('id');

            // Get classes from assignments - if specific class selected, use only that class
            $classIds = $classId ? collect([$classId]) : $examClassAssignments->pluck('class_id')->unique();

            // Get all active students in the selected classes
            $studentsQuery = \App\Models\School\Student::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->whereIn('class_id', $classIds)
                ->where('status', 'active');

            if ($streamId) {
                $studentsQuery->where('stream_id', $streamId);
            }

            $students = $studentsQuery->with(['class', 'stream'])->orderBy('class_id')->orderBy('first_name')->get();
            \Log::info('Found ' . $students->count() . ' active students in ' . $classIds->count() . ' classes');
        }

        // Get existing marks for these students and assignments
        // Only get marks for registered students
        $existingMarks = collect();
        if ($examClassAssignmentIds->isNotEmpty() && $students->isNotEmpty()) {
            // Get all registrations to filter marks
            $allRegistrations = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $examClassAssignmentIds)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy(function($reg) {
                    return $reg->student_id . '-' . $reg->exam_class_assignment_id;
                });
            
            // Get marks but only for registered students
            $allMarks = \App\Models\SchoolExamMark::whereIn('exam_class_assignment_id', $examClassAssignmentIds)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();
            
            // Filter marks to only include those for registered students
            $existingMarks = $allMarks->filter(function($mark) use ($allRegistrations, $examClassAssignments) {
                $assignment = $examClassAssignments->find($mark->exam_class_assignment_id);
                if (!$assignment) return false;
                
                $regKey = $mark->student_id . '-' . $mark->exam_class_assignment_id;
                $registration = $allRegistrations->get($regKey);
                
                // Only include marks if student is registered
                return $registration && $registration->status === 'registered';
            })->keyBy(function($mark) use ($examClassAssignments) {
                $assignment = $examClassAssignments->find($mark->exam_class_assignment_id);
                return $mark->student_id . '-' . ($assignment ? $assignment->subject_id : '');
                });
        }

        // Get registration statuses for students and assignments
        $registrationsRaw = collect();
        if ($examClassAssignmentIds->isNotEmpty() && $students->isNotEmpty()) {
            $registrationsRaw = \App\Models\School\SchoolExamRegistration::whereIn('exam_class_assignment_id', $examClassAssignmentIds)
                ->whereIn('student_id', $students->pluck('id'))
                ->get();
        }

        \Log::info('Found ' . $existingMarks->count() . ' existing marks');
        \Log::info('Found ' . $registrationsRaw->count() . ' registrations');

        $response = [
            'students' => $students->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'admission_no' => $student->admission_number ?? $student->admission_no ?? '',
                    'class_id' => $student->class_id,
                    'class_name' => $student->class ? $student->class->name : 'N/A',
                    'stream_name' => $student->stream ? $student->stream->name : '',
                ];
            }),
            'subjects' => $subjects->map(function($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'short_name' => $subject->short_name ?? $subject->name,
                    'code' => $subject->code ?? '',
                ];
            }),
            'assignments' => $examClassAssignments->map(function($assignment) {
                return [
                    'id' => $assignment->id,
                    'subject_id' => $assignment->subject_id,
                    'class_id' => $assignment->class_id,
                ];
            }),
            'existing_marks' => $existingMarks->map(function($mark) use ($examClassAssignments) {
                $assignment = $examClassAssignments->find($mark->exam_class_assignment_id);
                return [
                    'student_id' => $mark->student_id,
                    'subject_id' => $assignment ? $assignment->subject_id : null,
                    'mark' => $mark->marks_obtained,
                ];
            })->values(),
            'registrations' => $registrationsRaw->map(function($reg) use ($examClassAssignments) {
                $assignment = $examClassAssignments->find($reg->exam_class_assignment_id);
                return [
                    'student_id' => $reg->student_id,
                    'subject_id' => $assignment ? $assignment->subject_id : null,
                    'status' => $reg->status,
                ];
            })->values()
        ];

        \Log::info('Returning response with ' . $response['students']->count() . ' students and ' . $response['subjects']->count() . ' subjects');

        return response()->json($response);
    }

    /**
     * Save marks via AJAX
     */
    public function saveMarks(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'nullable|exists:classes,id',
            'marks' => 'required|array',
            'marks.*.student_id' => 'required|exists:students,id',
            'marks.*.subject_id' => 'required|exists:subjects,id',
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
            $classId = $request->class_id;
            $marksData = $request->marks;

            // Get exam class assignments for this exam type and academic year
            $examClassAssignmentsQuery = \App\Models\ExamClassAssignment::where('exam_type_id', $examTypeId)
                ->where('academic_year_id', $academicYearId)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                });

            // Filter by class if specified
            if ($classId) {
                $examClassAssignmentsQuery->where('class_id', $classId);
            }

            $examClassAssignments = $examClassAssignmentsQuery->with('subject')->get();

            $assignmentBySubjectAndClass = $examClassAssignments->groupBy(function($assignment) {
                return $assignment->subject_id . '-' . $assignment->class_id;
            });

            foreach ($marksData as $markData) {
                $studentId = $markData['student_id'];
                $subjectId = $markData['subject_id'];
                $mark = $markData['mark'];

                // Get the student to find their class
                $student = \App\Models\School\Student::find($studentId);
                if (!$student) continue;

                // Find the assignment for this subject and student's class
                $assignmentKey = $subjectId . '-' . $student->class_id;
                $assignments = $assignmentBySubjectAndClass->get($assignmentKey, collect());

                if ($assignments->isEmpty()) {
                    continue; // Skip if no assignment found for this subject and class
                }

                $assignment = $assignments->first();

                // Check if student is registered for this exam
                $registration = \App\Models\SchoolExamRegistration::where('exam_class_assignment_id', $assignment->id)
                    ->where('student_id', $studentId)
                    ->where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->first();

                // Only save marks if student is registered
                if (!$registration || $registration->status !== 'registered') {
                    // Skip saving marks for non-registered students
                    // If there's an existing mark, delete it
                    \App\Models\SchoolExamMark::where('exam_class_assignment_id', $assignment->id)
                        ->where('student_id', $studentId)
                        ->delete();
                    continue;
                }

                // Save or update the mark
                if ($mark !== null && $mark !== '') {
                    \App\Models\SchoolExamMark::updateOrCreate(
                        [
                            'exam_class_assignment_id' => $assignment->id,
                            'student_id' => $studentId,
                        ],
                        [
                            'marks_obtained' => $mark,
                            'max_marks' => 100, // Default max marks
                            'company_id' => $companyId,
                            'branch_id' => $branchId,
                            'created_by' => Auth::id(),
                            'updated_by' => Auth::id(),
                        ]
                    );
                } else {
                    // Delete mark if empty
                    \App\Models\SchoolExamMark::where('exam_class_assignment_id', $assignment->id)
                        ->where('student_id', $studentId)
                        ->delete();
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
                'message' => 'An error occurred while saving marks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download marks sample Excel file
     */
    public function downloadMarksSample(Request $request)
    {
        try {
            $examTypeId = $request->get('exam_type_id');
            $classId = $request->get('class_id');
            $streamId = $request->get('stream_id');
            $academicYearId = $request->get('academic_year_id');
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            if (!$examTypeId) {
                return back()->with('error', 'Exam type is required');
            }

            if (!$companyId) {
                return back()->with('error', 'Company information not found. Please log in again.');
            }

            // Validate that class_id is provided and valid
            if (!$classId || $classId === 'all') {
                return back()->with('error', 'Please select a specific class to download the marks sample.');
            }

            // Generate filename
            $filename = 'marks_sample_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Use Excel export
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\MarksSampleExport($examTypeId, $classId, $streamId, $academicYearId, $companyId, $branchId),
                $filename
            );
        } catch (\Exception $e) {
            \Log::error('Marks Sample Export Error: ' . $e->getMessage(), [
                'exam_type_id' => $request->get('exam_type_id'),
                'class_id' => $request->get('class_id'),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to generate marks sample. Please try again or contact support if the problem persists.');
        }
    }

    /**
     * Import marks from Excel file
     */
    public function importMarks(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'exam_type_id' => 'required|exists:school_exam_types,id',
                'class_id' => 'required|exists:classes,id',
                'academic_year_id' => 'required|exists:academic_years,id',
                'marks_file' => 'required|file|mimes:xlsx,xls|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $examTypeId = $request->exam_type_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;
            $academicYearId = $request->academic_year_id;
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;
            $userId = Auth::id();

            // Import the Excel file
            $import = new \App\Imports\MarksImport(
                $examTypeId,
                $classId,
                $streamId,
                $academicYearId,
                $companyId,
                $branchId,
                $userId
            );

            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('marks_file'));

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

            $message = "Import completed. {$successCount} marks imported successfully.";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " errors occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            \Log::error('Marks Import Error: ' . $e->getMessage(), [
                'exam_type_id' => $request->get('exam_type_id'),
                'class_id' => $request->get('class_id'),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
