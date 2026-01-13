@extends('layouts.main')

@section('title', 'Exams & Academics Management')

@section('content')
<style>
    .exams-dashboard {
        margin-left: 250px;
        padding: 25px;
        background: #f5f7fa;
        min-height: 100vh;
    }

    /* Breadcrumb */
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 0;
        margin-top: 70px;
        margin-bottom: 20px;
    }

    .breadcrumb-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 6px;
        color: rgba(0, 0, 0, 0.6);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .breadcrumb-btn:hover {
        background: rgba(255, 255, 255, 1);
        color: rgba(0, 0, 0, 0.8);
    }

    .breadcrumb-btn.active {
        background: rgba(16, 185, 129, 0.9);
        color: white;
    }

    .breadcrumb-separator {
        color: rgba(0, 0, 0, 0.3);
        font-size: 16px;
    }

    /* Page Header */
    .page-header {
        background: rgba(16, 185, 129, 0.95);
        border-radius: 12px;
        padding: 28px 30px;
        margin-bottom: 25px;
    }

    .header-title {
        font-size: 24px;
        font-weight: 600;
        color: white;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-title i {
        font-size: 28px;
        opacity: 0.9;
    }

    .header-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 14px;
        font-weight: 400;
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 25px;
    }

    @media (max-width: 1200px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .stats-row { grid-template-columns: 1fr; }
    }

    .stat-card {
        border-radius: 8px;
        padding: 15px 20px;
        position: relative;
        overflow: hidden;
        min-height: 130px;
        display: flex;
        flex-direction: column;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    }

    .stat-card.bg-blue { background: #0d6efd; }
    .stat-card.bg-green { background: #198754; }
    .stat-card.bg-yellow { background: #ffc107; }
    .stat-card.bg-red { background: #dc3545; }
    .stat-card.bg-orange { background: #ffc107; }
    .stat-card.bg-purple { background: #dc3545; }

    .stat-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 70px;
        opacity: 0.3;
        color: rgba(0, 0, 0, 0.15);
    }

    .stat-content {
        position: relative;
        z-index: 1;
        flex: 1;
    }

    .stat-content p {
        font-size: 38px;
        font-weight: 700;
        color: white;
        margin: 0 0 5px;
        line-height: 1;
    }

    .stat-content h4 {
        font-size: 14px;
        font-weight: 500;
        color: white;
        text-transform: none;
        letter-spacing: 0;
        margin: 0;
    }

    .stat-card .more-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        background: rgba(0, 0, 0, 0.15);
        color: white;
        text-decoration: none;
        font-size: 13px;
        padding: 8px;
        margin: 10px -20px -15px -20px;
        transition: background 0.2s ease;
    }

    .stat-card .more-info:hover {
        background: rgba(0, 0, 0, 0.25);
    }

    .stat-card.bg-orange .stat-content p,
    .stat-card.bg-orange .stat-content h4,
    .stat-card.bg-yellow .stat-content p,
    .stat-card.bg-yellow .stat-content h4 {
        color: rgba(0, 0, 0, 0.85);
    }

    .stat-card.bg-orange .more-info,
    .stat-card.bg-yellow .more-info {
        color: rgba(0, 0, 0, 0.85);
    }

    .stat-card.bg-orange .stat-icon,
    .stat-card.bg-yellow .stat-icon {
        color: rgba(0, 0, 0, 0.15);
    }

    /* Section Title */
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: rgba(0, 0, 0, 0.8);
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #333;
        font-size: 18px;
    }

    /* Features Grid */
    .features-section {
        margin-bottom: 25px;
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    @media (max-width: 1200px) {
        .features-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .features-grid { grid-template-columns: 1fr; }
    }

    .feature-card {
        background: white;
        border-radius: 10px;
        padding: 22px;
        transition: all 0.2s ease;
        border-left: 4px solid transparent;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .feature-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .feature-card.border-green { border-left-color: #10b981; background: rgba(16, 185, 129, 0.03); }
    .feature-card.border-blue { border-left-color: #3b82f6; background: rgba(59, 130, 246, 0.03); }
    .feature-card.border-orange { border-left-color: #f59e0b; background: rgba(245, 158, 11, 0.03); }
    .feature-card.border-purple { border-left-color: #8b5cf6; background: rgba(139, 92, 246, 0.03); }

    .feature-icon {
        width: 46px;
        height: 46px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        margin-bottom: 14px;
    }

    .feature-icon.green { background: #10b981; }
    .feature-icon.blue { background: #3b82f6; }
    .feature-icon.orange { background: #f59e0b; }
    .feature-icon.purple { background: #8b5cf6; }

    .feature-title {
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 6px;
    }

    .feature-desc {
        font-size: 13px;
        color: #9ca3af;
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .feature-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        color: white;
        transition: all 0.2s ease;
    }

    .feature-btn.green { background: #10b981; }
    .feature-btn.green:hover { background: #059669; }
    .feature-btn.blue { background: #3b82f6; }
    .feature-btn.blue:hover { background: #2563eb; }
    .feature-btn.orange { background: #f59e0b; }
    .feature-btn.orange:hover { background: #d97706; }
    .feature-btn.purple { background: #8b5cf6; }
    .feature-btn.purple:hover { background: #7c3aed; }

    .feature-btn.disabled {
        background: #9ca3af;
        cursor: not-allowed;
    }

    .feature-btn i {
        font-size: 16px;
    }

    /* Info Card */
    .info-card {
        background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
        border-radius: 10px;
        padding: 20px 24px;
        margin-bottom: 20px;
    }

    .info-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 10px;
    }

    .info-card-icon {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .info-card-title {
        font-size: 15px;
        font-weight: 600;
        color: white;
    }

    .info-card-text {
        color: rgba(255, 255, 255, 0.85);
        font-size: 13px;
        line-height: 1.6;
        margin: 0;
        padding-left: 48px;
    }

    /* Quick Actions */
    .quick-actions {
        background: #fef3c7;
        border-radius: 10px;
        overflow: hidden;
    }

    .quick-actions-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 20px;
        background: transparent;
    }

    .quick-actions-icon {
        width: 36px;
        height: 36px;
        background: #f59e0b;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .quick-actions-title {
        font-size: 15px;
        font-weight: 600;
        color: #b45309;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        padding: 0 20px 20px 20px;
    }

    @media (max-width: 1200px) {
        .quick-actions-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .quick-actions-grid { grid-template-columns: 1fr; }
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        background: white;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        border-bottom: 3px solid #3b82f6;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .quick-action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .quick-action-btn i {
        font-size: 18px;
        color: #f59e0b;
    }

    .quick-action-btn span {
        font-size: 13px;
        font-weight: 500;
        color: #374151;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .exams-dashboard {
            margin-left: 0;
            padding: 15px;
        }

        .page-header {
            padding: 22px;
        }

        .header-title {
            font-size: 20px;
        }
    }
</style>

<div class="exams-dashboard">
    <!-- Breadcrumb -->
    <nav class="breadcrumb-nav">
        <a href="{{ route('dashboard') }}" class="breadcrumb-btn">
            <i class='bx bx-home'></i>
            Dashboard
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.index') }}" class="breadcrumb-btn">
            <i class='bx bx-building'></i>
            College
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-graduation'></i>
            Exams & Academics
        </span>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1 class="header-title">
            <i class='bx bx-book-reader'></i>
            Exams & Academics Management
        </h1>
        <p class="header-subtitle">
            Manage examinations, assessments, and academic records.
        </p>
    </div>

    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card bg-blue">
            <div class="stat-icon">
                <i class='bx bx-book'></i>
            </div>
            <div class="stat-content">
                <p>{{ \App\Models\College\Course::count() }}</p>
                <h4>Total Courses</h4>
            </div>
            <a href="{{ route('college.courses.index') }}" class="more-info">
                More info <i class='bx bx-link-external'></i>
            </a>
        </div>

        <div class="stat-card bg-green">
            <div class="stat-icon">
                <i class='bx bx-bar-chart-alt-2'></i>
            </div>
            <div class="stat-content">
                <p>{{ \App\Models\College\AssessmentType::count() ?: DB::table('assessment_types')->count() }}</p>
                <h4>Assessment Types</h4>
            </div>
            <a href="#" class="more-info">
                More info <i class='bx bx-link-external'></i>
            </a>
        </div>

        <div class="stat-card bg-yellow">
            <div class="stat-icon">
                <i class='bx bx-calendar-event'></i>
            </div>
            <div class="stat-content">
                <p>{{ \App\Models\College\ExamSchedule::where('exam_date', '>=', today())->where('status', '!=', 'cancelled')->count() }}</p>
                <h4>Upcoming Exams</h4>
            </div>
            <a href="{{ route('college.exam-schedules.index') }}" class="more-info">
                More info <i class='bx bx-link-external'></i>
            </a>
        </div>

        <div class="stat-card bg-red">
            <div class="stat-icon">
                <i class='bx bx-pie-chart-alt-2'></i>
            </div>
            <div class="stat-content">
                <p>{{ \App\Models\College\AssessmentScore::where('status', 'published')->count() + \App\Models\College\CourseResult::where('result_status', 'published')->count() }}</p>
                <h4>Results Published</h4>
            </div>
            <a href="{{ route('college.assessment-scores.index') }}" class="more-info">
                More info <i class='bx bx-link-external'></i>
            </a>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features-section">
        <h2 class="section-title">
            <i class='bx bx-grid-alt'></i>
            Management Features
        </h2>

        <div class="features-grid">
            <div class="feature-card border-green">
                <div class="feature-icon green">
                    <i class='bx bx-book-open'></i>
                </div>
                <h3 class="feature-title">Courses</h3>
                <p class="feature-desc">Manage curriculum and course materials.</p>
                <a href="{{ route('college.courses.index') }}" class="feature-btn green">
                    <i class='bx bx-right-arrow-alt'></i>
                    Manage
                </a>
            </div>

            <div class="feature-card border-blue">
                <div class="feature-icon blue">
                    <i class='bx bx-calendar-check'></i>
                </div>
                <h3 class="feature-title">Exam Schedules</h3>
                <p class="feature-desc">Create exam timetables and venues.</p>
                <a href="{{ route('college.exam-schedules.index') }}" class="feature-btn blue">
                    <i class='bx bx-right-arrow-alt'></i>
                    Manage
                </a>
            </div>

            <div class="feature-card border-orange">
                <div class="feature-icon orange">
                    <i class='bx bx-edit'></i>
                </div>
                <h3 class="feature-title">CA Scores</h3>
                <p class="feature-desc">Record tests and assignment scores.</p>
                <a href="{{ route('college.assessment-scores.index') }}" class="feature-btn orange">
                    <i class='bx bx-right-arrow-alt'></i>
                    Enter
                </a>
            </div>

            <div class="feature-card border-purple">
                <div class="feature-icon purple">
                    <i class='bx bx-file'></i>
                </div>
                <h3 class="feature-title">Exam Scores</h3>
                <p class="feature-desc">Record final and supplementary exams.</p>
                <a href="{{ route('college.final-exam-scores.index') }}" class="feature-btn purple">
                    <i class='bx bx-right-arrow-alt'></i>
                    Enter
                </a>
            </div>
        </div>

        <!-- Second Row -->
        <div class="features-grid" style="margin-top: 18px;">
            <div class="feature-card border-green">
                <div class="feature-icon green">
                    <i class='bx bx-trophy'></i>
                </div>
                <h3 class="feature-title">Final Results</h3>
                <p class="feature-desc">View combined results with grades.</p>
                <a href="{{ route('college.course-results.index') }}" class="feature-btn green">
                    <i class='bx bx-right-arrow-alt'></i>
                    View
                </a>
            </div>

            <div class="feature-card border-blue">
                <div class="feature-icon blue">
                    <i class='bx bx-calculator'></i>
                </div>
                <h3 class="feature-title">Generate Results</h3>
                <p class="feature-desc">Auto-calculate grades and GPA.</p>
                <a href="{{ route('college.course-results.generate') }}" class="feature-btn blue">
                    <i class='bx bx-right-arrow-alt'></i>
                    Generate
                </a>
            </div>

            <div class="feature-card border-orange">
                <div class="feature-icon orange">
                    <i class='bx bx-bar-chart-alt-2'></i>
                </div>
                <h3 class="feature-title">Reports</h3>
                <p class="feature-desc">Performance reports and analytics.</p>
                <button class="feature-btn disabled" disabled>
                    <i class='bx bx-time'></i>
                    Soon
                </button>
            </div>

            <div class="feature-card border-purple">
                <div class="feature-icon purple">
                    <i class='bx bx-user-circle'></i>
                </div>
                <h3 class="feature-title">Student Portal</h3>
                <p class="feature-desc">Students view their results.</p>
                <a href="{{ route('college.student-portal.final-results') }}" class="feature-btn purple">
                    <i class='bx bx-right-arrow-alt'></i>
                    View
                </a>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="info-card">
        <div class="info-card-header">
            <div class="info-card-icon">
                <i class='bx bx-info-circle'></i>
            </div>
            <h3 class="info-card-title">About This Module</h3>
        </div>
        <p class="info-card-text">
            Manage examinations, record student results, and generate academic performance reports.
        </p>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <div class="quick-actions-header">
            <div class="quick-actions-icon">
                <i class='bx bx-zap'></i>
            </div>
            <h3 class="quick-actions-title">Quick Actions</h3>
        </div>

        <div class="quick-actions-grid">
            <a href="{{ route('college.assessment-scores.create') }}" class="quick-action-btn">
                <i class='bx bx-edit'></i>
                <span>Enter CA Scores</span>
            </a>

            <a href="{{ route('college.final-exam-scores.create') }}" class="quick-action-btn">
                <i class='bx bx-file'></i>
                <span>Enter Exam Scores</span>
            </a>

            <a href="{{ route('college.course-results.generate') }}" class="quick-action-btn">
                <i class='bx bx-calculator'></i>
                <span>Generate Results</span>
            </a>

            <a href="{{ route('college.exam-schedules.create') }}" class="quick-action-btn">
                <i class='bx bx-plus-circle'></i>
                <span>Create Exam Schedule</span>
            </a>

            <a href="{{ route('college.courses.create') }}" class="quick-action-btn">
                <i class='bx bx-book-add'></i>
                <span>Add New Course</span>
            </a>

            <a href="{{ route('college.exam-schedules.calendar') }}" class="quick-action-btn">
                <i class='bx bx-calendar'></i>
                <span>View Calendar</span>
            </a>

            <a href="{{ route('college.student-portal.ca-results') }}" class="quick-action-btn">
                <i class='bx bx-user'></i>
                <span>Student CA Results</span>
            </a>

            <a href="{{ route('college.student-portal.transcript') }}" class="quick-action-btn">
                <i class='bx bx-certification'></i>
                <span>View Transcript</span>
            </a>
        </div>
    </div>
</div>
@endsection
