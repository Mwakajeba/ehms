<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Assignment;
use App\Models\School\AssignmentClass;
use App\Models\School\AssignmentAttachment;
use App\Models\School\AssignmentSubmission;
use App\Models\School\AcademicYear;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\School\Subject;
use App\Models\School\Student;
use App\Models\Hr\Employee;
use App\Models\SchoolGradeScale;
use App\Services\ParentNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssignmentSubmissionsExport;
use App\Imports\AssignmentSubmissionsImport;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('year_name', 'desc')
            ->get();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('school.assignments.index', compact('academicYears', 'classes', 'subjects'));
    }

    /**
     * Get assignments data for DataTables.
     */
    public function data(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'teacher', 'creator', 'assignmentClasses.classe', 'assignmentClasses.stream'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        // Apply default filter for current academic year if no academic year is selected
        if (!$request->filled('academic_year_id')) {
            $currentAcademicYear = AcademicYear::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('is_current', true)
                ->first();
            if ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id);
            }
        }

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('assignment_id', function ($assignment) {
                return $assignment->assignment_id;
            })
            ->addColumn('title', function ($assignment) {
                return $assignment->title;
            })
            ->addColumn('type_badge', function ($assignment) {
                $badges = [
                    'homework' => 'primary',
                    'classwork' => 'info',
                    'project' => 'success',
                    'revision_task' => 'warning'
                ];
                $badge = $badges[$assignment->type] ?? 'secondary';
                $typeName = ucwords(str_replace('_', ' ', $assignment->type));
                return '<span class="badge bg-' . $badge . '">' . $typeName . '</span>';
            })
            ->addColumn('subject_name', function ($assignment) {
                return $assignment->subject ? $assignment->subject->name : 'N/A';
            })
            ->addColumn('class_stream', function ($assignment) {
                if ($assignment->assignmentClasses->isEmpty()) {
                    return 'N/A';
                }
                
                $classStreams = $assignment->assignmentClasses->map(function ($ac) {
                    $class = $ac->classe ? $ac->classe->name : 'N/A';
                    $stream = $ac->stream ? ' - ' . $ac->stream->name : '';
                    return $class . $stream;
                })->unique()->implode(', ');
                
                return $classStreams ?: 'N/A';
            })
            ->addColumn('teacher_name', function ($assignment) {
                return $assignment->teacher 
                    ? ($assignment->teacher->first_name . ' ' . $assignment->teacher->last_name)
                    : 'N/A';
            })
            ->addColumn('due_date', function ($assignment) {
                return $assignment->due_date ? $assignment->due_date->format('M d, Y') : 'N/A';
            })
            ->addColumn('status_badge', function ($assignment) {
                $badges = [
                    'draft' => 'secondary',
                    'published' => 'success',
                    'closed' => 'warning',
                    'archived' => 'dark'
                ];
                $badge = $badges[$assignment->status] ?? 'secondary';
                return '<span class="badge bg-' . $badge . '">' . ucfirst($assignment->status) . '</span>';
            })
            ->addColumn('actions', function ($assignment) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<a href="' . route('school.assignments.show', $assignment->hashid) . '" class="btn btn-sm btn-info" title="View"><i class="bx bx-show"></i></a> ';
                $actions .= '<a href="' . route('school.assignments.edit', $assignment->hashid) . '" class="btn btn-sm btn-warning" title="Edit"><i class="bx bx-edit"></i></a> ';
                $actions .= '<a href="' . route('school.assignments.submissions', $assignment->hashid) . '" class="btn btn-sm btn-success" title="Add Submission"><i class="bx bx-plus-circle"></i></a> ';
                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-assignment" data-id="' . $assignment->hashid . '" title="Delete"><i class="bx bx-trash"></i></button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['type_badge', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new assignment.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('year_name', 'desc')
            ->get();

        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_current', true)
            ->first();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $teachers = Employee::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('school.assignments.create', compact(
            'academicYears',
            'currentAcademicYear',
            'classes',
            'subjects',
            'teachers'
        ));
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:homework,classwork,project,revision_task',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term' => 'nullable|string|max:50',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:hr_employees,id',
            'date_assigned' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date_assigned',
            'due_time' => 'nullable|date_format:H:i',
            'estimated_completion_time' => 'nullable|integer|min:1',
            'is_recurring' => 'nullable|boolean',
            'submission_type' => 'required|in:written,online_upload,photo_upload',
            'resubmission_allowed' => 'nullable|boolean',
            'max_attempts' => 'nullable|integer|min:1',
            'lock_after_deadline' => 'nullable|boolean',
            'total_marks' => 'nullable|numeric|min:0',
            'passing_marks' => 'nullable|numeric|min:0',
            'rubric' => 'nullable|string',
            'auto_graded' => 'nullable|boolean',
            'classes' => 'required|array|min:1',
            'classes.*.class_id' => 'required|exists:classes,id',
            'classes.*.stream_id' => 'nullable|exists:streams,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Generate assignment ID
            $assignmentId = Assignment::generateAssignmentId();

            // Create assignment
            $assignment = Assignment::create([
                'assignment_id' => $assignmentId,
                'title' => $request->title,
                'type' => $request->type,
                'description' => $request->description,
                'instructions' => $request->instructions,
                'academic_year_id' => $request->academic_year_id,
                'term' => $request->term,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'date_assigned' => $request->date_assigned,
                'due_date' => $request->due_date,
                'due_time' => $request->due_time,
                'estimated_completion_time' => $request->estimated_completion_time,
                'is_recurring' => $request->has('is_recurring'),
                'submission_type' => $request->submission_type,
                'resubmission_allowed' => $request->has('resubmission_allowed'),
                'max_attempts' => $request->max_attempts ?? 1,
                'lock_after_deadline' => $request->has('lock_after_deadline'),
                'total_marks' => $request->total_marks,
                'passing_marks' => $request->passing_marks,
                'rubric' => $request->rubric,
                'auto_graded' => $request->has('auto_graded'),
                'status' => $request->status ?? 'draft',
                'is_active' => true,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'created_by' => Auth::id(),
            ]);

            // Assign to classes
            foreach ($request->classes as $classData) {
                AssignmentClass::create([
                    'assignment_id' => $assignment->id,
                    'class_id' => $classData['class_id'],
                    'stream_id' => $classData['stream_id'] ?? null,
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                ]);
            }

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('assignments/attachments', 'public');
                    
                    AssignmentAttachment::create([
                        'attachable_type' => Assignment::class,
                        'attachable_id' => $assignment->id,
                        'file_name' => $file->hashName(),
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'company_id' => $companyId,
                        'branch_id' => $branchId,
                    ]);
                }
            }

            DB::commit();

            // Send notification if assignment is published
            if ($assignment->status === 'published') {
                try {
                    $notificationService = new ParentNotificationService();
                    $subject = $assignment->subject->name ?? 'Subject';
                    $classes = $assignment->assignmentClasses()->with('classe')->get();
                    
                    foreach ($classes as $assignmentClass) {
                        $class = $assignmentClass->classe;
                        $students = Student::where('class_id', $class->id)
                            ->where('academic_year_id', $assignment->academic_year_id)
                            ->where('status', 'active')
                            ->get();
                        
                        foreach ($students as $student) {
                            $title = 'Kazi Mpya Imetengenezwa';
                            $message = "Kazi mpya imetengenezwa: {$assignment->title} ({$subject}). Tarehe ya mwisho: " . \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y');
                            $notificationService->notifyStudentParents(
                                $student,
                                'assignment_published',
                                $title,
                                $message,
                                ['assignment_id' => $assignment->id, 'title' => $assignment->title]
                            );
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send assignment notification', ['error' => $e->getMessage()]);
                }
            }

            return redirect()->route('school.assignments.index')
                ->with('success', 'Assignment created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create assignment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified assignment.
     */
    public function show($hashId)
    {
        $assignment = Assignment::findByHashid($hashId, [
            'academicYear',
            'subject',
            'teacher',
            'creator',
            'assignmentClasses.classe',
            'assignmentClasses.stream',
            'attachments',
            'submissions.student',
        ]);

        if (!$assignment) {
            abort(404, 'Assignment not found');
        }

        return view('school.assignments.show', compact('assignment'));
    }

    /**
     * Show the form for editing the specified assignment.
     */
    public function edit($hashId)
    {
        $assignment = Assignment::findByHashid($hashId, [
            'assignmentClasses.classe',
            'assignmentClasses.stream',
            'attachments'
        ]);

        if (!$assignment) {
            abort(404, 'Assignment not found');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->orderBy('year_name', 'desc')
            ->get();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $teachers = Employee::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('school.assignments.edit', compact(
            'assignment',
            'academicYears',
            'classes',
            'subjects',
            'teachers'
        ));
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, $hashId)
    {
        $assignment = Assignment::findByHashid($hashId);

        if (!$assignment) {
            abort(404, 'Assignment not found');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'type' => 'required|in:homework,classwork,project,revision_task',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term' => 'nullable|string|max:50',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:hr_employees,id',
            'date_assigned' => 'required|date',
            'due_date' => 'required|date|after_or_equal:date_assigned',
            'due_time' => 'nullable|date_format:H:i',
            'estimated_completion_time' => 'nullable|integer|min:1',
            'is_recurring' => 'nullable|boolean',
            'submission_type' => 'required|in:written,online_upload,photo_upload',
            'resubmission_allowed' => 'nullable|boolean',
            'max_attempts' => 'nullable|integer|min:1',
            'lock_after_deadline' => 'nullable|boolean',
            'total_marks' => 'nullable|numeric|min:0',
            'passing_marks' => 'nullable|numeric|min:0',
            'rubric' => 'nullable|string',
            'auto_graded' => 'nullable|boolean',
            'status' => 'required|in:draft,published,closed,archived',
            'classes' => 'required|array|min:1',
            'classes.*.class_id' => 'required|exists:classes,id',
            'classes.*.stream_id' => 'nullable|exists:streams,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update assignment
            $assignment->update([
                'title' => $request->title,
                'type' => $request->type,
                'description' => $request->description,
                'instructions' => $request->instructions,
                'academic_year_id' => $request->academic_year_id,
                'term' => $request->term,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
                'date_assigned' => $request->date_assigned,
                'due_date' => $request->due_date,
                'due_time' => $request->due_time,
                'estimated_completion_time' => $request->estimated_completion_time,
                'is_recurring' => $request->has('is_recurring'),
                'submission_type' => $request->submission_type,
                'resubmission_allowed' => $request->has('resubmission_allowed'),
                'max_attempts' => $request->max_attempts ?? 1,
                'lock_after_deadline' => $request->has('lock_after_deadline'),
                'total_marks' => $request->total_marks,
                'passing_marks' => $request->passing_marks,
                'rubric' => $request->rubric,
                'auto_graded' => $request->has('auto_graded'),
                'status' => $request->status,
                'updated_by' => Auth::id(),
            ]);

            // Update classes - delete old and create new
            $assignment->assignmentClasses()->delete();
            foreach ($request->classes as $classData) {
                AssignmentClass::create([
                    'assignment_id' => $assignment->id,
                    'class_id' => $classData['class_id'],
                    'stream_id' => $classData['stream_id'] ?? null,
                    'company_id' => $assignment->company_id,
                    'branch_id' => $assignment->branch_id,
                ]);
            }

            // Handle new file attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filePath = $file->store('assignments/attachments', 'public');
                    
                    AssignmentAttachment::create([
                        'attachable_type' => Assignment::class,
                        'attachable_id' => $assignment->id,
                        'file_name' => $file->hashName(),
                        'original_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath,
                        'file_type' => $file->getClientOriginalExtension(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'company_id' => $assignment->company_id,
                        'branch_id' => $assignment->branch_id,
                    ]);
                }
            }

            DB::commit();

            // Send notification if assignment status changed to published
            if ($request->status === 'published' && $assignment->getOriginal('status') !== 'published') {
                try {
                    $notificationService = new ParentNotificationService();
                    $subject = $assignment->subject->name ?? 'Subject';
                    $classes = $assignment->assignmentClasses()->with('classe')->get();
                    
                    foreach ($classes as $assignmentClass) {
                        $class = $assignmentClass->classe;
                        $students = Student::where('class_id', $class->id)
                            ->where('academic_year_id', $assignment->academic_year_id)
                            ->where('status', 'active')
                            ->get();
                        
                        foreach ($students as $student) {
                            $title = 'Kazi Mpya Imetengenezwa';
                            $message = "Kazi mpya imetengenezwa: {$assignment->title} ({$subject}). Tarehe ya mwisho: " . \Carbon\Carbon::parse($assignment->due_date)->format('d/m/Y');
                            $notificationService->notifyStudentParents(
                                $student,
                                'assignment_published',
                                $title,
                                $message,
                                ['assignment_id' => $assignment->id, 'title' => $assignment->title]
                            );
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send assignment notification', ['error' => $e->getMessage()]);
                }
            }

            return redirect()->route('school.assignments.index')
                ->with('success', 'Assignment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update assignment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy($hashId)
    {
        $assignment = Assignment::findByHashid($hashId);

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'error' => 'Assignment not found'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Delete attachments
            foreach ($assignment->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->file_path);
                $attachment->delete();
            }

            // Delete assignment (cascade will handle related records)
            $assignment->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Failed to delete assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the submissions page for an assignment.
     */
    public function submissions($hashId)
    {
        $assignment = Assignment::findByHashid($hashId, ['academicYear', 'subject', 'teacher', 'assignmentClasses.classe', 'assignmentClasses.stream']);
        
        if (!$assignment) {
            abort(404, 'Assignment not found');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get all students from assigned classes/streams
        $studentIds = [];
        foreach ($assignment->assignmentClasses as $assignmentClass) {
            $query = Student::where('company_id', $companyId)
                ->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                })
                ->where('class_id', $assignmentClass->class_id)
                ->where('academic_year_id', $assignment->academic_year_id)
                ->where('status', 'active');

            if ($assignmentClass->stream_id) {
                $query->where('stream_id', $assignmentClass->stream_id);
            }

            $studentIds = array_merge($studentIds, $query->pluck('id')->toArray());
        }

        $students = Student::whereIn('id', array_unique($studentIds))
            ->with(['class', 'stream'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Get existing submissions
        $existingSubmissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        // Get grade scale for the academic year
        $gradeScale = SchoolGradeScale::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('academic_year_id', $assignment->academic_year_id)
            ->where('is_active', true)
            ->with('grades')
            ->first();

        // Prepare grade scale data for JavaScript
        $gradeScaleData = [];
        if ($gradeScale && $gradeScale->grades) {
            foreach ($gradeScale->grades as $grade) {
                $gradeScaleData[] = [
                    'min_marks' => (float)$grade->min_marks,
                    'max_marks' => (float)$grade->max_marks,
                    'grade_letter' => $grade->grade_letter,
                    'remarks' => $grade->remarks
                ];
            }
        }

        return view('school.assignments.submissions', compact('assignment', 'students', 'existingSubmissions', 'gradeScale', 'gradeScaleData'));
    }

    /**
     * Store submissions for an assignment.
     */
    public function storeSubmissions(Request $request, $hashId)
    {
        $assignment = Assignment::findByHashid($hashId);
        
        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'submissions' => 'required|array|min:1',
            'submissions.*.student_id' => 'required|exists:students,id',
            'submissions.*.marks_obtained' => 'nullable|numeric|min:0|max:' . ($assignment->total_marks ?? 999999),
            'submissions.*.teacher_comments' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Get grade scale
            $gradeScale = SchoolGradeScale::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('academic_year_id', $assignment->academic_year_id)
                ->where('is_active', true)
                ->with('grades')
                ->first();

            DB::beginTransaction();

            foreach ($request->submissions as $submissionData) {
                $student = Student::find($submissionData['student_id']);
                if (!$student) continue;

                // Calculate percentage if marks are provided
                $percentage = null;
                if (isset($submissionData['marks_obtained']) && $submissionData['marks_obtained'] !== '' && $assignment->total_marks) {
                    $percentage = ($submissionData['marks_obtained'] / $assignment->total_marks) * 100;
                }

                // Determine grade and remarks
                $grade = null;
                $remarks = null;
                if ($percentage !== null) {
                    if ($gradeScale) {
                        $gradeObj = $gradeScale->getGradeForMark($percentage);
                        if ($gradeObj) {
                            $grade = $gradeObj->grade_letter;
                            $remarks = $gradeObj->remarks;
                        }
                    } else {
                        // Fallback to default grading
                        if ($percentage >= 90) {
                            $grade = 'A';
                            $remarks = 'EXCELLENT';
                        } elseif ($percentage >= 80) {
                            $grade = 'B';
                            $remarks = 'VERY GOOD';
                        } elseif ($percentage >= 70) {
                            $grade = 'C';
                            $remarks = 'AVERAGE';
                        } elseif ($percentage >= 60) {
                            $grade = 'D';
                            $remarks = 'BELOW AVERAGE';
                        } else {
                            $grade = 'E';
                            $remarks = 'UNSATISFACTORY';
                        }
                    }
                }

                // Check if submission already exists (find by assignment_id and student_id, prefer attempt_number = 1)
                $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->where('student_id', $student->id)
                    ->orderBy('attempt_number', 'asc')
                    ->first();

                $submissionDataArray = [
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'class_id' => $student->class_id,
                    'stream_id' => $student->stream_id,
                    'marks_obtained' => $submissionData['marks_obtained'] ?? null,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'remarks' => $remarks,
                    'teacher_comments' => $submissionData['teacher_comments'] ?? null,
                    'status' => isset($submissionData['marks_obtained']) && $submissionData['marks_obtained'] !== '' ? 'marked' : 'submitted',
                    'marked_by' => Auth::id(),
                    'marked_at' => now(),
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                ];

                if ($submission) {
                    // Update existing submission
                    $submission->update($submissionDataArray);
                } else {
                    // Create new submission with attempt_number = 1
                    $submissionDataArray['attempt_number'] = 1;
                    AssignmentSubmission::create($submissionDataArray);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Submissions saved successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('AssignmentController@storeSubmissions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save submissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export sample Excel file for submissions.
     */
    public function exportSubmissionsSample($hashId)
    {
        $assignment = Assignment::findByHashid($hashId, ['academicYear', 'subject', 'teacher', 'assignmentClasses.classe', 'assignmentClasses.stream']);
        
        if (!$assignment) {
            abort(404, 'Assignment not found');
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get all students from assigned classes/streams
        $studentIds = [];
        foreach ($assignment->assignmentClasses as $assignmentClass) {
            $query = Student::where('company_id', $companyId)
                ->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
                })
                ->where('class_id', $assignmentClass->class_id)
                ->where('academic_year_id', $assignment->academic_year_id)
                ->where('status', 'active');

            if ($assignmentClass->stream_id) {
                $query->where('stream_id', $assignmentClass->stream_id);
            }

            $studentIds = array_merge($studentIds, $query->pluck('id')->toArray());
        }

        $students = Student::whereIn('id', array_unique($studentIds))
            ->with(['class', 'stream'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Get existing submissions
        $existingSubmissions = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $filename = 'assignment_submissions_' . str_replace(' ', '_', $assignment->assignment_id) . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(
            new AssignmentSubmissionsExport($students, $existingSubmissions, $assignment),
            $filename
        );
    }

    /**
     * Import submissions from Excel file.
     */
    public function importSubmissions(Request $request, $hashId)
    {
        $assignment = Assignment::findByHashid($hashId);
        
        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Get grade scale
            $gradeScale = SchoolGradeScale::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('academic_year_id', $assignment->academic_year_id)
                ->where('is_active', true)
                ->with('grades')
                ->first();

            $import = new AssignmentSubmissionsImport($assignment, $companyId, $branchId, $gradeScale);
            
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

            $message = "Successfully imported {$successCount} submission(s).";
            if (!empty($errors)) {
                $message .= " " . count($errors) . " error(s) occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'success_count' => $successCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            \Log::error('AssignmentController@importSubmissions error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to import submissions: ' . $e->getMessage()
            ], 500);
        }
    }
}

