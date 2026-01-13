@extends('layouts.main')

@section('title', 'Subject Details - ' . $subject->name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Header with Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-dark">
                    <i class="bx bx-book me-2 text-primary"></i>{{ $subject->name }}
                </h4>
                <p class="text-muted mb-0">Subject Information & Details</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('school.academics.subjects.edit', $subject->hashid) }}" class="btn btn-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('school.academics.subjects.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-list-ul me-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Status Indicator -->
        <div class="alert alert-{{ $subject->is_active ? 'success' : 'warning' }} border-0 mb-4">
            <div class="d-flex align-items-center">
                <i class="bx bx-{{ $subject->is_active ? 'check-circle' : 'pause-circle' }} fs-4 me-2"></i>
                <div>
                    <strong>Status:</strong> {{ $subject->is_active ? 'Active' : 'Inactive' }}
                    <small class="text-muted ms-2">
                        (Created: {{ $subject->created_at->format('M d, Y H:i') }} |
                        Updated: {{ $subject->updated_at->format('M d, Y H:i') }})
                    </small>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Information Panel -->
            <div class="col-lg-8">
                <div class="card border">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-info-circle me-2 text-primary"></i>Subject Information
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr class="border-bottom">
                                        <td class="fw-semibold bg-light" width="30%">Subject Code</td>
                                        <td>
                                            <span class="badge bg-primary fs-6 px-3 py-2">{{ $subject->code }}</span>
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-semibold bg-light">Subject Name</td>
                                        <td class="fw-bold fs-5 text-dark">{{ $subject->name }}</td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-semibold bg-light">Short Name</td>
                                        <td>
                                            @if($subject->short_name)
                                                <span class="badge bg-light text-dark border px-3 py-2">{{ $subject->short_name }}</span>
                                            @else
                                                <span class="text-muted fst-italic">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-semibold bg-light">Subject Type</td>
                                        <td>
                                            @if($subject->subject_type)
                                                <span class="badge bg-info px-3 py-2">
                                                    {{ ucfirst($subject->subject_type) }}
                                                </span>
                                            @else
                                                <span class="text-muted fst-italic">Not specified</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-semibold bg-light">Subject Groups</td>
                                        <td>
                                            @if($subject->subjectGroup)
                                                <span class="badge bg-success px-3 py-2">{{ $subject->subjectGroup->name }}</span>
                                            @else
                                                <span class="text-muted fst-italic">Not assigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="border-bottom">
                                        <td class="fw-semibold bg-light">Sort Order</td>
                                        <td>
                                            <span class="badge bg-secondary px-3 py-2">{{ $subject->sort_order }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Academic Configuration -->
                <div class="card border mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-cog me-2 text-success"></i>Academic Configuration
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="text-center p-3 border rounded">
                                    <div class="display-6 fw-bold text-primary mb-1">{{ $subject->credit_hours ?? '—' }}</div>
                                    <div class="text-muted small">Credit Hours</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 border rounded">
                                    <div class="display-6 fw-bold text-warning mb-1">{{ $subject->passing_marks ?? '—' }}</div>
                                    <div class="text-muted small">Passing Marks (%)</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 border rounded">
                                    <div class="display-6 fw-bold text-info mb-1">{{ $subject->total_marks ?? '—' }}</div>
                                    <div class="text-muted small">Total Marks</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($subject->description)
                <div class="card border mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-file me-2 text-secondary"></i>Description
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0 text-dark">{{ $subject->description }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Statistics Panel -->
                <div class="card border">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-bar-chart me-2 text-info"></i>Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between p-3 border rounded mb-3">
                            <div>
                                <div class="fw-bold h4 mb-0 text-primary">{{ $subject->curricula->count() }}</div>
                                <div class="text-muted small">Associated Curricula</div>
                            </div>
                            <i class="bx bx-book-open fs-1 text-primary opacity-50"></i>
                        </div>
                        <hr>
                        <div class="small text-muted">
                            <div class="mb-2">
                                <strong>Created:</strong><br>
                                {{ $subject->created_at->format('l, F j, Y \a\t g:i A') }}
                            </div>
                            <div>
                                <strong>Last Updated:</strong><br>
                                {{ $subject->updated_at->format('l, F j, Y \a\t g:i A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card border mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-navigation me-2 text-warning"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="{{ route('school.academics.subjects.edit', $subject->hashid) }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bx bx-edit me-3 text-primary"></i>
                                <div>
                                    <div class="fw-semibold">Edit Subject</div>
                                    <small class="text-muted">Modify subject details</small>
                                </div>
                            </a>
                            <a href="{{ route('school.academics.subjects.create') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bx bx-plus me-3 text-success"></i>
                                <div>
                                    <div class="fw-semibold">Create New Subject</div>
                                    <small class="text-muted">Add another subject</small>
                                </div>
                            </a>
                            <a href="{{ route('school.academics.subjects.index') }}" class="list-group-item list-group-item-action d-flex align-items-center">
                                <i class="bx bx-list-ul me-3 text-info"></i>
                                <div>
                                    <div class="fw-semibold">View All Subjects</div>
                                    <small class="text-muted">Browse subject list</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Associated Curricula -->
        @if($subject->curricula->count() > 0)
        <div class="card border mt-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold">
                    <i class="bx bx-link me-2 text-warning"></i>Associated Curricula
                </h6>
                <span class="badge bg-warning text-dark">{{ $subject->curricula->count() }} item(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="curriculaTable">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 fw-semibold">#</th>
                                <th class="border-0 fw-semibold">Class</th>
                                <th class="border-0 fw-semibold">Stream</th>
                                <th class="border-0 fw-semibold">Academic Year</th>
                                <th class="border-0 fw-semibold">Status</th>
                                <th class="border-0 fw-semibold">Effective Period</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subject->curricula as $index => $curriculum)
                            <tr>
                                <td class="fw-semibold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $curriculum->classe->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if($curriculum->stream)
                                        <span class="badge bg-info">{{ $curriculum->stream->name }}</span>
                                    @else
                                        <span class="text-muted">All Streams</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-success">{{ $curriculum->academicYear->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    @if($curriculum->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    @if($curriculum->effective_from)
                                        {{ $curriculum->effective_from->format('M d, Y') }}
                                    @else
                                        <em>Not set</em>
                                    @endif
                                    <br>
                                    @if($curriculum->effective_to)
                                        to {{ $curriculum->effective_to->format('M d, Y') }}
                                    @else
                                        <em class="text-success">Ongoing</em>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .page-wrapper {
        background-color: #f8f9fa;
        min-height: 100vh;
    }

    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-radius: 8px;
    }

    .card-header {
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        border-bottom: 2px solid #dee2e6;
        padding: 0.75rem;
    }

    .table td {
        padding: 0.75rem;
        vertical-align: middle;
    }

    .badge {
        font-weight: 500;
        font-size: 0.75rem;
    }

    .alert {
        border-radius: 8px;
        border-left: 4px solid;
    }

    .alert-success {
        border-left-color: #198754;
    }

    .alert-warning {
        border-left-color: #ffc107;
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 0.5rem 1rem;
    }

    .list-group-item {
        border: none;
        padding: 1rem 1.25rem;
    }

    .list-group-item:hover {
        background-color: #f8f9fa;
    }

    .display-6 {
        font-size: 2rem;
    }

    .border {
        border-color: #dee2e6 !important;
    }

    .text-muted {
        color: #6c757d !important;
    }

    /* DataTables customization for system look */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .dataTables_wrapper .dataTables_info {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        margin: 0 2px;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0d6efd;
        color: white !important;
        border-color: #0d6efd;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable for curricula if it exists
    if ($('#curriculaTable').length) {
        $('#curriculaTable').DataTable({
            "pageLength": 10,
            "responsive": true,
            "order": [[4, "desc"]], // Sort by status by default
            "columnDefs": [
                { "orderable": false, "targets": [5] }, // Effective period not sortable
                { "width": "5%", "targets": [0] }, // # column
                { "width": "20%", "targets": [1, 2, 3, 4] }, // Other columns
                { "width": "30%", "targets": [5] } // Effective period
            ],
            "language": {
                "search": "Filter curricula:",
                "lengthMenu": "Show _MENU_ per page",
                "info": "_START_ to _END_ of _TOTAL_ curricula",
                "emptyTable": "No curricula found",
                "zeroRecords": "No matching curricula",
                "infoEmpty": "No curricula available",
                "infoFiltered": "(filtered from _MAX_ total)"
            },
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
    }
});
</script>
@endpush