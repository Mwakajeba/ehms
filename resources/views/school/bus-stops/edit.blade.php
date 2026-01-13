@extends('layouts.main')

@section('title', 'Edit Bus Stop')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Bus Stops', 'url' => route('school.bus-stops.index'), 'icon' => 'bx bx-map-pin'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT BUS STOP</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Bus Stop</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.bus-stops.update', $busStop) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information Row -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="section-title">
                                        <i class="bx bx-info-circle me-2"></i>Basic Information
                                    </h6>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-label required">Stop Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name', $busStop->stop_name) }}"
                                               placeholder="e.g., Main Gate, Downtown, North Station" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Enter a descriptive name for the bus stop</small>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="code" class="form-label required">Stop Code</label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                                               id="code" name="code" value="{{ old('code', $busStop->stop_code) }}"
                                               placeholder="e.g., BS001, MG001, DT001" required>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Enter a unique code for the bus stop (e.g., BS001)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing and Order Row -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="section-title">
                                        <i class="bx bx-money me-2"></i>Pricing & Order
                                    </h6>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="fare" class="form-label">Fare Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">TZS</span>
                                            <input type="number" class="form-control @error('fare') is-invalid @enderror"
                                                   id="fare" name="fare" value="{{ old('fare', $busStop->fare) }}"
                                                   placeholder="0.00" step="0.01" min="0" pattern="[0-9]+(\.[0-9]{1,2})?"
                                                   inputmode="decimal">
                                        </div>
                                        @error('fare')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Optional: Enter the fare for this bus stop (numbers only, e.g., 5000.00)</small>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="sequence_order" class="form-label">Sequence Order</label>
                                        <input type="number" class="form-control @error('sequence_order') is-invalid @enderror"
                                               id="sequence_order" name="sequence_order" value="{{ old('sequence_order', $busStop->sequence_order) }}"
                                               placeholder="0" min="0">
                                        @error('sequence_order')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Order in which this stop appears in routes (lower numbers first)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Row -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="section-title">
                                        <i class="bx bx-toggle-right me-2"></i>Status & Settings
                                    </h6>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="is_active" class="form-label">Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   id="is_active" name="is_active" value="1"
                                                   {{ old('is_active', $busStop->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="is_active">
                                                {{ old('is_active', $busStop->is_active) ? 'Active' : 'Inactive' }}
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">Inactive stops won't be available for new route assignments</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Location Coordinates Row -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="section-title">
                                        <i class="bx bx-map-pin me-2"></i>Location Coordinates
                                    </h6>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="latitude" class="form-label">Latitude</label>
                                        <input type="number" class="form-control @error('latitude') is-invalid @enderror"
                                               id="latitude" name="latitude" value="{{ old('latitude', $busStop->latitude) }}"
                                               placeholder="-6.792354" step="any" min="-90" max="90">
                                        @error('latitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">GPS latitude coordinate (optional)</small>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label for="longitude" class="form-label">Longitude</label>
                                        <input type="number" class="form-control @error('longitude') is-invalid @enderror"
                                               id="longitude" name="longitude" value="{{ old('longitude', $busStop->longitude) }}"
                                               placeholder="39.208328" step="any" min="-180" max="180">
                                        @error('longitude')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">GPS longitude coordinate (optional)</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Row -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="section-title">
                                        <i class="bx bx-file me-2"></i>Additional Information
                                    </h6>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="3"
                                                  placeholder="Describe the bus stop location, landmarks, or any special notes">{{ old('description', $busStop->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Optional: Provide details about the bus stop location or special instructions</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.bus-stops.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Bus Stops
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Bus Stop
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
                            <i class="bx bx-info-circle me-1 text-info"></i> Bus Stop Details
                        </h6>
                        <hr />

                        <div class="info-item mb-3">
                            <div class="info-label">Created</div>
                            <div class="info-value">{{ $busStop->created_at->format('M d, Y \a\t h:i A') }}</div>
                        </div>

                        @if($busStop->updated_at != $busStop->created_at)
                        <div class="info-item mb-3">
                            <div class="info-label">Last Updated</div>
                            <div class="info-value">{{ $busStop->updated_at->format('M d, Y \a\t h:i A') }}</div>
                        </div>
                        @endif

                        <div class="info-item mb-3">
                            <div class="info-label">Routes Assigned</div>
                            <div class="info-value">
                                <span class="badge bg-primary fs-6">{{ $busStop->routes->count() }}</span>
                            </div>
                        </div>

                        <div class="info-item mb-4">
                            <div class="info-label">Current Status</div>
                            <div class="info-value">
                                @if($busStop->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </div>
                        </div>

                        <hr />

                        <div class="help-section">
                            <h6 class="mb-2">
                                <i class="bx bx-help-circle me-1"></i>What are Bus Stops?
                            </h6>
                            <p class="small text-muted mb-0">
                                Bus stops define the pickup and drop-off points for school transportation routes.
                                They can be assigned to multiple routes and have specific fare amounts.
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
    /* System-focused styling - clean and professional */
    .section-title {
        color: #495057;
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
        display: flex;
        align-items: center;
    }

    .section-title i {
        color: #0d6efd;
        font-size: 1.1rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-label.required::after {
        content: " *";
        color: #dc3545;
        font-weight: bold;
    }

    .form-control {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        color: #6c757d;
        font-weight: 500;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .form-check-label {
        margin-left: 0.5rem;
        font-weight: 500;
        color: #495057;
    }

    .form-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .invalid-feedback {
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Card styling for system interface */
    .card {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-body {
        padding: 2rem;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1.5rem;
    }

    /* Button styling */
    .btn {
        padding: 0.5rem 1.5rem;
        font-weight: 500;
        border-radius: 0.375rem;
        border: 1px solid transparent;
        transition: all 0.15s ease-in-out;
    }

    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5c636a;
        border-color: #565e64;
    }

    /* Row spacing for better organization */
    .row {
        margin-left: -0.75rem;
        margin-right: -0.75rem;
    }

    .row > [class*="col-"] {
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem;
        }

        .section-title {
            font-size: 0.95rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 0.5rem;
        }
    }

    /* Focus states for accessibility */
    .form-control:focus,
    .form-check-input:focus {
        outline: 0;
    }

    /* Loading state styling */
    .form-control:disabled,
    .btn:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    /* Sidebar information styling */
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f8f9fa;
    }

    .info-item:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        flex: 0 0 auto;
        margin-right: 1rem;
    }

    .info-value {
        color: #6c757d;
        font-size: 0.875rem;
        flex: 1;
        text-align: right;
    }

    .help-section h6 {
        color: #495057;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .help-section p {
        line-height: 1.4;
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

        // Dynamic status label update
        $('#is_active').on('change', function() {
            const label = $(this).next('.form-check-label');
            if ($(this).is(':checked')) {
                label.text('Active');
            } else {
                label.text('Inactive');
            }
        });

        // Form validation feedback
        $('form').on('submit', function(e) {
            const requiredFields = $('input[required], select[required], textarea[required]');
            let isValid = true;

            requiredFields.each(function() {
                if (!$(this).val().trim()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                // Show error message
                if (!$('.alert-danger').length) {
                    $('.card-body').prepend(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bx bx-error-circle me-1"></i>
                            Please fill in all required fields.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `);
                }
                // Scroll to first error
                const firstError = $('.is-invalid').first();
                if (firstError.length) {
                    $('html, body').animate({
                        scrollTop: firstError.offset().top - 100
                    }, 500);
                }
            }
        });

        // Remove validation errors on input
        $('.form-control').on('input', function() {
            if ($(this).hasClass('is-invalid') && $(this).val().trim()) {
                $(this).removeClass('is-invalid');
            }
        });

        console.log('Edit bus stop form loaded with enhanced functionality');
    });
</script>
@endpush