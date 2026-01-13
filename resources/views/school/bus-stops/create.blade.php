@extends('layouts.main')

@section('title', 'Create New Bus Stop')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Bus Stops', 'url' => route('school.bus-stops.index'), 'icon' => 'bx bx-map-pin'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW BUS STOP</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <!-- Bus Stop Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Bus Stop</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.bus-stops.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Stop Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name') }}"
                                               placeholder="e.g., Main Gate, Downtown, North Station" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter a descriptive name for the bus stop</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Stop Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                                               id="code" name="code" value="{{ old('code') }}"
                                               placeholder="e.g., BS001, MG001, DT001" required>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Enter a unique code for the bus stop (e.g., BS001)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="fare" class="form-label">Fare</label>
                                        <div class="input-group">
                                            <span class="input-group-text">TZS</span>
                                            <input type="number" class="form-control @error('fare') is-invalid @enderror"
                                                   id="fare" name="fare" value="{{ old('fare') }}"
                                                   placeholder="0.00" step="0.01" min="0" pattern="[0-9]+(\.[0-9]{1,2})?"
                                                   inputmode="decimal">
                                        </div>
                                        @error('fare')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Optional: Enter the fare for this bus stop (numbers only, e.g., 5000.00)</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="sequence_order" class="form-label">Sequence Order</label>
                                        <input type="number" class="form-control @error('sequence_order') is-invalid @enderror"
                                               id="sequence_order" name="sequence_order" value="{{ old('sequence_order', 0) }}"
                                               placeholder="0" min="0">
                                        @error('sequence_order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Order in which this stop appears in routes (lower numbers first)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="is_active" class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                        <div class="form-text">Inactive stops won't be available for new route assignments</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="latitude" class="form-label">Latitude</label>
                                        <input type="number" class="form-control @error('latitude') is-invalid @enderror"
                                               id="latitude" name="latitude" value="{{ old('latitude') }}"
                                               placeholder="-6.792354" step="any" min="-90" max="90">
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">GPS latitude coordinate (optional)</div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="longitude" class="form-label">Longitude</label>
                                        <input type="number" class="form-control @error('longitude') is-invalid @enderror"
                                               id="longitude" name="longitude" value="{{ old('longitude') }}"
                                               placeholder="39.208328" step="any" min="-180" max="180">
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">GPS longitude coordinate (optional)</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="3"
                                                  placeholder="Describe the bus stop location, landmarks, or any special notes">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Optional: Provide details about the bus stop location or special instructions</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.bus-stops.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Bus Stops
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Bus Stop
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
                            <h6>What are Bus Stops?</h6>
                            <p class="small text-muted">
                                Bus stops define the pickup and drop-off points for school transportation routes.
                                Each stop can be assigned to multiple routes and helps organize student transportation.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Required Fields:</h6>
                            <ul class="small text-muted">
                                <li><strong>Stop Name:</strong> Descriptive name (e.g., "Main Gate")</li>
                                <li><strong>Stop Code:</strong> Unique identifier (e.g., "BS001")</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Optional Fields:</h6>
                            <ul class="small text-muted">
                                <li><strong>Fare:</strong> Transportation cost for this stop</li>
                                <li><strong>Sequence Order:</strong> Order in route stops</li>
                                <li><strong>GPS Coordinates:</strong> For mapping purposes</li>
                                <li><strong>Description:</strong> Additional location details</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Examples:</h6>
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
        // Auto-capitalize first letter for stop name
        $('#name').on('input', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
        });

        // Auto-uppercase for stop code
        $('#code').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Format fare input - allow only numbers and decimal point
        $('#fare').on('input', function() {
            let value = $(this).val();
            // Remove any non-numeric characters except decimal point
            value = value.replace(/[^0-9.]/g, '');
            // Ensure only one decimal point
            let parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            // Limit to 2 decimal places
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }
            $(this).val(value);
        });

        // Prevent non-numeric keys except decimal point and navigation keys
        $('#fare').on('keypress', function(e) {
            let charCode = (e.which) ? e.which : e.keyCode;
            // Allow: backspace, delete, tab, escape, enter, decimal point, and numbers
            if ($.inArray(charCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                // Allow: Ctrl+A, Command+A
                (charCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                // Allow: home, end, left, right, down, up
                (charCode >= 35 && charCode <= 40)) {
                // Let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (charCode < 48 || charCode > 57)) && (charCode < 96 || charCode > 105)) {
                e.preventDefault();
            }
        });

        console.log('Create bus stop form loaded');
    });
</script>
@endpush