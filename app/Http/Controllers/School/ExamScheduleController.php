<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\ExamSchedule;
use App\Models\ExamScheduleSession;
use App\Models\ExamSchedulePaper;
use App\Models\ExamInvigilation;
use App\Models\ExamClassAssignment;
use App\Models\SchoolExamType;
use App\Models\School\Classe as SchoolClass;
use App\Models\School\Subject;
use App\Models\School\AcademicYear;
use App\Models\School\Stream;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExamScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of exam schedules.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Handle AJAX request for DataTables
        if ($request->ajax()) {
            $query = ExamSchedule::where('company_id', $companyId)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->with(['examType', 'academicYear', 'papers.classe', 'papers.stream', 'papers.session'])
                ->withCount('papers');

            // Apply filters
            if ($request->has('exam_type_id') && !empty($request->exam_type_id)) {
                $query->where('exam_type_id', $request->exam_type_id);
            }

            if ($request->has('academic_year_id') && !empty($request->academic_year_id)) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            $schedules = $query->orderBy('start_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'data' => $schedules->map(function($schedule) {
                    // Get papers with details for display
                    $papers = $schedule->papers()->with(['classe', 'stream'])->get();
                    
                    $papersHtml = '<div class="papers-info">';
                    if ($papers->count() > 0) {
                        $papersHtml .= '<span class="badge bg-info me-1">' . $papers->count() . ' Paper(s)</span>';
                        $papersHtml .= '<div class="papers-list mt-2" style="display: none;">';
                        $papersHtml .= '<table class="table table-sm table-bordered mb-0">';
                        $papersHtml .= '<thead><tr><th>Subject</th><th>Class</th><th>Stream</th><th>Date</th><th>Time</th><th>Type</th></tr></thead>';
                        $papersHtml .= '<tbody>';
                        foreach ($papers as $paper) {
                            $papersHtml .= '<tr>';
                            $papersHtml .= '<td><small>' . ($paper->subject_name ?? 'N/A') . '</small></td>';
                            $papersHtml .= '<td><small>' . ($paper->classe->name ?? 'N/A') . '</small></td>';
                            $papersHtml .= '<td><small>' . ($paper->stream->name ?? 'All') . '</small></td>';
                            $papersHtml .= '<td><small>' . ($paper->session && $paper->session->session_date ? $paper->session->session_date->format('M d') : 'N/A') . '</small></td>';
                            $papersHtml .= '<td><small>';
                            if ($paper->scheduled_start_time && $paper->scheduled_end_time) {
                                $papersHtml .= \Carbon\Carbon::parse($paper->scheduled_start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($paper->scheduled_end_time)->format('H:i');
                            } else {
                                $papersHtml .= 'N/A';
                            }
                            $papersHtml .= '</small></td>';
                            $papersHtml .= '<td><small><span class="badge bg-secondary">' . ucfirst($paper->paper_type ?? 'N/A') . '</span></small></td>';
                            $papersHtml .= '</tr>';
                        }
                        $papersHtml .= '</tbody></table>';
                        $papersHtml .= '</div>';
                        $papersHtml .= '<button type="button" class="btn btn-sm btn-link p-0 mt-1 toggle-papers" data-schedule-id="' . $schedule->id . '">';
                        $papersHtml .= '<small>Show/Hide Details</small>';
                        $papersHtml .= '</button>';
                    } else {
                        $papersHtml .= '<span class="badge bg-secondary">0 Papers</span>';
                    }
                    $papersHtml .= '</div>';
                    
                    return [
                        'id' => $schedule->hashid,
                        'exam_name' => $schedule->exam_name,
                        'exam_type' => $schedule->examType->name ?? 'N/A',
                        'academic_year' => $schedule->academicYear->year_name ?? 'N/A',
                        'term' => $schedule->term ?? 'N/A',
                        'start_date' => $schedule->start_date->format('M d, Y'),
                        'end_date' => $schedule->end_date->format('M d, Y'),
                        'papers' => $papersHtml,
                        'status' => $schedule->getStatusBadge(),
                        'actions' => view('school.exam-schedules.partials.actions', compact('schedule'))->render(),
                    ];
                })
            ]);
        }

        // Regular view request
        $examTypes = SchoolExamType::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->orderBy('start_date', 'desc')
            ->orderBy('year_name')
            ->get();

        return view('school.exam-schedules.index', compact('examTypes', 'academicYears'));
    }

    /**
     * Show the form for creating a new exam schedule.
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $examTypes = SchoolExamType::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all academic years for the company/branch
        $academicYears = AcademicYear::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->orderBy('start_date', 'desc')
            ->orderBy('year_name')
            ->get();

        // Get the current active academic year (is_current = true)
        $currentAcademicYear = AcademicYear::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('is_current', true)
            ->first();

        // If no current academic year found, try to get one with status = 'active'
        if (!$currentAcademicYear) {
            $currentAcademicYear = AcademicYear::where('company_id', $companyId)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->where('status', 'active')
                ->orderBy('start_date', 'desc')
                ->first();
        }

        // Get classes from subject groups (or all classes as fallback)
        $classIds = \App\Models\School\SubjectGroup::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('is_active', true)
            ->whereNotNull('class_id')
            ->distinct()
            ->pluck('class_id')
            ->toArray();

        if (!empty($classIds)) {
            $classes = SchoolClass::whereIn('id', $classIds)
                ->where('company_id', $companyId)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } else {
            // Fallback: If no subject groups found, return all active classes
            $classes = SchoolClass::where('company_id', $companyId)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        return view('school.exam-schedules.create', compact('examTypes', 'academicYears', 'currentAcademicYear', 'classes'));
    }

    /**
     * Get classes for selected exam type and academic year from subject groups.
     */
    public function getClasses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get classes from subject groups
        $classIds = \App\Models\School\SubjectGroup::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('is_active', true)
            ->whereNotNull('class_id')
            ->distinct()
            ->pluck('class_id')
            ->toArray();

        \Log::info('ExamScheduleController@getClasses - Class IDs from subject groups:', ['class_ids' => $classIds]);

        // Get classes that have subject groups
        $classes = [];
        if (!empty($classIds)) {
            $classes = SchoolClass::whereIn('id', $classIds)
                ->where('company_id', $companyId)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        } else {
            // Fallback: If no subject groups found, return all active classes
            \Log::warning('ExamScheduleController@getClasses - No subject groups found, falling back to all classes');
            $classes = SchoolClass::where('company_id', $companyId)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray();
        }

        \Log::info('ExamScheduleController@getClasses - Classes found:', ['count' => count($classes), 'classes' => $classes]);

        return response()->json([
            'success' => true,
            'classes' => $classes,
        ]);
    }

    /**
     * Get streams for a class from subject groups.
     */
    public function getStreams($classId)
    {
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $streams = [];

        if ($classId) {
            // Get streams that are associated with this class
            $streams = \App\Models\School\Stream::whereHas('classes', function($query) use ($classId) {
                $query->where('classes.id', $classId);
            })
            ->where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(function($stream) {
                return [
                    'id' => $stream->id,
                    'name' => $stream->name,
                ];
            })
            ->toArray();
        }

        return response()->json(['streams' => $streams]);
    }

    /**
     * Get courses/subjects for selected exam and class assignment.
     */
    public function getCourses(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'nullable|exists:streams,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        // Get exam class assignments for the selected class (stream is optional)
        $assignments = ExamClassAssignment::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('exam_type_id', $request->exam_type_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->with(['subject', 'classe', 'stream', 'examType'])
            ->get();

        // Get all assignments for the class regardless of stream (stream is optional)

        $courses = $assignments->map(function($assignment) {
            $subject = $assignment->subject;
            $stream = $assignment->stream;
            
            // Get number of students from exam registrations
            $studentCount = \App\Models\School\SchoolExamRegistration::where('exam_class_assignment_id', $assignment->id)
                ->where('status', 'registered')
                ->count();

            return [
                'assignment_id' => $assignment->id,
                'subject_id' => $subject->id,
                'subject_name' => $subject->name,
                'subject_code' => $subject->code ?? '',
                'class_id' => $assignment->class_id,
                'class_name' => $assignment->classe->name ?? '',
                'stream_id' => $assignment->stream_id,
                'stream_name' => $stream ? $stream->name : null,
                'number_of_students' => $studentCount,
                'exam_type' => $assignment->examType->name ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'courses' => $courses,
        ]);
    }

    /**
     * Store a newly created exam schedule.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term' => 'nullable|in:I,II,III',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'exam_days' => 'nullable|array',
            'has_half_day_exams' => 'boolean',
            'min_break_minutes' => 'integer|min:0|max:120',
            'assignment_ids' => 'nullable|array',
            'assignment_ids.*' => 'exists:exam_class_assignments,id',
            'course_dates' => 'nullable|array',
            'course_start_times' => 'nullable|array',
            'course_end_times' => 'nullable|array',
            'course_types' => 'nullable|array',
            'course_types.*' => 'in:theory,practical,oral',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        DB::beginTransaction();
        try {
            // Get exam type name for default exam_name
            $examType = SchoolExamType::findOrFail($request->exam_type_id);
            
            $schedule = ExamSchedule::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'exam_type_id' => $request->exam_type_id,
                'academic_year_id' => $request->academic_year_id,
                'exam_name' => $examType->name, // Use exam type name as default
                'term' => $request->term,
                'exam_type_category' => 'written', // Default to written
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'exam_days' => $request->exam_days ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'has_half_day_exams' => $request->has_half_day_exams ?? false,
                'min_break_minutes' => $request->min_break_minutes ?? 30,
                'status' => 'draft',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            // Save selected courses/papers if any
            \Log::info('ExamScheduleController@store - Checking for assignment_ids', [
                'has_assignment_ids' => $request->has('assignment_ids'),
                'assignment_ids' => $request->assignment_ids,
                'course_dates' => $request->course_dates,
                'course_start_times' => $request->course_start_times,
                'course_end_times' => $request->course_end_times,
                'course_types' => $request->course_types,
            ]);

            if ($request->has('assignment_ids') && !empty($request->assignment_ids)) {
                $assignmentIds = $request->assignment_ids;
                $courseDates = $request->course_dates ?? [];
                $courseStartTimes = $request->course_start_times ?? [];
                $courseEndTimes = $request->course_end_times ?? [];
                $courseTypes = $request->course_types ?? [];

                $papersCreated = 0;
                $papersSkipped = 0;

                foreach ($assignmentIds as $assignmentId) {
                    if (!isset($courseDates[$assignmentId]) || !isset($courseStartTimes[$assignmentId]) || !isset($courseEndTimes[$assignmentId])) {
                        \Log::warning('ExamScheduleController@store - Skipping paper due to missing fields', [
                            'assignment_id' => $assignmentId,
                            'has_date' => isset($courseDates[$assignmentId]),
                            'has_start_time' => isset($courseStartTimes[$assignmentId]),
                            'has_end_time' => isset($courseEndTimes[$assignmentId]),
                        ]);
                        $papersSkipped++;
                        continue; // Skip if required fields are missing
                    }

                    // Get the assignment details
                    $assignment = ExamClassAssignment::with(['subject', 'classe', 'stream'])->find($assignmentId);
                    if (!$assignment) {
                        continue;
                    }

                    $examDate = Carbon::parse($courseDates[$assignmentId]);
                    $startTime = Carbon::parse($courseStartTimes[$assignmentId]);
                    $endTime = Carbon::parse($courseEndTimes[$assignmentId]);
                    $paperType = $courseTypes[$assignmentId] ?? 'theory';

                    // Calculate duration in minutes
                    $durationMinutes = $startTime->diffInMinutes($endTime);

                    // Find or create a session for this date and time
                    $session = ExamScheduleSession::firstOrCreate(
                        [
                            'exam_schedule_id' => $schedule->id,
                            'session_date' => $examDate->format('Y-m-d'),
                            'start_time' => $startTime->format('H:i:s'),
                            'end_time' => $endTime->format('H:i:s'),
                        ],
                        [
                            'session_name' => $startTime->format('H:i') . ' - ' . $endTime->format('H:i'),
                            'is_half_day' => false,
                            'order' => 1,
                        ]
                    );

                    // Get number of registered students
                    $studentCount = \App\Models\School\SchoolExamRegistration::where('exam_class_assignment_id', $assignmentId)
                        ->where('status', 'registered')
                        ->count();

                    // Create the paper
                    try {
                        $paper = ExamSchedulePaper::create([
                            'exam_schedule_id' => $schedule->id,
                            'exam_schedule_session_id' => $session->id,
                            'exam_class_assignment_id' => $assignmentId,
                            'class_id' => $assignment->class_id,
                            'stream_id' => $assignment->stream_id,
                            'subject_id' => $assignment->subject_id,
                            'subject_name' => $assignment->subject->name ?? '',
                            'subject_code' => $assignment->subject->code ?? '',
                            'total_marks' => 100, // Default, should come from assignment or subject
                            'duration_minutes' => $durationMinutes,
                            'is_compulsory' => true,
                            'paper_type' => $paperType,
                            'subject_priority' => 0,
                            'is_heavy_subject' => false,
                            'scheduled_start_time' => $startTime->format('H:i:s'),
                            'scheduled_end_time' => $endTime->format('H:i:s'),
                            'number_of_students' => $studentCount,
                            'status' => 'scheduled',
                        ]);
                        $papersCreated++;
                        \Log::info('ExamScheduleController@store - Paper created successfully', [
                            'paper_id' => $paper->id,
                            'assignment_id' => $assignmentId,
                            'subject_name' => $paper->subject_name,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('ExamScheduleController@store - Failed to create paper', [
                            'assignment_id' => $assignmentId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $papersSkipped++;
                    }
                }

                \Log::info('ExamScheduleController@store - Papers creation summary', [
                    'total_assignment_ids' => count($assignmentIds),
                    'papers_created' => $papersCreated,
                    'papers_skipped' => $papersSkipped,
                ]);
            } else {
                \Log::warning('ExamScheduleController@store - No assignment_ids provided', [
                    'request_keys' => array_keys($request->all()),
                ]);
            }

            DB::commit();

            $message = 'Exam schedule created successfully.';
            if ($request->has('assignment_ids') && !empty($request->assignment_ids)) {
                $message .= ' ' . count($request->assignment_ids) . ' paper(s) have been scheduled.';
            }

            return redirect()->route('school.exam-schedules.show', $schedule->hashid)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ExamScheduleController@store error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create exam schedule: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified exam schedule.
     */
    public function show($hashid)
    {
        $schedule = ExamSchedule::findByHashid($hashid);

        if (!$schedule) {
            abort(404, 'Exam schedule not found');
        }

        // Load all relationships - ensure papers are loaded
        $schedule->load([
            'sessions', 
            'papers.session',
            'papers.classe',
            'papers.stream',
            'papers.invigilations.invigilator',
            'creator',
            'company',
            'branch'
        ]);
        
        // Load company with logo if needed
        if ($schedule->company_id) {
            $schedule->load('company');
        }

        // Refresh papers relationship to ensure we have the latest data
        $schedule->load('papers');

        // Debug: Check if papers exist in database
        $papersInDb = \App\Models\ExamSchedulePaper::where('exam_schedule_id', $schedule->id)->get();
        \Log::info('ExamScheduleController@show - Papers check', [
            'schedule_id' => $schedule->id,
            'papers_via_relationship' => $schedule->papers->count(),
            'papers_in_database' => $papersInDb->count(),
        ]);
        
        // If papers exist in DB but not in relationship, reload
        if ($papersInDb->count() > 0 && $schedule->papers->count() == 0) {
            \Log::warning('ExamScheduleController@show - Papers exist in DB but not loading via relationship', [
                'schedule_id' => $schedule->id,
                'papers_in_db' => $papersInDb->count(),
            ]);
            $schedule->refresh();
            $schedule->load('papers');
        }
        
        // Calculate summary statistics
        $totalPapers = $schedule->papers->count();
        $totalSessions = $schedule->sessions->count();
        $totalStudents = $schedule->papers->sum('number_of_students');
        $papersByType = $schedule->papers->groupBy('paper_type')->map->count();
        
        // Get unique classes from papers
        $classes = $schedule->papers->pluck('classe')->filter()->unique('id')->values();
        $streams = $schedule->papers->pluck('stream')->filter()->unique('id')->values();

        // Group papers by date for timetable display
        $papersByDate = $schedule->papers->groupBy(function($paper) {
            if ($paper->session && $paper->session->session_date) {
                return $paper->session->session_date->format('Y-m-d');
            }
            // Fallback: use scheduled_start_time if session is missing
            if ($paper->scheduled_start_time) {
                try {
                    return \Carbon\Carbon::parse($paper->scheduled_start_time)->format('Y-m-d');
                } catch (\Exception $e) {
                    return 'unscheduled';
                }
            }
            return 'unscheduled';
        })->sortKeys();

        // Prepare papers data for print
        $papersForPrint = $schedule->papers->map(function($paper) {
            return [
                'subject_name' => $paper->subject_name ?? 'N/A',
                'subject_code' => $paper->subject_code ?? '',
                'class_name' => $paper->classe->name ?? 'N/A',
                'stream_name' => $paper->stream->name ?? 'All Streams',
                'paper_type' => ucfirst($paper->paper_type ?? 'N/A'),
                'date' => $paper->session && $paper->session->session_date 
                    ? $paper->session->session_date->format('l, M d, Y') 
                    : 'N/A',
                'start_time' => $paper->scheduled_start_time 
                    ? \Carbon\Carbon::parse($paper->scheduled_start_time)->format('H:i') 
                    : 'N/A',
                'end_time' => $paper->scheduled_end_time 
                    ? \Carbon\Carbon::parse($paper->scheduled_end_time)->format('H:i') 
                    : 'N/A',
                'duration' => ($paper->duration_minutes ?? 0) . ' min',
                'total_marks' => $paper->total_marks ?? 'N/A',
                'number_of_students' => $paper->number_of_students ?? 0,
            ];
        })->values();

        return view('school.exam-schedules.show', compact('schedule', 'papersByDate', 'totalPapers', 'totalSessions', 'totalStudents', 'papersByType', 'classes', 'streams', 'papersForPrint'));
    }

    /**
     * Show the form for editing the specified exam schedule.
     */
    public function edit($hashid)
    {
        $schedule = ExamSchedule::findByHashid($hashid);

        if (!$schedule) {
            abort(404, 'Exam schedule not found');
        }
        
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        $examTypes = SchoolExamType::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->orderBy('start_date', 'desc')
            ->orderBy('year_name')
            ->get();

        // Get classes from subject groups (or all classes as fallback)
        $classIds = \App\Models\School\SubjectGroup::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->where('is_active', true)
            ->whereNotNull('class_id')
            ->distinct()
            ->pluck('class_id')
            ->toArray();

        if (!empty($classIds)) {
            $classes = SchoolClass::whereIn('id', $classIds)
                ->where('company_id', $companyId)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        } else {
            // Fallback: If no subject groups found, return all active classes
            $classes = SchoolClass::where('company_id', $companyId)
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }

        // Load existing papers with their relationships
        $schedule->load([
            'papers.examClassAssignment.subject',
            'papers.classe',
            'papers.stream',
            'papers.session'
        ]);

        return view('school.exam-schedules.edit', compact('schedule', 'examTypes', 'academicYears', 'classes'));
    }

    /**
     * Update the specified exam schedule.
     */
    public function update(Request $request, $hashid)
    {
        $schedule = ExamSchedule::findByHashid($hashid);

        if (!$schedule) {
            abort(404, 'Exam schedule not found');
        }

        $validator = Validator::make($request->all(), [
            'exam_type_id' => 'required|exists:school_exam_types,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term' => 'nullable|in:I,II,III',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'exam_days' => 'nullable|array',
            'has_half_day_exams' => 'boolean',
            'min_break_minutes' => 'integer|min:0|max:120',
            'assignment_ids' => 'nullable|array',
            'assignment_ids.*' => 'exists:exam_class_assignments,id',
            'course_dates' => 'nullable|array',
            'course_start_times' => 'nullable|array',
            'course_end_times' => 'nullable|array',
            'course_types' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Get exam type name for exam_name
            $examType = SchoolExamType::find($request->exam_type_id);
            
            $schedule->update([
                'exam_type_id' => $request->exam_type_id,
                'academic_year_id' => $request->academic_year_id,
                'exam_name' => $examType->name ?? $schedule->exam_name,
                'term' => $request->term,
                'exam_type_category' => 'written', // Default as per create method
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'exam_days' => $request->exam_days ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                'has_half_day_exams' => $request->has_half_day_exams ?? false,
                'min_break_minutes' => $request->min_break_minutes ?? 30,
                'notes' => $request->notes,
            ]);

            // Save selected courses/papers if any (same logic as store method)
            \Log::info('ExamScheduleController@update - Checking for assignment_ids', [
                'has_assignment_ids' => $request->has('assignment_ids'),
                'assignment_ids' => $request->assignment_ids,
                'course_dates' => $request->course_dates,
                'course_start_times' => $request->course_start_times,
                'course_end_times' => $request->course_end_times,
                'course_types' => $request->course_types,
            ]);

            if ($request->has('assignment_ids') && !empty($request->assignment_ids)) {
                $assignmentIds = $request->assignment_ids;
                $courseDates = $request->course_dates ?? [];
                $courseStartTimes = $request->course_start_times ?? [];
                $courseEndTimes = $request->course_end_times ?? [];
                $courseTypes = $request->course_types ?? [];

                $papersCreated = 0;
                $papersSkipped = 0;

                foreach ($assignmentIds as $assignmentId) {
                    if (!isset($courseDates[$assignmentId]) || !isset($courseStartTimes[$assignmentId]) || !isset($courseEndTimes[$assignmentId])) {
                        \Log::warning('ExamScheduleController@update - Skipping paper due to missing fields', [
                            'assignment_id' => $assignmentId,
                            'has_date' => isset($courseDates[$assignmentId]),
                            'has_start_time' => isset($courseStartTimes[$assignmentId]),
                            'has_end_time' => isset($courseEndTimes[$assignmentId]),
                        ]);
                        $papersSkipped++;
                        continue;
                    }

                    // Check if paper already exists for this assignment
                    $existingPaper = ExamSchedulePaper::where('exam_schedule_id', $schedule->id)
                        ->where('exam_class_assignment_id', $assignmentId)
                        ->first();

                    if ($existingPaper) {
                        // Update existing paper
                        $examDate = Carbon::parse($courseDates[$assignmentId]);
                        $startTime = Carbon::parse($courseStartTimes[$assignmentId]);
                        $endTime = Carbon::parse($courseEndTimes[$assignmentId]);
                        $paperType = $courseTypes[$assignmentId] ?? 'theory';
                        $durationMinutes = $startTime->diffInMinutes($endTime);

                        // Find or create a session for this date and time
                        $session = ExamScheduleSession::firstOrCreate(
                            [
                                'exam_schedule_id' => $schedule->id,
                                'session_date' => $examDate->format('Y-m-d'),
                                'start_time' => $startTime->format('H:i:s'),
                                'end_time' => $endTime->format('H:i:s'),
                            ],
                            [
                                'session_name' => $startTime->format('H:i') . ' - ' . $endTime->format('H:i'),
                                'is_half_day' => false,
                                'order' => 1,
                            ]
                        );

                        $existingPaper->update([
                            'exam_schedule_session_id' => $session->id,
                            'paper_type' => $paperType,
                            'duration_minutes' => $durationMinutes,
                            'scheduled_start_time' => $startTime->format('H:i:s'),
                            'scheduled_end_time' => $endTime->format('H:i:s'),
                        ]);

                        $papersCreated++;
                        \Log::info('ExamScheduleController@update - Paper updated', [
                            'paper_id' => $existingPaper->id,
                            'assignment_id' => $assignmentId,
                        ]);
                        continue;
                    }

                    // Get the assignment details
                    $assignment = ExamClassAssignment::with(['subject', 'classe', 'stream'])->find($assignmentId);
                    if (!$assignment) {
                        continue;
                    }

                    $examDate = Carbon::parse($courseDates[$assignmentId]);
                    $startTime = Carbon::parse($courseStartTimes[$assignmentId]);
                    $endTime = Carbon::parse($courseEndTimes[$assignmentId]);
                    $paperType = $courseTypes[$assignmentId] ?? 'theory';

                    // Calculate duration in minutes
                    $durationMinutes = $startTime->diffInMinutes($endTime);

                    // Find or create a session for this date and time
                    $session = ExamScheduleSession::firstOrCreate(
                        [
                            'exam_schedule_id' => $schedule->id,
                            'session_date' => $examDate->format('Y-m-d'),
                            'start_time' => $startTime->format('H:i:s'),
                            'end_time' => $endTime->format('H:i:s'),
                        ],
                        [
                            'session_name' => $startTime->format('H:i') . ' - ' . $endTime->format('H:i'),
                            'is_half_day' => false,
                            'order' => 1,
                        ]
                    );

                    // Get number of registered students
                    $studentCount = \App\Models\School\SchoolExamRegistration::where('exam_class_assignment_id', $assignmentId)
                        ->where('status', 'registered')
                        ->count();

                    // Create the paper
                    try {
                        $paper = ExamSchedulePaper::create([
                            'exam_schedule_id' => $schedule->id,
                            'exam_schedule_session_id' => $session->id,
                            'exam_class_assignment_id' => $assignmentId,
                            'class_id' => $assignment->class_id,
                            'stream_id' => $assignment->stream_id,
                            'subject_id' => $assignment->subject_id,
                            'subject_name' => $assignment->subject->name ?? '',
                            'subject_code' => $assignment->subject->code ?? '',
                            'total_marks' => 100, // Default, should come from assignment or subject
                            'duration_minutes' => $durationMinutes,
                            'is_compulsory' => true,
                            'paper_type' => $paperType,
                            'subject_priority' => 0,
                            'is_heavy_subject' => false,
                            'scheduled_start_time' => $startTime->format('H:i:s'),
                            'scheduled_end_time' => $endTime->format('H:i:s'),
                            'number_of_students' => $studentCount,
                            'status' => 'scheduled',
                        ]);
                        $papersCreated++;
                        \Log::info('ExamScheduleController@update - Paper created successfully', [
                            'paper_id' => $paper->id,
                            'assignment_id' => $assignmentId,
                            'subject_name' => $paper->subject_name,
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('ExamScheduleController@update - Failed to create paper', [
                            'assignment_id' => $assignmentId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                        $papersSkipped++;
                    }
                }

                \Log::info('ExamScheduleController@update - Papers creation summary', [
                    'total_assignment_ids' => count($assignmentIds),
                    'papers_created' => $papersCreated,
                    'papers_skipped' => $papersSkipped,
                ]);
            } else {
                \Log::warning('ExamScheduleController@update - No assignment_ids provided', [
                    'request_keys' => array_keys($request->all()),
                ]);
            }

            DB::commit();

            $message = 'Exam schedule updated successfully.';
            if ($request->has('assignment_ids') && !empty($request->assignment_ids)) {
                $message .= ' ' . count($request->assignment_ids) . ' paper(s) have been scheduled.';
            }

            return redirect()->route('school.exam-schedules.show', $schedule->hashid)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ExamScheduleController@update error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to update exam schedule: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Auto-schedule papers based on constraints.
     */
    public function autoSchedule(Request $request, $hashid)
    {
        $schedule = ExamSchedule::findByHashid($hashid, ['papers']);

        if (!$schedule) {
            abort(404, 'Exam schedule not found');
        }

        $validator = Validator::make($request->all(), [
            'assignment_ids' => 'required|array|min:1',
            'assignment_ids.*' => 'required|exists:exam_class_assignments,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // This is a complex algorithm - implementing basic version
        // Full implementation would include all constraints
        
        DB::beginTransaction();
        try {
            // Get assignments with subject details
            $assignments = ExamClassAssignment::whereIn('id', $request->assignment_ids)
                ->with(['subject', 'classe', 'stream'])
                ->get();

            // Create sessions if they don't exist
            $this->createSessionsIfNeeded($schedule);

            // Get sessions
            $sessions = ExamScheduleSession::where('exam_schedule_id', $schedule->id)
                ->orderBy('session_date')
                ->orderBy('order')
                ->get()
                ->groupBy('session_date');

            // Schedule papers
            $scheduledPapers = [];
            $currentDate = Carbon::parse($schedule->start_date);
            $endDate = Carbon::parse($schedule->end_date);

            foreach ($assignments as $assignment) {
                $subject = $assignment->subject;
                
                // Determine priority (core subjects first)
                $priority = $this->getSubjectPriority($subject);
                $isHeavy = $this->isHeavySubject($subject);

                // Find available slot
                $slot = $this->findAvailableSlot($schedule, $sessions, $assignment, $priority, $isHeavy, $currentDate, $endDate);

                if ($slot) {
                    $paper = ExamSchedulePaper::create([
                        'exam_schedule_id' => $schedule->id,
                        'exam_schedule_session_id' => $slot['session_id'],
                        'exam_class_assignment_id' => $assignment->id,
                        'class_id' => $assignment->class_id,
                        'stream_id' => $assignment->stream_id,
                        'subject_id' => $subject->id,
                        'subject_name' => $subject->name,
                        'subject_code' => $subject->code ?? '',
                        'total_marks' => 100, // Default, should come from assignment or subject
                        'duration_minutes' => 120, // Default, should be configurable
                        'is_compulsory' => true,
                        'paper_type' => $schedule->exam_type_category,
                        'subject_priority' => $priority,
                        'is_heavy_subject' => $isHeavy,
                        'scheduled_start_time' => $slot['start_time'],
                        'scheduled_end_time' => $slot['end_time'],
                        'status' => 'scheduled',
                    ]);

                    $scheduledPapers[] = $paper;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($scheduledPapers) . ' papers scheduled successfully.',
                'papers' => $scheduledPapers,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ExamScheduleController@autoSchedule error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-schedule: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Create sessions if they don't exist.
     */
    private function createSessionsIfNeeded($schedule)
    {
        $existingSessions = ExamScheduleSession::where('exam_schedule_id', $schedule->id)->count();
        
        if ($existingSessions > 0) {
            return; // Sessions already exist
        }

        $currentDate = Carbon::parse($schedule->start_date);
        $endDate = Carbon::parse($schedule->end_date);
        $examDays = $schedule->exam_days ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        while ($currentDate->lte($endDate)) {
            $dayName = $currentDate->format('l');
            
            if (in_array($dayName, $examDays)) {
                // Morning session
                ExamScheduleSession::create([
                    'exam_schedule_id' => $schedule->id,
                    'session_date' => $currentDate->format('Y-m-d'),
                    'session_name' => 'Morning',
                    'start_time' => '08:30:00',
                    'end_time' => '10:00:00',
                    'is_half_day' => false,
                    'order' => 1,
                ]);

                // Mid-morning session
                ExamScheduleSession::create([
                    'exam_schedule_id' => $schedule->id,
                    'session_date' => $currentDate->format('Y-m-d'),
                    'session_name' => 'Mid-morning',
                    'start_time' => '10:30:00',
                    'end_time' => '12:00:00',
                    'is_half_day' => false,
                    'order' => 2,
                ]);

                // Afternoon session (optional)
                if (!$schedule->has_half_day_exams) {
                    ExamScheduleSession::create([
                        'exam_schedule_id' => $schedule->id,
                        'session_date' => $currentDate->format('Y-m-d'),
                        'session_name' => 'Afternoon',
                        'start_time' => '14:00:00',
                        'end_time' => '15:30:00',
                        'is_half_day' => false,
                        'order' => 3,
                    ]);
                }
            }

            $currentDate->addDay();
        }
    }

    /**
     * Helper: Get subject priority (higher = scheduled earlier).
     */
    private function getSubjectPriority($subject)
    {
        $coreSubjects = ['Mathematics', 'Maths', 'English', 'Kiswahili', 'Swahili'];
        $subjectName = strtolower($subject->name ?? '');

        foreach ($coreSubjects as $core) {
            if (stripos($subjectName, strtolower($core)) !== false) {
                return 10; // High priority
            }
        }

        return 5; // Default priority
    }

    /**
     * Helper: Check if subject is heavy.
     */
    private function isHeavySubject($subject)
    {
        $heavySubjects = ['Mathematics', 'Maths', 'Physics', 'Chemistry', 'Biology'];
        $subjectName = strtolower($subject->name ?? '');

        foreach ($heavySubjects as $heavy) {
            if (stripos($subjectName, strtolower($heavy)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper: Find available slot for a paper.
     */
    private function findAvailableSlot($schedule, $sessions, $assignment, $priority, $isHeavy, $startDate, $endDate)
    {
        // Simplified version - full implementation would check all constraints
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $daySessions = $sessions->get($currentDate->format('Y-m-d'), collect());

            foreach ($daySessions as $session) {
                // Check if class already has exam in this session
                $existingPaper = ExamSchedulePaper::where('exam_schedule_id', $schedule->id)
                    ->where('exam_schedule_session_id', $session->id)
                    ->where('class_id', $assignment->class_id)
                    ->where('stream_id', $assignment->stream_id)
                    ->first();

                if (!$existingPaper) {
                    // Check if two heavy subjects on same day
                    if ($isHeavy) {
                        $sessionIds = $daySessions->pluck('id');
                        $heavyCount = ExamSchedulePaper::where('exam_schedule_id', $schedule->id)
                            ->whereIn('exam_schedule_session_id', $sessionIds)
                            ->where('class_id', $assignment->class_id)
                            ->when($assignment->stream_id, function($q) use ($assignment) {
                                return $q->where('stream_id', $assignment->stream_id);
                            }, function($q) {
                                return $q->whereNull('stream_id');
                            })
                            ->where('is_heavy_subject', true)
                            ->count();

                        if ($heavyCount >= 1) {
                            continue; // Skip this day
                        }
                    }

                    return [
                        'session_id' => $session->id,
                        'start_time' => $session->start_time,
                        'end_time' => $session->end_time,
                    ];
                }
            }

            $currentDate->addDay();
        }

        return null; // No slot found
    }

    /**
     * Remove the specified exam schedule from storage.
     */
    /**
     * Publish exam schedule to parents.
     */
    public function publish($hashid)
    {
        $schedule = ExamSchedule::findByHashid($hashid);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Exam schedule not found.'
            ], 404);
        }

        // Check if schedule can be published (must have papers)
        if ($schedule->papers()->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot publish exam schedule without any papers. Please add papers first.'
            ], 422);
        }

        // Update status to published
        $schedule->update([
            'status' => 'published'
        ]);

        // TODO: Add notification logic here to send to parents
        // This could include:
        // - SMS notifications
        // - Email notifications
        // - Push notifications via mobile app
        // - In-app notifications

        return response()->json([
            'success' => true,
            'message' => 'Exam schedule has been published to parents successfully.',
            'status' => $schedule->getStatusBadge()
        ]);
    }

    /**
     * Remove the specified exam schedule from storage.
     */
    public function destroy($hashid)
    {
        $schedule = ExamSchedule::findByHashid($hashid);

        if (!$schedule) {
            abort(404, 'Exam schedule not found');
        }

        // Check authorization
        $companyId = Auth::user()->company_id;
        $branchId = session('branch_id') ?: Auth::user()->branch_id;

        if ($schedule->company_id !== $companyId) {
            abort(403, 'Unauthorized access to exam schedule.');
        }

        if ($schedule->branch_id && $schedule->branch_id !== $branchId) {
            abort(403, 'Unauthorized access to exam schedule.');
        }

        // Prevent deletion of published or ongoing schedules
        if (in_array($schedule->status, ['published', 'ongoing'])) {
            return redirect()->back()
                ->with('error', 'Cannot delete a published or ongoing exam schedule. Please cancel it first.');
        }

        DB::beginTransaction();
        try {
            // Delete related records (cascade will handle most, but we'll be explicit)
            ExamSchedulePaper::where('exam_schedule_id', $schedule->id)->delete();
            ExamScheduleSession::where('exam_schedule_id', $schedule->id)->delete();
            
            // Delete the schedule
            $schedule->delete();

            DB::commit();

            return redirect()->route('school.exam-schedules.index')
                ->with('success', 'Exam schedule deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('ExamScheduleController@destroy error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete exam schedule: ' . $e->getMessage());
        }
    }
}

