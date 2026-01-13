@extends('layouts.main')

@section('title', 'Students Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Students', 'url' => '#', 'icon' => 'bx bx-user']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENTS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-user me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">College Students</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('college.students.import') }}" class="btn btn-success btn-md">
                                    <i class="bx bx-upload me-2"></i> Import Students
                                </a>
                                <a href="{{ route('college.students.create') }}" class="btn btn-primary btn-md">
                                    <i class="bx bx-plus me-2"></i> Add New Student
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Filter Section -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-filter me-2"></i> Filter Students
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('college.students.index') }}" id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-2 col-sm-6">
                                            <label for="program_id" class="form-label fw-bold">Program</label>
                                            <select class="form-select" id="program_id" name="program_id">
                                                <option value="">All Programs</option>
                                                @foreach($programs as $program)
                                                    <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                                        {{ $program->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6">
                                            <label for="department_id" class="form-label fw-bold">Department</label>
                                            <select class="form-select" id="department_id" name="department_id">
                                                <option value="">All Departments</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6">
                                            <label for="academic_year_id" class="form-label fw-bold">Academic Year</label>
                                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                                <option value="">All Academic Years</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                        {{ $year->year_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6">
                                            <label for="status" class="form-label fw-bold">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">All Statuses</option>
                                                @foreach($statuses as $statusOption)
                                                    <option value="{{ $statusOption }}" {{ request('status') == $statusOption ? 'selected' : '' }}>
                                                        {{ ucfirst($statusOption) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6">
                                            <label for="admission_level" class="form-label fw-bold">Level</label>
                                            <select class="form-select" id="admission_level" name="admission_level">
                                                <option value="">All Levels</option>
                                                @foreach($levels as $level)
                                                    <option value="{{ $level }}" {{ request('admission_level') == $level ? 'selected' : '' }}>
                                                        {{ str_replace('Level', 'Level ', $level) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-sm-6 d-flex align-items-end">
                                            <div class="d-flex gap-2 w-100">
                                                <button type="submit" class="btn btn-primary flex-fill" id="filterBtn">
                                                    <i class="bx bx-search me-1"></i> Filter
                                                </button>
                                                <a href="{{ route('college.students.index') }}" class="btn btn-outline-secondary">
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
                                <h6 class="mb-1">College Students</h6>
                                <small class="text-muted">
                                    Use the filters above to search and filter students by program, department, academic year, status, and level
                                </small>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="students-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Student Number</th>
                                        <th>Full Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Program</th>
                                        <th>Department</th>
                                        <th>Courses</th>
                                        <th>Enrollment Year</th>
                                        <th>Status</th>
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
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete student <strong id="deleteStudentName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bx bx-warning me-1"></i>
                    This action cannot be undone. All associated data will be affected.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Student</button>
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Document ready, initializing Students DataTable...');

        // Check if jQuery and DataTable are available
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables is not loaded');
            return;
        }

        console.log('jQuery and DataTables are available');

        // Initialize DataTable
        try {
            $('#students-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("college.students.data") }}',
                    type: 'GET',
                    data: function(d) {
                        d.program_id = $('#program_id').val();
                        d.department_id = $('#department_id').val();
                        d.academic_year_id = $('#academic_year_id').val();
                        d.status = $('#status').val();
                        d.admission_level = $('#admission_level').val();
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX Error:', error, thrown);
                        console.log('Response:', xhr.responseText);
                        console.log('Status:', xhr.status);
                        if (xhr.status === 403) {
                            alert('Access denied. Please select a branch first.');
                            window.location.href = '{{ route("change-branch") }}';
                        } else {
                            alert('Error loading data: ' + error + ' - ' + thrown);
                        }
                    }
                },
                columns: [
                    {
                        data: null,
                        name: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'student_number',
                        name: 'student_number'
                    },
                    {
                        data: 'full_name',
                        name: 'full_name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'program',
                        name: 'program'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'courses_list',
                        name: 'courses_list',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'enrollment_year',
                        name: 'enrollment_year'
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        orderable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                pageLength: 25,
                responsive: true,
                language: {
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                },
                initComplete: function() {
                    console.log('Students DataTable initialized successfully');
                }
            });

            console.log('Students DataTable initialized');
        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }

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
            $('#program_id').val('').trigger('change');
            $('#department_id').val('').trigger('change');
            $('#academic_year_id').val('').trigger('change');
            $('#status').val('').trigger('change');
            $('#admission_level').val('').trigger('change');
            reloadDataTable();
        });

        // Initialize Select2 elements
        function initializeSelect2() {
            $('#program_id, #department_id, #academic_year_id, #status, #admission_level').select2({
                placeholder: 'Select',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5',
                minimumInputLength: 0
            });
        }
    });

    function confirmDelete(id, name) {
        $('#deleteStudentName').text(name);
        $('#deleteForm').attr('action', '{{ url("college/students") }}/' + id);
        $('#deleteModal').modal('show');
    }
</script>
@endpush