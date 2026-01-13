<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\School\Student;
use App\Models\School\Classe;
use App\Models\School\AcademicYear;
use App\Models\School\AttendanceSession;
use App\Models\School\StudentAttendance;
use App\Models\FeeInvoice;
use App\Models\School\SubjectTeacher;
use App\Models\School\ClassTeacher;
use App\Models\Hr\Employee;
use App\Models\School\Assignment;
use App\Models\School\LibraryMaterial;
use App\Models\School\AssignmentSubmission;
use App\Models\SchoolExamMark;
use App\Models\School\Subject;
use App\Models\SchoolExam;

class SchoolDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = session('branch_id') ?: $user->branch_id;

        // Get active academic year (current one)
        $activeAcademicYear = AcademicYear::where('company_id', $companyId)
            ->where('is_current', true)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->first();
        
        // If no current academic year, try to get one with status = 'active'
        if (!$activeAcademicYear) {
            $activeAcademicYear = AcademicYear::where('company_id', $companyId)
                ->where('status', 'active')
                ->when($branchId, function($query) use ($branchId) {
                    return $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                })
                ->first();
        }

        $academicYearId = $activeAcademicYear->id ?? null;

        // 1. Student Enrollment Data
        $enrollmentData = $this->getStudentEnrollment($companyId, $branchId, $academicYearId);
        
        // 2. Attendance Rate
        $attendanceData = $this->getAttendanceRate($companyId, $branchId, $academicYearId);
        
        // 3. Fee Payment Status
        $feePaymentData = $this->getFeePaymentStatus($companyId, $branchId, $academicYearId);
        
        // 4. Academic Performance
        $academicPerformanceData = $this->getAcademicPerformance($companyId, $branchId, $academicYearId);
        
        // 5. Teacher Performance
        $teacherPerformanceData = $this->getTeacherPerformance($companyId, $branchId, $academicYearId);
        
        // 6. Attendance Trend
        $attendanceTrendData = $this->getAttendanceTrend($companyId, $branchId, $academicYearId);
        
        // 7. Student-Teacher Ratio
        $studentTeacherRatio = $this->getStudentTeacherRatio($companyId, $branchId, $academicYearId);
        
        // 8. Additional Statistics
        $additionalStats = $this->getAdditionalStatistics($companyId, $branchId, $academicYearId);

        return view('school.dashboard.index', compact(
            'enrollmentData',
            'attendanceData',
            'feePaymentData',
            'academicPerformanceData',
            'teacherPerformanceData',
            'attendanceTrendData',
            'studentTeacherRatio',
            'additionalStats',
            'activeAcademicYear'
        ));
    }

    /**
     * Get student enrollment by class
     */
    private function getStudentEnrollment($companyId, $branchId, $academicYearId)
    {
        $students = Student::where('company_id', $companyId)
            ->where('status', 'active')
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->with('class')
            ->get();

        $totalStudents = $students->count();
        
        $enrollmentByClass = $students->groupBy('class_id')->map(function($classStudents, $classId) {
            $firstStudent = $classStudents->first();
            $class = $firstStudent ? $firstStudent->class : null;
            return [
                'class_name' => $class->name ?? 'Unassigned',
                'count' => $classStudents->count()
            ];
        })->sortBy('class_name')->values();

        return [
            'total' => $totalStudents,
            'by_class' => $enrollmentByClass
        ];
    }

    /**
     * Get attendance rate
     */
    private function getAttendanceRate($companyId, $branchId, $academicYearId)
    {
        $sessions = AttendanceSession::whereHas('class', function($query) use ($companyId, $branchId) {
                $query->where('company_id', $companyId);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->with(['studentAttendances'])
            ->get();

        $totalRecords = 0;
        $presentCount = 0;

        foreach ($sessions as $session) {
            $attendances = $session->studentAttendances;
            $totalRecords += $attendances->count();
            $presentCount += $attendances->where('status', 'present')->count();
        }

        $attendanceRate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;

        // Monthly attendance trend for chart
        $monthlyTrend = $this->getMonthlyAttendanceTrend($sessions);

        return [
            'rate' => $attendanceRate,
            'total_records' => $totalRecords,
            'present' => $presentCount,
            'monthly_trend' => $monthlyTrend
        ];
    }

    /**
     * Get monthly attendance trend
     */
    private function getMonthlyAttendanceTrend($sessions)
    {
        $monthlyData = $sessions->groupBy(function($session) {
            return Carbon::parse($session->session_date)->format('Y-m');
        })->map(function($monthSessions, $monthKey) {
            $totalRecords = 0;
            $presentCount = 0;

            foreach ($monthSessions as $session) {
                $attendances = $session->studentAttendances;
                $totalRecords += $attendances->count();
                $presentCount += $attendances->where('status', 'present')->count();
            }

            $rate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;

            return [
                'month' => Carbon::parse($monthKey . '-01')->format('M'),
                'rate' => $rate
            ];
        })->sortBy(function($item, $key) {
            return $key;
        })->values()->take(6);

        return $monthlyData;
    }

    /**
     * Get fee payment status
     */
    private function getFeePaymentStatus($companyId, $branchId, $academicYearId)
    {
        $invoices = FeeInvoice::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->get();

        $totalInvoices = $invoices->count();
        $fullyPaid = $invoices->where('status', 'paid')->count();
        $outstanding = $invoices->filter(function($invoice) {
            return $invoice->status !== 'paid' && $invoice->paid_amount < $invoice->total_amount;
        })->count();

        $totalAmount = $invoices->sum('total_amount');
        $paidAmount = $invoices->sum('paid_amount');
        $outstandingAmount = $totalAmount - $paidAmount;

        $paymentRate = $totalInvoices > 0 ? round(($fullyPaid / $totalInvoices) * 100, 1) : 0;

        return [
            'rate' => $paymentRate,
            'total_invoices' => $totalInvoices,
            'fully_paid' => $fullyPaid,
            'outstanding' => $outstanding,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $outstandingAmount
        ];
    }

    /**
     * Get academic performance (grades distribution)
     */
    private function getAcademicPerformance($companyId, $branchId, $academicYearId)
    {
        // Use assignment submissions as primary source for academic performance
        $submissions = AssignmentSubmission::whereHas('student', function($query) use ($companyId, $branchId) {
                $query->where('company_id', $companyId);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->whereHas('assignment', function($query) use ($academicYearId) {
                if ($academicYearId) {
                    $query->where('academic_year_id', $academicYearId);
                }
            })
            ->whereNotNull('percentage')
            ->get();

        $gradeCounts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
        $totalMarks = 0;
        $totalRecords = 0;

        foreach ($submissions as $submission) {
            $percentage = (float) $submission->percentage;
            
            if ($percentage >= 80) $gradeCounts['A']++;
            elseif ($percentage >= 70) $gradeCounts['B']++;
            elseif ($percentage >= 60) $gradeCounts['C']++;
            elseif ($percentage >= 50) $gradeCounts['D']++;
            else $gradeCounts['F']++;

            $totalMarks += $percentage;
            $totalRecords++;
        }

        $averagePerformance = $totalRecords > 0 ? round($totalMarks / $totalRecords, 1) : 0;

        return [
            'average' => $averagePerformance,
            'grade_counts' => $gradeCounts,
            'total_students' => $totalRecords
        ];
    }

    /**
     * Get teacher performance
     */
    private function getTeacherPerformance($companyId, $branchId, $academicYearId)
    {
        $teachers = SubjectTeacher::whereHas('employee', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->where('is_active', true)
            ->with(['employee'])
            ->get()
            ->unique('employee_id')
            ->take(4);

        $teacherData = [];
        foreach ($teachers as $teacher) {
            if (!$teacher->employee) continue;

            // Calculate performance based on assignments completed by students
            $assignments = Assignment::where('teacher_id', $teacher->employee_id)
                ->when($academicYearId, function($query) use ($academicYearId) {
                    return $query->where('academic_year_id', $academicYearId);
                })
                ->count();

            // Simple performance calculation (can be enhanced)
            $performance = min(100, ($assignments * 10) + 50);

            $teacherData[] = [
                'name' => $teacher->employee->first_name . ' ' . $teacher->employee->last_name,
                'performance' => round($performance, 0)
            ];
        }

        $averagePerformance = count($teacherData) > 0 
            ? round(collect($teacherData)->avg('performance'), 1) 
            : 0;

        return [
            'average' => $averagePerformance,
            'teachers' => $teacherData
        ];
    }

    /**
     * Get attendance trend (last 6 months)
     */
    private function getAttendanceTrend($companyId, $branchId, $academicYearId)
    {
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        
        $sessions = AttendanceSession::whereHas('class', function($query) use ($companyId, $branchId) {
                $query->where('company_id', $companyId);
                if ($branchId) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->where('session_date', '>=', $sixMonthsAgo)
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->with(['studentAttendances'])
            ->get();

        $monthlyData = $sessions->groupBy(function($session) {
            return Carbon::parse($session->session_date)->format('Y-m');
        })->map(function($monthSessions, $monthKey) {
            $totalRecords = 0;
            $presentCount = 0;

            foreach ($monthSessions as $session) {
                $attendances = $session->studentAttendances;
                $totalRecords += $attendances->count();
                $presentCount += $attendances->where('status', 'present')->count();
            }

            $rate = $totalRecords > 0 ? round(($presentCount / $totalRecords) * 100, 1) : 0;

            return [
                'month' => Carbon::parse($monthKey . '-01')->format('M'),
                'rate' => $rate
            ];
        })->sortBy(function($item, $key) {
            return $key;
        })->values();

        // Fill in missing months with 0
        $trendData = [];
        $monthlyDataIndexed = $monthlyData->keyBy('month');

        for ($i = 5; $i >= 0; $i--) {
            $monthName = Carbon::now()->subMonths($i)->format('M');
            
            $monthData = $monthlyDataIndexed->get($monthName);

            $trendData[] = [
                'month' => $monthName,
                'rate' => $monthData['rate'] ?? 0
            ];
        }

        return collect($trendData);
    }

    /**
     * Get student-teacher ratio
     */
    private function getStudentTeacherRatio($companyId, $branchId, $academicYearId)
    {
        $totalStudents = Student::where('company_id', $companyId)
            ->where('status', 'active')
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->count();

        $totalTeachers = SubjectTeacher::whereHas('employee', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->where('is_active', true)
            ->select('employee_id')
            ->distinct()
            ->count();

        $ratio = $totalTeachers > 0 ? round($totalStudents / $totalTeachers, 0) : 0;

        return [
            'students' => $totalStudents,
            'teachers' => $totalTeachers,
            'ratio' => $ratio . ':1'
        ];
    }

    /**
     * Get additional statistics
     */
    private function getAdditionalStatistics($companyId, $branchId, $academicYearId)
    {
        // Library statistics
        $totalBooks = LibraryMaterial::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->count();

        $borrowedBooks = LibraryMaterial::where('company_id', $companyId)
            ->where('status', 'borrowed')
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->count();

        // Assignment statistics
        $totalAssignments = Assignment::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->count();

        // Class statistics
        $totalClasses = Classe::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('is_active', true)
            ->count();

        // Teacher statistics
        $totalTeachers = SubjectTeacher::whereHas('employee', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->where('is_active', true)
            ->select('employee_id')
            ->distinct()
            ->count();

        // Subject/Course statistics
        $totalSubjects = Subject::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where('branch_id', $branchId);
            })
            ->where('is_active', true)
            ->count();

        // Exam statistics
        $totalExams = SchoolExam::where('company_id', $companyId)
            ->when($branchId, function($query) use ($branchId) {
                return $query->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            })
            ->when($academicYearId, function($query) use ($academicYearId) {
                return $query->where('academic_year_id', $academicYearId);
            })
            ->count();

        return [
            'library' => [
                'total_books' => $totalBooks,
                'borrowed_books' => $borrowedBooks,
                'available_books' => $totalBooks - $borrowedBooks
            ],
            'assignments' => $totalAssignments,
            'classes' => $totalClasses,
            'teachers' => $totalTeachers,
            'subjects' => $totalSubjects,
            'exams' => $totalExams
        ];
    }
}

