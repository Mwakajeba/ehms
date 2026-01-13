@extends('layouts.main')

@section('title', 'Edit Bus')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Buses', 'url' => route('school.buses.index'), 'icon' => 'bx bx-bus'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="mb-0 text-primary font-weight-bold">
                <i class="bx bx-bus me-2"></i>Edit School Bus
            </h4>
            <div class="badge bg-primary px-3 py-2">
                <i class="bx bx-edit-alt me-1"></i>Update Details
            </div>
        </div>
        <hr class="mb-4" style="border-top: 2px solid #007bff;">

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">
                            <i class="bx bx-edit me-2"></i>Bus Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('school.buses.update', $bus) }}" method="POST" id="busForm">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('bus_number') is-invalid @enderror"
                                               id="bus_number" name="bus_number" value="{{ old('bus_number', $bus->bus_number) }}"
                                               placeholder="e.g., BUS001, SC001, T001" required>
                                        <label for="bus_number">
                                            <i class="bx bx-hash me-1"></i>Bus Number <span class="text-danger">*</span>
                                        </label>
                                        @error('bus_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Enter a unique number/code for the bus</small>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('driver_name') is-invalid @enderror"
                                               id="driver_name" name="driver_name" value="{{ old('driver_name', $bus->driver_name) }}"
                                               placeholder="e.g., John Doe" required>
                                        <label for="driver_name">
                                            <i class="bx bx-user me-1"></i>Driver Name <span class="text-danger">*</span>
                                        </label>
                                        @error('driver_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Full name of the bus driver</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control @error('capacity') is-invalid @enderror"
                                               id="capacity" name="capacity" value="{{ old('capacity', $bus->capacity) }}"
                                               placeholder="e.g., 50" min="1" required>
                                        <label for="capacity">
                                            <i class="bx bx-group me-1"></i>Capacity <span class="text-danger">*</span>
                                        </label>
                                        @error('capacity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Maximum number of passengers</small>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('driver_phone') is-invalid @enderror"
                                               id="driver_phone" name="driver_phone" value="{{ old('driver_phone', $bus->driver_phone) }}"
                                               placeholder="e.g., +255 123 456 789" required>
                                        <label for="driver_phone">
                                            <i class="bx bx-phone me-1"></i>Driver Phone <span class="text-danger">*</span>
                                        </label>
                                        @error('driver_phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Contact phone number of the driver</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('model') is-invalid @enderror"
                                               id="model" name="model" value="{{ old('model', $bus->model) }}"
                                               placeholder="e.g., Toyota Hiace, Nissan">
                                        <label for="model">
                                            <i class="bx bx-car me-1"></i>Model
                                        </label>
                                        @error('model')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Bus model or type (optional)</small>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control @error('registration_number') is-invalid @enderror"
                                               id="registration_number" name="registration_number" value="{{ old('registration_number', $bus->registration_number) }}"
                                               placeholder="e.g., T 123 ABC">
                                        <label for="registration_number">
                                            <i class="bx bx-id-card me-1"></i>Registration Number
                                        </label>
                                        @error('registration_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">Official registration number (optional)</small>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               id="is_active" name="is_active" value="1"
                                               {{ old('is_active', $bus->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="is_active">
                                            <i class="bx bx-check-circle me-1"></i>Active Status
                                        </label>
                                    </div>
                                    <small class="text-muted">Check to mark the bus as active for transportation</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <a href="{{ route('school.buses.index') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bx bx-arrow-back me-2"></i>Back to Buses
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="bx bx-save me-2"></i>Update Bus
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card shadow-lg border-0 rounded-lg mb-3">
                    <div class="card-header bg-gradient-info text-white">
                        <h6 class="mb-0">
                            <i class="bx bx-info-circle me-2"></i>Bus Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bx bx-calendar me-2 text-primary"></i>
                                <strong>Created:</strong>
                            </div>
                            <span class="text-muted">{{ $bus->created_at->format('M d, Y \a\t h:i A') }}</span>
                        </div>
                        @if($bus->updated_at != $bus->created_at)
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bx bx-time me-2 text-warning"></i>
                                <strong>Last Updated:</strong>
                            </div>
                            <span class="text-muted">{{ $bus->updated_at->format('M d, Y \a\t h:i A') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow-lg border-0 rounded-lg">
                    <div class="card-header bg-gradient-success text-white">
                        <h6 class="mb-0">
                            <i class="bx bx-bulb me-2"></i>Tips
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6>What are School Buses?</h6>
                        <p class="small text-muted mb-3">
                            School buses are vehicles used for student transportation with assigned drivers and capacity limits.
                        </p>
                        <ul class="list-unstyled small">
                            <li><i class="bx bx-check text-success me-1"></i>Ensure bus number is unique</li>
                            <li><i class="bx bx-check text-success me-1"></i>Verify driver contact details</li>
                            <li><i class="bx bx-check text-success me-1"></i>Check capacity against school needs</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
    }
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .form-floating > label {
        font-weight: 500;
    }
    .btn {
        transition: all 0.3s ease;
    }
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }
    .badge {
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

        console.log('Edit bus form loaded');
    });
</script>
@endpush