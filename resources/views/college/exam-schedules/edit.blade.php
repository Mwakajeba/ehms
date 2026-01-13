@extends('layouts.main')

@section('content')
<style>
    .exam-form-container {
        margin-left: 250px;
        padding: 20px 30px;
        background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
        min-height: 100vh;
    }

    /* Header Section */
    .page-header-card {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 50%, #1e40af 100%);
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

    .header-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        color: white;
        font-size: 13px;
        font-weight: 600;
        margin-top: 12px;
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

    /* Main Form Layout */
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
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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

    .form-row-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .form-row-4 {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
    }

    @media (max-width: 768px) {
        .form-row, .form-row-3, .form-row-4 {
            grid-template-columns: 1fr;
        }
    }

    /* Exam Type Cards */
    .exam-type-section {
        margin-top: 10px;
    }

    .exam-type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
    }

    .exam-type-card {
        position: relative;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .exam-type-card:hover {
        border-color: #3b82f6;
        background: #eff6ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    }

    .exam-type-card.selected {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
    }

    .exam-type-card input[type="radio"] {
        display: none;
    }

    .exam-type-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: white;
        flex-shrink: 0;
    }

    .exam-type-info {
        flex: 1;
        min-width: 0;
    }

    .exam-type-name {
        font-weight: 600;
        font-size: 13px;
        color: #1e293b;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .exam-type-card.selected .exam-type-name {
        color: #1e40af;
    }

    .exam-type-desc {
        font-size: 11px;
        color: #64748b;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .exam-type-card.selected .exam-type-desc {
        color: #3b82f6;
    }

    .exam-type-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #3b82f6;
        color: white;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 11px;
    }

    .exam-type-card.selected .exam-type-check {
        display: flex;
    }

    /* Materials Allowed */
    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
    }

    .material-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .material-item:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .material-item.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .material-item input {
        display: none;
    }

    .material-checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .material-item.selected .material-checkbox {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .material-label {
        font-size: 13px;
        color: #475569;
        font-weight: 500;
    }

    .material-item.selected .material-label {
        color: #1e40af;
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

    .info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f8fafc;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .info-item:last-child {
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

    /* Status Card */
    .status-card {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-radius: 16px;
        padding: 20px;
        border: 2px solid #3b82f6;
    }

    .status-title {
        font-size: 14px;
        font-weight: 700;
        color: #1e40af;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .status-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        background: white;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }

    .status-option:hover {
        border-color: #3b82f6;
    }

    .status-option.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .status-option input {
        display: none;
    }

    .status-radio {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .status-option.selected .status-radio {
        border-color: #3b82f6;
        background: #3b82f6;
    }

    .status-option.selected .status-radio::after {
        content: '';
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
    }

    .status-text {
        font-size: 13px;
        font-weight: 600;
        color: #1e293b;
    }

    /* Publish Toggle */
    .publish-toggle {
        margin-top: 15px;
        padding: 12px 14px;
        background: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .publish-label {
        font-size: 13px;
        color: #1e293b;
        font-weight: 500;
    }

    .toggle-switch {
        position: relative;
        width: 44px;
        height: 24px;
        background: #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .toggle-switch.active {
        background: #3b82f6;
    }

    .toggle-switch::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        width: 20px;
        height: 20px;
        background: white;
        border-radius: 50%;
        transition: all 0.3s;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .toggle-switch.active::after {
        left: 22px;
    }

    .toggle-switch input {
        display: none;
    }

    /* Action Buttons */
    .action-card {
        background: white;
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .btn-submit {
        width: 100%;
        padding: 14px 20px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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

    /* Validation Errors */
    .is-invalid {
        border-color: #ef4444 !important;
    }

    .invalid-feedback {
        color: #ef4444;
        font-size: 12px;
        margin-top: 6px;
    }

    .text-danger {
        color: #ef4444;
        font-size: 12px;
        margin-top: 6px;
    }

    /* Textarea */
    textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    /* Alert for postponed status */
    .alert-warning-custom {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 2px solid #f59e0b;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .alert-warning-custom i {
        font-size: 24px;
        color: #d97706;
    }

    .alert-warning-custom .alert-content {
        flex: 1;
    }

    .alert-warning-custom .alert-title {
        font-weight: 700;
        color: #92400e;
        margin-bottom: 2px;
    }

    .alert-warning-custom .alert-text {
        font-size: 13px;
        color: #a16207;
    }
</style>

<div class="exam-form-container">
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
        <a href="{{ route('college.exam-schedules.index') }}" class="breadcrumb-btn">
            <i class="bx bx-calendar"></i>
            Exam Schedules
        </a>
        <span class="breadcrumb-separator">›</span>
        <span class="breadcrumb-btn active">
            <i class="bx bx-edit"></i>
            Edit Schedule
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <h1 class="header-title">
                <i class="bx bx-edit"></i>
                Edit Exam Schedule
            </h1>
            <p class="header-subtitle">Update examination details for {{ $examSchedule->exam_name }}</p>
            <div class="header-badge">
                <i class="bx bx-info-circle"></i>
                Status: {{ $examSchedule->status_name }}
            </div>
        </div>
    </div>

    @if($examSchedule->status == 'postponed')
    <div class="alert-warning-custom">
        <i class="bx bx-error-circle"></i>
        <div class="alert-content">
            <div class="alert-title">Exam Postponed</div>
            <div class="alert-text">{{ $examSchedule->status_remarks ?? 'This exam has been postponed. Please update the schedule accordingly.' }}</div>
        </div>
    </div>
    @endif

    <form action="{{ route('college.exam-schedules.update', $examSchedule) }}" method="POST" id="examForm">
        @csrf
        @method('PUT')
        <div class="form-layout">
            <!-- Main Form Section -->
            <div class="main-form-section">
                <!-- Academic Information -->
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
                                <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" required>
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $examSchedule->academic_year_id) == $year->id ? 'selected' : '' }}>
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
                                <select name="semester_id" class="form-select @error('semester_id') is-invalid @enderror" required>
                                    <option value="">Select Semester</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" {{ old('semester_id', $examSchedule->semester_id) == $semester->id ? 'selected' : '' }}>
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
                                        <option value="{{ $program->id }}" {{ old('program_id', $examSchedule->program_id) == $program->id ? 'selected' : '' }}>
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
                                        <option value="{{ $course->id }}" {{ old('course_id', $examSchedule->course_id) == $course->id ? 'selected' : '' }}>
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

                <!-- Exam Details -->
                <div class="form-card" style="margin-top: 20px;">
                    <div class="form-card-header">
                        <h3 class="form-card-title">
                            <i class="bx bx-file"></i>
                            Exam Details
                        </h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label class="form-label">Exam Name <span class="required">*</span></label>
                            <input type="text" name="exam_name" class="form-control @error('exam_name') is-invalid @enderror" 
                                   value="{{ old('exam_name', $examSchedule->exam_name) }}" placeholder="e.g., Final Examination - Database Systems" required>
                            @error('exam_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Exam Type <span class="required">*</span></label>
                            <div class="exam-type-section">
                                <div class="exam-type-grid">
                                    @php
                                        $examTypeDescriptions = \App\Models\College\ExamSchedule::EXAM_TYPE_DESCRIPTIONS;
                                        $examTypeIcons = \App\Models\College\ExamSchedule::EXAM_TYPE_ICONS;
                                        $examTypeColors = \App\Models\College\ExamSchedule::EXAM_TYPE_COLORS;
                                    @endphp
                                    @foreach($examTypes as $key => $value)
                                        <label class="exam-type-card {{ old('exam_type', $examSchedule->exam_type) == $key ? 'selected' : '' }}">
                                            <input type="radio" name="exam_type" value="{{ $key }}" 
                                                   {{ old('exam_type', $examSchedule->exam_type) == $key ? 'checked' : '' }} required>
                                            <div class="exam-type-icon" style="background: {{ $examTypeColors[$key] ?? '#6b7280' }};">
                                                <i class="bx {{ $examTypeIcons[$key] ?? 'bx-file' }}"></i>
                                            </div>
                                            <div class="exam-type-info">
                                                <div class="exam-type-name">{{ $value }}</div>
                                                <div class="exam-type-desc">{{ $examTypeDescriptions[$key] ?? '' }}</div>
                                            </div>
                                            <div class="exam-type-check">
                                                <i class="bx bx-check"></i>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            @error('exam_type')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bx bx-layer text-primary me-1"></i>
                                Academic Level <span class="required">*</span>
                            </label>
                            <select name="level" id="level" class="form-select @error('level') is-invalid @enderror" required>
                                <option value="">Select Academic Level</option>
                                @php
                                    $groupedLevels = $levels->groupBy('category');
                                @endphp
                                @foreach($groupedLevels as $category => $categoryLevels)
                                    <optgroup label="{{ \App\Models\College\Level::CATEGORIES[$category] ?? ucfirst($category) }}">
                                        @foreach($categoryLevels as $level)
                                            <option value="{{ $level->code ?? $level->short_name }}" {{ old('level', $examSchedule->level) == ($level->code ?? $level->short_name) ? 'selected' : '' }}>{{ $level->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-hint">
                                <i class="bx bx-info-circle"></i> Select the academic qualification level for this examination.
                                <a href="{{ route('college.levels.index') }}" class="text-primary" target="_blank">Manage Levels</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Brief description about this exam...">{{ old('description', $examSchedule->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Schedule & Timing -->
                <div class="form-card" style="margin-top: 20px;">
                    <div class="form-card-header">
                        <h3 class="form-card-title">
                            <i class="bx bx-time-five"></i>
                            Schedule & Timing
                        </h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-row-4">
                            <div class="form-group">
                                <label class="form-label">Exam Date <span class="required">*</span></label>
                                <input type="date" name="exam_date" class="form-control @error('exam_date') is-invalid @enderror" 
                                       value="{{ old('exam_date', $examSchedule->exam_date->format('Y-m-d')) }}" required>
                                @error('exam_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Start Time <span class="required">*</span></label>
                                <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                       value="{{ old('start_time', \Carbon\Carbon::parse($examSchedule->start_time)->format('H:i')) }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">End Time <span class="required">*</span></label>
                                <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                       value="{{ old('end_time', \Carbon\Carbon::parse($examSchedule->end_time)->format('H:i')) }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Duration (min) <span class="required">*</span></label>
                                <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                       value="{{ old('duration_minutes', $examSchedule->duration_minutes) }}" min="30" max="480" required>
                                <div class="form-hint">30 - 480 minutes</div>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Venue & Marks -->
                <div class="form-card" style="margin-top: 20px;">
                    <div class="form-card-header">
                        <h3 class="form-card-title">
                            <i class="bx bx-map-pin"></i>
                            Venue & Marks Configuration
                        </h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-row-4">
                            <div class="form-group">
                                <label class="form-label">Venue / Hall <span class="required">*</span></label>
                                <select name="venue_id" id="venue_id" class="form-select @error('venue_id') is-invalid @enderror" required>
                                    <option value="">-- Select Venue --</option>
                                    @foreach($venues as $venue)
                                        <option value="{{ $venue->id }}" 
                                                data-building="{{ $venue->building }}" 
                                                data-capacity="{{ $venue->capacity }}"
                                                data-code="{{ $venue->code }}"
                                                data-type="{{ $venue->venue_type }}"
                                                {{ old('venue_id', $examSchedule->venue ? '' : '') == $venue->id || (str_contains($examSchedule->venue ?? '', $venue->name) && str_contains($examSchedule->venue ?? '', $venue->code)) ? 'selected' : '' }}>
                                            {{ $venue->name }} ({{ $venue->code }}) - Cap: {{ $venue->capacity }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('venue_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Building</label>
                                <input type="text" name="building" id="building" class="form-control @error('building') is-invalid @enderror" 
                                       value="{{ old('building', $examSchedule->building) }}" placeholder="Auto-filled from venue" readonly style="background-color: #f8fafc;">
                                @error('building')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Room Capacity</label>
                                <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                                       value="{{ old('capacity', $examSchedule->capacity) }}" min="1" placeholder="Auto-filled" readonly style="background-color: #f8fafc;">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">No. of Students</label>
                                <input type="number" name="number_of_students" class="form-control @error('number_of_students') is-invalid @enderror" 
                                       value="{{ old('number_of_students', $examSchedule->number_of_students) }}" min="1" placeholder="Students taking exam">
                                <div class="form-hint">Expected number of examinees</div>
                                @error('number_of_students')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Total Marks <span class="required">*</span></label>
                                <input type="number" name="total_marks" class="form-control @error('total_marks') is-invalid @enderror" 
                                       value="{{ old('total_marks', $examSchedule->total_marks) }}" min="1" max="100" step="0.01" required>
                                <div class="form-hint">Maximum marks for this exam</div>
                                @error('total_marks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pass Marks <span class="required">*</span></label>
                                <input type="number" name="pass_marks" class="form-control @error('pass_marks') is-invalid @enderror" 
                                       value="{{ old('pass_marks', $examSchedule->pass_marks) }}" min="1" max="100" step="0.01" required>
                                <div class="form-hint">Minimum marks to pass</div>
                                @error('pass_marks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invigilator & Instructions -->
                <div class="form-card" style="margin-top: 20px;">
                    <div class="form-card-header">
                        <h3 class="form-card-title">
                            <i class="bx bx-user-check"></i>
                            Invigilator & Instructions
                        </h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Select Invigilator</label>
                                <select name="invigilator_id" class="form-select @error('invigilator_id') is-invalid @enderror">
                                    <option value="">-- Select from system --</option>
                                    @foreach($invigilators as $invigilator)
                                        <option value="{{ $invigilator->id }}" {{ old('invigilator_id', $examSchedule->invigilator_id) == $invigilator->id ? 'selected' : '' }}>
                                            {{ $invigilator->first_name }} {{ $invigilator->middle_name }} {{ $invigilator->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('invigilator_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Or Enter Manually</label>
                                <input type="text" name="invigilator_name" class="form-control @error('invigilator_name') is-invalid @enderror" 
                                       value="{{ old('invigilator_name', $examSchedule->invigilator_name) }}" placeholder="Invigilator name">
                                @error('invigilator_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Exam Instructions</label>
                            <textarea name="instructions" class="form-control @error('instructions') is-invalid @enderror" 
                                      placeholder="Special instructions for students during the exam...">{{ old('instructions', $examSchedule->instructions) }}</textarea>
                            @error('instructions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Materials Allowed</label>
                            <div class="materials-grid">
                                @php
                                    $materials = [
                                        'Calculator', 
                                        'Scientific Calculator', 
                                        'Formula Sheet', 
                                        'Dictionary', 
                                        'Open Book', 
                                        'Notes', 
                                        'Graph Paper', 
                                        'Drawing Tools',
                                        'Ruler/Protractor',
                                        'Laptop/Computer',
                                        'Mobile Phone',
                                        'Reference Books',
                                        'Code Sheet',
                                        'Statistical Tables',
                                        'Law Books',
                                        'Case Materials',
                                        'Medical Charts',
                                        'Periodic Table',
                                        'Blank Paper',
                                        'Colored Pencils',
                                        'None (Closed Book)'
                                    ];
                                    $oldMaterials = old('materials_allowed', $examSchedule->materials_allowed ?? []);
                                @endphp
                                @foreach($materials as $material)
                                    <label class="material-item {{ in_array($material, $oldMaterials) ? 'selected' : '' }}">
                                        <input type="checkbox" name="materials_allowed[]" value="{{ $material }}"
                                               {{ in_array($material, $oldMaterials) ? 'checked' : '' }}>
                                        <div class="material-checkbox">
                                            <i class="bx bx-check" style="font-size: 12px;"></i>
                                        </div>
                                        <span class="material-label">{{ $material }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Remarks (for postponed exams) -->
                @if($examSchedule->status == 'postponed' || $examSchedule->status_remarks)
                <div class="form-card" style="margin-top: 20px;">
                    <div class="form-card-header">
                        <h3 class="form-card-title">
                            <i class="bx bx-message-detail"></i>
                            Status Remarks
                        </h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label class="form-label">Reason / Remarks</label>
                            <textarea name="status_remarks" class="form-control @error('status_remarks') is-invalid @enderror" 
                                      placeholder="Reason for postponement or any additional remarks...">{{ old('status_remarks', $examSchedule->status_remarks) }}</textarea>
                            @error('status_remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="sidebar-section">
                <!-- Quick Summary -->
                <div class="quick-info-card">
                    <h4 class="quick-info-title">
                        <i class="bx bx-info-circle"></i>
                        Quick Summary
                    </h4>
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                            <i class="bx bx-calendar"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Exam Date</div>
                            <div class="info-value" id="summaryDate">{{ $examSchedule->exam_date->format('D, M d, Y') }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                            <i class="bx bx-time"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Time</div>
                            <div class="info-value" id="summaryTime">{{ \Carbon\Carbon::parse($examSchedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($examSchedule->end_time)->format('H:i') }}</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="bx bx-hourglass"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Duration</div>
                            <div class="info-value" id="summaryDuration">{{ $examSchedule->duration_minutes }} minutes</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="bx bx-trophy"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Total / Pass Marks</div>
                            <div class="info-value" id="summaryMarks">{{ number_format($examSchedule->total_marks, 0) }} / {{ number_format($examSchedule->pass_marks, 0) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Status Selection -->
                <div class="status-card">
                    <h4 class="status-title">
                        <i class="bx bx-cog"></i>
                        Status & Publishing
                    </h4>
                    <div class="status-options">
                        <label class="status-option {{ old('status', $examSchedule->status) == 'draft' ? 'selected' : '' }}">
                            <input type="radio" name="status" value="draft" {{ old('status', $examSchedule->status) == 'draft' ? 'checked' : '' }} required>
                            <div class="status-radio"></div>
                            <span class="status-text">📝 Draft</span>
                        </label>
                        <label class="status-option {{ old('status', $examSchedule->status) == 'scheduled' ? 'selected' : '' }}">
                            <input type="radio" name="status" value="scheduled" {{ old('status', $examSchedule->status) == 'scheduled' ? 'checked' : '' }}>
                            <div class="status-radio"></div>
                            <span class="status-text">📅 Scheduled</span>
                        </label>
                        <label class="status-option {{ old('status', $examSchedule->status) == 'postponed' ? 'selected' : '' }}">
                            <input type="radio" name="status" value="postponed" {{ old('status', $examSchedule->status) == 'postponed' ? 'checked' : '' }}>
                            <div class="status-radio"></div>
                            <span class="status-text">⏸️ Postponed</span>
                        </label>
                    </div>
                    <div class="publish-toggle">
                        <span class="publish-label">Publish to Students</span>
                        <label class="toggle-switch {{ old('is_published', $examSchedule->is_published) ? 'active' : '' }}">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $examSchedule->is_published) ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-card">
                    <button type="submit" class="btn-submit">
                        <i class="bx bx-save"></i>
                        Update Exam Schedule
                    </button>
                    <a href="{{ route('college.exam-schedules.show', $examSchedule) }}" class="btn-cancel">
                        <i class="bx bx-x"></i>
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Exam Type Card Selection
    document.querySelectorAll('.exam-type-card').forEach(card => {
        card.addEventListener('click', function() {
            document.querySelectorAll('.exam-type-card').forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Material Item Selection
    document.querySelectorAll('.material-item').forEach(item => {
        item.addEventListener('click', function() {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('selected', checkbox.checked);
        });
    });

    // Status Option Selection
    document.querySelectorAll('.status-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.status-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            this.querySelector('input[type="radio"]').checked = true;
        });
    });

    // Toggle Switch
    document.querySelectorAll('.toggle-switch').forEach(toggle => {
        toggle.addEventListener('click', function() {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            this.classList.toggle('active', checkbox.checked);
        });
    });

    // Dynamic course loading based on program selection
    document.getElementById('program_id').addEventListener('change', function() {
        const programId = this.value;
        const courseSelect = document.getElementById('course_id');
        
        if (programId) {
            fetch(`{{ route('college.exam-schedules.courses-by-program') }}?program_id=${programId}`)
                .then(response => response.json())
                .then(courses => {
                    courseSelect.innerHTML = '<option value="">Select Course</option>';
                    courses.forEach(course => {
                        courseSelect.innerHTML += `<option value="${course.id}">${course.code} - ${course.name}</option>`;
                    });
                });
        } else {
            courseSelect.innerHTML = '<option value="">Select Course</option>';
        }
    });

    // Update Summary
    function updateSummary() {
        const date = document.querySelector('input[name="exam_date"]').value;
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;
        const duration = document.querySelector('input[name="duration_minutes"]').value;
        const totalMarks = document.querySelector('input[name="total_marks"]').value;
        const passMarks = document.querySelector('input[name="pass_marks"]').value;

        if (date) {
            const dateObj = new Date(date);
            document.getElementById('summaryDate').textContent = dateObj.toLocaleDateString('en-US', { 
                weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' 
            });
        }

        if (startTime && endTime) {
            document.getElementById('summaryTime').textContent = `${startTime} - ${endTime}`;
        }

        if (duration) {
            document.getElementById('summaryDuration').textContent = `${duration} minutes`;
        }

        if (totalMarks && passMarks) {
            document.getElementById('summaryMarks').textContent = `${totalMarks} / ${passMarks}`;
        }
    }

    // Auto-calculate duration when start and end time change
    document.querySelector('input[name="start_time"]').addEventListener('change', function() {
        calculateDuration();
        updateSummary();
    });
    document.querySelector('input[name="end_time"]').addEventListener('change', function() {
        calculateDuration();
        updateSummary();
    });
    document.querySelector('input[name="exam_date"]').addEventListener('change', updateSummary);
    document.querySelector('input[name="duration_minutes"]').addEventListener('change', updateSummary);
    document.querySelector('input[name="total_marks"]').addEventListener('change', updateSummary);
    document.querySelector('input[name="pass_marks"]').addEventListener('change', updateSummary);

    function calculateDuration() {
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;
        
        if (startTime && endTime) {
            const start = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);
            const diff = (end - start) / 60000;
            
            if (diff > 0) {
                document.querySelector('input[name="duration_minutes"]').value = diff;
            }
        }
    }

    // Venue selection - auto-fill building and capacity
    document.getElementById('venue_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const building = selectedOption.getAttribute('data-building') || '';
        const capacity = selectedOption.getAttribute('data-capacity') || '';
        
        document.getElementById('building').value = building;
        document.getElementById('capacity').value = capacity;
    });

    // Trigger venue change on page load if a venue is already selected
    if (document.getElementById('venue_id').value) {
        document.getElementById('venue_id').dispatchEvent(new Event('change'));
    }
</script>
@endpush
@endsection
