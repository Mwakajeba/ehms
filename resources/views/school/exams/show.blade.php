@extends('layouts.main')

@section('title', 'Exam Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exams', 'url' => route('school.exams.index'), 'icon' => 'bx bx-file'],
            ['label' => 'Exam Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="row">
            <div class="col-12">
                <!-- Exam Header -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h4 class="card-title mb-2">{{ $exam->exam_name }}</h4>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <span class="badge bg-{{ $exam->status === 'completed' ? 'success' : ($exam->status === 'ongoing' ? 'warning' : ($exam->status === 'scheduled' ? 'primary' : 'secondary')) }}">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                    <small class="text-muted">
                                        <i class="bx bx-calendar me-1"></i>
                                        {{ $exam->exam_date ? $exam->exam_date->format('M d, Y') : 'Date not set' }}
                                        @if($exam->start_time && $exam->end_time)
                                            | {{ $exam->start_time }} - {{ $exam->end_time }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('school.exams.edit', $exam) }}" class="btn btn-outline-warning btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                                    <i class="bx bx-printer me-1"></i> Print
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded me-1"></i> Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus('{{ $exam->id }}', 'scheduled')">
                                            <i class="bx bx-calendar-check me-1"></i> Mark as Scheduled
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus('{{ $exam->id }}', 'ongoing')">
                                            <i class="bx bx-play me-1"></i> Mark as Ongoing
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateStatus('{{ $exam->id }}', 'completed')">
                                            <i class="bx bx-check me-1"></i> Mark as Completed
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="confirmDelete('{{ $exam->id }}')">
                                            <i class="bx bx-trash me-1"></i> Delete Exam
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Exam Information -->
                    <div class="col-md-8">
                        <!-- Basic Details -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Exam Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Exam Type</label>
                                            <p class="mb-0">{{ $exam->examType ? $exam->examType->name : 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Academic Year</label>
                                            <p class="mb-0">{{ $exam->academicYear ? $exam->academicYear->year_name : 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Subject</label>
                                            <p class="mb-0">{{ $exam->subject ? $exam->subject->name : 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Class</label>
                                            <p class="mb-0">
                                                {{ $exam->class ? $exam->class->class_name : 'N/A' }}
                                                @if($exam->stream)
                                                    - {{ $exam->stream->name }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Duration</label>
                                            <p class="mb-0">{{ $exam->duration_minutes ? $exam->duration_minutes . ' minutes' : 'Not specified' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Weight</label>
                                            <p class="mb-0">{{ $exam->weight ? $exam->weight . '%' : 'Not specified' }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if($exam->description)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <p class="mb-0">{{ $exam->description }}</p>
                                </div>
                                @endif

                                @if($exam->instructions)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Instructions</label>
                                    <div class="border rounded p-3 bg-light">
                                        {!! nl2br(e($exam->instructions)) !!}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Marks Information -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-target me-1"></i> Marks & Grading</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="p-3 bg-primary bg-opacity-10 rounded">
                                            <h4 class="text-primary mb-1">{{ $exam->max_marks ?? 'N/A' }}</h4>
                                            <small class="text-muted">Maximum Marks</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-success bg-opacity-10 rounded">
                                            <h4 class="text-success mb-1">{{ $exam->pass_marks ?? 'N/A' }}</h4>
                                            <small class="text-muted">Pass Marks</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 bg-info bg-opacity-10 rounded">
                                            <h4 class="text-info mb-1">{{ $exam->weight ?? 0 }}%</h4>
                                            <small class="text-muted">Weight</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics & Quick Actions -->
                    <div class="col-md-4">
                        <!-- Quick Stats -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-bar-chart me-1"></i> Quick Statistics</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Total Students</span>
                                    <span class="badge bg-primary">{{ $exam->class ? $exam->class->students()->count() : 0 }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Exam Status</span>
                                    <span class="badge bg-{{ $exam->status === 'completed' ? 'success' : ($exam->status === 'ongoing' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($exam->status) }}
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span>Days Until Exam</span>
                                    <span class="badge bg-{{ $exam->exam_date && $exam->exam_date->isFuture() ? 'info' : 'danger' }}">
                                        @if($exam->exam_date)
                                            @if($exam->exam_date->isFuture())
                                                {{ $exam->exam_date->diffInDays(now()) }} days
                                            @elseif($exam->exam_date->isToday())
                                                Today
                                            @else
                                                Past
                                            @endif
                                        @else
                                            Not set
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-cog me-1"></i> Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('school.exams.edit', $exam) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="bx bx-edit me-1"></i> Edit Exam
                                    </a>
                                    <button class="btn btn-outline-primary btn-sm" onclick="duplicateExam('{{ $exam->id }}')">
                                        <i class="bx bx-copy me-1"></i> Duplicate Exam
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="exportExam('{{ $exam->id }}')">
                                        <i class="bx bx-download me-1"></i> Export Details
                                    </button>
                                    @if($exam->status !== 'completed')
                                    <button class="btn btn-outline-success btn-sm" onclick="updateStatus('{{ $exam->id }}', 'completed')">
                                        <i class="bx bx-check me-1"></i> Mark Completed
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Related Information -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-link me-1"></i> Related Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <small class="text-muted">Exam Type:</small><br>
                                    <a href="{{ route('school.exam-types.show', $exam->exam_type_id) }}" class="text-decoration-none">
                                        {{ $exam->examType ? $exam->examType->name : 'N/A' }}
                                    </a>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Subject:</small><br>
                                    <span>{{ $exam->subject ? $exam->subject->name : 'N/A' }}</span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted">Class:</small><br>
                                    <span>{{ $exam->class ? $exam->class->class_name : 'N/A' }}</span>
                                </div>
                                <div class="mb-0">
                                    <small class="text-muted">Academic Year:</small><br>
                                    <span>{{ $exam->academicYear ? $exam->academicYear->year_name : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="{{ route('school.exams.index') }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to Exams
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Exam Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="statusSelect" class="form-label">Status</label>
                    <select class="form-select" id="statusSelect">
                        <option value="scheduled">Scheduled</option>
                        <option value="draft">Draft</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusUpdate">Update Status</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this exam? This action cannot be undone.</p>
                <p class="text-muted small">Exam: <strong>{{ $exam->exam_name }}</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Exam</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.75rem;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1rem;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    .rounded {
        border-radius: 0.375rem !important;
    }

    .gap-2 {
        gap: 0.5rem !important;
    }

    .gap-3 {
        gap: 1rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
let statusUpdateUrl = '';
let deleteUrl = '';

function updateStatus(examId, status) {
    statusUpdateUrl = `/school/exams/${examId}/status`;
    $('#statusSelect').val(status);
    $('#statusModal').modal('show');
}

function confirmDelete(examId) {
    deleteUrl = `/school/exams/${examId}`;
    $('#deleteModal').modal('show');
}

function duplicateExam(examId) {
    if (confirm('Are you sure you want to duplicate this exam?')) {
        // Implement duplication logic
        toastr.info('Exam duplication feature coming soon');
    }
}

function exportExam(examId) {
    // Implement export logic
    toastr.info('Exam export feature coming soon');
}

$(document).ready(function() {
    // Confirm status update
    $('#confirmStatusUpdate').on('click', function() {
        const newStatus = $('#statusSelect').val();

        $.ajax({
            url: statusUpdateUrl,
            type: 'PATCH',
            data: { status: newStatus },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#statusModal').modal('hide');
                location.reload();
                toastr.success(response.message || 'Status updated successfully');
            },
            error: function(xhr) {
                $('#statusModal').modal('hide');
                const error = xhr.responseJSON?.error || 'Error updating status';
                toastr.error(error);
            }
        });
    });

    // Confirm delete
    $('#confirmDelete').on('click', function() {
        $.ajax({
            url: deleteUrl,
            type: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                window.location.href = '{{ route("school.exams.index") }}';
                toastr.success(response.message || 'Exam deleted successfully');
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                const error = xhr.responseJSON?.error || 'Error deleting exam';
                toastr.error(error);
            }
        });
    });
});
</script>
@endpush