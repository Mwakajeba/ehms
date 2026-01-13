@extends('layouts.main')

@section('title', 'Students Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Students', 'url' => '#', 'icon' => 'bx bx-id-card']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENTS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-id-card me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Student Records</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('school.students.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add New Student
                                </a>
                                <a href="{{ route('school.students.import') }}" class="btn btn-outline-success">
                                    <i class="bx bx-upload me-1"></i> Import Excel
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

                        <!-- Filter Section -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-filter me-2"></i> Filter Students
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('school.students.index') }}" id="filterForm">
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
                                                <a href="{{ route('school.students.index') }}" class="btn btn-outline-secondary">
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
                                <h6 class="mb-1">Student Records</h6>
                                <small class="text-muted">
                                    Use the filters above to search and filter students by class, stream, and academic year
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="exportToExcel()">
                                    <i class="bx bx-download me-1"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportToPDF()">
                                    <i class="bx bx-file me-1"></i> Export PDF
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="studentsTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Admission No.</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Category</th>
                                        <th>Gender</th>
                                        <th>Guardian</th>
                                        <th>Discount</th>
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
                <h6 class="text-center mb-3">Are you sure you want to delete this student?</h6>
                <p class="text-center text-muted mb-0" id="studentNameText">
                    <strong>Student: <span id="studentName"></span></strong>
                </p>
                <div class="alert alert-warning mt-3" role="alert">
                    <i class="bx bx-info-circle me-1"></i>
                    <strong>Warning:</strong> This action cannot be undone. The student record and all associated data will be permanently deleted.
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
                        <i class="bx bx-trash me-1"></i> Yes, Delete Student
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
    window.studentsTable = $('#studentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.students.data") }}',
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
            { data: 'admission_number', name: 'admission_number' },
            { data: 'full_name', name: 'full_name' },
            { data: 'class_name', name: 'class_name' },
            { data: 'stream_name', name: 'stream_name' },
            { data: 'boarding_badge', name: 'boarding_type', orderable: false },
            { data: 'gender_badge', name: 'gender', orderable: false },
            { data: 'guardian_info', name: 'guardian_info', orderable: false, searchable: false },
            { data: 'discount_info', name: 'discount_info', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[1, 'asc']],
        responsive: true,
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
        },
        initComplete: function() {
            // Style the DataTables elements
            $('.dataTables_filter input').addClass('form-control form-control-sm');

            // Check if we need to refresh the table (e.g., after guardian assignment)
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('refresh') === '1') {
                // Remove the refresh parameter from URL without triggering a page reload
                const newUrl = window.location.pathname + window.location.search.replace(/[?&]refresh=1/, '');
                window.history.replaceState({}, document.title, newUrl);

                // Reload the DataTable to show updated guardian information
                if (window.studentsTable) {
                    window.studentsTable.ajax.reload(null, false);
                }
            }
        }
    });

    // Reload DataTable with new filter parameters
    function reloadDataTable() {
        if (window.studentsTable) {
            window.studentsTable.ajax.reload(function() {
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

    // Export functions
    window.exportToExcel = function() {
        const classId = $('#class_id').val();
        const streamId = $('#stream_id').val();
        const academicYearId = $('#academic_year_id').val();

        // Generate a simple hash ID for security
        const hashId = btoa(Date.now().toString()).replace(/=/g, '');

        let url = '{{ route("school.students.export.excel", ":hashId") }}'.replace(':hashId', hashId);
        const params = new URLSearchParams();

        if (classId) params.append('class_id', classId);
        if (streamId) params.append('stream_id', streamId);
        if (academicYearId) params.append('academic_year_id', academicYearId);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        window.open(url, '_blank');
    };

    window.exportToPDF = function() {
        const classId = $('#class_id').val();
        const streamId = $('#stream_id').val();
        const academicYearId = $('#academic_year_id').val();

        // Generate a simple hash ID for security
        const hashId = btoa(Date.now().toString()).replace(/=/g, '');

        let url = '{{ route("school.students.export.pdf", ":hashId") }}'.replace(':hashId', hashId);
        const params = new URLSearchParams();

        if (classId) params.append('class_id', classId);
        if (streamId) params.append('stream_id', streamId);
        if (academicYearId) params.append('academic_year_id', academicYearId);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        window.open(url, '_blank');
    };

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
            url: '{{ route("school.api.students.streams-by-class") }}',
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
    window.confirmDelete = function(studentName, deleteUrl) {
        $('#studentName').text(studentName);
        $('#studentNameText').show();
        $('#deleteForm').attr('action', deleteUrl);
        $('#deleteModal').modal('show');
    };

    // Toast notification function
    function showToast(message, type = 'info') {
        const toastColors = {
            success: '#198754',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#0dcaf0'
        };

        // Create toast element
        const toast = $(`
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                 style="background-color: ${toastColors[type]}; position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        // Add to body and show
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        // Remove after shown
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});
</script>
@endpush