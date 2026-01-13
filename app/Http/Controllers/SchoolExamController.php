<?php

namespace App\Http\Controllers;

use App\Models\SchoolExam;
use App\Models\SchoolExamType;
use App\Models\School\AcademicYear;
use App\Models\School\Subject;
use App\Models\School\Classe;
use App\Models\School\Stream;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SchoolExamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $companyId = Auth::user()->company_id;
        $branchId = Auth::user()->branch_id;

        // Handle AJAX request for DataTables
        if ($request->ajax()) {
            $query = SchoolExam::forCompany($companyId)
                ->forBranch($branchId)
                ->with(['examType', 'subject', 'class', 'stream', 'academicYear']);

            // Filters
            if ($request->has('academic_year_id') && !empty($request->academic_year_id)) {
                $query->forAcademicYear($request->academic_year_id);
            }

            if ($request->has('exam_type_id') && !empty($request->exam_type_id)) {
                $query->forExamType($request->exam_type_id);
            }

            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->forClass($request->class_id);
            }

            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $query->forSubject($request->subject_id);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->withStatus($request->status);
            }

            // Date range filter
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->where('exam_date', '>=', $request->date_from);
            }

            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->where('exam_date', '<=', $request->date_to);
            }

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('exam_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('examType', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('subject', function ($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $exams = $query->orderBy('exam_date', 'desc')
                          ->orderBy('start_time', 'desc')
                          ->get();

            return response()->json([
                'data' => $exams->map(function ($exam) {
                    return [
                        'id' => $exam->id,
                        'exam_name' => $exam->exam_name,
                        'exam_type' => $exam->examType,
                        'academic_year' => $exam->academicYear,
                        'subject' => $exam->subject,
                        'class' => $exam->class,
                        'stream' => $exam->stream,
                        'exam_date' => $exam->exam_date,
                        'start_time' => $exam->start_time,
                        'end_time' => $exam->end_time,
                        'max_marks' => $exam->max_marks,
                        'pass_marks' => $exam->pass_marks,
                        'weight' => $exam->weight,
                        'status' => $exam->status,
                        'actions' => view('school.exams.partials.actions', compact('exam'))->render(),
                    ];
                })
            ]);
        }

        // Regular view request
        $exams = SchoolExam::forCompany($companyId)
            ->forBranch($branchId)
            ->with(['examType', 'subject', 'class', 'stream', 'academicYear'])
            ->orderBy('exam_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(15);

        // Get filter options
        $academicYears = AcademicYear::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('year_name')
            ->get();

        $examTypes = SchoolExamType::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $classes = Classe::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $subjects = Subject::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $streams = Stream::forCompany($companyId)
            ->forBranch($branchId)
            ->orderBy('name')
            ->get();

        return view('school.exams.index', compact(
            'exams', 'academicYears', 'examTypes', 'classes', 'subjects', 'streams'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $companyId = Auth::user()->company_id;
        $branchId = Auth::user()->branch_id;

        $academicYears = AcademicYear::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('year_name')
            ->get();

        $examTypes = SchoolExamType::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $classes = Classe::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $subjects = Subject::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $streams = Stream::forCompany($companyId)
            ->forBranch($branchId)
            ->orderBy('name')
            ->get();

        return view('school.exams.create', compact(
            'academicYears', 'examTypes', 'classes', 'subjects', 'streams'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required|exists:academic_years,id',
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
            'exam_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'exam_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_marks' => 'required|numeric|min:0|max:999.99',
            'pass_marks' => 'required|numeric|min:0|max_marks',
            'weight' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:scheduled,draft,ongoing,completed,cancelled',
            'instructions' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate that the selected entities belong to the user's company/branch
        $this->validateEntityOwnership($request);

        // Check for exam scheduling conflicts
        $conflict = SchoolExam::forCompany(Auth::user()->company_id)
            ->forBranch(Auth::user()->branch_id)
            ->where('exam_date', $request->exam_date)
            ->where('class_id', $request->class_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })
            ->when($request->stream_id, function ($query) use ($request) {
                return $query->where('stream_id', $request->stream_id);
            })
            ->where('id', '!=', $request->id ?? null)
            ->exists();

        if ($conflict) {
            return redirect()->back()
                ->withErrors(['exam_date' => 'There is a scheduling conflict with another exam for this class/stream at the selected time.'])
                ->withInput();
        }

        SchoolExam::create([
            'company_id' => Auth::user()->company_id,
            'branch_id' => Auth::user()->branch_id,
            'academic_year_id' => $request->academic_year_id,
            'exam_type_id' => $request->exam_type_id,
            'subject_id' => $request->subject_id,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id,
            'exam_name' => $request->exam_name,
            'description' => $request->description,
            'exam_date' => $request->exam_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_marks' => $request->max_marks,
            'pass_marks' => $request->pass_marks,
            'weight' => $request->weight,
            'status' => $request->status,
            'instructions' => $request->instructions,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('school.exams.index')
            ->with('success', 'Exam created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SchoolExam $exam): View
    {
        // Check if user has access to this exam
        if ($exam->company_id !== Auth::user()->company_id ||
            ($exam->branch_id && $exam->branch_id !== Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to exam.');
        }

        $exam->load([
            'examType', 'subject', 'class', 'stream', 'academicYear', 'creator'
        ]);

        return view('school.exams.show', compact('exam'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SchoolExam $exam): View
    {
        // Check if user has access to this exam
        if ($exam->company_id !== Auth::user()->company_id ||
            ($exam->branch_id && $exam->branch_id !== Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to exam.');
        }

        $companyId = Auth::user()->company_id;
        $branchId = Auth::user()->branch_id;

        $academicYears = AcademicYear::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('year_name')
            ->get();

        $examTypes = SchoolExamType::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $classes = Classe::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $subjects = Subject::forCompany($companyId)
            ->forBranch($branchId)
            ->active()
            ->orderBy('name')
            ->get();

        $streams = Stream::forCompany($companyId)
            ->forBranch($branchId)
            ->where('class_id', $exam->class_id)
            ->orderBy('name')
            ->get();

        return view('school.exams.edit', compact(
            'exam', 'academicYears', 'examTypes', 'classes', 'subjects', 'streams'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SchoolExam $exam): RedirectResponse
    {
        // Check if user has access to this exam
        if ($exam->company_id !== Auth::user()->company_id ||
            ($exam->branch_id && $exam->branch_id !== Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to exam.');
        }

        $validator = Validator::make($request->all(), [
            'academic_year_id' => 'required|exists:academic_years,id',
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
            'exam_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'exam_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_marks' => 'required|numeric|min:0|max:999.99',
            'pass_marks' => 'required|numeric|min:0|max_marks',
            'weight' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:scheduled,draft,ongoing,completed,cancelled',
            'instructions' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate that the selected entities belong to the user's company/branch
        $this->validateEntityOwnership($request);

        // Check for exam scheduling conflicts (excluding current exam)
        $conflict = SchoolExam::forCompany(Auth::user()->company_id)
            ->forBranch(Auth::user()->branch_id)
            ->where('exam_date', $request->exam_date)
            ->where('class_id', $request->class_id)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($q) use ($request) {
                          $q->where('start_time', '<=', $request->start_time)
                            ->where('end_time', '>=', $request->end_time);
                      });
            })
            ->when($request->stream_id, function ($query) use ($request) {
                return $query->where('stream_id', $request->stream_id);
            })
            ->where('id', '!=', $exam->id)
            ->exists();

        if ($conflict) {
            return redirect()->back()
                ->withErrors(['exam_date' => 'There is a scheduling conflict with another exam for this class/stream at the selected time.'])
                ->withInput();
        }

        $exam->update([
            'academic_year_id' => $request->academic_year_id,
            'exam_type_id' => $request->exam_type_id,
            'subject_id' => $request->subject_id,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id,
            'exam_name' => $request->exam_name,
            'description' => $request->description,
            'exam_date' => $request->exam_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'max_marks' => $request->max_marks,
            'pass_marks' => $request->pass_marks,
            'weight' => $request->weight,
            'status' => $request->status,
            'instructions' => $request->instructions,
        ]);

        return redirect()->route('school.exams.index')
            ->with('success', 'Exam updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SchoolExam $exam): RedirectResponse
    {
        // Check if user has access to this exam
        if ($exam->company_id !== Auth::user()->company_id ||
            ($exam->branch_id && $exam->branch_id !== Auth::user()->branch_id)) {
            abort(403, 'Unauthorized access to exam.');
        }

        // Prevent deletion of completed exams
        if ($exam->status === 'completed') {
            return redirect()->back()
                ->with('error', 'Cannot delete a completed exam.');
        }

        $exam->delete();

        return redirect()->route('school.exams.index')
            ->with('success', 'Exam deleted successfully.');
    }

    /**
     * Update exam status.
     */
    public function updateStatus(Request $request, SchoolExam $exam): JsonResponse
    {
        // Check if user has access to this exam
        if ($exam->company_id !== Auth::user()->company_id ||
            ($exam->branch_id && $exam->branch_id !== Auth::user()->branch_id)) {
            return response()->json(['error' => 'Unauthorized access to exam.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:scheduled,draft,ongoing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid status provided.'], 422);
        }

        $exam->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Exam status updated successfully.',
            'status' => $exam->status,
        ]);
    }

    /**
     * Get streams for a specific class (AJAX endpoint).
     */
    public function getStreams(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid class ID.'], 422);
        }

        $streams = Stream::forCompany(Auth::user()->company_id)
            ->forBranch(Auth::user()->branch_id)
            ->where('class_id', $request->class_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['streams' => $streams]);
    }

    /**
     * Validate that selected entities belong to user's company/branch.
     */
    private function validateEntityOwnership(Request $request): void
    {
        $companyId = Auth::user()->company_id;
        $branchId = Auth::user()->branch_id;

        // Validate academic year
        $academicYear = AcademicYear::forCompany($companyId)
            ->forBranch($branchId)
            ->find($request->academic_year_id);
        if (!$academicYear) {
            throw new \Exception('Invalid academic year selected.');
        }

        // Validate exam type
        $examType = SchoolExamType::forCompany($companyId)
            ->forBranch($branchId)
            ->find($request->exam_type_id);
        if (!$examType) {
            throw new \Exception('Invalid exam type selected.');
        }

        // Validate subject
        $subject = Subject::forCompany($companyId)
            ->forBranch($branchId)
            ->find($request->subject_id);
        if (!$subject) {
            throw new \Exception('Invalid subject selected.');
        }

        // Validate class
        $class = Classe::forCompany($companyId)
            ->forBranch($branchId)
            ->find($request->class_id);
        if (!$class) {
            throw new \Exception('Invalid class selected.');
        }

        // Validate stream if provided
        if ($request->stream_id) {
            $stream = Stream::forCompany($companyId)
                ->forBranch($branchId)
                ->where('class_id', $request->class_id)
                ->find($request->stream_id);
            if (!$stream) {
                throw new \Exception('Invalid stream selected for the chosen class.');
            }
        }
    }
}
