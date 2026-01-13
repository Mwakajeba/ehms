@extends('layouts.main')

@section('title', 'Exam Types')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Types', 'url' => '#', 'icon' => 'bx bx-category']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-category me-1 font-22 text-primary"></i>
                                <span class="h5 mb-0 text-primary">Exam Types Management</span>
                            </div>
                            <div>
                                <a href="{{ route('school.exam-types.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add Exam Type
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="examTypesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Weight (%)</th>
                                        <th>Exams Count</th>
                                        <th>Status</th>
                                        <th>Published</th>
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
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#examTypesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.exam-types.data") }}',
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
                data: 'description',
                name: 'description',
                orderable: false,
                searchable: true,
                render: function(data) {
                    return data || 'No description';
                }
            },
            {
                data: 'weight',
                name: 'weight',
                orderable: true,
                searchable: false,
                width: '10%',
                className: 'text-center'
            },
            {
                data: 'exams_count',
                name: 'exams_count',
                orderable: false,
                searchable: false,
                width: '10%',
                className: 'text-center'
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
                data: 'is_published',
                name: 'is_published',
                orderable: true,
                searchable: false,
                width: '10%',
                render: function(data, type, row) {
                    if (data == 1) {
                        return '<span class="badge bg-info"><i class="bx bx-globe me-1"></i>Published</span>';
                    } else {
                        return '<span class="badge bg-warning"><i class="bx bx-globe me-1"></i>Unpublished</span>';
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
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
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
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');
        var examTypeName = $(this).closest('tr').find('td:nth-child(2)').text();

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the exam type "${examTypeName}". This action cannot be undone and will also delete all associated exams.`,
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

    // Handle status toggle
    $(document).on('click', '.status-toggle', function() {
        const url = $(this).data('url');
        const isActive = $(this).hasClass('btn-secondary'); // If it's secondary button, it's currently active
        const action = isActive ? 'deactivate' : 'activate';
        const actionText = isActive ? 'Deactivate' : 'Activate';

        Swal.fire({
            title: `Are you sure?`,
            text: `You are about to ${action} this exam type.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: isActive ? '#6c757d' : '#28a745',
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
                        $('#examTypesTable').DataTable().ajax.reload();
                        toastr.success(response.message || 'Status updated successfully');
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Error updating status';
                        toastr.error(error);
                    }
                });
            }
        });
    });

    // Handle publish toggle
    $(document).on('click', '.publish-toggle', function() {
        const url = $(this).data('url');
        const isPublished = $(this).hasClass('btn-outline-secondary'); // If it's outline-secondary, it's currently published
        const action = isPublished ? 'unpublish' : 'publish';
        const actionText = isPublished ? 'Unpublish' : 'Publish';

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
                        $('#examTypesTable').DataTable().ajax.reload();
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
});
</script>
@endpush