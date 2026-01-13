@extends('layouts.main')

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .score-form-container {
        margin-left: 250px;
        padding: 20px 30px;
        background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
        min-height: 100vh;
    }

    /* Select2 Custom Styling */
    .select2-container {
        width: 100% !important;
    }

    .select2-container--default .select2-selection--single {
        height: 48px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        padding: 8px 12px;
        transition: all 0.3s ease;
    }

    .select2-container--default .select2-selection--single:hover {
        border-color: #cbd5e1;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6;
        background: white;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #1e293b;
        line-height: 28px;
        padding-left: 4px;
        font-size: 14px;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #94a3b8;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px;
        right: 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #64748b transparent transparent transparent;
        border-width: 6px 5px 0 5px;
    }

    .select2-dropdown {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        margin-top: 4px;
        overflow: hidden;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        margin: 8px;
        width: calc(100% - 16px);
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #3b82f6;
        outline: none;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .select2-container--default .select2-results__option {
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background: #eff6ff;
        color: #1e40af;
        font-weight: 600;
    }

    .select2-results__option--selectable {
        cursor: pointer;
    }

    .select2-container--default .select2-results > .select2-results__options {
        max-height: 300px;
    }

    /* Select2 with icon */
    .select2-selection__rendered .select-icon {
        margin-right: 8px;
        color: #3b82f6;
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

    .page-header-card::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 10%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.05);
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
        border-color: #cbd5e1;
        color: #475569;
    }

    .breadcrumb-btn i {
        font-size: 16px;
    }

    .breadcrumb-btn.active {
        background: white;
        border-color: #3b82f6;
        color: #1e40af;
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(59, 130, 246, 0.15);
    }

    .breadcrumb-separator {
        color: #cbd5e1;
        font-size: 18px;
    }

    /* Form Layout */
    .form-layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 25px;
    }

    @media (max-width: 1200px) {
        .form-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Form Card */
    .form-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .form-card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 20px 25px;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-card-title {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-card-title i {
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 18px;
    }

    .form-card-body {
        padding: 25px;
    }

    /* Form Groups */
    .form-group {
        margin-bottom: 20px;
    }

    .form-group:last-child {
        margin-bottom: 0;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 8px;
    }

    .form-label .required {
        color: #ef4444;
        margin-left: 2px;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #3b82f6;
        background: white;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }

    .form-control::placeholder {
        color: #94a3b8;
    }

    .form-hint {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    /* Info Cards */
    .info-card {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #93c5fd;
        border-radius: 12px;
        padding: 16px;
        margin-top: 20px;
        display: none;
    }

    .info-card.show {
        display: block;
    }

    .info-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }

    .info-card-header i {
        width: 32px;
        height: 32px;
        background: #3b82f6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }

    .info-card-header h4 {
        font-size: 14px;
        font-weight: 600;
        margin: 0;
        color: #1e40af;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
    }

    .info-item {
        background: white;
        padding: 10px 12px;
        border-radius: 8px;
    }

    .info-item label {
        font-size: 11px;
        color: #6b7280;
        display: block;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-item span {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
    }

    /* Score Section */
    .score-section {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
    }

    .score-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .score-section-title i {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }

    .score-display-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 2px solid #e2e8f0;
    }

    .score-display-value {
        display: flex;
        align-items: baseline;
        justify-content: center;
        gap: 8px;
    }

    .score-display-value .current {
        font-size: 48px;
        font-weight: 700;
        color: #3b82f6;
    }

    .score-display-value .separator {
        font-size: 32px;
        color: #94a3b8;
    }

    .score-display-value .max {
        font-size: 24px;
        font-weight: 600;
        color: #64748b;
    }

    .score-label {
        font-size: 12px;
        color: #64748b;
        margin-top: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Sidebar */
    .sidebar-section {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Quick Info Card */
    .quick-info-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .quick-info-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .quick-info-title i {
        color: #3b82f6;
    }

    .info-item-quick {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f8fafc;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .info-item-quick:last-child {
        margin-bottom: 0;
    }

    .info-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: white;
    }

    .info-content {
        flex: 1;
    }

    .info-label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 14px;
        color: #1e293b;
        font-weight: 600;
    }

    /* Publish Card */
    .publish-card {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 16px;
        padding: 20px;
        border: 2px solid #fbbf24;
    }

    .publish-title {
        font-size: 14px;
        font-weight: 700;
        color: #92400e;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .publish-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px;
        background: white;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .publish-option:last-child {
        margin-bottom: 0;
    }

    .publish-label {
        font-size: 13px;
        color: #1e293b;
        font-weight: 500;
    }

    .publish-hint {
        font-size: 11px;
        color: #64748b;
        margin-top: 4px;
    }

    .toggle-switch {
        position: relative;
        width: 48px;
        height: 26px;
        background: #e2e8f0;
        border-radius: 13px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .toggle-switch.active {
        background: #3b82f6;
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .toggle-switch.active::after {
        left: 25px;
    }

    .toggle-switch input {
        display: none;
    }

    /* Action Card */
    .action-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .btn-submit {
        width: 100%;
        padding: 14px 20px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(59, 130, 246, 0.4);
    }

    .btn-cancel {
        width: 100%;
        padding: 12px 20px;
        background: #f1f5f9;
        color: #64748b;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #475569;
    }

    /* Alert Styles */
    .alert {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .alert-success {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 2px solid #86efac;
        color: #166534;
    }

    .alert-error {
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border: 2px solid #fca5a5;
        color: #991b1b;
    }

    .alert i {
        font-size: 20px;
    }

    textarea.form-control {
        min-height: 80px;
        resize: vertical;
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
            <i class="bx bx-plus-circle"></i>
            Enter Score
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <h1 class="header-title">
                <i class="bx bx-edit"></i>
                Enter Final Exam Score
            </h1>
            <p class="header-subtitle">Record individual student final exam result</p>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class='bx bx-check-circle'></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error">
        <i class='bx bx-error-circle'></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 2px solid #fcd34d; color: #92400e;">
        <i class='bx bx-error' style="color: #f59e0b;"></i>
        <span>{{ session('warning') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <i class='bx bx-error-circle'></i>
        <div>
            <ul style="margin: 0; padding-left: 1rem;">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Fee Payment Warning Banner -->
    <div class="alert" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border: 2px solid #f59e0b; color: #92400e; margin-bottom: 20px;">
        <i class='bx bx-info-circle' style="font-size: 24px; color: #f59e0b;"></i>
        <div>
            <strong>Important Notice:</strong> Students with outstanding fee balances are not eligible to take examinations. 
            Please ensure the selected student has cleared all fee payments before entering their exam scores.
        </div>
    </div>

    <form action="{{ route('college.final-exam-scores.store') }}" method="POST" id="scoreForm">
        @csrf
        <div class="form-layout">
            <!-- Main Form Section -->
            <div class="main-form-section">
                <!-- Exam Information -->
                <div class="form-card">
                    <div class="form-card-header">
                        <h3 class="form-card-title">
                            <i class="bx bx-book"></i>
                            Academic Information
                        </h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Academic Year <span class="required">*</span></label>
                                <select name="academic_year_id" id="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $currentAcademicYear?->id) == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Semester <span class="required">*</span></label>
                                <select name="semester_id" id="semester_id" class="form-select @error('semester_id') is-invalid @enderror" required>
                                    <option value="">Select Semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" {{ old('semester_id', $currentSemester?->id) == $semester->id ? 'selected' : '' }}>
                                            {{ $semester->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('semester_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Program <span class="required">*</span></label>
                                <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                                    <option value="">Select Program</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('program_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Course <span class="required">*</span></label>
                                <select name="course_id" id="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
                                    <option value="">Select Course</option>
                                     @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->code }} - {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam & Student Selection -->
                <div class="form-card" style="margin-top: 20px;">
                    <div class="form-card-header">
                        <h3 class="form-card-title">
                            <i class="bx bx-user-check"></i>
                            Exam & Student Selection
                        </h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Final Exam <span class="required">*</span></label>
                                <select name="exam_schedule_id" id="exam_schedule_id" class="form-select @error('exam_schedule_id') is-invalid @enderror" required>
                                    <option value="">Select Exam</option>
                                        @foreach($examSchedules as $exam)
                                            <option value="{{ $exam->id }}" {{ old('exam_schedule_id') == $exam->id ? 'selected' : '' }}>
                                                {{ $exam->exam_name }} - {{ $exam->course->code ?? '' }} {{ $exam->course->name ?? 'N/A' }} ({{ $exam->exam_date->format('M d, Y') }})
                                            </option>
                                        @endforeach
                                </select>
                                <p class="form-hint">Select the scheduled final exam</p>
                                @error('exam_schedule_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Student <span class="required">*</span></label>
                                <select name="course_registration_id" id="course_registration_id" class="form-select @error('course_registration_id') is-invalid @enderror" required>
                                    <option value="">Select Student</option>
                                     @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->student_number }} - {{ $student->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('course_registration_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Exam Info Display -->
                        <div class="info-card" id="examInfoCard">
                            <div class="info-card-header">
                                <i class='bx bx-calendar'></i>
                                <h4>Exam Information</h4>
                            </div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Exam Date</label>
                                    <span id="examDate">-</span>
                                </div>
                                <div class="info-item">
                                    <label>Exam Type</label>
                                    <span id="examType">-</span>
                                </div>
                                <div class="info-item">
                                    <label>Maximum Score</label>
                                    <span id="examMaxScore">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Student Info Display -->
                        <div class="info-card" id="studentInfoCard">
                            <div class="info-card-header">
                                <i class='bx bx-user'></i>
                                <h4>Student Information</h4>
                            </div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <label>Student ID</label>
                                    <span id="studentId">-</span>
                                </div>
                                <div class="info-item">
                                    <label>Student Name</label>
                                    <span id="studentName">-</span>
                                </div>
                                <div class="info-item">
                                    <label>CA Total</label>
                                    <span id="caTotal">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Score Entry Section -->
                        <div class="score-section">
                            <h4 class="score-section-title">
                                <i class='bx bx-calculator'></i>
                                Score Entry
                            </h4>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Exam Score <span class="required">*</span></label>
                                    <input type="number" 
                                           name="score" 
                                           id="score" 
                                           class="form-control @error('score') is-invalid @enderror" 
                                           min="0" 
                                           step="0.01" 
                                           value="{{ old('score') }}"
                                           placeholder="Enter exam score"
                                           required>
                                    <p class="form-hint">Maximum score: <strong id="maxScoreHint">-</strong></p>
                                    @error('score')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Score Preview</label>
                                    <div class="score-display-card">
                                        <div class="score-display-value">
                                            <span class="current" id="currentScoreDisplay">0</span>
                                            <span class="separator">/</span>
                                            <span class="max" id="maxScoreDisplay">0</span>
                                        </div>
                                        <div class="score-label">Points</div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="Optional remarks about this exam score">{{ old('remarks') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Section -->
            <div class="sidebar-section">
                <!-- Quick Info Card -->
                <div class="quick-info-card">
                    <h4 class="quick-info-title">
                        <i class='bx bx-info-circle'></i>
                        Quick Information
                    </h4>
                    <div class="info-item-quick">
                        <div class="info-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <i class='bx bx-book-open'></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Type</div>
                            <div class="info-value">Final Examination</div>
                        </div>
                    </div>
                    <div class="info-item-quick">
                        <div class="info-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                            <i class='bx bx-calendar'></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Date</div>
                            <div class="info-value">{{ now()->format('M d, Y') }}</div>
                        </div>
                    </div>
                    <div class="info-item-quick">
                        <div class="info-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <i class='bx bx-user'></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Entered By</div>
                            <div class="info-value">{{ auth()->user()->name }}</div>
                        </div>
                    </div>
                </div>

                <!-- Publish Toggle Card -->
                <div class="publish-card">
                    <h4 class="publish-title">
                        <i class='bx bx-globe'></i>
                        Publish Settings
                    </h4>
                    <div class="publish-option">
                        <div>
                            <div class="publish-label">Publish to Student Portal</div>
                            <div class="publish-hint">Student will see this result immediately</div>
                        </div>
                        <div class="toggle-switch" id="publishToggle" onclick="togglePublish()">
                            <input type="checkbox" name="is_published" value="1" id="publishCheckbox" {{ old('is_published') ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-card">
                    <button type="submit" class="btn-submit">
                        <i class='bx bx-save'></i>
                        Save Final Score
                    </button>
                    <a href="{{ route('college.final-exam-scores.index') }}" class="btn-cancel">
                        <i class='bx bx-x'></i>
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for all select elements
    $('#academic_year_id').select2({
        placeholder: 'Select Academic Year',
        allowClear: true,
        width: '100%'
    });

    $('#semester_id').select2({
        placeholder: 'Select Semester',
        allowClear: true,
        width: '100%'
    });

    $('#program_id').select2({
        placeholder: 'Select Program',
        allowClear: true,
        width: '100%'
    });

    $('#course_id').select2({
        placeholder: 'Select Course',
        allowClear: true,
        width: '100%'
    });

    $('#exam_schedule_id').select2({
        placeholder: 'Select Final Exam',
        allowClear: true,
        width: '100%'
    });

    $('#course_registration_id').select2({
        placeholder: 'Select Student',
        allowClear: true,
        width: '100%'
    });

    const programSelect = document.getElementById('program_id');
    const courseSelect = document.getElementById('course_id');
    const examSelect = document.getElementById('exam_schedule_id');
    const studentSelect = document.getElementById('course_registration_id');
    const scoreInput = document.getElementById('score');
    const maxScoreHint = document.getElementById('maxScoreHint');
    const maxScoreDisplay = document.getElementById('maxScoreDisplay');
    const currentScoreDisplay = document.getElementById('currentScoreDisplay');
    const studentInfoCard = document.getElementById('studentInfoCard');
    const examInfoCard = document.getElementById('examInfoCard');
    const publishCheckbox = document.getElementById('publishCheckbox');
    const publishToggle = document.getElementById('publishToggle');

    // Initialize publish toggle state
    if (publishCheckbox.checked) {
        publishToggle.classList.add('active');
    }

    // Load courses when program changes
    $('#program_id').on('change', function() {
        const programId = this.value;
        const academicYearId = document.getElementById('academic_year_id').value;
        const semesterId = document.getElementById('semester_id').value;

        // Reset dependent selects
        $('#course_id').empty().append('<option value="">Loading...</option>').trigger('change');
        $('#exam_schedule_id').empty().append('<option value="">Select Exam</option>').trigger('change');
        $('#course_registration_id').empty().append('<option value="">Select Student</option>').trigger('change');

        if (programId) {
            const url = `/college/api/courses-by-program?program_id=${programId}&academic_year_id=${academicYearId}&semester_id=${semesterId}`;
            console.log('Fetching URL:', url);
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) throw new Error('Network error: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Courses received:', data);
                    $('#course_id').empty().append('<option value="">Select Course</option>');
                    if (data.length === 0) {
                        $('#course_id').append('<option value="">No courses found for this program</option>');
                    } else {
                        data.forEach(course => {
                            $('#course_id').append(`<option value="${course.id}">${course.code} - ${course.name}</option>`);
                        });
                    }
                    $('#course_id').trigger('change');
                })
                .catch(error => {
                    console.error('Error loading courses:', error);
                    $('#course_id').empty().append('<option value="">Failed to load courses</option>').trigger('change');
                });
        } else {
            $('#course_id').empty().append('<option value="">Select Course</option>').trigger('change');
        }
    });

    // Load exams and students when course changes
    $('#course_id').on('change', function() {
        const courseId = this.value;
        const academicYearId = document.getElementById('academic_year_id').value;
        const semesterId = document.getElementById('semester_id').value;

        $('#exam_schedule_id').empty().append('<option value="">Loading...</option>').trigger('change');
        $('#course_registration_id').empty().append('<option value="">Loading...</option>').trigger('change');

        if (courseId) {
            // Load final exams
            fetch(`/college/api/final-exams?course_id=${courseId}&academic_year_id=${academicYearId}&semester_id=${semesterId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    $('#exam_schedule_id').empty().append('<option value="">Select Exam</option>');
                    if (data.length === 0) {
                        $('#exam_schedule_id').append('<option value="">No exams found</option>');
                    } else {
                        data.forEach(exam => {
                            $('#exam_schedule_id').append(`<option value="${exam.id}" data-max-score="${exam.total_marks || exam.max_score || 100}" data-date="${exam.exam_date}" data-type="${exam.exam_type || 'Final'}">${exam.exam_name || exam.title} - ${exam.exam_date}</option>`);
                        });
                    }
                    $('#exam_schedule_id').trigger('change');
                })
                .catch(error => {
                    console.error('Error loading exams:', error);
                    $('#exam_schedule_id').empty().append('<option value="">Failed to load exams</option>').trigger('change');
                });

            // Load registered students
            fetch(`/college/api/registered-students?course_id=${courseId}&academic_year_id=${academicYearId}&semester_id=${semesterId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    $('#course_registration_id').empty().append('<option value="">Select Student</option>');
                    if (data.length === 0) {
                        $('#course_registration_id').append('<option value="">No registered students found</option>');
                    } else {
                        data.forEach(reg => {
                            $('#course_registration_id').append(`<option value="${reg.id}" data-student-id="${reg.student_number || reg.student_id}" data-student-name="${reg.student_name}" data-ca-total="${reg.ca_total || 'N/A'}">${reg.student_number || reg.student_id} - ${reg.student_name}</option>`);
                        });
                    }
                    $('#course_registration_id').trigger('change');
                })
                .catch(error => {
                    console.error('Error loading students:', error);
                    $('#course_registration_id').empty().append('<option value="">Failed to load students</option>').trigger('change');
                });
        } else {
            $('#exam_schedule_id').empty().append('<option value="">Select Exam</option>').trigger('change');
            $('#course_registration_id').empty().append('<option value="">Select Student</option>').trigger('change');
        }
    });

    // Update exam info display when exam changes
    $('#exam_schedule_id').on('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const maxScore = selectedOption.dataset.maxScore || 100;
            maxScoreHint.textContent = maxScore;
            maxScoreDisplay.textContent = maxScore;
            scoreInput.max = maxScore;
            
            document.getElementById('examDate').textContent = selectedOption.dataset.date || '-';
            document.getElementById('examType').textContent = selectedOption.dataset.type || 'Final';
            document.getElementById('examMaxScore').textContent = maxScore;
            examInfoCard.classList.add('show');
        } else {
            examInfoCard.classList.remove('show');
            maxScoreHint.textContent = '-';
            maxScoreDisplay.textContent = '0';
        }
    });

    // Show student info when student is selected
    $('#course_registration_id').on('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.value) {
            document.getElementById('studentId').textContent = selectedOption.dataset.studentId || '-';
            document.getElementById('studentName').textContent = selectedOption.dataset.studentName || '-';
            document.getElementById('caTotal').textContent = selectedOption.dataset.caTotal || 'Not Calculated';
            studentInfoCard.classList.add('show');
        } else {
            studentInfoCard.classList.remove('show');
        }
    });

    // Update score display in real-time
    scoreInput.addEventListener('input', function() {
        currentScoreDisplay.textContent = this.value || '0';
    });
});

// Toggle publish switch
function togglePublish() {
    const toggle = document.getElementById('publishToggle');
    const checkbox = document.getElementById('publishCheckbox');
    toggle.classList.toggle('active');
    checkbox.checked = toggle.classList.contains('active');
}
</script>
@endpush
