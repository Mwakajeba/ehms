<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\College\Student;
use App\Models\College\Department;
use App\Models\College\Program;
use App\Models\College\FeeInvoice;
use App\Models\College\Course;
use App\Models\College\ExamSchedule;
use App\Models\College\Semester;
use App\Models\School\AcademicYear;

class CollegeController extends Controller
{
    /**
     * Display the college management dashboard.
     */
    public function index()
    {
        // Get current company and branch
        $companyId = auth()->user()->company_id;
        $branchId = session('branch_id');

        // College Statistics
        $statistics = [
            'total_students' => Student::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->count(),

            'active_students' => Student::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('status', 'active')
                ->count(),

            'departments' => Department::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->count(),

            'programs' => Program::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->count(),

            'fee_invoices' => FeeInvoice::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->count(),

            'paid_invoices' => FeeInvoice::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('status', 'paid')
                ->count(),

            'unpaid_invoices' => FeeInvoice::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where(function($query) {
                    $query->where('status', 'unpaid')
                          ->orWhereRaw('total_amount > paid_amount');
                })
                ->count(),

            'courses' => Course::where('status', 'active')->count(),

            'lecturers' => 0, // Lecturer module removed

            'exams' => ExamSchedule::where('branch_id', $branchId)->count(),

            'academic_years' => AcademicYear::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->count(),

            'semesters' => Semester::where('status', 'active')->count(),
        ];

        // College Analytics
        $analytics = [
            'new_enrollments' => Student::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->whereYear('created_at', now()->year)
                ->count(),

            'graduates' => Student::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('status', 'graduated')
                ->count(),

            'fee_collection' => FeeInvoice::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->sum('paid_amount'),

            'outstanding' => FeeInvoice::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where(function($query) {
                    $query->where('status', 'unpaid')
                          ->orWhereRaw('total_amount > paid_amount');
                })
                ->selectRaw('SUM(total_amount - paid_amount) as outstanding')
                ->first()
                ->outstanding ?? 0,

            'active_sessions' => AcademicYear::where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('is_current', true)
                ->count(),
        ];

        return view('college.index', compact('statistics', 'analytics'));
    }
}