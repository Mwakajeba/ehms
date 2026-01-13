@extends('layouts.main')

@section('title', 'Bus Stops Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Bus Stops', 'url' => '#', 'icon' => 'bx bx-map-pin']
        ]" />
        <h6 class="mb-0 text-uppercase">BUS STOPS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-map-pin me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Bus Stops</h5>
                            </div>
                            <a href="{{ route('school.bus-stops.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add Bus Stop
                            </a>
                        </div>
                        <hr />

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                            <table id="bus-stops-table" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>SN</th>
                                        <th>Stop Code</th>
                                        <th>Stop Name</th>
                                        <th>Fare</th>
                                        <th>Sequence</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
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
                Are you sure you want to delete the bus stop "<strong id="busStopName"></strong>"? This action cannot be undone.
                <div class="alert alert-danger mt-3" id="deleteWarning" style="display: none;">
                    <i class="bx bx-error-circle me-1"></i>
                    <strong>Cannot Delete:</strong> This bus stop is assigned to <span id="routeCount"></span> route(s). Please remove it from all routes before deleting.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="deleteButton">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .badge {
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables with server-side processing
        $('#bus-stops-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.bus-stops.index") }}',
                type: 'GET',
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX Error:', error, thrown);
                    console.error('Response:', xhr.responseText);
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center', width: '5%'},
                {data: 'stop_code', name: 'stop_code', className: 'text-center'},
                {data: 'stop_name', name: 'stop_name'},
                {data: 'formatted_fare', name: 'fare', className: 'text-end'},
                {data: 'sequence_order', name: 'sequence_order', className: 'text-center'},
                {data: 'status_badge', name: 'is_active', orderable: false, className: 'text-center'},
                {data: 'formatted_date', name: 'created_at', className: 'text-center'},
                {data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center'}
            ],
            responsive: true,
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
            order: [[1, 'asc']], // Sort by stop code (column 1) ascending
            language: {
                search: "Search bus stops:",
                lengthMenu: "Show _MENU_ bus stops per page",
                info: "Showing _START_ to _END_ of _TOTAL_ bus stops",
                infoEmpty: "Showing 0 to 0 of 0 bus stops",
                infoFiltered: "(filtered from _MAX_ total bus stops)",
                zeroRecords: "No bus stops found",
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            initComplete: function() {
                console.log('DataTables initialized successfully');
            },
            drawCallback: function() {
                // Attach event listeners to delete buttons after table draw
                $('.delete-bus-stop-btn').on('click', function() {
                    var busStopId = $(this).data('bus-stop-id');
                    var busStopName = $(this).data('bus-stop-name');
                    var routeCount = $(this).data('route-count');

                    $('#busStopName').text(busStopName);
                    $('#deleteForm').attr('action', '{{ url("school/bus-stops") }}/' + busStopId);

                    // Show warning if bus stop is assigned to routes
                    if (routeCount > 0) {
                        $('#routeCount').text(routeCount);
                        $('#deleteWarning').show();
                        $('#deleteButton').prop('disabled', true).text('Cannot Delete');
                    } else {
                        $('#deleteWarning').hide();
                        $('#deleteButton').prop('disabled', false).text('Delete');
                    }

                    $('#deleteModal').modal('show');
                });
            }
        });
    });
</script>
@endpush