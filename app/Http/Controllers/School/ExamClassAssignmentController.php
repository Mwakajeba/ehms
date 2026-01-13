<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ExamClassAssignment;
use App\Models\SchoolExam;
use App\Models\SchoolExamType;
use App\Models\SchoolExamRegistration;
use App\Models\School\Classe as SchoolClass;
use App\Models\Stream;
use App\Models\School\Subject;
use App\Models\School\AcademicYear;
use App\Models\School\Curriculum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ExamClassAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $exams = SchoolExam::active()
            ->with('examType')
            ->orderBy('exam_name')
            ->get();

        $examTypes = SchoolExamType::active()
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::active()
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::active()
            ->orderBy('year_name')
            ->get();

        return view('school.exam-class-assignments.index', compact('exams', 'examTypes', 'classes', 'academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $examTypes = SchoolExamType::active()
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::active()
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::active()
            ->orderBy('year_name')
            ->get();

        return view('school.exam-class-assignments.create', compact('examTypes', 'classes', 'academicYears'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'classes' => 'required|array|min:1',
            'classes.*' => 'required|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Get current academic year
        $currentAcademicYear = AcademicYear::current();
        if (!$currentAcademicYear) {
            return redirect()->back()
                ->withErrors(['academic_year' => 'No current academic year found. Please set a current academic year first.'])
                ->withInput();
        }

        $examTypeId = $request->exam_type_id;
        $classIds = array_unique($request->classes); // Remove duplicates
        $companyId = session('company_id') ?: Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $createdAssignments = [];
        $errors = [];

        \DB::beginTransaction();

        try {
            foreach ($classIds as $classId) {
                // Get subjects from the primary subject group for this class
                $primarySubjectGroup = \App\Models\School\SubjectGroup::where('class_id', $classId)
                    ->where('company_id', $companyId)
                    ->where(function ($query) use ($branchId) {
                        $query->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                    })
                    ->where('is_active', true)
                    ->orderBy('id', 'asc')
                    ->first();

                if (!$primarySubjectGroup) {
                    $class = \App\Models\School\Classe::find($classId);
                    $errors[] = "No subject group found for class: " . ($class->name ?? 'Unknown') . ". Assignments cannot be created without a subject group.";
                    continue;
                }

                $subjectsFromGroup = $primarySubjectGroup->subjects()
                    ->where('is_active', true)
                    ->get();

                if ($subjectsFromGroup->isEmpty()) {
                    $class = \App\Models\School\Classe::find($classId);
                    $errors[] = "No subjects found in subject group for class: " . ($class->name ?? 'Unknown') . ". Assignments cannot be created without subjects.";
                    continue;
                }

                foreach ($subjectsFromGroup as $subject) {
                    // Check for duplicate assignment
                    $existingAssignment = ExamClassAssignment::where('exam_type_id', $examTypeId)
                        ->where('class_id', $classId)
                        ->where('subject_id', $subject->id)
                        ->where('academic_year_id', $currentAcademicYear->id)
                        ->first();

                    if ($existingAssignment) {
                        continue; // Skip duplicates
                    }

                    $assignment = ExamClassAssignment::create([
                        'exam_type_id' => $examTypeId,
                        'class_id' => $classId,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $currentAcademicYear->id,
                        'status' => 'assigned',
                        'assigned_date' => now(),
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'created_by' => Auth::id(),
                    ]);

                    $createdAssignments[] = $assignment;
                }
            }

            \DB::commit();

            // Calculate statistics
            $totalAssignments = count($createdAssignments);
            $totalClasses = count(array_unique(array_column($createdAssignments, 'class_id')));
            $totalSubjects = count(array_unique(array_column($createdAssignments, 'subject_id')));

            $examType = SchoolExamType::find($examTypeId);
            return redirect()->route('school.exam-class-assignments.index')
                ->with('success', "Exam class assignments created successfully! Exam Type: {$examType->name}, Total: {$totalAssignments} assignments for {$totalClasses} classes and {$totalSubjects} subjects.");

        } catch (\Exception $e) {
            \DB::rollback();

            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while creating assignments: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ExamClassAssignment $assignment)
    {
        $this->authorize('view', $assignment);

        $assignment->load([
            'examType',
            'classe',
            'stream',
            'subject',
            'academicYear',
            'creator'
        ]);

        return view('school.exam-class-assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExamClassAssignment $assignment)
    {
        $this->authorize('update', $assignment);

        $examTypes = SchoolExamType::active()
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::active()
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::active()
            ->orderBy('year_name')
            ->get();

        return view('school.exam-class-assignments.edit', compact('assignment', 'examTypes', 'classes', 'academicYears'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamClassAssignment $assignment)
    {
        $this->authorize('update', $assignment);

        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'due_date' => 'nullable|date',
            'status' => 'required|in:assigned,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for duplicate assignment (excluding current assignment)
        $existingAssignment = ExamClassAssignment::where('exam_type_id', $request->exam_type_id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('id', '!=', $assignment->id)
            ->when($request->stream_id, function($query) use ($request) {
                return $query->where('stream_id', $request->stream_id);
            }, function($query) {
                return $query->whereNull('stream_id');
            })
            ->first();

        if ($existingAssignment) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['duplicate' => 'An assignment for this exam, class, subject, and academic year combination already exists.']);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $assignment->update([
            'exam_type_id' => $request->exam_type_id,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id,
            'subject_id' => $request->subject_id,
            'academic_year_id' => $request->academic_year_id,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('school.exam-class-assignments.index')
            ->with('success', 'Exam class assignment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamClassAssignment $assignment)
    {
        $this->authorize('delete', $assignment);

        $assignment->delete();

        return redirect()->route('school.exam-class-assignments.index')
            ->with('success', 'Exam class assignment deleted successfully.');
    }

    public function data(Request $request): JsonResponse
    {
        // Get current user info for use in closures
        $user = auth()->user();
        $companyId = session('company_id') ?: $user->company_id;
        $branchId = session('branch_id') ?: $user->branch_id;

        // Get aggregated data grouped by exam_type, class, and academic_year
        $aggregatedData = ExamClassAssignment::select([
            'exam_type_id',
            'class_id',
            'academic_year_id',
            \DB::raw('MIN(id) as id'), // For ordering
            \DB::raw('COUNT(*) as total_assignments')
        ])
        ->with([
            'examType:id,name',
            'classe:id,name',
            'academicYear:id,year_name'
        ])
        ->where('company_id', $companyId)
        ->where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
        })
        ->groupBy('exam_type_id', 'class_id', 'academic_year_id')
        ->orderBy('id', 'desc')
        ->get();

        // Add additional data for each group
        $data = $aggregatedData->map(function ($group) use ($companyId, $branchId) {
            // Get total students registered in this class
            $totalStudents = \App\Models\School\Student::where('class_id', $group->class_id)
                ->where('status', 'active')
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->count();

            // Get total subjects for this class (count of subjects in the primary subject group for this class)
            $primarySubjectGroup = \App\Models\School\SubjectGroup::where('class_id', $group->class_id)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->first();

            $totalSubjects = $primarySubjectGroup ? $primarySubjectGroup->subjects()->count() : 0;

            // Get total streams in this class
            $totalStreams = \App\Models\School\Stream::whereHas('classes', function($query) use ($group) {
                $query->where('classes.id', $group->class_id);
            })
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->count();

            return [
                'id' => $group->id,
                'exam_type_name' => $group->examType->name ?? 'N/A',
                'class_name' => $group->classe->name ?? 'N/A',
                'total_students' => $totalStudents,
                'total_streams' => $totalStreams,
                'total_subjects' => $totalSubjects,
                'academic_year_name' => $group->academicYear->year_name ?? 'N/A',
                'exam_type_id' => $group->exam_type_id,
                'class_id' => $group->class_id,
                'academic_year_id' => $group->academic_year_id,
                'actions' => $this->getGroupActions($group)
            ];
        });

        return DataTables::of($data)
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Get actions for a group of assignments.
     */
    private function getGroupActions($group): string
    {
        $actions = '';

        // View assignments in this group
        $actions .= '<a href="' . route('school.exam-class-assignments.show-group', [
            'exam_type_hash' => \Vinkla\Hashids\Facades\Hashids::encode($group->exam_type_id),
            'class_hash' => \Vinkla\Hashids\Facades\Hashids::encode($group->class_id),
            'academic_year_hash' => \Vinkla\Hashids\Facades\Hashids::encode($group->academic_year_id)
        ]) . '" class="btn btn-sm btn-outline-info me-1" title="View Assignments">';
        $actions .= '<i class="bx bx-show"></i>';
        $actions .= '</a>';

        // Get counts for confirmation dialog
        $assignmentCount = ExamClassAssignment::where('exam_type_id', $group->exam_type_id)
            ->where('class_id', $group->class_id)
            ->where('academic_year_id', $group->academic_year_id)
            ->where('company_id', auth()->user()->company_id)
            ->count();

        $registrationCount = \DB::table('school_exam_registrations')
            ->join('exam_class_assignments', 'school_exam_registrations.exam_class_assignment_id', '=', 'exam_class_assignments.id')
            ->where('exam_class_assignments.exam_type_id', $group->exam_type_id)
            ->where('exam_class_assignments.class_id', $group->class_id)
            ->where('exam_class_assignments.academic_year_id', $group->academic_year_id)
            ->where('exam_class_assignments.company_id', auth()->user()->company_id)
            ->count();

        // Delete entire group with enhanced confirmation
        $actions .= '<button type="button" class="btn btn-sm btn-outline-danger me-1 delete-group-btn position-relative" 
            data-url="' . route('school.exam-class-assignments.destroy-group', [
                'exam_type_hash' => \Vinkla\Hashids\Facades\Hashids::encode($group->exam_type_id),
                'class_hash' => \Vinkla\Hashids\Facades\Hashids::encode($group->class_id),
                'academic_year_hash' => \Vinkla\Hashids\Facades\Hashids::encode($group->academic_year_id)
            ]) . '"
            data-assignments="' . $assignmentCount . '"
            data-registrations="' . $registrationCount . '"
            data-group-info="' . htmlspecialchars($group->examType->name ?? 'N/A') . ' - ' . htmlspecialchars($group->classe->name ?? 'N/A') . ' (' . htmlspecialchars($group->academicYear->year_name ?? 'N/A') . ')" 
            title="🚨 DANGER: Delete ALL assignments and student registrations for this exam/class/year combination. This action CANNOT be undone!"
            data-bs-toggle="tooltip"
            data-bs-placement="top">
            <i class="bx bx-trash"></i> <span class="d-none d-md-inline">DELETE ALL</span>
            <span class="badge bg-danger position-absolute top-0 start-100 translate-middle" style="font-size: 0.6rem;">!</span>
        </button>';

        return $actions;
    }

    /**
     * Update assignment status.
     */
    public function updateStatus(Request $request, ExamClassAssignment $assignment, string $status): JsonResponse
    {
        $this->authorize('update', $assignment);

        $validStatuses = ['assigned', 'in_progress', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        $assignment->update(['status' => $status]);

        return response()->json([
            'message' => 'Assignment status updated successfully.',
            'status' => $status
        ]);
    }

    public function getStreams(int $classId): JsonResponse
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        
        $streams = Stream::whereHas('classes', function($query) use ($classId) {
            $query->where('classes.id', $classId);
        })
        ->active()
        ->where('company_id', auth()->user()->company_id)
        ->where(function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                  ->orWhereNull('branch_id');
        })
        ->orderBy('name')
        ->get(['id', 'name']);

        return response()->json($streams);
    }

    /**
     * Get subjects for a specific class.
     */
    public function getSubjects(int $classId): JsonResponse
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        
        // Get subjects from the primary subject group for this class
        $primarySubjectGroup = \App\Models\School\SubjectGroup::where('class_id', $classId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->first();

        if ($primarySubjectGroup) {
            $subjectsFromGroup = $primarySubjectGroup->subjects()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name']);
        } else {
            $subjectsFromGroup = collect(); // Empty collection
        }

        return response()->json($subjectsFromGroup);
    }

    /**
     * Show assignments for a specific group.
     */
    public function showGroup($examTypeHash, $classHash, $academicYearHash)
    {
        $examTypeId = \Vinkla\Hashids\Facades\Hashids::decode($examTypeHash)[0] ?? null;
        $classId = \Vinkla\Hashids\Facades\Hashids::decode($classHash)[0] ?? null;
        $academicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearHash)[0] ?? null;

        if (!$examTypeId || !$classId || !$academicYearId) {
            abort(404, 'Invalid group parameters');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get the primary subject group for this class to order subjects correctly
        $primarySubjectGroup = \App\Models\School\SubjectGroup::where('class_id', $classId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->first();

        $assignments = ExamClassAssignment::with([
            'examType',
            'classe',
            'subject',
            'academicYear',
            'stream',
            'creator'
        ]);

        if ($primarySubjectGroup) {
            $assignments = $assignments->leftJoin('subject_subject_group', function($join) use ($primarySubjectGroup) {
                $join->on('exam_class_assignments.subject_id', '=', 'subject_subject_group.subject_id')
                     ->where('subject_subject_group.subject_group_id', '=', $primarySubjectGroup->id);
            })
            ->orderBy('subject_subject_group.sort_order', 'asc');
        }

        $assignments = $assignments->where('exam_type_id', $examTypeId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('exam_class_assignments.id', 'asc')
            ->select('exam_class_assignments.*')
            ->get();

        // Get students for this class
        $students = \App\Models\School\Student::with(['stream', 'academicYear'])
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('admission_number')
            ->get();

        $examType = \App\Models\SchoolExamType::find($examTypeId);
        $class = \App\Models\School\Classe::find($classId);
        $academicYear = \App\Models\School\AcademicYear::find($academicYearId);

        return view('school.exam-class-assignments.show-group', compact('assignments', 'students', 'examType', 'class', 'academicYear'));
    }

    /**
     * Show form to edit a group of assignments.
     */
    public function editGroup($examTypeHash, $classHash, $academicYearHash)
    {
        $examTypeId = \Vinkla\Hashids\Facades\Hashids::decode($examTypeHash)[0] ?? null;
        $classId = \Vinkla\Hashids\Facades\Hashids::decode($classHash)[0] ?? null;
        $academicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearHash)[0] ?? null;

        if (!$examTypeId || !$classId || !$academicYearId) {
            abort(404, 'Invalid group parameters');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        
        $assignments = ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->get();

        $examType = \App\Models\SchoolExamType::find($examTypeId);
        $class = \App\Models\School\Classe::find($classId);
        $academicYear = \App\Models\School\AcademicYear::find($academicYearId);

        return view('school.exam-class-assignments.edit-group', compact('assignments', 'examType', 'class', 'academicYear'));
    }

    /**
     * Delete all assignments in a group.
     */
    public function destroyGroup($examTypeHash, $classHash, $academicYearHash)
    {
        $examTypeId = \Vinkla\Hashids\Facades\Hashids::decode($examTypeHash)[0] ?? null;
        $classId = \Vinkla\Hashids\Facades\Hashids::decode($classHash)[0] ?? null;
        $academicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearHash)[0] ?? null;

        if (!$examTypeId || !$classId || !$academicYearId) {
            $errorMessage = 'Invalid group parameters';
            if (request()->expectsJson()) {
                return response()->json(['error' => $errorMessage], 400);
            }
            return redirect()->back()->withErrors(['error' => $errorMessage]);
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $assignments = ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->get();

        if ($assignments->isEmpty()) {
            $errorMessage = 'No assignments found for the specified group';
            if (request()->expectsJson()) {
                return response()->json(['error' => $errorMessage], 404);
            }
            return redirect()->back()->withErrors(['error' => $errorMessage]);
        }

        // Count registrations before deletion
        $registrationCount = \DB::table('school_exam_registrations')
            ->join('exam_class_assignments', 'school_exam_registrations.exam_class_assignment_id', '=', 'exam_class_assignments.id')
            ->where('exam_class_assignments.exam_type_id', $examTypeId)
            ->where('exam_class_assignments.class_id', $classId)
            ->where('exam_class_assignments.academic_year_id', $academicYearId)
            ->where('exam_class_assignments.company_id', auth()->user()->company_id)
            ->count();

        foreach ($assignments as $assignment) {
            $this->authorize('delete', $assignment);
        }

        \DB::beginTransaction();

        try {
            // Delete the assignments (this will cascade delete registrations if FK is set)
            $deletedAssignments = ExamClassAssignment::where('exam_type_id', $examTypeId)
                ->where('class_id', $classId)
                ->where('academic_year_id', $academicYearId)
                ->where('company_id', auth()->user()->company_id)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->delete();

            \DB::commit();

            $examType = \App\Models\SchoolExamType::find($examTypeId);
            $class = \App\Models\School\Classe::find($classId);
            $academicYear = \App\Models\School\AcademicYear::find($academicYearId);

            $groupName = ($examType->name ?? 'Unknown Exam') . ' - ' . ($class->name ?? 'Unknown Class') . ' (' . ($academicYear->year_name ?? 'Unknown Year') . ')';
            $successMessage = "CRITICAL DELETION COMPLETED: Deleted {$deletedAssignments} exam assignments and {$registrationCount} student registrations for group: {$groupName}";

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => $successMessage,
                    'deleted_assignments' => $deletedAssignments,
                    'deleted_registrations' => $registrationCount,
                    'group_name' => $groupName
                ]);
            }

            return redirect()->route('school.exam-class-assignments.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            \DB::rollback();

            $errorMessage = 'Failed to delete group: ' . $e->getMessage();
            if (request()->expectsJson()) {
                return response()->json(['error' => $errorMessage], 500);
            }

            return redirect()->back()->withErrors(['error' => $errorMessage]);
        }
    }    /**
     * Update all assignments in a group.
     */
    public function updateGroup(Request $request, $examTypeHash, $classHash, $academicYearHash)
    {
        $examTypeId = \Vinkla\Hashids\Facades\Hashids::decode($examTypeHash)[0] ?? null;
        $classId = \Vinkla\Hashids\Facades\Hashids::decode($classHash)[0] ?? null;
        $academicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearHash)[0] ?? null;

        if (!$examTypeId || !$classId || !$academicYearId) {
            abort(404, 'Invalid group parameters');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        
        $request->validate([
            'due_date' => 'nullable|date',
            'status' => 'required|in:assigned,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignments = ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->get();

        foreach ($assignments as $assignment) {
            $this->authorize('update', $assignment);
        }

        ExamClassAssignment::where('exam_type_id', $examTypeId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->update([
                'due_date' => $request->due_date,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

        return redirect()->route('school.exam-class-assignments.index')
            ->with('success', 'Exam class assignment group updated successfully.');
    }

    /**
     * Show form to manage student registration for a specific assignment.
     */
    public function manageStudentRegistration($examTypeHash, $classHash, $academicYearHash, $studentId = null)
    {
        $examTypeId = \Vinkla\Hashids\Facades\Hashids::decode($examTypeHash)[0] ?? null;
        $classId = \Vinkla\Hashids\Facades\Hashids::decode($classHash)[0] ?? null;
        $academicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearHash)[0] ?? null;

        if (!$examTypeId || !$classId || !$academicYearId) {
            abort(404, 'Invalid group parameters');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get the primary subject group for this class to order subjects correctly
        $primarySubjectGroup = \App\Models\School\SubjectGroup::where('class_id', $classId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->first();

        $assignments = ExamClassAssignment::with(['subject']);

        if ($primarySubjectGroup) {
            $assignments = $assignments->leftJoin('subject_subject_group', function($join) use ($primarySubjectGroup) {
                $join->on('exam_class_assignments.subject_id', '=', 'subject_subject_group.subject_id')
                     ->where('subject_subject_group.subject_group_id', '=', $primarySubjectGroup->id);
            })
            ->orderBy('subject_subject_group.sort_order', 'asc');
        }

        $assignments = $assignments->where('exam_type_id', $examTypeId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('exam_class_assignments.id', 'asc')
            ->select('exam_class_assignments.*')
            ->get();

        // If student_id is provided in route, use it; otherwise check query parameter
        $studentId = $studentId ?: request('student_id');
        $student = \App\Models\School\Student::find($studentId);

        if (!$student) {
            abort(404, 'Student not found');
        }

        // Get existing registrations for this student
        $existingRegistrations = SchoolExamRegistration::where('student_id', $studentId)
            ->whereIn('exam_class_assignment_id', $assignments->pluck('id'))
            ->pluck('status', 'exam_class_assignment_id');

        // Get existing reasons for this student
        $existingReasons = SchoolExamRegistration::where('student_id', $studentId)
            ->whereIn('exam_class_assignment_id', $assignments->pluck('id'))
            ->pluck('reason', 'exam_class_assignment_id');

        return view('school.exam-class-assignments.manage-registration', compact('assignments', 'student', 'existingRegistrations', 'existingReasons'));
    }

    /**
     * Save student registration for assignments.
     */
    public function saveStudentRegistration(Request $request, $examTypeHash, $classHash, $academicYearHash)
    {
        $examTypeId = \Vinkla\Hashids\Facades\Hashids::decode($examTypeHash)[0] ?? null;
        $classId = \Vinkla\Hashids\Facades\Hashids::decode($classHash)[0] ?? null;
        $academicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearHash)[0] ?? null;

        if (!$examTypeId || !$classId || !$academicYearId) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid group parameters'], 400);
            }
            return redirect()->back()->withErrors(['error' => 'Invalid group parameters']);
        }

        $request->validate([
            'student_id' => 'required|exists:students,id',
            'registrations' => 'required|array',
            'registrations.*.assignment_id' => 'required|exists:exam_class_assignments,id',
            'registrations.*.status' => 'required|in:registered,exempted,absent,attended',
            'registrations.*.reason' => 'nullable|string|max:500'
        ]);

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $companyId = auth()->user()->company_id;

        \DB::beginTransaction();

        try {
            foreach ($request->registrations as $registrationData) {
                // Get the exam class assignment to get academic_year_id and exam_type_id
                $assignment = ExamClassAssignment::find($registrationData['assignment_id']);
                if (!$assignment) {
                    continue;
                }

                SchoolExamRegistration::updateOrCreate(
                    [
                        'exam_class_assignment_id' => $registrationData['assignment_id'],
                        'student_id' => $request->student_id
                    ],
                    [
                        'academic_year_id' => $assignment->academic_year_id,
                        'exam_type_id' => $assignment->exam_type_id,
                        'status' => $registrationData['status'],
                        'reason' => $registrationData['reason'] ?? null,
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                        'created_by' => Auth::id()
                    ]
                );
            }

            \DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Student registration updated successfully',
                    'success' => true
                ]);
            }

            return redirect()->route('school.exam-class-assignments.show-group', [
                'exam_type_hash' => $examTypeHash,
                'class_hash' => $classHash,
                'academic_year_hash' => $academicYearHash
            ])->with('success', 'Student registration updated successfully');

        } catch (\Exception $e) {
            \DB::rollback();

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Failed to update registration: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => 'Failed to update registration: ' . $e->getMessage()]);
        }
    }

    /**
     * Show form to bulk manage student registrations for all students in a group.
     */
    public function bulkManageRegistration($examTypeHash, $classHash, $academicYearHash)
    {
        $examTypeId = \Vinkla\Hashids\Facades\Hashids::decode($examTypeHash)[0] ?? null;
        $classId = \Vinkla\Hashids\Facades\Hashids::decode($classHash)[0] ?? null;
        $academicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearHash)[0] ?? null;

        if (!$examTypeId || !$classId || !$academicYearId) {
            abort(404, 'Invalid group parameters');
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get the primary subject group for this class to order subjects correctly
        $primarySubjectGroup = \App\Models\School\SubjectGroup::where('class_id', $classId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('id', 'asc')
            ->first();

        $assignments = ExamClassAssignment::with(['subject']);

        if ($primarySubjectGroup) {
            $assignments = $assignments->leftJoin('subject_subject_group', function($join) use ($primarySubjectGroup) {
                $join->on('exam_class_assignments.subject_id', '=', 'subject_subject_group.subject_id')
                     ->where('subject_subject_group.subject_group_id', '=', $primarySubjectGroup->id);
            })
            ->orderBy('subject_subject_group.sort_order', 'asc');
        }

        $assignments = $assignments->where('exam_type_id', $examTypeId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('exam_class_assignments.id', 'asc')
            ->select('exam_class_assignments.*')
            ->get();

        // Get students for this class
        $students = \App\Models\School\Student::with(['stream', 'academicYear'])
            ->where('class_id', $classId)
            ->where('status', 'active')
            ->where('company_id', auth()->user()->company_id)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('admission_number')
            ->get();

        $examType = \App\Models\SchoolExamType::find($examTypeId);
        $class = \App\Models\School\Classe::find($classId);
        $academicYear = \App\Models\School\AcademicYear::find($academicYearId);

        // Get existing registrations for all students and assignments
        $existingRegistrations = SchoolExamRegistration::whereIn('exam_class_assignment_id', $assignments->pluck('id'))
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy(function ($item) {
                return $item->student_id . '_' . $item->exam_class_assignment_id;
            });

        return view('school.exam-class-assignments.bulk-manage-registration', compact('assignments', 'students', 'examType', 'class', 'academicYear', 'existingRegistrations'));
    }

    /**
     * Save bulk student registrations for all students in a group.
     */
    public function bulkSaveRegistration(Request $request, $examTypeHash, $classHash, $academicYearHash)
    {
        $examTypeId = \Vinkla\Hashids\Facades\Hashids::decode($examTypeHash)[0] ?? null;
        $classId = \Vinkla\Hashids\Facades\Hashids::decode($classHash)[0] ?? null;
        $academicYearId = \Vinkla\Hashids\Facades\Hashids::decode($academicYearHash)[0] ?? null;

        if (!$examTypeId || !$classId || !$academicYearId) {
            \Log::error('Bulk registration: Invalid group parameters', [
                'exam_type_hash' => $examTypeHash,
                'class_hash' => $classHash,
                'academic_year_hash' => $academicYearHash,
                'decoded' => [
                    'exam_type_id' => $examTypeId,
                    'class_id' => $classId,
                    'academic_year_id' => $academicYearId
                ]
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Invalid group parameters',
                    'success' => false
                ], 400);
        }
            return redirect()->back()->withErrors(['error' => 'Invalid group parameters']);
        }

        // Handle JSON input (bypasses max_input_vars limit)
        // Laravel should auto-parse JSON, but we ensure it's handled correctly
        if ($request->header('Content-Type') === 'application/json' || $request->isJson()) {
            try {
                $jsonContent = $request->getContent();
                if (!empty($jsonContent)) {
                    $jsonData = json_decode($jsonContent, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                        $request->merge($jsonData);
                    } else {
                        \Log::warning('Failed to parse JSON in bulk registration', [
                            'json_error' => json_last_error_msg(),
                            'content_length' => strlen($jsonContent)
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error parsing JSON in bulk registration', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Validate the request
        $validator = Validator::make($request->all(), [
            'registrations' => 'required|array|min:1',
            'registrations.*.student_id' => 'required|exists:students,id',
            'registrations.*.assignment_id' => 'required|exists:exam_class_assignments,id',
            'registrations.*.status' => 'required|in:registered,exempted,absent,attended',
            'registrations.*.reason' => 'nullable|string|max:500'
        ]);
        
        if ($validator->fails()) {
            \Log::error('Bulk registration validation failed', [
                'errors' => $validator->errors(),
                'registrations_count' => count($request->registrations ?? []),
                'sample_data' => array_slice($request->registrations ?? [], 0, 3) // Log first 3 for debugging
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'success' => false
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $branchId = session('branch_id') ?: Auth::user()->branch_id;
        $companyId = auth()->user()->company_id;

        // Log incoming request data for debugging
        \Log::info('Bulk registration request received', [
            'has_registrations' => $request->has('registrations'),
            'registrations_type' => gettype($request->registrations),
            'registrations_count' => is_array($request->registrations) ? count($request->registrations) : 0,
            'request_method' => $request->method(),
            'is_ajax' => $request->ajax(),
            'expects_json' => $request->expectsJson(),
            'content_type' => $request->header('Content-Type')
        ]);

        \DB::beginTransaction();

        try {
        $savedCount = 0;
        $skippedCount = 0;
            $registrations = $request->registrations ?? [];
            
            if (!is_array($registrations)) {
                \Log::error('Registrations is not an array', [
                    'type' => gettype($registrations),
                    'value' => $registrations
                ]);
                throw new \Exception('Invalid registrations data format');
            }
            
            \Log::info('Bulk registration save started', [
                'total_registrations' => count($registrations),
                'exam_type_id' => $examTypeId,
                'class_id' => $classId,
                'academic_year_id' => $academicYearId
            ]);

            foreach ($registrations as $index => $registrationData) {
                // Validate required fields are present
                    if (empty($registrationData['student_id']) || empty($registrationData['assignment_id']) || empty($registrationData['status'])) {
                    \Log::warning('Skipping registration with missing data', [
                        'index' => $index,
                        'data' => $registrationData
                    ]);
                        $skippedCount++;
                        continue;
                    }
                    
                    // Get the exam class assignment to get academic_year_id and exam_type_id
                    $assignment = ExamClassAssignment::find($registrationData['assignment_id']);
                    if (!$assignment) {
                    \Log::warning('Assignment not found', [
                        'assignment_id' => $registrationData['assignment_id']
                    ]);
                        $skippedCount++;
                        continue;
                    }
                    
                // Verify assignment belongs to correct exam type, class, and academic year
                if ($assignment->exam_type_id != $examTypeId || 
                    $assignment->class_id != $classId || 
                    $assignment->academic_year_id != $academicYearId) {
                    \Log::warning('Assignment mismatch', [
                        'assignment_id' => $assignment->id,
                        'expected' => ['exam_type_id' => $examTypeId, 'class_id' => $classId, 'academic_year_id' => $academicYearId],
                        'actual' => ['exam_type_id' => $assignment->exam_type_id, 'class_id' => $assignment->class_id, 'academic_year_id' => $assignment->academic_year_id]
                    ]);
                        $skippedCount++;
                        continue;
                    }

                    SchoolExamRegistration::updateOrCreate(
                        [
                            'exam_class_assignment_id' => $registrationData['assignment_id'],
                            'student_id' => $registrationData['student_id']
                        ],
                        [
                            'academic_year_id' => $assignment->academic_year_id,
                            'exam_type_id' => $assignment->exam_type_id,
                            'status' => $registrationData['status'],
                        'reason' => !empty($registrationData['reason']) ? trim($registrationData['reason']) : null,
                            'company_id' => $companyId,
                            'branch_id' => $branchId,
                            'created_by' => Auth::id()
                        ]
                    );
                
                    $savedCount++;
            }

            \DB::commit();

            \Log::info('Bulk registration save completed', [
                'saved_count' => $savedCount,
                'skipped_count' => $skippedCount,
                'total_processed' => count($registrations)
            ]);

            $message = "Bulk registration updated successfully. {$savedCount} registration(s) saved.";
                    if ($skippedCount > 0) {
                $message .= " {$skippedCount} registration(s) skipped due to invalid data.";
                    }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $message,
                    'success' => true,
                    'saved_count' => $savedCount,
                    'skipped_count' => $skippedCount,
                    'redirect_url' => route('school.exam-class-assignments.show-group', [
                        'exam_type_hash' => $examTypeHash,
                        'class_hash' => $classHash,
                        'academic_year_hash' => $academicYearHash
                    ])
                ]);
            }

            return redirect()->route('school.exam-class-assignments.show-group', [
                'exam_type_hash' => $examTypeHash,
                'class_hash' => $classHash,
                'academic_year_hash' => $academicYearHash
            ])->with('success', $message);

        } catch (\Exception $e) {
            \DB::rollback();
            
            \Log::error('Bulk registration save failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'registrations_count' => count($request->registrations ?? [])
            ]);

            $errorMessage = 'Failed to update bulk registration: ' . $e->getMessage();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'error' => $errorMessage,
                    'success' => false
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => $errorMessage]);
        }
    }

    /**
     * Check for duplicate assignments before creating new ones.
     */
    public function checkDuplicates(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'classes' => 'required|array|min:1',
            'classes.*' => 'required|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get current academic year
        $currentAcademicYear = AcademicYear::current();
        if (!$currentAcademicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No current academic year found. Please set a current academic year first.'
            ], 400);
        }

        $examTypeId = $request->exam_type_id;
        $classIds = array_unique($request->classes);
        $companyId = session('company_id') ?: Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $duplicates = [];
        $totalAssignments = 0;
        $totalDuplicates = 0;

        foreach ($classIds as $classId) {
            // Get subjects from the primary subject group for this class
            $primarySubjectGroup = \App\Models\School\SubjectGroup::where('class_id', $classId)
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->first();

            if (!$primarySubjectGroup) {
                continue; // Skip classes with no subject group
            }

            $subjectsFromGroup = $primarySubjectGroup->subjects()
                ->where('is_active', true)
                ->get();

            if ($subjectsFromGroup->isEmpty()) {
                continue; // Skip classes with no subjects
            }

            $class = \App\Models\School\Classe::find($classId);
            $classDuplicates = [];

            foreach ($subjectsFromGroup as $subject) {
                $totalAssignments++;

                // Check for existing assignment
                $existingAssignment = ExamClassAssignment::where('exam_type_id', $examTypeId)
                    ->where('class_id', $classId)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->first();

                if ($existingAssignment) {
                    $totalDuplicates++;
                    $classDuplicates[] = [
                        'subject_name' => $subject->name,
                        'subject_id' => $subject->id
                    ];
                }
            }

            if (!empty($classDuplicates)) {
                $duplicates[] = [
                    'class_name' => $class->name ?? 'Unknown Class',
                    'class_id' => $classId,
                    'subjects' => $classDuplicates
                ];
            }
        }

        $examType = SchoolExamType::find($examTypeId);

        if ($totalDuplicates > 0) {
            return response()->json([
                'success' => false,
                'has_duplicates' => true,
                'message' => "Duplicate assignments found!",
                'details' => [
                    'exam_type' => $examType->name ?? 'Unknown Exam Type',
                    'academic_year' => $currentAcademicYear->year_name,
                    'total_assignments_would_create' => $totalAssignments,
                    'total_duplicates_found' => $totalDuplicates,
                    'duplicates' => $duplicates
                ]
            ]);
        }

        return response()->json([
            'success' => true,
            'has_duplicates' => false,
            'message' => 'No duplicates found. You can proceed with creating the assignments.',
            'details' => [
                'exam_type' => $examType->name ?? 'Unknown Exam Type',
                'academic_year' => $currentAcademicYear->year_name,
                'total_assignments_would_create' => $totalAssignments
            ]
        ]);
    }
}