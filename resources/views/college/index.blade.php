@extends('layouts.main')

@section('title', 'College Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => '#', 'icon' => 'bx bx-graduation']
        ]" />
        <h6 class="mb-0 text-uppercase">COLLEGE MANAGEMENT</h6>
        <hr />

        <!-- College Statistics -->
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-graduation me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">College Statistics</h5>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card radius-10 bg-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Total Students</p>
                                                <h4 class="text-white">{{ number_format($statistics['total_students']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-group"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Active Students</p>
                                                <h4 class="text-white">{{ number_format($statistics['active_students']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-user-check"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Departments</p>
                                                <h4 class="text-white">{{ number_format($statistics['departments']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-building"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Programs</p>
                                                <h4 class="text-white">{{ number_format($statistics['programs']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-book"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-danger">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Courses</p>
                                                <h4 class="text-white">{{ number_format($statistics['courses']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-book-open"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-dark">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Exams</p>
                                                <h4 class="text-white">{{ number_format($statistics['exams']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-edit"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Fee Invoices</p>
                                                <h4 class="text-white">{{ number_format($statistics['fee_invoices']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-receipt"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Paid Invoices</p>
                                                <h4 class="text-white">{{ number_format($statistics['paid_invoices']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-check-circle"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-3">
                                <div class="card radius-10 bg-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Unpaid Invoices</p>
                                            <h4 class="text-white">{{ number_format($statistics['unpaid_invoices']) }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-time"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-3">
                                <div class="card radius-10 bg-primary">
                                    <div class="card-body">
                                     <div class="d-flex align-items-center">
                                        <div class="">
                                            <p class="mb-1 text-white">Academic Years</p>
                                            <h4 class="text-white">{{ number_format($statistics['academic_years']) }}</h4>
                                        </div>
                                        <div class="ms-auto fs-1 text-white"><i class="bx bx-calendar"></i></div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-secondary">
                                    <div class="card-body">
                                     <div class="d-flex align-items-center">
                                        <div class="">
                                            <p class="mb-1 text-white">Semesters</p>
                                            <h4 class="text-white">{{ number_format($statistics['semesters']) }}</h4>
                                        </div>
                                        <div class="ms-auto fs-1 text-white"><i class="bx bx-list-ol"></i></div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="card border-top border-0 border-4 border-success">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-trending-up me-1 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">College Analytics</h5>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h4 class="text-success mb-1">{{ number_format($analytics['new_enrollments']) }}</h4>
                                    <small class="text-muted">New Enrollments</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-primary mb-1">{{ number_format($analytics['graduates']) }}</h4>
                                    <small class="text-muted">Graduates</small>
                                </div>
                            </div>
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h4 class="text-success mb-1">{{ number_format($analytics['fee_collection'], 2) }}</h4>
                                    <small class="text-muted">Fee Collection</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info mb-1">{{ number_format($analytics['outstanding'], 2) }}</h4>
                                    <small class="text-muted">Outstanding</small>
                                </div>
                            </div>
                            <div class="text-center mb-3">
                                <h4 class="text-warning mb-1">{{ number_format($analytics['active_sessions']) }}</h4>
                                <small class="text-muted">Active Sessions</small>
                            </div>
                            <hr>
                            <button class="btn btn-success" disabled>
                                <i class="bx bx-bar-chart me-1"></i> View College Reports (Coming Soon)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- College Management Modules -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-grid me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">College Management Modules</h5>
                        </div>
                        <hr>

                        <!-- Student Management Module -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="bx bx-graduation me-2"></i>Student Management
                                </h5>
                            </div>

                            <!-- Department Card -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-primary position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-building fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Departments</h6>
                                        <p class="card-text small">Manage college departments</p>
                                        <a href="{{ route('college.departments.index') }}" class="btn btn-primary">
                                            <i class="bx bx-building me-1"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Programs Card -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-success position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-book fs-1 text-success"></i>
                                        </div>
                                        <h6 class="card-title">Programs</h6>
                                        <p class="card-text small">Manage academic programs</p>
                                        <a href="{{ route('college.programs.index') }}" class="btn btn-success">
                                            <i class="bx bx-book me-1"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Students Card -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-info position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-group fs-1 text-info"></i>
                                        </div>
                                        <h6 class="card-title">Students</h6>
                                        <p class="card-text small">Manage student records</p>
                                        <a href="{{ route('college.students.index') }}" class="btn btn-info">
                                            <i class="bx bx-group me-1"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Courses Shortcut Card -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-danger position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-book-open fs-1 text-danger"></i>
                                        </div>
                                        <h6 class="card-title">Courses</h6>
                                        <p class="card-text small">Browse courses offered</p>
                                        <a href="{{ route('college.courses.index') }}" class="btn btn-danger">
                                            <i class="bx bx-book-open me-1"></i> View Courses
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Years Card -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-warning position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-calendar fs-1 text-warning"></i>
                                        </div>
                                        <h6 class="card-title">Academic Years</h6>
                                        <p class="card-text small">Manage academic years</p>
                                        <a href="{{ route('college.academic-years.index') }}" class="btn btn-warning">
                                            <i class="bx bx-calendar me-1"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Semesters Card -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-purple position-relative" style="border-color: #6f42c1 !important;">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-time-five fs-1" style="color: #6f42c1;"></i>
                                        </div>
                                        <h6 class="card-title">Semesters</h6>
                                        <p class="card-text small">Manage academic semesters</p>
                                        <a href="{{ route('college.semesters.index') }}" class="btn" style="background-color: #6f42c1; color: white;">
                                            <i class="bx bx-time-five me-1"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Levels Card -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-teal position-relative" style="border-color: #0ea5e9 !important;">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-layer fs-1" style="color: #0ea5e9;"></i>
                                        </div>
                                        <h6 class="card-title">Academic Levels</h6>
                                        <p class="card-text small">Manage qualification levels</p>
                                        <a href="{{ route('college.levels.index') }}" class="btn" style="background-color: #0ea5e9; color: white;">
                                            <i class="bx bx-layer me-1"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Categories Card -->
                            <div class="col-md-6 col-lg-3 mb-4">
                                <div class="card border-secondary position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-folder fs-1 text-secondary"></i>
                                        </div>
                                        <h6 class="card-title">Document Categories</h6>
                                        <p class="card-text small">Manage document categories</p>
                                        <a href="{{ route('college.document-categories.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-folder me-1"></i> Manage
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Other Modules -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="bx bx-cog me-2"></i>Other Modules
                                </h5>
                            </div>

                            <!-- Fee Management Module -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-money fs-1 text-warning"></i>
                                        </div>
                                        <h6 class="card-title">Fee Management</h6>
                                        <p class="card-text small">Handle fees, invoices, and payments</p>
                                        <a href="{{ route('college.fee-management.index') }}" class="btn btn-warning">
                                            <i class="bx bx-money me-1"></i> Manage Fees
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Exams & Academics Management Module -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-book-open fs-1 text-danger"></i>
                                        </div>
                                        <h6 class="card-title">Exams & Academics Management</h6>
                                        <p class="card-text small">Manage exams, results, and transcripts</p>
                                        <a href="{{ route('college.exams-management.dashboard') }}" class="btn btn-danger">
                                            <i class="bx bx-book-open me-1"></i> Manage Exams
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Timetable Management Module -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-calendar-alt fs-1 text-info"></i>
                                        </div>
                                        <h6 class="card-title">Timetable Management</h6>
                                        <p class="card-text small">Create and manage program timetables</p>
                                        <a href="{{ route('college.timetables.index') }}" class="btn btn-info">
                                            <i class="bx bx-calendar-alt me-1"></i> Manage Timetables
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

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    /* Notification badge positioning */
    .position-relative .badge {
        z-index: 10;
        font-size: 0.7rem;
        min-width: 1.5rem;
        height: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .border-primary {
        border-color: #0d6efd !important;
    }

    .border-success {
        border-color: #198754 !important;
    }

    .border-warning {
        border-color: #ffc107 !important;
    }

    .border-info {
        border-color: #0dcaf0 !important;
    }

    .border-danger {
        border-color: #dc3545 !important;
    }

    .border-secondary {
        border-color: #6c757d !important;
    }

    .border-dark {
        border-color: #212529 !important;
    }

    .text-purple {
        color: #6f42c1 !important;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .card-title {
        font-size: 0.9rem;
        font-weight: 600;
    }

    .card-text {
        font-size: 0.75rem;
        line-height: 1.2;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Add any interactive functionality here
        console.log('College Management dashboard loaded');
    });
</script>
@endpush