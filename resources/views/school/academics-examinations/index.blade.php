@extends('layouts.main')

@section('title', 'Academics & Examinations')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => '#', 'icon' => 'bx bx-book']
        ]" />
        <h6 class="mb-0 text-uppercase">ACADEMICS & EXAMINATIONS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-book me-1 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">Academics & Examinations Module</h5>
                        </div>
                        <hr />

                        <!-- Academics & Examinations Features -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-center">
                                            <div><i class="bx bx-book me-1 font-22 text-primary"></i></div>
                                            <h5 class="mb-0 text-primary">Academics & Examinations Features</h5>
                                        </div>
                                        <hr />

                                        <!-- Academics Group -->
                                        <div class="row mb-4">
                                            <div class="col-12">
                                                <h6 class="text-primary mb-3"><i class="bx bx-graduation me-2"></i>ACADEMICS</h6>
                                            </div>
                                            <!-- Subject -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-success position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                                        {{ $subjectsCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-book fs-1 text-success"></i>
                                                        </div>
                                                        <h6 class="card-title">Subject</h6>
                                                        <p class="card-text small">Manage subjects offered in the curriculum</p>
                                                        <a href="{{ route('school.subjects.index') }}" class="btn btn-success btn-sm">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Subjects
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Subject Group -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-info position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">
                                                        {{ $subjectGroupsCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-group fs-1 text-info"></i>
                                                        </div>
                                                        <h6 class="card-title">Subject Group</h6>
                                                        <p class="card-text small">Organize subjects into groups and categories</p>
                                                        <a href="{{ route('school.subject-groups.index') }}" class="btn btn-info btn-sm">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Subject Groups
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Assign Teacher to Class -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-warning position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                                        {{ $classTeachersCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-user-plus fs-1 text-warning"></i>
                                                        </div>
                                                        <h6 class="card-title">Assign Teacher to Class</h6>
                                                        <p class="card-text small">Assign teachers to specific classes and subjects</p>
                                                        <a href="{{ route('school.class-teachers.index') }}" class="btn btn-warning btn-sm">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Class Teachers
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Assign Teacher to Subject -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-secondary position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                                                        {{ $subjectTeachersCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-link fs-1 text-secondary"></i>
                                                        </div>
                                                        <h6 class="card-title">Assign Teacher to Subject</h6>
                                                        <p class="card-text small">Link teachers with their specialized subjects</p>
                                                        <a href="{{ route('school.subject-teachers.index') }}" class="btn btn-secondary btn-sm">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Subject Teachers
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- School Timetable -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-primary position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                                        {{ $timetablesCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-time-five fs-1 text-primary"></i>
                                                        </div>
                                                        <h6 class="card-title">School Timetable</h6>
                                                        <p class="card-text small">Create and manage class, teacher, and room timetables</p>
                                                        <a href="{{ route('school.timetables.index') }}" class="btn btn-primary btn-sm">
                                                            <i class="bx bx-calendar me-1"></i> Manage Timetables
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- School Assignment & Homework -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-dark position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
                                                        {{ $assignmentsCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-book-open fs-1 text-dark"></i>
                                                        </div>
                                                        <h6 class="card-title">School Assignment & Homework</h6>
                                                        <p class="card-text small">Create, assign, and manage assignments, homework, and projects</p>
                                                        <a href="{{ route('school.assignments.index') }}" class="btn btn-dark btn-sm">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Assignments
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- School Digital Library / Learning Portal -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-purple position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-purple">
                                                        {{ $libraryMaterialsCount ?? 0 }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-library fs-1 text-purple"></i>
                                                        </div>
                                                        <h6 class="card-title">School Digital Library / Learning Portal</h6>
                                                        <p class="card-text small">Upload and manage PDF books, notes, past papers, and assignments</p>
                                                        <a href="{{ route('school.library.index') }}" class="btn btn-purple btn-sm">
                                                            <i class="bx bx-library me-1"></i> Manage Library
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- EXAMS -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        
                                        <hr />

                                        <div class="row">
                                            <div class="col-12">
                                                <h6 class="text-danger mb-3"><i class="bx bx-test-tube me-2"></i>EXAMS</h6>
                                            </div>
                                            <!-- Marks Grade -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-danger position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                        {{ $gradeScalesCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-star fs-1 text-danger"></i>
                                                        </div>
                                                        <h6 class="card-title">Marks Grade</h6>
                                                        <p class="card-text small">Define grading scales and mark ranges</p>
                                                        <a href="{{ route('school.grade-scales.index') }}" class="btn btn-danger btn-sm">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Grade Scales
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Exam Types -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-primary position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                                        {{ $examTypesCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-category fs-1 text-primary"></i>
                                                        </div>
                                                        <h6 class="card-title">Exam Types</h6>
                                                        <p class="card-text small">Create and manage different types of examinations</p>
                                                        <a href="{{ route('school.exam-types.index') }}" class="btn btn-primary btn-sm">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Exam Types
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Exam Schedule -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-success position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                                        0
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-calendar-event fs-1 text-success"></i>
                                                        </div>
                                                        <h6 class="card-title">Exam Schedule</h6>
                                                        <p class="card-text small">Plan and schedule examination dates, times, and invigilation</p>
                                                        <a href="{{ route('school.exam-schedules.index') }}" class="btn btn-success btn-sm">
                                                            <i class="bx bx-calendar me-1"></i> Manage Schedule
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Assign Class to Exam -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-info position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">
                                                        {{ $examClassAssignmentsCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-target-lock fs-1 text-info"></i>
                                                        </div>
                                                        <h6 class="card-title">Assign Class to Exam</h6>
                                                        <p class="card-text small">Assign classes and subjects to specific exams</p>
                                                        <a href="{{ route('school.exam-class-assignments.index') }}" class="btn btn-info btn-sm">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Assignments
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Add Marks -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-warning position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                                        {{ $marksCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-edit fs-1 text-warning"></i>
                                                        </div>
                                                        <h6 class="card-title">Add Marks</h6>
                                                        <p class="card-text small">Enter and manage student examination marks</p>
                                                        <a href="{{ route('school.marks-entry') }}" class="btn btn-warning btn-sm">
                                                            <i class="bx bx-edit me-1"></i> Enter Marks
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- School Management Settings -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <div class="card-title d-flex align-items-center">
                                            <div><i class="bx bx-cog me-1 font-22 text-success"></i></div>
                                            <h5 class="mb-0 text-success">School Management Settings</h5>
                                        </div>
                                        <hr />

                                        <!-- School Settings Submodules -->
                                        <div class="row">
                                            <!-- Academic Years -->
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card border-success position-relative">
                                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                                        {{ $academicYearsCount }}
                                                    </span>
                                                    <div class="card-body text-center">
                                                        <div class="mb-3">
                                                            <i class="bx bx-calendar fs-1 text-success"></i>
                                                        </div>
                                                        <h6 class="card-title">Academic Years</h6>
                                                        <p class="card-text small">Manage academic years, set current year, and track student enrollments</p>
                                                        <a href="{{ route('school.academic-years.index') }}" class="btn btn-success">
                                                            <i class="bx bx-list-ul me-1"></i> Manage Academic Years
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
        console.log('Academics & Examinations module loaded');
    });
</script>
@endpush