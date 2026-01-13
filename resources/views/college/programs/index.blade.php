@extends('layouts.main')

@section('title', 'Programs Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Programs', 'url' => '#', 'icon' => 'bx bx-graduation']
        ]" />
        <h6 class="mb-0 text-uppercase">PROGRAMS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-graduation me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Academic Programs</h5>
                            </div>
                            <a href="{{ route('college.programs.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Program
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table id="programs-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Program Name</th>
                                        <th>Code</th>
                                        <th>Department</th>
                                        <th>Duration</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Created At</th>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the program <strong id="deleteProgramName"></strong>?</p>
                <div class="alert alert-warning">
                    <i class="bx bx-warning me-1"></i>
                    This action cannot be undone. All associated students and data will be affected.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Program</button>
                </form>
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Document ready, initializing Programs DataTable...');

        // Check if jQuery and DataTable are available
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded');
            return;
        }
        if (typeof $.fn.DataTable === 'undefined') {
            console.error('DataTables is not loaded');
            return;
        }

        console.log('jQuery and DataTables are available');

        // Initialize DataTable
        try {
            $('#programs-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("college.programs.data") }}',
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX Error:', error, thrown);
                        console.log('Response:', xhr.responseText);
                        console.log('Status:', xhr.status);
                        if (xhr.status === 403) {
                            alert('Access denied. Please select a branch first.');
                            window.location.href = '{{ route("change-branch") }}';
                        } else {
                            alert('Error loading data: ' + error + ' - ' + thrown);
                        }
                    }
                },
                columns: [
                    {
                        data: null,
                        name: null,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'department',
                        name: 'department',
                        orderable: false
                    },
                    {
                        data: 'duration_years',
                        name: 'duration_years',
                        render: function(data) {
                            return data ? data + ' years' : 'N/A';
                        }
                    },
                    {
                        data: 'students_count',
                        name: 'students_count',
                        orderable: false,
                        render: function(data) {
                            return '<span class="badge bg-info">' + data + '</span>';
                        }
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data) {
                            return data ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                        },
                        orderable: false
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        render: function(data) {
                            if (!data) return '';
                            var date = new Date(data);
                            return date.toLocaleDateString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: '2-digit'
                            });
                        }
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
                    console.log('Programs DataTable initialized successfully');
                }
            });

            console.log('Programs DataTable initialized');
        } catch (error) {
            console.error('Error initializing DataTable:', error);
        }
    });

    function confirmDelete(id, name) {
        $('#deleteProgramName').text(name);
        $('#deleteForm').attr('action', '{{ url("college/programs") }}/' + id);
        $('#deleteModal').modal('show');
    }
</script>
@endpush