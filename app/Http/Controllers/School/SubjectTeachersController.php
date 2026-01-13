<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\SubjectTeacher;
use App\Models\School\Classe;
use App\Models\School\Subject;
use App\Models\School\AcademicYear;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubjectTeachersController extends Controller
{
    /**
     * Display a listing of subject teacher assignments.
     */
    public function index()
    {
        $companyId = session('company_id') ?: Auth::user()->company_id ?? null;
        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;

        // Get subject teachers filtered by branch
        // Get all records and let DataTables handle pagination on client side
        $query = SubjectTeacher::with(['employee', 'subject', 'classe', 'stream', 'academicYear']);
        
        // Filter by branch_id directly (SubjectTeacher has branch_id field)
        // This ensures we only show assignments for the current branch
        if ($branchId) {
            $query->where('branch_id', $branchId);
        } else {
            // If no branch_id, get all records for the company
            if ($companyId) {
                $query->whereHas('employee', function($empQuery) use ($companyId) {
                    $empQuery->where('company_id', $companyId);
                });
            }
        }
        
        $subjectTeachers = $query->orderBy('created_at', 'desc')->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->where(function($query) {
                $query->where('is_current', true)
                      ->orWhere('status', 'active');
            })
            ->get();
        
        $classes = $companyId && $branchId
            ? Classe::forCompany($companyId)->forBranch($branchId)->active()->get()
            : Classe::active()->get();
        
        $subjects = \App\Models\School\Subject::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->get();
        
        $employees = Employee::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'active')
            ->get();

        return view('school.subject-teachers.index', compact('subjectTeachers', 'academicYears', 'classes', 'subjects', 'employees'));
    }

    /**
     * Show the form for creating a new subject teacher assignment.
     */
    public function create()
    {
        $companyId = session('company_id') ?: Auth::user()->company_id ?? null;
        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;

        $classes = $companyId && $branchId
            ? Classe::forCompany($companyId)->forBranch($branchId)->active()->get()
            : Classe::active()->get();
        
        $employees = Employee::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'active')
            ->get();
        
        $streams = \App\Models\School\Stream::all();
        
        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                });
            })
            ->where(function($query) {
                $query->where('is_current', true)
                      ->orWhere('status', 'active');
            })
            ->first();

        return view('school.subject-teachers.create', compact('classes', 'employees', 'streams', 'currentAcademicYear'));
    }

    /**
     * Store a newly created subject teacher assignment.
     */
    public function store(Request $request)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;
        $companyId = session('company_id') ?: Auth::user()->company_id ?? null;

        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'required|exists:streams,id',
            'assignments' => 'required|array|min:1',
            'assignments.*.employee_id' => [
                'required',
                Rule::exists('hr_employees', 'id')->where(function ($query) use ($branchId, $companyId) {
                    if ($branchId) $query->where('branch_id', $branchId);
                    if ($companyId) $query->where('company_id', $companyId);
                }),
            ],
            'assignments.*.subject_id' => 'required|exists:subjects,id',
            'assignments.*.is_active' => 'boolean'
        ]);

        // Get the current active academic year
        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where(function($query) use ($branchId) {
                if ($branchId) {
                    $query->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    });
                }
            })
            ->where(function($query) {
                $query->where('is_current', true)
                      ->orWhere('status', 'active');
            })
            ->first();
        
        if (!$currentAcademicYear) {
            return back()->withErrors(['academic_year' => 'No active academic year found. Please set an active academic year first.']);
        }

        $createdAssignments = [];
        $errors = [];

        foreach ($request->assignments as $index => $assignment) {
            try {
                // Check if this exact assignment already exists (including stream_id)
                // A teacher CAN teach the same subject in the same class to DIFFERENT streams
                // A teacher CANNOT teach the same subject in the same class to the SAME stream
                $duplicateExists = SubjectTeacher::where('employee_id', $assignment['employee_id'])
                    ->where('subject_id', $assignment['subject_id'])
                    ->where('class_id', $request->class_id)
                    ->where('stream_id', $request->stream_id) // Must include stream_id in check
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->exists();

                if ($duplicateExists) {
                    $employee = Employee::find($assignment['employee_id']);
                    $subject = Subject::find($assignment['subject_id']);
                    $employeeName = $employee ? $employee->first_name . ' ' . $employee->last_name : 'Unknown';
                    $subjectName = $subject ? $subject->name : 'Unknown';
                    $errors[] = "Assignment #".($index + 1).": Teacher {$employeeName} is already assigned to subject {$subjectName} for this class and stream combination.";
                    continue;
                }

                $newAssignment = SubjectTeacher::create([
                    'employee_id' => $assignment['employee_id'],
                    'subject_id' => $assignment['subject_id'],
                    'class_id' => $request->class_id,
                    'stream_id' => $request->stream_id,
                    'academic_year_id' => $currentAcademicYear->id,
                    'branch_id' => $branchId,
                    'is_active' => $assignment['is_active'] ?? true,
                ]);

                $createdAssignments[] = $newAssignment;
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle unique constraint violations specifically
                if ($e->getCode() === '23000' || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $employee = Employee::find($assignment['employee_id']);
                    $subject = Subject::find($assignment['subject_id']);
                    $employeeName = $employee ? $employee->first_name . ' ' . $employee->last_name : 'Unknown';
                    $subjectName = $subject ? $subject->name : 'Unknown';
                    $errors[] = "Assignment #".($index + 1).": Teacher {$employeeName} is already assigned to subject {$subjectName} for this class and stream combination (database constraint violation).";
                } else {
                    $errors[] = "Assignment #".($index + 1).": Failed to create assignment - " . $e->getMessage();
                }
            } catch (\Exception $e) {
                $errors[] = "Assignment #".($index + 1).": Failed to create assignment - " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            if (!empty($createdAssignments)) {
                $successMessage = count($createdAssignments) . ' assignment(s) created successfully.';
                if (!empty($errors)) {
                    $successMessage .= ' However, some assignments failed: ' . implode(', ', $errors);
                }
                return redirect()->route('school.subject-teachers.index')
                    ->with('warning', $successMessage);
            } else {
                return back()->withErrors(['assignments' => implode(' | ', $errors)]);
            }
        }

        return redirect()->route('school.subject-teachers.index')
            ->with('success', count($createdAssignments) . ' subject teacher assignment(s) created successfully.');
    }

    /**
     * Display the specified subject teacher assignment.
     */
    public function show(SubjectTeacher $subjectTeacher)
    {
        $subjectTeacher->load(['employee', 'subject', 'classe', 'stream', 'academicYear']);
        return view('school.subject-teachers.show', compact('subjectTeacher'));
    }

    /**
     * Show the form for editing the specified subject teacher assignment.
     */
    public function edit(SubjectTeacher $subjectTeacher)
    {
        $academicYears = AcademicYear::where('status', 'active')->get();
        $classes = Classe::all();
        $subjects = Subject::all();
        $employees = Employee::where('status', 'active')->get();
        $streams = \App\Models\School\Stream::all();

        return view('school.subject-teachers.edit', compact('subjectTeacher', 'academicYears', 'classes', 'subjects', 'employees', 'streams'));
    }

    /**
     * Update the specified subject teacher assignment.
     */
    public function update(Request $request, SubjectTeacher $subjectTeacher)
    {
        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'required|exists:streams,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_active' => 'boolean'
        ]);

        // Check if this assignment already exists (excluding current record)
        // First check if the same teacher is already assigned to this combination
        $teacherExists = SubjectTeacher::where('employee_id', $request->employee_id)
            ->where('subject_id', $request->subject_id)
            ->where('class_id', $request->class_id)
            ->where('stream_id', $request->stream_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('id', '!=', $subjectTeacher->id)
            ->exists();

        if ($teacherExists) {
            return redirect()->back()->withInput()->with('sweetalert', [
                'type' => 'error',
                'title' => 'Duplicate Assignment',
                'message' => 'This teacher is already assigned to this subject, class, and stream for the selected academic year.'
            ]);
        }

        // Also check if any other teacher is already assigned to this subject-class-stream combination
        $assignmentExists = SubjectTeacher::where('subject_id', $request->subject_id)
            ->where('class_id', $request->class_id)
            ->where('stream_id', $request->stream_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('id', '!=', $subjectTeacher->id)
            ->exists();

        if ($assignmentExists) {
            $existingAssignment = SubjectTeacher::where('subject_id', $request->subject_id)
                ->where('class_id', $request->class_id)
                ->where('stream_id', $request->stream_id)
                ->where('academic_year_id', $request->academic_year_id)
                ->where('id', '!=', $subjectTeacher->id)
                ->with('employee')
                ->first();

            return redirect()->back()->withInput()->with('sweetalert', [
                'type' => 'warning',
                'title' => 'Assignment Already Exists',
                'message' => 'Another teacher (' . $existingAssignment->employee->first_name . ' ' . $existingAssignment->employee->last_name . ') is already assigned to this subject, class, and stream combination for the selected academic year.'
            ]);
        }

        $subjectTeacher->update($request->all());

        return redirect()->route('school.subject-teachers.index')
            ->with('success', 'Subject teacher assignment updated successfully.');
    }

    /**
     * Remove the specified subject teacher assignment.
     */
    public function destroy(SubjectTeacher $subjectTeacher)
    {
        $subjectTeacher->delete();

        return redirect()->route('school.subject-teachers.index')
            ->with('success', 'Subject teacher assignment removed successfully.');
    }

    /**
     * Toggle the active status of a subject teacher assignment.
     */
    public function toggleStatus(SubjectTeacher $subjectTeacher)
    {
        $subjectTeacher->update(['is_active' => !$subjectTeacher->is_active]);

        $status = $subjectTeacher->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Subject teacher assignment {$status} successfully.");
    }

    /**
     * Get streams for a specific class.
     */
    public function getStreams($classId)
    {
        if (!$classId) {
            return response()->json(['streams' => []]);
        }

        $streams = \App\Models\School\Stream::whereHas('classes', function($query) use ($classId) {
            $query->where('classes.id', $classId);
        })->get(['id', 'name']);

        return response()->json(['streams' => $streams]);
    }

    /**
     * Get subjects for a specific class.
     */
    public function getSubjects($classId)
    {
        if (!$classId) {
            return response()->json(['subjects' => []]);
        }

        // Get subjects that belong to subject groups for this class
        $subjects = \App\Models\School\Subject::whereHas('subjectGroups', function($query) use ($classId) {
            $query->where('subject_groups.class_id', $classId)
                  ->where('subject_groups.is_active', true);
        })
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name', 'code']);

        return response()->json(['subjects' => $subjects]);
    }

    /**
     * Check for duplicate assignments.
     */
    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'required|exists:streams,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'exclude_id' => 'nullable|exists:subject_teachers,id'
        ]);

        $excludeId = $request->exclude_id;

        // Check if the same teacher is already assigned to this combination
        $teacherExists = SubjectTeacher::where('employee_id', $request->employee_id)
            ->where('subject_id', $request->subject_id)
            ->where('class_id', $request->class_id)
            ->where('stream_id', $request->stream_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->when($excludeId, function($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists();

        // Check if any other teacher is already assigned to this subject-class-stream combination
        $assignmentExists = SubjectTeacher::where('subject_id', $request->subject_id)
            ->where('class_id', $request->class_id)
            ->where('stream_id', $request->stream_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->when($excludeId, function($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->with('employee')
            ->first();

        $response = [
            'duplicate_teacher' => $teacherExists,
            'duplicate_assignment' => $assignmentExists ? true : false,
            'existing_teacher' => $assignmentExists ? $assignmentExists->employee->first_name . ' ' . $assignmentExists->employee->last_name : null
        ];

        return response()->json($response);
    }
}