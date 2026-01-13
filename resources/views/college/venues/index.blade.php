@extends('layouts.main')

@section('title', 'Venues')

@section('content')
<div class="page-content" style="margin-top: 70px; margin-left: 235px; margin-right: 20px;">
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="row mb-3">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 me-2">
                        <i class="bx bx-home-alt me-1"></i> Dashboard
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.index') }}" class="btn btn-light btn-sm rounded-pill px-3 mx-2">
                        <i class="bx bx-book-reader me-1"></i> College Management
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <span class="btn btn-primary btn-sm rounded-pill px-3 ms-2">
                        <i class="bx bx-building me-1"></i> Venues
                    </span>
                </nav>
            </div>
        </div>

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="bx bx-building me-2"></i>Venues Management
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">College</a></li>
                            <li class="breadcrumb-item active">Venues</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Venues</p>
                                <h4 class="fs-22 fw-semibold mb-0" id="totalVenues">-</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                    <i class="bx bx-building text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Lecture Halls</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-info" id="lectureHalls">-</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                    <i class="bx bx-chalkboard text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Labs</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-success" id="labs">-</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                    <i class="bx bx-test-tube text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Capacity</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-warning" id="totalCapacity">-</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle rounded fs-3">
                                    <i class="bx bx-user text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-list-ul me-2"></i>All Venues
                                </h5>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('college.timetables.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="bx bx-calendar-alt me-1"></i> Timetables
                                </a>
                                <a href="{{ route('college.venues.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add Venue
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Venue Type</label>
                                <select class="form-select" id="filterType">
                                    <option value="">All Types</option>
                                    <option value="lecture_hall">Lecture Hall</option>
                                    <option value="lab">Laboratory</option>
                                    <option value="computer_lab">Computer Lab</option>
                                    <option value="seminar_room">Seminar Room</option>
                                    <option value="auditorium">Auditorium</option>
                                    <option value="classroom">Classroom</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Building</label>
                                <input type="text" class="form-control" id="filterBuilding" placeholder="Search building...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Capacity</label>
                                <select class="form-select" id="filterCapacity">
                                    <option value="">All Sizes</option>
                                    <option value="small">Small (≤30)</option>
                                    <option value="medium">Medium (31-100)</option>
                                    <option value="large">Large (101-200)</option>
                                    <option value="xlarge">Very Large (>200)</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-secondary w-100" id="resetFilters">
                                    <i class="bx bx-reset me-1"></i> Reset
                                </button>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="venuesTable" class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Building</th>
                                        <th>Floor</th>
                                        <th>Type</th>
                                        <th>Capacity</th>
                                        <th>Facilities</th>
                                        <th>Status</th>
                                        <th style="width: 120px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
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
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Delete Venue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this venue?</p>
                <p class="text-danger small"><i class="bx bx-info-circle me-1"></i> This may affect existing timetables using this venue.</p>
                <input type="hidden" id="deleteVenueId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bx bx-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#venuesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("college.venues.data") }}',
            data: function(d) {
                d.venue_type = $('#filterType').val();
                d.building = $('#filterBuilding').val();
                d.is_active = $('#filterStatus').val();
                d.capacity = $('#filterCapacity').val();
            }
        },
        columns: [
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'building', name: 'building' },
            { data: 'floor', name: 'floor' },
            { data: 'venue_type_badge', name: 'venue_type', orderable: true },
            { data: 'capacity', name: 'capacity' },
            { data: 'facilities_badges', name: 'facilities', orderable: false, searchable: false },
            { data: 'status_badge', name: 'is_active', orderable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        drawCallback: function() {
            updateStats();
        }
    });

    // Filter change handlers
    $('#filterType, #filterStatus, #filterCapacity').on('change', function() {
        table.draw();
    });

    $('#filterBuilding').on('keyup', function() {
        table.draw();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filterType, #filterStatus, #filterCapacity, #filterBuilding').val('');
        table.draw();
    });

    // Delete venue
    $(document).on('click', '.delete-btn', function() {
        $('#deleteVenueId').val($(this).data('id'));
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        var id = $('#deleteVenueId').val();
        $.ajax({
            url: '/college/venues/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#deleteModal').modal('hide');
                    table.draw();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'An error occurred');
            }
        });
    });

    // Update statistics
    function updateStats() {
        var info = table.page.info();
        $('#totalVenues').text(info.recordsTotal);
    }
});
</script>
@endpush
