<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\Assignment;
use App\Models\School\AssignmentSubmission;
use App\Models\School\AssignmentClass;
use App\Models\School\AcademicYear;
use App\Models\School\Classe;
use App\Models\School\Subject;
use App\Models\School\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssignmentCompletionRateExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AssignmentReportsController extends Controller
{
    /**
     * Get current active academic year
     */
    private function getCurrentAcademicYear($companyId, $branchId)
    {
        return AcademicYear::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_current', true)
            ->first();
    }

    /**
     * Assignment Completion Rate Report
     */
    public function completionRate(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

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

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getCompletionRateData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportCompletionRateExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportCompletionRatePdf($request);
        }

        return view('school.reports.assignments.completion-rate', compact('academicYears', 'classes', 'subjects', 'currentAcademicYear'));
    }

    /**
     * Get completion rate data for DataTables
     */
    private function getCompletionRateData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get assignments with filters
        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream', 'submissions'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

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

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();

        $data = [];
        foreach ($assignments as $index => $assignment) {
            // Get all students assigned to this assignment
            $studentIds = [];
            foreach ($assignment->assignmentClasses as $assignmentClass) {
                $studentsQuery = Student::where('company_id', $companyId)
                    ->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    })
                    ->where('class_id', $assignmentClass->class_id)
                    ->where('academic_year_id', $assignment->academic_year_id)
                    ->where('status', 'active');

                if ($assignmentClass->stream_id) {
                    $studentsQuery->where('stream_id', $assignmentClass->stream_id);
                }

                $studentIds = array_merge($studentIds, $studentsQuery->pluck('id')->toArray());
            }

            $totalStudents = count(array_unique($studentIds));
            
            // Count completed submissions (submissions with status 'submitted', 'marked', or 'returned')
            $completedCount = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->whereIn('student_id', array_unique($studentIds))
                ->whereIn('status', ['submitted', 'marked', 'returned'])
                ->distinct()
                ->count('student_id');

            $pendingCount = $totalStudents - $completedCount;
            $completionRate = $totalStudents > 0 ? ($completedCount / $totalStudents) * 100 : 0;

            // Build class/stream string
            $classStreams = $assignment->assignmentClasses->map(function ($ac) {
                $class = $ac->classe ? $ac->classe->name : 'N/A';
                $stream = $ac->stream ? ' - ' . $ac->stream->name : '';
                return $class . $stream;
            })->unique()->implode(', ');

            $data[] = [
                'DT_RowIndex' => $index + 1,
                'assignment_id' => $assignment->assignment_id,
                'title' => $assignment->title,
                'class_stream' => $classStreams ?: 'N/A',
                'subject' => $assignment->subject ? $assignment->subject->name : 'N/A',
                'total_students' => $totalStudents,
                'completed' => $completedCount,
                'pending' => $pendingCount,
                'completion_rate' => number_format($completionRate, 2) . '%',
                'completion_rate_raw' => $completionRate,
                'due_date' => $assignment->due_date ? $assignment->due_date->format('M d, Y') : 'N/A',
            ];
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('completion_rate_badge', function ($row) {
                $rate = $row['completion_rate_raw'];
                if ($rate >= 80) {
                    $badge = 'success';
                } elseif ($rate >= 60) {
                    $badge = 'info';
                } elseif ($rate >= 40) {
                    $badge = 'warning';
                } else {
                    $badge = 'danger';
                }
                return '<span class="badge bg-' . $badge . '">' . $row['completion_rate'] . '</span>';
            })
            ->rawColumns(['completion_rate_badge'])
            ->make(true);
    }

    /**
     * Export completion rate to Excel
     */
    private function exportCompletionRateExcel(Request $request)
    {
        $data = $this->getCompletionRateDataForExport($request);
        return Excel::download(new AssignmentCompletionRateExport($data), 'assignment_completion_rate_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export completion rate to PDF
     */
    private function exportCompletionRatePdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getCompletionRateDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.completion-rate-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('assignment_completion_rate_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Get completion rate data for export
     */
    private function getCompletionRateDataForExport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream', 'submissions'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

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

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();
        $data = [];

        foreach ($assignments as $assignment) {
            $studentIds = [];
            foreach ($assignment->assignmentClasses as $assignmentClass) {
                $studentsQuery = Student::where('company_id', $companyId)
                    ->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    })
                    ->where('class_id', $assignmentClass->class_id)
                    ->where('academic_year_id', $assignment->academic_year_id)
                    ->where('status', 'active');

                if ($assignmentClass->stream_id) {
                    $studentsQuery->where('stream_id', $assignmentClass->stream_id);
                }

                $studentIds = array_merge($studentIds, $studentsQuery->pluck('id')->toArray());
            }

            $totalStudents = count(array_unique($studentIds));
            $completedCount = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->whereIn('student_id', array_unique($studentIds))
                ->whereIn('status', ['submitted', 'marked', 'returned'])
                ->distinct()
                ->count('student_id');

            $pendingCount = $totalStudents - $completedCount;
            $completionRate = $totalStudents > 0 ? ($completedCount / $totalStudents) * 100 : 0;

            $classStreams = $assignment->assignmentClasses->map(function ($ac) {
                $class = $ac->classe ? $ac->classe->name : 'N/A';
                $stream = $ac->stream ? ' - ' . $ac->stream->name : '';
                return $class . $stream;
            })->unique()->implode(', ');

            $data[] = [
                'assignment_id' => $assignment->assignment_id,
                'title' => $assignment->title,
                'academic_year' => $assignment->academicYear ? $assignment->academicYear->year_name : 'N/A',
                'subject' => $assignment->subject ? $assignment->subject->name : 'N/A',
                'class_stream' => $classStreams ?: 'N/A',
                'due_date' => $assignment->due_date ? $assignment->due_date->format('M d, Y') : 'N/A',
                'total_students' => $totalStudents,
                'completed' => $completedCount,
                'pending' => $pendingCount,
                'completion_rate' => number_format($completionRate, 2),
            ];
        }

        return $data;
    }

    /**
     * Get filters for export
     */
    private function getFiltersForExport(Request $request)
    {
        $filters = [];

        if ($request->filled('academic_year_id')) {
            $year = AcademicYear::find($request->academic_year_id);
            $filters['Academic Year'] = $year ? $year->year_name : 'N/A';
        }

        if ($request->filled('class_id')) {
            $class = Classe::find($request->class_id);
            $filters['Class'] = $class ? $class->name : 'N/A';
        }

        if ($request->filled('subject_id')) {
            $subject = Subject::find($request->subject_id);
            $filters['Subject'] = $subject ? $subject->name : 'N/A';
        }

        if ($request->filled('date_from')) {
            $filters['Date From'] = \Carbon\Carbon::parse($request->date_from)->format('M d, Y');
        }

        if ($request->filled('date_to')) {
            $filters['Date To'] = \Carbon\Carbon::parse($request->date_to)->format('M d, Y');
        }

        return $filters;
    }

    /**
     * Late Submissions Report
     */
    public function lateSubmissions(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

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

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getLateSubmissionsData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportLateSubmissionsExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportLateSubmissionsPdf($request);
        }

        return view('school.reports.assignments.late-submissions', compact('academicYears', 'classes', 'subjects', 'currentAcademicYear'));
    }

    /**
     * Get late submissions data for DataTables
     */
    private function getLateSubmissionsData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get late submissions
        $query = AssignmentSubmission::with([
            'assignment.academicYear',
            'assignment.subject',
            'assignment.assignmentClasses.classe',
            'assignment.assignmentClasses.stream',
            'student.class',
            'student.stream'
        ])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_late', true)
            ->whereNotNull('submitted_at');

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->whereHas('assignment', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('assignment', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('submitted_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('submitted_at', '<=', $request->date_to . ' 23:59:59');
        }

        $submissions = $query->get();

        $data = [];
        foreach ($submissions as $index => $submission) {
            $assignment = $submission->assignment;
            $student = $submission->student;

            if (!$assignment || !$student) {
                continue;
            }

            // Calculate days late
            $dueDate = $assignment->due_date;
            $submittedDate = $submission->submitted_at;
            $daysLate = 0;

            if ($dueDate && $submittedDate) {
                $dueDateTime = \Carbon\Carbon::parse($dueDate);
                if ($assignment->due_time) {
                    $timeParts = explode(':', $assignment->due_time);
                    if (count($timeParts) >= 2) {
                        $dueDateTime->setTime((int)$timeParts[0], (int)$timeParts[1]);
                    }
                } else {
                    $dueDateTime->setTime(23, 59, 59);
                }
                $submittedDateTime = \Carbon\Carbon::parse($submittedDate);
                $daysLate = max(0, $dueDateTime->diffInDays($submittedDateTime, false));
            }

            // Build class/stream string
            $classStream = '';
            if ($student->class) {
                $classStream = $student->class->name;
                if ($student->stream) {
                    $classStream .= ' - ' . $student->stream->name;
                }
            }

            $data[] = [
                'DT_RowIndex' => $index + 1,
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'admission_no' => $student->admission_no ?? 'N/A',
                'assignment_id' => $assignment->assignment_id,
                'assignment_title' => $assignment->title,
                'subject' => $assignment->subject ? $assignment->subject->name : 'N/A',
                'class_stream' => $classStream ?: 'N/A',
                'due_date' => $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : 'N/A',
                'submitted_date' => $submittedDate ? \Carbon\Carbon::parse($submittedDate)->format('M d, Y H:i') : 'N/A',
                'days_late' => $daysLate,
                'days_late_raw' => $daysLate,
            ];
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('days_late_badge', function ($row) {
                $days = $row['days_late_raw'];
                if ($days >= 7) {
                    $badge = 'danger';
                } elseif ($days >= 3) {
                    $badge = 'warning';
                } else {
                    $badge = 'info';
                }
                return '<span class="badge bg-' . $badge . '">' . $days . ' day(s)</span>';
            })
            ->rawColumns(['days_late_badge'])
            ->make(true);
    }

    /**
     * Export late submissions to Excel
     */
    private function exportLateSubmissionsExcel(Request $request)
    {
        $data = $this->getLateSubmissionsDataForExport($request);
        return Excel::download(new \App\Exports\LateSubmissionsExport($data), 'late_submissions_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export late submissions to PDF
     */
    private function exportLateSubmissionsPdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getLateSubmissionsDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.late-submissions-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('late_submissions_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Get late submissions data for export
     */
    private function getLateSubmissionsDataForExport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = AssignmentSubmission::with([
            'assignment.academicYear',
            'assignment.subject',
            'student.class',
            'student.stream'
        ])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_late', true)
            ->whereNotNull('submitted_at');

        if ($request->filled('academic_year_id')) {
            $query->whereHas('assignment', function ($q) use ($request) {
                $q->where('academic_year_id', $request->academic_year_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('assignment', function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('submitted_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('submitted_at', '<=', $request->date_to . ' 23:59:59');
        }

        $submissions = $query->get();
        $data = [];

        foreach ($submissions as $submission) {
            $assignment = $submission->assignment;
            $student = $submission->student;

            if (!$assignment || !$student) {
                continue;
            }

            $dueDate = $assignment->due_date;
            $submittedDate = $submission->submitted_at;
            $daysLate = 0;

            if ($dueDate && $submittedDate) {
                $dueDateTime = \Carbon\Carbon::parse($dueDate);
                if ($assignment->due_time) {
                    $timeParts = explode(':', $assignment->due_time);
                    if (count($timeParts) >= 2) {
                        $dueDateTime->setTime((int)$timeParts[0], (int)$timeParts[1]);
                    }
                } else {
                    $dueDateTime->setTime(23, 59, 59);
                }
                $submittedDateTime = \Carbon\Carbon::parse($submittedDate);
                $daysLate = max(0, $dueDateTime->diffInDays($submittedDateTime, false));
            }

            $classStream = '';
            if ($student->class) {
                $classStream = $student->class->name;
                if ($student->stream) {
                    $classStream .= ' - ' . $student->stream->name;
                }
            }

            $data[] = [
                'admission_no' => $student->admission_no ?? 'N/A',
                'student_name' => $student->first_name . ' ' . $student->last_name,
                'assignment_id' => $assignment->assignment_id,
                'assignment_title' => $assignment->title,
                'academic_year' => $assignment->academicYear ? $assignment->academicYear->year_name : 'N/A',
                'subject' => $assignment->subject ? $assignment->subject->name : 'N/A',
                'class_stream' => $classStream ?: 'N/A',
                'due_date' => $dueDate ? \Carbon\Carbon::parse($dueDate)->format('M d, Y') : 'N/A',
                'submitted_date' => $submittedDate ? \Carbon\Carbon::parse($submittedDate)->format('M d, Y H:i') : 'N/A',
                'days_late' => $daysLate,
            ];
        }

        return $data;
    }

    /**
     * Average Marks per Assignment Report
     */
    public function averageMarks(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getAverageMarksData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportAverageMarksExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportAverageMarksPdf($request);
        }

        return view('school.reports.assignments.average-marks', compact('academicYears', 'subjects', 'classes', 'currentAcademicYear'));
    }

    /**
     * Get average marks data for DataTables
     */
    private function getAverageMarksData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get assignments with filters
        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream', 'submissions'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->whereNotNull('total_marks')
            ->where('total_marks', '>', 0);

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();

        $data = [];
        foreach ($assignments as $index => $assignment) {
            // Get submissions with marks
            $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->whereNotNull('marks_obtained')
                ->where('status', '!=', 'not_started')
                ->get();

            if ($submissions->isEmpty()) {
                continue; // Skip assignments with no marked submissions
            }

            // Calculate statistics
            $marks = $submissions->pluck('marks_obtained')->filter()->toArray();
            $totalMarks = $assignment->total_marks;
            $averageMarks = count($marks) > 0 ? array_sum($marks) / count($marks) : 0;
            $highestMarks = !empty($marks) ? max($marks) : 0;
            $lowestMarks = !empty($marks) ? min($marks) : 0;
            $averagePercentage = $totalMarks > 0 ? ($averageMarks / $totalMarks) * 100 : 0;
            $submittedCount = count($marks);

            // Build class/stream string
            $classStreams = $assignment->assignmentClasses->map(function ($ac) {
                $class = $ac->classe ? $ac->classe->name : 'N/A';
                $stream = $ac->stream ? ' - ' . $ac->stream->name : '';
                return $class . $stream;
            })->unique()->implode(', ');

            $data[] = [
                'DT_RowIndex' => $index + 1,
                'assignment_id' => $assignment->assignment_id,
                'title' => $assignment->title,
                'subject' => $assignment->subject ? $assignment->subject->name : 'N/A',
                'class_stream' => $classStreams ?: 'N/A',
                'total_marks' => number_format($totalMarks, 2),
                'total_marks_raw' => $totalMarks,
                'average_marks' => number_format($averageMarks, 2),
                'average_marks_raw' => $averageMarks,
                'average_percentage' => number_format($averagePercentage, 2) . '%',
                'average_percentage_raw' => $averagePercentage,
                'highest_marks' => number_format($highestMarks, 2),
                'highest_marks_raw' => $highestMarks,
                'lowest_marks' => number_format($lowestMarks, 2),
                'lowest_marks_raw' => $lowestMarks,
                'submitted_count' => $submittedCount,
            ];
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('average_percentage_badge', function ($row) {
                $percentage = $row['average_percentage_raw'];
                if ($percentage >= 80) {
                    $badge = 'success';
                } elseif ($percentage >= 60) {
                    $badge = 'info';
                } elseif ($percentage >= 40) {
                    $badge = 'warning';
                } else {
                    $badge = 'danger';
                }
                return '<span class="badge bg-' . $badge . '">' . $row['average_percentage'] . '</span>';
            })
            ->rawColumns(['average_percentage_badge'])
            ->make(true);
    }

    /**
     * Export average marks to Excel
     */
    private function exportAverageMarksExcel(Request $request)
    {
        $data = $this->getAverageMarksDataForExport($request);
        return Excel::download(new \App\Exports\AverageMarksExport($data), 'average_marks_per_assignment_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export average marks to PDF
     */
    private function exportAverageMarksPdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getAverageMarksDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.average-marks-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('average_marks_per_assignment_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Get average marks data for export
     */
    private function getAverageMarksDataForExport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->whereNotNull('total_marks')
            ->where('total_marks', '>', 0);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();
        $data = [];

        foreach ($assignments as $assignment) {
            $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->whereNotNull('marks_obtained')
                ->where('status', '!=', 'not_started')
                ->get();

            if ($submissions->isEmpty()) {
                continue;
            }

            $marks = $submissions->pluck('marks_obtained')->filter()->toArray();
            $totalMarks = $assignment->total_marks;
            $averageMarks = count($marks) > 0 ? array_sum($marks) / count($marks) : 0;
            $highestMarks = !empty($marks) ? max($marks) : 0;
            $lowestMarks = !empty($marks) ? min($marks) : 0;
            $averagePercentage = $totalMarks > 0 ? ($averageMarks / $totalMarks) * 100 : 0;
            $submittedCount = count($marks);

            $classStreams = $assignment->assignmentClasses->map(function ($ac) {
                $class = $ac->classe ? $ac->classe->name : 'N/A';
                $stream = $ac->stream ? ' - ' . $ac->stream->name : '';
                return $class . $stream;
            })->unique()->implode(', ');

            $data[] = [
                'assignment_id' => $assignment->assignment_id,
                'title' => $assignment->title,
                'academic_year' => $assignment->academicYear ? $assignment->academicYear->year_name : 'N/A',
                'subject' => $assignment->subject ? $assignment->subject->name : 'N/A',
                'class_stream' => $classStreams ?: 'N/A',
                'total_marks' => number_format($totalMarks, 2),
                'average_marks' => number_format($averageMarks, 2),
                'average_percentage' => number_format($averagePercentage, 2),
                'highest_marks' => number_format($highestMarks, 2),
                'lowest_marks' => number_format($lowestMarks, 2),
                'submitted_count' => $submittedCount,
            ];
        }

        return $data;
    }

    /**
     * Weak Topic Analysis Report
     */
    public function weakTopicAnalysis(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getWeakTopicAnalysisData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportWeakTopicAnalysisExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportWeakTopicAnalysisPdf($request);
        }

        return view('school.reports.assignments.weak-topic-analysis', compact('academicYears', 'subjects', 'classes', 'currentAcademicYear'));
    }

    /**
     * Get weak topic analysis data for DataTables
     */
    private function getWeakTopicAnalysisData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get assignments with filters
        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream', 'submissions'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->whereNotNull('total_marks')
            ->where('total_marks', '>', 0);

        // Apply filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();

        $data = [];
        foreach ($assignments as $index => $assignment) {
            // Get submissions with marks
            $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->whereNotNull('marks_obtained')
                ->where('status', '!=', 'not_started')
                ->get();

            if ($submissions->isEmpty()) {
                continue; // Skip assignments with no marked submissions
            }

            // Calculate statistics
            $marks = $submissions->pluck('marks_obtained')->filter()->toArray();
            $totalMarks = $assignment->total_marks;
            $passingMarks = $assignment->passing_marks ?? ($totalMarks * 0.5); // Default to 50% if not set
            $averageMarks = count($marks) > 0 ? array_sum($marks) / count($marks) : 0;
            $averagePercentage = $totalMarks > 0 ? ($averageMarks / $totalMarks) * 100 : 0;
            
            // Calculate pass rate (students who scored >= passing marks)
            $passedCount = 0;
            $strugglingCount = 0;
            foreach ($marks as $mark) {
                if ($mark >= $passingMarks) {
                    $passedCount++;
                } else {
                    $strugglingCount++;
                }
            }
            
            $totalStudents = count($marks);
            $passRate = $totalStudents > 0 ? ($passedCount / $totalStudents) * 100 : 0;

            // Only include assignments with low performance (average < 60% or pass rate < 50%)
            if ($averagePercentage >= 60 && $passRate >= 50) {
                continue; // Skip strong topics
            }

            // Build class/stream string
            $classStreams = $assignment->assignmentClasses->map(function ($ac) {
                $class = $ac->classe ? $ac->classe->name : 'N/A';
                $stream = $ac->stream ? ' - ' . $ac->stream->name : '';
                return $class . $stream;
            })->unique()->implode(', ');

            $data[] = [
                'DT_RowIndex' => $index + 1,
                'assignment_id' => $assignment->assignment_id,
                'title' => $assignment->title,
                'subject' => $assignment->subject ? $assignment->subject->name : 'N/A',
                'class_stream' => $classStreams ?: 'N/A',
                'average_score' => number_format($averagePercentage, 2) . '%',
                'average_score_raw' => $averagePercentage,
                'pass_rate' => number_format($passRate, 2) . '%',
                'pass_rate_raw' => $passRate,
                'struggling_count' => $strugglingCount,
                'total_students' => $totalStudents,
                'average_marks' => number_format($averageMarks, 2),
            ];
        }

        // Sort by average score (lowest first) to show weakest topics first
        usort($data, function($a, $b) {
            return $a['average_score_raw'] <=> $b['average_score_raw'];
        });

        // Re-index after sorting
        foreach ($data as $index => &$row) {
            $row['DT_RowIndex'] = $index + 1;
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('average_score_badge', function ($row) {
                $percentage = $row['average_score_raw'];
                if ($percentage >= 60) {
                    $badge = 'success';
                } elseif ($percentage >= 40) {
                    $badge = 'warning';
                } else {
                    $badge = 'danger';
                }
                return '<span class="badge bg-' . $badge . '">' . $row['average_score'] . '</span>';
            })
            ->addColumn('pass_rate_badge', function ($row) {
                $rate = $row['pass_rate_raw'];
                if ($rate >= 70) {
                    $badge = 'success';
                } elseif ($rate >= 50) {
                    $badge = 'warning';
                } else {
                    $badge = 'danger';
                }
                return '<span class="badge bg-' . $badge . '">' . $row['pass_rate'] . '</span>';
            })
            ->rawColumns(['average_score_badge', 'pass_rate_badge'])
            ->make(true);
    }

    /**
     * Export weak topic analysis to Excel
     */
    private function exportWeakTopicAnalysisExcel(Request $request)
    {
        $data = $this->getWeakTopicAnalysisDataForExport($request);
        return Excel::download(new \App\Exports\WeakTopicAnalysisExport($data), 'weak_topic_analysis_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export weak topic analysis to PDF
     */
    private function exportWeakTopicAnalysisPdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getWeakTopicAnalysisDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.weak-topic-analysis-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('weak_topic_analysis_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Get weak topic analysis data for export
     */
    private function getWeakTopicAnalysisDataForExport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->whereNotNull('total_marks')
            ->where('total_marks', '>', 0);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();
        $data = [];

        foreach ($assignments as $assignment) {
            $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->whereNotNull('marks_obtained')
                ->where('status', '!=', 'not_started')
                ->get();

            if ($submissions->isEmpty()) {
                continue;
            }

            $marks = $submissions->pluck('marks_obtained')->filter()->toArray();
            $totalMarks = $assignment->total_marks;
            $passingMarks = $assignment->passing_marks ?? ($totalMarks * 0.5);
            $averageMarks = count($marks) > 0 ? array_sum($marks) / count($marks) : 0;
            $averagePercentage = $totalMarks > 0 ? ($averageMarks / $totalMarks) * 100 : 0;
            
            $passedCount = 0;
            $strugglingCount = 0;
            foreach ($marks as $mark) {
                if ($mark >= $passingMarks) {
                    $passedCount++;
                } else {
                    $strugglingCount++;
                }
            }
            
            $totalStudents = count($marks);
            $passRate = $totalStudents > 0 ? ($passedCount / $totalStudents) * 100 : 0;

            // Only include weak topics
            if ($averagePercentage >= 60 && $passRate >= 50) {
                continue;
            }

            $classStreams = $assignment->assignmentClasses->map(function ($ac) {
                $class = $ac->classe ? $ac->classe->name : 'N/A';
                $stream = $ac->stream ? ' - ' . $ac->stream->name : '';
                return $class . $stream;
            })->unique()->implode(', ');

            $data[] = [
                'assignment_id' => $assignment->assignment_id,
                'title' => $assignment->title,
                'academic_year' => $assignment->academicYear ? $assignment->academicYear->year_name : 'N/A',
                'subject' => $assignment->subject ? $assignment->subject->name : 'N/A',
                'class_stream' => $classStreams ?: 'N/A',
                'average_score' => number_format($averagePercentage, 2),
                'pass_rate' => number_format($passRate, 2),
                'struggling_count' => $strugglingCount,
                'total_students' => $totalStudents,
                'average_marks' => number_format($averageMarks, 2),
                'total_marks' => number_format($totalMarks, 2),
            ];
        }

        // Sort by average score (lowest first)
        usort($data, function($a, $b) {
            return (float)$a['average_score'] <=> (float)$b['average_score'];
        });

        return $data;
    }

    /**
     * Subject-Wise Homework Performance Report
     */
    public function subjectHomeworkPerformance(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getSubjectHomeworkPerformanceData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportSubjectHomeworkPerformanceExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportSubjectHomeworkPerformancePdf($request);
        }

        return view('school.reports.assignments.subject-homework-performance', compact('academicYears', 'subjects', 'classes', 'currentAcademicYear'));
    }

    /**
     * Get subject-wise homework performance data for DataTables
     */
    private function getSubjectHomeworkPerformanceData(Request $request)
    {
        try {
            $companyId = Auth::user()->company_id;
            $branchId = session('branch_id') ?: Auth::user()->branch_id;

            // Get homework assignments filtered by type = 'homework'
            $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses', 'submissions'])
                ->where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                })
                ->where('type', 'homework')
                ->whereNotNull('total_marks')
                ->where('total_marks', '>', 0);

            // Apply filters
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }

            if ($request->filled('class_id')) {
                $query->whereHas('assignmentClasses', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            if ($request->filled('date_from')) {
                $query->where('due_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('due_date', '<=', $request->date_to);
            }

            $assignments = $query->get();

            // Group by subject
            $subjectData = [];
            
            foreach ($assignments as $assignment) {
                if (!$assignment->subject_id) {
                    continue;
                }

                $subjectId = $assignment->subject_id;
                $subjectName = $assignment->subject ? $assignment->subject->name : 'N/A';
                
                if (!isset($subjectData[$subjectId])) {
                    $subjectData[$subjectId] = [
                        'subject_id' => $subjectId,
                        'subject_name' => $subjectName,
                        'total_homework' => 0,
                        'total_submissions' => 0,
                        'total_marks' => 0,
                        'total_obtained' => 0,
                        'completed_count' => 0,
                        'expected_submissions' => 0, // Track total expected submissions
                        'student_scores' => [], // Store student_id => [total_marks, total_obtained, count]
                    ];
                }
                
                $subjectData[$subjectId]['total_homework']++;
                
                // Get students assigned to this assignment
                $studentIds = [];
                foreach ($assignment->assignmentClasses as $assignmentClass) {
                    if (!$assignmentClass->class_id || !$assignment->academic_year_id) {
                        continue;
                    }
                    
                    $studentsQuery = Student::where('company_id', $companyId)
                        ->where(function ($q) use ($branchId) {
                            $q->where('branch_id', $branchId)
                              ->orWhereNull('branch_id');
                        })
                        ->where('class_id', $assignmentClass->class_id)
                        ->where('academic_year_id', $assignment->academic_year_id)
                        ->where('status', 'active');
                    
                    if ($assignmentClass->stream_id) {
                        $studentsQuery->where('stream_id', $assignmentClass->stream_id);
                    }
                    
                    $studentIds = array_merge($studentIds, $studentsQuery->pluck('id')->toArray());
                }
                
                $totalStudents = count(array_unique($studentIds));
                $subjectData[$subjectId]['expected_submissions'] += $totalStudents;
                
                // Get all submissions for this assignment with marks
                $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->whereNotNull('marks_obtained')
                    ->where('status', '!=', 'not_started')
                    ->with('student')
                    ->get();
                
                foreach ($submissions as $submission) {
                    if (!$submission->student_id) {
                        continue;
                    }

                    $subjectData[$subjectId]['total_submissions']++;
                    $subjectData[$subjectId]['total_marks'] += $assignment->total_marks;
                    $subjectData[$subjectId]['total_obtained'] += $submission->marks_obtained;
                    
                    // Track student performance
                    $studentId = $submission->student_id;
                    if (!isset($subjectData[$subjectId]['student_scores'][$studentId])) {
                        $subjectData[$subjectId]['student_scores'][$studentId] = [
                            'total_marks' => 0,
                            'total_obtained' => 0,
                            'count' => 0,
                            'student_name' => $submission->student ? ($submission->student->first_name . ' ' . $submission->student->last_name) : 'N/A'
                        ];
                    }
                    $subjectData[$subjectId]['student_scores'][$studentId]['total_marks'] += $assignment->total_marks;
                    $subjectData[$subjectId]['student_scores'][$studentId]['total_obtained'] += $submission->marks_obtained;
                    $subjectData[$subjectId]['student_scores'][$studentId]['count']++;
                }
                
                // Count completed (submitted) students
                $completed = AssignmentSubmission::where('assignment_id', $assignment->id)
                    ->whereIn('student_id', array_unique($studentIds))
                    ->whereIn('status', ['submitted', 'marked', 'returned'])
                    ->distinct()
                    ->count('student_id');
                
                $subjectData[$subjectId]['completed_count'] += $completed;
            }

            // Convert to array format for DataTables
            $data = [];
            foreach ($subjectData as $index => $subject) {
                // Calculate average score
                $averageScore = 0;
                if ($subject['total_submissions'] > 0 && $subject['total_marks'] > 0) {
                    $averageScore = ($subject['total_obtained'] / $subject['total_marks']) * 100;
                }
                
                // Calculate completion rate
                $completionRate = 0;
                if (isset($subject['expected_submissions']) && $subject['expected_submissions'] > 0) {
                    $completionRate = ($subject['completed_count'] / $subject['expected_submissions']) * 100;
                }
                
                // Find top performer
                $topPerformer = 'N/A';
                $topScore = 0;
                foreach ($subject['student_scores'] as $studentId => $studentData) {
                    if ($studentData['count'] > 0 && $studentData['total_marks'] > 0) {
                        $studentAvg = ($studentData['total_obtained'] / $studentData['total_marks']) * 100;
                        if ($studentAvg > $topScore) {
                            $topScore = $studentAvg;
                            $topPerformer = $studentData['student_name'] . ' (' . number_format($studentAvg, 1) . '%)';
                        }
                    }
                }
                
                // Find needs improvement (students with average < 50%)
                $needsImprovement = [];
                foreach ($subject['student_scores'] as $studentId => $studentData) {
                    if ($studentData['count'] > 0 && $studentData['total_marks'] > 0) {
                        $studentAvg = ($studentData['total_obtained'] / $studentData['total_marks']) * 100;
                        if ($studentAvg < 50) {
                            $needsImprovement[] = $studentData['student_name'];
                        }
                    }
                }
                $needsImprovementStr = count($needsImprovement) > 0 
                    ? implode(', ', array_slice($needsImprovement, 0, 3)) . (count($needsImprovement) > 3 ? '...' : '')
                    : 'None';

                $data[] = [
                    'DT_RowIndex' => $index + 1,
                    'subject' => $subject['subject_name'],
                    'total_homework' => $subject['total_homework'],
                    'average_score' => number_format($averageScore, 2) . '%',
                    'average_score_raw' => $averageScore,
                    'completion_rate' => number_format($completionRate, 2) . '%',
                    'completion_rate_raw' => $completionRate,
                    'top_performer' => $topPerformer,
                    'needs_improvement' => $needsImprovementStr,
                    'needs_improvement_count' => count($needsImprovement),
                ];
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('average_score_badge', function ($row) {
                    $percentage = $row['average_score_raw'] ?? 0;
                    if ($percentage >= 80) {
                        $badge = 'success';
                    } elseif ($percentage >= 60) {
                        $badge = 'info';
                    } elseif ($percentage >= 40) {
                        $badge = 'warning';
                    } else {
                        $badge = 'danger';
                    }
                    return '<span class="badge bg-' . $badge . '">' . $row['average_score'] . '</span>';
                })
                ->addColumn('completion_rate_badge', function ($row) {
                    $rate = $row['completion_rate_raw'] ?? 0;
                    if ($rate >= 80) {
                        $badge = 'success';
                    } elseif ($rate >= 60) {
                        $badge = 'info';
                    } elseif ($rate >= 40) {
                        $badge = 'warning';
                    } else {
                        $badge = 'danger';
                    }
                    return '<span class="badge bg-' . $badge . '">' . $row['completion_rate'] . '</span>';
                })
                ->rawColumns(['average_score_badge', 'completion_rate_badge'])
                ->make(true);
        } catch (\Exception $e) {
            \Log::error('Error in getSubjectHomeworkPerformanceData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data. Please try again.'
            ], 500);
        }
    }

    /**
     * Export subject homework performance to Excel
     */
    private function exportSubjectHomeworkPerformanceExcel(Request $request)
    {
        $data = $this->getSubjectHomeworkPerformanceDataForExport($request);
        return Excel::download(new \App\Exports\SubjectHomeworkPerformanceExport($data), 'subject_homework_performance_' . date('Y-m-d') . '.xlsx');
    }

    /**
     * Export subject homework performance to PDF
     */
    private function exportSubjectHomeworkPerformancePdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getSubjectHomeworkPerformanceDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.subject-homework-performance-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('subject_homework_performance_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Get subject homework performance data for export
     */
    private function getSubjectHomeworkPerformanceDataForExport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'submissions.student'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('type', 'homework')
            ->whereNotNull('total_marks')
            ->where('total_marks', '>', 0);

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();

        $subjectData = [];
        
        foreach ($assignments as $assignment) {
            if (!$assignment->subject_id) {
                continue;
            }

            $subjectId = $assignment->subject_id;
            $subjectName = $assignment->subject ? $assignment->subject->name : 'N/A';
            
            if (!isset($subjectData[$subjectId])) {
                $subjectData[$subjectId] = [
                    'subject_name' => $subjectName,
                    'academic_year' => $assignment->academicYear ? $assignment->academicYear->year_name : 'N/A',
                    'total_homework' => 0,
                    'total_submissions' => 0,
                    'total_marks' => 0,
                    'total_obtained' => 0,
                    'completed_count' => 0,
                    'expected_submissions' => 0,
                    'student_scores' => [],
                ];
            }
            
            $subjectData[$subjectId]['total_homework']++;
            
            // Get students assigned to this assignment
            $studentIds = [];
            foreach ($assignment->assignmentClasses as $assignmentClass) {
                if (!$assignmentClass->class_id || !$assignment->academic_year_id) {
                    continue;
                }
                
                $studentsQuery = Student::where('company_id', $companyId)
                    ->where(function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                          ->orWhereNull('branch_id');
                    })
                    ->where('class_id', $assignmentClass->class_id)
                    ->where('academic_year_id', $assignment->academic_year_id)
                    ->where('status', 'active');
                
                if ($assignmentClass->stream_id) {
                    $studentsQuery->where('stream_id', $assignmentClass->stream_id);
                }
                
                $studentIds = array_merge($studentIds, $studentsQuery->pluck('id')->toArray());
            }
            
            $totalStudents = count(array_unique($studentIds));
            $subjectData[$subjectId]['expected_submissions'] += $totalStudents;
            
            $submissions = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->whereNotNull('marks_obtained')
                ->where('status', '!=', 'not_started')
                ->with('student')
                ->get();
            
            foreach ($submissions as $submission) {
                if (!$submission->student_id) {
                    continue;
                }

                $subjectData[$subjectId]['total_submissions']++;
                $subjectData[$subjectId]['total_marks'] += $assignment->total_marks;
                $subjectData[$subjectId]['total_obtained'] += $submission->marks_obtained;
                
                $studentId = $submission->student_id;
                if (!isset($subjectData[$subjectId]['student_scores'][$studentId])) {
                    $subjectData[$subjectId]['student_scores'][$studentId] = [
                        'total_marks' => 0,
                        'total_obtained' => 0,
                        'count' => 0,
                        'student_name' => $submission->student ? ($submission->student->first_name . ' ' . $submission->student->last_name) : 'N/A'
                    ];
                }
                $subjectData[$subjectId]['student_scores'][$studentId]['total_marks'] += $assignment->total_marks;
                $subjectData[$subjectId]['student_scores'][$studentId]['total_obtained'] += $submission->marks_obtained;
                $subjectData[$subjectId]['student_scores'][$studentId]['count']++;
            }
            
            $completed = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->whereIn('student_id', array_unique($studentIds))
                ->whereIn('status', ['submitted', 'marked', 'returned'])
                ->distinct()
                ->count('student_id');
            
            $subjectData[$subjectId]['completed_count'] += $completed;
        }

        $data = [];
        foreach ($subjectData as $subject) {
            $averageScore = 0;
            if ($subject['total_submissions'] > 0 && $subject['total_marks'] > 0) {
                $averageScore = ($subject['total_obtained'] / $subject['total_marks']) * 100;
            }
            
            $completionRate = 0;
            if (isset($subject['expected_submissions']) && $subject['expected_submissions'] > 0) {
                $completionRate = ($subject['completed_count'] / $subject['expected_submissions']) * 100;
            }
            
            $topPerformer = 'N/A';
            $topScore = 0;
            foreach ($subject['student_scores'] as $studentData) {
                if ($studentData['count'] > 0 && $studentData['total_marks'] > 0) {
                    $studentAvg = ($studentData['total_obtained'] / $studentData['total_marks']) * 100;
                    if ($studentAvg > $topScore) {
                        $topScore = $studentAvg;
                        $topPerformer = $studentData['student_name'] . ' (' . number_format($studentAvg, 1) . '%)';
                    }
                }
            }
            
            $needsImprovement = [];
            foreach ($subject['student_scores'] as $studentData) {
                if ($studentData['count'] > 0 && $studentData['total_marks'] > 0) {
                    $studentAvg = ($studentData['total_obtained'] / $studentData['total_marks']) * 100;
                    if ($studentAvg < 50) {
                        $needsImprovement[] = $studentData['student_name'];
                    }
                }
            }
            $needsImprovementStr = count($needsImprovement) > 0 
                ? implode(', ', array_slice($needsImprovement, 0, 5))
                : 'None';

            $data[] = [
                'academic_year' => $subject['academic_year'],
                'subject' => $subject['subject_name'],
                'total_homework' => $subject['total_homework'],
                'average_score' => number_format($averageScore, 2),
                'completion_rate' => number_format($completionRate, 2),
                'top_performer' => $topPerformer,
                'needs_improvement' => $needsImprovementStr,
                'needs_improvement_count' => count($needsImprovement),
            ];
        }

        return $data;
    }

    /**
     * Improvement Tracking Report
     */
    public function improvementTracking(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

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

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getImprovementTrackingData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportImprovementTrackingExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportImprovementTrackingPdf($request);
        }

        return view('school.reports.assignments.improvement-tracking', compact('academicYears', 'classes', 'subjects', 'currentAcademicYear'));
    }

    /**
     * Teacher Assignment Frequency Report
     */
    public function teacherAssignmentFrequency(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

        $subjects = Subject::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getTeacherAssignmentFrequencyData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportTeacherAssignmentFrequencyExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportTeacherAssignmentFrequencyPdf($request);
        }

        return view('school.reports.assignments.teacher-assignment-frequency', compact('academicYears', 'subjects', 'currentAcademicYear'));
    }

    /**
     * Class Workload Balance Report
     */
    public function classWorkloadBalance(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getClassWorkloadBalanceData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportClassWorkloadBalanceExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportClassWorkloadBalancePdf($request);
        }

        return view('school.reports.assignments.class-workload-balance', compact('academicYears', 'classes', 'currentAcademicYear'));
    }

    /**
     * School-Wide Compliance Report
     */
    public function schoolCompliance(Request $request)
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

        $currentAcademicYear = $this->getCurrentAcademicYear($companyId, $branchId);

        $classes = Classe::where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Handle AJAX DataTables request
        if ($request->ajax()) {
            return $this->getSchoolComplianceData($request);
        }

        // Handle exports
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportSchoolComplianceExcel($request);
        }

        if ($request->has('export') && $request->export === 'pdf') {
            return $this->exportSchoolCompliancePdf($request);
        }

        return view('school.reports.assignments.school-compliance', compact('academicYears', 'classes', 'currentAcademicYear'));
    }

    // ==================== Improvement Tracking Report Methods ====================

    private function getImprovementTrackingData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream', 'submissions.student'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->whereNotNull('total_marks')
            ->where('total_marks', '>', 0);

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

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();

        // Group by student and calculate improvement
        $studentData = [];
        foreach ($assignments as $assignment) {
            foreach ($assignment->submissions as $submission) {
                $studentId = $submission->student_id;
                if (!isset($studentData[$studentId])) {
                    $studentData[$studentId] = [
                        'student_id' => $studentId,
                        'student_name' => $submission->student->first_name . ' ' . $submission->student->last_name,
                        'admission_number' => $submission->student->admission_number ?? 'N/A',
                        'class' => $submission->student->class->name ?? 'N/A',
                        'submissions' => [],
                        'first_score' => null,
                        'latest_score' => null,
                        'improvement' => 0,
                        'trend' => 'stable'
                    ];
                }

                if ($submission->marks_obtained !== null && $assignment->total_marks > 0) {
                    $percentage = ($submission->marks_obtained / $assignment->total_marks) * 100;
                    $studentData[$studentId]['submissions'][] = [
                        'date' => $submission->submitted_at,
                        'score' => $percentage,
                        'assignment' => $assignment->title
                    ];
                }
            }
        }

        // Calculate improvement for each student
        $data = [];
        foreach ($studentData as $student) {
            if (count($student['submissions']) < 2) {
                continue; // Need at least 2 submissions to show improvement
            }

            // Sort by date
            usort($student['submissions'], function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            $firstScore = $student['submissions'][0]['score'];
            $latestScore = $student['submissions'][count($student['submissions']) - 1]['score'];
            $improvement = $latestScore - $firstScore;

            $data[] = [
                'student_name' => $student['student_name'],
                'admission_number' => $student['admission_number'],
                'class' => $student['class'],
                'total_submissions' => count($student['submissions']),
                'first_score' => number_format($firstScore, 2),
                'latest_score' => number_format($latestScore, 2),
                'improvement' => number_format($improvement, 2),
                'trend' => $improvement > 0 ? 'improving' : ($improvement < 0 ? 'declining' : 'stable')
            ];
        }

        // Sort by improvement (highest first)
        usort($data, function($a, $b) {
            return (float)$b['improvement'] <=> (float)$a['improvement'];
        });

        return DataTables::of($data)->make(true);
    }

    private function exportImprovementTrackingExcel(Request $request)
    {
        $data = $this->getImprovementTrackingDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        return Excel::download(new \App\Exports\ImprovementTrackingExport($data, $filters), 
            'improvement_tracking_' . date('Y-m-d') . '.xlsx');
    }

    private function exportImprovementTrackingPdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getImprovementTrackingDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.improvement-tracking-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('improvement_tracking_' . date('Y-m-d') . '.pdf');
    }

    private function getImprovementTrackingDataForExport(Request $request)
    {
        // Similar to getImprovementTrackingData but return array instead of DataTables
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream', 'submissions.student'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->whereNotNull('total_marks')
            ->where('total_marks', '>', 0);

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

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();

        $studentData = [];
        foreach ($assignments as $assignment) {
            foreach ($assignment->submissions as $submission) {
                $studentId = $submission->student_id;
                if (!isset($studentData[$studentId])) {
                    $studentData[$studentId] = [
                        'student_id' => $studentId,
                        'student_name' => $submission->student->first_name . ' ' . $submission->student->last_name,
                        'admission_number' => $submission->student->admission_number ?? 'N/A',
                        'class' => $submission->student->class->name ?? 'N/A',
                        'submissions' => []
                    ];
                }

                if ($submission->marks_obtained !== null && $assignment->total_marks > 0) {
                    $percentage = ($submission->marks_obtained / $assignment->total_marks) * 100;
                    $studentData[$studentId]['submissions'][] = [
                        'date' => $submission->submitted_at,
                        'score' => $percentage
                    ];
                }
            }
        }

        $data = [];
        foreach ($studentData as $student) {
            if (count($student['submissions']) < 2) {
                continue;
            }

            usort($student['submissions'], function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });

            $firstScore = $student['submissions'][0]['score'];
            $latestScore = $student['submissions'][count($student['submissions']) - 1]['score'];
            $improvement = $latestScore - $firstScore;

            $data[] = [
                'student_name' => $student['student_name'],
                'admission_number' => $student['admission_number'],
                'class' => $student['class'],
                'total_submissions' => count($student['submissions']),
                'first_score' => number_format($firstScore, 2),
                'latest_score' => number_format($latestScore, 2),
                'improvement' => number_format($improvement, 2),
                'trend' => $improvement > 0 ? 'Improving' : ($improvement < 0 ? 'Declining' : 'Stable')
            ];
        }

        usort($data, function($a, $b) {
            return (float)$b['improvement'] <=> (float)$a['improvement'];
        });

        return $data;
    }

    // ==================== Teacher Assignment Frequency Report Methods ====================

    private function getTeacherAssignmentFrequencyData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['teacher', 'subject', 'academicYear'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date_assigned', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_assigned', '<=', $request->date_to);
        }

        $assignments = $query->get();

        // Group by teacher
        $teacherData = [];
        foreach ($assignments as $assignment) {
            if (!$assignment->teacher) continue;
            
            $teacherId = $assignment->teacher_id;
            if (!isset($teacherData[$teacherId])) {
                $teacherData[$teacherId] = [
                    'teacher_id' => $teacherId,
                    'teacher_name' => $assignment->teacher->first_name . ' ' . $assignment->teacher->last_name,
                    'employee_id' => $assignment->teacher->employee_id ?? 'N/A',
                    'total_assignments' => 0,
                    'subjects' => [],
                    'by_type' => ['homework' => 0, 'classwork' => 0, 'project' => 0, 'revision_task' => 0]
                ];
            }

            $teacherData[$teacherId]['total_assignments']++;
            $teacherData[$teacherId]['by_type'][$assignment->type] = ($teacherData[$teacherId]['by_type'][$assignment->type] ?? 0) + 1;
            
            $subjectName = $assignment->subject->name ?? 'N/A';
            if (!isset($teacherData[$teacherId]['subjects'][$subjectName])) {
                $teacherData[$teacherId]['subjects'][$subjectName] = 0;
            }
            $teacherData[$teacherId]['subjects'][$subjectName]++;
        }

        $data = [];
        foreach ($teacherData as $teacher) {
            $subjectsStr = implode(', ', array_keys($teacher['subjects']));
            $data[] = [
                'teacher_name' => $teacher['teacher_name'],
                'employee_id' => $teacher['employee_id'],
                'total_assignments' => $teacher['total_assignments'],
                'homework_count' => $teacher['by_type']['homework'],
                'classwork_count' => $teacher['by_type']['classwork'],
                'project_count' => $teacher['by_type']['project'],
                'revision_task_count' => $teacher['by_type']['revision_task'],
                'subjects' => $subjectsStr
            ];
        }

        // Sort by total assignments (highest first)
        usort($data, function($a, $b) {
            return $b['total_assignments'] <=> $a['total_assignments'];
        });

        return DataTables::of($data)->make(true);
    }

    private function exportTeacherAssignmentFrequencyExcel(Request $request)
    {
        $data = $this->getTeacherAssignmentFrequencyDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        return Excel::download(new \App\Exports\TeacherAssignmentFrequencyExport($data, $filters), 
            'teacher_assignment_frequency_' . date('Y-m-d') . '.xlsx');
    }

    private function exportTeacherAssignmentFrequencyPdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getTeacherAssignmentFrequencyDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.teacher-assignment-frequency-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('teacher_assignment_frequency_' . date('Y-m-d') . '.pdf');
    }

    private function getTeacherAssignmentFrequencyDataForExport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['teacher', 'subject', 'academicYear'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date_assigned', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_assigned', '<=', $request->date_to);
        }

        $assignments = $query->get();

        $teacherData = [];
        foreach ($assignments as $assignment) {
            if (!$assignment->teacher) continue;
            
            $teacherId = $assignment->teacher_id;
            if (!isset($teacherData[$teacherId])) {
                $teacherData[$teacherId] = [
                    'teacher_id' => $teacherId,
                    'teacher_name' => $assignment->teacher->first_name . ' ' . $assignment->teacher->last_name,
                    'employee_id' => $assignment->teacher->employee_id ?? 'N/A',
                    'total_assignments' => 0,
                    'subjects' => [],
                    'by_type' => ['homework' => 0, 'classwork' => 0, 'project' => 0, 'revision_task' => 0]
                ];
            }

            $teacherData[$teacherId]['total_assignments']++;
            $teacherData[$teacherId]['by_type'][$assignment->type] = ($teacherData[$teacherId]['by_type'][$assignment->type] ?? 0) + 1;
            
            $subjectName = $assignment->subject->name ?? 'N/A';
            if (!isset($teacherData[$teacherId]['subjects'][$subjectName])) {
                $teacherData[$teacherId]['subjects'][$subjectName] = 0;
            }
            $teacherData[$teacherId]['subjects'][$subjectName]++;
        }

        $data = [];
        foreach ($teacherData as $teacher) {
            $subjectsStr = implode(', ', array_keys($teacher['subjects']));
            $data[] = [
                'teacher_name' => $teacher['teacher_name'],
                'employee_id' => $teacher['employee_id'],
                'total_assignments' => $teacher['total_assignments'],
                'homework_count' => $teacher['by_type']['homework'],
                'classwork_count' => $teacher['by_type']['classwork'],
                'project_count' => $teacher['by_type']['project'],
                'revision_task_count' => $teacher['by_type']['revision_task'],
                'subjects' => $subjectsStr
            ];
        }

        usort($data, function($a, $b) {
            return $b['total_assignments'] <=> $a['total_assignments'];
        });

        return $data;
    }

    // ==================== Class Workload Balance Report Methods ====================

    private function getClassWorkloadBalanceData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['assignmentClasses.classe', 'assignmentClasses.stream', 'academicYear', 'subject'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('date_assigned', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_assigned', '<=', $request->date_to);
        }

        $assignments = $query->get();

        // Group by class
        $classData = [];
        foreach ($assignments as $assignment) {
            foreach ($assignment->assignmentClasses as $assignmentClass) {
                $classId = $assignmentClass->class_id;
                $className = $assignmentClass->classe->name ?? 'N/A';
                $streamName = $assignmentClass->stream->name ?? 'All';
                $key = $classId . '_' . ($assignmentClass->stream_id ?? 'all');

                if (!isset($classData[$key])) {
                    $classData[$key] = [
                        'class_id' => $classId,
                        'class_name' => $className,
                        'stream_name' => $streamName,
                        'total_assignments' => 0,
                        'total_estimated_time' => 0,
                        'by_type' => ['homework' => 0, 'classwork' => 0, 'project' => 0, 'revision_task' => 0],
                        'by_subject' => []
                    ];
                }

                $classData[$key]['total_assignments']++;
                $classData[$key]['by_type'][$assignment->type] = ($classData[$key]['by_type'][$assignment->type] ?? 0) + 1;
                $classData[$key]['total_estimated_time'] += ($assignment->estimated_completion_time ?? 0);

                $subjectName = $assignment->subject->name ?? 'N/A';
                if (!isset($classData[$key]['by_subject'][$subjectName])) {
                    $classData[$key]['by_subject'][$subjectName] = 0;
                }
                $classData[$key]['by_subject'][$subjectName]++;
            }
        }

        $data = [];
        foreach ($classData as $class) {
            $subjectsStr = implode(', ', array_keys($class['by_subject']));
            $data[] = [
                'class_name' => $class['class_name'],
                'stream_name' => $class['stream_name'],
                'total_assignments' => $class['total_assignments'],
                'total_estimated_time' => $class['total_estimated_time'],
                'homework_count' => $class['by_type']['homework'],
                'classwork_count' => $class['by_type']['classwork'],
                'project_count' => $class['by_type']['project'],
                'revision_task_count' => $class['by_type']['revision_task'],
                'subjects' => $subjectsStr,
                'workload_status' => $class['total_assignments'] > 20 ? 'High' : ($class['total_assignments'] > 10 ? 'Medium' : 'Low')
            ];
        }

        // Sort by total assignments (highest first)
        usort($data, function($a, $b) {
            return $b['total_assignments'] <=> $a['total_assignments'];
        });

        return DataTables::of($data)->make(true);
    }

    private function exportClassWorkloadBalanceExcel(Request $request)
    {
        $data = $this->getClassWorkloadBalanceDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        return Excel::download(new \App\Exports\ClassWorkloadBalanceExport($data, $filters), 
            'class_workload_balance_' . date('Y-m-d') . '.xlsx');
    }

    private function exportClassWorkloadBalancePdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getClassWorkloadBalanceDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.class-workload-balance-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('class_workload_balance_' . date('Y-m-d') . '.pdf');
    }

    private function getClassWorkloadBalanceDataForExport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['assignmentClasses.classe', 'assignmentClasses.stream', 'academicYear', 'subject'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('date_assigned', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_assigned', '<=', $request->date_to);
        }

        $assignments = $query->get();

        $classData = [];
        foreach ($assignments as $assignment) {
            foreach ($assignment->assignmentClasses as $assignmentClass) {
                $classId = $assignmentClass->class_id;
                $className = $assignmentClass->classe->name ?? 'N/A';
                $streamName = $assignmentClass->stream->name ?? 'All';
                $key = $classId . '_' . ($assignmentClass->stream_id ?? 'all');

                if (!isset($classData[$key])) {
                    $classData[$key] = [
                        'class_id' => $classId,
                        'class_name' => $className,
                        'stream_name' => $streamName,
                        'total_assignments' => 0,
                        'total_estimated_time' => 0,
                        'by_type' => ['homework' => 0, 'classwork' => 0, 'project' => 0, 'revision_task' => 0],
                        'by_subject' => []
                    ];
                }

                $classData[$key]['total_assignments']++;
                $classData[$key]['by_type'][$assignment->type] = ($classData[$key]['by_type'][$assignment->type] ?? 0) + 1;
                $classData[$key]['total_estimated_time'] += ($assignment->estimated_completion_time ?? 0);

                $subjectName = $assignment->subject->name ?? 'N/A';
                if (!isset($classData[$key]['by_subject'][$subjectName])) {
                    $classData[$key]['by_subject'][$subjectName] = 0;
                }
                $classData[$key]['by_subject'][$subjectName]++;
            }
        }

        $data = [];
        foreach ($classData as $class) {
            $subjectsStr = implode(', ', array_keys($class['by_subject']));
            $data[] = [
                'class_name' => $class['class_name'],
                'stream_name' => $class['stream_name'],
                'total_assignments' => $class['total_assignments'],
                'total_estimated_time' => $class['total_estimated_time'],
                'homework_count' => $class['by_type']['homework'],
                'classwork_count' => $class['by_type']['classwork'],
                'project_count' => $class['by_type']['project'],
                'revision_task_count' => $class['by_type']['revision_task'],
                'subjects' => $subjectsStr,
                'workload_status' => $class['total_assignments'] > 20 ? 'High' : ($class['total_assignments'] > 10 ? 'Medium' : 'Low')
            ];
        }

        usort($data, function($a, $b) {
            return $b['total_assignments'] <=> $a['total_assignments'];
        });

        return $data;
    }

    // ==================== School-Wide Compliance Report Methods ====================

    private function getSchoolComplianceData(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream', 'submissions'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();

        // Calculate compliance metrics
        $totalAssignments = $assignments->count();
        $assignmentsWithSubmissions = 0;
        $assignmentsWithMarks = 0;
        $onTimeSubmissions = 0;
        $lateSubmissions = 0;
        $totalSubmissions = 0;

        foreach ($assignments as $assignment) {
            $submissions = $assignment->submissions;
            if ($submissions->count() > 0) {
                $assignmentsWithSubmissions++;
            }

            $hasMarks = $submissions->whereNotNull('marks_obtained')->count() > 0;
            if ($hasMarks) {
                $assignmentsWithMarks++;
            }

            foreach ($submissions as $submission) {
                $totalSubmissions++;
                if ($submission->is_late) {
                    $lateSubmissions++;
                } else {
                    $onTimeSubmissions++;
                }
            }
        }

        $complianceRate = $totalAssignments > 0 ? ($assignmentsWithSubmissions / $totalAssignments) * 100 : 0;
        $markingRate = $totalAssignments > 0 ? ($assignmentsWithMarks / $totalAssignments) * 100 : 0;
        $onTimeRate = $totalSubmissions > 0 ? ($onTimeSubmissions / $totalSubmissions) * 100 : 0;

        $data = [
            [
                'metric' => 'Total Assignments',
                'value' => $totalAssignments,
                'target' => 'N/A',
                'status' => 'info'
            ],
            [
                'metric' => 'Assignments with Submissions',
                'value' => $assignmentsWithSubmissions,
                'target' => '100%',
                'status' => $complianceRate >= 80 ? 'success' : ($complianceRate >= 60 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Compliance Rate',
                'value' => number_format($complianceRate, 2) . '%',
                'target' => '≥80%',
                'status' => $complianceRate >= 80 ? 'success' : ($complianceRate >= 60 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Assignments with Marks',
                'value' => $assignmentsWithMarks,
                'target' => '100%',
                'status' => $markingRate >= 90 ? 'success' : ($markingRate >= 70 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Marking Rate',
                'value' => number_format($markingRate, 2) . '%',
                'target' => '≥90%',
                'status' => $markingRate >= 90 ? 'success' : ($markingRate >= 70 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Total Submissions',
                'value' => $totalSubmissions,
                'target' => 'N/A',
                'status' => 'info'
            ],
            [
                'metric' => 'On-Time Submissions',
                'value' => $onTimeSubmissions,
                'target' => '≥80%',
                'status' => $onTimeRate >= 80 ? 'success' : ($onTimeRate >= 60 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Late Submissions',
                'value' => $lateSubmissions,
                'target' => '≤20%',
                'status' => $onTimeRate >= 80 ? 'success' : ($onTimeRate >= 60 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'On-Time Submission Rate',
                'value' => number_format($onTimeRate, 2) . '%',
                'target' => '≥80%',
                'status' => $onTimeRate >= 80 ? 'success' : ($onTimeRate >= 60 ? 'warning' : 'danger')
            ]
        ];

        return DataTables::of($data)->make(true);
    }

    private function exportSchoolComplianceExcel(Request $request)
    {
        $data = $this->getSchoolComplianceDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        return Excel::download(new \App\Exports\SchoolComplianceExport($data, $filters), 
            'school_compliance_' . date('Y-m-d') . '.xlsx');
    }

    private function exportSchoolCompliancePdf(Request $request)
    {
        $company = \App\Models\Company::find(Auth::user()->company_id);
        $data = $this->getSchoolComplianceDataForExport($request);
        $filters = $this->getFiltersForExport($request);
        
        $pdf = Pdf::loadView('school.reports.assignments.exports.school-compliance-pdf', [
            'data' => $data,
            'company' => $company,
            'filters' => $filters,
            'generatedAt' => now()
        ])->setPaper('a4', 'landscape');
        
        return $pdf->download('school_compliance_' . date('Y-m-d') . '.pdf');
    }

    private function getSchoolComplianceDataForExport(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $query = Assignment::with(['academicYear', 'subject', 'assignmentClasses.classe', 'assignmentClasses.stream', 'submissions'])
            ->where('company_id', $companyId)
            ->where(function ($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            });

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('class_id')) {
            $query->whereHas('assignmentClasses', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $assignments = $query->get();

        $totalAssignments = $assignments->count();
        $assignmentsWithSubmissions = 0;
        $assignmentsWithMarks = 0;
        $onTimeSubmissions = 0;
        $lateSubmissions = 0;
        $totalSubmissions = 0;

        foreach ($assignments as $assignment) {
            $submissions = $assignment->submissions;
            if ($submissions->count() > 0) {
                $assignmentsWithSubmissions++;
            }

            $hasMarks = $submissions->whereNotNull('marks_obtained')->count() > 0;
            if ($hasMarks) {
                $assignmentsWithMarks++;
            }

            foreach ($submissions as $submission) {
                $totalSubmissions++;
                if ($submission->is_late) {
                    $lateSubmissions++;
                } else {
                    $onTimeSubmissions++;
                }
            }
        }

        $complianceRate = $totalAssignments > 0 ? ($assignmentsWithSubmissions / $totalAssignments) * 100 : 0;
        $markingRate = $totalAssignments > 0 ? ($assignmentsWithMarks / $totalAssignments) * 100 : 0;
        $onTimeRate = $totalSubmissions > 0 ? ($onTimeSubmissions / $totalSubmissions) * 100 : 0;

        return [
            [
                'metric' => 'Total Assignments',
                'value' => $totalAssignments,
                'target' => 'N/A',
                'status' => 'info'
            ],
            [
                'metric' => 'Assignments with Submissions',
                'value' => $assignmentsWithSubmissions,
                'target' => '100%',
                'status' => $complianceRate >= 80 ? 'success' : ($complianceRate >= 60 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Compliance Rate',
                'value' => number_format($complianceRate, 2) . '%',
                'target' => '≥80%',
                'status' => $complianceRate >= 80 ? 'success' : ($complianceRate >= 60 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Assignments with Marks',
                'value' => $assignmentsWithMarks,
                'target' => '100%',
                'status' => $markingRate >= 90 ? 'success' : ($markingRate >= 70 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Marking Rate',
                'value' => number_format($markingRate, 2) . '%',
                'target' => '≥90%',
                'status' => $markingRate >= 90 ? 'success' : ($markingRate >= 70 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Total Submissions',
                'value' => $totalSubmissions,
                'target' => 'N/A',
                'status' => 'info'
            ],
            [
                'metric' => 'On-Time Submissions',
                'value' => $onTimeSubmissions,
                'target' => '≥80%',
                'status' => $onTimeRate >= 80 ? 'success' : ($onTimeRate >= 60 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'Late Submissions',
                'value' => $lateSubmissions,
                'target' => '≤20%',
                'status' => $onTimeRate >= 80 ? 'success' : ($onTimeRate >= 60 ? 'warning' : 'danger')
            ],
            [
                'metric' => 'On-Time Submission Rate',
                'value' => number_format($onTimeRate, 2) . '%',
                'target' => '≥80%',
                'status' => $onTimeRate >= 80 ? 'success' : ($onTimeRate >= 60 ? 'warning' : 'danger')
            ]
        ];
    }
}

