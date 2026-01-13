@extends('layouts.main')

@section('title', 'Manage Student Exam Registration')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Class Assignments', 'url' => route('school.exam-class-assignments.index'), 'icon' => 'bx bx-target-lock'],
            ['label' => 'Manage Registration', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-1"></i>
                @if(session('error'))
                    {{ session('error') }}
                @else
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-user-check me-1 font-22 text-primary"></i>
                                <span class="h5 mb-0 text-primary">Manage Exam Registration for {{ $student->first_name }} {{ $student->last_name }}</span>
                            </div>
                        </div>
                        <hr />

                        <!-- Student Header Info -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-body py-2">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h5 class="mb-2 text-primary">
                                                    <i class="bx bx-user me-2"></i>{{ $student->first_name }} {{ $student->last_name }}
                                                </h5>
                                                <div class="row g-2">
                                                    <div class="col-sm-4">
                                                        <small class="text-muted d-block">Admission No.</small>
                                                        <strong>{{ $student->admission_number ?? 'N/A' }}</strong>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <small class="text-muted d-block">Gender</small>
                                                        <strong>{{ ucfirst($student->gender ?? 'N/A') }}</strong>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <small class="text-muted d-block">Class</small>
                                                        <strong>{{ $student->class->name ?? 'N/A' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row text-center g-1">
                                                    <div class="col-3">
                                                        <div class="p-1 bg-primary bg-opacity-10 rounded">
                                                            <h5 class="mb-0 text-primary" id="registered-count">0</h5>
                                                            <small class="text-muted">Reg</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="p-1 bg-warning bg-opacity-10 rounded">
                                                            <h5 class="mb-0 text-warning" id="exempted-count">0</h5>
                                                            <small class="text-muted">Exe</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="p-1 bg-danger bg-opacity-10 rounded">
                                                            <h5 class="mb-0 text-danger" id="absent-count">0</h5>
                                                            <small class="text-muted">Abs</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-3">
                                                        <div class="p-1 bg-success bg-opacity-10 rounded">
                                                            <h5 class="mb-0 text-success" id="attended-count">0</h5>
                                                            <small class="text-muted">Att</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-muted">
                                        <i class="bx bx-list-ul me-1"></i>Subject Registrations ({{ $assignments->count() }} subjects)
                                    </h6>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setAllStatus('registered')" title="Register for all subjects">
                                            <i class="bx bx-check-circle me-1"></i>Register All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="setAllStatus('exempted')" title="Exempt from all subjects">
                                            <i class="bx bx-x-circle me-1"></i>Exempt All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAllStatus('absent')" title="Mark absent for all subjects">
                                            <i class="bx bx-time me-1"></i>Absent All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Registration Form -->
                        <form id="registrationForm" action="{{ route('school.exam-class-assignments.save-registration', ['exam_type_hash' => request()->route('exam_type_hash'), 'class_hash' => request()->route('class_hash'), 'academic_year_hash' => request()->route('academic_year_hash')]) }}" method="POST">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%" class="text-center">#</th>
                                            <th width="30%">Subject</th>
                                            <th width="12%">Code</th>
                                            <th width="18%">Stream</th>
                                            <th width="20%">Status</th>
                                            <th width="15%">Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignments as $index => $assignment)
                                        <tr>
                                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <strong class="text-dark">{{ $assignment->subject->name ?? 'N/A' }}</strong>
                                                        @if($assignment->stream)
                                                            <br><small class="text-muted">{{ $assignment->stream->name }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code class="bg-light px-1 py-1 rounded small">{{ $assignment->subject->code ?? 'N/A' }}</code>
                                            </td>
                                            <td>
                                                @if($assignment->stream)
                                                    <span class="badge bg-info">{{ $assignment->stream->name }}</span>
                                                @else
                                                    <span class="text-muted">All Streams</span>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="hidden" name="registrations[{{ $index }}][assignment_id]" value="{{ $assignment->id }}">
                                                <select class="form-select form-select-sm status-select" name="registrations[{{ $index }}][status]">
                                                    <option value="registered" {{ ($existingRegistrations[$assignment->id] ?? 'registered') == 'registered' ? 'selected' : '' }}>
                                                        Registered
                                                    </option>
                                                    <option value="exempted" {{ ($existingRegistrations[$assignment->id] ?? 'registered') == 'exempted' ? 'selected' : '' }}>
                                                        Exempted
                                                    </option>
                                                    <option value="absent" {{ ($existingRegistrations[$assignment->id] ?? 'registered') == 'absent' ? 'selected' : '' }}>
                                                        Absent
                                                    </option>
                                                    <option value="attended" {{ ($existingRegistrations[$assignment->id] ?? 'registered') == 'attended' ? 'selected' : '' }}>
                                                        Attended
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="registrations[{{ $index }}][reason]" value="{{ $existingReasons[$assignment->id] ?? '' }}" placeholder="Optional">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('school.exam-class-assignments.show-group', ['exam_type_hash' => request()->route('exam_type_hash'), 'class_hash' => request()->route('class_hash'), 'academic_year_hash' => request()->route('academic_year_hash')]) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to Students
                            </a>
                            <button type="submit" class="btn btn-success" form="registrationForm">
                                <i class="bx bx-save me-1"></i> Save Changes
                            </button>
                        </div>

@push('styles')
<style>
    .card {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: none;
        border-radius: 10px;
    }

    .card.border-primary {
        border-color: #0d6efd !important;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        padding: 0.75rem 0.5rem;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 0.5rem;
        vertical-align: middle;
        border-bottom: 1px solid #f8f9fa;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .status-select {
        min-width: 140px;
        font-size: 0.875rem;
        border-radius: 6px;
    }

    .form-control-sm {
        border-radius: 4px;
        font-size: 0.875rem;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.375rem;
    }

    .btn-group .btn {
        border-radius: 6px !important;
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    .btn-group .btn:not(:last-child) {
        margin-right: 0.125rem;
    }

    .bg-primary.bg-opacity-10 {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    .bg-warning.bg-opacity-10 {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-danger.bg-opacity-10 {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    .bg-success.bg-opacity-10 {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }

    .table-responsive {
        max-height: 400px;
        overflow-y: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .code {
        font-family: 'Courier New', monospace;
        font-size: 0.875rem;
    }

    /* Responsive table adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            max-height: 300px;
        }

        .table thead th,
        .table tbody td {
            padding: 0.375rem 0.25rem;
            font-size: 0.8rem;
        }

        .status-select {
            min-width: 120px;
            font-size: 0.8rem;
        }

        .form-control-sm {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .table thead th:nth-child(3),
        .table tbody td:nth-child(3) {
            display: none; /* Hide code column on very small screens */
        }

        .table-responsive {
            max-height: 250px;
        }
    }

    /* Custom scrollbar for table */
    .table-responsive::-webkit-scrollbar {
        width: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    updateSummary();

    // Update summary when status selects change
    $('.status-select').on('change', function() {
        updateSummary();
    });
});

function updateSummary() {
    const statusCounts = {
        registered: 0,
        exempted: 0,
        absent: 0,
        attended: 0
    };

    $('.status-select').each(function() {
        const status = $(this).val();
        statusCounts[status]++;
    });

    $('#registered-count').text(statusCounts.registered);
    $('#exempted-count').text(statusCounts.exempted);
    $('#absent-count').text(statusCounts.absent);
    $('#attended-count').text(statusCounts.attended);
}

function setAllStatus(status) {
    $('.status-select').val(status).trigger('change');
}
</script>
@endpush