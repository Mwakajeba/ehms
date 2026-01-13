<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\School\AttendanceSession;
use App\Models\School\Student;
use App\Models\School\Classe;
use App\Models\School\Stream;
use App\Models\School\AcademicYear;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class AttendanceReportsController extends Controller
{
    /**
     * Display the attendance reports dashboard.
     */
    public function index(Request $request)
    {
        \Log::info('AttendanceReportsController@index called', [
            'user_id' => auth()->id(),
            'branch_id' => session('branch_id'),
            'request_data' => $request->all()
        ]);

        try {
            $branchId = session('branch_id');
            $companyId = auth()->user()->company_id;

            $classes = Classe::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    if ($branchId) {
                        $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                    }
                })
                ->orderBy('name')
                ->get();

            $streams = Stream::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    if ($branchId) {
                        $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                    }
                })
                ->orderBy('name')
                ->get();

            $academicYears = AcademicYear::where('company_id', $companyId)
                ->orderBy('year_name')
                ->get();

            // Get current academic year for default selection
            $currentAcademicYear = AcademicYear::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    if ($branchId) {
                        $query->where(function ($subQ) use ($branchId) {
                            $subQ->where('branch_id', $branchId)->orWhereNull('branch_id');
                        });
                    }
                })
                ->where('is_current', true)
                ->first();

            \Log::info('Data loaded successfully', [
                'classes_count' => $classes->count(),
                'streams_count' => $streams->count(),
                'academic_years_count' => $academicYears->count(),
                'current_academic_year_id' => $currentAcademicYear ? $currentAcademicYear->id : null
            ]);

            // Get summary statistics
            $summaryStats = $this->getSummaryStatistics($request);

            \Log::info('Summary stats calculated', ['summary_stats' => $summaryStats]);

            $view = view('school.reports.attendance-report', compact(
                'classes',
                'streams',
                'academicYears',
                'currentAcademicYear',
                'summaryStats'
            ));

            \Log::info('View created successfully', ['view_name' => $view->getName()]);

            return $view;

        } catch (\Exception $e) {
            \Log::error('Error in AttendanceReportsController@index', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a simple error response for debugging
            return response('Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get attendance summary data for DataTables.
     */
    public function getSummaryData(Request $request)
    {
        $query = AttendanceSession::with(['class', 'stream', 'academicYear', 'studentAttendances']);

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('session_date', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        return DataTables::of($query)
            ->addColumn('session_date_formatted', function ($session) {
                return Carbon::parse($session->session_date)->format('M d, Y');
            })
            ->addColumn('class_name', function ($session) {
                return $session->class->name;
            })
            ->addColumn('stream_name', function ($session) {
                return $session->stream->name;
            })
            ->addColumn('academic_year_name', function ($session) {
                return $session->academicYear->year_name ?? 'N/A';
            })
            ->addColumn('total_students', function ($session) {
                $stats = $session->getAttendanceStats();
                return $stats['total_students'];
            })
            ->addColumn('present', function ($session) {
                $stats = $session->getAttendanceStats();
                return $stats['present'];
            })
            ->addColumn('absent', function ($session) {
                $stats = $session->getAttendanceStats();
                return $stats['absent'];
            })
            ->addColumn('late', function ($session) {
                $stats = $session->getAttendanceStats();
                return $stats['late'];
            })
            ->addColumn('sick', function ($session) {
                $stats = $session->getAttendanceStats();
                return $stats['sick'];
            })
            ->addColumn('attendance_rate', function ($session) {
                $stats = $session->getAttendanceStats();
                $total = $stats['total_students'];
                $present = $stats['present'];
                return $total > 0 ? round(($present / $total) * 100, 1) . '%' : '0%';
            })
            ->make(true);
    }

    /**
     * Get summary statistics for the dashboard
     */
    private function getSummaryStatistics(Request $request)
    {
        $branchId = session('branch_id');
        $companyId = auth()->user()->company_id;

        $query = AttendanceSession::whereHas('class', function ($q) use ($companyId, $branchId) {
            $q->where('company_id', $companyId);
            if ($branchId) {
                $q->where(function ($subQ) use ($branchId) {
                    $subQ->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }
        });

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('session_date', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        $sessions = $query->with('studentAttendances')->get();

        $totalSessions = $sessions->count();
        $totalStudents = $sessions->sum(function ($session) {
            return $session->studentAttendances->count();
        });

        $totalPresent = $sessions->sum(function ($session) {
            return $session->studentAttendances->where('status', 'present')->count();
        });

        $totalAbsent = $sessions->sum(function ($session) {
            return $session->studentAttendances->where('status', 'absent')->count();
        });

        $totalLate = $sessions->sum(function ($session) {
            return $session->studentAttendances->where('status', 'late')->count();
        });

        $totalSick = $sessions->sum(function ($session) {
            return $session->studentAttendances->where('status', 'sick')->count();
        });

        $overallAttendanceRate = $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100, 1) : 0;

        return [
            'total_sessions' => $totalSessions,
            'total_students' => $totalStudents,
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_late' => $totalLate,
            'total_sick' => $totalSick,
            'overall_attendance_rate' => $overallAttendanceRate
        ];
    }

    /**
     * Get attendance trends data for charts
     */
    public function getTrendsData(Request $request)
    {
        try {
            $branchId = session('branch_id');
            $companyId = auth()->user()->company_id;
            $days = $request->get('days', 30);
            
            // Parse dates - use request dates if provided, otherwise default to last 30 days
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
            } else {
                $startDate = Carbon::now()->subDays($days)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
            }

            \Log::info('getTrendsData called', [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'filters' => $request->only(['class_id', 'stream_id', 'academic_year_id'])
            ]);

            $query = AttendanceSession::where('session_date', '>=', $startDate)
                ->where('session_date', '<=', $endDate)
                ->whereHas('class', function ($q) use ($companyId, $branchId) {
                    $q->where('company_id', $companyId);
                    if ($branchId) {
                        $q->where(function ($subQ) use ($branchId) {
                            $subQ->where('branch_id', $branchId)->orWhereNull('branch_id');
                        });
                    }
                });

            // Apply filters
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }
            if ($request->filled('stream_id')) {
                $query->where('stream_id', $request->stream_id);
            }
            if ($request->filled('academic_year_id')) {
                $query->where('academic_year_id', $request->academic_year_id);
            }

            $sessions = $query->with('studentAttendances')
                ->orderBy('session_date')
                ->get();

            \Log::info('getTrendsData sessions found', ['count' => $sessions->count()]);

            $trends = $sessions->groupBy(function ($session) {
                    return $session->session_date->format('Y-m-d');
                })
                ->map(function ($sessions, $date) {
                    $totalStudents = $sessions->sum(function ($session) {
                        return $session->studentAttendances->count();
                    });
                    $totalPresent = $sessions->sum(function ($session) {
                        return $session->studentAttendances->where('status', 'present')->count();
                    });

                    return [
                        'date' => $date,
                        'attendance_rate' => $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100, 1) : 0,
                        'total_sessions' => $sessions->count(),
                        'total_students' => $totalStudents,
                        'present' => $totalPresent
                    ];
                })
                ->values()
                ->sortBy('date')
                ->values();

            \Log::info('getTrendsData trends prepared', ['count' => $trends->count(), 'data' => $trends->toArray()]);

            $response = response()->json($trends);
            
            // Add CORS headers if needed
            $response->header('Content-Type', 'application/json');
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
            
            return $response;
        } catch (\Exception $e) {
            \Log::error('Error in getTrendsData', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get class-wise attendance statistics
     */
    public function getClassWiseStats(Request $request)
    {
        try {
            $branchId = session('branch_id');
            $companyId = auth()->user()->company_id;
            $startDate = $request->get('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->startOfMonth();
            $endDate = $request->get('end_date') ? Carbon::parse($request->end_date) : Carbon::now()->endOfMonth();

            $query = Classe::where('company_id', $companyId);

            if ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            }

            // Apply class filter if provided
            if ($request->filled('class_id')) {
                $query->where('id', $request->class_id);
            }

            $stats = $query->with(['attendanceSessions' => function ($query) use ($startDate, $endDate, $request) {
                $query->whereBetween('session_date', [$startDate, $endDate]);
                
                if ($request->filled('stream_id')) {
                    $query->where('stream_id', $request->stream_id);
                }
                if ($request->filled('academic_year_id')) {
                    $query->where('academic_year_id', $request->academic_year_id);
                }
                
                $query->with('studentAttendances');
            }])
            ->get()
            ->map(function ($class) {
                $sessions = $class->attendanceSessions;
                $totalStudents = $sessions->sum(function ($session) {
                    return $session->studentAttendances->count();
                });
                $totalPresent = $sessions->sum(function ($session) {
                    return $session->studentAttendances->where('status', 'present')->count();
                });

                return [
                    'class_name' => $class->name,
                    'total_sessions' => $sessions->count(),
                    'total_students' => $totalStudents,
                    'present' => $totalPresent,
                    'attendance_rate' => $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100, 1) : 0
                ];
            })
            ->filter(function ($stat) {
                return $stat['total_sessions'] > 0; // Only return classes with sessions
            })
            ->values();

            return response()->json($stats);
        } catch (\Exception $e) {
            \Log::error('Error in getClassWiseStats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([], 500);
        }
    }

    /**
     * Get students for a specific attendance session
     */
    public function getSessionStudents(Request $request, $sessionId)
    {
        try {
            $session = AttendanceSession::with(['studentAttendances.student', 'class', 'stream', 'academicYear'])
                ->findOrFail($sessionId);

            $students = $session->studentAttendances->map(function ($attendance) {
                return [
                    'id' => $attendance->student->id,
                    'admission_number' => $attendance->student->admission_number,
                    'first_name' => $attendance->student->first_name,
                    'last_name' => $attendance->student->last_name,
                    'full_name' => $attendance->student->first_name . ' ' . $attendance->student->last_name,
                    'status' => $attendance->status,
                    'formatted_status' => $attendance->formatted_status,
                    'time_in' => $attendance->time_in ? $attendance->time_in->format('H:i') : null,
                    'time_out' => $attendance->time_out ? $attendance->time_out->format('H:i') : null,
                    'notes' => $attendance->notes,
                    'status_badge' => $attendance->status_badge
                ];
            });

            return response()->json([
                'success' => true,
                'session' => [
                    'id' => $session->id,
                    'session_date' => $session->session_date->format('M d, Y'),
                    'class_name' => $session->class->name,
                    'stream_name' => $session->stream->name,
                    'academic_year' => $session->academicYear->year_name ?? 'N/A'
                ],
                'students' => $students,
                'summary' => [
                    'total_students' => $students->count(),
                    'present' => $students->where('status', 'present')->count(),
                    'absent' => $students->where('status', 'absent')->count(),
                    'late' => $students->where('status', 'late')->count(),
                    'sick' => $students->where('status', 'sick')->count(),
                    'attendance_rate' => $students->count() > 0 ? round(($students->where('status', 'present')->count() / $students->count()) * 100, 1) : 0
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching session students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student attendance report
     */
    public function getStudentReport(Request $request)
    {
        $studentId = $request->get('student_id');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $student = Student::with(['studentAttendances' => function ($query) use ($startDate, $endDate) {
            $query->whereHas('attendanceSession', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('session_date', [$startDate, $endDate]);
            })->with('attendanceSession');
        }])->findOrFail($studentId);

        $attendances = $student->studentAttendances;

        $stats = [
            'total_days' => $attendances->count(),
            'present' => $attendances->where('status', 'present')->count(),
            'absent' => $attendances->where('status', 'absent')->count(),
            'late' => $attendances->where('status', 'late')->count(),
            'sick' => $attendances->where('status', 'sick')->count(),
        ];

        $stats['attendance_rate'] = $stats['total_days'] > 0
            ? round(($stats['present'] / $stats['total_days']) * 100, 1)
            : 0;

        return response()->json([
            'student' => $student,
            'stats' => $stats,
            'attendances' => $attendances->map(function ($attendance) {
                return [
                    'date' => $attendance->attendanceSession->session_date->format('Y-m-d'),
                    'status' => $attendance->status,
                    'formatted_status' => $attendance->formatted_status,
                    'time_in' => $attendance->time_in ? $attendance->time_in->format('H:i') : null,
                    'time_out' => $attendance->time_out ? $attendance->time_out->format('H:i') : null,
                    'notes' => $attendance->notes
                ];
            })
        ]);
    }

    /**
     * Export attendance report
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'summary');
        $format = $request->get('format', 'csv');

        switch ($format) {
            case 'pdf':
                return $this->exportPdf($request, $type);
            case 'excel':
                return $this->exportExcel($request, $type);
            case 'csv':
            default:
                return $this->exportCsv($request, $type);
        }
    }

    private function exportPdf(Request $request, $type)
    {
        $data = $this->getExportData($request, $type);

        $pdf = \PDF::loadView('school.reports.attendance-pdf', [
            'data' => $data,
            'type' => $type,
            'filters' => $request->all(),
            'generated_at' => now()
        ]);

        $filename = 'attendance_' . $type . '_report_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }

    private function exportExcel(Request $request, $type)
    {
        $data = $this->getExportData($request, $type);

        return \Excel::download(new class($data, $type, $request->all()) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
            private $data;
            private $type;
            private $filters;

            public function __construct($data, $type, $filters)
            {
                $this->data = $data;
                $this->type = $type;
                $this->filters = $filters;
            }

            public function collection()
            {
                return collect($this->data);
            }

            public function headings(): array
            {
                switch ($this->type) {
                    case 'summary':
                        return ['Date', 'Class', 'Stream', 'Academic Year', 'Total Students', 'Present', 'Absent', 'Late', 'Sick', 'Attendance Rate'];
                    case 'student':
                        return ['Student Name', 'Date', 'Status', 'Time In', 'Time Out', 'Notes'];
                    case 'class':
                        return ['Class', 'Total Sessions', 'Total Students', 'Present', 'Attendance Rate'];
                    default:
                        return [];
                }
            }
        }, 'attendance_' . $type . '_report_' . date('Y-m-d_H-i-s') . '.xlsx');
    }

    private function exportCsv(Request $request, $type)
    {
        $data = $this->getExportData($request, $type);

        $csvData = [];
        $headers = [];

        switch ($type) {
            case 'summary':
                $headers = ['Date', 'Class', 'Stream', 'Academic Year', 'Total Students', 'Present', 'Absent', 'Late', 'Sick', 'Attendance Rate'];
                foreach ($data as $row) {
                    $csvData[] = [
                        $row['session_date_formatted'] ?? '',
                        $row['class_name'] ?? '',
                        $row['stream_name'] ?? '',
                        $row['academic_year_name'] ?? '',
                        $row['total_students'] ?? 0,
                        $row['present'] ?? 0,
                        $row['absent'] ?? 0,
                        $row['late'] ?? 0,
                        $row['sick'] ?? 0,
                        $row['attendance_rate'] ?? '0%'
                    ];
                }
                break;
            case 'student':
                $headers = ['Student Name', 'Date', 'Status', 'Time In', 'Time Out', 'Notes'];
                foreach ($data['attendances'] as $attendance) {
                    $csvData[] = [
                        $data['student']['first_name'] . ' ' . $data['student']['last_name'],
                        $attendance['date'],
                        $attendance['formatted_status'],
                        $attendance['time_in'] ?: '',
                        $attendance['time_out'] ?: '',
                        $attendance['notes'] ?: ''
                    ];
                }
                break;
            case 'class':
                $headers = ['Class', 'Total Sessions', 'Total Students', 'Present', 'Attendance Rate'];
                foreach ($data as $row) {
                    $csvData[] = [
                        $row['class_name'] ?? '',
                        $row['total_sessions'] ?? 0,
                        $row['total_students'] ?? 0,
                        $row['present'] ?? 0,
                        $row['attendance_rate'] ?? 0
                    ];
                }
                break;
        }

        return $this->generateCSV(array_merge([$headers], $csvData), 'attendance_' . $type . '_report_' . date('Y-m-d_H-i-s') . '.csv');
    }

    private function getExportData(Request $request, $type)
    {
        switch ($type) {
            case 'summary':
                return $this->getSummaryDataForExport($request);
            case 'student':
                $studentId = $request->get('student_id');
                $data = json_decode($this->getStudentReport($request)->getContent(), true);
                return $data;
            case 'class':
                return json_decode($this->getClassWiseStats($request)->getContent(), true);
            default:
                return [];
        }
    }

    private function getSummaryDataForExport(Request $request)
    {
        $query = AttendanceSession::with(['class', 'stream', 'academicYear', 'studentAttendances']);

        // Apply filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('session_date', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);
        }

        return $query->get()->map(function ($session) {
            $stats = $session->getAttendanceStats();
            return [
                'session_date_formatted' => Carbon::parse($session->session_date)->format('M d, Y'),
                'class_name' => $session->class->name,
                'stream_name' => $session->stream->name,
                'academic_year_name' => $session->academicYear->year_name ?? 'N/A',
                'total_students' => $stats['total_students'],
                'present' => $stats['present'],
                'absent' => $stats['absent'],
                'late' => $stats['late'],
                'sick' => $stats['sick'],
                'attendance_rate' => $stats['total_students'] > 0 ? round(($stats['present'] / $stats['total_students']) * 100, 1) . '%' : '0%'
            ];
        })->toArray();
    }

    /**
     * Generate CSV file from data
     */
    private function generateCSV($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');

            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get students for dropdown selection
     */
    public function getStudents(Request $request)
    {
        $query = Student::with(['class', 'stream'])
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('stream_id')) {
            $query->where('stream_id', $request->stream_id);
        }

        $students = $query->get()->map(function ($student) {
            return [
                'id' => $student->id,
                'text' => $student->first_name . ' ' . $student->last_name . ' (' . $student->admission_number . ')',
                'class' => $student->class->name ?? 'N/A',
                'stream' => $student->stream->name ?? 'N/A'
            ];
        });

        return response()->json($students);
    }

    /**
     * Monthly Attendance Trend Analysis Report
     */
    public function monthlyAttendanceTrend(Request $request)
    {
        try {
            $branchId = session('branch_id');
            $companyId = auth()->user()->company_id;

            // Get filters
            $academicYearId = $request->academic_year_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;
            $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfMonth() : Carbon::now()->subMonths(11)->startOfMonth();
            $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfMonth() : Carbon::now()->endOfMonth();

            // Get classes for filter
            $classes = Classe::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    if ($branchId) {
                        $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                    }
                })
                ->orderBy('name')
                ->get();

            // Get streams for filter
            $streams = Stream::where('company_id', $companyId)
                ->where(function ($query) use ($branchId) {
                    if ($branchId) {
                        $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                    }
                })
                ->orderBy('name')
                ->get();

            // Get academic years for filter
            $academicYears = AcademicYear::where('company_id', $companyId)
                ->orderBy('year_name', 'desc')
                ->get();

            // Get current academic year for default
            $currentAcademicYear = AcademicYear::where('company_id', $companyId)
                ->where('is_current', true)
                ->first();

            if (!$academicYearId && $currentAcademicYear) {
                $academicYearId = $currentAcademicYear->id;
            }

            // Handle exports
            if ($request->has('export')) {
                return $this->exportMonthlyAttendanceTrend($request);
            }

            // Get monthly attendance trend data
            $trendData = $this->getMonthlyAttendanceTrendData($branchId, $companyId, $academicYearId, $classId, $streamId, $startDate, $endDate);

            return view('school.reports.monthly-attendance-trend', compact(
                'classes',
                'streams',
                'academicYears',
                'currentAcademicYear',
                'trendData',
                'academicYearId',
                'classId',
                'streamId',
                'startDate',
                'endDate'
            ));
        } catch (\Exception $e) {
            \Log::error('Monthly Attendance Trend Report error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request' => $request->all()
            ]);

            return redirect()->back()->with('error', 'An error occurred while generating the report. Please try again.');
        }
    }

    /**
     * Get monthly attendance trend data
     */
    private function getMonthlyAttendanceTrendData($branchId, $companyId, $academicYearId, $classId, $streamId, $startDate, $endDate)
    {
        // Build query for attendance sessions
        $query = AttendanceSession::whereBetween('session_date', [$startDate, $endDate])
            ->whereHas('class', function ($q) use ($companyId, $branchId) {
                $q->where('company_id', $companyId);
                if ($branchId) {
                    $q->where(function ($subQ) use ($branchId) {
                        $subQ->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                }
            })
            ->with(['studentAttendances', 'class', 'stream', 'academicYear']);

        // Apply filters
        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        if ($streamId) {
            $query->where('stream_id', $streamId);
        }

        $sessions = $query->get();

        // Group by month and calculate statistics
        $monthlyData = $sessions->groupBy(function ($session) {
            return $session->session_date->format('Y-m');
        })->map(function ($monthSessions, $monthKey) {
            $totalSessions = $monthSessions->count();

            // Get unique students for this month (from all sessions in the month)
            $uniqueStudents = $monthSessions->flatMap(function ($session) {
                return $session->studentAttendances->pluck('student_id');
            })->unique()->count();

            // Calculate attendance statistics
            $totalPresent = $monthSessions->sum(function ($session) {
                return $session->studentAttendances->where('status', 'present')->count();
            });
            $totalAbsent = $monthSessions->sum(function ($session) {
                return $session->studentAttendances->where('status', 'absent')->count();
            });
            $totalLate = $monthSessions->sum(function ($session) {
                return $session->studentAttendances->where('status', 'late')->count();
            });
            $totalSick = $monthSessions->sum(function ($session) {
                return $session->studentAttendances->where('status', 'sick')->count();
            });

            $totalAttendanceRecords = $totalPresent + $totalAbsent + $totalLate + $totalSick;
            $attendanceRate = $totalAttendanceRecords > 0 ? round(($totalPresent / $totalAttendanceRecords) * 100, 2) : 0;

            return [
                'month' => $monthKey,
                'month_name' => Carbon::parse($monthKey . '-01')->format('F Y'),
                'total_sessions' => $totalSessions,
                'unique_students' => $uniqueStudents,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_late' => $totalLate,
                'total_sick' => $totalSick,
                'total_records' => $totalAttendanceRecords,
                'attendance_rate' => $attendanceRate,
            ];
        })->sortBy('month')->values();

        // Calculate grand totals
        $grandTotals = [
            'total_sessions' => $monthlyData->sum('total_sessions'),
            'total_students' => $monthlyData->max('unique_students') ?? 0,
            'total_present' => $monthlyData->sum('total_present'),
            'total_absent' => $monthlyData->sum('total_absent'),
            'total_late' => $monthlyData->sum('total_late'),
            'total_sick' => $monthlyData->sum('total_sick'),
            'total_records' => $monthlyData->sum('total_records'),
            'overall_attendance_rate' => $monthlyData->sum('total_records') > 0 
                ? round(($monthlyData->sum('total_present') / $monthlyData->sum('total_records')) * 100, 2) 
                : 0,
        ];

        return [
            'monthly_data' => $monthlyData,
            'grand_totals' => $grandTotals,
        ];
    }

    /**
     * Export monthly attendance trend report
     */
    private function exportMonthlyAttendanceTrend(Request $request)
    {
        try {
            $branchId = session('branch_id');
            $companyId = auth()->user()->company_id;
            $exportType = $request->export ?? 'pdf';

            // Get filters
            $academicYearId = $request->academic_year_id;
            $classId = $request->class_id;
            $streamId = $request->stream_id;
            $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfMonth() : Carbon::now()->subMonths(11)->startOfMonth();
            $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfMonth() : Carbon::now()->endOfMonth();

            // Get trend data
            $trendData = $this->getMonthlyAttendanceTrendData($branchId, $companyId, $academicYearId, $classId, $streamId, $startDate, $endDate);

            // Get filter labels for display
            $academicYear = $academicYearId ? AcademicYear::find($academicYearId) : null;
            $class = $classId ? Classe::find($classId) : null;
            $stream = $streamId ? Stream::find($streamId) : null;
            $company = auth()->user()->company;

            if ($exportType === 'pdf') {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('school.reports.exports.monthly-attendance-trend-pdf', compact(
                    'trendData',
                    'academicYear',
                    'class',
                    'stream',
                    'startDate',
                    'endDate',
                    'company'
                ));
                $pdf->setPaper('a4', 'landscape');
                $filename = 'monthly-attendance-trend-report-' . date('Y-m-d-H-i-s') . '.pdf';
                return $pdf->download($filename);
            } elseif ($exportType === 'excel') {
                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\MonthlyAttendanceTrendExport($trendData, $academicYear, $class, $stream, $startDate, $endDate, $company), 
                    'monthly-attendance-trend-report-' . date('Y-m-d-H-i-s') . '.xlsx'
                );
            }

            return redirect()->back()->with('error', 'Invalid export type');
        } catch (\Exception $e) {
            \Log::error('Monthly Attendance Trend Export error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'An error occurred while exporting the report. Please try again.');
        }
    }
}