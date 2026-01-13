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
        border-radius: 20px;
        padding: 30px;
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(5, 150, 105, 0.3);
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

    /* Form Layout */    /* Main Form Layout */
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
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
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
        border-color: #10b981;
        background: white;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
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
        border-color: #10b981;
        background: #f0fdf4;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }

    .exam-type-card.selected {
        border-color: #10b981;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
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
        color: #065f46;
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
        color: #047857;
    }

    .exam-type-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #10b981;
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
        border-color: #10b981;
        background: #f0fdf4;
    }

    .material-item.selected {
        border-color: #10b981;
        background: #ecfdf5;
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
        background: #10b981;
        border-color: #10b981;
        color: white;
    }

    .material-label {
        font-size: 13px;
        color: #475569;
        font-weight: 500;
    }

    .material-item.selected .material-label {
        color: #065f46;
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
        color: #10b981;
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
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-radius: 16px;
        padding: 20px;
        border: 2px solid #fbbf24;
    }

    .status-title {
        font-size: 14px;
        font-weight: 700;
        color: #92400e;
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
        border-color: #f59e0b;
    }

    .status-option.selected {
        border-color: #f59e0b;
        background: #fffbeb;
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
        border-color: #f59e0b;
        background: #f59e0b;
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
        background: #10b981;
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
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
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
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
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

    /* Date Picker Styles */
    .date-picker-wrapper {
        position: relative;
    }

    .date-input-group {
        position: relative;
    }

    .date-input {
        padding-left: 45px !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        color: #1e293b !important;
    }

    .date-display {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        align-items: center;
        gap: 8px;
        color: #10b981;
        font-size: 18px;
        pointer-events: none;
    }

    .date-display span {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    .date-info-box {
        display: flex;
        gap: 20px;
        margin-top: 10px;
        padding: 12px 16px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-radius: 10px;
        border: 1px solid #a7f3d0;
    }

    .date-info-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #065f46;
    }

    .date-info-item i {
        font-size: 18px;
    }

    /* Time Summary Box */
    .time-summary-box {
        margin-top: 20px;
        padding: 18px;
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        border-radius: 12px;
        border: 2px solid #7dd3fc;
    }

    .time-summary-title {
        font-size: 14px;
        font-weight: 700;
        color: #0369a1;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .time-summary-title i {
        font-size: 18px;
    }

    .time-summary-content {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }

    .time-summary-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .time-summary-item .label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .time-summary-item .value {
        font-size: 14px;
        font-weight: 700;
        color: #0c4a6e;
    }

    @media (max-width: 768px) {
        .time-summary-content {
            grid-template-columns: 1fr;
        }
        .date-info-box {
            flex-direction: column;
            gap: 10px;
        }
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
            <i class="bx bx-plus-circle"></i>
            Create New
        </span>
    </div>

    <!-- Page Header -->
    <div class="page-header-card">
        <div class="header-content">
            <h1 class="header-title">
                <i class="bx bx-calendar-plus"></i>
                Create New Exam Schedule
            </h1>
            <p class="header-subtitle">Schedule a new examination for your students</p>
        </div>
    </div>

    <form action="{{ route('college.exam-schedules.store') }}" method="POST" id="examForm">
        @csrf
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
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
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
                                        <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id ? 'selected' : '' }}>
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
                        <div class="form-row">
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
                                                <option value="{{ $level->code ?? $level->short_name }}" {{ old('level') == ($level->code ?? $level->short_name) ? 'selected' : '' }}>{{ $level->name }}</option>
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
                                <!-- Empty placeholder for grid alignment or add another field -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exam Details -->
                <div class="form-card" style="margin-top: 20px;">
                    <div class="form-card-header">
                        <h3 class="form-card-title">
                            <i class="bx bx-edit"></i>
                            Exam Details
                        </h3>
                    </div>
                    <div class="form-card-body">
                        <div class="form-group">
                            <label class="form-label">Exam Name <span class="required">*</span></label>
                            <input type="text" name="exam_name" class="form-control @error('exam_name') is-invalid @enderror" 
                                   value="{{ old('exam_name') }}" placeholder="e.g., Final Examination - Database Systems" required>
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
                                        <label class="exam-type-card {{ old('exam_type', 'midterm') == $key ? 'selected' : '' }}">
                                            <input type="radio" name="exam_type" value="{{ $key }}" 
                                                   {{ old('exam_type', 'midterm') == $key ? 'checked' : '' }} required>
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
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Brief description about this exam...">{{ old('description') }}</textarea>
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
                        <!-- Exam Date Selection with Calendar View -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bx bx-calendar-event text-success me-1"></i>
                                Examination Date <span class="required">*</span>
                            </label>
                            <div class="date-picker-wrapper">
                                <div class="date-input-group">
                                    <input type="date" name="exam_date" id="exam_date" 
                                           class="form-control date-input @error('exam_date') is-invalid @enderror" 
                                           value="{{ old('exam_date') }}" 
                                           min="{{ date('Y-m-d') }}" required>
                                    <div class="date-display" id="dateDisplay">
                                        <i class="bx bx-calendar"></i>
                                    </div>
                                </div>
                                <div class="date-info-box" id="dateInfoBox" style="display: none;">
                                    <div class="date-info-item">
                                        <i class="bx bx-calendar-check text-primary"></i>
                                        <span id="dayOfWeek">-</span>
                                    </div>
                                    <div class="date-info-item">
                                        <i class="bx bx-time text-warning"></i>
                                        <span id="daysUntilExam">-</span>
                                    </div>
                                </div>
                            </div>
                            @error('exam_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-hint">
                                <i class="bx bx-info-circle"></i> Select the date when this examination will be conducted
                            </div>
                        </div>

                        <div class="form-row-3" style="margin-top: 15px;">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bx bx-log-in-circle text-success me-1"></i>
                                    Start Time <span class="required">*</span>
                                </label>
                                <input type="time" name="start_time" id="start_time" 
                                       class="form-control @error('start_time') is-invalid @enderror" 
                                       value="{{ old('start_time', '09:00') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bx bx-log-out-circle text-danger me-1"></i>
                                    End Time <span class="required">*</span>
                                </label>
                                <input type="time" name="end_time" id="end_time" 
                                       class="form-control @error('end_time') is-invalid @enderror" 
                                       value="{{ old('end_time', '12:00') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bx bx-timer text-info me-1"></i>
                                    Duration (min) <span class="required">*</span>
                                </label>
                                <input type="number" name="duration_minutes" id="duration_minutes" 
                                       class="form-control @error('duration_minutes') is-invalid @enderror" 
                                       value="{{ old('duration_minutes', 180) }}" min="30" max="480" required>
                                <div class="form-hint">30 - 480 minutes</div>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Time Summary -->
                        <div class="time-summary-box" id="timeSummaryBox">
                            <div class="time-summary-title">
                                <i class="bx bx-calendar-check"></i> Exam Schedule Summary
                            </div>
                            <div class="time-summary-content">
                                <div class="time-summary-item">
                                    <span class="label">Date:</span>
                                    <span class="value" id="summaryDate">Not selected</span>
                                </div>
                                <div class="time-summary-item">
                                    <span class="label">Time:</span>
                                    <span class="value" id="summaryTime">09:00 - 12:00</span>
                                </div>
                                <div class="time-summary-item">
                                    <span class="label">Duration:</span>
                                    <span class="value" id="summaryDuration">3 hours 0 minutes</span>
                                </div>
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
                                                {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
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
                                       value="{{ old('building') }}" placeholder="Auto-filled from venue" readonly style="background-color: #f8fafc;">
                                @error('building')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Room Capacity</label>
                                <input type="number" name="capacity" id="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                                       value="{{ old('capacity') }}" min="1" placeholder="Auto-filled" readonly style="background-color: #f8fafc;">
                                @error('capacity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">No. of Students</label>
                                <input type="number" name="number_of_students" class="form-control @error('number_of_students') is-invalid @enderror" 
                                       value="{{ old('number_of_students') }}" min="1" placeholder="Students taking exam">
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
                                       value="{{ old('total_marks', 60) }}" min="1" max="100" step="0.01" required>
                                <div class="form-hint">Maximum marks for this exam</div>
                                @error('total_marks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Pass Marks <span class="required">*</span></label>
                                <input type="number" name="pass_marks" class="form-control @error('pass_marks') is-invalid @enderror" 
                                       value="{{ old('pass_marks', 24) }}" min="1" max="100" step="0.01" required>
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
                                        <option value="{{ $invigilator->id }}" {{ old('invigilator_id') == $invigilator->id ? 'selected' : '' }}>
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
                                       value="{{ old('invigilator_name') }}" placeholder="Invigilator name">
                                @error('invigilator_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Exam Instructions</label>
                            <textarea name="instructions" class="form-control @error('instructions') is-invalid @enderror" 
                                      placeholder="Special instructions for students during the exam...">{{ old('instructions') }}</textarea>
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
                                    $oldMaterials = old('materials_allowed', []);
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
                            <div class="info-value" id="summaryDate">Not set</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                            <i class="bx bx-time"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Time</div>
                            <div class="info-value" id="summaryTime">09:00 - 12:00</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="bx bx-hourglass"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Duration</div>
                            <div class="info-value" id="summaryDuration">180 minutes</div>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="bx bx-trophy"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Total / Pass Marks</div>
                            <div class="info-value" id="summaryMarks">60 / 24</div>
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
                        <label class="status-option {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}">
                            <input type="radio" name="status" value="draft" {{ old('status', 'draft') == 'draft' ? 'checked' : '' }} required>
                            <div class="status-radio"></div>
                            <span class="status-text">📝 Draft</span>
                        </label>
                        <label class="status-option {{ old('status') == 'scheduled' ? 'selected' : '' }}">
                            <input type="radio" name="status" value="scheduled" {{ old('status') == 'scheduled' ? 'checked' : '' }}>
                            <div class="status-radio"></div>
                            <span class="status-text">📅 Scheduled</span>
                        </label>
                    </div>
                    <div class="publish-toggle">
                        <span class="publish-label">Publish to Students</span>
                        <label class="toggle-switch {{ old('is_published') ? 'active' : '' }}">
                            <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-card">
                    <button type="submit" class="btn-submit">
                        <i class="bx bx-save"></i>
                        Save Exam Schedule
                    </button>
                    <a href="{{ route('college.exam-schedules.index') }}" class="btn-cancel">
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
    // Venue Selection - Auto-populate building and capacity
    document.getElementById('venue_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const buildingInput = document.getElementById('building');
        const capacityInput = document.getElementById('capacity');
        
        if (this.value) {
            buildingInput.value = selectedOption.dataset.building || '';
            capacityInput.value = selectedOption.dataset.capacity || '';
        } else {
            buildingInput.value = '';
            capacityInput.value = '';
        }
    });
    
    // Trigger venue change on page load if already selected (for old values)
    if (document.getElementById('venue_id').value) {
        document.getElementById('venue_id').dispatchEvent(new Event('change'));
    }

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
    document.querySelector('input[name="exam_date"]').addEventListener('change', function() {
        updateDateDisplay();
        updateSummary();
    });
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

    // Enhanced Date Display Functions
    function updateDateDisplay() {
        const dateInput = document.getElementById('exam_date');
        const dateInfoBox = document.getElementById('dateInfoBox');
        const selectedDateText = document.getElementById('selectedDateText');
        const dayOfWeek = document.getElementById('dayOfWeek');
        const daysUntilExam = document.getElementById('daysUntilExam');
        
        if (dateInput.value) {
            const selectedDate = new Date(dateInput.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            
            // Format display text
            const formattedDate = `${days[selectedDate.getDay()]}, ${selectedDate.getDate()} ${months[selectedDate.getMonth()]} ${selectedDate.getFullYear()}`;
            selectedDateText.textContent = formattedDate;
            
            // Day of week
            dayOfWeek.textContent = days[selectedDate.getDay()];
            
            // Calculate days until exam
            const diffTime = selectedDate - today;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays === 0) {
                daysUntilExam.textContent = 'Today';
            } else if (diffDays === 1) {
                daysUntilExam.textContent = 'Tomorrow';
            } else if (diffDays > 1) {
                daysUntilExam.textContent = `${diffDays} days from now`;
            } else {
                daysUntilExam.textContent = 'Date has passed';
            }
            
            // Show info box
            dateInfoBox.style.display = 'flex';
            
            // Update summary
            document.getElementById('summaryDate').textContent = formattedDate;
        } else {
            selectedDateText.textContent = 'Select examination date';
            dateInfoBox.style.display = 'none';
            document.getElementById('summaryDate').textContent = 'Not selected';
        }
    }

    // Update time summary
    function updateTimeSummary() {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const duration = document.getElementById('duration_minutes').value;
        
        if (startTime && endTime) {
            // Format time to 12-hour format
            const formatTime = (time) => {
                const [hours, minutes] = time.split(':');
                const hour = parseInt(hours);
                const ampm = hour >= 12 ? 'PM' : 'AM';
                const formattedHour = hour % 12 || 12;
                return `${formattedHour}:${minutes} ${ampm}`;
            };
            
            document.getElementById('summaryTime').textContent = `${formatTime(startTime)} - ${formatTime(endTime)}`;
        }
        
        if (duration) {
            const hours = Math.floor(duration / 60);
            const mins = duration % 60;
            let durationText = '';
            if (hours > 0) durationText += `${hours} hour${hours > 1 ? 's' : ''}`;
            if (mins > 0) durationText += ` ${mins} minute${mins > 1 ? 's' : ''}`;
            document.getElementById('summaryDuration').textContent = durationText.trim() || `${duration} minutes`;
        }
    }

    // Add listeners for time fields
    document.getElementById('start_time').addEventListener('change', updateTimeSummary);
    document.getElementById('end_time').addEventListener('change', updateTimeSummary);
    document.getElementById('duration_minutes').addEventListener('change', updateTimeSummary);

    // Initialize on page load
    updateDateDisplay();
    updateTimeSummary();
    updateSummary();
</script>
@endpush
@endsection
