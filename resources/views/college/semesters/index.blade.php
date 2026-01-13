@extends('layouts.main')

@section('title', 'Semesters Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Semesters', 'url' => '#', 'icon' => 'bx bx-time-five']
        ]" />
        <h6 class="mb-0 text-uppercase">SEMESTERS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-time-five me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Semesters</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('college.semesters.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add Semester
                                </a>
                            </div>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="semesters-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Semester Number</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Status</th>
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Semester</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteSemesterName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#semesters-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("college.semesters.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'number', name: 'number', orderable: true },
            { data: 'name', name: 'name', orderable: true },
            { data: 'description', name: 'description', orderable: false },
            { data: 'status_badge', name: 'status', orderable: true },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Delete confirmation
function confirmDelete(id, name) {
    $('#deleteSemesterName').text(name);
    $('#deleteForm').attr('action', '{{ url("/college/semesters") }}/' + id);
    $('#deleteModal').modal('show');
}
</script>
@endpush
