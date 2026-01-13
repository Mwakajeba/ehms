@extends('layouts.main')

@push('styles')
<style>
    .score-form-container {
        margin-left: 250px;
        padding: 20px 30px;
        background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
        min-height: 100vh;
    }

    /* Header Section */
    .page-header-card {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
        border-radius: 20px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(245, 158, 11, 0.3);
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
        color: #f59e0b;
        border-color: #f59e0b;
    }

    .breadcrumb-btn.active {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-color: transparent;
    }

    .breadcrumb-separator {
        color: #cbd5e1;
        font-size: 18px;
    }

    /* Form Cards */
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .form-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .form-card-header i {
        font-size: 24px;
        color: #f59e0b;
    }

    .form-card-title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }

    .form-card-body {
        padding: 25px;
    }

    /* Info Display */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .info-item {
        padding: 15px;
        background: #f8fafc;
        border-radius: 10px;
        border-left: 4px solid #f59e0b;
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
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
    }

    /* Form Groups */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .form-label .required {
        color: #ef4444;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 15px;
        color: #1f2937;
        transition: all 0.2s ease;
        background-color: white;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }

    /* Current Score Display */
    .current-score-display {
        text-align: center;
        padding: 20px;
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 12px;
        margin-bottom: 20px;
    }

    .current-score-value {
        font-size: 36px;
        font-weight: 700;
        color: #92400e;
    }

    .current-score-label {
        font-size: 14px;
        color: #b45309;
        margin-top: 5px;
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
        justify-content: center;
        gap: 8px;
        padding: 14px 28px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-save {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        flex: 1;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #475569;
    }

    @media (max-width: 768px) {
        .score-form-container {
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
<div class="score-form-container">
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
            <i class="bx bx-edit"></i>
            Edit Score
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <h1 class="header-title">
                <i class="bx bx-edit"></i>
                Edit Final Exam Score
            </h1>
            <p class="header-subtitle">Update the score for {{ $finalExamScore->student->full_name ?? 'Student' }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="background: #dcfce7; border: 2px solid #86efac; color: #166534; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px;">
        <i class='bx bx-check-circle'></i>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error" style="background: #fee2e2; border: 2px solid #fca5a5; color: #991b1b; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px;">
        <i class='bx bx-error-circle'></i>
        <ul style="margin: 0; padding-left: 1rem;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('college.final-exam-scores.update', $finalExamScore->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Student & Exam Info -->
        <div class="form-card">
            <div class="form-card-header">
                <i class='bx bx-info-circle'></i>
                <h3 class="form-card-title">Student & Exam Information</h3>
            </div>
            <div class="form-card-body">
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
                        <label>Maximum Marks</label>
                        <span>{{ $finalExamScore->max_marks }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Score Entry -->
        <div class="form-card">
            <div class="form-card-header">
                <i class='bx bx-calculator'></i>
                <h3 class="form-card-title">Score Entry</h3>
            </div>
            <div class="form-card-body">
                <!-- Current Score Display -->
                <div class="current-score-display">
                    <div class="current-score-value">
                        {{ $finalExamScore->status === 'absent' ? 'ABSENT' : number_format($finalExamScore->score, 2) . '/' . number_format($finalExamScore->max_marks, 2) }}
                    </div>
                    <div class="current-score-label">Current Score</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status <span class="required">*</span></label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="marked" {{ old('status', $finalExamScore->status) == 'marked' ? 'selected' : '' }}>Marked</option>
                        <option value="absent" {{ old('status', $finalExamScore->status) == 'absent' ? 'selected' : '' }}>Absent</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="scoreGroup">
                    <label class="form-label">Score <span class="required">*</span></label>
                    <input type="number" 
                           name="score" 
                           id="score" 
                           class="form-control @error('score') is-invalid @enderror" 
                           value="{{ old('score', $finalExamScore->score) }}"
                           min="0" 
                           max="{{ $finalExamScore->max_marks }}"
                           step="0.01"
                           placeholder="Enter score">
                    <small style="color: #64748b; margin-top: 5px; display: block;">Maximum allowed: {{ $finalExamScore->max_marks }}</small>
                    @error('score')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Optional remarks">{{ old('remarks', $finalExamScore->remarks) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('college.final-exam-scores.index') }}" class="btn-action btn-cancel">
                <i class='bx bx-x'></i>
                Cancel
            </a>
            <button type="submit" class="btn-action btn-save">
                <i class='bx bx-save'></i>
                Update Score
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const scoreGroup = document.getElementById('scoreGroup');
    const scoreInput = document.getElementById('score');

    function toggleScoreField() {
        if (statusSelect.value === 'absent') {
            scoreGroup.style.display = 'none';
            scoreInput.removeAttribute('required');
        } else {
            scoreGroup.style.display = 'block';
            scoreInput.setAttribute('required', 'required');
        }
    }

    statusSelect.addEventListener('change', toggleScoreField);
    toggleScoreField(); // Initial state
});
</script>
@endsection
