@extends('layouts.main')

@section('title', 'Create New Route')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Routes', 'url' => route('school.routes.index'), 'icon' => 'bx bx-map'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW ROUTE</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <!-- Bus Stops Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-map-pin me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Bus Stops Management</h5>
                        </div>
                        <hr />

                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Bus Stops:</strong> Define pickup and drop-off points for your transportation routes.
                            <a href="{{ route('school.bus-stops.index') }}" class="alert-link">Manage Bus Stops</a>
                        </div>

                        <div class="text-center">
                            <a href="{{ route('school.bus-stops.create') }}" class="btn btn-outline-primary">
                                <i class="bx bx-plus me-1"></i> Add New Bus Stop
                            </a>
                            <a href="{{ route('school.bus-stops.index') }}" class="btn btn-outline-secondary ms-2">
                                <i class="bx bx-list-ul me-1"></i> View All Bus Stops
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Route Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Transportation Route</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.routes.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="route_name" class="form-label">Route Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('route_name') is-invalid @enderror"
                                               id="route_name" name="route_name" value="{{ old('route_name') }}"
                                               placeholder="e.g., Route A, Downtown Route, North Route" required>
                                        @error('route_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter a descriptive name for the transportation route</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="route_code" class="form-label">Route Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('route_code') is-invalid @enderror"
                                               id="route_code" name="route_code" value="{{ old('route_code') }}"
                                               placeholder="e.g., RT001, A001, NR001" required>
                                        @error('route_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter a unique code for the route (e.g., RT001)</div>
                                    </div>
                                </div>
                            </div>



                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="form-label">Associated Bus Stops</label>
                                        <div class="row">
                                            @forelse($busStops as $busStop)
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                               id="bus_stop_{{ $busStop->id }}" name="bus_stops[]"
                                                               value="{{ $busStop->id }}"
                                                               {{ in_array($busStop->id, old('bus_stops', [])) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="bus_stop_{{ $busStop->id }}">
                                                            {{ $busStop->stop_name }}
                                                            <br><small class="text-muted">{{ $busStop->stop_code }} - {{ number_format($busStop->fare ?? 0, 2) }} TZS</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <div class="alert alert-warning">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        No bus stops available. <a href="{{ route('school.bus-stops.create') }}">Create a bus stop first</a>.
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                        <div class="form-text">Select the bus stops that belong to this route. You can select multiple stops.</div>
                                        @error('bus_stops')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.routes.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Routes
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Route
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1 text-info"></i> Information
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <h6>What are Transportation Routes?</h6>
                            <p class="small text-muted">
                                Transportation routes define the paths that school buses or transportation services follow
                                to pick up and drop off students. Each route has a unique code and name, and can have multiple stops and serve different areas.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>What are Bus Stops?</h6>
                            <p class="small text-muted">
                                Bus stops are the pickup and drop-off points along transportation routes.
                                Create bus stops first, then assign them to routes for organized student transportation.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Required Fields:</h6>
                            <ul class="small text-muted">
                                <li><strong>Route Name:</strong> Descriptive name (e.g., "Downtown Route")</li>
                                <li><strong>Route Code:</strong> Unique identifier (e.g., "RT001")</li>
                                <li><strong>Description:</strong> Optional details about the route</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Examples:</h6>
                            <ul class="small text-muted">
                                <li>Route Code: RT001, Name: Downtown Area</li>
                                <li>Route Code: NR002, Name: North Route</li>
                                <li>Route Code: ER003, Name: East Route</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Bus Stop Examples:</h6>
                            <ul class="small text-muted">
                                <li>Main Gate (MG001) - School entrance</li>
                                <li>Downtown (DT001) - City center area</li>
                                <li>North Station (NS001) - Residential north</li>
                            </ul>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-text {
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-capitalize first letter for route name
        $('#route_name').on('input', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
        });

        // Auto-uppercase for route code
        $('#route_code').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        console.log('Create route form loaded');
    });
</script>
@endpush