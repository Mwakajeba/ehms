<?php

namespace App\Http\Controllers;

use App\Models\ExamClassAssignment;
use App\Models\SchoolExam;
use App\Models\School\Classe;
use App\Models\School\Subject;
use App\Models\School\AcademicYear;
use App\Models\School\Stream;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ExamClassAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $exams = SchoolExam::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->orderBy('exam_date', 'desc')
            ->get();

        $classes = Classe::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('school.exam-class-assignments.index', compact('exams', 'classes', 'academicYears'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $examId = $request->get('exam_id');

        $exams = SchoolExam::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->with(['examType', 'subject', 'class', 'academicYear'])
            ->orderBy('exam_date', 'desc')
            ->get();

        $classes = Classe::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->with('streams')
            ->orderBy('name')
            ->get();

        $subjects = Subject::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();

        $streams = Stream::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('school.exam-class-assignments.create', compact(
            'exams', 'classes', 'subjects', 'academicYears', 'streams', 'examId'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:school_exams,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'stream_id' => 'nullable|exists:streams,id',
            'assigned_date' => 'required|date|before_or_equal:today',
            'due_date' => 'nullable|date|after:assigned_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if assignment already exists
        $existingAssignment = ExamClassAssignment::where('exam_id', $request->exam_id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->when($request->stream_id, function ($query) use ($request) {
                return $query->where('stream_id', $request->stream_id);
            })
            ->first();

        if ($existingAssignment) {
            return redirect()->back()
                ->with('error', 'This class-subject combination is already assigned to this exam.')
                ->withInput();
        }

        ExamClassAssignment::create([
            'company_id' => Auth::user()->company_id,
            'branch_id' => session('branch_id') ?: Auth::user()->branch_id,
            'exam_id' => $request->exam_id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'academic_year_id' => $request->academic_year_id,
            'stream_id' => $request->stream_id,
            'assigned_date' => $request->assigned_date,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('school.exam-class-assignments.index')
            ->with('success', 'Exam class assignment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ExamClassAssignment $examClassAssignment): View
    {
        // Check if user has access to this assignment
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examClassAssignment->company_id !== Auth::user()->company_id ||
            ($examClassAssignment->branch_id && $examClassAssignment->branch_id !== $userBranchId)) {
            abort(403, 'Unauthorized access to exam class assignment.');
        }

        $examClassAssignment->load(['exam.examType', 'classe', 'subject', 'academicYear', 'stream', 'creator']);

        return view('school.exam-class-assignments.show', compact('examClassAssignment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExamClassAssignment $examClassAssignment): View
    {
        // Check if user has access to this assignment
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examClassAssignment->company_id !== Auth::user()->company_id ||
            ($examClassAssignment->branch_id && $examClassAssignment->branch_id !== $userBranchId)) {
            abort(403, 'Unauthorized access to exam class assignment.');
        }

        $exams = SchoolExam::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->with(['examType', 'subject', 'class', 'academicYear'])
            ->orderBy('exam_date', 'desc')
            ->get();

        $classes = Classe::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->with('streams')
            ->orderBy('name')
            ->get();

        $subjects = Subject::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();

        $streams = Stream::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->orderBy('name')
            ->get();

        return view('school.exam-class-assignments.edit', compact(
            'examClassAssignment', 'exams', 'classes', 'subjects', 'academicYears', 'streams'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExamClassAssignment $examClassAssignment): RedirectResponse
    {
        // Check if user has access to this assignment
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examClassAssignment->company_id !== Auth::user()->company_id ||
            ($examClassAssignment->branch_id && $examClassAssignment->branch_id !== $userBranchId)) {
            abort(403, 'Unauthorized access to exam class assignment.');
        }

        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:school_exams,id',
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'stream_id' => 'nullable|exists:streams,id',
            'status' => 'required|in:assigned,in_progress,completed,cancelled',
            'assigned_date' => 'required|date|before_or_equal:today',
            'due_date' => 'nullable|date|after:assigned_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if assignment already exists (excluding current)
        $existingAssignment = ExamClassAssignment::where('exam_id', $request->exam_id)
            ->where('class_id', $request->class_id)
            ->where('subject_id', $request->subject_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->when($request->stream_id, function ($query) use ($request) {
                return $query->where('stream_id', $request->stream_id);
            })
            ->where('id', '!=', $examClassAssignment->id)
            ->first();

        if ($existingAssignment) {
            return redirect()->back()
                ->with('error', 'This class-subject combination is already assigned to this exam.')
                ->withInput();
        }

        $examClassAssignment->update([
            'exam_id' => $request->exam_id,
            'class_id' => $request->class_id,
            'subject_id' => $request->subject_id,
            'academic_year_id' => $request->academic_year_id,
            'stream_id' => $request->stream_id,
            'status' => $request->status,
            'assigned_date' => $request->assigned_date,
            'due_date' => $request->due_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('school.exam-class-assignments.index')
            ->with('success', 'Exam class assignment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExamClassAssignment $examClassAssignment): RedirectResponse
    {
        // Check if user has access to this assignment
        $userBranchId = session('branch_id') ?: Auth::user()->branch_id;
        if ($examClassAssignment->company_id !== Auth::user()->company_id ||
            ($examClassAssignment->branch_id && $examClassAssignment->branch_id !== $userBranchId)) {
            abort(403, 'Unauthorized access to exam class assignment.');
        }

        $examClassAssignment->delete();

        return redirect()->route('school.exam-class-assignments.index')
            ->with('success', 'Exam class assignment deleted successfully.');
    }

    /**
     * Get exam class assignments data for DataTables
     */
    public function data(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = ExamClassAssignment::forCompany($companyId)
            ->forBranch($branchId)
            ->with(['exam.examType', 'classe', 'subject', 'academicYear', 'stream']);

        // Apply filters
        if ($request->has('exam_id') && $request->exam_id) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->has('class_id') && $request->class_id) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('academic_year_id') && $request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('exam_info', function ($assignment) {
                $examName = $assignment->exam->exam_name ?? 'N/A';
                $examType = $assignment->exam->examType->name ?? 'N/A';
                $examDate = $assignment->exam->exam_date ? $assignment->exam->exam_date->format('M d, Y') : 'N/A';
                return "<strong>{$examName}</strong><br><small class='text-muted'>{$examType} - {$examDate}</small>";
            })
            ->addColumn('class_subject', function ($assignment) {
                $className = $assignment->classe->name ?? 'N/A';
                $subjectName = $assignment->subject->name ?? 'N/A';
                $streamName = $assignment->stream ? " ({$assignment->stream->name})" : '';
                return "<strong>{$className}{$streamName}</strong><br><small class='text-muted'>{$subjectName}</small>";
            })
            ->addColumn('dates', function ($assignment) {
                $assigned = $assignment->assigned_date->format('M d, Y');
                $due = $assignment->due_date ? $assignment->due_date->format('M d, Y') : 'No due date';
                return "<strong>Assigned:</strong> {$assigned}<br><small class='text-muted'><strong>Due:</strong> {$due}</small>";
            })
            ->addColumn('status_badge', function ($assignment) {
                return $assignment->getStatusBadge();
            })
            ->addColumn('actions', function ($assignment) {
                $actions = '';

                // View button
                $actions .= '<a href="' . route('school.exam-class-assignments.show', $assignment->id) . '" class="btn btn-sm btn-info" title="View">';
                $actions .= '<i class="bx bx-show"></i>';
                $actions .= '</a> ';

                // Edit button
                $actions .= '<a href="' . route('school.exam-class-assignments.edit', $assignment->id) . '" class="btn btn-sm btn-warning" title="Edit">';
                $actions .= '<i class="bx bx-edit"></i>';
                $actions .= '</a> ';

                // Delete button
                $actions .= '<button type="button" class="btn btn-sm btn-danger delete-btn" title="Delete" data-id="' . $assignment->id . '" data-url="' . route('school.exam-class-assignments.destroy', $assignment->id) . '">';
                $actions .= '<i class="bx bx-trash"></i>';
                $actions .= '</button>';

                return $actions;
            })
            ->rawColumns(['exam_info', 'class_subject', 'dates', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * Get subjects for a specific class (AJAX)
     */
    public function getSubjectsForClass(Request $request): JsonResponse
    {
        $classId = $request->get('class_id');
        $academicYearId = $request->get('academic_year_id');

        if (!$classId || !$academicYearId) {
            return response()->json(['subjects' => []]);
        }

        // Get subjects that are assigned to this class in the academic year
        // This would typically come from a curriculum or subject assignment table
        // For now, return all active subjects for the company/branch
        $subjects = Subject::forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json(['subjects' => $subjects]);
    }

    /**
     * Get streams for a specific class (AJAX)
     */
    public function getStreamsForClass(Request $request): JsonResponse
    {
        $classId = $request->get('class_id');

        if (!$classId) {
            return response()->json(['streams' => []]);
        }

        $streams = Classe::find($classId)
            ->streams()
            ->forCompany(Auth::user()->company_id)
            ->forBranch(session('branch_id') ?: Auth::user()->branch_id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['streams' => $streams]);
    }
}
