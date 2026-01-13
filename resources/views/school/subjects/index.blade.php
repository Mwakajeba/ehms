@extends('layouts.main')

@section('title', 'Subjects Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subjects', 'url' => '#', 'icon' => 'bx bx-book-open']
        ]" />
        <h6 class="mb-0 text-uppercase">SUBJECTS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Academic Subjects</h5>
                            </div>
                            <a href="{{ route('school.subjects.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Subject
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="subjects-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Subject Name</th>
                                        <th>Short Name</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Subject Group</th>
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#subjects-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.subjects.data") }}',
                type: 'GET'
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'short_name',
                    name: 'short_name'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'type_badge',
                    name: 'type_badge',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'subject_group_name',
                    name: 'subject_group_name',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status_badge',
                    name: 'status_badge',
                    orderable: false,
                    searchable: false
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
                // Add export buttons if needed
                this.api().buttons().container().appendTo('#subjects-table_wrapper .col-md-6:eq(0)');
            }
        });

        console.log('Subjects DataTable loaded');
    });

    // SweetAlert for delete confirmation
    $(document).on('click', '.delete-subject-btn', function(e) {
        e.preventDefault();
        var button = $(this);
        var form = button.closest('form');
        var subjectName = button.data('subject-name');

        Swal.fire({
            title: 'Delete Subject?',
            html: 'Are you sure you want to delete "<strong>' + subjectName + '</strong>"?<br><br>' +
                  '<small class="text-muted">This will permanently remove the subject and may affect:</small><br>' +
                  '<small class="text-muted">• Curriculum assignments</small><br>' +
                  '<small class="text-muted">• Academic records</small><br>' +
                  '<small class="text-muted">• Related academic data</small><br><br>' +
                  '<small class="text-warning"><strong>Note:</strong> Subjects assigned to subject groups cannot be deleted. Remove from all subject groups first.</small><br><br>' +
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
</script>
@endpush