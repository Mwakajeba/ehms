@extends('layouts.main')

@section('title', 'Course Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Courses', 'url' => '#', 'icon' => 'bx bx-book']
        ]" />
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">COURSE MANAGEMENT</h6>
            <a href="{{ route('college.courses.create') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus me-1"></i> Add Course
            </a>
        </div>
        <hr />

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bx bx-exclamation-circle me-1"></i>Validation Error!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-light" style="border-bottom: 1px solid #dee2e6;">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="filterProgram" class="form-label small fw-bold">Program</label>
                        <select id="filterProgram" class="form-select filter-select">
                            <option value="">All Programs</option>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="filterCourse" class="form-label small fw-bold">Course</label>
                        <input type="text" id="filterCourse" class="form-control filter-input" placeholder="Search course name or code...">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="filterStatus" class="form-label small fw-bold">Status</label>
                        <select id="filterStatus" class="form-select filter-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <button class="btn btn-secondary w-100 filter-btn" id="resetFilterBtn">
                            <i class="bx bx-refresh me-1"></i> Reset Filters
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="coursesTable" class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="bg-dark text-white">Code</th>
                                <th class="bg-dark text-white">Name</th>
                                <th class="bg-dark text-white">Program</th>
                                <th class="bg-dark text-white">Students</th>
                                <th class="bg-dark text-white">Level</th>
                                <th class="bg-dark text-white">Semester</th>
                                <th class="bg-dark text-white">Credits</th>
                                <th class="bg-dark text-white">Type</th>
                                <th class="bg-dark text-white">Status</th>
                                <th class="bg-dark text-white">Created By</th>
                                <th class="bg-dark text-white" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card {
        border-radius: 0.5rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        padding: 1.5rem;
        background-color: #f8f9fa;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Filter Styles */
    .filter-select, .filter-input {
        height: 45px;
        border-radius: 10px !important;
        border: 1px solid #ced4da;
        font-size: 14px;
    }

    .filter-select:focus, .filter-input:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .filter-btn {
        height: 45px;
        border-radius: 10px !important;
        font-size: 14px;
    }

    /* Select2 Custom Styling */
    .select2-container--default .select2-selection--single {
        height: 45px !important;
        border-radius: 10px !important;
        border: 1px solid #ced4da;
        padding: 6px 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        padding-left: 0;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px !important;
        right: 8px;
    }

    .select2-dropdown {
        border-radius: 10px !important;
        border: 1px solid #ced4da;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option {
        padding: 10px 12px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
        border-radius: 6px;
        margin: 2px 4px;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 8px;
        padding: 8px 12px;
    }

    .table thead th {
        font-weight: 600;
        font-size: 0.85rem;
        color: #495057;
        padding: 0.75rem;
        border-color: #dee2e6;
        background-color: #f8f9fa;
    }

    .table tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        color: #495057;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .btn-sm {
        padding: 0.35rem 0.6rem;
        font-size: 0.85rem;
    }

    .badge {
        padding: 0.35em 0.65em;
        font-weight: 500;
    }

    .form-label {
        margin-bottom: 0.5rem;
        color: #495057;
    }

    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }

        .table thead th {
            padding: 0.5rem;
        }

        .table tbody td {
            padding: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    // Initialize Select2 on filter dropdowns
    $('.filter-select').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true,
        placeholder: 'Select an option'
    });

    // Initialize DataTable
    const table = $('#coursesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("college.courses.data") }}',
            data: function (d) {
                d.program_id = $('#filterProgram').val();
                d.course = $('#filterCourse').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'program_name', name: 'program_name', orderable: false },
            { data: 'student_count', name: 'student_count', searchable: false },
            { data: 'level', name: 'level' },
            { data: 'semester', name: 'semester' },
            { data: 'credit_hours', name: 'credit_hours' },
            { data: 'core_elective', name: 'core_elective' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'created_by_name', name: 'created_by_name', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 10,
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search courses...",
            lengthMenu: "_MENU_",
            info: "Showing _START_ to _END_ of _TOTAL_ courses",
            paginate: {
                first: "<i class='bx bx-chevrons-left'></i>",
                last: "<i class='bx bx-chevrons-right'></i>",
                next: "<i class='bx bx-chevron-right'></i>",
                previous: "<i class='bx bx-chevron-left'></i>"
            }
        },
        columnDefs: [
            { orderable: false, targets: [9] }
        ]
    });

    // Delete Course
    $(document).on('click', '.delete-btn', function () {
        const courseId = $(this).data('id');
        const courseName = $(this).data('name');
        
        if (confirm(`Are you sure you want to delete "${courseName}"?`)) {
            $.ajax({
                url: `/college/courses/${courseId}`,
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message);
                    } else {
                        alert(response.message);
                    }
                    table.ajax.reload();
                },
                error: function (xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to delete course';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message);
                    } else {
                        alert(message);
                    }
                }
            });
        }
    });

    // Filter changes for select dropdowns
    $('#filterProgram, #filterStatus').on('change', function () {
        table.ajax.reload();
    });

    // Course search filter with debounce
    let searchTimeout;
    $('#filterCourse').on('keyup', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            table.ajax.reload();
        }, 500);
    });

    // Reset Filters
    $('#resetFilterBtn').on('click', function () {
        $('#filterProgram').val('').trigger('change');
        $('#filterCourse').val('');
        $('#filterStatus').val('').trigger('change');
        table.ajax.reload();
    });
});
</script>
@endpush

@endsection
