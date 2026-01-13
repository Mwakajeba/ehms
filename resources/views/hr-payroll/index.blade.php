@extends('layouts.main')

@section('title', 'HR & Payroll')

@push('styles')
<style>
    .module-card {
        position: relative;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .module-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .count-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        color: white;
        z-index: 10;
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'HR & Payroll', 'url' => '#', 'icon' => 'bx bx-user']
        ]" />
            <h6 class="mb-0 text-uppercase">HR & PAYROLL MANAGEMENT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">HR & Payroll Dashboard</h4>

                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bx bx-check-circle me-2"></i>
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <div class="row">
                                <!-- Employee Management -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">{{ number_format($stats['employees']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user-plus text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Employee Management</h5>
                                            <p class="card-text">Manage employee records, personal information, and employment details.</p>
                                            <a href="{{ route('hr.employees.index') }}" class="btn btn-primary">
                                                <i class="bx bx-group me-1"></i>Manage Employees
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payroll Processing -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-success">{{ number_format($stats['payrolls']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-money text-success" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Payroll Processing</h5>
                                            <p class="card-text">Process monthly salaries, calculate deductions, and generate payslips.</p>
                                            <a href="{{ route('hr.payrolls.index') }}" class="btn btn-success">
                                                <i class="bx bx-calculator me-1"></i>Manage Payrolls
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- HR Departments -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">{{ number_format($stats['departments']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-buildings text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">HR Departments</h5>
                                            <p class="card-text">Manage organizational departments and departmental structures.</p>
                                            <a href="{{ route('hr.departments.index') }}" class="btn btn-info">
                                                <i class="bx bx-building me-1"></i>Manage Departments
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- HR Positions -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-warning">{{ number_format($stats['positions']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-briefcase text-warning" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">HR Positions</h5>
                                            <p class="card-text">Manage job positions, roles, and responsibilities.</p>
                                            <a href="{{ route('hr.positions.index') }}" class="btn btn-warning">
                                                <i class="bx bx-briefcase me-1"></i>Manage Positions
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Job Grades -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">{{ number_format($stats['job_grades']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-layer text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Job Grades</h5>
                                            <p class="card-text">Manage job grades with salary bands and grade structures.</p>
                                            <a href="{{ route('hr.job-grades.index') }}" class="btn btn-primary">
                                                <i class="bx bx-layer me-1"></i>Manage Job Grades
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contracts -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">{{ number_format($stats['contracts']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-file text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Employee Contracts</h5>
                                            <p class="card-text">Manage employee contracts, amendments, and renewals.</p>
                                            <a href="{{ route('hr.contracts.index') }}" class="btn btn-info">
                                                <i class="bx bx-file me-1"></i>Manage Contracts
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Employee Compliance -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-success">{{ number_format($stats['compliance']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Employee Compliance</h5>
                                            <p class="card-text">Track PAYE, Pension, NHIF, WCF, and SDL compliance records.</p>
                                            <a href="{{ route('hr.employee-compliance.index') }}" class="btn btn-success">
                                                <i class="bx bx-check-circle me-1"></i>Manage Compliance
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phase 2: Time, Attendance & Leave Enhancement -->
                                <div class="col-12 mt-4">
                                    <h5 class="mb-3"><i class="bx bx-time-five me-2"></i>Time, Attendance & Leave Management</h5>
                                    <hr>
                                </div>

                                <!-- Work Schedules -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">{{ number_format($stats['work_schedules']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calendar-week text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Work Schedules</h5>
                                            <p class="card-text">Define work schedules, weekly patterns, and standard working hours.</p>
                                            <a href="{{ route('hr.work-schedules.index') }}" class="btn btn-primary">
                                                <i class="bx bx-calendar-week me-1"></i>Manage Schedules
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Shifts -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">{{ number_format($stats['shifts']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-time-five text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Shifts</h5>
                                            <p class="card-text">Manage work shifts, shift timings, and shift differentials.</p>
                                            <a href="{{ route('hr.shifts.index') }}" class="btn btn-info">
                                                <i class="bx bx-time-five me-1"></i>Manage Shifts
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Employee Schedules -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-success">{{ number_format($stats['employee_schedules']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user-check text-success" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Employee Schedules</h5>
                                            <p class="card-text">Assign work schedules and shifts to employees with effective dating.</p>
                                            <a href="{{ route('hr.employee-schedules.index') }}" class="btn btn-success">
                                                <i class="bx bx-user-check me-1"></i>Manage Assignments
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Management -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-warning">{{ number_format($stats['attendance']['this_month']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-time text-warning" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Attendance Management</h5>
                                            <p class="card-text">Track employee attendance, clock in/out, hours worked, and exceptions.</p>
                                            <a href="{{ route('hr.attendance.index') }}" class="btn btn-warning">
                                                <i class="bx bx-calendar me-1"></i>Manage Attendance
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overtime Rules -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-secondary">{{ number_format($stats['overtime_rules']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-cog text-secondary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Overtime Rules</h5>
                                            <p class="card-text">Configure overtime rates, rules, and approval requirements by grade and day type.</p>
                                            <a href="{{ route('hr.overtime-rules.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-cog me-1"></i>Manage Rules
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Overtime Requests -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-danger">{{ number_format($stats['overtime_requests']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-time text-danger" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Overtime Requests</h5>
                                            <p class="card-text">Manage overtime requests, approvals, and track overtime hours.</p>
                                            <a href="{{ route('hr.overtime-requests.index') }}" class="btn btn-danger">
                                                <i class="bx bx-time me-1"></i>Manage Requests
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Holiday Calendars -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">{{ number_format($stats['holiday_calendars']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calendar-heart text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Holiday Calendars</h5>
                                            <p class="card-text">Manage public holidays, company holidays, and regional holidays.</p>
                                            <a href="{{ route('hr.holiday-calendars.index') }}" class="btn btn-primary">
                                                <i class="bx bx-calendar-heart me-1"></i>Manage Holidays
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Biometric Devices -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">{{ number_format($stats['biometric_devices']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-fingerprint text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Biometric Devices</h5>
                                            <p class="card-text">Configure and manage biometric devices for automatic attendance capture.</p>
                                            <a href="{{ route('hr.biometric-devices.index') }}" class="btn btn-info">
                                                <i class="bx bx-fingerprint me-1"></i>Manage Devices
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Leave Management -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-success">{{ number_format($stats['leave']['total_requests']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calendar-check text-success" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Leave Management</h5>
                                            <p class="card-text">Manage employee leave requests, approvals, and leave balances.</p>
                                            <a href="{{ route('hr.leave.index') }}" class="btn btn-success">
                                                <i class="bx bx-calendar-plus me-1"></i>Manage Leaves
                                            </a>
                                        </div>
                                    </div>
                                </div>


                                <!-- Salary Advance Management -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-danger">{{ number_format($stats['salary_advances']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-credit-card text-danger" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Salary Advance Management</h5>
                                            <p class="card-text">Process and manage employee salary advance requests and repayments.</p>
                                            <a href="{{ route('hr.salary-advances.index') }}" class="btn btn-danger">
                                                <i class="bx bx-credit-card me-1"></i>Manage Advances
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- External Loan Management -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">{{ number_format($stats['external_loans']['total']) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-credit-card-alt text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">External Loan Management</h5>
                                            <p class="card-text">Manage external loans, bank loans, and loan deductions from salaries.</p>
                                            <a href="{{ route('hr.external-loans.index') }}" class="btn btn-primary">
                                                <i class="bx bx-credit-card-alt me-1"></i>External Loans
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- HESLB Loan Management -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">{{ number_format($stats['heslb_loans']['total'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-book text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">HESLB Loan Management</h5>
                                            <p class="card-text">Manage Higher Education Students' Loans Board loans and track repayments.</p>
                                            <a href="{{ route('hr.heslb-loans.index') }}" class="btn btn-info">
                                                <i class="bx bx-book me-1"></i>HESLB Loans
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phase 3: Payroll Enhancement & Statutory Compliance -->
                                <div class="col-12 mt-4">
                                    <h5 class="mb-3"><i class="bx bx-calculator me-2"></i>Payroll Enhancement & Statutory Compliance</h5>
                                    <hr>
                                </div>

                                <!-- Payroll Calendars -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-primary">{{ number_format($stats['payroll_calendars']['total'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calendar text-primary" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Payroll Calendars</h5>
                                            <p class="card-text">Manage payroll cycles, cut-off dates, and pay dates.</p>
                                            <a href="{{ route('hr.payroll-calendars.index') }}" class="btn btn-primary">
                                                <i class="bx bx-calendar me-1"></i>Manage Calendars
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pay Groups -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">{{ number_format($stats['pay_groups']['total'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-group text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Pay Groups</h5>
                                            <p class="card-text">Categorize employees by payment frequency and rules.</p>
                                            <a href="{{ route('hr.pay-groups.index') }}" class="btn btn-info">
                                                <i class="bx bx-group me-1"></i>Manage Pay Groups
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Salary Components -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-success">{{ number_format($stats['salary_components']['total'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calculator text-success" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Salary Components</h5>
                                            <p class="card-text">Define earnings and deductions components for flexible salary structures.</p>
                                            <a href="{{ route('hr.salary-components.index') }}" class="btn btn-success">
                                                <i class="bx bx-calculator me-1"></i>Manage Components
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Employee Salary Structures -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-info">{{ number_format($stats['employee_salary_structures']['with_structure'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-money text-info" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Employee Salary Structures</h5>
                                            <p class="card-text">Assign salary components to employees and manage their salary structures.</p>
                                            <a href="{{ route('hr.employee-salary-structure.index') }}" class="btn btn-info">
                                                <i class="bx bx-money me-1"></i>Manage Structures
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statutory Rules -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-danger">{{ number_format($stats['statutory_rules']['total'] ?? 0) }}</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-shield text-danger" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">Statutory Rules</h5>
                                            <p class="card-text">Configure Tanzania statutory compliance rules (PAYE, NHIF, Pension, WCF, SDL, HESLB).</p>
                                            <a href="{{ route('hr.statutory-rules.index') }}" class="btn btn-danger">
                                                <i class="bx bx-shield me-1"></i>Manage Rules
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payroll Settings -->
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card module-card h-100">
                                        <div class="count-badge bg-dark">-</div>
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-cog text-dark" style="font-size: 3rem;"></i>
                                            </div>
                                            <h5 class="card-title">HR & Payroll Settings</h5>
                                            <p class="card-text">Configure payroll settings and HR settings.</p>
                                            <a href="{{ route('hr.payroll-settings.index') }}" class="btn btn-dark">
                                                <i class="bx bx-cog me-1"></i>Payroll Settings
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">
                                            <i class="bx bx-info-circle me-2"></i>HR & Payroll Module
                                        </h6>
                                        <p class="mb-0">
                                            The HR & Payroll module provides comprehensive features including employee management, payroll
                                            processing, attendance tracking, leave management, performance evaluation, and
                                            comprehensive reporting.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
