@extends('layouts.main')

@section('title', 'Create New Bus')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Buses', 'url' => route('school.buses.index'), 'icon' => 'bx bx-bus'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW BUS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <!-- Bus Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New School Bus</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.buses.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="bus_number" class="form-label">Bus Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('bus_number') is-invalid @enderror"
                                               id="bus_number" name="bus_number" value="{{ old('bus_number') }}"
                                               placeholder="e.g., BUS001, SC001, T001" required>
                                        @error('bus_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter a unique number/code for the bus</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="driver_name" class="form-label">Driver Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('driver_name') is-invalid @enderror"
                                               id="driver_name" name="driver_name" value="{{ old('driver_name') }}"
                                               placeholder="e.g., John Doe" required>
                                        @error('driver_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Full name of the bus driver</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="capacity" class="form-label">Capacity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                               id="capacity" name="capacity" value="{{ old('capacity') }}"
                                               placeholder="e.g., 50" min="1" required>
                                        @error('capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Maximum number of passengers</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="driver_phone" class="form-label">Driver Phone <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('driver_phone') is-invalid @enderror"
                                               id="driver_phone" name="driver_phone" value="{{ old('driver_phone') }}"
                                               placeholder="e.g., +255 123 456 789" required>
                                        @error('driver_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Contact phone number of the driver</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="model" class="form-label">Model</label>
                                        <input type="text" class="form-control @error('model') is-invalid @enderror"
                                               id="model" name="model" value="{{ old('model') }}"
                                               placeholder="e.g., Toyota Hiace, Nissan">
                                        @error('model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Bus model or type (optional)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="registration_number" class="form-label">Registration Number</label>
                                        <input type="text" class="form-control @error('registration_number') is-invalid @enderror"
                                               id="registration_number" name="registration_number" value="{{ old('registration_number') }}"
                                               placeholder="e.g., T 123 ABC">
                                        @error('registration_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Official registration number (optional)</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <div class="form-check">
                                            <input type="hidden" name="is_active" value="0">
                                            <input class="form-check-input" type="checkbox"
                                                   id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                        <div class="form-text">Check to mark the bus as active for transportation</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.buses.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Buses
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Bus
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
                            <h6>What are School Buses?</h6>
                            <p class="small text-muted">
                                School buses are vehicles used for student transportation. Each bus has a unique number,
                                driver information, and capacity for organized transportation services.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Required Fields:</h6>
                            <ul class="small text-muted">
                                <li><strong>Bus Number:</strong> Unique identifier (e.g., "BUS001")</li>
                                <li><strong>Driver Name:</strong> Full name of the bus driver</li>
                                <li><strong>Driver Phone:</strong> Contact number for the driver</li>
                                <li><strong>Capacity:</strong> Maximum number of passengers</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Optional Fields:</h6>
                            <ul class="small text-muted">
                                <li><strong>Model:</strong> Bus make and model (e.g., "Toyota Hiace")</li>
                                <li><strong>Registration Number:</strong> Official vehicle registration</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Examples:</h6>
                            <ul class="small text-muted">
                                <li>Bus Number: BUS001, Driver: John Doe, Capacity: 50</li>
                                <li>Bus Number: SC002, Driver: Jane Smith, Capacity: 45</li>
                                <li>Bus Number: T003, Driver: Mike Johnson, Capacity: 60</li>
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
        // Auto-uppercase for bus number
        $('#bus_number').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Auto-capitalize first letter for driver name
        $('#driver_name').on('input', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
        });

        // Auto-uppercase for registration number
        $('#registration_number').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        console.log('Create bus form loaded');
    });
</script>
@endpush