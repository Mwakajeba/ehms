@extends('layouts.main')

@section('title', 'Exam Type Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Types', 'url' => route('school.exam-types.index'), 'icon' => 'bx bx-category'],
            ['label' => $examType->name, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-show me-1 font-22 text-info"></i>
                                <span class="h5 mb-0 text-info">Exam Type Details</span>
                            </div>
                            <div>
                                <a href="{{ route('school.exam-types.edit', $examType) }}" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.exam-types.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="card border-primary mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-info-circle me-1"></i> Basic Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <dl class="row">
                                                    <dt class="col-sm-4">Name:</dt>
                                                    <dd class="col-sm-8">{{ $examType->name }}</dd>

                                                    <dt class="col-sm-4">Status:</dt>
                                                    <dd class="col-sm-8">
                                                        <span class="badge {{ $examType->is_active ? 'bg-success' : 'bg-danger' }}">
                                                            {{ $examType->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </dd>

                                                    <dt class="col-sm-4">Published:</dt>
                                                    <dd class="col-sm-8">
                                                        <span class="badge {{ $examType->is_published ? 'bg-info' : 'bg-warning' }}">
                                                            <i class="bx bx-globe me-1"></i>
                                                            {{ $examType->is_published ? 'Published' : 'Unpublished' }}
                                                        </span>
                                                    </dd>

                                                    <dt class="col-sm-4">Weight:</dt>
                                                    <dd class="col-sm-8">{{ $examType->weight }}%</dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <dl class="row">
                                                    <dt class="col-sm-4">Created:</dt>
                                                    <dd class="col-sm-8">{{ $examType->created_at->format('M d, Y H:i') }}</dd>

                                                    <dt class="col-sm-4">Updated:</dt>
                                                    <dd class="col-sm-8">{{ $examType->updated_at->format('M d, Y H:i') }}</dd>

                                                    <dt class="col-sm-4">Created By:</dt>
                                                    <dd class="col-sm-8">{{ $examType->creator->name ?? 'System' }}</dd>
                                                </dl>
                                            </div>
                                        </div>

                                        @if($examType->description)
                                        <div class="row">
                                            <div class="col-12">
                                                <dt>Description:</dt>
                                                <dd>{{ $examType->description }}</dd>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Associated Exams -->
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bx bx-file me-1"></i> Associated Exams ({{ $examType->exams->count() }})
                                        </h6>
                                        <a href="{{ route('school.exams.create') }}?exam_type_id={{ $examType->id }}" class="btn btn-light btn-sm">
                                            <i class="bx bx-plus me-1"></i> Create Exam
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        @if($examType->exams->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Exam Name</th>
                                                        <th>Subject</th>
                                                        <th>Class</th>
                                                        <th>Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($examType->exams->take(10) as $exam)
                                                    <tr>
                                                        <td>{{ $exam->exam_name }}</td>
                                                        <td>{{ $exam->subject->name ?? 'N/A' }}</td>
                                                        <td>{{ $exam->class->class_name ?? 'N/A' }}</td>
                                                        <td>{{ $exam->exam_date->format('M d, Y') }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $exam->status === 'completed' ? 'success' : ($exam->status === 'scheduled' ? 'primary' : 'warning') }}">
                                                                {{ ucfirst($exam->status) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('school.exams.show', $exam) }}" class="btn btn-sm btn-outline-info">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                            @if($examType->exams->count() > 10)
                                            <div class="text-center mt-3">
                                                <a href="{{ route('school.exams.index', ['exam_type_id' => $examType->id]) }}" class="btn btn-outline-primary btn-sm">
                                                    View All Exams ({{ $examType->exams->count() }})
                                                </a>
                                            </div>
                                            @endif
                                        </div>
                                        @else
                                        <div class="text-center py-4">
                                            <i class="bx bx-file bx-lg text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No exams have been created for this exam type yet.</p>
                                            <a href="{{ route('school.exams.create') }}?exam_type_id={{ $examType->id }}" class="btn btn-primary btn-sm mt-2">
                                                <i class="bx bx-plus me-1"></i> Create First Exam
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Statistics Card -->
                                <div class="card border-info mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-bar-chart me-1"></i> Statistics
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h4 class="text-info mb-1">{{ $examType->exams->count() }}</h4>
                                                    <small class="text-muted">Total Exams</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-success mb-1">{{ $examType->exams->where('status', 'completed')->count() }}</h4>
                                                <small class="text-muted">Completed</small>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <h4 class="text-primary mb-1">{{ $examType->exams->where('status', 'scheduled')->count() }}</h4>
                                                    <small class="text-muted">Scheduled</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <h4 class="text-warning mb-1">{{ $examType->exams->where('status', 'ongoing')->count() }}</h4>
                                                <small class="text-muted">Ongoing</small>
                                            </div>
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
                                            <a href="{{ route('school.exam-types.edit', $examType) }}" class="btn btn-outline-warning btn-sm">
                                                <i class="bx bx-edit me-1"></i> Edit Exam Type
                                            </a>
                                            <button type="button"
                                                    class="btn btn-outline-{{ $examType->is_active ? 'danger' : 'success' }} btn-sm status-toggle"
                                                    data-id="{{ $examType->id }}"
                                                    data-url="{{ route('school.exam-types.toggle-status', $examType) }}">
                                                <i class="bx bx-{{ $examType->is_active ? 'x' : 'check' }} me-1"></i>
                                                {{ $examType->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                            <button type="button"
                                                    class="btn btn-outline-{{ $examType->is_published ? 'secondary' : 'primary' }} btn-sm publish-toggle"
                                                    data-id="{{ $examType->id }}"
                                                    data-url="{{ route('school.exam-types.toggle-publish', $examType) }}">
                                                <i class="bx bx-globe me-1"></i>
                                                {{ $examType->is_published ? 'Unpublish' : 'Publish' }}
                                            </button>
                                            @if($examType->exams->count() === 0)
                                            <button type="button"
                                                    class="btn btn-outline-danger btn-sm delete-btn"
                                                    data-id="{{ $examType->id }}"
                                                    data-url="{{ route('school.exam-types.destroy', $examType) }}">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </button>
                                            @endif
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

<!-- Status Toggle Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="statusAction"></span> this exam type?</p>
                <div id="statusWarning" class="alert alert-warning d-none">
                    <strong>Warning:</strong> Deactivating this exam type will prevent it from being used in new exams.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatus">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let statusUrl = '';

    // Handle status toggle
    $('.status-toggle').on('click', function() {
        const isActive = $(this).hasClass('btn-outline-danger');
        statusUrl = $(this).data('url');

        $('#statusAction').text(isActive ? 'deactivate' : 'activate');
        if (isActive) {
            $('#statusWarning').removeClass('d-none');
        } else {
            $('#statusWarning').addClass('d-none');
        }

        $('#statusModal').modal('show');
    });

    // Handle publish toggle
    $('.publish-toggle').on('click', function() {
        const isPublished = $(this).hasClass('btn-outline-secondary');
        const action = isPublished ? 'unpublish' : 'publish';
        const url = $(this).data('url');

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to ${action} this exam type. ${action === 'publish' ? 'This will make it visible to parents and students.' : 'This will hide it from parents and students.'}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: isPublished ? '#6c757d' : '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${action} it!`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        location.reload();
                        toastr.success(response.message || 'Publish status updated successfully');
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Error updating publish status';
                        toastr.error(error);
                    }
                });
            }
        });
    });

    // Confirm status change
    $('#confirmStatus').on('click', function() {
        $.ajax({
            url: statusUrl,
            type: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#statusModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                $('#statusModal').modal('hide');
                const error = xhr.responseJSON?.error || 'Error updating status';
                toastr.error(error);
            }
        });
    });

    // Handle delete
    $('.delete-btn').on('click', function() {
        if (confirm('Are you sure you want to delete this exam type? This action cannot be undone.')) {
            const url = $(this).data('url');
            const form = $('<form>', {
                'method': 'POST',
                'action': url
            });
            form.append($('<input>', {
                'name': '_method',
                'value': 'DELETE',
                'type': 'hidden'
            }));
            form.append($('<input>', {
                'name': '_token',
                'value': $('meta[name="csrf-token"]').attr('content'),
                'type': 'hidden'
            }));
            $('body').append(form);
            form.submit();
        }
    });
});
</script>
@endpush