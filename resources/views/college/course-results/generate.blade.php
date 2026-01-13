@extends('layouts.main')

@section('title', 'Generate Course Results')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .generate-results-container {
        margin-left: 250px;
        padding: 20px 30px;
        background: #f8fafc;
        min-height: 100vh;
    }

    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 15px 0;
        margin-top: 70px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .breadcrumb-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #64748b;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .breadcrumb-btn:hover {
        background: #3b82f6;
        border-color: transparent;
        color: white;
    }

    .breadcrumb-btn.active {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        border-color: transparent;
        color: white;
        font-weight: 600;
    }

    .breadcrumb-separator {
        color: #f59e0b;
        font-size: 20px;
        font-weight: bold;
    }

    .page-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 10px 40px rgba(139, 92, 246, 0.3);
    }

    .page-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header p {
        opacity: 0.9;
        font-size: 15px;
        margin: 0;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 25px;
    }

    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Form Card Styles */
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 25px;
    }

    .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #e2e8f0;
    }

    .card-header h3 {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-header h3 i {
        color: #8b5cf6;
    }

    .card-body {
        padding: 25px;
    }

    .form-row {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group label .required {
        color: #ef4444;
    }

    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #8b5cf6;
        background: white;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    /* Select2 Custom Styling */
    .select2-container--default .select2-selection--single {
        height: 48px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 8px 15px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 28px;
        color: #1e293b;
        padding-left: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
        right: 10px;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #8b5cf6;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }

    .select2-dropdown {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .select2-results__option--highlighted[aria-selected] {
        background: #8b5cf6 !important;
    }

    /* Button Styles */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
    }

    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    }

    .btn-secondary {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .btn-secondary:hover {
        background: #f8fafc;
        color: #1e293b;
    }

    .btn-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    }

    .form-actions {
        display: flex;
        gap: 12px;
        padding: 20px 25px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    /* Sidebar Styles */
    .sidebar-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .sidebar-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        padding: 20px;
        color: white;
    }

    .sidebar-header h4 {
        font-size: 16px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar-body {
        padding: 20px;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-list li:last-child {
        border-bottom: none;
    }

    .info-list .label {
        font-size: 13px;
        color: #64748b;
    }

    .info-list .value {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        max-width: 180px;
        text-align: right;
        word-wrap: break-word;
    }

    /* Info Card */
    .info-card {
        background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
        border: 1px solid #c4b5fd;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .info-card i {
        font-size: 24px;
        color: #7c3aed;
    }

    .info-card h4 {
        font-size: 14px;
        font-weight: 700;
        color: #5b21b6;
        margin: 0 0 8px;
    }

    .info-card p {
        font-size: 13px;
        color: #6d28d9;
        margin: 0;
        line-height: 1.5;
    }

    /* Preview Card */
    .preview-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        display: none;
    }

    .preview-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        padding: 20px 25px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .preview-header h3 {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .preview-header-actions {
        display: flex;
        gap: 10px;
    }

    .preview-header .btn {
        padding: 8px 16px;
        font-size: 13px;
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
        padding: 20px 25px;
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border-bottom: 1px solid #ddd6fe;
    }

    @media (max-width: 1200px) {
        .stats-row {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stat-box {
        text-align: center;
    }

    .stat-box .stat-value {
        font-size: 24px;
        font-weight: 800;
        color: #5b21b6;
        line-height: 1;
    }

    .stat-box .stat-label {
        font-size: 11px;
        font-weight: 600;
        color: #7c3aed;
        text-transform: uppercase;
        margin-top: 5px;
    }

    /* Results Table */
    .results-table-wrapper {
        overflow-x: auto;
    }

    .results-table {
        width: 100%;
        border-collapse: collapse;
    }

    .results-table th {
        background: #f8fafc;
        padding: 15px 20px;
        text-align: left;
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #e2e8f0;
    }

    .results-table td {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #1e293b;
    }

    .results-table tbody tr:hover {
        background: #f8fafc;
    }

    .student-cell {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .student-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #7c3aed;
        font-size: 14px;
    }

    .student-info h4 {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .student-info span {
        font-size: 12px;
        color: #64748b;
    }

    .score-cell {
        font-weight: 600;
        text-align: center;
    }

    .grade-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        min-width: 45px;
    }

    .grade-a { background: #d1fae5; color: #065f46; }
    .grade-b { background: #dbeafe; color: #1e40af; }
    .grade-c { background: #fef3c7; color: #92400e; }
    .grade-d { background: #fed7aa; color: #9a3412; }
    .grade-f { background: #fecaca; color: #991b1b; }

    .status-pass {
        color: #10b981;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .status-fail {
        color: #ef4444;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Preview Actions */
    .preview-actions {
        padding: 20px 25px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .checkbox-group {
        display: flex;
        gap: 20px;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #374151;
        cursor: pointer;
    }

    .checkbox-label input[type="checkbox"] {
        width: 18px;
        height: 18px;
        accent-color: #8b5cf6;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: #64748b;
    }

    .empty-state i {
        font-size: 60px;
        color: #c4b5fd;
        margin-bottom: 15px;
    }

    .empty-state h4 {
        font-size: 18px;
        color: #374151;
        margin: 0 0 8px;
    }

    .empty-state p {
        font-size: 14px;
        margin: 0;
    }

    /* Grading Scale */
    .grading-scale {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 20px;
    }

    .grading-scale h4 {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .grading-scale h4 i {
        color: #8b5cf6;
    }

    .grade-scale-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
    }

    @media (max-width: 768px) {
        .grade-scale-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    .grade-item {
        text-align: center;
        padding: 12px 8px;
        border-radius: 8px;
        background: #f8fafc;
    }

    .grade-item .letter {
        font-size: 18px;
        font-weight: 800;
    }

    .grade-item .range {
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }

    .grade-item .points {
        font-size: 11px;
        font-weight: 600;
        color: #374151;
        margin-top: 2px;
    }

    /* Alert Styles */
    .alert {
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .alert-info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-overlay.show {
        display: flex;
    }

    .loading-spinner {
        text-align: center;
    }

    .loading-spinner i {
        font-size: 50px;
        color: #8b5cf6;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .loading-spinner p {
        margin-top: 15px;
        color: #374151;
        font-weight: 600;
        font-size: 16px;
    }

    @media (max-width: 768px) {
        .generate-results-container {
            margin-left: 0;
            padding: 15px;
        }

        .breadcrumb-nav {
            margin-top: 60px;
        }

        .page-header {
            padding: 20px;
        }

        .page-header h1 {
            font-size: 22px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .preview-actions {
            flex-direction: column;
            gap: 15px;
        }

        .checkbox-group {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
@endpush

@section('content')
<div class="generate-results-container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <a href="{{ route('college.course-results.index') }}" class="breadcrumb-btn">
            <i class='bx bx-list-ul'></i> All Results
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-calculator'></i> Generate Results
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1>
            <i class='bx bx-calculator'></i>
            Generate Course Results
        </h1>
        <p>Calculate end-of-semester results by combining CA (40%) and Final Exam (60%) scores</p>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success">
        <i class='bx bx-check-circle' style="font-size: 20px;"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger">
        <i class='bx bx-error-circle' style="font-size: 20px;"></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul style="margin: 5px 0 0 20px; padding: 0;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="content-grid">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Info Card -->
            <div class="info-card">
                <i class='bx bx-info-circle'></i>
                <div>
                    <h4>How Results are Calculated (Weighted Grading)</h4>
                    <p><strong>Continuous Assessment (CA) = 40%</strong> | <strong>Final Examination = 60%</strong></p>
                    <p style="margin-top: 8px; font-size: 13px;">
                        The system calculates: <strong>Total = CA (40%) + Exam (60%)</strong>. 
                        CA percentage is derived from all assessment scores, then weighted to 40%. 
                        Exam percentage is weighted to 60%. Pass mark is 40%.
                    </p>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="form-card">
                <div class="card-header">
                    <h3><i class='bx bx-filter-alt'></i> Select Course for Results</h3>
                </div>
                <div class="card-body">
                    <form id="filterForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Academic Year <span class="required">*</span></label>
                                <select name="academic_year_id" id="academic_year_id" class="form-control select2" required>
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ request('academic_year_id', $currentAcademicYear?->id) == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Semester <span class="required">*</span></label>
                                <select name="semester_id" id="semester_id" class="form-control select2" required>
                                    <option value="">Select Semester</option>
                                    @foreach($semesters as $semester)
                                    <option value="{{ $semester->id }}" {{ request('semester_id', $currentSemester?->id) == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Program <span class="required">*</span></label>
                                <select name="program_id" id="program_id" class="form-control select2" required>
                                    <option value="">Select Program</option>
                                    @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Course <span class="required">*</span></label>
                                <select name="course_id" id="course_id" class="form-control select2" required>
                                    <option value="">Select Course</option>
                                    @foreach($courses as $course)
                                    <option value="{{ $course->id }}" data-name="{{ $course->code }} - {{ $course->name }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->code }} - {{ $course->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-primary" id="previewBtn">
                        <i class='bx bx-show'></i> Preview Results
                    </button>
                    <button type="button" class="btn btn-warning" id="generateAllBtn">
                        <i class='bx bx-refresh'></i> Generate All for Semester
                    </button>
                </div>
            </div>

            <!-- Initial Message -->
            <div class="alert alert-info" id="initialMessage">
                <i class='bx bx-info-circle' style="font-size: 20px;"></i>
                <span>Select a program and course above to preview and generate results.</span>
            </div>

            <!-- Preview Card -->
            <div class="preview-card" id="previewCard">
                <form id="saveResultsForm" action="{{ route('college.course-results.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="course_id" id="form_course_id">
                    <input type="hidden" name="academic_year_id" id="form_academic_year_id">
                    <input type="hidden" name="semester_id" id="form_semester_id">

                    <div class="preview-header">
                        <h3><i class='bx bx-table'></i> Results Preview - <span id="courseName">Course</span></h3>
                        <div class="preview-header-actions">
                            <button type="button" class="btn btn-secondary" id="exportBtn">
                                <i class='bx bx-download'></i> Export
                            </button>
                        </div>
                    </div>

                    <!-- Stats Row -->
                    <div class="stats-row">
                        <div class="stat-box">
                            <div class="stat-value" id="totalStudents">0</div>
                            <div class="stat-label">Total Students</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value" id="passRate">0%</div>
                            <div class="stat-label">Pass Rate</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value" id="avgScore">0</div>
                            <div class="stat-label">Average Score</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value" id="highestScore">0</div>
                            <div class="stat-label">Highest</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value" id="lowestScore">0</div>
                            <div class="stat-label">Lowest</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value" id="classGPA">0.00</div>
                            <div class="stat-label">Class GPA</div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <div class="results-table-wrapper">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th style="text-align: center;">CA Total</th>
                                    <th style="text-align: center;">Exam Score</th>
                                    <th style="text-align: center;">Total</th>
                                    <th style="text-align: center;">Grade</th>
                                    <th style="text-align: center;">GPA</th>
                                    <th style="text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody id="resultsTableBody">
                                <!-- Results will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div class="empty-state" id="emptyState" style="display: none;">
                        <i class='bx bx-calculator'></i>
                        <h4>No Results to Generate</h4>
                        <p>No students found for this course or missing score data.</p>
                    </div>

                    <!-- Preview Actions -->
                    <div class="preview-actions">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="publish_results" value="1">
                                Publish results to students
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="auto_approve" value="1">
                                Auto-approve results
                            </label>
                        </div>
                        <div style="display: flex; gap: 12px;">
                            <a href="{{ route('college.course-results.index') }}" class="btn btn-secondary">
                                <i class='bx bx-x'></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success" id="saveBtn" disabled>
                                <i class='bx bx-save'></i> Save Results
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Quick Stats -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h4><i class='bx bx-stats'></i> Current Selection</h4>
                </div>
                <div class="sidebar-body">
                    <ul class="info-list">
                        <li>
                            <span class="label">Academic Year</span>
                            <span class="value" id="selectedYear">-</span>
                        </li>
                        <li>
                            <span class="label">Semester</span>
                            <span class="value" id="selectedSemester">-</span>
                        </li>
                        <li>
                            <span class="label">Program</span>
                            <span class="value" id="selectedProgram">-</span>
                        </li>
                        <li>
                            <span class="label">Course</span>
                            <span class="value" id="selectedCourse">-</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Grading Scale -->
            <div class="grading-scale">
                <h4><i class='bx bx-book-open'></i> Grading Scale</h4>
                <div class="grade-scale-grid">
                    <div class="grade-item">
                        <div class="letter" style="color: #065f46;">A</div>
                        <div class="range">80-100</div>
                        <div class="points">4.0 pts</div>
                    </div>
                    <div class="grade-item">
                        <div class="letter" style="color: #1e40af;">B</div>
                        <div class="range">70-79</div>
                        <div class="points">3.0 pts</div>
                    </div>
                    <div class="grade-item">
                        <div class="letter" style="color: #92400e;">C</div>
                        <div class="range">60-69</div>
                        <div class="points">2.0 pts</div>
                    </div>
                    <div class="grade-item">
                        <div class="letter" style="color: #9a3412;">D</div>
                        <div class="range">50-59</div>
                        <div class="points">1.0 pts</div>
                    </div>
                    <div class="grade-item">
                        <div class="letter" style="color: #991b1b;">F</div>
                        <div class="range">0-49</div>
                        <div class="points">0.0 pts</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="sidebar-card" style="margin-top: 20px;">
                <div class="sidebar-header">
                    <h4><i class='bx bx-cog'></i> Quick Actions</h4>
                </div>
                <div class="sidebar-body" style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="{{ route('college.course-results.index') }}" class="btn btn-secondary" style="width: 100%;">
                        <i class='bx bx-list-ul'></i> View All Results
                    </a>
                    <a href="{{ route('college.assessment-scores.index') }}" class="btn btn-secondary" style="width: 100%;">
                        <i class='bx bx-clipboard'></i> Assessment Scores
                    </a>
                    <a href="{{ route('college.final-exam-scores.index') }}" class="btn btn-secondary" style="width: 100%;">
                        <i class='bx bx-file'></i> Final Exam Scores
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <i class='bx bx-loader-alt'></i>
        <p>Calculating results...</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2').select2({
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Select an option';
        }
    });

    const programSelect = document.getElementById('program_id');
    const courseSelect = document.getElementById('course_id');
    const previewCard = document.getElementById('previewCard');
    const initialMessage = document.getElementById('initialMessage');
    const resultsTableBody = document.getElementById('resultsTableBody');
    const emptyState = document.getElementById('emptyState');
    const saveBtn = document.getElementById('saveBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');

    // Update sidebar info on selection change
    function updateSidebarInfo() {
        const yearSelect = document.getElementById('academic_year_id');
        const semesterSelect = document.getElementById('semester_id');
        
        document.getElementById('selectedYear').textContent = yearSelect.options[yearSelect.selectedIndex]?.text || '-';
        document.getElementById('selectedSemester').textContent = semesterSelect.options[semesterSelect.selectedIndex]?.text || '-';
        document.getElementById('selectedProgram').textContent = programSelect.options[programSelect.selectedIndex]?.text || '-';
        document.getElementById('selectedCourse').textContent = courseSelect.options[courseSelect.selectedIndex]?.text || '-';
    }

    // Listen for changes
    document.getElementById('academic_year_id').addEventListener('change', updateSidebarInfo);
    document.getElementById('semester_id').addEventListener('change', updateSidebarInfo);
    $('#program_id').on('change', function() {
        updateSidebarInfo();
        loadCourses();
    });
    $('#course_id').on('change', updateSidebarInfo);

    // Load courses when program changes
    function loadCourses() {
        const programId = programSelect.value;
        const academicYearId = document.getElementById('academic_year_id').value;
        const semesterId = document.getElementById('semester_id').value;

        // Reset course select
        $('#course_id').empty().append('<option value="">Loading...</option>').trigger('change');

        // Only require program_id to load courses
        if (programId) {
            let url = `/college/api/courses-by-program?program_id=${programId}`;
            if (academicYearId) url += `&academic_year_id=${academicYearId}`;
            if (semesterId) url += `&semester_id=${semesterId}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    $('#course_id').empty().append('<option value="">Select Course</option>');
                    if (data && data.length > 0) {
                        data.forEach(course => {
                            $('#course_id').append(`<option value="${course.id}" data-name="${course.code} - ${course.name}">${course.code} - ${course.name}</option>`);
                        });
                    } else {
                        $('#course_id').empty().append('<option value="">No courses found</option>');
                    }
                    $('#course_id').trigger('change');
                })
                .catch((error) => {
                    console.error('Error loading courses:', error);
                    $('#course_id').empty().append('<option value="">Failed to load courses</option>').trigger('change');
                });
        } else {
            $('#course_id').empty().append('<option value="">Select Program first</option>').trigger('change');
        }
    }

    // Preview results
    document.getElementById('previewBtn').addEventListener('click', function() {
        const courseId = courseSelect.value;
        const academicYearId = document.getElementById('academic_year_id').value;
        const semesterId = document.getElementById('semester_id').value;

        if (!courseId) {
            alert('Please select a course');
            return;
        }

        const selectedOption = courseSelect.options[courseSelect.selectedIndex];
        const courseName = selectedOption.dataset.name || 'Course';

        document.getElementById('courseName').textContent = courseName;
        document.getElementById('form_course_id').value = courseId;
        document.getElementById('form_academic_year_id').value = academicYearId;
        document.getElementById('form_semester_id').value = semesterId;

        loadingOverlay.classList.add('show');

        fetch(`/college/api/calculate-results-preview?course_id=${courseId}&academic_year_id=${academicYearId}&semester_id=${semesterId}`)
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server returned ${response.status}: ${text.substring(0, 200)}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                loadingOverlay.classList.remove('show');

                if (!data.students || data.students.length === 0) {
                    resultsTableBody.innerHTML = '';
                    emptyState.style.display = 'block';
                    document.querySelector('.results-table-wrapper').style.display = 'none';
                    previewCard.style.display = 'block';
                    initialMessage.style.display = 'none';
                    saveBtn.disabled = true;
                    return;
                }

                emptyState.style.display = 'none';
                document.querySelector('.results-table-wrapper').style.display = 'block';
                previewCard.style.display = 'block';
                initialMessage.style.display = 'none';
                saveBtn.disabled = false;

                // Update stats
                document.getElementById('totalStudents').textContent = data.stats.total;
                document.getElementById('passRate').textContent = data.stats.pass_rate + '%';
                document.getElementById('avgScore').textContent = data.stats.average.toFixed(1);
                document.getElementById('highestScore').textContent = data.stats.highest;
                document.getElementById('lowestScore').textContent = data.stats.lowest;
                document.getElementById('classGPA').textContent = data.stats.class_gpa.toFixed(2);

                // Build table
                let html = '';
                data.students.forEach((student, index) => {
                    const initials = student.name.split(' ').map(n => n[0]).join('').substring(0, 2);
                    const gradeClass = getGradeClass(student.grade);
                    
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>
                                <div class="student-cell">
                                    <div class="student-avatar">${initials}</div>
                                    <div class="student-info">
                                        <h4>${student.name}</h4>
                                        <span>${student.student_id}</span>
                                    </div>
                                </div>
                                <input type="hidden" name="results[${index}][course_registration_id]" value="${student.registration_id}">
                                <input type="hidden" name="results[${index}][ca_total]" value="${student.ca_total}">
                                <input type="hidden" name="results[${index}][exam_score]" value="${student.exam_score}">
                                <input type="hidden" name="results[${index}][total]" value="${student.total}">
                                <input type="hidden" name="results[${index}][grade]" value="${student.grade}">
                                <input type="hidden" name="results[${index}][gpa]" value="${student.gpa}">
                            </td>
                            <td class="score-cell">${student.ca_total !== null ? student.ca_total : '-'}</td>
                            <td class="score-cell">${student.exam_score !== null ? student.exam_score : '-'}</td>
                            <td class="score-cell" style="font-weight: 700;">${student.total !== null ? student.total : '-'}</td>
                            <td style="text-align: center;">
                                <span class="grade-badge ${gradeClass}">${student.grade || '-'}</span>
                            </td>
                            <td class="score-cell">${student.gpa !== null ? student.gpa.toFixed(2) : '-'}</td>
                            <td style="text-align: center;">
                                ${student.total >= 50 ? '<span class="status-pass"><i class="bx bx-check-circle"></i> Pass</span>' : '<span class="status-fail"><i class="bx bx-x-circle"></i> Fail</span>'}
                            </td>
                        </tr>
                    `;
                });
                resultsTableBody.innerHTML = html;
            })
            .catch(error => {
                loadingOverlay.classList.remove('show');
                alert('Failed to calculate results: ' + error.message);
                console.error('Preview Error:', error);
            });
    });

    function getGradeClass(grade) {
        if (!grade) return '';
        const letter = grade.charAt(0).toUpperCase();
        switch(letter) {
            case 'A': return 'grade-a';
            case 'B': return 'grade-b';
            case 'C': return 'grade-c';
            case 'D': return 'grade-d';
            case 'F': return 'grade-f';
            default: return '';
        }
    }

    // Generate all results for semester
    document.getElementById('generateAllBtn').addEventListener('click', function() {
        const academicYearId = document.getElementById('academic_year_id').value;
        const semesterId = document.getElementById('semester_id').value;

        if (!academicYearId || !semesterId) {
            alert('Please select academic year and semester');
            return;
        }

        if (confirm('This will calculate results for ALL courses in the selected semester. Continue?')) {
            loadingOverlay.classList.add('show');
            
            fetch(`/college/course-results/generate-all`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    academic_year_id: academicYearId,
                    semester_id: semesterId
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`Server returned ${response.status}: ${text.substring(0, 200)}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                loadingOverlay.classList.remove('show');
                if (data.success) {
                    alert(data.message || 'Results generated successfully!');
                    window.location.href = '{{ route("college.course-results.index") }}';
                } else {
                    alert('Error: ' + (data.message || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                loadingOverlay.classList.remove('show');
                alert('Failed to generate results: ' + error.message);
                console.error('Generate All Error:', error);
            });
        }
    });

    // Initial sidebar update
    updateSidebarInfo();

    // Export button click handler
    document.getElementById('exportBtn').addEventListener('click', function() {
        const table = document.querySelector('.results-table');
        const rows = table.querySelectorAll('tbody tr');
        
        if (rows.length === 0) {
            alert('No data to export. Please preview results first.');
            return;
        }

        const courseName = document.getElementById('courseName').textContent;
        const academicYear = document.getElementById('sidebarYear').textContent;
        const semester = document.getElementById('sidebarSemester').textContent;

        // Build CSV content
        let csvContent = 'Course Results Export\n';
        csvContent += `Course: ${courseName}\n`;
        csvContent += `Academic Year: ${academicYear}\n`;
        csvContent += `Semester: ${semester}\n`;
        csvContent += `Export Date: ${new Date().toLocaleDateString()}\n\n`;
        
        // Stats
        csvContent += 'Summary Statistics\n';
        csvContent += `Total Students,${document.getElementById('totalStudents').textContent}\n`;
        csvContent += `Pass Rate,${document.getElementById('passRate').textContent}\n`;
        csvContent += `Average Score,${document.getElementById('avgScore').textContent}\n`;
        csvContent += `Highest Score,${document.getElementById('highestScore').textContent}\n`;
        csvContent += `Lowest Score,${document.getElementById('lowestScore').textContent}\n`;
        csvContent += `Class GPA,${document.getElementById('classGPA').textContent}\n\n`;
        
        // Headers
        csvContent += '#,Student ID,Student Name,CA Total,Exam Score,Total,Grade,GPA,Status\n';
        
        // Data rows
        rows.forEach((row, index) => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 8) {
                const studentInfo = cells[1].querySelector('.student-info');
                const studentName = studentInfo ? studentInfo.querySelector('h4').textContent : '';
                const studentId = studentInfo ? studentInfo.querySelector('span').textContent : '';
                const caTotal = cells[2].textContent.trim();
                const examScore = cells[3].textContent.trim();
                const total = cells[4].textContent.trim();
                const grade = cells[5].textContent.trim();
                const gpa = cells[6].textContent.trim();
                const status = cells[7].textContent.trim().replace(/\s+/g, ' ');
                
                csvContent += `${index + 1},"${studentId}","${studentName}",${caTotal},${examScore},${total},${grade},${gpa},"${status}"\n`;
            }
        });

        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const fileName = `Course_Results_${courseName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().slice(0,10)}.csv`;
        
        if (navigator.msSaveBlob) {
            // IE 10+
            navigator.msSaveBlob(blob, fileName);
        } else {
            link.href = URL.createObjectURL(blob);
            link.download = fileName;
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    });
});
</script>
@endpush
