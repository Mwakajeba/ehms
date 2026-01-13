@extends('layouts.main')

@section('title', 'School Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Management', 'url' => '#', 'icon' => 'bx bx-school']
        ]" />
        <h6 class="mb-0 text-uppercase">SCHOOL MANAGEMENT</h6>
        <hr />

        <!-- School Statistics -->
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-school me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">School Statistics</h5>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card radius-10 bg-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Total Students</p>
                                                <h4 class="text-white">0</h4>
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
                                                <h4 class="text-white">0</h4>
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
                                                <p class="mb-1 text-white">Teachers</p>
                                                <h4 class="text-white">0</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-chalkboard"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Classes</p>
                                                <h4 class="text-white">0</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-building"></i></div>
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
                                                <h4 class="text-white">0</h4>
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
                                                <h4 class="text-white">0</h4>
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
                                            <h4 class="text-white">0</h4>
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
                                                <p class="mb-1 text-white">Exams</p>
                                            <h4 class="text-white">0</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-book"></i></div>
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
                            <h5 class="mb-0 text-success">School Analytics</h5>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h4 class="text-success mb-1">0</h4>
                                    <small class="text-muted">New Admissions</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-primary mb-1">0</h4>
                                    <small class="text-muted">Graduates</small>
                                </div>
                            </div>
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h4 class="text-success mb-1">0</h4>
                                    <small class="text-muted">Fee Collection</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info mb-1">0</h4>
                                    <small class="text-muted">Outstanding</small>
                                </div>
                            </div>
                            <div class="text-center mb-3">
                                <h4 class="text-warning mb-1">0</h4>
                                <small class="text-muted">Active Sessions</small>
                            </div>
                            <hr>
                            <a href="{{ route('school.reports.index') }}" class="btn btn-success">
                                <i class="bx bx-bar-chart me-1"></i> View School Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- School Management Modules -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-grid me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">School Management Modules</h5>
                        </div>
                        <hr>

                        <!-- School Management Modules -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="bx bx-school me-2"></i>School Management Modules
                                </h5>
                            </div>

                            <!-- Student Information Module -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-group fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Student Information</h6>
                                        <p class="card-text small">Manage student data, admissions, transportation, and communications</p>
                                        <div class="d-grid gap-1">
                                            <a href="{{ route('school.student-informations.index') }}" class="btn btn-primary btn-sm">
                                                <i class="bx bx-group me-1"></i> Students
                                            </a>
                                            <a href="{{ route('school.routes.index') }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-map me-1"></i> Routes
                                            </a>
                                            <a href="{{ route('school.bus-stops.index') }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-map-pin me-1"></i> Bus Stops
                                            </a>
                                            <a href="{{ route('school.buses.index') }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-bus me-1"></i> Buses
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academics & Examinations Module -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-book fs-1 text-success"></i>
                                        </div>
                                        <h6 class="card-title">Academics & Examinations</h6>
                                        <p class="card-text small">Handle fees, invoices, reminders, and academic performance tracking</p>
                                        <a href="{{ route('school.academics-examinations.index') }}" class="btn btn-success">
                                            <i class="bx bx-book me-1"></i> Manage Academics
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Management Module -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-money fs-1 text-warning"></i>
                                        </div>
                                        <h6 class="card-title">Fee Management</h6>
                                        <p class="card-text small">Manage subjects, teachers, examinations, and academic sessions</p>
                                        <a href="{{ route('school.fee-management.index') }}" class="btn btn-warning">
                                            <i class="bx bx-money me-1"></i> Manage Fees
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Links -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-primary mb-3">
                                    <i class="bx bx-link me-2"></i>Quick Links
                                </h5>
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
        console.log('School Management dashboard loaded');
    });
</script>
@endpush