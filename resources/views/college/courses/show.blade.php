@extends('layouts.main')

@section('title', $course->name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Courses', 'url' => route('college.courses.index'), 'icon' => 'bx bx-book'],
            ['label' => $course->name, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">COURSE DETAILS</h6>
        <hr />

        <div class="row">
            <!-- Information Sidebar -->
            <div class="col-12 col-lg-4 order-2 order-lg-2">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-cog me-2"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('college.courses.edit', $course->id) }}" class="btn btn-warning btn-sm w-100 mb-2">
                            <i class="bx bx-edit me-1"></i> Edit Course
                        </a>
                        <button type="button" class="btn btn-danger btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bx bx-trash me-1"></i> Delete Course
                        </button>
                        <a href="{{ route('college.courses.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bx bx-arrow-back me-1"></i> Back to List
                        </a>
                    </div>
                </div>

                <!-- Enrollment Statistics -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-stats me-2"></i> Enrollment Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small"><i class="bx bx-user-check text-primary me-2"></i>Enrolled</span>
                            <span class="badge bg-primary">{{ $enrolledStudents->where('status', 'enrolled')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small"><i class="bx bx-check-circle text-success me-2"></i>Completed</span>
                            <span class="badge bg-success">{{ $enrolledStudents->where('status', 'completed')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small"><i class="bx bx-log-out text-warning me-2"></i>Withdrawn</span>
                            <span class="badge bg-warning text-dark">{{ $enrolledStudents->where('status', 'withdrawn')->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small"><i class="bx bx-group text-info me-2"></i>Total</span>
                            <span class="badge bg-info">{{ $enrolledStudents->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-check-shield me-2"></i> Status Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-3">
                            @if($course->status == 'active')
                                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bx bx-check-circle text-success" style="font-size: 40px;"></i>
                                </div>
                                <h5 class="text-success mb-1">Active</h5>
                                <p class="text-muted small mb-0">Course is available for enrollment</p>
                            @else
                                <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="bx bx-x-circle text-danger" style="font-size: 40px;"></i>
                                </div>
                                <h5 class="text-danger mb-1">Inactive</h5>
                                <p class="text-muted small mb-0">Course is not available for enrollment</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Info Card -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i> Quick Info
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="text-muted small mb-1">Course Type</p>
                            @if($course->core_elective == 'Core')
                                <span class="badge bg-warning text-dark"><i class="bx bx-star me-1"></i>Core (Mandatory)</span>
                            @else
                                <span class="badge bg-secondary"><i class="bx bx-bookmark me-1"></i>Elective</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <p class="text-muted small mb-1">Credit Hours</p>
                            <span class="badge bg-primary"><i class="bx bx-time me-1"></i>{{ $course->credit_hours }} Hours</span>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Active Instructors</p>
                            <span class="badge bg-info"><i class="bx bx-user-voice me-1"></i>{{ $activeInstructors->count() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Record Information -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-history me-2"></i> Record Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="text-muted small mb-1"><i class="bx bx-user-plus me-1"></i>Created By</p>
                            <p class="small fw-semibold mb-0">{{ $course->createdBy?->name ?? 'System' }}</p>
                        </div>
                        <div class="mb-3">
                            <p class="text-muted small mb-1"><i class="bx bx-calendar-plus me-1"></i>Created</p>
                            <p class="small fw-semibold mb-0">{{ $course->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div class="mb-3">
                            <p class="text-muted small mb-1"><i class="bx bx-user-check me-1"></i>Updated By</p>
                            <p class="small fw-semibold mb-0">{{ $course->updatedBy?->name ?? 'System' }}</p>
                        </div>
                        <div>
                            <p class="text-muted small mb-1"><i class="bx bx-calendar-check me-1"></i>Updated</p>
                            <p class="small fw-semibold mb-0">{{ $course->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Instructor History -->
                @php
                    $archivedInstructors = $instructorHistory->where('status', 'archived');
                @endphp
                @if($archivedInstructors->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-history me-2"></i> Instructor History
                            <span class="badge bg-secondary ms-2">{{ $archivedInstructors->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                        @foreach($archivedInstructors as $history)
                        <div class="mb-2 pb-2 @if(!$loop->last) border-bottom @endif">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="mb-0 fw-semibold small">{{ $history->employee->first_name ?? '' }} {{ $history->employee->last_name ?? '' }}</p>
                                    <small class="text-muted">{{ $history->academic_year }} - {{ $history->semester }}</small>
                                </div>
                                <span class="badge bg-secondary">Archived</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Main Content -->
            <div class="col-12 col-lg-8 order-1 order-lg-1">
                <!-- Course Header Card -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="rounded-circle bg-white bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                    <i class="bx bx-book-open text-white" style="font-size: 28px;"></i>
                                </div>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">{{ $course->name }}</h5>
                                <p class="mb-0 opacity-90"><i class="bx bx-code-alt me-1"></i>{{ $course->code }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Status Badges -->
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <span class="badge bg-primary px-3 py-2">
                                <i class="bx bx-time me-1"></i>{{ $course->credit_hours }} Credit Hours
                            </span>
                            <span class="badge @if($course->core_elective == 'Core') bg-warning text-dark @else bg-info @endif px-3 py-2">
                                <i class="bx bx-star me-1"></i>{{ $course->core_elective }}
                            </span>
                            <span class="badge @if($course->status == 'active') bg-success @else bg-danger @endif px-3 py-2">
                                <i class="bx bx-check-circle me-1"></i>{{ ucfirst($course->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Basic Information Section -->
                <div class="card border-info mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-id-card me-2 text-info"></i> Basic Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted small mb-1"><i class="bx bx-book-reader me-1"></i>Program</p>
                                    <p class="fw-semibold mb-0">{{ $course->program->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted small mb-1"><i class="bx bx-layer me-1"></i>Level</p>
                                    <p class="fw-semibold mb-0">{{ $course->level }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted small mb-1"><i class="bx bx-calendar me-1"></i>Semester</p>
                                    <p class="fw-semibold mb-0">Semester {{ $course->semester }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <p class="text-muted small mb-1"><i class="bx bx-time-five me-1"></i>Credit Hours</p>
                                    <p class="fw-semibold mb-0">{{ $course->credit_hours }} Hours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="card border-success mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-file me-2 text-success"></i> Description
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($course->description)
                        <p class="text-muted mb-0">{{ $course->description }}</p>
                        @else
                        <div class="text-center py-4">
                            <i class="bx bx-file text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2 mb-0">No description added.</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Course Instructors Card -->
                <div class="card border-warning mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bx bx-user-voice me-2 text-warning"></i> Course Instructors
                            <span class="badge bg-warning text-dark ms-2">{{ $activeInstructors->count() }}</span>
                        </h6>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignInstructorModal">
                            <i class="bx bx-plus me-1"></i>Add Instructor
                        </button>
                    </div>
                    <div class="card-body p-0">
                        @if($activeInstructors->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3"><i class="bx bx-user me-1"></i>Instructor</th>
                                        <th class="py-3"><i class="bx bx-calendar me-1"></i>Academic Year</th>
                                        <th class="py-3"><i class="bx bx-book me-1"></i>Semester</th>
                                        <th class="py-3"><i class="bx bx-calendar-check me-1"></i>Assigned</th>
                                        <th class="py-3"><i class="bx bx-check-circle me-1"></i>Status</th>
                                        <th class="py-3 text-center"><i class="bx bx-cog me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activeInstructors as $instructor)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                    <i class="bx bx-user text-primary"></i>
                                                </div>
                                                <div>
                                                    <p class="mb-0 fw-semibold">{{ $instructor->employee->first_name ?? '' }} {{ $instructor->employee->last_name ?? '' }}</p>
                                                    <small class="text-muted">{{ $instructor->employee->employee_id ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3">{{ $instructor->academic_year }}</td>
                                        <td class="py-3">{{ $instructor->semester }}</td>
                                        <td class="py-3">{{ $instructor->date_assigned->format('M d, Y') }}</td>
                                        <td class="py-3">
                                            <span class="badge bg-success">{{ ucfirst($instructor->status) }}</span>
                                        </td>
                                        <td class="py-3 text-center">
                                            <form action="{{ route('college.courses.remove-instructor', [$course->id, $instructor->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('Archive this instructor assignment?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Archive">
                                                    <i class="bx bx-archive"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-4">
                            <i class="bx bx-user-x text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2 mb-3">No instructor assigned</p>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignInstructorModal">
                                <i class="bx bx-plus me-1"></i>Assign Instructor
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Enrolled Students Card -->
                <div class="card border-secondary">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-group me-2 text-secondary"></i> Enrolled Students
                            <span class="badge bg-secondary ms-2">{{ $enrolledStudents->count() }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @if($enrolledStudents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-4 py-3"><i class="bx bx-hash me-1"></i>#</th>
                                        <th class="py-3"><i class="bx bx-id-card me-1"></i>Student No.</th>
                                        <th class="py-3"><i class="bx bx-user me-1"></i>Full Name</th>
                                        <th class="py-3"><i class="bx bx-book-reader me-1"></i>Program</th>
                                        <th class="py-3"><i class="bx bx-calendar me-1"></i>Academic Year</th>
                                        <th class="py-3"><i class="bx bx-check-shield me-1"></i>Status</th>
                                        <th class="py-3 text-center"><i class="bx bx-cog me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($enrolledStudents as $index => $enrollment)
                                    <tr>
                                        <td class="px-4 py-3">{{ $index + 1 }}</td>
                                        <td class="py-3">
                                            <span class="badge bg-light text-dark">{{ $enrollment->student->student_number ?? 'N/A' }}</span>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                                    <i class="bx bx-user text-primary" style="font-size: 14px;"></i>
                                                </div>
                                                <span class="fw-semibold">{{ $enrollment->student->full_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <small>{{ $enrollment->student->program->name ?? 'N/A' }}</small>
                                        </td>
                                        <td class="py-3">
                                            <small>{{ $enrollment->academicYear->name ?? 'N/A' }}</small>
                                        </td>
                                        <td class="py-3">
                                            @switch($enrollment->status)
                                                @case('enrolled')
                                                    <span class="badge bg-success">Enrolled</span>
                                                    @break
                                                @case('completed')
                                                    <span class="badge bg-primary">Completed</span>
                                                    @break
                                                @case('withdrawn')
                                                    <span class="badge bg-warning text-dark">Withdrawn</span>
                                                    @break
                                                @case('failed')
                                                    <span class="badge bg-danger">Failed</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ ucfirst($enrollment->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td class="py-3 text-center">
                                            <a href="{{ route('college.students.show', \Vinkla\Hashids\Facades\Hashids::encode($enrollment->student->id)) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-5">
                            <i class="bx bx-user-x text-muted" style="font-size: 48px;"></i>
                            <p class="text-muted mt-2 mb-0">No students enrolled in this course</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Instructor Modal -->
<div class="modal fade" id="assignInstructorModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h6 class="modal-title fw-bold"><i class="bx bx-user-plus me-2"></i>Assign Instructor</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('college.courses.assign-instructor', $course->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px;">
                            <i class="bx bx-user-voice text-primary" style="font-size: 28px;"></i>
                        </div>
                        <p class="fw-bold mb-0">{{ $course->code }} - {{ $course->name }}</p>
                    </div>

                    <div class="mb-3">
                        <label for="employee_id" class="form-label fw-semibold">
                            <i class="bx bx-user me-1"></i>Select Instructor <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="employee_id" name="employee_id" required>
                            <option value="">-- Select Employee --</option>
                            @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id ?? 'N/A' }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="academic_year" class="form-label fw-semibold">
                                <i class="bx bx-calendar me-1"></i>Academic Year <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="academic_year" name="academic_year" required>
                                <option value="">-- Select Year --</option>
                                @foreach($academicYears as $academicYear)
                                <option value="{{ $academicYear->name }}" {{ $academicYear->status == 'active' ? 'selected' : '' }}>
                                    {{ $academicYear->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="semester" class="form-label fw-semibold">
                                <i class="bx bx-book me-1"></i>Semester <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="semester" name="semester" required>
                                <option value="">-- Select --</option>
                                <option value="Semester 1">Semester 1</option>
                                <option value="Semester 2">Semester 2</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">
                            <i class="bx bx-note me-1"></i>Notes (Optional)
                        </label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Additional notes..."></textarea>
                    </div>

                    @if($activeInstructors->count() > 0)
                    <div class="alert alert-info small mb-0 py-2">
                        <i class="bx bx-info-circle me-1"></i>
                        Multiple instructors can be assigned to this course.
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bx bx-check me-1"></i>Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold"><i class="bx bx-trash me-2"></i>Delete Course</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-danger bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="bx bx-trash text-danger" style="font-size: 28px;"></i>
                </div>
                <p class="mb-2">Are you sure you want to delete this course?</p>
                <p class="fw-bold mb-3">{{ $course->code }} - {{ $course->name }}</p>
                @if($enrolledStudents->count() > 0)
                <div class="alert alert-danger small mb-3 py-2">
                    <i class="bx bx-exclamation-triangle me-1"></i>
                    This course has <strong>{{ $enrolledStudents->count() }}</strong> enrolled students!
                </div>
                @endif
                <div class="alert alert-warning small mb-0 py-2">
                    <i class="bx bx-exclamation-triangle me-1"></i>
                    This action cannot be undone.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <form action="{{ route('college.courses.destroy', $course->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bx bx-trash me-1"></i>Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        border-radius: 0.5rem;
    }

    .card.border-primary {
        border-color: #0d6efd !important;
    }

    .card.border-info {
        border-color: #0dcaf0 !important;
    }

    .card.border-success {
        border-color: #198754 !important;
    }

    .card.border-warning {
        border-color: #ffc107 !important;
    }

    .card.border-secondary {
        border-color: #6c757d !important;
    }

    .table th {
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        color: #6c757d;
        border-bottom: 1px solid #dee2e6;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.03);
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.8rem;
        }
    }
</style>
@endpush
