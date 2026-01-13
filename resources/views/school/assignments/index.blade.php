@extends('layouts.main')

@section('title', 'School Assignments & Homework')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Assignments & Homework', 'url' => '#', 'icon' => 'bx bx-book-open']
        ]" />
        <h6 class="mb-0 text-uppercase">SCHOOL ASSIGNMENTS & HOMEWORK</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Manage Assignments</h5>
                            </div>
                            <div>
                                <a href="{{ route('school.assignments.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Create New Assignment
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Filters -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label small">Academic Year</label>
                                <select class="form-select form-select-sm" id="academic_year_id">
                                    <option value="">All Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->year_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Class</label>
                                <select class="form-select form-select-sm" id="class_id">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Subject</label>
                                <select class="form-select form-select-sm" id="subject_id">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Type</label>
                                <select class="form-select form-select-sm" id="type">
                                    <option value="">All Types</option>
                                    <option value="homework">Homework</option>
                                    <option value="classwork">Classwork</option>
                                    <option value="project">Project</option>
                                    <option value="revision_task">Revision Task</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label small">Status</label>
                                <select class="form-select form-select-sm" id="status">
                                    <option value="">All Statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="closed">Closed</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="col-md-9 text-end">
                                <button type="button" class="btn btn-sm btn-secondary" id="clearFiltersBtn">
                                    <i class="bx bx-x me-1"></i> Clear Filters
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="assignments-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Assignment ID</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>Subject</th>
                                        <th>Class/Stream</th>
                                        <th>Teacher</th>
                                        <th>Due Date</th>
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

    .font-14 {
        font-size: 0.875rem;
    }

    .font-13 {
        font-size: 0.8125rem;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper {
        margin-top: 1rem;
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
    }

    .dataTables_length select:focus,
    .dataTables_filter input:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.75rem;
        margin-left: 2px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: white;
        color: #495057;
        text-decoration: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #e9ecef;
        border-color: #adb5bd;
        color: #495057;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #007bff;
        border-color: #007bff;
        color: white;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #6c757d;
        background: #e9ecef;
        border-color: #dee2e6;
        cursor: not-allowed;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#assignments-table').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            ajax: {
                url: '{{ route("school.assignments.data") }}',
                data: function(d) {
                    d.academic_year_id = $('#academic_year_id').val();
                    d.class_id = $('#class_id').val();
                    d.subject_id = $('#subject_id').val();
                    d.type = $('#type').val();
                    d.status = $('#status').val();
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX error:', error);
                    console.error('Response:', xhr.responseText);
                    
                    var errorMessage = 'Failed to load assignments. ';
                    if (xhr.status === 500) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response && response.error) {
                                errorMessage += response.error;
                            } else if (response && response.message) {
                                errorMessage += response.message;
                            } else {
                                errorMessage += 'Server error occurred.';
                            }
                        } catch (e) {
                            errorMessage += 'Server error occurred.';
                        }
                    } else if (xhr.status === 404) {
                        errorMessage += 'Route not found.';
                    } else {
                        errorMessage += 'Please check the console for details.';
                    }
                    
                    // Show error in table
                    $('#assignments-table tbody').html(
                        '<tr><td colspan="10" class="text-center text-danger">' + 
                        '<i class="bx bx-error-circle me-2"></i>' + errorMessage + 
                        '</td></tr>'
                    );
                    
                    // Show toastr notification if available
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage, 'Error Loading Assignments');
                    } else {
                        alert(errorMessage);
                    }
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'assignment_id', name: 'assignment_id' },
                { data: 'title', name: 'title' },
                { data: 'type_badge', name: 'type', orderable: false },
                { data: 'subject_name', name: 'subject_name' },
                { data: 'class_stream', name: 'class_stream' },
                { data: 'teacher_name', name: 'teacher_name' },
                { data: 'due_date', name: 'due_date' },
                { data: 'status_badge', name: 'status', orderable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[7, 'asc']], // Sort by due date
            columnDefs: [
                { orderable: false, targets: [0, 3, 8, 9] }, // #, Type, Status, Actions columns not sortable
                { searchable: false, targets: [0, 3, 8, 9] } // #, Type, Status, Actions columns not searchable
            ],
            language: {
                search: "Search assignments:",
                lengthMenu: "Show _MENU_ assignments per page",
                info: "Showing _START_ to _END_ of _TOTAL_ assignments",
                infoEmpty: "No assignments found",
                infoFiltered: "(filtered from _MAX_ total assignments)",
                processing: '<div class="text-center"><i class="bx bx-loader-alt bx-spin bx-lg"></i> Loading...</div>',
                emptyTable: 'No assignments found. <a href="{{ route("school.assignments.create") }}" class="btn btn-sm btn-primary ms-2">Create New Assignment</a>',
                zeroRecords: 'No matching assignments found'
            },
            initComplete: function() {
                // Style the DataTables elements
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-control form-control-sm');
            }
        });

        // Apply filters on change
        $('#academic_year_id, #class_id, #subject_id, #type, #status').on('change', function() {
            table.ajax.reload();
        });

        // Clear filters
        $('#clearFiltersBtn').on('click', function() {
            $('#academic_year_id, #class_id, #subject_id, #type, #status').val('').trigger('change');
            table.ajax.reload();
        });

        // Delete assignment
        $(document).on('click', '.delete-assignment', function() {
            var hashId = $(this).data('id');
            var row = $(this).closest('tr');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Assignment?',
                    text: 'Are you sure you want to delete this assignment? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ url("school/assignments") }}/' + hashId,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    table.row(row).remove().draw();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Deleted!',
                                        text: response.message || 'Assignment deleted successfully',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.error || 'Failed to delete assignment',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                var error = xhr.responseJSON?.error || 'Failed to delete assignment';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error,
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this assignment?')) {
                    $.ajax({
                        url: '{{ url("school/assignments") }}/' + hashId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                table.row(row).remove().draw();
                                alert(response.message || 'Assignment deleted successfully');
                            } else {
                                alert(response.error || 'Failed to delete assignment');
                            }
                        },
                        error: function(xhr) {
                            var error = xhr.responseJSON?.error || 'Failed to delete assignment';
                            alert(error);
                        }
                    });
                }
            }
        });
    });
</script>
@endpush

