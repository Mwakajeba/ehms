@extends('layouts.main')

@section('title', 'Academic Year Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Academic Years', 'url' => route('college.academic-years.index'), 'icon' => 'bx bx-calendar'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">ACADEMIC YEAR DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <!-- Academic Year Information Section -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-detail me-1 font-22 text-primary"></i>
                                <h5 class="mb-0 text-primary d-inline">{{ $academicYear->year_name }}</h5>
                                {!! $academicYear->getCurrentBadge() !!}
                                {!! $academicYear->getStatusBadge() !!}
                            </div>
                            <div>
                                @if($academicYear->canBeEdited())
                                    <a href="{{ route('college.academic-years.edit', $academicYear) }}" class="btn btn-primary btn-sm">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
                                @endif
                                @if(!$academicYear->is_current && in_array($academicYear->status, ['active', 'upcoming']))
                                    <button type="button" class="btn btn-success btn-sm ms-2" onclick="setAsCurrent({{ $academicYear->id }})">
                                        <i class="bx bx-check-circle me-1"></i> Set as Current
                                    </button>
                                @endif
                                @if($academicYear->isActive())
                                    <button type="button" class="btn btn-warning btn-sm ms-2" onclick="markCompleted({{ $academicYear->id }})">
                                        <i class="bx bx-check me-1"></i> Mark Completed
                                    </button>
                                @endif
                            </div>
                        </div>
                        <hr />

                        <!-- Basic Information -->
                        <div class="card border-primary mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-info-circle me-2 text-primary"></i> Basic Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Academic Year Name:</label>
                                            <p class="mb-0">{{ $academicYear->year_name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Status:</label>
                                            <p class="mb-0">{!! $academicYear->getStatusBadge() !!}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Start Date:</label>
                                            <p class="mb-0">
                                                {{ $academicYear->start_date ? $academicYear->start_date->format('M d, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">End Date:</label>
                                            <p class="mb-0">
                                                {{ $academicYear->end_date ? $academicYear->end_date->format('M d, Y') : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Duration:</label>
                                            <p class="mb-0">{{ $academicYear->formatted_duration }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Progress:</label>
                                            <p class="mb-0">
                                                @if($academicYear->isActive())
                                                    <div class="progress mt-2" style="height: 20px;">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                             style="width: {{ $academicYear->progress_percentage }}%"
                                                             aria-valuenow="{{ $academicYear->progress_percentage }}"
                                                             aria-valuemin="0" aria-valuemax="100">
                                                            {{ number_format($academicYear->progress_percentage, 1) }}%
                                                        </div>
                                                    </div>
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if($academicYear->description)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description:</label>
                                    <p class="mb-0">{{ $academicYear->description }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="card border-info">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-time me-2 text-info"></i> Timestamps
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Created:</label>
                                            <p class="mb-0">{{ $academicYear->created_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Last Updated:</label>
                                            <p class="mb-0">{{ $academicYear->updated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bx bx-group font-24 text-primary"></i>
                                <h4 class="mt-2 text-primary">{{ $stats['total_students'] }}</h4>
                                <p class="mb-0 text-muted">Students</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bx bx-chalkboard font-24 text-success"></i>
                                <h4 class="mt-2 text-success">{{ $stats['total_classes'] }}</h4>
                                <p class="mb-0 text-muted">Classes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bx bx-user-check font-24 text-warning"></i>
                                <h4 class="mt-2 text-warning">{{ $stats['total_enrollments'] }}</h4>
                                <p class="mb-0 text-muted">Enrollments</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="bx bx-money font-24 text-info"></i>
                                <h4 class="mt-2 text-info">{{ $stats['total_fee_settings'] }}</h4>
                                <p class="mb-0 text-muted">Fee Settings</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-cog me-1"></i> Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($academicYear->canBeEdited())
                                <a href="{{ route('college.academic-years.edit', $academicYear) }}" class="btn btn-primary">
                                    <i class="bx bx-edit me-1"></i> Edit Academic Year
                                </a>
                            @endif

                            @if(!$academicYear->is_current && in_array($academicYear->status, ['active', 'upcoming']))
                                <button type="button" class="btn btn-success" onclick="setAsCurrent({{ $academicYear->id }})">
                                    <i class="bx bx-check-circle me-1"></i> Set as Current
                                </button>
                            @endif

                            @if($academicYear->isActive())
                                <button type="button" class="btn btn-warning" onclick="markCompleted({{ $academicYear->id }})">
                                    <i class="bx bx-check me-1"></i> Mark as Completed
                                </button>
                            @endif

                            <a href="{{ route('college.academic-years.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                @if($academicYear->canBeDeleted())
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0 text-danger"><i class="bx bx-trash me-1"></i> Danger Zone</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                This academic year can be deleted as it has no associated records.
                            </p>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $academicYear->id }}, '{{ addslashes($academicYear->year_name) }}')">
                                <i class="bx bx-trash me-1"></i> Delete Academic Year
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Set Current Modal -->
<div class="modal fade" id="setCurrentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Academic Year as Current</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to set <strong id="currentYearName"></strong> as the current academic year?</p>
                <p class="text-muted">This will unset any other academic year that is currently marked as current.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSetCurrent">Set as Current</button>
            </div>
        </div>
    </div>
</div>

<!-- Mark Completed Modal -->
<div class="modal fade" id="markCompletedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Academic Year as Completed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark <strong id="completedYearName"></strong> as completed?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmMarkCompleted">Mark as Completed</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Academic Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteYearName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Set as current functionality
function setAsCurrent(id) {
    // Get the year name from the page
    const yearName = '{{ addslashes($academicYear->year_name) }}';

    $('#currentYearName').text(yearName);
    $('#setCurrentModal').modal('show');

    $('#confirmSetCurrent').off('click').on('click', function() {
        const form = $('<form>', {
            'method': 'POST',
            'action': `{{ url('/college/academic-years') }}/${id}/set-current`
        });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        $('body').append(form);
        form.submit();
    });
}

// Mark as completed functionality
function markCompleted(id) {
    // Get the year name from the page
    const yearName = '{{ addslashes($academicYear->year_name) }}';

    $('#completedYearName').text(yearName);
    $('#markCompletedModal').modal('show');

    $('#confirmMarkCompleted').off('click').on('click', function() {
        const form = $('<form>', {
            'method': 'POST',
            'action': `{{ url('/college/academic-years') }}/${id}/mark-completed`
        });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        $('body').append(form);
        form.submit();
    });
}

// Delete functionality
function confirmDelete(id, yearName) {
    $('#deleteYearName').text(yearName);
    $('#deleteModal').modal('show');

    $('#confirmDelete').off('click').on('click', function() {
        const form = $('<form>', {
            'method': 'POST',
            'action': `{{ url('/college/academic-years') }}/${id}`
        });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
        $('body').append(form);
        form.submit();
    });
}
</script>
@endpush