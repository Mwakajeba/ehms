@extends('layouts.main')

@section('title', 'Attendance Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Attendance', 'url' => '#', 'icon' => 'bx bx-calendar-check']
        ]" />
        <h6 class="mb-0 text-uppercase">ATTENDANCE MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-calendar-check me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Attendance Sessions</h5>
                            </div>
                            <div>
                                <a href="{{ route('school.attendance.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Create Attendance Session
                                </a>
                            </div>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Filter Section -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-filter me-2"></i> Filter Attendance Sessions
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('school.attendance.index') }}" id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="class_id" class="form-label fw-bold">Class</label>
                                            <select class="form-select" id="class_id" name="class_id">
                                                <option value="">All Classes</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ $selectedClass == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="stream_id" class="form-label fw-bold">Stream</label>
                                            <select class="form-select" id="stream_id" name="stream_id">
                                                <option value="">All Streams</option>
                                                @foreach($streams as $stream)
                                                    <option value="{{ $stream->id }}" {{ $selectedStream == $stream->id ? 'selected' : '' }}>
                                                        {{ $stream->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="academic_year_id" class="form-label fw-bold">Academic Year</label>
                                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                                <option value="">All Academic Years</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}" {{ $selectedAcademicYear == $year->id ? 'selected' : '' }}>
                                                        {{ $year->year_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary" id="filterBtn">
                                                    <i class="bx bx-search me-1"></i> Filter
                                                </button>
                                                <a href="{{ route('school.attendance.index') }}" class="btn btn-outline-secondary">
                                                    <i class="bx bx-refresh me-1"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Results Summary -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">Attendance Sessions</h6>
                                <small class="text-muted">
                                    Manage attendance sessions for different classes and streams
                                </small>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="attendanceTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Class & Stream</th>
                                        <th>Academic Year</th>
                                        <th>Status</th>
                                        <th>Attendance Stats</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bx bx-trash me-2"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                </div>
                <h6 class="text-center mb-3">Are you sure you want to delete this attendance session?</h6>
                <p class="text-center text-muted mb-0" id="sessionInfoText">
                    <strong>Session: <span id="sessionInfo"></span></strong>
                </p>
                <div class="alert alert-warning mt-3" role="alert">
                    <i class="bx bx-info-circle me-1"></i>
                    <strong>Warning:</strong> This action cannot be undone. All attendance records for this session will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Yes, Delete Session
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    /* Filter Section Styles */
    .card.border-info .card-header {
        background: linear-gradient(135deg, #0dcaf0 0%, #0d6efd 100%) !important;
        border-bottom: 2px solid #0dcaf0;
    }

    .form-label {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-select {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-select:focus {
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
        transition: all 0.15s ease-in-out;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .btn-outline-secondary {
        transition: all 0.15s ease-in-out;
    }

    .btn-outline-secondary:hover {
        transform: translateY(-1px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .d-flex.gap-2 {
            flex-direction: column;
            gap: 0.5rem !important;
        }

        .d-flex.gap-2 .btn {
            width: 100%;
        }
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper {
        padding: 1rem 0;
    }

    .dataTables_length,
    .dataTables_filter,
    .dataTables_info,
    .dataTables_paginate {
        margin-bottom: 1rem;
    }

    .dataTables_length select,
    .dataTables_filter input {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_length select:focus,
    .dataTables_filter input:focus {
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
        outline: none;
    }

    /* DataTables pagination container */
    .dataTables_wrapper .dataTables_paginate {
        padding: 0.75rem 0 0 0 !important;
        margin: 0 !important;
        text-align: center !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.25rem 0.5rem !important;
        margin: 0 0.0625rem !important;
        border-radius: 0.25rem !important;
        border: 1px solid #dee2e6 !important;
        background: white !important;
        color: #0d6efd !important;
        font-size: 0.875rem !important;
        line-height: 1.2 !important;
        min-width: auto !important;
        height: auto !important;
        transition: all 0.15s ease-in-out !important;
        font-weight: 500 !important;
        display: inline-block !important;
        text-align: center !important;
        vertical-align: middle !important;
        cursor: pointer !important;
        user-select: none !important;
        border-radius: 0.25rem !important;
        box-sizing: border-box !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #0d6efd !important;
        color: white !important;
        border-color: #0d6efd !important;
        text-decoration: none !important;
        transform: translateY(-1px) !important;
        box-shadow: 0 0.125rem 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #0d6efd !important;
        color: white !important;
        border-color: #0d6efd !important;
        font-weight: 600 !important;
        box-shadow: 0 0.125rem 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #6c757d !important;
        background: #e9ecef !important;
        border-color: #dee2e6 !important;
        cursor: not-allowed !important;
        opacity: 0.6 !important;
        pointer-events: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
        background: #e9ecef !important;
        color: #6c757d !important;
        border-color: #dee2e6 !important;
        transform: none !important;
        box-shadow: none !important;
    }

    /* Previous/Next button specific styling */
    .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
    .dataTables_wrapper .dataTables_paginate .paginate_button.next {
        font-weight: 600 !important;
        padding: 0.25rem 0.75rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.previous:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.next:hover {
        background: #0d6efd !important;
        color: white !important;
    }

    /* Ensure pagination buttons don't wrap awkwardly */
    .dataTables_wrapper .dataTables_paginate {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 0.25rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        flex: none !important;
        white-space: nowrap !important;
    }

    /* Processing indicator */
    .dataTables_processing {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        z-index: 1000;
    }

    /* Mobile responsive pagination */
    @media (max-width: 576px) {
        .dataTables_wrapper .dataTables_paginate {
            padding: 0.5rem 0 !important;
            gap: 0.125rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.25rem 0.375rem !important;
            font-size: 0.75rem !important;
            min-width: 2rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
        .dataTables_wrapper .dataTables_paginate .paginate_button.next {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.75rem !important;
        }
    }

    @media (max-width: 768px) {
        .dataTables_wrapper .dataTables_paginate {
            justify-content: center !important;
            flex-wrap: wrap !important;
            gap: 0.25rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            margin: 0.0625rem !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    window.attendanceTable = $('#attendanceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.attendance.data") }}',
            type: 'GET',
            data: function(d) {
                d.class_id = $('#class_id').val();
                d.stream_id = $('#stream_id').val();
                d.academic_year_id = $('#academic_year_id').val();
            },
            error: function(xhr, status, error) {
                console.error('DataTables error:', error);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'session_date_formatted', name: 'session_date' },
            { data: 'class_stream', name: 'class_stream', orderable: false },
            { data: 'academic_year_name', name: 'academic_year_name' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'attendance_stats', name: 'attendance_stats', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[1, 'desc']],
        responsive: true,
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
        },
        initComplete: function() {
            // Style the DataTables elements
            $('.dataTables_filter input').addClass('form-control form-control-sm');
        }
    });

    // Reload DataTable with new filter parameters
    function reloadDataTable() {
        if (window.attendanceTable) {
            window.attendanceTable.ajax.reload(function() {
                // Reset filter button state after reload completes
                $('#filterBtn').prop('disabled', false).html('<i class="bx bx-search me-1"></i> Filter');
            }, false); // false parameter prevents resetting paging
        }
    }

    // Filter button click handler
    $('#filterBtn').on('click', function() {
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Filtering...');
        reloadDataTable();
    });

    // Clear filters button click handler
    $('#clearFiltersBtn').on('click', function() {
        $('#class_id').val('').trigger('change');
        $('#stream_id').val('').trigger('change');
        $('#academic_year_id').val('').trigger('change');
        reloadDataTable();
    });

    // Initialize Select2 elements
    function initializeSelect2() {
        $('#class_id, #stream_id, #academic_year_id').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });
    }

    // Function to load streams for a class
    function loadStreamsForClass(classId) {
        const streamSelect = $('#stream_id');

        // Show loading state
        streamSelect.html('<option value="">Loading streams...</option>');
        streamSelect.prop('disabled', true);

        // Make AJAX call to get streams for this class
        $.ajax({
            url: '{{ route("school.api.attendance.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Clear loading option
                streamSelect.empty();
                streamSelect.append('<option value="">All Streams</option>');

                if (response.streams && response.streams.length > 0) {
                    // Add stream options
                    response.streams.forEach(function(stream) {
                        streamSelect.append(`<option value="${stream.id}">${stream.name}</option>`);
                    });
                    streamSelect.prop('disabled', false);
                } else {
                    streamSelect.append('<option value="">No streams available</option>');
                    streamSelect.prop('disabled', true);
                }

                // Re-initialize Select2 after populating options
                streamSelect.select2({
                    placeholder: 'All Streams',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    minimumInputLength: 0
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.empty();
                streamSelect.append('<option value="">Error loading streams</option>');
                streamSelect.prop('disabled', true);
            }
        });
    }

    // Class change handler
    $('#class_id').on('change', function() {
        const classId = $(this).val();
        if (classId) {
            loadStreamsForClass(classId);
        } else {
            // Reset stream select when no class is selected
            $('#stream_id').empty().append('<option value="">All Streams</option>').prop('disabled', false);
            $('#stream_id').select2({
                placeholder: 'All Streams',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5',
                minimumInputLength: 0
            });
        }
    });

    // Initialize Select2 on page load
    initializeSelect2();

    // Load streams if a class is already selected (on page reload)
    const selectedClassId = $('#class_id').val();
    if (selectedClassId) {
        loadStreamsForClass(selectedClassId);
    }

    // Delete confirmation modal function
    window.confirmDelete = function(sessionInfo, deleteUrl) {
        $('#sessionInfo').text(sessionInfo);
        $('#sessionInfoText').show();
        $('#deleteForm').attr('action', deleteUrl);
        $('#deleteModal').modal('show');
    };
});
</script>
@endpush