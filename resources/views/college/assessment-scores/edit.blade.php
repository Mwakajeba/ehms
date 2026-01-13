@extends('layouts.main')

@section('title', 'Edit Assessment Score')

@push('styles')
<style>
    .edit-score-container {
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
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 10px 40px rgba(245, 158, 11, 0.3);
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

    .form-card {
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
        color: #f59e0b;
    }

    .card-body {
        padding: 25px;
    }

    .form-group {
        margin-bottom: 20px;
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
        border-color: #f59e0b;
        background: white;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
    }

    .form-control:disabled, .form-control[readonly] {
        background: #f1f5f9;
        color: #64748b;
        cursor: not-allowed;
    }

    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .info-display {
        padding: 12px 15px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        color: #1e293b;
    }

    .info-display.highlight {
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        border-color: #fcd34d;
        color: #92400e;
        font-weight: 600;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        padding: 20px 25px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

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
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
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

    /* Sidebar Styles */
    .sidebar-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .sidebar-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
    }

    .current-score-display {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 12px;
        padding: 25px;
        text-align: center;
        color: white;
        margin-bottom: 15px;
    }

    .current-score-display .score-value {
        font-size: 36px;
        font-weight: 800;
        line-height: 1;
    }

    .current-score-display .score-max {
        font-size: 16px;
        opacity: 0.8;
        margin-top: 5px;
    }

    .current-score-display .score-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 8px;
        opacity: 0.9;
    }

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

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-danger {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #bfdbfe;
    }

    @media (max-width: 768px) {
        .edit-score-container {
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

        .form-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@section('content')
<div class="edit-score-container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-nav">
        <a href="{{ route('college.assessment-scores.index') }}" class="breadcrumb-btn">
            <i class='bx bx-list-ul'></i> All Scores
        </a>
        <span class="breadcrumb-separator">›</span>
        <a href="{{ route('college.assessment-scores.show', $assessmentScore) }}" class="breadcrumb-btn">
            <i class='bx bx-show'></i> View Details
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class='bx bx-edit'></i> Edit Score
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <h1>
            <i class='bx bx-edit'></i>
            Edit Assessment Score
        </h1>
        <p>Update score for {{ $assessmentScore->student->first_name ?? 'Unknown' }} {{ $assessmentScore->student->last_name ?? '' }} - {{ $assessmentScore->courseAssessment->title ?? 'Assessment' }}</p>
    </div>

    <!-- Error Messages -->
    @if ($errors->any())
    <div class="alert alert-danger">
        <i class='bx bx-error-circle'></i>
        <div>
            <strong>Please fix the following errors:</strong>
            <ul style="margin: 5px 0 0 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <form action="{{ route('college.assessment-scores.update', $assessmentScore) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="content-grid">
            <!-- Main Form -->
            <div class="main-content">
                <!-- Student & Assessment Info (Read-only) -->
                <div class="form-card" style="margin-bottom: 25px;">
                    <div class="card-header">
                        <h3><i class='bx bx-info-circle'></i> Assessment Information</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Student Name</label>
                                <div class="info-display">{{ $assessmentScore->student->first_name ?? '' }} {{ $assessmentScore->student->last_name ?? '' }}</div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Student ID</label>
                                <div class="info-display">{{ $assessmentScore->student->student_number ?? 'N/A' }}</div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Assessment Type</label>
                                <div class="info-display">{{ $assessmentScore->courseAssessment->assessmentType->name ?? 'N/A' }}</div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Assessment Title</label>
                                <div class="info-display">{{ $assessmentScore->courseAssessment->title ?? 'N/A' }}</div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Course</label>
                                <div class="info-display">{{ $assessmentScore->courseAssessment->course->code ?? '' }} - {{ $assessmentScore->courseAssessment->course->name ?? 'N/A' }}</div>
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label>Maximum Marks</label>
                                <div class="info-display highlight">{{ number_format($assessmentScore->max_marks, 1) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Score Edit Form -->
                <div class="form-card">
                    <div class="card-header">
                        <h3><i class='bx bx-edit-alt'></i> Update Score</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                            <div class="form-group">
                                <label>Score <span class="required">*</span></label>
                                <input type="number" 
                                       name="score" 
                                       class="form-control" 
                                       value="{{ old('score', $assessmentScore->score) }}"
                                       min="0" 
                                       max="{{ $assessmentScore->max_marks }}"
                                       step="0.1"
                                       required>
                                <small style="color: #64748b; font-size: 12px; margin-top: 5px; display: block;">
                                    Maximum allowed: {{ number_format($assessmentScore->max_marks, 1) }}
                                </small>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" {{ $assessmentScore->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="marked" {{ $assessmentScore->status == 'marked' ? 'selected' : '' }}>Marked</option>
                                    <option value="published" {{ $assessmentScore->status == 'published' ? 'selected' : '' }}>Published</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Remarks</label>
                            <textarea name="remarks" 
                                      class="form-control" 
                                      placeholder="Enter any remarks or comments about this score...">{{ old('remarks', $assessmentScore->remarks) }}</textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save'></i> Update Score
                        </button>
                        <a href="{{ route('college.assessment-scores.show', $assessmentScore) }}" class="btn btn-secondary">
                            <i class='bx bx-x'></i> Cancel
                        </a>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Current Score Display -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <h4><i class='bx bx-trophy'></i> Current Score</h4>
                    </div>
                    <div class="sidebar-body">
                        <div class="current-score-display">
                            <div class="score-value">{{ number_format($assessmentScore->score, 1) }}</div>
                            <div class="score-max">out of {{ number_format($assessmentScore->max_marks, 1) }}</div>
                            <div class="score-label">Current Score</div>
                        </div>
                        <ul class="info-list">
                            @php
                                $percentage = $assessmentScore->max_marks > 0 
                                    ? ($assessmentScore->score / $assessmentScore->max_marks) * 100 
                                    : 0;
                            @endphp
                            <li>
                                <span class="label">Percentage</span>
                                <span class="value">{{ number_format($percentage, 1) }}%</span>
                            </li>
                            <li>
                                <span class="label">Weighted Score</span>
                                <span class="value">{{ number_format($assessmentScore->weighted_score, 2) }}</span>
                            </li>
                            <li>
                                <span class="label">Status</span>
                                <span class="value">
                                    <span class="status-badge {{ $assessmentScore->status }}">
                                        {{ ucfirst($assessmentScore->status) }}
                                    </span>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Record Info -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <h4><i class='bx bx-time'></i> Record Info</h4>
                    </div>
                    <div class="sidebar-body">
                        <ul class="info-list">
                            <li>
                                <span class="label">Marked By</span>
                                <span class="value">{{ $assessmentScore->markedBy->name ?? 'N/A' }}</span>
                            </li>
                            <li>
                                <span class="label">Marked Date</span>
                                <span class="value">{{ $assessmentScore->marked_date ? \Carbon\Carbon::parse($assessmentScore->marked_date)->format('M d, Y') : 'N/A' }}</span>
                            </li>
                            <li>
                                <span class="label">Created</span>
                                <span class="value">{{ $assessmentScore->created_at->format('M d, Y') }}</span>
                            </li>
                            <li>
                                <span class="label">Last Updated</span>
                                <span class="value">{{ $assessmentScore->updated_at->format('M d, Y H:i') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="sidebar-card">
                    <div class="sidebar-header">
                        <h4><i class='bx bx-cog'></i> Quick Actions</h4>
                    </div>
                    <div class="sidebar-body" style="display: flex; flex-direction: column; gap: 10px;">
                        <a href="{{ route('college.assessment-scores.show', $assessmentScore) }}" class="btn btn-secondary" style="width: 100%;">
                            <i class='bx bx-show'></i> View Details
                        </a>
                        <a href="{{ route('college.assessment-scores.index') }}" class="btn btn-secondary" style="width: 100%;">
                            <i class='bx bx-list-ul'></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
