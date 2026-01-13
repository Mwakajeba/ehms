<?php

namespace App\Http\Controllers;

use App\Models\SchoolExamType;
use App\Models\School\Student;
use App\Services\ParentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SchoolExamTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        return view('school.exam-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('school.exam-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:school_exam_types,name,NULL,id,company_id,' . Auth::user()->company_id . ',branch_id,' . (session('branch_id') ?: Auth::user()->branch_id ?? 'NULL'),
            'description' => 'nullable|string|max:1000',
            'weight' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        SchoolExamType::create([
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'name' => $request->name,
            'description' => $request->description,
            'weight' => $request->weight,
            'is_active' => $request->has('is_active'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('school.exam-types.index')
            ->with('success', 'Exam type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolExamType $examType): View
    {
        // Check if user has access to this exam type
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examType->company_id !== Auth::user()->company_id ||
            ($examType->branch_id && $examType->branch_id !== $userBranchId)) {
            abort(403, 'Unauthorized access to exam type.');
        }

        $examType->load(['company', 'branch', 'exams' => function ($query) {
            $query->with(['subject', 'class', 'stream'])->latest()->limit(10);
        }]);

        return view('school.exam-types.show', compact('examType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolExamType $examType): View
    {
        // Check if user has access to this exam type
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examType->company_id !== Auth::user()->company_id ||
            ($examType->branch_id && $examType->branch_id !== $userBranchId)) {
            abort(403, 'Unauthorized access to exam type.');
        }

        return view('school.exam-types.edit', compact('examType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SchoolExamType $examType): RedirectResponse
    {
        // Check if user has access to this exam type
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examType->company_id !== Auth::user()->company_id ||
            ($examType->branch_id && $examType->branch_id !== $userBranchId)) {
            abort(403, 'Unauthorized access to exam type.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:school_exam_types,name,' . $examType->id . ',id,company_id,' . Auth::user()->company_id . ',branch_id,' . (session('branch_id') ?: Auth::user()->branch_id ?? 'NULL'),
            'description' => 'nullable|string|max:1000',
            'weight' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $examType->update([
            'name' => $request->name,
            'description' => $request->description,
            'weight' => $request->weight,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('school.exam-types.index')
            ->with('success', 'Exam type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolExamType $examType): RedirectResponse
    {
        // Check if user has access to this exam type
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examType->company_id !== Auth::user()->company_id ||
            ($examType->branch_id && $examType->branch_id !== $userBranchId)) {
            abort(403, 'Unauthorized access to exam type.');
        }

        // Check if exam type has associated exams
        if ($examType->exams()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete exam type that has associated exams.');
        }

        $examType->delete();

        return redirect()->route('school.exam-types.index')
            ->with('success', 'Exam type deleted successfully.');
    }

    /**
     * Get exam types data for DataTables
     */
    public function data(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = SchoolExamType::forCompany($companyId)
            ->forBranch($branchId)
            ->with(['company', 'branch']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('exams_count', function ($examType) {
                return $examType->exams()->count();
            })
            ->addColumn('is_published', function ($examType) {
                return $examType->is_published;
            })
            ->addColumn('actions', function ($examType) {
                $actions = '';

                // View button
                $actions .= '<a href="' . route('school.exam-types.show', $examType->id) . '" class="btn btn-sm btn-info" title="View">';
                $actions .= '<i class="bx bx-show"></i>';
                $actions .= '</a> ';

                // Edit button
                $actions .= '<a href="' . route('school.exam-types.edit', $examType->id) . '" class="btn btn-sm btn-warning" title="Edit">';
                $actions .= '<i class="bx bx-edit"></i>';
                $actions .= '</a> ';

                // Toggle status button
                if ($examType->is_active) {
                    $actions .= '<button type="button" class="btn btn-sm btn-secondary status-toggle" title="Deactivate" data-id="' . $examType->id . '" data-url="' . route('school.exam-types.toggle-status', $examType->id) . '">';
                    $actions .= '<i class="bx bx-pause"></i>';
                    $actions .= '</button> ';
                } else {
                    $actions .= '<button type="button" class="btn btn-sm btn-success status-toggle" title="Activate" data-id="' . $examType->id . '" data-url="' . route('school.exam-types.toggle-status', $examType->id) . '">';
                    $actions .= '<i class="bx bx-play"></i>';
                    $actions .= '</button> ';
                }

                // Toggle publish button
                if ($examType->is_published) {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-secondary publish-toggle" title="Unpublish" data-id="' . $examType->id . '" data-url="' . route('school.exam-types.toggle-publish', $examType->id) . '">';
                    $actions .= '<i class="bx bx-globe"></i>';
                    $actions .= '</button> ';
                } else {
                    $actions .= '<button type="button" class="btn btn-sm btn-outline-primary publish-toggle" title="Publish" data-id="' . $examType->id . '" data-url="' . route('school.exam-types.toggle-publish', $examType->id) . '">';
                    $actions .= '<i class="bx bx-globe"></i>';
                    $actions .= '</button> ';
                }

                // Delete button
                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete" data-id="' . $examType->id . '" data-url="' . route('school.exam-types.destroy', $examType->id) . '">';
                $actions .= '<i class="bx bx-trash"></i>';
                $actions .= '</button>';

                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Toggle the publish status of an exam type.
     */
    public function togglePublish($id): JsonResponse
    {
        $examType = SchoolExamType::findOrFail($id);

        // Check if user has access to this exam type
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examType->company_id !== Auth::user()->company_id ||
            ($examType->branch_id && $examType->branch_id !== $userBranchId)) {
            return response()->json(['error' => 'Unauthorized access to exam type.'], 403);
        }

        $wasPublished = $examType->is_published;
        $examType->update(['is_published' => !$examType->is_published]);

        // Send notification if exam type is now published
        if ($examType->is_published && !$wasPublished) {
            try {
                $notificationService = new ParentNotificationService();
                $activeAcademicYear = \App\Models\School\AcademicYear::where('is_current', true)->first();
                
                if ($activeAcademicYear) {
                    // Get all exam class assignments for this exam type
                    $examAssignments = \App\Models\ExamClassAssignment::where('exam_type_id', $examType->id)
                        ->where('academic_year_id', $activeAcademicYear->id)
                        ->with(['classe', 'stream'])
                        ->get();
                    
                    // Get unique students who have registrations for these exams
                    $studentIds = \App\Models\SchoolExamRegistration::whereIn('exam_class_assignment_id', $examAssignments->pluck('id'))
                        ->distinct()
                        ->pluck('student_id')
                        ->toArray();
                    
                    // Get all students
                    $students = Student::whereIn('id', $studentIds)
                        ->where('status', 'active')
                        ->get();
                    
                    foreach ($students as $student) {
                        $title = 'Matokeo ya Mtihani Yamechapishwa: ' . $examType->name;
                        $message = "Matokeo ya mtihani wa {$examType->name} kwa {$student->first_name} {$student->last_name} (Mwaka wa Masomo: {$activeAcademicYear->year_name}) yamechapishwa. Tafadhali angalia kwenye programu.";
                        $notificationService->notifyStudentParents(
                            $student,
                            'exam_published',
                            $title,
                            $message,
                            [
                                'exam_type_id' => $examType->id,
                                'exam_type_name' => $examType->name,
                                'academic_year_id' => $activeAcademicYear->id,
                                'academic_year_name' => $activeAcademicYear->year_name,
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send exam notification', [
                    'exam_type_id' => $examType->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Exam type publish status updated successfully.',
            'is_published' => $examType->is_published,
        ]);
    }
}
