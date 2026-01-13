@extends('layouts.main')

@section('title', 'Final Exam Scores')

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
        background: #3b82f6;
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
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
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
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    @media (max-width: 1200px) {
        .stats-row { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .stats-row { grid-template-columns: 1fr; }
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid transparent;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .stat-card.blue { border-left-color: #3b82f6; }
    .stat-card.red { border-left-color: #ef4444; }
    .stat-card.green { border-left-color: #10b981; }
    .stat-card.purple { border-left-color: #8b5cf6; }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
    }

    .stat-icon.blue { background: #3b82f6; }
    .stat-icon.red { background: #ef4444; }
    .stat-icon.green { background: #10b981; }
    .stat-icon.purple { background: #8b5cf6; }

    .stat-content h4 {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        margin: 0 0 5px;
    }

    .stat-content p {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

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
        grid-template-columns: repeat(5, 1fr);
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
        border-color: #3b82f6;
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

    .btn-primary {
        background: #3b82f6;
        color: white;
    }

    .btn-primary:hover {
        background: #2563eb;
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
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
        padding: 14px 16px;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        border-bottom: 1px solid #e5e7eb;
    }

    .data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 14px;
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
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #3b82f6;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .student-details h4 {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }

    .student-details span {
        font-size: 12px;
        color: #64748b;
    }

    .score-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .score-badge.excellent { background: #dcfce7; color: #166534; }
    .score-badge.good { background: #dbeafe; color: #1e40af; }
    .score-badge.average { background: #fef3c7; color: #92400e; }
    .score-badge.poor { background: #fee2e2; color: #991b1b; }
    .score-badge.absent { background: #f3f4f6; color: #374151; }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.absent { background: #f3f4f6; color: #374151; }
    .status-badge.marked { background: #dbeafe; color: #1e40af; }
    .status-badge.published { background: #dcfce7; color: #166534; }

    .action-btns {
        display: flex;
        gap: 8px;
    }

    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
    }

    .action-btn.view { background: #dbeafe; color: #3b82f6; }
    .action-btn.view:hover { background: #3b82f6; color: white; }
    .action-btn.edit { background: #fef3c7; color: #f59e0b; }
    .action-btn.edit:hover { background: #f59e0b; color: white; }
    .action-btn.publish { background: #dcfce7; color: #10b981; }
    .action-btn.publish:hover { background: #10b981; color: white; }

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
            <i class='bx bx-file'></i> Exam Scores
        </span>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class='bx bx-file'></i> Final Exam Scores</h1>
        <p>Manage scores for final exams, supplementary exams, special exams, and makeup exams</p>
    </div>

    <!-- Statistics Row -->
    <div class="stats-row">
        <div class="stat-card blue">
            <div class="stat-icon blue"><i class='bx bx-file'></i></div>
            <div class="stat-content">
                <h4>Total Records</h4>
                <p>{{ number_format($stats['total']) }}</p>
            </div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon red"><i class='bx bx-user-x'></i></div>
            <div class="stat-content">
                <h4>Absent</h4>
                <p>{{ number_format($stats['absent']) }}</p>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon green"><i class='bx bx-check'></i></div>
            <div class="stat-content">
                <h4>Marked</h4>
                <p>{{ number_format($stats['marked']) }}</p>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon purple"><i class='bx bx-show'></i></div>
            <div class="stat-content">
                <h4>Published</h4>
                <p>{{ number_format($stats['published']) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-card">
        <h3><i class='bx bx-filter-alt'></i> Filter Results</h3>
        <form action="{{ route('college.final-exam-scores.index') }}" method="GET">
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
                    <label>Course</label>
                    <select name="course_id" class="form-control">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        <option value="marked" {{ request('status') == 'marked' ? 'selected' : '' }}>Marked</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
                <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class='bx bx-search'></i> Filter
                    </button>
                    <a href="{{ route('college.final-exam-scores.index') }}" class="btn btn-secondary">
                        <i class='bx bx-reset'></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="data-card">
        <div class="data-card-header">
            <h3><i class='bx bx-list-ul'></i> Final Exam Score Records</h3>
            <a href="{{ route('college.final-exam-scores.create') }}" class="btn btn-primary">
                <i class='bx bx-plus'></i> Enter Scores
            </a>
        </div>

        @if($scores->count() > 0)
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Exam</th>
                            <th>Course</th>
                            <th>Score</th>
                            <th>Weighted</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scores as $score)
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            {{ strtoupper(substr($score->student->first_name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div class="student-details">
                                            <h4>{{ $score->student->full_name ?? 'N/A' }}</h4>
                                            <span>{{ $score->student->student_number ?? '' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $score->examSchedule->exam_name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $score->examSchedule->exam_date ? \Carbon\Carbon::parse($score->examSchedule->exam_date)->format('M d, Y') : '' }}</small>
                                </td>
                                <td>{{ $score->examSchedule->course->code ?? '' }} - {{ Str::limit($score->examSchedule->course->name ?? '', 30) }}</td>
                                <td>
                                    @if($score->status === 'absent')
                                        <span class="score-badge absent">Absent</span>
                                    @else
                                        @php
                                            $maxMarks = $score->max_marks ?? $score->examSchedule->total_marks ?? 100;
                                            $percentage = $maxMarks > 0 ? ($score->score / $maxMarks) * 100 : 0;
                                            $scoreClass = $percentage >= 80 ? 'excellent' : ($percentage >= 60 ? 'good' : ($percentage >= 40 ? 'average' : 'poor'));
                                        @endphp
                                        <span class="score-badge {{ $scoreClass }}">
                                            {{ number_format($score->score, 2) }}/{{ number_format($maxMarks, 2) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $maxMarks = $score->max_marks ?? $score->examSchedule->total_marks ?? 100;
                                        $weightedPercentage = $maxMarks > 0 ? ($score->score / $maxMarks) * 100 : 0;
                                    @endphp
                                    {{ number_format($weightedPercentage, 2) }}%
                                </td>
                                <td>
                                    <span class="status-badge {{ $score->status }}">
                                        <i class='bx bx-{{ $score->status == 'published' ? 'check-circle' : ($score->status == 'marked' ? 'check' : 'user-x') }}'></i>
                                        {{ ucfirst($score->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="{{ route('college.final-exam-scores.show', $score) }}" class="action-btn view" title="View">
                                            <i class='bx bx-show'></i>
                                        </a>
                                        @if($score->status !== 'published')
                                            <a href="{{ route('college.final-exam-scores.edit', $score) }}" class="action-btn edit" title="Edit">
                                                <i class='bx bx-edit'></i>
                                            </a>
                                            <form action="{{ route('college.final-exam-scores.publish', $score) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="action-btn publish" title="Publish">
                                                    <i class='bx bx-upload'></i>
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
                {{ $scores->links() }}
            </div>
        @else
            <div class="empty-state">
                <i class='bx bx-folder-open'></i>
                <h3>No Exam Scores Found</h3>
                <p>No scores match your current filter criteria. Try adjusting your filters or add new scores.</p>
                <a href="{{ route('college.final-exam-scores.create') }}" class="btn btn-primary" style="margin-top: 15px;">
                    <i class='bx bx-plus'></i> Enter Scores
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
