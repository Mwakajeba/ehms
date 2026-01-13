@extends('layouts.main')

@section('title', 'Course Result Details')

@section('content')
<style>
    .result-detail-page {
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
        background: #8b5cf6;
        border-color: transparent;
        color: white;
    }

    .breadcrumb-btn.active {
        background: #8b5cf6;
        border-color: transparent;
        color: white;
        font-weight: 600;
    }

    .breadcrumb-separator {
        color: #8b5cf6;
        font-size: 20px;
        font-weight: bold;
    }

    .page-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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

    .header-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-white {
        background: white;
        color: #8b5cf6;
    }

    .btn-white:hover {
        background: #f1f5f9;
    }

    .btn-success {
        background: #10b981;
        color: white;
    }

    .btn-success:hover {
        background: #059669;
    }

    .btn-warning {
        background: #f59e0b;
        color: white;
    }

    .btn-warning:hover {
        background: #d97706;
    }

    .content-grid {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 25px;
    }

    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .card-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }

    .card-header h3 i {
        color: #8b5cf6;
        font-size: 20px;
    }

    .card-body {
        padding: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .info-label {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
    }

    .student-profile {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .student-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        font-weight: 700;
    }

    .student-info h4 {
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 5px 0;
    }

    .student-info span {
        font-size: 14px;
        color: #64748b;
    }

    /* Grade Display */
    .grade-display {
        text-align: center;
        padding: 25px;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .grade-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 36px;
        font-weight: 700;
        color: white;
    }

    .grade-a { background: linear-gradient(135deg, #10b981, #059669); }
    .grade-b { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .grade-c { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .grade-d { background: linear-gradient(135deg, #f97316, #ea580c); }
    .grade-f { background: linear-gradient(135deg, #ef4444, #dc2626); }

    .grade-label {
        font-size: 14px;
        color: #64748b;
        margin-bottom: 5px;
    }

    .grade-value {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }

    .gpa-value {
        font-size: 16px;
        color: #64748b;
        margin-top: 5px;
    }

    /* Scores Section */
    .scores-breakdown {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .scores-breakdown {
            grid-template-columns: 1fr;
        }
    }

    .score-box {
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        background: #f8fafc;
    }

    .score-box.ca {
        background: linear-gradient(135deg, #dbeafe, #eff6ff);
        border: 1px solid #93c5fd;
    }

    .score-box.exam {
        background: linear-gradient(135deg, #fef3c7, #fffbeb);
        border: 1px solid #fcd34d;
    }

    .score-box.total {
        background: linear-gradient(135deg, #d1fae5, #ecfdf5);
        border: 1px solid #6ee7b7;
    }

    .score-label {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
        text-transform: uppercase;
        margin-bottom: 8px;
    }

    .score-value {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
    }

    /* Assessment Table */
    .assessment-table {
        width: 100%;
        border-collapse: collapse;
    }

    .assessment-table th,
    .assessment-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
    }

    .assessment-table th {
        background: #f8fafc;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
    }

    .assessment-table td {
        font-size: 14px;
        color: #1e293b;
    }

    /* Status Badges */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-draft {
        background: #fef3c7;
        color: #92400e;
    }

    .status-published {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }

    .status-passed {
        background: #d1fae5;
        color: #065f46;
    }

    .status-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Sidebar */
    .sidebar-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }

    .sidebar-header {
        padding: 15px 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .sidebar-header h4 {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .sidebar-header h4 i {
        color: #8b5cf6;
    }

    .sidebar-body {
        padding: 15px 20px;
    }

    .sidebar-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .sidebar-item:last-child {
        border-bottom: none;
    }

    .sidebar-label {
        font-size: 13px;
        color: #64748b;
    }

    .sidebar-value {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
    }

    .action-btn-primary {
        background: #8b5cf6;
        color: white;
    }

    .action-btn-primary:hover {
        background: #7c3aed;
    }

    .action-btn-success {
        background: #10b981;
        color: white;
    }

    .action-btn-success:hover {
        background: #059669;
    }

    .action-btn-warning {
        background: #f59e0b;
        color: white;
    }

    .action-btn-warning:hover {
        background: #d97706;
    }

    .action-btn-outline {
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .action-btn-outline:hover {
        background: #f8fafc;
        border-color: #8b5cf6;
        color: #8b5cf6;
    }

    .empty-state {
        text-align: center;
        padding: 30px;
        color: #64748b;
    }

    .empty-state i {
        font-size: 40px;
        margin-bottom: 10px;
        opacity: 0.5;
    }
</style>

<div class="result-detail-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb-nav">
        <a href="{{ route('college.index') }}" class="breadcrumb-btn">
            <i class='bx bx-home'></i> College
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.course-results.index') }}" class="breadcrumb-btn">
            <i class='bx bx-list-ul'></i> Course Results
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-show'></i> Result Details
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1>
            <i class='bx bx-file'></i>
            Course Result Details
        </h1>
        <p>Viewing detailed result information for {{ $courseResult->student->full_name ?? 'Student' }}</p>
        <div class="header-actions">
            <a href="{{ route('college.course-results.index') }}" class="btn btn-white">
                <i class='bx bx-arrow-back'></i> Back to List
            </a>
            @if($courseResult->result_status === 'draft')
                <form action="{{ route('college.course-results.publish', $courseResult) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class='bx bx-check-circle'></i> Publish Result
                    </button>
                </form>
            @elseif($courseResult->result_status === 'published')
                <form action="{{ route('college.course-results.approve', $courseResult) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class='bx bx-badge-check'></i> Approve Result
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="content-grid">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Student Info Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class='bx bx-user'></i> Student Information</h3>
                    <span class="status-badge status-{{ strtolower($courseResult->result_status) }}">
                        {{ ucfirst($courseResult->result_status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="student-profile">
                        <div class="student-avatar">
                            {{ strtoupper(substr($courseResult->student->first_name ?? 'S', 0, 1)) }}{{ strtoupper(substr($courseResult->student->last_name ?? 'T', 0, 1)) }}
                        </div>
                        <div class="student-info">
                            <h4>{{ $courseResult->student->full_name ?? 'N/A' }}</h4>
                            <span>{{ $courseResult->student->student_number ?? 'N/A' }} • {{ $courseResult->program->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Course</span>
                            <span class="info-value">{{ $courseResult->course->code ?? '' }} - {{ $courseResult->course->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Credit Hours</span>
                            <span class="info-value">{{ $courseResult->credit_hours ?? 0 }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Academic Year</span>
                            <span class="info-value">{{ $courseResult->academicYear->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Semester</span>
                            <span class="info-value">{{ $courseResult->semester->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Card -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class='bx bx-bar-chart-alt-2'></i> Results Summary</h3>
                </div>
                <div class="card-body">
                    <!-- Grade Display -->
                    <div class="grade-display">
                        @php
                            $gradeClass = 'grade-f';
                            if (str_starts_with($courseResult->grade ?? '', 'A')) $gradeClass = 'grade-a';
                            elseif (str_starts_with($courseResult->grade ?? '', 'B')) $gradeClass = 'grade-b';
                            elseif (str_starts_with($courseResult->grade ?? '', 'C')) $gradeClass = 'grade-c';
                            elseif (str_starts_with($courseResult->grade ?? '', 'D')) $gradeClass = 'grade-d';
                        @endphp
                        <div class="grade-circle {{ $gradeClass }}">
                            {{ $courseResult->grade ?? 'N/A' }}
                        </div>
                        <div class="grade-label">Final Grade</div>
                        <div class="grade-value">{{ $courseResult->remark ?? 'N/A' }}</div>
                        <div class="gpa-value">GPA Points: {{ number_format($courseResult->gpa_points ?? 0, 2) }}</div>
                    </div>

                    <!-- Scores Breakdown -->
                    <div class="scores-breakdown">
                        <div class="score-box ca">
                            <div class="score-label">CA Total (40%)</div>
                            <div class="score-value">{{ number_format($courseResult->ca_total ?? 0, 1) }}<small>/40</small></div>
                        </div>
                        <div class="score-box exam">
                            <div class="score-label">Exam Score (60%)</div>
                            <div class="score-value">{{ number_format($courseResult->exam_total ?? 0, 1) }}<small>/60</small></div>
                        </div>
                        <div class="score-box total">
                            <div class="score-label">Total Marks</div>
                            <div class="score-value">{{ number_format($courseResult->total_marks ?? 0, 1) }}<small>/100</small></div>
                        </div>
                    </div>

                    <!-- Weight Information -->
                    <div style="text-align: center; padding: 15px; background: #f0f9ff; border-radius: 8px; margin: 15px 0;">
                        <div style="font-size: 13px; color: #0369a1;">
                            <strong>Grading Formula:</strong> Total = CA (40%) + Final Exam (60%) | Pass Mark: 40%
                        </div>
                    </div>

                    <!-- Pass/Fail Status -->
                    <div style="text-align: center;">
                        @if(in_array(strtolower($courseResult->course_status ?? ''), ['pass', 'passed']))
                            <span class="status-badge status-passed" style="font-size: 16px; padding: 10px 25px;">
                                <i class='bx bx-check-circle'></i> PASSED
                            </span>
                        @else
                            <span class="status-badge status-failed" style="font-size: 16px; padding: 10px 25px;">
                                <i class='bx bx-x-circle'></i> FAILED
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Assessment Scores -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class='bx bx-list-check'></i> Assessment Scores (CA)</h3>
                </div>
                <div class="card-body">
                    @if($assessmentScores && $assessmentScores->count() > 0)
                        <table class="assessment-table">
                            <thead>
                                <tr>
                                    <th>Assessment</th>
                                    <th>Score</th>
                                    <th>Max</th>
                                    <th>Weighted</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assessmentScores as $score)
                                <tr>
                                    <td>{{ $score->courseAssessment->title ?? 'Assessment' }}</td>
                                    <td>{{ number_format($score->score ?? 0, 1) }}</td>
                                    <td>{{ number_format($score->courseAssessment->max_marks ?? 100, 0) }}</td>
                                    <td>{{ number_format($score->weighted_score ?? 0, 1) }}</td>
                                    <td>
                                        <span class="status-badge status-{{ strtolower($score->status ?? 'draft') }}">
                                            {{ ucfirst($score->status ?? 'Draft') }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">
                            <i class='bx bx-file'></i>
                            <p>No assessment scores recorded</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Final Exam Score -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class='bx bx-edit'></i> Final Exam Score</h3>
                </div>
                <div class="card-body">
                    @if($examScore)
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Exam</span>
                                <span class="info-value">{{ $examScore->finalExam->title ?? 'Final Exam' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Score</span>
                                <span class="info-value">{{ number_format($examScore->score ?? 0, 1) }} / {{ number_format($examScore->finalExam->max_marks ?? 100, 0) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Weighted Score</span>
                                <span class="info-value">{{ number_format($examScore->weighted_score ?? 0, 1) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Status</span>
                                <span class="status-badge status-{{ strtolower($examScore->status ?? 'draft') }}">
                                    {{ ucfirst($examScore->status ?? 'Draft') }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class='bx bx-edit'></i>
                            <p>No final exam score recorded</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Actions -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h4><i class='bx bx-cog'></i> Actions</h4>
                </div>
                <div class="sidebar-body">
                    <div class="action-buttons">
                        <a href="{{ route('college.course-results.index') }}" class="action-btn action-btn-outline">
                            <i class='bx bx-arrow-back'></i> Back to List
                        </a>
                        @if($courseResult->result_status === 'draft')
                            <form action="{{ route('college.course-results.publish', $courseResult) }}" method="POST">
                                @csrf
                                <button type="submit" class="action-btn action-btn-success">
                                    <i class='bx bx-check-circle'></i> Publish Result
                                </button>
                            </form>
                        @elseif($courseResult->result_status === 'published')
                            <form action="{{ route('college.course-results.approve', $courseResult) }}" method="POST">
                                @csrf
                                <button type="submit" class="action-btn action-btn-warning">
                                    <i class='bx bx-badge-check'></i> Approve Result
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('college.course-results.generate') }}" class="action-btn action-btn-primary">
                            <i class='bx bx-refresh'></i> Generate Results
                        </a>
                    </div>
                </div>
            </div>

            <!-- Result Info -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h4><i class='bx bx-info-circle'></i> Result Information</h4>
                </div>
                <div class="sidebar-body">
                    <div class="sidebar-item">
                        <span class="sidebar-label">Result ID</span>
                        <span class="sidebar-value">#{{ $courseResult->id }}</span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label">Attempt</span>
                        <span class="sidebar-value">{{ $courseResult->attempt_number ?? 1 }}</span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label">Is Retake</span>
                        <span class="sidebar-value">{{ $courseResult->is_retake ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label">Status</span>
                        <span class="sidebar-value">
                            <span class="status-badge status-{{ strtolower($courseResult->result_status) }}">
                                {{ ucfirst($courseResult->result_status) }}
                            </span>
                        </span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label">Created</span>
                        <span class="sidebar-value">{{ $courseResult->created_at->format('M d, Y') }}</span>
                    </div>
                    @if($courseResult->published_date)
                    <div class="sidebar-item">
                        <span class="sidebar-label">Published</span>
                        <span class="sidebar-value">{{ \Carbon\Carbon::parse($courseResult->published_date)->format('M d, Y') }}</span>
                    </div>
                    @endif
                    @if($courseResult->approved_date)
                    <div class="sidebar-item">
                        <span class="sidebar-label">Approved</span>
                        <span class="sidebar-value">{{ \Carbon\Carbon::parse($courseResult->approved_date)->format('M d, Y') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quality Points -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h4><i class='bx bx-calculator'></i> Quality Points</h4>
                </div>
                <div class="sidebar-body">
                    @php
                        $qualityPoints = ($courseResult->gpa_points ?? 0) * ($courseResult->credit_hours ?? 0);
                    @endphp
                    <div class="sidebar-item">
                        <span class="sidebar-label">Credit Hours</span>
                        <span class="sidebar-value">{{ $courseResult->credit_hours ?? 0 }}</span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label">GPA Points</span>
                        <span class="sidebar-value">{{ number_format($courseResult->gpa_points ?? 0, 2) }}</span>
                    </div>
                    <div class="sidebar-item">
                        <span class="sidebar-label">Quality Points</span>
                        <span class="sidebar-value" style="color: #8b5cf6; font-size: 16px;">{{ number_format($qualityPoints, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
