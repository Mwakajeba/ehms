@extends('layouts.main')

@section('title', 'Class Teachers Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Class Teachers', 'url' => '#', 'icon' => 'bx bx-user-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CLASS TEACHERS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-user-plus me-1 font-22 text-warning"></i></div>
                                <h5 class="mb-0 text-warning">Class Teacher Assignments</h5>
                            </div>
                            <a href="{{ route('school.class-teachers.create') }}" class="btn btn-warning">
                                <i class="bx bx-plus me-1"></i> Assign Class Teacher
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="class-teachers-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Academic Year</th>
                                        <th>Status</th>
                                        <th>Assigned Date</th>
                                        <th>Actions</th>
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Class Teachers page loaded');

        // Initialize DataTable
        $('#class-teachers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.class-teachers.data") }}',
                type: 'GET'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                {
                    data: 'employee_info',
                    name: 'employee.full_name',
                    render: function(data, type, row) {
                        return `
                            <div class="d-flex align-items-center">
                                <div class="ms-2">
                                    <h6 class="mb-0 font-14">${data.name}</h6>
                                    <p class="mb-0 font-13 text-muted">${data.number}</p>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'class_name',
                    name: 'classe.name',
                    render: function(data, type, row) {
                        return `<span class="badge bg-info">${data}</span>`;
                    }
                },
                {
                    data: 'stream_name',
                    name: 'stream.name',
                    render: function(data, type, row) {
                        return data !== '-' ? `<span class="badge bg-secondary">${data}</span>` : `<span class="text-muted">${data}</span>`;
                    }
                },
                {
                    data: 'academic_year_name',
                    name: 'academicYear.year_name',
                    render: function(data, type, row) {
                        return `<span class="badge bg-primary">${data}</span>`;
                    }
                },
                { data: 'status_badge', name: 'is_active', orderable: false, searchable: false },
                { data: 'assigned_date', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
            order: [[6, 'desc']], // Sort by assigned date descending
            responsive: true,
            language: {
                processing: '<div class="text-center"><i class="bx bx-loader-alt bx-spin bx-lg"></i> Loading...</div>'
            },
            initComplete: function() {
                console.log('DataTable initialized');
            }
        });

        // SweetAlert for delete confirmation
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var button = $(this);
            var form = button.closest('form');
            var assignmentName = button.data('name');

            Swal.fire({
                title: 'Delete Class Teacher Assignment?',
                html: 'Are you sure you want to delete the assignment for "<strong>' + assignmentName + '</strong>"?<br><br>' +
                      '<small class="text-muted">This will remove the teacher from this class assignment.</small><br><br>' +
                      '<strong class="text-danger">This action cannot be undone!</strong>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // SweetAlert for toggle status confirmation
        $(document).on('click', '.toggle-status-btn', function(e) {
            e.preventDefault();
            var button = $(this);
            var form = button.closest('form');
            var assignmentName = button.data('name');
            var action = button.data('action');

            var title, confirmText, confirmButtonColor, icon;

            if (action === 'deactivate') {
                title = 'Deactivate Class Teacher Assignment?';
                confirmText = 'Yes, deactivate it!';
                confirmButtonColor = '#fd7e14';
                icon = 'warning';
            } else {
                title = 'Activate Class Teacher Assignment?';
                confirmText = 'Yes, activate it!';
                confirmButtonColor = '#28a745';
                icon = 'question';
            }

            Swal.fire({
                title: title,
                html: 'Are you sure you want to <strong>' + action + '</strong> the assignment for "<strong>' + assignmentName + '</strong>"?<br><br>' +
                      '<small class="text-muted">This will change the teacher\'s assignment status.</small>',
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush