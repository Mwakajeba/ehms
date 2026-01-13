@extends('layouts.main')

@section('title', 'Routes Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Routes', 'url' => '#', 'icon' => 'bx bx-map']
        ]" />
        <h6 class="mb-0 text-uppercase">ROUTES MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-map me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Transportation Routes</h5>
                            </div>
                            <a href="{{ route('school.routes.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Route
                            </a>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="routesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Route Code</th>
                                        <th>Route Name</th>
                                        <th>Bus Stops</th>
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
                        Are you sure you want to delete the route "<strong id="routeName"></strong>"? This action cannot be undone.
                        <div class="alert alert-danger mt-3" id="deleteWarning" style="display: none;">
                            <i class="bx bx-error-circle me-1"></i>
                            <strong>Cannot Delete:</strong> This route is assigned to <span id="busCount"></span> bus(es). Please remove all bus assignments before deleting.
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
        console.log('Initializing DataTable...');

        // Destroy any existing DataTable instance first
        if ($.fn.DataTable.isDataTable('#routesTable')) {
            $('#routesTable').DataTable().destroy();
            console.log('Destroyed existing DataTable instance');
        }

        // Remove any existing data-bs-toggle attributes that might cause issues
        $('#routesTable').removeAttr('data-bs-toggle');

        $('#routesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.routes.index") }}',
                type: 'GET',
                data: function(d) {
                    d.simple = 'true'; // Add the simple parameter to trigger server-side processing
                }
            },
            deferRender: true,
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            ordering: true,
            searching: true,
            paging: true,
            info: true,
            lengthChange: true,
            destroy: true, // Allow reinitialization
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'route_code', orderable: true },
                { data: 'route_name', orderable: true },
                { data: 'bus_stops_count', orderable: false, searchable: false },
                { data: 'students_count', orderable: false, searchable: false },
                { data: 'formatted_date', orderable: true },
                { data: 'actions', orderable: false, searchable: false }
            ],
            language: {
                emptyTable: '<div class="text-center py-4"><div class="d-flex flex-column align-items-center"><i class="bx bx-map fs-1 text-muted mb-2"></i><p class="text-muted mb-0">No routes found</p><a href="{{ route("school.routes.create") }}" class="btn btn-primary btn-sm mt-2"><i class="bx bx-plus me-1"></i> Add First Route</a></div></div>',
                processing: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
            },
            drawCallback: function() {
                console.log('DataTable draw callback executed');
                // Attach event listeners to delete buttons after table draw
                $('.delete-route-btn').on('click', function() {
                    var routeId = $(this).data('route-id');
                    var routeName = $(this).data('route-name');
                    var busCount = $(this).data('bus-count');

                    $('#routeName').text(routeName);
                    $('#deleteForm').attr('action', '{{ url("school/routes") }}/' + routeId);

                    // Show warning if route is assigned to buses
                    if (busCount > 0) {
                        $('#busCount').text(busCount);
                        $('#deleteWarning').show();
                        $('#deleteButton').prop('disabled', true).text('Cannot Delete');
                    } else {
                        $('#deleteWarning').hide();
                        $('#deleteButton').prop('disabled', false).text('Delete');
                    }

                    $('#deleteModal').modal('show');
                });
            },
            initComplete: function() {
                console.log('DataTable initialization complete - using server-side processing');
                console.log('DataTable settings:', this.api().settings()[0]);
            }
        });
    });
</script>
@endpush