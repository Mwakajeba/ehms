@extends('layouts.main')

@section('title', 'Subject Teachers Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subject Teachers', 'url' => '#', 'icon' => 'bx bx-link']
        ]" />
        <h6 class="mb-0 text-uppercase">SUBJECT TEACHERS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-link me-1 font-22 text-secondary"></i></div>
                                <h5 class="mb-0 text-secondary">Subject Teacher Assignments</h5>
                            </div>
                            <a href="{{ route('school.subject-teachers.create') }}" class="btn btn-secondary">
                                <i class="bx bx-plus me-1"></i> Assign Subject Teacher
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="subject-teachers-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Employee</th>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Academic Year</th>
                                        <th>Status</th>
                                        <th>Assigned Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subjectTeachers as $subjectTeacher)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="ms-2">
                                                    <h6 class="mb-0 font-14">{{ $subjectTeacher->employee->first_name }} {{ $subjectTeacher->employee->last_name }}</h6>
                                                    <p class="mb-0 font-13 text-muted">{{ $subjectTeacher->employee->employee_number }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0 font-14">{{ $subjectTeacher->subject->name }}</h6>
                                                <p class="mb-0 font-13 text-muted">{{ $subjectTeacher->subject->code }}</p>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $subjectTeacher->classe->name }}</span>
                                        </td>
                                        <td>
                                            @if($subjectTeacher->stream)
                                                <span class="badge bg-secondary">{{ $subjectTeacher->stream->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $subjectTeacher->academicYear->year_name }}</span>
                                        </td>
                                        <td>
                                            @if($subjectTeacher->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>{{ $subjectTeacher->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('school.subject-teachers.show', $subjectTeacher->hashid) }}" class="btn btn-sm btn-outline-primary" title="View">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                                <a href="{{ route('school.subject-teachers.edit', $subjectTeacher->hashid) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <form action="{{ route('school.subject-teachers.toggle-status', $subjectTeacher->hashid) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-warning toggle-status-btn" title="{{ $subjectTeacher->is_active ? 'Deactivate' : 'Activate' }}"
                                                            data-name="{{ $subjectTeacher->employee->first_name }} {{ $subjectTeacher->employee->last_name }} ({{ $subjectTeacher->subject->name }} - {{ $subjectTeacher->classe->name }}{{ $subjectTeacher->stream ? ' - ' . $subjectTeacher->stream->name : '' }})"
                                                            data-action="{{ $subjectTeacher->is_active ? 'deactivate' : 'activate' }}">
                                                        <i class="bx bx-{{ $subjectTeacher->is_active ? 'x' : 'check' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('school.subject-teachers.destroy', $subjectTeacher->hashid) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" title="Delete" 
                                                            data-hashid="{{ $subjectTeacher->hashid }}"
                                                            data-name="{{ $subjectTeacher->employee->first_name }} {{ $subjectTeacher->employee->last_name }} ({{ $subjectTeacher->subject->name }} - {{ $subjectTeacher->classe->name }}{{ $subjectTeacher->stream ? ' - ' . $subjectTeacher->stream->name : '' }})">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
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
        console.log('Subject Teachers page loaded');

        // SweetAlert for delete confirmation - must be bound before DataTables initialization
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            var button = $(this);
            var form = button.closest('form.delete-form');
            var assignmentName = button.data('name');

            Swal.fire({
                title: 'Delete Subject Teacher Assignment?',
                html: 'Are you sure you want to delete the assignment for "<strong>' + assignmentName + '</strong>"?<br><br>' +
                      '<small class="text-muted">This will remove the teacher from this subject assignment.</small><br><br>' +
                      '<strong class="text-danger">This action cannot be undone!</strong>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ensure form submission works
                    if (form.length > 0) {
                        form[0].submit();
                    } else {
                        console.error('Delete form not found');
                        Swal.fire('Error', 'Unable to delete. Please refresh the page and try again.', 'error');
                    }
                }
            });
            
            return false;
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
                title = 'Deactivate Subject Teacher Assignment?';
                confirmText = 'Yes, deactivate it!';
                confirmButtonColor = '#fd7e14';
                icon = 'warning';
            } else {
                title = 'Activate Subject Teacher Assignment?';
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

        // Initialize DataTables
        var table = $('#subject-teachers-table').DataTable({
            responsive: true,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: [8] }, // Actions column not sortable
                { searchable: false, targets: [0, 8] } // # and Actions columns not searchable
            ],
            language: {
                search: "Search assignments:",
                lengthMenu: "Show _MENU_ assignments per page",
                info: "Showing _START_ to _END_ of _TOTAL_ assignments",
                infoEmpty: "No assignments found",
                infoFiltered: "(filtered from _MAX_ total assignments)",
                emptyTable: "No subject teacher assignments found",
                zeroRecords: "No matching assignments found"
            },
            drawCallback: function() {
                // Re-bind delete button handlers after DataTables redraws
                // This ensures delete buttons work even after table redraws
            },
            initComplete: function() {
                // Style the DataTables elements
                $('.dataTables_filter input').addClass('form-control form-control-sm');
                $('.dataTables_length select').addClass('form-control form-control-sm');
            }
        });
    });
</script>
@endpush