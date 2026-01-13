<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Hr\Employee;
use App\Models\Hr\Department;
use App\Models\Hr\Position;
use App\Models\Hr\JobGrade;
use App\Models\Hr\Contract;
use App\Models\Hr\EmployeeCompliance;
use App\Models\Hr\WorkSchedule;
use App\Models\Hr\Shift;
use App\Models\Hr\EmployeeSchedule;
use App\Models\Hr\Attendance;
use App\Models\Hr\OvertimeRule;
use App\Models\Hr\OvertimeRequest;
use App\Models\Hr\HolidayCalendar;
use App\Models\Hr\BiometricDevice;
use App\Models\Hr\BiometricLog;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\SalaryAdvance;
use App\Models\Hr\ExternalLoan;
use App\Models\Hr\HeslbLoan;
use App\Models\Hr\EmployeeSalaryStructure;
use App\Models\Payroll;
use Carbon\Carbon;

class HrPayrollController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $company = $user->company;
        $companyId = current_company_id();

        // Get user's permitted branches
        $permittedBranchIds = collect($user->branches ?? [])->pluck('id')->all();
        if (empty($permittedBranchIds) && $user->branch_id) {
            $permittedBranchIds = [(int)$user->branch_id];
        }

        // Get selected branch from session or default
        $selectedBranchId = session('branch_id');
        if (!$selectedBranchId && count($permittedBranchIds) === 1) {
            $selectedBranchId = $permittedBranchIds[0];
        }

        $selectedBranch = null;
        if ($selectedBranchId) {
            $selectedBranch = $company->branches()->find($selectedBranchId);
        }

        // Get dashboard statistics for counts
        $stats = $this->getDashboardStatistics($companyId);

        return view('hr-payroll.index', compact('company', 'selectedBranch', 'permittedBranchIds', 'stats'));
    }

    /**
     * Get dashboard statistics for module index pages
     */
    public function getDashboardStatistics($companyId)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $thisYear = Carbon::now()->year;

        return [
            // Employee Management
            'employees' => [
                'total' => Employee::where('company_id', $companyId)->count(),
                'active' => Employee::where('company_id', $companyId)->where('status', 'active')->count(),
                'inactive' => Employee::where('company_id', $companyId)->where('status', '!=', 'active')->count(),
                'new_this_month' => Employee::where('company_id', $companyId)->whereMonth('created_at', $today->month)->count(),
            ],

            // Organizational Structure
            'departments' => [
                'total' => Department::where('company_id', $companyId)->count(),
                'active' => Department::where('company_id', $companyId)->count(), // All departments are considered active
            ],
            'positions' => [
                'total' => Position::where('company_id', $companyId)->count(),
                'filled' => Position::where('company_id', $companyId)
                    ->where(function($q) {
                        // Check if position has headcount data and is filled
                        $q->whereRaw('COALESCE(filled_headcount, 0) >= COALESCE(approved_headcount, 1)')
                          // Or if it has employees assigned (for positions without headcount tracking)
                          ->orWhereHas('employees');
                    })
                    ->count(),
                'vacant' => Position::where('company_id', $companyId)
                    ->where(function($q) {
                        // Check if position has headcount data and is vacant
                        $q->whereRaw('COALESCE(filled_headcount, 0) < COALESCE(approved_headcount, 1)')
                          // Or if it doesn't have employees assigned
                          ->whereDoesntHave('employees');
                    })
                    ->count(),
            ],

            // Job Grades
            'job_grades' => [
                'total' => JobGrade::where('company_id', $companyId)->count(),
                'active' => JobGrade::where('company_id', $companyId)->where('is_active', true)->count(),
            ],

            // Contracts
            'contracts' => [
                'total' => Contract::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->count(),
                'active' => Contract::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                    ->where('start_date', '<=', $today)
                    ->where(function($q) use ($today) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
                    })
                    ->where('status', 'active')
                    ->count(),
                'expiring_soon' => Contract::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                    ->whereNotNull('end_date')
                    ->where('end_date', '>=', $today)
                    ->where('end_date', '<=', $today->copy()->addDays(30))
                    ->where('status', 'active')
                    ->count(),
            ],

            // Compliance
            'compliance' => [
                'total' => EmployeeCompliance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->count(),
                'valid' => EmployeeCompliance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                    ->where('is_valid', true)
                    ->where(function($q) use ($today) {
                        $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', $today);
                    })
                    ->count(),
                'expired' => EmployeeCompliance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                    ->where(function($q) use ($today) {
                        $q->where('is_valid', false)
                          ->orWhere(function($q2) use ($today) {
                              $q2->whereNotNull('expiry_date')->where('expiry_date', '<', $today);
                          });
                    })
                    ->count(),
            ],

            // Work Schedules & Shifts
            'work_schedules' => [
                'total' => WorkSchedule::where('company_id', $companyId)->count(),
                'active' => WorkSchedule::where('company_id', $companyId)->where('is_active', true)->count(),
            ],
            'shifts' => [
                'total' => Shift::where('company_id', $companyId)->count(),
                'active' => Shift::where('company_id', $companyId)->where('is_active', true)->count(),
            ],
            'employee_schedules' => [
                'total' => EmployeeSchedule::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->count(),
                'active' => EmployeeSchedule::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->where('effective_date', '<=', $today)
                ->where(function($q) use ($today) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
                })
                ->count(),
            ],

            // Attendance
            'attendance' => [
                'today' => Attendance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->whereDate('attendance_date', $today)->count(),
                'this_month' => Attendance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->whereMonth('attendance_date', $today->month)->count(),
                'present_today' => Attendance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->whereDate('attendance_date', $today)->where('status', 'present')->count(),
                'absent_today' => Attendance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->whereDate('attendance_date', $today)->where('status', 'absent')->count(),
            ],

            // Overtime
            'overtime_rules' => [
                'total' => OvertimeRule::where('company_id', $companyId)->count(),
                'active' => OvertimeRule::where('company_id', $companyId)->where('is_active', true)->count(),
            ],
            'overtime_requests' => [
                'total' => OvertimeRequest::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->count(),
                'pending' => OvertimeRequest::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('status', 'pending')->count(),
                'approved' => OvertimeRequest::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('status', 'approved')->count(),
            ],

            // Holiday Calendars
            'holiday_calendars' => [
                'total' => HolidayCalendar::where('company_id', $companyId)->count(),
                'active' => HolidayCalendar::where('company_id', $companyId)->where('is_active', true)->count(),
            ],

            // Biometric Devices
            'biometric_devices' => [
                'total' => BiometricDevice::where('company_id', $companyId)->count(),
                'active' => BiometricDevice::where('company_id', $companyId)->where('is_active', true)->count(),
                'pending_logs' => BiometricLog::whereHas('device', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('status', 'pending')->count(),
            ],

            // Leave Management
            'leave' => [
                'total_requests' => LeaveRequest::where('company_id', $companyId)->count(),
                'pending' => LeaveRequest::where('company_id', $companyId)->where('status', 'pending')->count(),
                'approved' => LeaveRequest::where('company_id', $companyId)->where('status', 'approved')->count(),
                'this_month' => LeaveRequest::where('company_id', $companyId)
                    ->whereMonth('requested_at', $today->month)
                    ->count(),
            ],

            // Salary Advances
            'salary_advances' => [
                'total' => SalaryAdvance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->count(),
                'outstanding' => SalaryAdvance::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('is_active', true)->count(),
            ],

            // External Loans
            'external_loans' => [
                'total' => ExternalLoan::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->count(),
                'active' => ExternalLoan::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('is_active', true)->count(),
            ],
            
            // HESLB Loans
            'heslb_loans' => [
                'total' => HeslbLoan::where('company_id', $companyId)->count(),
                'active' => HeslbLoan::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->where('outstanding_balance', '>', 0)
                    ->count(),
            ],

            // Payroll
            'payrolls' => [
                'total' => Payroll::where('company_id', $companyId)->count(),
                'draft' => Payroll::where('company_id', $companyId)->where('status', 'draft')->count(),
                'processing' => Payroll::where('company_id', $companyId)->where('status', 'processing')->count(),
                'completed' => Payroll::where('company_id', $companyId)->where('status', 'completed')->count(),
                'this_month' => Payroll::where('company_id', $companyId)
                    ->where('year', $thisYear)
                    ->where('month', $today->month)
                    ->count(),
            ],

            // Phase 3: Payroll Enhancement
            'payroll_calendars' => [
                'total' => \App\Models\Hr\PayrollCalendar::where('company_id', $companyId)->count(),
                'this_year' => \App\Models\Hr\PayrollCalendar::where('company_id', $companyId)
                    ->where('calendar_year', $thisYear)
                    ->count(),
                'locked' => \App\Models\Hr\PayrollCalendar::where('company_id', $companyId)
                    ->where('is_locked', true)
                    ->count(),
            ],
            'pay_groups' => [
                'total' => \App\Models\Hr\PayGroup::where('company_id', $companyId)->count(),
                'active' => \App\Models\Hr\PayGroup::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->count(),
            ],
            'salary_components' => [
                'total' => \App\Models\Hr\SalaryComponent::where('company_id', $companyId)->count(),
                'earnings' => \App\Models\Hr\SalaryComponent::where('company_id', $companyId)
                    ->where('component_type', 'earning')
                    ->where('is_active', true)
                    ->count(),
                'deductions' => \App\Models\Hr\SalaryComponent::where('company_id', $companyId)
                    ->where('component_type', 'deduction')
                    ->where('is_active', true)
                    ->count(),
            ],
            'employee_salary_structures' => [
                'total' => \App\Models\Hr\EmployeeSalaryStructure::whereHas('employee', function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->where('effective_date', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', $today);
                })
                ->distinct('employee_id')
                ->count('employee_id'),
                'with_structure' => Employee::where('company_id', $companyId)
                    ->whereHas('salaryStructures', function ($q) use ($today) {
                        $q->where('effective_date', '<=', $today)
                          ->where(function ($query) use ($today) {
                              $query->whereNull('end_date')
                                    ->orWhere('end_date', '>=', $today);
                          });
                    })
                    ->count(),
            ],
            'statutory_rules' => [
                'total' => \App\Models\Hr\StatutoryRule::where('company_id', $companyId)->count(),
                'active' => \App\Models\Hr\StatutoryRule::where('company_id', $companyId)
                    ->where('is_active', true)
                    ->where('effective_from', '<=', $today)
                    ->where(function($q) use ($today) {
                        $q->whereNull('effective_to')->orWhere('effective_to', '>=', $today);
                    })
                    ->count(),
            ],
        ];
    }
}
