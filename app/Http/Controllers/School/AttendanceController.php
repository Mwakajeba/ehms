<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use App\Models\School\AttendanceSession;
use App\Models\School\StudentAttendance;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\School\AcademicYear;
use App\Models\School\Student;
use App\Services\ParentNotificationService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance sessions.
     */
    public function index(Request $request)
    {
        // Get filter options
        $classes = Classe::all();
        $streams = Stream::all();
        $academicYears = AcademicYear::all();

        // Get selected filter values for form repopulation
        $selectedClass = $request->get('class_id');
        $selectedStream = $request->get('stream_id');
        $selectedAcademicYear = $request->get('academic_year_id');

        // Set current academic year as default if no academic year is selected
        if (!$selectedAcademicYear) {
            $currentAcademicYear = AcademicYear::current();
            if ($currentAcademicYear) {
                $selectedAcademicYear = $currentAcademicYear->id;
            }
        }

        return view('school.attendance.index', compact(
            'classes', 'streams', 'academicYears',
            'selectedClass', 'selectedStream', 'selectedAcademicYear'
        ));
    }

    /**
     * Show the form for creating a new attendance session.
     */
    public function create()
    {
        $classes = Classe::all();
        $streams = Stream::all();
        $academicYears = AcademicYear::all();

        // Set current academic year as default
        $currentAcademicYear = AcademicYear::current();
        $defaultAcademicYear = $currentAcademicYear ? $currentAcademicYear->id : null;

        return view('school.attendance.create', compact('classes', 'streams', 'academicYears', 'defaultAcademicYear'));
    }

    /**
     * Store a newly created attendance session.
     */
    public function store(Request $request)
    {
        $request->validate([
            'session_date' => 'required|date|before_or_equal:today',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'required|exists:streams,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Check if attendance session already exists for this date/class/stream
        $existingSession = AttendanceSession::where('session_date', $request->session_date)
            ->where('class_id', $request->class_id)
            ->where('stream_id', $request->stream_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->first();

        if ($existingSession) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['session_date' => 'Attendance session already exists for this date, class, and stream.']);
        }

        $attendanceSession = AttendanceSession::create([
            'session_date' => $request->session_date,
            'class_id' => $request->class_id,
            'stream_id' => $request->stream_id,
            'academic_year_id' => $request->academic_year_id,
            'created_by' => auth()->id(),
            'notes' => $request->notes
        ]);

        return redirect()->route('school.attendance.show', $attendanceSession->getRouteKey())
            ->with('success', 'Attendance session created successfully. You can now mark attendance for students.');
    }

    /**
     * Display the specified attendance session.
     */
    public function show(AttendanceSession $attendanceSession)
    {
        $attendanceSession->load(['class', 'stream', 'academicYear', 'studentAttendances.student']);

        // Get students for this class/stream/academic year
        $students = Student::where('class_id', $attendanceSession->class_id)
            ->where('stream_id', $attendanceSession->stream_id)
            ->where('academic_year_id', $attendanceSession->academic_year_id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Debug logging
        \Log::info('Attendance session show', [
            'session_id' => $attendanceSession->id,
            'class_id' => $attendanceSession->class_id,
            'stream_id' => $attendanceSession->stream_id,
            'academic_year_id' => $attendanceSession->academic_year_id,
            'students_count' => $students->count(),
            'first_few_student_ids' => $students->take(5)->pluck('id')->toArray()
        ]);

        // Get attendance statistics
        $stats = $attendanceSession->getAttendanceStats();

        return view('school.attendance.show', compact('attendanceSession', 'students', 'stats'));
    }

    /**
     * Show the form for editing the specified attendance session.
     */
    public function edit(AttendanceSession $attendanceSession)
    {
        $classes = Classe::all();
        $streams = Stream::all();
        $academicYears = AcademicYear::all();

        return view('school.attendance.edit', compact('attendanceSession', 'classes', 'streams', 'academicYears'));
    }

    /**
     * Update the specified attendance session.
     */
    public function update(Request $request, AttendanceSession $attendanceSession)
    {
        // If only status is being updated (for re-opening sessions), make other fields optional
        if ($request->has('status') && !$request->has(['session_date', 'class_id', 'stream_id', 'academic_year_id'])) {
            $request->validate([
                'status' => 'required|in:active,completed,cancelled',
                'notes' => 'nullable|string|max:1000'
            ]);

            $attendanceSession->update($request->only(['status', 'notes']));

            return response()->json([
                'success' => true,
                'message' => 'Attendance session updated successfully.'
            ]);
        }

        // Full update validation
        $request->validate([
            'session_date' => 'required|date|before_or_equal:today',
            'class_id' => 'required|exists:classes,id',
            'stream_id' => 'required|exists:streams,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'status' => 'required|in:active,completed,cancelled',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Check if another attendance session exists for this date/class/stream (excluding current)
        $existingSession = AttendanceSession::where('session_date', $request->session_date)
            ->where('class_id', $request->class_id)
            ->where('stream_id', $request->stream_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('id', '!=', $attendanceSession->id)
            ->first();

        if ($existingSession) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['session_date' => 'Another attendance session already exists for this date, class, and stream.']);
        }

        $attendanceSession->update($request->only([
            'session_date', 'class_id', 'stream_id', 'academic_year_id', 'status', 'notes'
        ]));

        return redirect()->route('school.attendance.show', $attendanceSession->getRouteKey())
            ->with('success', 'Attendance session updated successfully.');
    }

    /**
     * Remove the specified attendance session.
     */
    public function destroy(AttendanceSession $attendanceSession)
    {
        // Check if session has attendance records
        if ($attendanceSession->studentAttendances()->count() > 0) {
            return redirect()->back()
                ->withErrors(['error' => 'Cannot delete attendance session that has student attendance records.']);
        }

        $attendanceSession->delete();

        return redirect()->route('school.attendance.index')
            ->with('success', 'Attendance session deleted successfully.');
    }

    /**
     * Mark attendance for students in a session.
     */
    public function markAttendance(Request $request, AttendanceSession $attendanceSession)
    {
        // Check if this is individual marking or bulk marking
        if ($request->has('student_id')) {
            // Individual marking
            $request->validate([
                'student_id' => 'required|exists:students,id',
                'status' => 'required|in:present,absent,late,sick',
                'time_in' => 'nullable|date_format:H:i',
                'time_out' => 'nullable|date_format:H:i',
                'notes' => 'nullable|string|max:255'
            ]);

            // Check if attendance record already exists
            $attendance = $attendanceSession->studentAttendances()->where('student_id', $request->student_id)->first();

            if ($attendance) {
                // Update existing record
                $attendance->update([
                    'status' => $request->status,
                    'time_in' => $request->time_in,
                    'time_out' => $request->time_out,
                    'notes' => $request->notes
                ]);
            } else {
                // Create new record
                $attendance = StudentAttendance::create([
                    'attendance_session_id' => $attendanceSession->id,
                    'student_id' => $request->student_id,
                    'status' => $request->status,
                    'time_in' => $request->time_in,
                    'time_out' => $request->time_out,
                    'notes' => $request->notes
                ]);
            }

            // Send notification if student is absent
            if ($request->status === 'absent') {
                try {
                    $student = Student::find($request->student_id);
                    if ($student) {
                        $notificationService = new ParentNotificationService();
                        $date = Carbon::parse($attendanceSession->date)->format('d/m/Y');
                        $title = 'Mwanafunzi Hayupo Shuleni';
                        $message = "Mwanafunzi {$student->first_name} {$student->last_name} hayupo shuleni tarehe {$date}.";
                        $notificationService->notifyStudentParents(
                            $student,
                            'student_absent',
                            $title,
                            $message,
                            ['attendance_session_id' => $attendanceSession->id, 'date' => $attendanceSession->date]
                        );
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send absence notification', ['error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Attendance updated successfully.'
            ]);
        } else {
            // Bulk marking or finalize
            if ($request->has('finalize')) {
                // Just finalize the session
                $attendanceSession->update(['status' => 'completed']);
                return response()->json([
                    'success' => true,
                    'message' => 'Attendance session finalized successfully.'
                ]);
            }

            // Bulk marking
            $request->validate([
                'status' => 'required|in:present,absent,late,sick',
                'time_in' => 'nullable|date_format:H:i',
                'time_out' => 'nullable|date_format:H:i',
                'notes' => 'nullable|string|max:255'
            ]);

            // Get all students for this session (same logic as show method)
            $students = Student::where('class_id', $attendanceSession->class_id)
                ->where('stream_id', $attendanceSession->stream_id)
                ->where('academic_year_id', $attendanceSession->academic_year_id)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();

            \Log::info('Bulk attendance marking started', [
                'session_id' => $attendanceSession->id,
                'students_from_db_count' => $students->count(),
                'status' => $request->status
            ]);

            // Delete existing attendance records for this session
            $attendanceSession->studentAttendances()->delete();

            $createdCount = 0;
            $absentStudents = [];
            // Create new attendance records for all students
            foreach ($students as $student) {
                StudentAttendance::create([
                    'attendance_session_id' => $attendanceSession->id,
                    'student_id' => $student->id,
                    'status' => $request->status,
                    'time_in' => $request->time_in,
                    'time_out' => $request->time_out,
                    'notes' => $request->notes
                ]);
                $createdCount++;
                
                // Track absent students for notifications
                if ($request->status === 'absent') {
                    $absentStudents[] = $student;
                }
            }

            // Send notifications for absent students
            if ($request->status === 'absent' && !empty($absentStudents)) {
                try {
                    $notificationService = new ParentNotificationService();
                    $date = Carbon::parse($attendanceSession->date)->format('d/m/Y');
                    
                    foreach ($absentStudents as $student) {
                        $title = 'Mwanafunzi Hayupo Shuleni';
                        $message = "Mwanafunzi {$student->first_name} {$student->last_name} hayupo shuleni tarehe {$date}.";
                        $notificationService->notifyStudentParents(
                            $student,
                            'student_absent',
                            $title,
                            $message,
                            ['attendance_session_id' => $attendanceSession->id, 'date' => $attendanceSession->date]
                        );
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send bulk absence notifications', ['error' => $e->getMessage()]);
                }
            }

            \Log::info('Bulk attendance marking completed', [
                'session_id' => $attendanceSession->id,
                'created_count' => $createdCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Attendance marked successfully for {$createdCount} students."
            ]);
        }
    }

    /**
     * Get attendance sessions data for DataTables.
     */
    public function data(Request $request)
    {
        $query = AttendanceSession::with(['class', 'stream', 'academicYear', 'creator'])
            ->orderBy('session_date', 'desc');

        // Apply default filter for current academic year if no academic year is selected
        if (!$request->filled('academic_year_id')) {
            $currentAcademicYear = AcademicYear::current();
            if ($currentAcademicYear) {
                $query->where('academic_year_id', $currentAcademicYear->id);
            }
        }

        // Apply filters if provided
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $searchValue = $request->search['value'];

                    $query->where(function ($q) use ($searchValue) {
                        $q->where('session_date', 'LIKE', '%' . $searchValue . '%')
                          ->orWhere('notes', 'LIKE', '%' . $searchValue . '%')
                          ->orWhereHas('class', function ($classQuery) use ($searchValue) {
                              $classQuery->where('name', 'LIKE', '%' . $searchValue . '%');
                          })
                          ->orWhereHas('stream', function ($streamQuery) use ($searchValue) {
                              $streamQuery->where('name', 'LIKE', '%' . $searchValue . '%');
                          })
                          ->orWhereHas('academicYear', function ($yearQuery) use ($searchValue) {
                              $yearQuery->where('year_name', 'LIKE', '%' . $searchValue . '%');
                          });
                    });
                }
            })
            ->addIndexColumn()
            ->addColumn('session_date_formatted', function ($session) {
                return Carbon::parse($session->session_date)->format('M d, Y');
            })
            ->addColumn('class_stream', function ($session) {
                return $session->class->name . ' - ' . $session->stream->name;
            })
            ->addColumn('academic_year_name', function ($session) {
                return $session->academicYear->year_name ?? 'N/A';
            })
            ->addColumn('status_badge', function ($session) {
                $statusColors = [
                    'active' => 'primary',
                    'completed' => 'success',
                    'cancelled' => 'danger'
                ];
                $color = $statusColors[$session->status] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($session->status) . '</span>';
            })
            ->addColumn('attendance_stats', function ($session) {
                $stats = $session->getAttendanceStats();
                return '<div class="text-center">
                    <small class="text-muted d-block">Total: ' . $stats['total_students'] . '</small>
                    <small class="text-success d-block">Present: ' . $stats['present'] . '</small>
                    <small class="text-danger d-block">Absent: ' . $stats['absent'] . '</small>
                </div>';
            })
            ->addColumn('actions', function ($session) {
                return view('school.attendance.partials.actions', compact('session'))->render();
            })
            ->rawColumns(['status_badge', 'attendance_stats', 'actions'])
            ->make(true);
    }

    /**
     * Get streams for a specific class via AJAX.
     */
    public function getStreamsByClass(Request $request)
    {
        $classId = $request->get('class_id');
        $search = $request->get('q', '');

        if (!$classId) {
            return response()->json(['streams' => []]);
        }

        $class = Classe::find($classId);

        if (!$class) {
            return response()->json(['streams' => []]);
        }

        $streamsQuery = $class->streams()->select('streams.id', 'streams.name');

        if (!empty($search)) {
            $streamsQuery->where('streams.name', 'LIKE', '%' . $search . '%');
        }

        $streams = $streamsQuery->get();

        return response()->json(['streams' => $streams]);
    }
}
