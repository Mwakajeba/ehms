@extends('layouts.main')

@section('title', 'Assessment Score Details')

@push('styles')
<style>
    .score-details-container {
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
        background: #10b981;
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
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 10px 40px rgba(16, 185, 129, 0.3);
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

    .header-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .header-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .header-btn.back-btn {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .header-btn.back-btn:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .header-btn.edit-btn {
        background: white;
        color: #10b981;
    }

    .header-btn.edit-btn:hover {
        background: #f0fdf4;
        transform: translateY(-2px);
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

    .detail-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
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
        color: #10b981;
    }

    .card-body {
        padding: 25px;
    }

    .info-grid {
        display: grid;
        gap: 20px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .info-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
        padding: 12px 15px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }

    .info-value.highlight {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-color: #a7f3d0;
        color: #059669;
        font-weight: 600;
    }

    .score-display {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 16px;
        padding: 30px;
        text-align: center;
        color: white;
        margin-bottom: 20px;
    }

    .score-display .score-value {
        font-size: 48px;
        font-weight: 800;
        line-height: 1;
    }

    .score-display .score-max {
        font-size: 20px;
        opacity: 0.8;
        margin-top: 5px;
    }

    .score-display .score-label {
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 10px;
        opacity: 0.9;
    }

    .percentage-bar {
        background: #e2e8f0;
        border-radius: 10px;
        height: 12px;
        overflow: hidden;
        margin-top: 15px;
    }

    .percentage-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .percentage-fill.excellent { background: linear-gradient(90deg, #10b981, #059669); }
    .percentage-fill.good { background: linear-gradient(90deg, #3b82f6, #2563eb); }
    .percentage-fill.average { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .percentage-fill.poor { background: linear-gradient(90deg, #ef4444, #dc2626); }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-badge.draft {
        background: #fef3c7;
        color: #92400e;
    }

    .status-badge.marked {
        background: #dbeafe;
        color: #1e40af;
    }

    .status-badge.published {
        background: #d1fae5;
        color: #065f46;
    }

    .sidebar-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .sidebar-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

    .meta-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .meta-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .meta-list li:last-child {
        border-bottom: none;
    }

    .meta-list .label {
        font-size: 13px;
        color: #64748b;
    }

    .meta-list .value {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
    }

    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .action-btn.primary {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    }

    .action-btn.secondary {
        background: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }

    .action-btn.secondary:hover {
        background: #f8fafc;
        color: #1e293b;
    }

    .action-btn.danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .action-btn.danger:hover {
        background: #fecaca;
    }

    @media (max-width: 768px) {
        .score-details-container {
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
    }
</style>
@endpush

@section('content')
<div class="score-details-container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <a href="{{ route('college.assessment-scores.index') }}" class="breadcrumb-btn">
            <i class='bx bx-list-ul'></i> All Scores
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.assessment-scores.create') }}" class="breadcrumb-btn">
            <i class='bx bx-plus-circle'></i> Enter Score
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-show'></i> View Details
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1>
            <i class='bx bx-detail'></i>
            Assessment Score Details
        </h1>
        <p>Viewing score record for {{ $assessmentScore->student->first_name ?? 'Unknown' }} {{ $assessmentScore->student->last_name ?? '' }}</p>
        
        <div class="header-actions">
            <a href="{{ route('college.assessment-scores.index') }}" class="header-btn back-btn">
                <i class='bx bx-arrow-back'></i> Back to List
            </a>
            <a href="{{ route('college.assessment-scores.edit', $assessmentScore) }}" class="header-btn edit-btn">
                <i class='bx bx-edit'></i> Edit Score
            </a>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Main Content -->
        <div class="main-content">
            <!-- Score Card -->
            <div class="detail-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h3><i class='bx bx-trophy'></i> Score Information</h3>
                </div>
                <div class="card-body">
                    <div class="score-display">
                        <div class="score-value">{{ number_format($assessmentScore->score, 1) }}</div>
                        <div class="score-max">out of {{ number_format($assessmentScore->max_marks, 1) }}</div>
                        <div class="score-label">Score Achieved</div>
                        
                        @php
                            $percentage = $assessmentScore->max_marks > 0 
                                ? ($assessmentScore->score / $assessmentScore->max_marks) * 100 
                                : 0;
                            $percentClass = $percentage >= 70 ? 'excellent' : ($percentage >= 50 ? 'good' : ($percentage >= 40 ? 'average' : 'poor'));
                        @endphp
                        <div class="percentage-bar">
                            <div class="percentage-fill {{ $percentClass }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <div class="info-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="info-item">
                            <span class="info-label">Percentage</span>
                            <span class="info-value highlight">{{ number_format($percentage, 1) }}%</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Weighted Score</span>
                            <span class="info-value highlight">{{ number_format($assessmentScore->weighted_score, 2) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                <span class="status-badge {{ $assessmentScore->status }}">
                                    <i class='bx bx-{{ $assessmentScore->status == 'published' ? 'check-circle' : ($assessmentScore->status == 'marked' ? 'time' : 'pencil') }}'></i>
                                    {{ ucfirst($assessmentScore->status) }}
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Remarks</span>
                            <span class="info-value">{{ $assessmentScore->remarks ?? 'No remarks' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Student Information -->
            <div class="detail-card" style="margin-bottom: 25px;">
                <div class="card-header">
                    <h3><i class='bx bx-user'></i> Student Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="info-item">
                            <span class="info-label">Student Name</span>
                            <span class="info-value">{{ $assessmentScore->student->first_name ?? '' }} {{ $assessmentScore->student->last_name ?? '' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Student ID</span>
                            <span class="info-value">{{ $assessmentScore->student->student_number ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value">{{ $assessmentScore->student->email ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Program</span>
                            <span class="info-value">{{ $assessmentScore->student->program->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assessment Information -->
            <div class="detail-card">
                <div class="card-header">
                    <h3><i class='bx bx-book-open'></i> Assessment Information</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="info-item">
                            <span class="info-label">Assessment Type</span>
                            <span class="info-value">{{ $assessmentScore->courseAssessment->assessmentType->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Assessment Title</span>
                            <span class="info-value">{{ $assessmentScore->courseAssessment->title ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Course</span>
                            <span class="info-value">{{ $assessmentScore->courseAssessment->course->code ?? '' }} - {{ $assessmentScore->courseAssessment->course->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Max Marks</span>
                            <span class="info-value">{{ number_format($assessmentScore->courseAssessment->max_marks ?? 0, 1) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Weight Percentage</span>
                            <span class="info-value">{{ $assessmentScore->courseAssessment->weight_percentage ?? 0 }}%</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Assessment Date</span>
                            <span class="info-value">{{ $assessmentScore->courseAssessment->assessment_date ? \Carbon\Carbon::parse($assessmentScore->courseAssessment->assessment_date)->format('M d, Y') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Quick Stats -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h4><i class='bx bx-stats'></i> Quick Stats</h4>
                </div>
                <div class="sidebar-body">
                    <ul class="meta-list">
                        <li>
                            <span class="label">Score</span>
                            <span class="value">{{ number_format($assessmentScore->score, 1) }}/{{ number_format($assessmentScore->max_marks, 1) }}</span>
                        </li>
                        <li>
                            <span class="label">Percentage</span>
                            <span class="value">{{ number_format($percentage, 1) }}%</span>
                        </li>
                        <li>
                            <span class="label">Weighted</span>
                            <span class="value">{{ number_format($assessmentScore->weighted_score, 2) }}</span>
                        </li>
                        <li>
                            <span class="label">Status</span>
                            <span class="value">{{ ucfirst($assessmentScore->status) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Record Information -->
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <h4><i class='bx bx-info-circle'></i> Record Info</h4>
                </div>
                <div class="sidebar-body">
                    <ul class="meta-list">
                        <li>
                            <span class="label">Marked By</span>
                            <span class="value">{{ $assessmentScore->markedBy->name ?? 'N/A' }}</span>
                        </li>
                        <li>
                            <span class="label">Marked Date</span>
                            <span class="value">{{ $assessmentScore->marked_date ? \Carbon\Carbon::parse($assessmentScore->marked_date)->format('M d, Y') : 'N/A' }}</span>
                        </li>
                        @if($assessmentScore->status == 'published')
                        <li>
                            <span class="label">Published By</span>
                            <span class="value">{{ $assessmentScore->publishedBy->name ?? 'N/A' }}</span>
                        </li>
                        <li>
                            <span class="label">Published Date</span>
                            <span class="value">{{ $assessmentScore->published_date ? \Carbon\Carbon::parse($assessmentScore->published_date)->format('M d, Y') : 'N/A' }}</span>
                        </li>
                        @endif
                        <li>
                            <span class="label">Created</span>
                            <span class="value">{{ $assessmentScore->created_at->format('M d, Y') }}</span>
                        </li>
                        <li>
                            <span class="label">Updated</span>
                            <span class="value">{{ $assessmentScore->updated_at->format('M d, Y') }}</span>
                        </li>
                    </ul>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('college.assessment-scores.edit', $assessmentScore) }}" class="action-btn primary">
                        <i class='bx bx-edit'></i> Edit Score
                    </a>
                    <a href="{{ route('college.assessment-scores.index') }}" class="action-btn secondary">
                        <i class='bx bx-list-ul'></i> Back to List
                    </a>
                    <form action="{{ route('college.assessment-scores.destroy', $assessmentScore) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this score?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn danger" style="width: 100%;">
                            <i class='bx bx-trash'></i> Delete Score
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
