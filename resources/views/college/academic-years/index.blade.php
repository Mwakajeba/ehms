@extends('layouts.main')

@section('title', 'Academic Years Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Academic Years', 'url' => '#', 'icon' => 'bx bx-calendar']
        ]" />
        <h6 class="mb-0 text-uppercase">ACADEMIC YEARS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-calendar me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Academic Years</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info" onclick="generateNextYear()">
                                    <i class="bx bx-magic me-1"></i> Generate Next Year
                                </button>
                                <a href="{{ route('college.academic-years.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add Academic Year
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
                            <table id="academic-years-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Academic Year</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Current</th>
                                        <th>Progress</th>
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

<!-- Set Current Modal -->
<div class="modal fade" id="setCurrentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Set Academic Year as Current</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to set <strong id="currentYearName"></strong> as the current academic year?</p>
                <p class="text-muted">This will unset any other academic year that is currently marked as current.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSetCurrent">Set as Current</button>
            </div>
        </div>
    </div>
</div>

<!-- Mark Completed Modal -->
<div class="modal fade" id="markCompletedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Academic Year as Completed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark <strong id="completedYearName"></strong> as completed?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmMarkCompleted">Mark as Completed</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Academic Year</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteYearName"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#academic-years-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("college.academic-years.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'year_name', name: 'year_name', orderable: true },
            { data: 'duration', name: 'start_date', orderable: false },
            { data: 'status_badge', name: 'status', orderable: true },
            { data: 'current_badge', name: 'is_current', orderable: true },
            { data: 'progress', name: 'progress', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
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

// Set as current functionality
function setAsCurrent(id) {
    // Get the year name from the table row
    const row = $(`button[onclick="setAsCurrent(${id})"]`).closest('tr');
    const yearName = row.find('td:first').text().trim();

    $('#currentYearName').text(yearName);
    $('#setCurrentModal').modal('show');

    $('#confirmSetCurrent').off('click').on('click', function() {
        const form = $('<form>', {
            'method': 'POST',
            'action': `{{ url('/college/academic-years') }}/${id}/set-current`
        });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        $('body').append(form);
        form.submit();
    });
}

// Mark as completed functionality
function markCompleted(id) {
    // Get the year name from the table row
    const row = $(`button[onclick="markCompleted(${id})"]`).closest('tr');
    const yearName = row.find('td:first').text().trim();

    $('#completedYearName').text(yearName);
    $('#markCompletedModal').modal('show');

    $('#confirmMarkCompleted').off('click').on('click', function() {
        const form = $('<form>', {
            'method': 'POST',
            'action': `{{ url('/college/academic-years') }}/${id}/mark-completed`
        });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        $('body').append(form);
        form.submit();
    });
}

// Delete functionality
function confirmDelete(id, yearName) {
    $('#deleteYearName').text(yearName);
    $('#deleteModal').modal('show');

    $('#confirmDelete').off('click').on('click', function() {
        const form = $('<form>', {
            'method': 'POST',
            'action': `{{ url('/college/academic-years') }}/${id}`
        });
        form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': '{{ csrf_token() }}' }));
        form.append($('<input>', { 'type': 'hidden', 'name': '_method', 'value': 'DELETE' }));
        $('body').append(form);
        form.submit();
    });
}

// Generate next year functionality
function generateNextYear() {
    if (confirm('Generate the next academic year based on the current year?')) {
        $.get('{{ route("college.academic-years.generate-next") }}')
            .done(function(data) {
                // Redirect to create page with pre-filled data
                window.location.href = '{{ route("college.academic-years.create") }}' +
                    '?year_name=' + encodeURIComponent(data.year_name) +
                    '&start_date=' + encodeURIComponent(data.start_date) +
                    '&end_date=' + encodeURIComponent(data.end_date);
            })
            .fail(function() {
                alert('Failed to generate next academic year. Please try again.');
            });
    }
}
</script>
@endpush