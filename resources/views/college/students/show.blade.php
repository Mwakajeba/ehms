@extends('layouts.main')

@section('title', 'Student Profile - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Students', 'url' => route('college.students.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Profile', 'url' => '#', 'icon' => 'bx bx-user-circle']
        ]" />

        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                @if($student->student_photo)
                                    <img src="{{ asset('storage/' . $student->student_photo) }}"
                                         alt="Student Photo"
                                         class="rounded-circle border"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border"
                                         style="width: 100px; height: 100px;">
                                        <i class="bx bx-user text-muted" style="font-size: 2.5rem;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h2 class="mb-1 text-primary fw-bold">{{ $student->first_name }} {{ $student->last_name }}</h2>
                                        <p class="mb-2 text-muted">
                                            <i class="bx bx-id-card me-1"></i>
                                            Student ID: <strong>{{ $student->student_number }}</strong>
                                        </p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            @php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'inactive' => 'secondary',
                                                    'graduated' => 'info',
                                                    'suspended' => 'warning',
                                                    'transferred' => 'primary'
                                                ];
                                                $statusColor = $statusColors[$student->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }} fs-6 px-3 py-2">
                                                <i class="bx bx-circle me-1"></i>{{ ucfirst($student->status) }}
                                            </span>
                                            @if($student->program)
                                                <span class="badge bg-light text-dark fs-6 px-3 py-2">
                                                    <i class="bx bx-graduation me-1"></i>{{ $student->program->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="{{ route('college.students.edit', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}"
                                           class="btn btn-primary">
                                            <i class="bx bx-edit me-1"></i>Edit Profile
                                        </a>
                                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                                            <i class="bx bx-plus-circle me-1"></i>Add Courses
                                        </button>
                                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#changeProgramModal">
                                            <i class="bx bx-transfer me-1"></i>Change Program
                                        </button>
                                        <a href="{{ route('college.students.index') }}"
                                           class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i>Back to List
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Personal Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bx bx-user me-2"></i>Personal Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">First Name</label>
                                    <p class="mb-0 fw-semibold">{{ $student->first_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Last Name</label>
                                    <p class="mb-0 fw-semibold">{{ $student->last_name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Email Address</label>
                                    <p class="mb-0">
                                        <a href="mailto:{{ $student->email }}" class="text-decoration-none">
                                            {{ $student->email }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Phone Number</label>
                                    <p class="mb-0">
                                        @if($student->phone)
                                            <a href="tel:{{ $student->phone }}" class="text-decoration-none">
                                                {{ $student->phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Date of Birth</label>
                                    <p class="mb-0">
                                        @if($student->date_of_birth)
                                            {{ $student->date_of_birth->format('F j, Y') }}
                                            <small class="text-muted">({{ $student->date_of_birth->age }} years old)</small>
                                        @else
                                            <span class="text-muted">Not provided</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Gender</label>
                                    <p class="mb-0">{{ ucfirst($student->gender) }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Nationality</label>
                                    <p class="mb-0">{{ $student->nationality ?: 'Not specified' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">ID/Passport Number</label>
                                    <p class="mb-0">{{ $student->id_number ?: 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parent/Guardian Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-user-check me-2"></i>Parent/Guardian Information
                        </h5>
                        <a href="{{ route('college.students.assign-parents', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-user-plus me-1"></i>Manage Parents
                        </a>
                    </div>
                    <div class="card-body">
                        @if($student->parents && $student->parents->count() > 0)
                            <div class="row g-3">
                                @foreach($student->parents as $parent)
                                    <div class="col-12">
                                        <div class="parent-card border rounded p-3 mb-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0 text-primary">
                                                    <i class="bx bx-user me-2"></i>{{ $parent->name }}
                                                </h6>
                                                <span class="badge bg-info">{{ ucfirst($parent->pivot->relationship ?? 'Parent') }}</span>
                                            </div>
                                            <div class="row g-2">
                                                @if($parent->phone)
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Phone</small>
                                                        <a href="tel:{{ $parent->phone }}" class="text-decoration-none">
                                                            {{ $parent->phone }}
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($parent->alt_phone)
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Alt Phone</small>
                                                        <a href="tel:{{ $parent->alt_phone }}" class="text-decoration-none">
                                                            {{ $parent->alt_phone }}
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($parent->email)
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Email</small>
                                                        <a href="mailto:{{ $parent->email }}" class="text-decoration-none">
                                                            {{ $parent->email }}
                                                        </a>
                                                    </div>
                                                @endif
                                                @if($parent->occupation)
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Occupation</small>
                                                        <span>{{ $parent->occupation }}</span>
                                                    </div>
                                                @endif
                                                @if($parent->address)
                                                    <div class="col-12">
                                                        <small class="text-muted d-block">Address</small>
                                                        <span>{{ $parent->address }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bx bx-user-x text-muted" style="font-size: 3rem;"></i>
                                <h6 class="text-muted mt-2">No Parents/Guardians Assigned</h6>
                                <p class="text-muted small mb-3">Click "Manage Parents" to assign parent or guardian information for this student.</p>
                                <a href="{{ route('college.students.assign-parents', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}"
                                   class="btn btn-primary">
                                    <i class="bx bx-user-plus me-1"></i>Assign Parents
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bx bx-book me-2"></i>Academic Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Student Number</label>
                                    <p class="mb-0">
                                        <span class="badge bg-info">{{ $student->student_number }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Program</label>
                                    <p class="mb-0 fw-semibold">{{ $student->program ? $student->program->name : 'Not assigned' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Department</label>
                                    <p class="mb-0">{{ $student->program && $student->program->department ? $student->program->department->name : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Enrollment Year</label>
                                    <p class="mb-0 fw-semibold">{{ $enrollmentYearName ?: 'Not set' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Expected Graduation Year</label>
                                    <p class="mb-0">{{ $student->graduation_year ?: 'Not set' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Admission Date</label>
                                    <p class="mb-0">
                                        @if($student->admission_date)
                                            {{ $student->admission_date->format('F j, Y') }}
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Admission Level</label>
                                    <p class="mb-0 fw-semibold">
                                        @php
                                            $admissionLevels = [
                                                1 => 'First Year',
                                                2 => 'Second Year',
                                                3 => 'Third Year',
                                                4 => 'Fourth Year'
                                            ];
                                        @endphp
                                        {{ $admissionLevels[$student->admission_level] ?? 'Unknown' }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Current Year of Study</label>
                                    <p class="mb-0 fw-semibold text-primary">{{ $yearOfStudy }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Date Joined</label>
                                    <p class="mb-0">{{ $student->created_at->format('F j, Y') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bx bx-map me-2"></i>Address Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Permanent Address</label>
                                    <p class="mb-0">{{ $student->permanent_address }}</p>
                                </div>
                            </div>
                            @if($student->current_address)
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Current/Mailing Address</label>
                                    <p class="mb-0">{{ $student->current_address }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Previous Education -->
                @if($student->previous_school || $student->qualification || $student->grade_score)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bx bx-graduation me-2"></i>Previous Education
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if($student->previous_school)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Previous School/College</label>
                                    <p class="mb-0">{{ $student->previous_school }}</p>
                                </div>
                            </div>
                            @endif
                            @if($student->qualification)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Qualification Obtained</label>
                                    <p class="mb-0">{{ $student->qualification }}</p>
                                </div>
                            </div>
                            @endif
                            @if($student->grade_score)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Grade/Score</label>
                                    <p class="mb-0 fw-semibold">{{ $student->grade_score }}</p>
                                </div>
                            </div>
                            @endif
                            @if($student->completion_year)
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small mb-1">Year of Completion</label>
                                    <p class="mb-0">{{ $student->completion_year }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bx bx-cog me-2 text-primary"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('college.students.edit', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}"
                               class="btn btn-primary">
                                <i class="bx bx-edit me-2"></i>Edit Profile
                            </a>
                            <a href="mailto:{{ $student->email }}"
                               class="btn btn-outline-info">
                                <i class="bx bx-envelope me-2"></i>Send Email
                            </a>
                            @if($student->phone)
                            <a href="tel:{{ $student->phone }}"
                               class="btn btn-outline-success">
                                <i class="bx bx-phone me-2"></i>Call Student
                            </a>
                            @endif
                            <button type="button" class="btn btn-outline-danger"
                                    onclick="confirmDelete()">
                                <i class="bx bx-trash me-2"></i>Delete Student
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                @if($student->emergency_contact_name || $student->emergency_contact_phone)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bx bx-phone me-2"></i>Emergency Contact
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($student->emergency_contact_name)
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Contact Name</label>
                            <p class="mb-0 fw-semibold">{{ $student->emergency_contact_name }}</p>
                        </div>
                        @endif
                        @if($student->emergency_contact_phone)
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Contact Phone</label>
                            <p class="mb-0">
                                <a href="tel:{{ $student->emergency_contact_phone }}"
                                   class="text-decoration-none">
                                    {{ $student->emergency_contact_phone }}
                                </a>
                            </p>
                        </div>
                        @endif
                        @if($student->emergency_contact_relationship)
                        <div class="mb-0">
                            <label class="text-muted small mb-1">Relationship</label>
                            <p class="mb-0">{{ ucfirst($student->emergency_contact_relationship) }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Course History - All Time -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bx bx-book me-2"></i>Course History (All Time)
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $allCourseEnrollments = $student->courseEnrollments->sortByDesc('enrolled_date');
                            $groupedByStatus = $allCourseEnrollments->groupBy('status');
                        @endphp
                        
                        @if($allCourseEnrollments->count() > 0)
                            <!-- Currently Enrolled Courses -->
                            @if($groupedByStatus->has('enrolled'))
                                <div class="mb-4">
                                    <h6 class="text-success mb-3">
                                        <i class="bx bx-check-circle me-1"></i>Currently Enrolled 
                                        <span class="badge bg-success">{{ $groupedByStatus->get('enrolled')->count() }}</span>
                                    </h6>
                                    <div class="row g-3">
                                        @foreach($groupedByStatus->get('enrolled') as $enrollment)
                                            <div class="col-md-6">
                                                <div class="p-3 border border-success rounded bg-light">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0 text-primary">
                                                            <i class="bx bx-book-content me-1"></i>
                                                            {{ $enrollment->course->name ?? 'Unknown Course' }}
                                                        </h6>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-info btn-sm view-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}"
                                                                data-course-name="{{ $enrollment->course->name ?? 'Unknown' }}"
                                                                data-course-code="{{ $enrollment->course->code ?? 'N/A' }}"
                                                                data-credit-hours="{{ $enrollment->course->credit_hours ?? 'N/A' }}"
                                                                data-status="enrolled"
                                                                data-grade="{{ $enrollment->grade ?? 'N/A' }}"
                                                                data-academic-year="{{ $enrollment->academicYear->name ?? 'N/A' }}"
                                                                data-semester="{{ $enrollment->semester->name ?? 'N/A' }}"
                                                                data-enrolled-date="{{ $enrollment->enrolled_date ? $enrollment->enrolled_date->format('M d, Y') : 'N/A' }}"
                                                                data-course-id="{{ $enrollment->course_id }}"
                                                                data-bs-toggle="tooltip" title="View Details">
                                                                <i class="bx bx-show"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm edit-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}"
                                                                data-course-name="{{ $enrollment->course->name ?? 'Unknown' }}"
                                                                data-course-code="{{ $enrollment->course->code ?? 'N/A' }}"
                                                                data-status="enrolled"
                                                                data-grade="{{ $enrollment->grade ?? '' }}"
                                                                data-bs-toggle="tooltip" title="Edit Status">
                                                                <i class="bx bx-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm delete-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}" 
                                                                data-name="{{ $enrollment->course->name ?? 'this enrollment' }}" 
                                                                data-bs-toggle="tooltip" title="Delete">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <p class="mb-1 small text-muted">
                                                        <strong>Code:</strong> {{ $enrollment->course->code ?? 'N/A' }}
                                                    </p>
                                                    <p class="mb-1 small text-muted">
                                                        <strong>Credits:</strong> {{ $enrollment->course->credit_hours ?? 'N/A' }}
                                                    </p>
                                                    @if($enrollment->academicYear)
                                                        <p class="mb-1 small text-muted">
                                                            <strong>Academic Year:</strong> {{ $enrollment->academicYear->name ?? 'N/A' }}
                                                        </p>
                                                    @endif
                                                    @if($enrollment->semester)
                                                        <p class="mb-1 small text-muted">
                                                            <strong>Semester:</strong> {{ $enrollment->semester->name ?? 'N/A' }}
                                                        </p>
                                                    @endif
                                                    <p class="mb-0 small">
                                                        <span class="badge bg-success">Enrolled</span>
                                                        @if($enrollment->enrolled_date)
                                                            <span class="text-muted ms-2">{{ $enrollment->enrolled_date->format('M d, Y') }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Completed Courses -->
                            @if($groupedByStatus->has('completed'))
                                <div class="mb-4">
                                    <h6 class="text-info mb-3">
                                        <i class="bx bx-check-double me-1"></i>Completed 
                                        <span class="badge bg-info">{{ $groupedByStatus->get('completed')->count() }}</span>
                                    </h6>
                                    <div class="row g-3">
                                        @foreach($groupedByStatus->get('completed') as $enrollment)
                                            <div class="col-md-6">
                                                <div class="p-3 border border-info rounded">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0 text-dark">
                                                            <i class="bx bx-book-content me-1"></i>
                                                            {{ $enrollment->course->name ?? 'Unknown Course' }}
                                                        </h6>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-info btn-sm view-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}"
                                                                data-course-name="{{ $enrollment->course->name ?? 'Unknown' }}"
                                                                data-course-code="{{ $enrollment->course->code ?? 'N/A' }}"
                                                                data-credit-hours="{{ $enrollment->course->credit_hours ?? 'N/A' }}"
                                                                data-status="completed"
                                                                data-grade="{{ $enrollment->grade ?? 'N/A' }}"
                                                                data-academic-year="{{ $enrollment->academicYear->name ?? 'N/A' }}"
                                                                data-semester="{{ $enrollment->semester->name ?? 'N/A' }}"
                                                                data-enrolled-date="{{ $enrollment->enrolled_date ? $enrollment->enrolled_date->format('M d, Y') : 'N/A' }}"
                                                                data-course-id="{{ $enrollment->course_id }}"
                                                                data-bs-toggle="tooltip" title="View Details">
                                                                <i class="bx bx-show"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm edit-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}"
                                                                data-course-name="{{ $enrollment->course->name ?? 'Unknown' }}"
                                                                data-course-code="{{ $enrollment->course->code ?? 'N/A' }}"
                                                                data-status="completed"
                                                                data-grade="{{ $enrollment->grade ?? '' }}"
                                                                data-bs-toggle="tooltip" title="Edit Status">
                                                                <i class="bx bx-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm delete-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}" 
                                                                data-name="{{ $enrollment->course->name ?? 'this enrollment' }}" 
                                                                data-bs-toggle="tooltip" title="Delete">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <p class="mb-1 small text-muted">
                                                        <strong>Code:</strong> {{ $enrollment->course->code ?? 'N/A' }}
                                                    </p>
                                                    <p class="mb-1 small text-muted">
                                                        <strong>Credits:</strong> {{ $enrollment->course->credit_hours ?? 'N/A' }}
                                                    </p>
                                                    @if($enrollment->grade)
                                                        <p class="mb-1 small text-muted">
                                                            <strong>Grade:</strong> <span class="badge bg-primary">{{ $enrollment->grade }}</span>
                                                        </p>
                                                    @endif
                                                    @if($enrollment->academicYear)
                                                        <p class="mb-1 small text-muted">
                                                            <strong>Academic Year:</strong> {{ $enrollment->academicYear->name ?? 'N/A' }}
                                                        </p>
                                                    @endif
                                                    @if($enrollment->semester)
                                                        <p class="mb-1 small text-muted">
                                                            <strong>Semester:</strong> {{ $enrollment->semester->name ?? 'N/A' }}
                                                        </p>
                                                    @endif
                                                    <p class="mb-0 small">
                                                        <span class="badge bg-info">Completed</span>
                                                        @if($enrollment->enrolled_date)
                                                            <span class="text-muted ms-2">{{ $enrollment->enrolled_date->format('M d, Y') }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Dropped Courses -->
                            @if($groupedByStatus->has('dropped'))
                                <div class="mb-4">
                                    <h6 class="text-warning mb-3">
                                        <i class="bx bx-x-circle me-1"></i>Dropped 
                                        <span class="badge bg-warning">{{ $groupedByStatus->get('dropped')->count() }}</span>
                                    </h6>
                                    <div class="row g-3">
                                        @foreach($groupedByStatus->get('dropped') as $enrollment)
                                            <div class="col-md-6">
                                                <div class="p-3 border border-warning rounded">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0 text-dark">
                                                            <i class="bx bx-book-content me-1"></i>
                                                            {{ $enrollment->course->name ?? 'Unknown Course' }}
                                                        </h6>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-info btn-sm view-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}"
                                                                data-course-name="{{ $enrollment->course->name ?? 'Unknown' }}"
                                                                data-course-code="{{ $enrollment->course->code ?? 'N/A' }}"
                                                                data-credit-hours="{{ $enrollment->course->credit_hours ?? 'N/A' }}"
                                                                data-status="dropped"
                                                                data-grade="{{ $enrollment->grade ?? 'N/A' }}"
                                                                data-academic-year="{{ $enrollment->academicYear->name ?? 'N/A' }}"
                                                                data-semester="{{ $enrollment->semester->name ?? 'N/A' }}"
                                                                data-enrolled-date="{{ $enrollment->enrolled_date ? $enrollment->enrolled_date->format('M d, Y') : 'N/A' }}"
                                                                data-course-id="{{ $enrollment->course_id }}"
                                                                data-bs-toggle="tooltip" title="View Details">
                                                                <i class="bx bx-show"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm edit-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}"
                                                                data-course-name="{{ $enrollment->course->name ?? 'Unknown' }}"
                                                                data-course-code="{{ $enrollment->course->code ?? 'N/A' }}"
                                                                data-status="dropped"
                                                                data-grade="{{ $enrollment->grade ?? '' }}"
                                                                data-bs-toggle="tooltip" title="Edit Status">
                                                                <i class="bx bx-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm delete-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}" 
                                                                data-name="{{ $enrollment->course->name ?? 'this enrollment' }}" 
                                                                data-bs-toggle="tooltip" title="Delete">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <p class="mb-1 small text-muted">
                                                        <strong>Code:</strong> {{ $enrollment->course->code ?? 'N/A' }}
                                                    </p>
                                                    @if($enrollment->academicYear)
                                                        <p class="mb-1 small text-muted">
                                                            <strong>Academic Year:</strong> {{ $enrollment->academicYear->name ?? 'N/A' }}
                                                        </p>
                                                    @endif
                                                    <p class="mb-0 small">
                                                        <span class="badge bg-warning text-dark">Dropped</span>
                                                        @if($enrollment->unassigned_date)
                                                            <span class="text-muted ms-2">{{ $enrollment->unassigned_date->format('M d, Y') }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Failed Courses -->
                            @if($groupedByStatus->has('failed'))
                                <div class="mb-0">
                                    <h6 class="text-danger mb-3">
                                        <i class="bx bx-error-circle me-1"></i>Failed 
                                        <span class="badge bg-danger">{{ $groupedByStatus->get('failed')->count() }}</span>
                                    </h6>
                                    <div class="row g-3">
                                        @foreach($groupedByStatus->get('failed') as $enrollment)
                                            <div class="col-md-6">
                                                <div class="p-3 border border-danger rounded">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0 text-dark">
                                                            <i class="bx bx-book-content me-1"></i>
                                                            {{ $enrollment->course->name ?? 'Unknown Course' }}
                                                        </h6>
                                                        <div class="btn-group btn-group-sm">
                                                            <button type="button" class="btn btn-info btn-sm view-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}"
                                                                data-course-name="{{ $enrollment->course->name ?? 'Unknown' }}"
                                                                data-course-code="{{ $enrollment->course->code ?? 'N/A' }}"
                                                                data-credit-hours="{{ $enrollment->course->credit_hours ?? 'N/A' }}"
                                                                data-status="failed"
                                                                data-grade="{{ $enrollment->grade ?? 'N/A' }}"
                                                                data-academic-year="{{ $enrollment->academicYear->name ?? 'N/A' }}"
                                                                data-semester="{{ $enrollment->semester->name ?? 'N/A' }}"
                                                                data-enrolled-date="{{ $enrollment->enrolled_date ? $enrollment->enrolled_date->format('M d, Y') : 'N/A' }}"
                                                                data-course-id="{{ $enrollment->course_id }}"
                                                                data-bs-toggle="tooltip" title="View Details">
                                                                <i class="bx bx-show"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-warning btn-sm edit-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}"
                                                                data-course-name="{{ $enrollment->course->name ?? 'Unknown' }}"
                                                                data-course-code="{{ $enrollment->course->code ?? 'N/A' }}"
                                                                data-status="failed"
                                                                data-grade="{{ $enrollment->grade ?? '' }}"
                                                                data-bs-toggle="tooltip" title="Edit Status">
                                                                <i class="bx bx-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-danger btn-sm delete-enrollment-btn" 
                                                                data-id="{{ $enrollment->id }}" 
                                                                data-name="{{ $enrollment->course->name ?? 'this enrollment' }}" 
                                                                data-bs-toggle="tooltip" title="Delete">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <p class="mb-1 small text-muted">
                                                        <strong>Code:</strong> {{ $enrollment->course->code ?? 'N/A' }}
                                                    </p>
                                                    @if($enrollment->grade)
                                                        <p class="mb-1 small text-muted">
                                                            <strong>Grade:</strong> <span class="badge bg-danger">{{ $enrollment->grade }}</span>
                                                        </p>
                                                    @endif
                                                    @if($enrollment->academicYear)
                                                        <p class="mb-1 small text-muted">
                                                            <strong>Academic Year:</strong> {{ $enrollment->academicYear->name ?? 'N/A' }}
                                                        </p>
                                                    @endif
                                                    <p class="mb-0 small">
                                                        <span class="badge bg-danger">Failed</span>
                                                        @if($enrollment->enrolled_date)
                                                            <span class="text-muted ms-2">{{ $enrollment->enrolled_date->format('M d, Y') }}</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Summary Statistics -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="mb-3">Course Summary</h6>
                                <div class="row text-center">
                                    <div class="col-3">
                                        <div class="p-2">
                                            <h4 class="mb-0 text-primary">{{ $allCourseEnrollments->count() }}</h4>
                                            <small class="text-muted">Total Courses</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2">
                                            <h4 class="mb-0 text-success">{{ $groupedByStatus->has('enrolled') ? $groupedByStatus->get('enrolled')->count() : 0 }}</h4>
                                            <small class="text-muted">Enrolled</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2">
                                            <h4 class="mb-0 text-info">{{ $groupedByStatus->has('completed') ? $groupedByStatus->get('completed')->count() : 0 }}</h4>
                                            <small class="text-muted">Completed</small>
                                        </div>
                                    </div>
                                    <div class="col-3">
                                        <div class="p-2">
                                            <h4 class="mb-0 text-warning">{{ $groupedByStatus->has('dropped') ? $groupedByStatus->get('dropped')->count() : 0 }}</h4>
                                            <small class="text-muted">Dropped</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bx bx-book text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-0">No course history available</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Current Program Details -->
                @if($student->program)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bx bx-graduation me-2"></i>Current Program
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Program Name</label>
                            <p class="mb-0 fw-semibold">{{ $student->program->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Department</label>
                            <p class="mb-0">{{ $student->program->department->name ?? 'N/A' }}</p>
                        </div>
                        @if($student->program->level)
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Level</label>
                            <p class="mb-0">{{ $student->program->level }}</p>
                        </div>
                        @endif
                        @if($student->program->duration)
                        <div class="mb-0">
                            <label class="text-muted small mb-1">Duration</label>
                            <p class="mb-0">{{ $student->program->duration }} years</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Account Information -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bx bx-info-circle me-2 text-primary"></i>Account Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Profile Created</label>
                            <p class="mb-0">{{ $student->created_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small mb-1">Last Updated</label>
                            <p class="mb-0">{{ $student->updated_at->format('F j, Y \a\t g:i A') }}</p>
                        </div>
                        <div class="mb-0">
                            <label class="text-muted small mb-1">Record ID</label>
                            <p class="mb-0">
                                <code class="small">{{ $student->id }}</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-error me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to delete the student profile for <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>?</p>
                <div class="alert alert-warning">
                    <i class="bx bx-warning me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. All associated academic records, grades, and enrollment data will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <form action="{{ route('college.students.destroy', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i>Delete Student
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Course Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addCourseModalLabel">
                    <i class="bx bx-plus-circle me-2"></i>Add Courses for {{ $student->first_name }} {{ $student->last_name }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('college.students.add-courses', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        Select additional courses to enroll this student. Already enrolled courses will be shown as disabled.
                    </div>

                    <div class="mb-3">
                        <label for="academic_year_id" class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                        <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                            <option value="">Select Academic Year</option>
                            @php
                                $academicYears = \App\Models\College\AcademicYear::where('status', 'active')->orderBy('name', 'desc')->get();
                            @endphp
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="semester_id" class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" id="semester_id" name="semester_id" required>
                            <option value="">Select Semester</option>
                            @php
                                $semesters = \App\Models\College\Semester::where('status', 'active')->get();
                            @endphp
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="add_courses" class="form-label fw-bold">Select Courses <span class="text-danger">*</span></label>
                        <select class="form-select" id="add_courses" name="courses[]" multiple required>
                            @php
                                $allCourses = \App\Models\College\Course::where('status', 'active')->orderBy('name')->get();
                                $enrolledCourseIds = $student->courseEnrollments()->where('status', 'enrolled')->pluck('course_id')->toArray();
                            @endphp
                            @foreach($allCourses as $course)
                                <option value="{{ $course->id }}" {{ in_array($course->id, $enrolledCourseIds) ? 'disabled' : '' }}>
                                    {{ $course->name }} ({{ $course->code }}) {{ in_array($course->id, $enrolledCourseIds) ? '- Already Enrolled' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl (Windows) or Cmd (Mac) to select multiple courses</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check me-1"></i>Add Courses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Change Program Modal -->
<div class="modal fade" id="changeProgramModal" tabindex="-1" aria-labelledby="changeProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="changeProgramModalLabel">
                    <i class="bx bx-transfer me-2"></i>Change Program for {{ $student->first_name }} {{ $student->last_name }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('college.students.change-program', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bx bx-error me-2"></i>
                        <strong>Warning:</strong> Changing the program will update the student's current program assignment.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Program</label>
                        <input type="text" class="form-control" value="{{ $student->program->name ?? 'N/A' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="new_program_id" class="form-label fw-bold">New Program <span class="text-danger">*</span></label>
                        <select class="form-select" id="new_program_id" name="program_id" required>
                            <option value="">Select New Program</option>
                            @php
                                $programs = \App\Models\College\Program::where('is_active', true)
                                    ->with('department')
                                    ->orderBy('name')
                                    ->get();
                            @endphp
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ $student->program_id == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }} ({{ $program->department->name ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="change_reason" class="form-label fw-bold">Reason for Change</label>
                        <textarea class="form-control" id="change_reason" name="reason" rows="3" placeholder="Optional: Explain why the program is being changed"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bx bx-check me-1"></i>Change Program
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Enrollment Modal -->
<div class="modal fade" id="editEnrollmentModal" tabindex="-1" aria-labelledby="editEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning py-3">
                <h5 class="modal-title mb-0" id="editEnrollmentModalLabel">
                    <i class="bx bx-edit me-2"></i>Edit Enrollment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editEnrollmentForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body py-3">
                    <input type="hidden" id="edit_enrollment_id" name="enrollment_id">
                    
                    <div class="mb-3 p-3 bg-light rounded border-start border-warning border-3">
                        <small class="text-muted d-block mb-1">Course</small>
                        <span id="edit_course_name" class="fw-semibold text-dark"></span>
                        <span id="edit_course_code" class="badge bg-secondary ms-2"></span>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <label for="edit_status" class="form-label mb-2">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="enrolled">Enrolled</option>
                                <option value="completed">Completed</option>
                                <option value="dropped">Dropped</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="edit_grade" class="form-label mb-2">Grade</label>
                            <input type="text" class="form-control" id="edit_grade" name="grade" placeholder="A, B+, C">
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label for="edit_remarks" class="form-label mb-2">Remarks</label>
                        <textarea class="form-control" id="edit_remarks" name="remarks" rows="2" placeholder="Optional remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer py-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bx bx-save me-1"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Enrollment Modal -->
<div class="modal fade" id="viewEnrollmentModal" tabindex="-1" aria-labelledby="viewEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white py-3">
                <h5 class="modal-title mb-0" id="viewEnrollmentModalLabel">
                    <i class="bx bx-book-content me-2"></i>Enrollment Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div class="p-3 mb-3 bg-info bg-opacity-10 rounded border-start border-info border-3">
                    <span id="view_course_name" class="fw-bold text-primary d-block fs-5"></span>
                    <small class="text-muted">Code: <span id="view_course_code" class="fw-semibold"></span></small>
                </div>
                
                <div class="row g-2">
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">Credits</small>
                            <span id="view_credit_hours" class="fw-bold"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">Status</small>
                            <span id="view_status"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">Grade</small>
                            <span id="view_grade" class="fw-bold"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">Semester</small>
                            <span id="view_semester" class="fw-bold"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">Academic Year</small>
                            <span id="view_academic_year" class="fw-bold"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light rounded text-center">
                            <small class="text-muted d-block mb-1">Enrolled Date</small>
                            <span id="view_enrolled_date" class="fw-bold"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-3 justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="view_course_link" class="btn btn-info">
                    <i class="bx bx-link-external me-1"></i>View Course
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Enrollment Confirmation Modal -->
<div class="modal fade" id="deleteEnrollmentModal" tabindex="-1" aria-labelledby="deleteEnrollmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteEnrollmentModalLabel">
                    <i class="bx bx-trash me-2"></i>Delete Enrollment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <i class="bx bx-error-circle text-danger" style="font-size: 2.5rem;"></i>
                    </div>
                </div>
                <h5 class="mb-2">Are you sure?</h5>
                <p class="text-muted mb-0">You are about to delete the enrollment for:</p>
                <p class="fw-bold text-danger fs-5" id="delete_course_name"></p>
                <p class="small text-muted">This action cannot be undone.</p>
                <input type="hidden" id="delete_enrollment_id">
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteEnrollmentBtn">
                    <i class="bx bx-trash me-1"></i>Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }

    .info-item {
        padding: 0.75rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        border-left: 4px solid #e9ecef;
        transition: all 0.2s ease-in-out;
    }

    .info-item:hover {
        background-color: #e9ecef;
        border-left-color: #0d6efd;
    }

    .badge {
        font-weight: 500;
    }

    .card-header {
        border-bottom: none;
        font-weight: 600;
    }

    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .rounded-circle {
        border: 3px solid #fff !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    a.text-decoration-none:hover {
        text-decoration: underline !important;
    }

    .small {
        font-size: 0.875rem;
    }

    .parent-card {
        background-color: #f8f9fa;
        border-left: 4px solid #0d6efd !important;
        transition: all 0.2s ease-in-out;
    }

    .parent-card:hover {
        background-color: #e9ecef;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    /* Enrollment Action Buttons Hover Effects */
    .btn-group-sm .btn {
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        padding: 0.1rem 0.25rem;
        font-size: 0.65rem;
        line-height: 1;
        min-width: auto;
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-group-sm .btn i {
        font-size: 0.65rem;
        transition: transform 0.2s ease;
    }

    .btn-group-sm .btn:hover {
        transform: translateY(-1px) scale(1.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        z-index: 10;
    }

    .btn-group-sm .btn.btn-info:hover {
        background-color: #0dcaf0;
        border-color: #0dcaf0;
        box-shadow: 0 2px 8px rgba(13, 202, 240, 0.4);
    }

    .btn-group-sm .btn.btn-warning:hover {
        background-color: #ffc107;
        border-color: #ffc107;
        box-shadow: 0 2px 8px rgba(255, 193, 7, 0.4);
    }

    .btn-group-sm .btn.btn-danger:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
    }

    /* Tooltip style for buttons */
    .btn-group-sm .btn[title] {
        cursor: pointer;
    }

    .btn-group-sm .btn:hover i {
        transform: scale(1.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function confirmDelete() {
    $('#deleteModal').modal('show');
}

// Add some interactive features
$(document).ready(function() {
    // Initialize Select2 for course selection in modal
    $('#add_courses').select2({
        placeholder: 'Search and select courses...',
        allowClear: true,
        width: '100%',
        closeOnSelect: false,
        dropdownParent: $('#addCourseModal')
    });

    // Initialize Select2 for program selection in modal
    $('#new_program_id').select2({
        placeholder: 'Search and select program...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#changeProgramModal')
    });

    // Initialize Select2 for academic year and semester
    $('#academic_year_id, #semester_id').select2({
        width: '100%',
        dropdownParent: $('#addCourseModal')
    });

    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // View Enrollment Modal
    $(document).on('click', '.view-enrollment-btn', function() {
        const btn = $(this);
        
        // Populate view modal with data
        $('#view_course_name').text(btn.data('course-name'));
        $('#view_course_code').text(btn.data('course-code'));
        $('#view_credit_hours').text(btn.data('credit-hours'));
        $('#view_grade').text(btn.data('grade') || 'N/A');
        $('#view_academic_year').text(btn.data('academic-year'));
        $('#view_semester').text(btn.data('semester'));
        $('#view_enrolled_date').text(btn.data('enrolled-date'));
        $('#view_course_link').attr('href', '/college/courses/' + btn.data('course-id'));
        
        // Set status badge with appropriate color
        const status = btn.data('status');
        let statusBadge = '';
        switch(status) {
            case 'enrolled':
                statusBadge = '<span class="badge bg-success">Enrolled</span>';
                break;
            case 'completed':
                statusBadge = '<span class="badge bg-info">Completed</span>';
                break;
            case 'dropped':
                statusBadge = '<span class="badge bg-warning text-dark">Dropped</span>';
                break;
            case 'failed':
                statusBadge = '<span class="badge bg-danger">Failed</span>';
                break;
            default:
                statusBadge = '<span class="badge bg-secondary">' + status + '</span>';
        }
        $('#view_status').html(statusBadge);
        
        // Show the modal
        $('#viewEnrollmentModal').modal('show');
    });

    // Edit Enrollment Modal
    $(document).on('click', '.edit-enrollment-btn', function() {
        const btn = $(this);
        
        // Populate edit modal with data
        $('#edit_enrollment_id').val(btn.data('id'));
        $('#edit_course_name').text(btn.data('course-name'));
        $('#edit_course_code').text(btn.data('course-code'));
        $('#edit_status').val(btn.data('status'));
        $('#edit_grade').val(btn.data('grade') || '');
        $('#edit_remarks').val('');
        
        // Show the modal
        $('#editEnrollmentModal').modal('show');
    });

    // Delete Enrollment - Show Modal
    $(document).on('click', '.delete-enrollment-btn', function() {
        const btn = $(this);
        
        // Populate delete modal with data
        $('#delete_enrollment_id').val(btn.data('id'));
        $('#delete_course_name').text(btn.data('name'));
        
        // Show the modal
        $('#deleteEnrollmentModal').modal('show');
    });

    // Confirm Delete Enrollment
    $('#confirmDeleteEnrollmentBtn').on('click', function() {
        const enrollmentId = $('#delete_enrollment_id').val();
        
        $.ajax({
            url: `/college/course-enrollments/${enrollmentId}`,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteEnrollmentModal').modal('hide');
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Enrollment deleted successfully');
                } else {
                    alert(response.message || 'Enrollment deleted successfully');
                }
                // Reload the page to reflect changes
                location.reload();
            },
            error: function(xhr) {
                $('#deleteEnrollmentModal').modal('hide');
                const message = xhr.responseJSON?.message || 'Failed to delete enrollment';
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            }
        });
    });

    // Edit Enrollment Form Submit
    $('#editEnrollmentForm').on('submit', function(e) {
        e.preventDefault();
        
        const enrollmentId = $('#edit_enrollment_id').val();
        const formData = {
            status: $('#edit_status').val(),
            grade: $('#edit_grade').val(),
            remarks: $('#edit_remarks').val(),
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PUT'
        };
        
        $.ajax({
            url: `/college/course-enrollments/${enrollmentId}`,
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#editEnrollmentModal').modal('hide');
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message || 'Enrollment updated successfully');
                } else {
                    alert(response.message || 'Enrollment updated successfully');
                }
                // Reload the page to reflect changes
                location.reload();
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to update enrollment';
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            }
        });
    });
});
</script>
@endpush