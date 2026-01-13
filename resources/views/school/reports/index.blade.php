@extends('layouts.main')

@section('title', 'School Reports')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => '#', 'icon' => 'bx bx-bar-chart']
        ]" />
        <h6 class="mb-0 text-uppercase">SCHOOL REPORTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Fee & Income Reports Section -->
                        <div class="card-title d-flex align-items-center mb-4">
                            <div><i class="bx bx-money me-1 font-22 text-danger"></i></div>
                            <h5 class="mb-0 text-danger">Fee & Income Reports</h5>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-money fs-1 text-danger"></i>
                                        </div>
                                        <h6 class="card-title">Fee Payment Status</h6>
                                        <p class="card-text small">Fee payment status and outstanding amounts</p>
                                        <a href="{{ route('school.reports.fee-report') }}" class="btn btn-danger">
                                            <i class="bx bx-bar-chart me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-money fs-1 text-info"></i>
                                        </div>
                                        <h6 class="card-title">Detailed Fee Collection</h6>
                                        <p class="card-text small">Detailed fee collection reports</p>
                                        <a href="{{ route('school.reports.detailed-fee-collection') }}" class="btn btn-info">
                                            <i class="bx bx-bar-chart me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-time fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Fee Aging Report</h6>
                                        <p class="card-text small">Fee payment aging analysis</p>
                                        <a href="{{ route('school.reports.fee-aging') }}" class="btn btn-primary">
                                            <i class="bx bx-bar-chart me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-money fs-1 text-info"></i>
                                        </div>
                                        <h6 class="card-title">Class-Wise Revenue Collection</h6>
                                        <p class="card-text small">Revenue collection by class</p>
                                        <a href="{{ route('school.reports.class-wise-revenue-collection') }}" class="btn btn-info">
                                            <i class="bx bx-bar-chart me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                        <i class="bx bx-money fs-1 text-info"></i>
                                        </div>
                                        <h6 class="card-title">Fee Waivers & Discounts Report</h6>
                                        <p class="card-text small">Fee waivers and discounts analysis</p>
                                        <a href="{{ route('school.reports.fee-waivers-discounts') }}" class="btn btn-warning">
                                            <i class="bx bx-bar-chart me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-money fs-1 text-success"></i>
                                        </div>
                                        <h6 class="card-title">Other Income Collection Report</h6>
                                        <p class="card-text small">Other income collection and analysis</p>
                                        <a href="{{ route('school.reports.other-income-collection') }}" class="btn btn-success">
                                            <i class="bx bx-bar-chart me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Exam & Academic Reports Section -->
                        <div class="mt-5">
                            <div class="card-title d-flex align-items-center mb-4">
                                <div><i class="bx bx-book me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Exam & Academic Reports</h5>
                            </div>
                            <hr />

                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-group fs-1 text-primary"></i>
                                            </div>
                                            <h6 class="card-title">Gender Distribution Report (with Totals)</h6>
                                            <p class="card-text small">Gender distribution analysis with totals</p>
                                            <a href="{{ route('school.reports.gender-distribution') }}" class="btn btn-primary">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-trending-up fs-1 text-info"></i>
                                            </div>
                                            <h6 class="card-title">Examination Results Report</h6>
                                            <p class="card-text small">Examination Results Report</p>
                                            <a href="{{ route('school.reports.examination-results') }}" class="btn btn-info">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user-x fs-1 text-warning"></i>
                                            </div>
                                            <h6 class="card-title">Overall Analysis Report</h6>
                                            <p class="card-text small">Comprehensive performance analysis by class and stream</p>
                                            <a href="{{ route('school.reports.overall-analysis') }}" class="btn btn-warning">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-trending-up fs-1 text-info"></i>
                                            </div>
                                            <h6 class="card-title">Performance by Class</h6>
                                            <p class="card-text small">Academic performance analysis by class</p>
                                            <a href="{{ route('school.reports.performance-by-class') }}" class="btn btn-info">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-book fs-1 text-success"></i>
                                            </div>
                                            <h6 class="card-title">Subject-Wise Analysis Report</h6>
                                            <p class="card-text small">Subject performance analysis with statistics</p>
                                            <a href="{{ route('school.reports.subject-wise-analysis') }}" class="btn btn-success">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-bar-chart-alt fs-1 text-warning"></i>
                                            </div>
                                            <h6 class="card-title">Comparative Subject Performance Analysis by Grade and Gender</h6>
                                            <p class="card-text small">Comparative analysis by grade and gender</p>
                                            <a href="{{ route('school.reports.comparative-subject-performance') }}" class="btn btn-warning">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user fs-1 text-success"></i>
                                            </div>
                                            <h6 class="card-title">Student Subject Performance and Progress Analysis</h6>
                                            <p class="card-text small">Individual student progress analysis</p>
                                            <a href="{{ route('school.reports.student-subject-performance') }}" class="btn btn-success">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Reports Section -->
                        <div class="mt-5">
                            <div class="card-title d-flex align-items-center mb-4">
                                <div><i class="bx bx-calendar-check me-1 font-22 text-danger"></i></div>
                                <h5 class="mb-0 text-danger">Attendance Reports</h5>
                            </div>
                            <hr />

                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-danger position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-calendar-check fs-1 text-danger"></i>
                                            </div>
                                            <h6 class="card-title">Attendance Summary Report</h6>
                                            <p class="card-text small">Overall attendance summary</p>
                                            <a href="{{ route('school.reports.attendance-report') }}" class="btn btn-danger">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-secondary position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-line-chart fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="card-title">Attendance Trend Analysis (Monthly)</h6>
                                        <p class="card-text small">Monthly attendance trend analysis</p>
                                        <a href="{{ route('school.reports.monthly-attendance-trend') }}" class="btn btn-secondary">
                                            <i class="bx bx-bar-chart me-1"></i> View Report
                                        </a>
                                    </div>
                                </div>
                            </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-book fs-1 text-primary"></i>
                                            </div>
                                            <h6 class="card-title">Subject-Wise Attendance Report</h6>
                                            <p class="card-text small">Attendance analysis by subject</p>
                                            <a href="{{ route('school.reports.subject-wise-attendance') }}" class="btn btn-primary">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignment Reports Section -->
                        <div class="mt-5">
                            <div class="card-title d-flex align-items-center mb-4">
                                <div><i class="bx bx-book-open me-1 font-22 text-purple"></i></div>
                                <h5 class="mb-0 text-purple">Assignment Reports</h5>
                            </div>
                            <hr />

                            <div class="row">
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-purple position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-check-circle fs-1 text-purple"></i>
                                            </div>
                                            <h6 class="card-title">Assignment Completion Rate</h6>
                                            <p class="card-text small">Track completion rates by class, subject, and student</p>
                                            <a href="{{ route('school.reports.assignment-completion-rate') }}" class="btn bg-purple text-white">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-danger position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-time-five fs-1 text-danger"></i>
                                            </div>
                                            <h6 class="card-title">Late Submissions Report</h6>
                                            <p class="card-text small">Identify and analyze late assignment submissions</p>
                                            <a href="{{ route('school.reports.late-submissions') }}" class="btn btn-danger">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-line-chart fs-1 text-info"></i>
                                            </div>
                                            <h6 class="card-title">Average Marks per Assignment</h6>
                                            <p class="card-text small">Calculate and compare average marks across assignments</p>
                                            <a href="{{ route('school.reports.average-marks-assignment') }}" class="btn btn-info">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-error-circle fs-1 text-warning"></i>
                                            </div>
                                            <h6 class="card-title">Weak Topic Analysis</h6>
                                            <p class="card-text small">Identify topics where students struggle most</p>
                                            <a href="{{ route('school.reports.weak-topic-analysis') }}" class="btn btn-warning">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-primary position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-book-reader fs-1 text-primary"></i>
                                            </div>
                                            <h6 class="card-title">Subject-Wise Homework Performance</h6>
                                            <p class="card-text small">Analyze homework performance by subject</p>
                                            <a href="{{ route('school.reports.subject-homework-performance') }}" class="btn btn-primary">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-info position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-trending-up fs-1 text-info"></i>
                                            </div>
                                            <h6 class="card-title">Improvement Tracking Report</h6>
                                            <p class="card-text small">Track student improvement over time</p>
                                            <a href="{{ route('school.reports.improvement-tracking') }}" class="btn btn-info">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-secondary position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-user-check fs-1 text-secondary"></i>
                                            </div>
                                            <h6 class="card-title">Teacher Assignment Frequency</h6>
                                            <p class="card-text small">Analyze assignment frequency by teacher</p>
                                            <a href="{{ route('school.reports.teacher-assignment-frequency') }}" class="btn btn-secondary">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-warning position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                            <i class="bx bx-trending-up fs-1 text-info"></i>
                                            </div>
                                            <h6 class="card-title">Class Workload Balance</h6>
                                            <p class="card-text small">Analyze assignment workload distribution across classes</p>
                                            <a href="{{ route('school.reports.class-workload-balance') }}" class="btn btn-warning">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border-success position-relative">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <i class="bx bx-check-double fs-1 text-success"></i>
                                            </div>
                                            <h6 class="card-title">School-Wide Compliance Report</h6>
                                            <p class="card-text small">Overall assignment compliance and adherence metrics</p>
                                            <a href="{{ route('school.reports.school-compliance') }}" class="btn btn-success">
                                                <i class="bx bx-bar-chart me-1"></i> View Report
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

@push('styles')
<style>
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    .text-purple {
        color: #6f42c1 !important;
    }

    .border-purple {
        border-color: #6f42c1 !important;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
        border-color: #6f42c1 !important;
    }

    .bg-purple:hover {
        background-color: #5a32a3 !important;
        border-color: #5a32a3 !important;
    }
</style>
@endpush