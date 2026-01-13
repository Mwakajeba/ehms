@extends('layouts.main')

@section('title', 'Classes Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Classes', 'url' => '#', 'icon' => 'bx bx-building']
        ]" />
        <h6 class="mb-0 text-uppercase">CLASSES MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-building me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">School Classes</h5>
                            </div>
                            <a href="{{ route('school.classes.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Class
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="classes-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Class Name</th>
                                        <th>Streams</th>
                                        <th>Students</th>
                                        <th>Created At</th>
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

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="deleteModalBody"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
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

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#classes-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.classes.data") }}',
                type: 'GET'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'streams_display', name: 'streams_display', orderable: false, searchable: false },
                { data: 'students_count', name: 'students_count', orderable: false, searchable: false },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            pageLength: 25,
            responsive: true,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            initComplete: function() {
                // Add search functionality to the table
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
    });

    // Global delete confirmation variables
    let deleteClassId = null;
    let deleteClassName = '';

    function confirmDelete(classId, className, enrollmentCount, sectionCount) {
        deleteClassId = classId;
        deleteClassName = className;

        let modalBody = `Are you sure you want to delete the class "<strong>${className}</strong>"? This action cannot be undone.`;

        if (enrollmentCount > 0) {
            modalBody += `
                <div class="alert alert-danger mt-3">
                    <i class="bx bx-error-circle me-1"></i>
                    <strong>Cannot Delete:</strong> This class has ${enrollmentCount} student(s) enrolled. Please remove all student enrollments before deleting this class.
                </div>`;
            $('#confirmDeleteBtn').prop('disabled', true).text('Cannot Delete');
        } else {
            if (sectionCount > 0) {
                modalBody += `
                    <div class="alert alert-warning mt-3">
                        <i class="bx bx-error-circle me-1"></i>
                        <strong>Warning:</strong> This class has ${sectionCount} section(s) assigned. Deleting this class will also remove all associated sections.
                    </div>`;
            }
            $('#confirmDeleteBtn').prop('disabled', false).text('Delete');
        }

        $('#deleteModalBody').html(modalBody);
        $('#deleteModal').modal('show');
    }

    $('#confirmDeleteBtn').on('click', function() {
        if (deleteClassId && !$(this).prop('disabled')) {
            // Create and submit form for deletion
            const form = $('<form>', {
                'method': 'POST',
                'action': `{{ url('school/classes') }}/${deleteClassId}`
            });

            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': '{{ csrf_token() }}'
            }));

            form.append($('<input>', {
                'type': 'hidden',
                'name': '_method',
                'value': 'DELETE'
            }));

            $('body').append(form);
            form.submit();
        }
    });
</script>
@endpush