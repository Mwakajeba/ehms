@extends('layouts.main')

@section('title', 'Course Results')

@section('content')
<style>
    .results-dashboard {
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
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 25px;
        color: white;
    }

    .page-header h1 {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header p {
        opacity: 0.9;
        font-size: 14px;
        margin: 0;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    @media (max-width: 1400px) {
        .stats-row { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 900px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .stats-row { grid-template-columns: 1fr; }
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 18px;
        border-left: 4px solid transparent;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .stat-card.orange { border-left-color: #f59e0b; }
    .stat-card.gray { border-left-color: #6b7280; }
    .stat-card.blue { border-left-color: #3b82f6; }
    .stat-card.purple { border-left-color: #8b5cf6; }
    .stat-card.green { border-left-color: #10b981; }
    .stat-card.red { border-left-color: #ef4444; }

    .stat-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: white;
    }

    .stat-icon.orange { background: #f59e0b; }
    .stat-icon.gray { background: #6b7280; }
    .stat-icon.blue { background: #3b82f6; }
    .stat-icon.purple { background: #8b5cf6; }
    .stat-icon.green { background: #10b981; }
    .stat-icon.red { background: #ef4444; }

    .stat-content h4 {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        margin: 0 0 4px;
    }

    .stat-content p {
        font-size: 22px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 15px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
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
        transition: all 0.3s;
    }

    .btn-primary { background: #f59e0b; color: white; }
    .btn-primary:hover { background: #d97706; }
    .btn-success { background: #10b981; color: white; }
    .btn-success:hover { background: #059669; }
    .btn-info { background: #3b82f6; color: white; }
    .btn-info:hover { background: #2563eb; }
    .btn-secondary { background: #6b7280; color: white; }
    .btn-secondary:hover { background: #4b5563; }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }

    .filter-card h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 15px;
    }

    @media (max-width: 1200px) {
        .filter-grid { grid-template-columns: repeat(3, 1fr); }
    }

    @media (max-width: 768px) {
        .filter-grid { grid-template-columns: 1fr; }
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
        transition: border-color 0.3s;
    }

    .form-control:focus {
        outline: none;
        border-color: #f59e0b;
    }

    .data-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .data-card-header {
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .data-card-header h3 {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        background: #f8fafc;
        padding: 12px 14px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        border-bottom: 1px solid #e5e7eb;
    }

    .data-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
        color: #374151;
    }

    .data-table tr:hover {
        background: #f8fafc;
    }

    .student-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #f59e0b;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 13px;
    }

    .student-details h4 {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .student-details span {
        font-size: 11px;
        color: #64748b;
    }

    .grade-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
    }

    .grade-badge.a { background: #dcfce7; color: #166534; }
    .grade-badge.b { background: #dbeafe; color: #1e40af; }
    .grade-badge.c { background: #fef3c7; color: #92400e; }
    .grade-badge.d { background: #fed7aa; color: #9a3412; }
    .grade-badge.f { background: #fee2e2; color: #991b1b; }

    .marks-breakdown {
        font-size: 12px;
    }

    .marks-breakdown .ca { color: #10b981; font-weight: 600; }
    .marks-breakdown .exam { color: #3b82f6; font-weight: 600; }
    .marks-breakdown .total { color: #f59e0b; font-weight: 700; font-size: 14px; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .status-badge.pass { background: #dcfce7; color: #166534; }
    .status-badge.fail { background: #fee2e2; color: #991b1b; }
    .status-badge.draft { background: #f3f4f6; color: #374151; }
    .status-badge.published { background: #dbeafe; color: #1e40af; }
    .status-badge.approved { background: #dcfce7; color: #166534; }

    .action-btns {
        display: flex;
        gap: 6px;
    }

    .action-btn {
        width: 30px;
        height: 30px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
        font-size: 14px;
    }

    .action-btn.view { background: #dbeafe; color: #3b82f6; }
    .action-btn.view:hover { background: #3b82f6; color: white; }
    .action-btn.publish { background: #dcfce7; color: #10b981; }
    .action-btn.publish:hover { background: #10b981; color: white; }
    .action-btn.approve { background: #fef3c7; color: #f59e0b; }
    .action-btn.approve:hover { background: #f59e0b; color: white; }

    .pagination-wrapper {
        padding: 20px;
        display: flex;
        justify-content: center;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
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

    @media (max-width: 768px) {
        .results-dashboard {
            margin-left: 0;
            padding: 15px;
        }
    }
</style>

<div class="results-dashboard">
    <!-- Breadcrumb Navigation -->
    <nav class="breadcrumb-nav">
        <a href="{{ route('dashboard') }}" class="breadcrumb-btn">
            <i class='bx bx-home'></i> Dashboard
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.index') }}" class="breadcrumb-btn">
            <i class='bx bx-building'></i> College
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.exams-management.dashboard') }}" class="breadcrumb-btn">
            <i class='bx bx-book-reader'></i> Exams & Academics
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-trophy'></i> Course Results
        </span>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class='bx bx-trophy'></i> Final Course Results</h1>
        <p>End-of-semester combined results with CA Total, Exam Score, Final Grade, GPA, and Pass/Fail status</p>
    </div>

    <!-- Statistics Row -->
    <div class="stats-row">
        <div class="stat-card orange">
            <div class="stat-icon orange"><i class='bx bx-file'></i></div>
            <div class="stat-content">
                <h4>Total</h4>
                <p>{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="stat-card gray">
            <div class="stat-icon gray"><i class='bx bx-edit'></i></div>
            <div class="stat-content">
                <h4>Draft</h4>
                <p>{{ number_format($stats['draft']) }}</p>
            </div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class='bx bx-upload'></i></div>
            <div class="stat-content">
                <h4>Published</h4>
                <p>{{ number_format($stats['published']) }}</p>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple"><i class='bx bx-check-shield'></i></div>
            <div class="stat-content">
                <h4>Approved</h4>
                <p>{{ number_format($stats['approved']) }}</p>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon green"><i class='bx bx-check-circle'></i></div>
            <div class="stat-content">
                <h4>Passed</h4>
                <p>{{ number_format($stats['passed']) }}</p>
            </div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red"><i class='bx bx-x-circle'></i></div>
            <div class="stat-content">
                <h4>Failed</h4>
                <p>{{ number_format($stats['failed']) }}</p>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="action-bar">
        <div class="action-buttons">
            <a href="{{ route('college.course-results.generate') }}" class="btn btn-primary">
                <i class='bx bx-calculator'></i> Generate Results
            </a>
            <form action="{{ route('college.course-results.bulk-publish') }}" method="POST" style="display:inline;" id="bulkPublishForm">
                @csrf
                <input type="hidden" name="academic_year_id" id="publish_year_id" value="{{ request('academic_year_id') }}">
                <input type="hidden" name="semester_id" id="publish_semester_id" value="{{ request('semester_id') }}">
                <button type="button" class="btn btn-info" id="bulkPublishBtn">
                    <i class='bx bx-upload'></i> Bulk Publish
                </button>
            </form>
            <form action="{{ route('college.course-results.bulk-approve') }}" method="POST" style="display:inline;" id="bulkApproveForm">
                @csrf
                <input type="hidden" name="academic_year_id" id="approve_year_id" value="{{ request('academic_year_id') }}">
                <input type="hidden" name="semester_id" id="approve_semester_id" value="{{ request('semester_id') }}">
                <button type="button" class="btn btn-success" id="bulkApproveBtn">
                    <i class='bx bx-check-shield'></i> Bulk Approve
                </button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <h3><i class='bx bx-filter-alt'></i> Filter Results</h3>
        <form action="{{ route('college.course-results.index') }}" method="GET">
            <div class="filter-grid">
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
                <div class="form-group">
                    <label>Program</label>
                    <select name="program_id" class="form-control">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Result Status</label>
                    <select name="result_status" class="form-control">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('result_status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('result_status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="approved" {{ request('result_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Pass/Fail</label>
                    <select name="course_status" class="form-control">
                        <option value="">All</option>
                        <option value="passed" {{ request('course_status') == 'passed' ? 'selected' : '' }}>Passed</option>
                        <option value="failed" {{ request('course_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-search'></i> Filter
                    </button>
                    <a href="{{ route('college.course-results.index') }}" class="btn btn-secondary">
                        <i class='bx bx-reset'></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Grading Information Banner -->
    <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #93c5fd; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <i class='bx bx-info-circle' style="font-size: 22px; color: #2563eb;"></i>
            <strong style="color: #1e40af;">Grading Formula:</strong>
        </div>
        <div style="display: flex; gap: 15px; flex-wrap: wrap; font-size: 13px; color: #1e40af;">
            <span style="background: #dbeafe; padding: 5px 12px; border-radius: 6px;"><strong>CA (40%)</strong> + <strong>Exam (60%)</strong> = <strong>Total (100%)</strong></span>
            <span style="background: #dcfce7; padding: 5px 12px; border-radius: 6px; color: #166534;"><i class='bx bx-check'></i> Pass: ≥40%</span>
            <span style="background: #fee2e2; padding: 5px 12px; border-radius: 6px; color: #991b1b;"><i class='bx bx-x'></i> Fail: &lt;40%</span>
        </div>
    </div>

    <!-- Data Table -->
    <div class="data-card">
        <div class="data-card-header">
            <h3><i class='bx bx-list-ul'></i> Course Result Records</h3>
        </div>

        @if($results->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Period</th>
                            <th title="Continuous Assessment (40%)">CA (40%)</th>
                            <th title="Final Examination (60%)">Exam (60%)</th>
                            <th title="Total = CA + Exam">Total (100%)</th>
                            <th>Grade</th>
                            <th>GPA</th>
                            <th>Status</th>
                            <th>Result</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $result)
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            {{ strtoupper(substr($result->student->first_name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div class="student-details">
                                            <h4>{{ $result->student->full_name ?? 'N/A' }}</h4>
                                            <span>{{ $result->student->student_number ?? '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $result->course->code ?? '' }}</strong>
                                    <br>
                                    <small>{{ Str::limit($result->course->name ?? '', 25) }}</small>
                                </td>
                                <td>
                                    <small>{{ $result->academicYear->name ?? '' }}</small>
                                    <br>
                                    <small>{{ $result->semester->name ?? '' }}</small>
                                </td>
                                <td>
                                    <span class="marks-breakdown ca" title="CA Score out of 40">{{ number_format($result->ca_total, 1) }}/40</span>
                                </td>
                                <td>
                                    <span class="marks-breakdown exam" title="Exam Score out of 60">{{ number_format($result->exam_total, 1) }}/60</span>
                                </td>
                                <td>
                                    <span class="marks-breakdown total" title="Total Score out of 100">{{ number_format($result->total_marks, 1) }}/100</span>
                                </td>
                                <td>
                                    @php
                                        $gradeClass = strtolower(substr($result->grade ?? 'F', 0, 1));
                                    @endphp
                                    <span class="grade-badge {{ $gradeClass }}">{{ $result->grade }}</span>
                                </td>
                                <td>
                                    <strong>{{ number_format($result->gpa_points, 2) }}</strong>
                                </td>
                                <td>
                                    @php
                                        $isPassed = in_array(strtolower($result->course_status), ['pass', 'passed']);
                                    @endphp
                                    <span class="status-badge {{ $isPassed ? 'pass' : 'fail' }}">
                                        <i class='bx bx-{{ $isPassed ? 'check' : 'x' }}'></i>
                                        {{ $isPassed ? 'Passed' : 'Failed' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $result->result_status }}">
                                        {{ ucfirst($result->result_status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="{{ route('college.course-results.show', $result) }}" class="action-btn view" title="View Details">
                                            <i class='bx bx-show'></i>
                                        </a>
                                        @if($result->result_status === 'draft')
                                            <form action="{{ route('college.course-results.publish', $result) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="action-btn publish" title="Publish">
                                                    <i class='bx bx-upload'></i>
                                                </button>
                                            </form>
                                        @elseif($result->result_status === 'published')
                                            <form action="{{ route('college.course-results.approve', $result) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="action-btn approve" title="Approve">
                                                    <i class='bx bx-check-shield'></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $results->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class='bx bx-folder-open'></i>
                <h3>No Course Results Found</h3>
                <p>No results match your current filter criteria. Generate results or adjust your filters.</p>
                <a href="{{ route('college.course-results.generate') }}" class="btn btn-primary" style="margin-top: 15px;">
                    <i class='bx bx-calculator'></i> Generate Results
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Bulk Action Modal -->
<div id="bulkActionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <h3 style="margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;" id="modalTitle">
            <i class='bx bx-info-circle' style="color: #3b82f6; font-size: 24px;"></i>
            <span>Select Period</span>
        </h3>
        <p style="color: #64748b; margin-bottom: 20px;" id="modalDescription">Please select the academic year and semester for bulk action.</p>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; font-weight: 500; margin-bottom: 5px;">Academic Year</label>
            <select id="modal_academic_year" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <option value="">Select Academic Year</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 500; margin-bottom: 5px;">Semester</label>
            <select id="modal_semester" style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
                <option value="">Select Semester</option>
                @foreach($semesters as $semester)
                    <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                @endforeach
            </select>
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button type="button" id="modalCancelBtn" style="padding: 10px 20px; border: 1px solid #e2e8f0; background: white; border-radius: 8px; cursor: pointer;">Cancel</button>
            <button type="button" id="modalConfirmBtn" style="padding: 10px 20px; border: none; background: #3b82f6; color: white; border-radius: 8px; cursor: pointer;">Confirm</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('bulkActionModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const modalConfirmBtn = document.getElementById('modalConfirmBtn');
    const modalCancelBtn = document.getElementById('modalCancelBtn');
    const modalAcademicYear = document.getElementById('modal_academic_year');
    const modalSemester = document.getElementById('modal_semester');
    
    let currentAction = null;

    // Bulk Publish Button
    document.getElementById('bulkPublishBtn').addEventListener('click', function() {
        currentAction = 'publish';
        modalTitle.innerHTML = '<i class="bx bx-upload" style="color: #0ea5e9; font-size: 24px;"></i><span>Bulk Publish Results</span>';
        modalDescription.textContent = 'This will publish all DRAFT results for the selected period. Students will be able to view their results.';
        modalConfirmBtn.style.background = '#0ea5e9';
        modalConfirmBtn.textContent = 'Publish All';
        modal.style.display = 'flex';
    });

    // Bulk Approve Button
    document.getElementById('bulkApproveBtn').addEventListener('click', function() {
        currentAction = 'approve';
        modalTitle.innerHTML = '<i class="bx bx-check-shield" style="color: #10b981; font-size: 24px;"></i><span>Bulk Approve Results</span>';
        modalDescription.textContent = 'This will approve all PUBLISHED results for the selected period. Approved results are final and official.';
        modalConfirmBtn.style.background = '#10b981';
        modalConfirmBtn.textContent = 'Approve All';
        modal.style.display = 'flex';
    });

    // Cancel Button
    modalCancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        currentAction = null;
    });

    // Click outside to close
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
            currentAction = null;
        }
    });

    // Confirm Button
    modalConfirmBtn.addEventListener('click', function() {
        const yearId = modalAcademicYear.value;
        const semesterId = modalSemester.value;

        if (!yearId || !semesterId) {
            alert('Please select both Academic Year and Semester');
            return;
        }

        if (currentAction === 'publish') {
            if (confirm('Are you sure you want to publish all draft results for this period?')) {
                document.getElementById('publish_year_id').value = yearId;
                document.getElementById('publish_semester_id').value = semesterId;
                document.getElementById('bulkPublishForm').submit();
            }
        } else if (currentAction === 'approve') {
            if (confirm('Are you sure you want to approve all published results for this period?')) {
                document.getElementById('approve_year_id').value = yearId;
                document.getElementById('approve_semester_id').value = semesterId;
                document.getElementById('bulkApproveForm').submit();
            }
        }
    });
});
</script>
@endpush
