@extends('layouts.main')

@push('styles')
<style>
    .score-show-container {
        margin-left: 250px;
        padding: 20px 30px;
        background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
        min-height: 100vh;
    }

    /* Header Section */
    .page-header-card {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1e40af 100%);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(59, 130, 246, 0.3);
        position: relative;
        overflow: hidden;
    }

    .page-header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .header-content {
        position: relative;
        z-index: 1;
    }

    .header-title {
        font-size: 28px;
        font-weight: 700;
        color: white;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-title i {
        font-size: 32px;
        background: rgba(255, 255, 255, 0.2);
        padding: 10px;
        border-radius: 12px;
    }

    .header-subtitle {
        color: rgba(255, 255, 255, 0.9);
        font-size: 15px;
        margin: 0;
    }

    /* Breadcrumb Navigation */
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 15px 0;
        margin-top: 70px;
        margin-bottom: 15px;
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
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .breadcrumb-btn:hover {
        background: #f8fafc;
        color: #3b82f6;
        border-color: #3b82f6;
    }

    .breadcrumb-btn.active {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border-color: transparent;
    }

    .breadcrumb-separator {
        color: #cbd5e1;
        font-size: 18px;
    }

    /* Info Cards */
    .info-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .info-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-card-header i {
        font-size: 24px;
        color: #3b82f6;
    }

    .info-card-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .info-card-body {
        padding: 25px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .info-item {
        padding: 15px;
        background: #f8fafc;
        border-radius: 10px;
        border-left: 4px solid #3b82f6;
    }

    .info-item label {
        display: block;
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .info-item span {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
    }

    /* Score Display */
    .score-display {
        text-align: center;
        padding: 30px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border-radius: 16px;
        margin: 20px 0;
    }

    .score-value {
        font-size: 48px;
        font-weight: 700;
        color: #166534;
    }

    .score-value .separator {
        color: #86efac;
        margin: 0 10px;
    }

    .score-percentage {
        font-size: 24px;
        color: #22c55e;
        margin-top: 10px;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
    }

    .status-badge.marked {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1d4ed8;
    }

    .status-badge.published {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
    }

    .status-badge.absent {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .btn-back {
        background: #f1f5f9;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .btn-back:hover {
        background: #e2e8f0;
        color: #475569;
    }

    .btn-publish {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: white;
    }

    .btn-publish:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
        color: white;
    }

    @media (max-width: 768px) {
        .score-show-container {
            margin-left: 0;
            padding: 15px;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="score-show-container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <a href="{{ route('dashboard') }}" class="breadcrumb-btn">
            <i class="bx bx-home"></i>
            Dashboard
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.index') }}" class="breadcrumb-btn">
            <i class="bx bx-building"></i>
            College
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.exams-management.dashboard') }}" class="breadcrumb-btn">
            <i class="bx bx-calendar-check"></i>
            Exams & Academics
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.final-exam-scores.index') }}" class="breadcrumb-btn">
            <i class="bx bx-file"></i>
            Final Exam Scores
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class="bx bx-show"></i>
            View Score
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <h1 class="header-title">
                <i class="bx bx-file"></i>
                Final Exam Score Details
            </h1>
            <p class="header-subtitle">View detailed information about this exam score</p>
        </div>
    </div>

    <!-- Student Information -->
    <div class="info-card">
        <div class="info-card-header">
            <i class='bx bx-user'></i>
            <h3>Student Information</h3>
        </div>
        <div class="info-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Student Number</label>
                    <span>{{ $finalExamScore->student->student_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Student Name</label>
                    <span>{{ $finalExamScore->student->full_name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Program</label>
                    <span>{{ $finalExamScore->student->program->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Information -->
    <div class="info-card">
        <div class="info-card-header">
            <i class='bx bx-calendar'></i>
            <h3>Exam Information</h3>
        </div>
        <div class="info-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Exam Name</label>
                    <span>{{ $finalExamScore->examSchedule->exam_name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Course</label>
                    <span>{{ $finalExamScore->examSchedule->course->code ?? '' }} - {{ $finalExamScore->examSchedule->course->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Exam Date</label>
                    <span>{{ $finalExamScore->examSchedule->exam_date ? $finalExamScore->examSchedule->exam_date->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Academic Year</label>
                    <span>{{ $finalExamScore->examSchedule->academicYear->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Semester</label>
                    <span>{{ $finalExamScore->examSchedule->semester->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Information -->
    <div class="info-card">
        <div class="info-card-header">
            <i class='bx bx-calculator'></i>
            <h3>Score Information</h3>
        </div>
        <div class="info-card-body">
            @if($finalExamScore->status === 'absent')
                <div class="score-display" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);">
                    <div class="score-value" style="color: #991b1b;">ABSENT</div>
                </div>
            @else
                <div class="score-display">
                    @php
                        $maxMarks = $finalExamScore->max_marks ?? $finalExamScore->examSchedule->total_marks ?? 100;
                        $percentage = $maxMarks > 0 ? ($finalExamScore->score / $maxMarks) * 100 : 0;
                    @endphp
                    <div class="score-value">
                        {{ number_format($finalExamScore->score, 2) }}
                        <span class="separator">/</span>
                        {{ number_format($maxMarks, 2) }}
                    </div>
                    <div class="score-percentage">{{ number_format($percentage, 1) }}%</div>
                </div>
            @endif

            <div class="info-grid" style="margin-top: 20px;">
                <div class="info-item">
                    <label>Status</label>
                    <span>
                        @if($finalExamScore->status === 'marked')
                            <span class="status-badge marked"><i class='bx bx-check'></i> Marked</span>
                        @elseif($finalExamScore->status === 'published')
                            <span class="status-badge published"><i class='bx bx-globe'></i> Published</span>
                        @else
                            <span class="status-badge absent"><i class='bx bx-x'></i> Absent</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <label>Marked By</label>
                    <span>{{ $finalExamScore->markedBy->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Marked Date</label>
                    <span>{{ $finalExamScore->marked_date ? $finalExamScore->marked_date->format('M d, Y') : 'N/A' }}</span>
                </div>
                @if($finalExamScore->remarks)
                <div class="info-item" style="grid-column: 1 / -1;">
                    <label>Remarks</label>
                    <span>{{ $finalExamScore->remarks }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="{{ route('college.final-exam-scores.index') }}" class="btn-action btn-back">
            <i class='bx bx-arrow-back'></i>
            Back to List
        </a>
        <a href="{{ route('college.final-exam-scores.edit', $finalExamScore->id) }}" class="btn-action btn-edit">
            <i class='bx bx-edit'></i>
            Edit Score
        </a>
        @if($finalExamScore->status === 'marked')
        <form action="{{ route('college.final-exam-scores.publish', $finalExamScore->id) }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="btn-action btn-publish">
                <i class='bx bx-globe'></i>
                Publish to Student
            </button>
        </form>
        @endif
    </div>
</div>
@endsection
