@extends('layouts.main')

@section('title', 'Student Information')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => '#', 'icon' => 'bx bx-group']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT INFORMATION</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-task me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Student Information Operations SESSION</h5>
                        </div>
                        <hr />

                        <!-- Student Information Operations -->
                        <div class="row mb-4">
                            <!-- Student Admission -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                        {{ $studentsCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-id-card fs-1 text-success"></i>
                                        </div>
                                        <h6 class="card-title">Student Admission</h6>
                                        <p class="card-text small">Register new students and manage admissions</p>
                                        <a href="{{ route('school.students.index') }}" class="btn btn-success">
                                            <i class="bx bx-list-ul me-1"></i> Manage Students
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>

                            <!-- Attendance -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-calendar-check fs-1 text-success"></i>
                                        </div>
                                        <h6 class="card-title">Attendance</h6>
                                        <p class="card-text small">Manage daily attendance sessions, mark student attendance, and track attendance records</p>
                                        <a href="{{ route('school.attendance.index') }}" class="btn btn-success">
                                            <i class="bx bx-calendar me-1"></i> Manage Attendance
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>

                            <!-- Student Transfers -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                        {{ $transfersCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-transfer fs-1 text-warning"></i>
                                        </div>
                                        <h6 class="card-title">Student Transfers</h6>
                                        <p class="card-text small">Manage student transfers, re-admissions, and transfer certificates</p>
                                        <a href="{{ route('school.student-transfers.index') }}" class="btn btn-warning">
                                            <i class="bx bx-transfer me-1"></i> Manage Transfers
                                        </a>
                                    </div>
                                    
                                </div>
                            </div>

                            <!-- Send Message -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-message fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Send Message</h6>
                                        <p class="card-text small">Send SMS/Email notifications to students</p>
                                        <button class="btn btn-primary btn-sm" disabled>
                                            <i class="bx bx-list-ul me-1"></i> Coming Soon
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Promote Student -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                        {{ $promotionsCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-up-arrow-alt fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Promote Student</h6>
                                        <p class="card-text small">Promote students to next class or grade</p>
                                        <a href="{{ route('school.promote-students.index') }}" class="btn btn-primary">
                                            <i class="bx bx-list-ul me-1"></i> Manage Promotions
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-cog me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Student Information Settings</h5>
                        </div>
                        <hr />
                        <!-- Student Information Settings -->
                        <div class="row mb-4">
                            <!-- Streams -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                        {{ $streamsCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-book-open fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Streams</h6>
                                        <p class="card-text small">Manage academic streams (Science, Arts, Commerce, etc.)</p>
                                        <a href="{{ route('school.streams.index') }}" class="btn btn-primary btn-sm">
                                            <i class="bx bx-list-ul me-1"></i> Manage Streams
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Class -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                        {{ $classesCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-building fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Class</h6>
                                        <p class="card-text small">Manage class information, capacity, and sections</p>
                                        <a href="{{ route('school.classes.index') }}" class="btn btn-primary btn-sm">
                                            <i class="bx bx-list-ul me-1"></i> Manage Classes
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Route -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">
                                        {{ $routesCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-map fs-1 text-info"></i>
                                        </div>
                                        <h6 class="card-title">Route</h6>
                                        <p class="card-text small">Manage transportation routes and stops</p>
                                        <a href="{{ route('school.routes.index') }}" class="btn btn-info btn-sm">
                                            <i class="bx bx-list-ul me-1"></i> Manage Routes
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Bus Stops -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                        {{ $busStopsCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-map-pin fs-1 text-warning"></i>
                                        </div>
                                        <h6 class="card-title">Bus Stops</h6>
                                        <p class="card-text small">Manage all bus stops and their details</p>
                                        <a href="{{ route('school.bus-stops.index') }}" class="btn btn-warning btn-sm">
                                            <i class="bx bx-list-ul me-1"></i> Manage Bus Stops
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Bus -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $busesCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-bus fs-1 text-danger"></i>
                                        </div>
                                        <h6 class="card-title">Bus</h6>
                                        <p class="card-text small">Manage school buses, drivers, and capacity</p>
                                        <a href="{{ route('school.buses.index') }}" class="btn btn-danger btn-sm">
                                            <i class="bx bx-list-ul me-1"></i> Manage Buses
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
