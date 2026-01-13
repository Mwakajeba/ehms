@extends('layouts.main')

@section('title', 'Exam Class Assignments')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Class Assignments', 'url' => '#', 'icon' => 'bx bx-target-lock']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-target-lock me-1 font-22 text-info"></i>
                                <span class="h5 mb-0 text-info">Exam Class Assignments Management</span>
                            </div>
                            <div>
                                <a href="{{ route('school.exam-class-assignments.create') }}" class="btn btn-info">
                                    <i class="bx bx-plus me-1"></i> Assign Class to Exam
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="examClassAssignmentsTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Exam Type</th>
                                        <th>Class</th>
                                        <th>Total Students</th>
                                        <th>Total Streams</th>
                                        <th>Total Subjects</th>
                                        <th>Academic Year</th>
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

    .overdue {
        color: #dc3545 !important;
        font-weight: bold;
    }

    .delete-group-btn {
        border: 2px solid #dc3545 !important;
        background: linear-gradient(45deg, #ffe6e6, #ffcccc) !important;
        color: #dc3545 !important;
        font-weight: bold;
        transition: all 0.3s ease;
    }

    .delete-group-btn:hover {
        background: linear-gradient(45deg, #ffcccc, #ffaaaa) !important;
        border-color: #b02a37 !important;
        color: #b02a37 !important;
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    }

    .delete-group-btn:active {
        transform: scale(0.95);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize DataTable
    // Initialize DataTable
    $('#examClassAssignmentsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.exam-class-assignments.data") }}',
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
                data: 'exam_type_name',
                name: 'exam_type_name',
                orderable: true,
                searchable: true
            },
            {
                data: 'class_name',
                name: 'class_name',
                orderable: true,
                searchable: true
            },
            {
                data: 'total_students',
                name: 'total_students',
                orderable: false,
                searchable: false,
                width: '10%'
            },
            {
                data: 'total_streams',
                name: 'total_streams',
                orderable: false,
                searchable: false,
                width: '10%'
            },
            {
                data: 'total_subjects',
                name: 'total_subjects',
                orderable: false,
                searchable: false,
                width: '10%'
            },
            {
                data: 'academic_year_name',
                name: 'academic_year_name',
                orderable: true,
                searchable: true
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                width: '15%'
            }
        ],
        order: [[0, 'desc']],
        pageLength: 50,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
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
        var assignmentInfo = $(this).closest('tr').find('td:nth-child(2)').text() +
                           ' - ' + $(this).closest('tr').find('td:nth-child(3)').text() +
                           ' (' + $(this).closest('tr').find('td:nth-child(5)').text() + ')';

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete the assignment "${assignmentInfo}". This action cannot be undone.`,
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

    // Handle group delete confirmation
    $(document).on('click', '.delete-group-btn', function(e) {
        e.preventDefault();
        var deleteUrl = $(this).data('url');
        var groupInfo = $(this).data('group-info');
        var assignmentCount = $(this).data('assignments');
        var registrationCount = $(this).data('registrations');

        // Build detailed warning message
        var warningHtml = '<div class="text-left">' +
            '<p class="mb-2"><strong>⚠️ DANGER: This action will permanently delete:</strong></p>' +
            '<ul class="mb-3 text-danger">' +
            '<li><strong>' + assignmentCount + ' exam assignments</strong> for this class and exam type</li>' +
            '<li><strong>' + registrationCount + ' student registrations</strong> across all subjects</li>' +
            '</ul>' +
            '<p class="mb-1 text-muted"><small>This includes all student exam registrations (registered, exempted, absent, attended) for:</small></p>' +
            '<p class="mb-0"><strong>' + groupInfo + '</strong></p>' +
            '<hr class="my-3">' +
            '<p class="text-danger mb-0"><strong>This action CANNOT be undone!</strong></p>' +
            '</div>';

        Swal.fire({
            title: '🚨 CRITICAL DELETION WARNING',
            html: warningHtml,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bx bx-trash me-1"></i>Yes, DELETE Everything!',
            cancelButtonText: '<i class="bx bx-x me-1"></i>Cancel (Safe)',
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary'
            },
            width: '600px',
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete all assignments and registrations.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Send DELETE request via AJAX
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deletion Completed',
                            text: response.message || 'All assignments and registrations have been deleted successfully.',
                            icon: 'success',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            // Reload the DataTable
                            $('#examClassAssignmentsTable').DataTable().ajax.reload();
                        });
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.message || xhr.responseJSON?.error || 'An error occurred while deleting the group';
                        Swal.fire({
                            title: 'Deletion Failed',
                            text: error,
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                });
            }
        });
    });

    // Handle status update
    $(document).on('click', '.status-update', function() {
        const url = $(this).data('url');
        const newStatus = $(this).data('status');
        const statusText = $(this).data('status-text');

        Swal.fire({
            title: 'Update Status',
            text: `Are you sure you want to mark this assignment as "${statusText}"?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'PATCH',
                    data: {
                        status: newStatus,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#examClassAssignmentsTable').DataTable().ajax.reload();
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
});
</script>
@endpush