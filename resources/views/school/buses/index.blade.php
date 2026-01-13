@extends('layouts.main')

@section('title', 'Buses Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Buses', 'url' => '#', 'icon' => 'bx bx-bus']
        ]" />
        <h6 class="mb-0 text-uppercase">BUSES MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-bus me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">School Buses</h5>
                            </div>
                            <a href="{{ route('school.buses.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Add New Bus
                            </a>
                        </div>
                        <hr />
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table id="busesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Bus Number</th>
                                        <th>Branch</th>
                                        <th>Driver Name</th>
                                        <th>Driver Phone</th>
                                        <th>Capacity</th>
                                        <th>Students</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th class="actions-column">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($buses ?? [] as $index => $bus)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><span class="badge bg-primary">{{ $bus->bus_number }}</span></td>
                                        <td><span class="badge bg-secondary">{{ $bus->branch->name ?? 'N/A' }}</span></td>
                                        <td>{{ $bus->driver_name }}</td>
                                        <td>{{ $bus->driver_phone }}</td>
                                        <td><span class="badge bg-info">{{ $bus->capacity }}</span></td>
                                        <td><span class="badge bg-warning">{{ $bus->students_count ?? 0 }}</span></td>
                                        <td>{!! $bus->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>
                                        <td>{{ $bus->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('school.buses.show', $bus) }}" class="btn btn-sm btn-outline-info" title="View"><i class="bx bx-show"></i></a>
                                                <a href="{{ route('school.buses.assign-routes', $bus) }}" class="btn btn-sm btn-outline-success" title="Assign Routes">Assign Routes</a>
                                                <a href="{{ route('school.buses.edit', $bus) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="bx bx-edit"></i></a>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-bus-btn" title="Delete" data-bus-id="{{ $bus->getRouteKey() }}" data-bus-number="{{ $bus->bus_number }}"><i class="bx bx-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="bx bx-bus fs-1 text-muted mb-2"></i>
                                                <p class="text-muted mb-0">No buses found</p>
                                                <a href="{{ route('school.buses.create') }}" class="btn btn-primary btn-sm mt-2">
                                                    <i class="bx bx-plus me-1"></i> Add First Bus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
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
                        Are you sure you want to delete the bus "<strong id="busNumber"></strong>"? This action cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteForm" action="" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
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
        margin-right: 1px;
        padding: 0.25rem 0.4rem;
        font-size: 0.75rem;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .btn-group .btn i {
        font-size: 0.875rem;
    }

    .actions-column {
        min-width: 200px;
        white-space: nowrap;
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

    /* Responsive adjustments for mobile */
    @media (max-width: 768px) {
        .btn-group .btn {
            padding: 0.2rem 0.3rem;
            font-size: 0.7rem;
        }

        .btn-group .btn i {
            font-size: 0.8rem;
        }

        .actions-column {
            min-width: 180px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Initializing Buses DataTable...');

        // Check if table has data
        var tableRows = $('#busesTable tbody tr');
        var hasExistingData = tableRows.length > 0 && !tableRows.first().hasClass('text-center');
        console.log('Has existing data:', hasExistingData);
        console.log('Table rows count:', tableRows.length);

        // Destroy any existing DataTable instance first
        if ($.fn.DataTable.isDataTable('#busesTable')) {
            $('#busesTable').DataTable().destroy();
            console.log('Destroyed existing DataTable instance');
        }

        // Remove any existing data-bs-toggle attributes that might cause issues
        $('#busesTable').removeAttr('data-bs-toggle');

        // Initialize DataTable
        $('#busesTable').DataTable({
            processing: false,
            serverSide: false, // Explicitly disable server-side processing
            ajax: false, // Explicitly disable AJAX
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
            language: {
                emptyTable: '<div class="text-center py-4"><div class="d-flex flex-column align-items-center"><i class="bx bx-bus fs-1 text-muted mb-2"></i><p class="text-muted mb-0">No buses found</p><a href="{{ route("school.buses.create") }}" class="btn btn-primary btn-sm mt-2"><i class="bx bx-plus me-1"></i> Add First Bus</a></div></div>'
            },
            drawCallback: function() {
                console.log('Buses DataTable draw callback executed');
                // Attach event listeners to delete buttons after table draw
                $('.delete-bus-btn').on('click', function() {
                    var busId = $(this).data('bus-id');
                    var busNumber = $(this).data('bus-number');
                    $('#busNumber').text(busNumber);
                    $('#deleteForm').attr('action', '{{ url("school/buses") }}/' + busId);
                    $('#deleteModal').modal('show');
                });
            },
            initComplete: function() {
                console.log('Buses DataTable initialization complete - using client-side data only');
            },
            // Override any global defaults
            deferLoading: null,
            ajaxDataProp: null,
            ajaxUrl: null
        });
    });
</script>
@endpush