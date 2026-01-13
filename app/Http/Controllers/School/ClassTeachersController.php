<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\ClassTeacher;
use App\Models\School\Classe;
use App\Models\School\AcademicYear;
use App\Models\Hr\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class ClassTeachersController extends Controller
{
    /**
     * Display a listing of class teacher assignments.
     */
    public function index()
    {
        $companyId = session('company_id') ?: Auth::user()->company_id ?? null;
        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;

        // Academic years not required on the index view; class teachers will default to current academic year.
        $classes = $companyId && $branchId
            ? Classe::forCompany($companyId)->forBranch($branchId)->active()->get()
            : Classe::active()->get();
        $streams = \App\Models\School\Stream::all();
        $employees = Employee::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'active')->get();

        return view('school.class-teachers.index', compact('classes', 'streams', 'employees'));
    }

    /**
     * Get class teachers data for DataTables.
     */
    public function data(Request $request)
    {
        $currentAcademicYear = AcademicYear::where('status', 'active')->first();

        $query = ClassTeacher::with(['employee', 'classe', 'stream', 'academicYear', 'branch'])
            ->when($request->class_id, function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            })
            ->when($request->stream_id, function($q) use ($request) {
                $q->where('stream_id', $request->stream_id);
            })
            ->when($request->employee_id, function($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            })
            ->when($request->academic_year_id, function($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            }, function($q) use ($currentAcademicYear) {
                // If no academic_year filter provided, default to current academic year
                if ($currentAcademicYear) {
                    $q->where('academic_year_id', $currentAcademicYear->id);
                }
            })
            ->when(!is_null($request->status), function($q) use ($request) {
                // status filter expects 'active' or 'inactive', or boolean 1/0
                if ($request->status === 'active' || $request->status == 1) {
                    $q->where('is_active', true);
                } elseif ($request->status === 'inactive' || $request->status == 0) {
                    $q->where('is_active', false);
                }
            })
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_info', function ($classTeacher) {
                return [
                    'name' => $classTeacher->employee->full_name,
                    'number' => $classTeacher->employee->employee_number
                ];
            })
            ->addColumn('class_name', function ($classTeacher) {
                return $classTeacher->classe->name;
            })
            ->addColumn('stream_name', function ($classTeacher) {
                return $classTeacher->stream ? $classTeacher->stream->name : '-';
            })
            ->addColumn('academic_year_name', function ($classTeacher) {
                return $classTeacher->academicYear->year_name;
            })
            ->addColumn('status_badge', function ($classTeacher) {
                return $classTeacher->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-secondary">Inactive</span>';
            })
            ->addColumn('assigned_date', function ($classTeacher) {
                return $classTeacher->created_at->format('M d, Y');
            })
            ->addColumn('actions', function ($classTeacher) {
                return view('school.class-teachers.partials.actions', compact('classTeacher'))->render();
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new class teacher assignment.
     */
    public function create()
    {
        $companyId = session('company_id') ?: Auth::user()->company_id ?? null;
        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;

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
        $streams = \App\Models\School\Stream::all();
        $employees = Employee::when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('status', 'active')->get();

        return view('school.class-teachers.create', compact('academicYears', 'classes', 'streams', 'employees'));
    }

    /**
     * Store a newly created class teacher assignment.
     */
    public function store(Request $request)
    {
        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;
        $companyId = session('company_id') ?: Auth::user()->company_id ?? null;

        $request->validate([
            'assignments' => 'required|array|min:1',
            'assignments.*.employee_id' => [
                'required',
                Rule::exists('hr_employees', 'id')->where(function ($query) use ($branchId, $companyId) {
                    if ($branchId) $query->where('branch_id', $branchId);
                    if ($companyId) $query->where('company_id', $companyId);
                }),
            ],
            'assignments.*.class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(function ($query) use ($branchId, $companyId) {
                    if ($branchId) $query->where('branch_id', $branchId);
                    if ($companyId) $query->where('company_id', $companyId);
                }),
            ],
            'assignments.*.stream_id' => 'required|exists:streams,id',
        ]);

        // Get the current academic year - check both is_current and status
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

        // Get branch ID
        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;

        $createdAssignments = [];
        $errors = [];

        $force = (bool) $request->get('force', false);

        foreach ($request->assignments as $index => $assignment) {
            try {
                // Check if this class+stream assignment already exists (across employees)
                $duplicateQuery = ClassTeacher::where('class_id', $assignment['class_id'])
                    ->where('stream_id', $assignment['stream_id'] ?? null)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('branch_id', $branchId);

                $exists = $duplicateQuery->exists();

                if ($exists && !$force) {
                    // Find existing assignment and include employee info if available
                    $existing = $duplicateQuery->with('employee')->first();
                    $employeeName = $existing && $existing->employee ? $existing->employee->full_name : 'N/A';
                    $class = Classe::find($assignment['class_id']);
                    $className = $class?->name ?? 'Unknown Class';
                    $streamName = null;
                    if ($assignment['stream_id']) {
                        $stream = \App\Models\School\Stream::find($assignment['stream_id']);
                        $streamName = $stream?->name ?? 'Unknown Stream';
                    }
                    $errors[] = "Assignment #".($index + 1).": {$className} (stream: " . ($streamName ? "'{$streamName}'" : 'none') . ") already assigned to {$employeeName}. Use override to replace.";
                    continue;
                }

                // If we're forcing, remove existing assignment(s) for that class+stream+year+branch
                if ($exists && $force) {
                    ClassTeacher::where('class_id', $assignment['class_id'])
                        ->where('stream_id', $assignment['stream_id'] ?? null)
                        ->where('academic_year_id', $currentAcademicYear->id)
                        ->where('branch_id', $branchId)
                        ->delete();
                }

                $newAssignment = ClassTeacher::create([
                    'employee_id' => $assignment['employee_id'],
                    'class_id' => $assignment['class_id'],
                    'stream_id' => $assignment['stream_id'] ?? null,
                    'academic_year_id' => $currentAcademicYear->id,
                    'branch_id' => $branchId,
                    'is_active' => $assignment['is_active'] ?? true,
                ]);

                $createdAssignments[] = $newAssignment;
            } catch (\Exception $e) {
                $errors[] = "Assignment #".($index + 1).": Failed to create assignment - " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            // If there were errors but some assignments were created, show partial success
            if (!empty($createdAssignments)) {
                $successMessage = count($createdAssignments) . ' assignment(s) created successfully.';
                if (!empty($errors)) {
                    $successMessage .= ' However, some assignments failed: ' . implode(', ', $errors);
                }
                return redirect()->route('school.class-teachers.index')
                    ->with('warning', $successMessage);
            } else {
                // All assignments failed
                return back()->withErrors(['assignments' => implode(' | ', $errors)]);
            }
        }

        return redirect()->route('school.class-teachers.index')
            ->with('success', count($createdAssignments) . ' class teacher assignment(s) created successfully.');
    }

    /**
     * Display the specified class teacher assignment.
     */
    public function show(ClassTeacher $classTeacher)
    {
        $classTeacher->load(['employee', 'classe', 'stream', 'academicYear', 'branch']);
        return view('school.class-teachers.show', compact('classTeacher'));
    }

    /**
     * Show the form for editing the specified class teacher assignment.
     */
    public function edit(ClassTeacher $classTeacher)
    {
        $classTeacher->load(['employee', 'classe', 'academicYear', 'branch']);
        $academicYears = AcademicYear::where('status', 'active')->get();
        $classes = Classe::all();
        $streams = \App\Models\School\Stream::all();
        $employees = Employee::where('status', 'active')->get();

        return view('school.class-teachers.edit', compact('classTeacher', 'academicYears', 'classes', 'streams', 'employees'));
    }

    /**
     * Update the specified class teacher assignment.
     */
    public function update(Request $request, ClassTeacher $classTeacher)
    {
        $request->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'required|exists:streams,id',
            'is_active' => 'boolean'
        ]);

        // Get the current academic year
        $currentAcademicYear = AcademicYear::where('status', 'active')->first();
        if (!$currentAcademicYear) {
            return back()->withErrors(['academic_year' => 'No active academic year found. Please set an active academic year first.']);
        }

        // Get branch ID
        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;

        // Check if this class+stream assignment already exists (excluding current record)
        $exists = ClassTeacher::where('class_id', $request->class_id)
            ->where('stream_id', $request->stream_id ?? null)
            ->where('academic_year_id', $currentAcademicYear->id)
            ->where('id', '!=', $classTeacher->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['assignment' => 'A class teacher assignment for this Class and Stream already exists for the current academic year.']);
        }

        $classTeacher->update([
            'employee_id' => $request->employee_id,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id ?? null,
            'academic_year_id' => $currentAcademicYear->id,
            'branch_id' => $branchId,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('school.class-teachers.index')
            ->with('success', 'Class teacher assignment updated successfully.');
    }

    /**
     * Remove the specified class teacher assignment.
     */
    public function destroy(ClassTeacher $classTeacher)
    {
        $classTeacher->delete();

        return redirect()->route('school.class-teachers.index')
            ->with('success', 'Class teacher assignment removed successfully.');
    }

    /**
     * Toggle the active status of a class teacher assignment.
     */
    public function toggleStatus(ClassTeacher $classTeacher)
    {
        $classTeacher->update(['is_active' => !$classTeacher->is_active]);

        $status = $classTeacher->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Class teacher assignment {$status} successfully.");
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
     * Check duplicate assignments for class+stream in the current academic year & branch.
     */
    public function checkDuplicates(Request $request)
    {
        $this->validate($request, [
            'assignments' => 'required|array',
            'assignments.*.class_id' => 'required|exists:classes,id',
            'assignments.*.stream_id' => 'nullable|exists:streams,id',
        ]);

        $branchId = session('branch_id') ?: Auth::user()->branch_id ?? null;
        $currentAcademicYear = AcademicYear::where('status', 'active')->first();
        if (!$currentAcademicYear) {
            return response()->json(['error' => 'No active academic year found.'], 422);
        }

        $duplicates = [];
        foreach ($request->assignments as $index => $assignment) {
            $exists = ClassTeacher::where('class_id', $assignment['class_id'])
                ->where('stream_id', $assignment['stream_id'] ?? null)
                ->where('academic_year_id', $currentAcademicYear->id)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->with('employee', 'classe', 'stream')
                ->first();

            if ($exists) {
                $duplicates[] = [
                    'index' => $index,
                    'class_id' => $exists->class_id,
                    'stream_id' => $exists->stream_id,
                    'existing_assignment' => [
                        'id' => $exists->id,
                        'employee_id' => $exists->employee?->id,
                        'employee_name' => $exists->employee?->full_name,
                        'class_name' => $exists->classe?->name,
                        'stream_name' => $exists->stream?->name,
                    ],
                ];
            }
        }

        return response()->json(['duplicates' => $duplicates]);
    }
}