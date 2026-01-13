@extends('layouts.main')

@section('title', 'Subject Groups Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Exams', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'Subjects', 'url' => route('school.subjects.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subject Groups', 'url' => '#', 'icon' => 'bx bx-group']
        ]" />
        <h6 class="mb-0 text-uppercase">SUBJECT GROUPS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-group me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Subject Groups</h5>
                            </div>
                            <a href="{{ route('school.subject-groups.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Group
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="subject-groups-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Class</th>
                                        <th>Subjects</th>
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
        $('#subject-groups-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.subject-groups.data") }}',
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
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'classe_name',
                    name: 'classe_name'
                },
                {
                    data: 'subjects_count',
                    name: 'subjects_count',
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
                this.api().buttons().container().appendTo('#subject-groups-table_wrapper .col-md-6:eq(0)');
            }
        });

        console.log('Subject Groups DataTable loaded');
    });

    // SweetAlert for delete confirmation
    $(document).on('click', '.delete-subject-group-btn', function(e) {
        e.preventDefault();
        var button = $(this);
        var form = button.closest('form');
        var subjectGroupName = button.data('subject-group-name');

        Swal.fire({
            title: 'Delete Subject Group?',
            html: 'Are you sure you want to delete "<strong>' + subjectGroupName + '</strong>"?<br><br>' +
                  '<small class="text-muted">This will permanently remove the subject group and may affect:</small><br>' +
                  '<small class="text-muted">• Subject assignments</small><br>' +
                  '<small class="text-muted">• Academic records</small><br>' +
                  '<small class="text-muted">• Related academic data</small><br><br>' +
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