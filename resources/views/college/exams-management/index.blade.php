@extends('layouts.main')

@section('title', 'Exams & Academics Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Exams & Academics', 'url' => '#', 'icon' => 'bx bx-book-open']
        ]" />
        <h6 class="mb-0 text-uppercase">EXAMS & ACADEMICS MANAGEMENT</h6>
        <hr />

        <!-- Exams Statistics -->
        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-5">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Exams & Academics Statistics</h5>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card radius-10 bg-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Total Courses</p>
                                                <h4 class="text-white">{{ \App\Models\College\Course::where('status', 'active')->count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-book"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Exam Schedules</p>
                                                <h4 class="text-white">{{ \App\Models\College\ExamSchedule::where('branch_id', session('branch_id'))->count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-calendar-check"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Assessment Types</p>
                                                <h4 class="text-white">{{ \App\Models\College\AssessmentType::count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-list-check"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Course Assessments</p>
                                                <h4 class="text-white">{{ \App\Models\College\CourseAssessment::count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-task"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Assessment Scores</p>
                                                <h4 class="text-white">{{ \App\Models\College\AssessmentScore::count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-bar-chart"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-info">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Final Exams</p>
                                                <h4 class="text-white">{{ \App\Models\College\FinalExam::count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-edit"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-warning">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Final Exam Scores</p>
                                                <h4 class="text-white">{{ \App\Models\College\FinalExamScore::count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-trophy"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card radius-10 bg-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="">
                                                <p class="mb-1 text-white">Grading Scales</p>
                                                <h4 class="text-white">{{ \App\Models\College\GradingScale::count() }}</h4>
                                            </div>
                                            <div class="ms-auto fs-1 text-white"><i class="bx bx-slider"></i></div>
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
                            <h5 class="mb-0 text-success">Quick Actions</h5>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h4 class="text-success mb-1">{{ \App\Models\College\Course::where('status', 'active')->count() }}</h4>
                                    <small class="text-muted">Active Courses</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-primary mb-1">{{ \App\Models\College\ExamSchedule::where('branch_id', session('branch_id'))->count() }}</h4>
                                    <small class="text-muted">Schedules</small>
                                </div>
                            </div>
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <h4 class="text-warning mb-1">{{ \App\Models\College\AssessmentScore::where('status', 'published')->count() }}</h4>
                                    <small class="text-muted">Published Scores</small>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info mb-1">{{ \App\Models\College\FinalExamScore::count() }}</h4>
                                    <small class="text-muted">Final Results</small>
                                </div>
                            </div>
                            <hr>
                            <a href="{{ route('college.courses.index') }}" class="btn btn-success">
                                <i class="bx bx-book me-1"></i> Manage Courses
                            </a>
                            <a href="{{ route('college.exam-schedules.index') }}" class="btn btn-outline-success">
                                <i class="bx bx-calendar me-1"></i> Exam Schedules
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Modules -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-grid me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Exams & Academics Modules</h5>
                        </div>
                        <hr>
                        <div class="row">
                            <!-- Master Timetable -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                            <i class="bx bx-calendar-event"></i>
                                            <span class="visually-hidden">master timetable</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-table fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Master Timetable</h5>
                                        <p class="card-text">View combined examination timetable for all programs.</p>
                                        <a href="{{ route('college.exam-schedules.master-timetable') }}" class="btn btn-primary">
                                            <i class="bx bx-calendar-event me-1"></i> View Timetable
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Exam Schedules -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                            {{ \App\Models\College\ExamSchedule::where('branch_id', session('branch_id'))->count() }}
                                            <span class="visually-hidden">schedules count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-calendar-alt fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Exam Schedules</h5>
                                        <p class="card-text">Manage examination timetables and schedules for courses.</p>
                                        <a href="{{ route('college.exam-schedules.index') }}" class="btn btn-success">
                                            <i class="bx bx-calendar me-1"></i> Manage Schedules
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Assessment Types -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-warning position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark">
                                            {{ \App\Models\College\AssessmentType::count() }}
                                            <span class="visually-hidden">assessment types count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-list-check fs-1 text-warning"></i>
                                        </div>
                                        <h5 class="card-title">Assessment Types</h5>
                                        <p class="card-text">Configure assessment types like quizzes, assignments, mid-terms.</p>
                                        <a href="{{ route('college.assessment-types.index') }}" class="btn btn-warning">
                                            <i class="bx bx-cog me-1"></i> Manage Types
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Assessments -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-info position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">
                                            {{ \App\Models\College\CourseAssessment::count() }}
                                            <span class="visually-hidden">assessments count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-task fs-1 text-info"></i>
                                        </div>
                                        <h5 class="card-title">Course Assessments</h5>
                                        <p class="card-text">Create assessments for courses with weight and max marks.</p>
                                        <a href="{{ route('college.course-assessments.index') }}" class="btn btn-info">
                                            <i class="bx bx-task me-1"></i> Manage Assessments
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Assessment Scores -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-success position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                            {{ \App\Models\College\AssessmentScore::count() }}
                                            <span class="visually-hidden">scores count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-bar-chart fs-1 text-success"></i>
                                        </div>
                                        <h5 class="card-title">Assessment Scores</h5>
                                        <p class="card-text">Record and manage student assessment scores and marks.</p>
                                        <a href="{{ route('college.assessment-scores.index') }}" class="btn btn-success">
                                            <i class="bx bx-edit me-1"></i> Manage Scores
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Final Exams -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-danger position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ \App\Models\College\FinalExam::count() }}
                                            <span class="visually-hidden">final exams count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-medal fs-1 text-danger"></i>
                                        </div>
                                        <h5 class="card-title">Final Exams</h5>
                                        <p class="card-text">Manage end-of-semester final examinations.</p>
                                        <a href="{{ route('college.final-exams.index') }}" class="btn btn-danger">
                                            <i class="bx bx-medal me-1"></i> Manage Final Exams
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Final Exam Scores -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-primary position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                            {{ \App\Models\College\FinalExamScore::count() }}
                                            <span class="visually-hidden">final scores count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-trophy fs-1 text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Final Exam Scores</h5>
                                        <p class="card-text">Record and manage final examination scores.</p>
                                        <a href="{{ route('college.final-exam-scores.index') }}" class="btn btn-primary">
                                            <i class="bx bx-trophy me-1"></i> Manage Final Scores
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Grading Scales -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-secondary position-relative">
                                    <div class="card-body text-center">
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                                            {{ \App\Models\College\GradingScale::count() }}
                                            <span class="visually-hidden">grading scales count</span>
                                        </span>
                                        <div class="mb-3">
                                            <i class="bx bx-slider fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="card-title">Grading Scales</h5>
                                        <p class="card-text">Configure grading scales, grade points, and letter grades.</p>
                                        <a href="{{ route('college.grading-scales.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-slider me-1"></i> Manage Grades
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Reports -->
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card border-dark position-relative">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="bx bx-bar-chart-alt-2 fs-1 text-dark"></i>
                                        </div>
                                        <h5 class="card-title">Academic Reports</h5>
                                        <p class="card-text">Generate academic performance reports and transcripts.</p>
                                        <a href="{{ route('college.students.index') }}" class="btn btn-dark">
                                            <i class="bx bx-file me-1"></i> View Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bx bx-info-circle" style="font-size: 1.5rem; color: #0dcaf0;"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-2">Exams & Academics Management</h6>
                                <p class="text-muted mb-0">This comprehensive module allows you to manage examinations, results, and academic assessments for college students. You can organize courses, schedule exams, record results, and generate detailed academic performance reports.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection