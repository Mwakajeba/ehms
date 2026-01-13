@extends('layouts.main')

@section('title', 'My Final Results')

@section('content')
<style>
    .student-portal {
        margin-left: 250px;
        padding: 20px 30px;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
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
        background: #f59e0b;
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
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 25px;
        color: white;
    }

    .page-header h1 {
        font-size: 26px;
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

    .cgpa-card {
        background: white;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        display: flex;
        justify-content: space-around;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
        border: 2px solid #f59e0b;
    }

    .cgpa-item {
        text-align: center;
    }

    .cgpa-item label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        display: block;
        margin-bottom: 5px;
    }

    .cgpa-item .value {
        font-size: 36px;
        font-weight: 800;
        color: #f59e0b;
    }

    .cgpa-item .value.small {
        font-size: 28px;
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 25px;
        border: 1px solid #e5e7eb;
    }

    .filter-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }

    .form-group {
        flex: 1;
        min-width: 200px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 5px;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
    }

    .form-control:focus {
        outline: none;
        border-color: #f59e0b;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .btn-primary { background: #f59e0b; color: white; }
    .btn-primary:hover { background: #d97706; }
    .btn-info { background: #3b82f6; color: white; }
    .btn-info:hover { background: #2563eb; }

    .semester-section {
        margin-bottom: 30px;
    }

    .semester-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border-radius: 12px 12px 0 0;
        padding: 18px 24px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .semester-title {
        font-size: 18px;
        font-weight: 600;
    }

    .semester-stats {
        display: flex;
        gap: 25px;
    }

    .semester-stat {
        text-align: center;
    }

    .semester-stat label {
        font-size: 10px;
        opacity: 0.8;
        display: block;
        margin-bottom: 2px;
    }

    .semester-stat .value {
        font-size: 20px;
        font-weight: 700;
    }

    .semester-stat .value.gpa { color: #fbbf24; }

    .results-table {
        background: white;
        border-radius: 0 0 12px 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-top: none;
    }

    .results-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .results-table th {
        background: #f8fafc;
        padding: 14px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        border-bottom: 1px solid #e5e7eb;
    }

    .results-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
        color: #374151;
    }

    .results-table tr:hover {
        background: #fefce8;
    }

    .course-code {
        font-weight: 700;
        color: #1e293b;
    }

    .course-name {
        font-size: 13px;
        color: #64748b;
    }

    .marks-cell {
        text-align: center;
    }

    .marks-cell.ca { color: #10b981; font-weight: 600; }
    .marks-cell.exam { color: #3b82f6; font-weight: 600; }
    .marks-cell.total { color: #f59e0b; font-weight: 700; font-size: 16px; }

    .grade-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
    }

    .grade-badge.a { background: #dcfce7; color: #166534; }
    .grade-badge.b { background: #dbeafe; color: #1e40af; }
    .grade-badge.c { background: #fef3c7; color: #92400e; }
    .grade-badge.d { background: #fed7aa; color: #9a3412; }
    .grade-badge.f { background: #fee2e2; color: #991b1b; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.pass { background: #dcfce7; color: #166534; }
    .status-badge.fail { background: #fee2e2; color: #991b1b; }

    .gpa-cell {
        font-weight: 700;
        color: #1e293b;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
    }

    .empty-state i {
        font-size: 64px;
        color: #d1d5db;
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 18px;
        color: #374151;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #6b7280;
        font-size: 14px;
    }

    .info-banner {
        background: #fef3c7;
        border: 1px solid #fcd34d;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-banner i {
        font-size: 24px;
        color: #f59e0b;
    }

    .info-banner p {
        margin: 0;
        font-size: 14px;
        color: #92400e;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .student-portal {
            margin-left: 0;
            padding: 15px;
        }

        .cgpa-card {
            flex-direction: column;
        }

        .semester-stats {
            flex-wrap: wrap;
            gap: 15px;
        }

        .results-table {
            overflow-x: auto;
        }
    }
</style>

<div class="student-portal">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb-nav">
        <a href="{{ route('dashboard') }}" class="breadcrumb-btn">
            <i class='bx bx-home'></i> Dashboard
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="#" class="breadcrumb-btn">
            <i class='bx bx-user'></i> Student Portal
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-trophy'></i> Final Results
        </span>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class='bx bx-trophy'></i> My Final Course Results</h1>
        <p>View your combined results with CA Total, Final Exam Score, Grade, GPA, and Pass/Fail status</p>
    </div>

    <!-- CGPA Summary -->
    <div class="cgpa-card">
        <div class="cgpa-item">
            <label>Cumulative GPA (CGPA)</label>
            <div class="value">{{ number_format($cgpa, 2) }}</div>
        </div>
        <div class="cgpa-item">
            <label>Total Credit Hours</label>
            <div class="value small">{{ $totalCreditHours }}</div>
        </div>
        <div class="cgpa-item">
            <label>Courses Completed</label>
            <div class="value small">{{ $results->flatten()->count() }}</div>
        </div>
        <div class="cgpa-item">
            <label>Semesters</label>
            <div class="value small">{{ $results->count() }}</div>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="info-banner">
        <i class='bx bx-info-circle'></i>
        <p>Final results are calculated at the end of each semester by combining your CA scores (40%) and Final Exam scores (60%).</p>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('college.student-portal.transcript') }}" class="btn btn-info">
            <i class='bx bx-file'></i> View Full Transcript
        </a>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <form action="{{ route('college.student-portal.final-results') }}" method="GET">
            <div class="filter-row">
                <div class="form-group">
                    <label>Academic Year</label>
                    <select name="academic_year_id" class="form-control">
                        <option value="">All Years</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester_id" class="form-control">
                        <option value="">All Semesters</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                {{ $semester->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="flex: 0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-filter'></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Results by Semester -->
    @if($results->count() > 0)
        @foreach($results as $period => $periodResults)
            @php
                $stats = $semesterStats[$period] ?? ['gpa' => 0, 'credit_hours' => 0, 'courses_passed' => 0, 'courses_failed' => 0];
            @endphp
            <div class="semester-section">
                <div class="semester-header">
                    <div class="semester-title">{{ $period }}</div>
                    <div class="semester-stats">
                        <div class="semester-stat">
                            <label>Semester GPA</label>
                            <span class="value gpa">{{ number_format($stats['gpa'], 2) }}</span>
                        </div>
                        <div class="semester-stat">
                            <label>Credit Hours</label>
                            <span class="value">{{ $stats['credit_hours'] }}</span>
                        </div>
                        <div class="semester-stat">
                            <label>Passed</label>
                            <span class="value" style="color: #4ade80;">{{ $stats['courses_passed'] }}</span>
                        </div>
                        <div class="semester-stat">
                            <label>Failed</label>
                            <span class="value" style="color: #f87171;">{{ $stats['courses_failed'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="results-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th style="text-align: center;">Credit</th>
                                <th style="text-align: center;">CA (40%)</th>
                                <th style="text-align: center;">Exam (60%)</th>
                                <th style="text-align: center;">Total</th>
                                <th style="text-align: center;">Grade</th>
                                <th style="text-align: center;">GPA</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periodResults as $result)
                                <tr>
                                    <td>
                                        <div class="course-code">{{ $result->course->code ?? '' }}</div>
                                        <div class="course-name">{{ $result->course->name ?? '' }}</div>
                                    </td>
                                    <td style="text-align: center;">{{ $result->credit_hours }}</td>
                                    <td class="marks-cell ca">{{ number_format($result->ca_total, 1) }}</td>
                                    <td class="marks-cell exam">{{ number_format($result->exam_total, 1) }}</td>
                                    <td class="marks-cell total">{{ number_format($result->total_marks, 1) }}</td>
                                    <td style="text-align: center;">
                                        @php
                                            $gradeClass = strtolower(substr($result->grade ?? 'F', 0, 1));
                                        @endphp
                                        <span class="grade-badge {{ $gradeClass }}">{{ $result->grade }}</span>
                                    </td>
                                    <td class="gpa-cell" style="text-align: center;">{{ number_format($result->gpa_points, 2) }}</td>
                                    <td style="text-align: center;">
                                        <span class="status-badge {{ $result->course_status == 'Pass' ? 'pass' : 'fail' }}">
                                            <i class='bx bx-{{ $result->course_status == 'Pass' ? 'check' : 'x' }}'></i>
                                            {{ $result->course_status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class='bx bx-folder-open'></i>
            <h3>No Final Results Found</h3>
            <p>You don't have any published final results yet. Results are released at the end of each semester.</p>
        </div>
    @endif
</div>
@endsection
