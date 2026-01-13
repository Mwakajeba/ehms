@extends('layouts.main')

@section('title', 'Create Timetable Entry')

@push('styles')
<style>
    .wizard-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
    }
    .wizard-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
    }
    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 50px;
        right: 50px;
        height: 3px;
        background: rgba(255,255,255,0.3);
        z-index: 1;
    }
    .wizard-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
    }
    .wizard-step-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }
    .wizard-step.active .wizard-step-icon {
        background: white;
        color: #667eea;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .wizard-step.completed .wizard-step-icon {
        background: #28a745;
    }
    .wizard-step-label {
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }
    .form-section:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }
    .form-section-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f0f0f0;
    }
    .form-section-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-right: 0.75rem;
    }
    .form-section-title {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        color: #333;
    }
    .form-section-subtitle {
        font-size: 0.8rem;
        color: #6c757d;
        margin: 0;
    }
    
    .quick-time-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.5rem;
    }
    .quick-time-btn {
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        background: #e9ecef;
        border: none;
        color: #495057;
        cursor: pointer;
        transition: all 0.2s;
    }
    .quick-time-btn:hover {
        background: #667eea;
        color: white;
    }
    
    .day-selector {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .day-btn {
        padding: 0.5rem 1rem;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: 500;
    }
    .day-btn:hover {
        border-color: #667eea;
        background: #f8f9fa;
    }
    .day-btn.active {
        border-color: #667eea;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .session-type-selector {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 0.5rem;
    }
    .session-type-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.75rem 0.5rem;
        border: 2px solid #dee2e6;
        border-radius: 10px;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }
    .session-type-btn i {
        font-size: 1.5rem;
        margin-bottom: 0.25rem;
    }
    .session-type-btn span {
        font-size: 0.75rem;
        font-weight: 500;
    }
    .session-type-btn:hover {
        border-color: #667eea;
        transform: translateY(-2px);
    }
    .session-type-btn.active {
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
    .session-type-btn.active.lecture { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); color: white; }
    .session-type-btn.active.tutorial { background: linear-gradient(135deg, #198754 0%, #157347 100%); color: white; }
    .session-type-btn.active.practical { background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); color: #333; }
    .session-type-btn.active.lab { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); color: white; }
    .session-type-btn.active.seminar { background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; }
    .session-type-btn.active.workshop { background: linear-gradient(135deg, #212529 0%, #343a40 100%); color: white; }
    .session-type-btn.active.exam { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; }
    
    .summary-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed #dee2e6;
    }
    .summary-item:last-child {
        border-bottom: none;
    }
    .summary-label {
        color: #6c757d;
        font-size: 0.85rem;
    }
    .summary-value {
        font-weight: 600;
        color: #333;
    }
    
    .floating-submit {
        position: sticky;
        bottom: 1rem;
        background: white;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
        z-index: 100;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        font-size: 0.875rem;
        margin-bottom: 0.4rem;
    }
    .form-label .text-danger {
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="page-content" style="margin-top: 70px; margin-left: 235px; margin-right: 20px;">
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="row mb-3">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="d-flex align-items-center flex-wrap gap-1">
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bx bx-home-alt me-1"></i> Dashboard
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bx bx-book-reader me-1"></i> College
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.timetables.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bx bx-calendar-alt me-1"></i> Timetables
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <span class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="bx bx-plus-circle me-1"></i> Create Entry
                    </span>
                </nav>
            </div>
        </div>

        <!-- Wizard Header -->
        <div class="wizard-container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h3 class="mb-1"><i class="bx bx-calendar-plus me-2"></i>Create Timetable Entry</h3>
                    <p class="mb-0 opacity-75">Add a new timetable session with all scheduling details</p>
                </div>
                <div class="col-lg-6">
                    <div class="wizard-steps">
                        <div class="wizard-step active" id="step1">
                            <div class="wizard-step-icon"><i class="bx bx-calendar"></i></div>
                            <span class="wizard-step-label">Basic Info</span>
                        </div>
                        <div class="wizard-step" id="step2">
                            <div class="wizard-step-icon"><i class="bx bx-book-open"></i></div>
                            <span class="wizard-step-label">Course & Slot</span>
                        </div>
                        <div class="wizard-step" id="step3">
                            <div class="wizard-step-icon"><i class="bx bx-map-pin"></i></div>
                            <span class="wizard-step-label">Venue & Staff</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('college.timetables.store-entry') }}" method="POST" id="timetableEntryForm">
            @csrf
            
            <div class="row">
                <!-- Main Form Column -->
                <div class="col-lg-8">
                    <!-- Section 1: Basic Information -->
                    <div class="form-section" id="section1">
                        <div class="form-section-header">
                            <div class="form-section-icon bg-primary-subtle text-primary">
                                <i class="bx bx-calendar-event"></i>
                            </div>
                            <div>
                                <h5 class="form-section-title">Basic Information</h5>
                                <p class="form-section-subtitle">Define the timetable context</p>
                            </div>
                        </div>

                        <div id="newTimetableFields">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                    <select name="academic_year_id" class="form-select @error('academic_year_id') is-invalid @enderror" id="academicYear" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ (old('academic_year_id') ?? ($currentAcademicYear->id ?? '')) == $year->id ? 'selected' : '' }}>
                                                {{ $year->name }} @if($year->is_current ?? false) (Current) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('academic_year_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select name="semester_id" class="form-select @error('semester_id') is-invalid @enderror" id="semester" required>
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

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Programme <span class="text-danger">*</span></label>
                                    <select name="program_id" class="form-select @error('program_id') is-invalid @enderror" id="programSelect" required>
                                        <option value="">Select Programme</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}" 
                                                data-duration="{{ $program->duration_years }}"
                                                {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                                {{ $program->code }} - {{ $program->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('program_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Level/Year <span class="text-danger">*</span></label>
                                    <select name="year_of_study" class="form-select @error('year_of_study') is-invalid @enderror" id="levelSelect" required>
                                        <option value="">Select Level</option>
                                        @foreach($levels ?? [] as $level)
                                            <option value="{{ $level->id }}" {{ old('year_of_study') == $level->id ? 'selected' : '' }}>
                                                {{ $level->name }} ({{ $level->short_name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('year_of_study')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Course & Schedule -->
                    <div class="form-section" id="section2">
                        <div class="form-section-header">
                            <div class="form-section-icon bg-success-subtle text-success">
                                <i class="bx bx-book-open"></i>
                            </div>
                            <div>
                                <h5 class="form-section-title">Course & Schedule</h5>
                                <p class="form-section-subtitle">Select course and time details</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Course <span class="text-danger">*</span></label>
                                <select name="course_id" class="form-select @error('course_id') is-invalid @enderror" id="courseSelect" required>
                                    <option value="">Select Course (Choose Programme First)</option>
                                    @foreach($courses ?? [] as $course)
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

                        <div class="mb-3">
                            <label class="form-label">Day of Week <span class="text-danger">*</span></label>
                            <div class="day-selector">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <label class="day-btn {{ old('day_of_week') == $day ? 'active' : '' }}" data-day="{{ $day }}">
                                        <input type="radio" name="day_of_week" value="{{ $day }}" class="d-none" {{ old('day_of_week') == $day ? 'checked' : '' }} required>
                                        {{ substr($day, 0, 3) }}
                                    </label>
                                @endforeach
                            </div>
                            @error('day_of_week')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" 
                                    id="startTime" value="{{ old('start_time', '08:00') }}" required>
                                @error('start_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="quick-time-btns">
                                    <button type="button" class="quick-time-btn" data-time="07:00">7 AM</button>
                                    <button type="button" class="quick-time-btn" data-time="08:00">8 AM</button>
                                    <button type="button" class="quick-time-btn" data-time="09:00">9 AM</button>
                                    <button type="button" class="quick-time-btn" data-time="10:00">10 AM</button>
                                    <button type="button" class="quick-time-btn" data-time="11:00">11 AM</button>
                                    <button type="button" class="quick-time-btn" data-time="14:00">2 PM</button>
                                    <button type="button" class="quick-time-btn" data-time="15:00">3 PM</button>
                                    <button type="button" class="quick-time-btn" data-time="16:00">4 PM</button>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" 
                                    id="endTime" value="{{ old('end_time', '10:00') }}" required>
                                @error('end_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="quick-time-btns">
                                    <button type="button" class="quick-time-btn end-time" data-duration="1">+1 Hr</button>
                                    <button type="button" class="quick-time-btn end-time" data-duration="2">+2 Hrs</button>
                                    <button type="button" class="quick-time-btn end-time" data-duration="3">+3 Hrs</button>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Session Type <span class="text-danger">*</span></label>
                            <div class="session-type-selector">
                                <label class="session-type-btn lecture {{ old('session_type', 'lecture') == 'lecture' ? 'active' : '' }}" data-type="lecture">
                                    <input type="radio" name="session_type" value="lecture" class="d-none" {{ old('session_type', 'lecture') == 'lecture' ? 'checked' : '' }} required>
                                    <i class="bx bx-chalkboard"></i>
                                    <span>Lecture</span>
                                </label>
                                <label class="session-type-btn tutorial {{ old('session_type') == 'tutorial' ? 'active' : '' }}" data-type="tutorial">
                                    <input type="radio" name="session_type" value="tutorial" class="d-none" {{ old('session_type') == 'tutorial' ? 'checked' : '' }}>
                                    <i class="bx bx-group"></i>
                                    <span>Tutorial</span>
                                </label>
                                <label class="session-type-btn practical {{ old('session_type') == 'practical' ? 'active' : '' }}" data-type="practical">
                                    <input type="radio" name="session_type" value="practical" class="d-none" {{ old('session_type') == 'practical' ? 'checked' : '' }}>
                                    <i class="bx bx-wrench"></i>
                                    <span>Practical</span>
                                </label>
                                <label class="session-type-btn lab {{ old('session_type') == 'lab' ? 'active' : '' }}" data-type="lab">
                                    <input type="radio" name="session_type" value="lab" class="d-none" {{ old('session_type') == 'lab' ? 'checked' : '' }}>
                                    <i class="bx bx-test-tube"></i>
                                    <span>Lab</span>
                                </label>
                                <label class="session-type-btn seminar {{ old('session_type') == 'seminar' ? 'active' : '' }}" data-type="seminar">
                                    <input type="radio" name="session_type" value="seminar" class="d-none" {{ old('session_type') == 'seminar' ? 'checked' : '' }}>
                                    <i class="bx bx-conversation"></i>
                                    <span>Seminar</span>
                                </label>
                                <label class="session-type-btn workshop {{ old('session_type') == 'workshop' ? 'active' : '' }}" data-type="workshop">
                                    <input type="radio" name="session_type" value="workshop" class="d-none" {{ old('session_type') == 'workshop' ? 'checked' : '' }}>
                                    <i class="bx bx-hard-hat"></i>
                                    <span>Workshop</span>
                                </label>
                                <label class="session-type-btn exam {{ old('session_type') == 'exam' ? 'active' : '' }}" data-type="exam">
                                    <input type="radio" name="session_type" value="exam" class="d-none" {{ old('session_type') == 'exam' ? 'checked' : '' }}>
                                    <i class="bx bx-edit-alt"></i>
                                    <span>Exam</span>
                                </label>
                            </div>
                            @error('session_type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Section 3: Venue & Instructor -->
                    <div class="form-section" id="section3">
                        <div class="form-section-header">
                            <div class="form-section-icon bg-info-subtle text-info">
                                <i class="bx bx-map-pin"></i>
                            </div>
                            <div>
                                <h5 class="form-section-title">Venue & Instructor</h5>
                                <p class="form-section-subtitle">Assign location and teaching staff</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Venue</label>
                                <select name="venue_id" class="form-select @error('venue_id') is-invalid @enderror" id="venueSelect">
                                    <option value="">Select Venue (Optional)</option>
                                    @foreach($venues as $venue)
                                        <option value="{{ $venue->id }}" 
                                            data-capacity="{{ $venue->capacity }}"
                                            data-type="{{ $venue->venue_type ?? '' }}"
                                            {{ old('venue_id') == $venue->id ? 'selected' : '' }}>
                                            {{ $venue->code }} - {{ $venue->name }} (Cap: {{ $venue->capacity }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('venue_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="venueConflict" class="small mt-1"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Instructor/Lecturer</label>
                                <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" id="instructorSelect">
                                    <option value="">Select Instructor (Optional)</option>
                                    @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" {{ old('employee_id') == $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="instructorConflict" class="small mt-1"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remarks/Notes</label>
                            <textarea name="remarks" class="form-control @error('remarks') is-invalid @enderror" 
                                rows="2" placeholder="Any additional notes (e.g., Group A only, Special requirements)">{{ old('remarks') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Floating Submit -->
                    <div class="floating-submit">
                        <a href="{{ route('college.timetables.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-x me-1"></i> Cancel
                        </a>
                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" id="addAnotherBtn">
                                <i class="bx bx-plus-circle me-1"></i> Save & Add Another
                            </button>
                            <button type="submit" class="btn btn-primary btn-lg px-4">
                                <i class="bx bx-save me-1"></i> Save Entry
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Live Preview Card -->
                    <div class="card border-0 shadow-sm sticky-top" style="top: 80px;">
                        <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-show me-2"></i>Entry Preview
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="summary-card">
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-calendar me-1"></i> Academic Year</span>
                                    <span class="summary-value" id="previewAcademicYear">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-bookmark me-1"></i> Semester</span>
                                    <span class="summary-value" id="previewSemester">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-book-reader me-1"></i> Programme</span>
                                    <span class="summary-value" id="previewProgram">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-layer me-1"></i> Level</span>
                                    <span class="summary-value" id="previewLevel">-</span>
                                </div>
                            </div>

                            <hr>

                            <div class="summary-card">
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-book-open me-1"></i> Course</span>
                                    <span class="summary-value" id="previewCourse">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-calendar-event me-1"></i> Day</span>
                                    <span class="summary-value" id="previewDay">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-time me-1"></i> Time</span>
                                    <span class="summary-value" id="previewTime">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-category me-1"></i> Session Type</span>
                                    <span class="summary-value" id="previewSessionType">
                                        <span class="badge bg-primary">Lecture</span>
                                    </span>
                                </div>
                            </div>

                            <hr>

                            <div class="summary-card mb-0">
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-map-pin me-1"></i> Venue</span>
                                    <span class="summary-value" id="previewVenue">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="summary-label"><i class="bx bx-user me-1"></i> Instructor</span>
                                    <span class="summary-value" id="previewInstructor">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-light">
                            <div class="d-flex align-items-center">
                                <i class="bx bx-info-circle text-info me-2"></i>
                                <small class="text-muted">Preview updates as you fill the form</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Tips -->
                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-header border-bottom">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-bulb text-warning me-2"></i>Quick Tips
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-1"></i>
                                    Select existing timetable to add more slots
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-1"></i>
                                    Use quick time buttons for faster entry
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-1"></i>
                                    Conflicts are checked automatically
                                </li>
                                <li>
                                    <i class="bx bx-check-circle text-success me-1"></i>
                                    "Save & Add Another" for bulk entries
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Day selector
    $('.day-btn').on('click', function() {
        $('.day-btn').removeClass('active');
        $(this).addClass('active');
        $(this).find('input').prop('checked', true);
        updatePreview();
    });

    // Session type selector
    $('.session-type-btn').on('click', function() {
        $('.session-type-btn').removeClass('active');
        $(this).addClass('active');
        $(this).find('input').prop('checked', true);
        updatePreview();
        highlightStep(2);
    });

    // Quick time buttons for start time
    $('.quick-time-btn:not(.end-time)').on('click', function() {
        var time = $(this).data('time');
        $('#startTime').val(time);
        // Auto-calculate end time (+2 hours)
        var parts = time.split(':');
        var endHour = parseInt(parts[0]) + 2;
        if (endHour > 23) endHour = 23;
        $('#endTime').val(String(endHour).padStart(2, '0') + ':00');
        updatePreview();
    });

    // Quick time buttons for end time (duration based)
    $('.quick-time-btn.end-time').on('click', function() {
        var duration = parseInt($(this).data('duration'));
        var startTime = $('#startTime').val();
        if (startTime) {
            var parts = startTime.split(':');
            var endHour = parseInt(parts[0]) + duration;
            if (endHour > 23) endHour = 23;
            $('#endTime').val(String(endHour).padStart(2, '0') + ':' + parts[1]);
            updatePreview();
        }
    });

    // Programme change - load courses
    $('#programSelect').on('change', function() {
        var programId = $(this).val();
        var $courseSelect = $('#courseSelect');
        
        // Update year options based on duration
        var duration = $(this).find(':selected').data('duration') || 6;
        $('#levelSelect option').each(function() {
            var level = parseInt($(this).val());
            if (level && level > duration) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
        
        // Load courses for program
        if (programId) {
            $courseSelect.html('<option value="">Loading courses...</option>');
            $.ajax({
                url: '/college/courses/by-program/' + programId,
                type: 'GET',
                success: function(courses) {
                    var options = '<option value="">Select Course</option>';
                    courses.forEach(function(course) {
                        options += '<option value="' + course.id + '">' + 
                            course.code + ' - ' + course.name + 
                            ' (Sem ' + (course.semester || '-') + ')</option>';
                    });
                    $courseSelect.html(options);
                },
                error: function() {
                    $courseSelect.html('<option value="">Failed to load courses</option>');
                }
            });
        } else {
            $courseSelect.html('<option value="">Select Course (Choose Programme First)</option>');
        }
        
        updatePreview();
        highlightStep(1);
    });

    // Existing timetable selection
    $('#existingTimetable').on('change', function() {
        var $selected = $(this).find(':selected');
        if ($selected.val()) {
            // Hide new timetable fields and auto-fill
            $('#newTimetableFields').slideUp(300);
            $('#academicYear').val($selected.data('year')).trigger('change');
            $('#semester').val($selected.data('semester')).trigger('change');
            $('#programSelect').val($selected.data('program')).trigger('change');
            $('#levelSelect').val($selected.data('level'));
        } else {
            // Show new timetable fields
            $('#newTimetableFields').slideDown(300);
        }
        updatePreview();
    });

    // Check venue conflicts
    function checkVenueConflict() {
        var venueId = $('#venueSelect').val();
        var day = $('input[name="day_of_week"]:checked').val();
        var startTime = $('#startTime').val();
        var endTime = $('#endTime').val();

        if (venueId && day && startTime && endTime) {
            $.ajax({
                url: '/college/timetables/check-venue',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    venue_id: venueId,
                    day_of_week: day,
                    start_time: startTime,
                    end_time: endTime
                },
                success: function(response) {
                    if (response.available) {
                        $('#venueConflict').html('<span class="text-success"><i class="bx bx-check-circle"></i> Available</span>');
                    } else {
                        $('#venueConflict').html('<span class="text-danger"><i class="bx bx-x-circle"></i> Conflict: ' + (response.conflict || 'Venue occupied') + '</span>');
                    }
                }
            });
        }
    }

    // Check instructor conflicts  
    function checkInstructorConflict() {
        var instructorId = $('#instructorSelect').val();
        var day = $('input[name="day_of_week"]:checked').val();
        var startTime = $('#startTime').val();
        var endTime = $('#endTime').val();

        if (instructorId && day && startTime && endTime) {
            $.ajax({
                url: '/college/timetables/check-instructor',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    instructor_id: instructorId,
                    day_of_week: day,
                    start_time: startTime,
                    end_time: endTime
                },
                success: function(response) {
                    if (response.available) {
                        $('#instructorConflict').html('<span class="text-success"><i class="bx bx-check-circle"></i> Available</span>');
                    } else {
                        $('#instructorConflict').html('<span class="text-danger"><i class="bx bx-x-circle"></i> Conflict</span>');
                    }
                }
            });
        }
    }

    // Trigger conflict checks
    $('#venueSelect, #startTime, #endTime').on('change', function() {
        checkVenueConflict();
        updatePreview();
    });

    $('#instructorSelect').on('change', function() {
        checkInstructorConflict();
        updatePreview();
    });

    $('input[name="day_of_week"]').on('change', function() {
        checkVenueConflict();
        checkInstructorConflict();
    });

    // Update preview panel
    function updatePreview() {
        var ayText = $('#academicYear option:selected').text();
        $('#previewAcademicYear').text(ayText && ayText !== 'Select Academic Year' ? ayText : '-');
        
        var semText = $('#semester option:selected').text();
        $('#previewSemester').text(semText && semText !== 'Select Semester' ? semText : '-');
        
        var program = $('#programSelect option:selected').text();
        $('#previewProgram').text(program && program !== 'Select Programme' ? program.split(' - ')[0] : '-');
        
        var level = $('#levelSelect').val();
        $('#previewLevel').text(level ? 'Level ' + level : '-');
        
        var course = $('#courseSelect option:selected').text();
        $('#previewCourse').text(course && !course.includes('Select') && !course.includes('Loading') ? course.split(' - ')[0] : '-');
        
        var day = $('input[name="day_of_week"]:checked').val();
        $('#previewDay').text(day || '-');
        
        var startTime = $('#startTime').val();
        var endTime = $('#endTime').val();
        if (startTime && endTime) {
            $('#previewTime').text(formatTime(startTime) + ' - ' + formatTime(endTime));
        } else {
            $('#previewTime').text('-');
        }
        
        var sessionType = $('input[name="session_type"]:checked').val();
        if (sessionType) {
            var colors = {
                'lecture': 'primary', 'tutorial': 'success', 'practical': 'warning',
                'lab': 'info', 'seminar': 'secondary', 'workshop': 'dark', 'exam': 'danger'
            };
            $('#previewSessionType').html('<span class="badge bg-' + colors[sessionType] + '">' + 
                sessionType.charAt(0).toUpperCase() + sessionType.slice(1) + '</span>');
        }
        
        var venue = $('#venueSelect option:selected').text();
        $('#previewVenue').text(venue && !venue.includes('Select') ? venue.split(' - ')[0] : '-');
        
        var instructor = $('#instructorSelect option:selected').text();
        $('#previewInstructor').text(instructor && !instructor.includes('Select') ? instructor.split(' (')[0] : '-');
    }

    function formatTime(time) {
        if (!time) return '';
        var parts = time.split(':');
        var hours = parseInt(parts[0]);
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        return hours + ':' + parts[1] + ' ' + ampm;
    }

    function highlightStep(step) {
        $('.wizard-step').removeClass('active completed');
        for (var i = 1; i < step; i++) {
            $('#step' + i).addClass('completed');
        }
        $('#step' + step).addClass('active');
    }

    // Trigger initial updates
    $('#academicYear, #semester, #levelSelect, #courseSelect').on('change', updatePreview);
    
    // Monitor form sections for step highlighting
    $('#section1 input, #section1 select').on('focus', function() { highlightStep(1); });
    $('#section2 input, #section2 select').on('focus', function() { highlightStep(2); });
    $('#section3 input, #section3 select, #section3 textarea').on('focus', function() { highlightStep(3); });

    // Save & Add Another
    $('#addAnotherBtn').on('click', function() {
        $('<input>').attr({
            type: 'hidden',
            name: 'add_another',
            value: '1'
        }).appendTo('#timetableEntryForm');
        $('#timetableEntryForm').submit();
    });

    // Initialize preview
    updatePreview();
    
    // If program is pre-selected, trigger change
    if ($('#programSelect').val()) {
        $('#programSelect').trigger('change');
    }
});
</script>
@endpush
