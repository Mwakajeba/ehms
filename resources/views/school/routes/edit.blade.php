@extends('layouts.main')

@section('title', 'Edit Route')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Routes', 'url' => route('school.routes.index'), 'icon' => 'bx bx-map'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT ROUTE</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Transportation Route</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.routes.update', $route) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="route_name" class="form-label">Route Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('route_name') is-invalid @enderror"
                                               id="route_name" name="route_name" value="{{ old('route_name', $route->route_name) }}"
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
                                               id="route_code" name="route_code" value="{{ old('route_code', $route->route_code) }}"
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
                                                               {{ in_array($busStop->id, old('bus_stops', $route->busStops->pluck('id')->toArray())) ? 'checked' : '' }}>
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
                                    <i class="bx bx-save me-1"></i> Update Route
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
                            <i class="bx bx-info-circle me-1 text-info"></i> Route Details
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <strong>Created:</strong><br>
                            <span class="text-muted">{{ $route->created_at->format('M d, Y \a\t h:i A') }}</span>
                        </div>
                        @if($route->updated_at != $route->created_at)
                        <div class="mb-3">
                            <strong>Last Updated:</strong><br>
                            <span class="text-muted">{{ $route->updated_at->format('M d, Y \a\t h:i A') }}</span>
                        </div>
                        @endif
                        <div class="mb-3">
                            <h6>What are Transportation Routes?</h6>
                            <p class="small text-muted">
                                Transportation routes define the paths that school buses follow to serve students.
                            </p>
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

        console.log('Edit route form loaded');
    });
</script>
@endpush