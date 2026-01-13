@extends('layouts.main')

@section('title', 'Grade Scales Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Grade Scales', 'url' => '#', 'icon' => 'bx bx-star']
        ]" />
        <h6 class="mb-0 text-uppercase">GRADE SCALES MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-star me-1 font-22 text-danger"></i></div>
                                <h5 class="mb-0 text-danger">Academic Grade Scales</h5>
                            </div>
                            <a href="{{ route('school.grade-scales.create') }}" class="btn btn-danger">
                                <i class="bx bx-plus me-1"></i> Add New Grade Scale
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="grade-scales-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Grade Scale Name</th>
                                        <th>Academic Year</th>
                                        <th>Maximum Marks</th>
                                        <th>Passed Average Point</th>
                                        <th>Grades Count</th>
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

    .fs-1 {
        font-size: 3rem !important;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    .badge {
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#grade-scales-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.grade-scales.data") }}',
            type: 'GET',
            data: function(d) {
                // Add any additional data if needed
                return d;
            }
        },
        columns: [
            {
                data: 'id',
                name: 'id',
                orderable: true,
                searchable: false,
                width: '5%'
            },
            {
                data: 'name',
                name: 'name',
                orderable: true,
                searchable: true
            },
            {
                data: 'academic_year_name',
                name: 'academic_year_name',
                orderable: false,
                searchable: false
            },
            {
                data: 'max_marks',
                name: 'max_marks',
                orderable: true,
                searchable: false,
                width: '10%'
            },
            {
                data: 'passed_average_point',
                name: 'passed_average_point',
                orderable: true,
                searchable: false,
                width: '10%'
            },
            {
                data: 'grades_count',
                name: 'grades_count',
                orderable: false,
                searchable: false,
                width: '10%'
            },
            {
                data: 'is_active',
                name: 'is_active',
                orderable: true,
                searchable: false,
                width: '10%',
                render: function(data, type, row) {
                    if (data == 1) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-secondary">Inactive</span>';
                    }
                }
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                width: '20%'
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        initComplete: function() {
            // Add search functionality to each column
            this.api().columns().every(function() {
                var column = this;
                $('input', this.footer()).on('keyup change', function() {
                    if (column.search() !== this.value) {
                        column.search(this.value).draw();
                    }
                });
            });
        }
    });

    // Handle delete confirmation
    $(document).on('click', '.delete-grade-scale', function(e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('href');
        var gradeScaleName = $(this).data('name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the grade scale "${gradeScaleName}". This action cannot be undone and will also delete all associated grades.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl;
            }
        });
    });

    // Handle toggle status
    $(document).on('click', '.toggle-status', function(e) {
        e.preventDefault();
        var toggleUrl = $(this).attr('href');
        var gradeScaleName = $(this).data('name');
        var status = $(this).data('status');
        var action = status === 'deactivate' ? 'deactivate' : 'activate';
        var confirmText = status === 'deactivate' 
            ? `Are you sure you want to deactivate the grade scale "${gradeScaleName}"?`
            : `Are you sure you want to activate the grade scale "${gradeScaleName}"?`;

        Swal.fire({
            title: 'Confirm Action',
            text: confirmText,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: status === 'deactivate' ? '#6c757d' : '#28a745',
            cancelButtonColor: '#3085d6',
            confirmButtonText: status === 'deactivate' ? 'Deactivate' : 'Activate',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: toggleUrl,
                    type: 'PATCH',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                $('#grade-scales-table').DataTable().ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'An error occurred while updating the grade scale status.',
                                icon: 'error'
                            });
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'An error occurred while updating the grade scale status.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endpush