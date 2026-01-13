@extends('layouts.main')

@section('title', 'Exams Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exams', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-file me-1 font-22 text-success"></i>
                                <span class="h5 mb-0 text-success">Exams Management</span>
                            </div>
                            <div>
                                <a href="{{ route('school.exams.create') }}" class="btn btn-success">
                                    <i class="bx bx-plus me-1"></i> Create Exam
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <select id="academicYearFilter" class="form-select form-select-sm">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->year_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="examTypeFilter" class="form-select form-select-sm">
                                    <option value="">All Exam Types</option>
                                    @foreach($examTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('exam_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="classFilter" class="form-select form-select-sm">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                        {{ $class->class_name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="subjectFilter" class="form-select form-select-sm">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select id="statusFilter" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button id="clearFilters" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="bx bx-refresh me-1"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Date Range Filters -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <input type="date" id="dateFrom" class="form-control form-control-sm" placeholder="From Date" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-4">
                                <input type="date" id="dateTo" class="form-control form-control-sm" placeholder="To Date" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-4">
                                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="Search exams..." value="{{ request('search') }}">
                            </div>
                        </div>

                        <!-- Exams Table -->
                        <div class="table-responsive">
                            <table id="examsTable" class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Exam Details</th>
                                        <th>Subject & Class</th>
                                        <th>Schedule</th>
                                        <th>Marks</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $exams->appends(request()->query())->links() }}
                        </div>
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
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .badge {
        font-size: 0.75rem;
    }

    .btn-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }

    .exam-details {
        line-height: 1.2;
    }

    .exam-name {
        font-weight: 600;
        color: #0d6efd;
    }

    .exam-type {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .schedule-info {
        font-size: 0.85rem;
    }

    .time-range {
        color: #198754;
        font-weight: 500;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    let table;
    let statusUpdateUrl = '';
    let currentExamId = '';

    // Initialize DataTable
    table = $('#examsTable').DataTable({
        ajax: {
            url: '{{ route("school.exams.index") }}',
            data: function(d) {
                d.academic_year_id = $('#academicYearFilter').val();
                d.exam_type_id = $('#examTypeFilter').val();
                d.class_id = $('#classFilter').val();
                d.subject_id = $('#subjectFilter').val();
                d.status = $('#statusFilter').val();
                d.date_from = $('#dateFrom').val();
                d.date_to = $('#dateTo').val();
                d.search = $('#searchInput').val();
            }
        },
        columns: [
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `
                        <div class="exam-details">
                            <div class="exam-name">${data.exam_name}</div>
                            <div class="exam-type">${data.exam_type ? data.exam_type.name : 'N/A'}</div>
                            <div class="text-muted small">${data.academic_year ? data.academic_year.year_name : 'N/A'}</div>
                        </div>
                    `;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data) {
                    return `
                        <div>
                            <div><strong>${data.subject ? data.subject.name : 'N/A'}</strong></div>
                            <div class="text-muted small">${data.class ? data.class.class_name : 'N/A'}${data.stream ? ' - ' + data.stream.name : ''}</div>
                        </div>
                    `;
                }
            },
            {
                data: null,
                orderable: true,
                render: function(data) {
                    const date = new Date(data.exam_date).toLocaleDateString();
                    const time = data.start_time && data.end_time ?
                        `<div class="time-range">${data.start_time} - ${data.end_time}</div>` : '';
                    return `
                        <div class="schedule-info">
                            <div>${date}</div>
                            ${time}
                        </div>
                    `;
                }
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(data) {
                    return `
                        <div>
                            <div><strong>${data.max_marks || 'N/A'}</strong></div>
                            <div class="text-muted small">Pass: ${data.pass_marks || 'N/A'}</div>
                        </div>
                    `;
                }
            },
            {
                data: 'status',
                orderable: true,
                className: 'text-center',
                render: function(data) {
                    const badges = {
                        'scheduled': '<span class="badge bg-primary">Scheduled</span>',
                        'draft': '<span class="badge bg-secondary">Draft</span>',
                        'ongoing': '<span class="badge bg-warning">Ongoing</span>',
                        'completed': '<span class="badge bg-success">Completed</span>',
                        'cancelled': '<span class="badge bg-danger">Cancelled</span>'
                    };
                    return badges[data] || `<span class="badge bg-secondary">${data}</span>`;
                }
            },
            {
                data: 'actions',
                orderable: false,
                className: 'text-center',
                width: '120px'
            }
        ],
        order: [[2, 'desc']],
        pageLength: 15,
        responsive: true,
        language: {
            emptyTable: "No exams found"
        },
        drawCallback: function() {
            // Re-bind events after table redraw
            bindActionEvents();
        }
    });

    // Bind action events
    function bindActionEvents() {
        // Status update
        $('.status-update-btn').off('click').on('click', function() {
            currentExamId = $(this).data('id');
            statusUpdateUrl = $(this).data('url');
            const currentStatus = $(this).data('status');

            $('#statusSelect').val(currentStatus);
            $('#statusModal').modal('show');
        });
    }

    // Filter change events
    $('#academicYearFilter, #examTypeFilter, #classFilter, #subjectFilter, #statusFilter, #dateFrom, #dateTo').on('change', function() {
        table.ajax.reload();
    });

    // Search input
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            table.ajax.reload();
        }, 500);
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#academicYearFilter, #examTypeFilter, #classFilter, #subjectFilter, #statusFilter').val('');
        $('#dateFrom, #dateTo, #searchInput').val('');
        table.ajax.reload();
    });

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
                table.ajax.reload();
                toastr.success(response.message || 'Status updated successfully');
            },
            error: function(xhr) {
                $('#statusModal').modal('hide');
                const error = xhr.responseJSON?.error || 'Error updating status';
                toastr.error(error);
            }
        });
    });

    // Initial bind of events
    bindActionEvents();
});
</script>
@endpush