@extends('layouts.main')

@section('title', 'Exam Class Assignment Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Class Assignments', 'url' => route('school.exam-class-assignments.index'), 'icon' => 'bx bx-target-lock'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-show me-1 font-22 text-info"></i>
                                <span class="h5 mb-0 text-info">Assignment Details</span>
                            </div>
                            <div>
                                <a href="{{ route('school.exam-class-assignments.edit', $assignment) }}" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.exam-class-assignments.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Assignment Information -->
                                <div class="card border-primary mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-info-circle me-1"></i> Assignment Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <dl class="row">
                                                    <dt class="col-sm-4">Exam Type:</dt>
                                                    <dd class="col-sm-8">
                                                        <span class="badge bg-info">{{ $assignment->examType->name }}</span>
                                                        </a>
                                                    </dd>

                                                    <dt class="col-sm-4">Class:</dt>
                                                    <dd class="col-sm-8">
                                                        <a href="{{ route('school.classes.show', $assignment->class) }}" class="text-decoration-none">
                                                            {{ $assignment->class->class_name }}
                                                        </a>
                                                    </dd>

                                                    <dt class="col-sm-4">Stream:</dt>
                                                    <dd class="col-sm-8">{{ $assignment->stream->stream_name ?? 'N/A' }}</dd>

                                                    <dt class="col-sm-4">Subject:</dt>
                                                    <dd class="col-sm-8">
                                                        <a href="{{ route('school.subjects.show', $assignment->subject) }}" class="text-decoration-none">
                                                            {{ $assignment->subject->subject_name }}
                                                        </a>
                                                    </dd>

                                                    <dt class="col-sm-4">Academic Year:</dt>
                                                    <dd class="col-sm-8">{{ $assignment->academicYear->year_name }}</dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <dl class="row">
                                                    <dt class="col-sm-4">Status:</dt>
                                                    <dd class="col-sm-8">
                                                        <span class="badge bg-{{ $assignment->status === 'completed' ? 'success' : ($assignment->status === 'in_progress' ? 'info' : ($assignment->status === 'pending' ? 'warning' : 'danger')) }}">
                                                            {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                                        </span>
                                                    </dd>

                                                    <dt class="col-sm-4">Due Date:</dt>
                                                    <dd class="col-sm-8">
                                                        @if($assignment->due_date)
                                                            @php
                                                                $isOverdue = $assignment->due_date->isPast() && $assignment->status !== 'completed';
                                                            @endphp
                                                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                                                {{ $assignment->due_date->format('M d, Y') }}
                                                                @if($isOverdue)
                                                                    <small class="text-danger">(Overdue)</small>
                                                                @endif
                                                            </span>
                                                        @else
                                                            N/A
                                                        @endif
                                                    </dd>

                                                    <dt class="col-sm-4">Created:</dt>
                                                    <dd class="col-sm-8">{{ $assignment->created_at->format('M d, Y H:i') }}</dd>

                                                    <dt class="col-sm-4">Updated:</dt>
                                                    <dd class="col-sm-8">{{ $assignment->updated_at->format('M d, Y H:i') }}</dd>

                                                    <dt class="col-sm-4">Created By:</dt>
                                                    <dd class="col-sm-8">{{ $assignment->creator->name ?? 'System' }}</dd>
                                                </dl>
                                            </div>
                                        </div>

                                        @if($assignment->notes)
                                        <div class="row">
                                            <div class="col-12">
                                                <dt>Notes:</dt>
                                                <dd>{{ $assignment->notes }}</dd>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Related Information -->
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-link me-1"></i> Related Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Exam Details -->
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-success">Exam Details</h6>
                                                <dl class="row small">
                                                    <dt class="col-sm-5">Exam Type:</dt>
                                                    <dd class="col-sm-7">{{ $assignment->exam->examType->name ?? 'N/A' }}</dd>

                                                    <dt class="col-sm-5">Exam Date:</dt>
                                                    <dd class="col-sm-7">{{ $assignment->exam->exam_date ? $assignment->exam->exam_date->format('M d, Y') : 'N/A' }}</dd>

                                                    <dt class="col-sm-5">Duration:</dt>
                                                    <dd class="col-sm-7">{{ $assignment->exam->duration ?? 'N/A' }} minutes</dd>

                                                    <dt class="col-sm-5">Total Marks:</dt>
                                                    <dd class="col-sm-7">{{ $assignment->exam->total_marks ?? 'N/A' }}</dd>
                                                </dl>
                                            </div>

                                            <!-- Class Details -->
                                            <div class="col-md-6 mb-3">
                                                <h6 class="text-success">Class Details</h6>
                                                <dl class="row small">
                                                    <dt class="col-sm-5">Class Name:</dt>
                                                    <dd class="col-sm-7">{{ $assignment->class->class_name }}</dd>

                                                    <dt class="col-sm-5">Stream:</dt>
                                                    <dd class="col-sm-7">{{ $assignment->stream->stream_name ?? 'N/A' }}</dd>

                                                    <dt class="col-sm-5">Students:</dt>
                                                    <dd class="col-sm-7">{{ $assignment->class->students()->count() }} enrolled</dd>

                                                    <dt class="col-sm-5">Teacher:</dt>
                                                    <dd class="col-sm-7">{{ $assignment->subject->teacher->name ?? 'Not assigned' }}</dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Status Management -->
                                <div class="card border-info mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-cog me-1"></i> Status Management
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Current Status</label>
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-{{ $assignment->status === 'completed' ? 'success' : ($assignment->status === 'in_progress' ? 'info' : ($assignment->status === 'pending' ? 'warning' : 'danger')) }} me-2">
                                                    {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                                </span>
                                                @if($assignment->due_date && $assignment->due_date->isPast() && $assignment->status !== 'completed')
                                                    <small class="text-danger">Overdue</small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2">
                                            @if($assignment->status !== 'pending')
                                                <button type="button"
                                                        class="btn btn-outline-warning btn-sm status-update"
                                                        data-status="pending"
                                                        data-status-text="Pending"
                                                        data-url="{{ route('school.exam-class-assignments.update-status', [$assignment, 'pending']) }}">
                                                    <i class="bx bx-time me-1"></i> Mark as Pending
                                                </button>
                                            @endif

                                            @if($assignment->status !== 'in_progress')
                                                <button type="button"
                                                        class="btn btn-outline-info btn-sm status-update"
                                                        data-status="in_progress"
                                                        data-status-text="In Progress"
                                                        data-url="{{ route('school.exam-class-assignments.update-status', [$assignment, 'in_progress']) }}">
                                                    <i class="bx bx-loader me-1"></i> Mark as In Progress
                                                </button>
                                            @endif

                                            @if($assignment->status !== 'completed')
                                                <button type="button"
                                                        class="btn btn-outline-success btn-sm status-update"
                                                        data-status="completed"
                                                        data-status-text="Completed"
                                                        data-url="{{ route('school.exam-class-assignments.update-status', [$assignment, 'completed']) }}">
                                                    <i class="bx bx-check me-1"></i> Mark as Completed
                                                </button>
                                            @endif

                                            @if($assignment->status !== 'cancelled')
                                                <button type="button"
                                                        class="btn btn-outline-danger btn-sm status-update"
                                                        data-status="cancelled"
                                                        data-status-text="Cancelled"
                                                        data-url="{{ route('school.exam-class-assignments.update-status', [$assignment, 'cancelled']) }}">
                                                    <i class="bx bx-x me-1"></i> Mark as Cancelled
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-cog me-1"></i> Quick Actions
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('school.exam-class-assignments.edit', $assignment) }}" class="btn btn-outline-warning btn-sm">
                                                <i class="bx bx-edit me-1"></i> Edit Assignment
                                            </a>
                                            <a href="{{ route('school.exams.show', $assignment->exam) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-file me-1"></i> View Exam Details
                                            </a>
                                            <a href="{{ route('school.classes.show', $assignment->class) }}" class="btn btn-outline-success btn-sm">
                                                <i class="bx bx-group me-1"></i> View Class Details
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm delete-btn"
                                                    data-url="{{ route('school.exam-class-assignments.destroy', $assignment) }}">
                                                <i class="bx bx-trash me-1"></i> Delete Assignment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle status update
    $('.status-update').on('click', function() {
        const url = $(this).data('url');
        const newStatus = $(this).data('status');
        const statusText = $(this).data('status-text');

        Swal.fire({
            title: 'Update Status',
            text: `Are you sure you want to mark this assignment as "${statusText}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'PATCH',
                    data: {
                        status: newStatus,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        location.reload();
                        toastr.success(response.message || 'Status updated successfully');
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Error updating status';
                        toastr.error(error);
                    }
                });
            }
        });
    });

    // Handle delete
    $('.delete-btn').on('click', function() {
        const deleteUrl = $(this).data('url');
        const assignmentInfo = '{{ $assignment->exam->name }} - {{ $assignment->class->class_name }} ({{ $assignment->subject->subject_name }})';

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the assignment "${assignmentInfo}". This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl;
            }
        });
    });
});
</script>
@endpush