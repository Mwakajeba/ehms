@extends('layouts.main')

@section('title', 'Create New Student')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Students', 'url' => route('school.students.index'), 'icon' => 'bx bx-id-card'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW STUDENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Student Record</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.students.store') }}" method="POST" enctype="multipart/form-data" id="studentForm">
                            @csrf

                            <!-- Academic Information Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-book me-2"></i> Academic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Academic Year</label>
                                                @php
                                                    $currentAcademicYear = \App\Models\School\AcademicYear::current();
                                                @endphp
                                                @if($currentAcademicYear)
                                                    <div class="input-group">
                                                        <input type="text" class="form-control bg-light" value="{{ $currentAcademicYear->year_name }}" readonly>
                                                        <span class="input-group-text bg-success text-white">
                                                            <i class="bx bx-check-circle"></i> Current
                                                        </span>
                                                    </div>
                                                    <div class="form-text text-success">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        New students will be automatically assigned to the current academic year.
                                                    </div>
                                                @else
                                                    <div class="alert alert-warning">
                                                        <i class="bx bx-error-circle me-1"></i>
                                                        No current academic year is set. Please <a href="{{ route('school.academic-years.index') }}" class="alert-link">set a current academic year</a> first.
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="admission_number" class="form-label fw-bold">Admission Number</label>
                                                <input type="text" class="form-control @error('admission_number') is-invalid @enderror"
                                                       id="admission_number" name="admission_number" value="{{ old('admission_number') }}"
                                                       placeholder="ADM001">
                                                @error('admission_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Auto-generated when class is selected
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                                <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                                    <option value="">Select Class</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="stream_id" class="form-label fw-bold">Stream <span class="text-danger">*</span></label>
                                                <select class="form-select @error('stream_id') is-invalid @enderror" id="stream_id" name="stream_id" required disabled>
                                                    <option value="">Select a class first</option>
                                                </select>
                                                @error('stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="discount_type" class="form-label fw-bold">Discount Type</label>
                                                <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type">
                                                    <option value="">No Discount</option>
                                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                </select>
                                                @error('discount_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Select the type of discount to apply to this student
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="discount_value" class="form-label fw-bold">Discount Value</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control @error('discount_value') is-invalid @enderror"
                                                           id="discount_value" name="discount_value" value="{{ old('discount_value') }}"
                                                           min="0" step="0.01" placeholder="0.00">
                                                    <span class="input-group-text" id="discount-unit">Amount</span>
                                                </div>
                                                @error('discount_value')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Enter the discount amount or percentage
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information Section -->
                            <div class="card border-info mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-user me-2"></i> Personal Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                                       id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="last_name" class="form-label fw-bold">Last Name</label>
                                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                                       id="last_name" name="last_name" value="{{ old('last_name') }}">
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="date_of_birth" class="form-label fw-bold">Date of Birth</label>
                                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="gender" class="form-label fw-bold">Gender</label>
                                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                                    <option value="">Select Gender</option>
                                                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                                    <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                @error('gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="admission_date" class="form-label fw-bold">Admission Date</label>
                                                <input type="date" class="form-control @error('admission_date') is-invalid @enderror"
                                                       id="admission_date" name="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}">
                                                @error('admission_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="address" class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                                <textarea class="form-control @error('address') is-invalid @enderror"
                                                          id="address" name="address" rows="2" required>{{ old('address') }}</textarea>
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($errors->has('name'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-1"></i>
                                    <strong>Error:</strong> {{ $errors->first('name') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <!-- Boarding & Transport Section -->
                            <div class="card border-warning mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-bus me-2"></i> Boarding & Transport
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="boarding_type" class="form-label fw-bold">Boarding Type</label>
                                                <select class="form-select @error('boarding_type') is-invalid @enderror" id="boarding_type" name="boarding_type">
                                                    <option value="">Select Type</option>
                                                    <option value="day" {{ old('boarding_type') == 'day' ? 'selected' : '' }}>Day Scholar</option>
                                                    <option value="boarding" {{ old('boarding_type') == 'boarding' ? 'selected' : '' }}>Boarding</option>
                                                </select>
                                                @error('boarding_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Transport Section (only show if Day Scholar) -->
                                    <div id="transportSection" style="display: none;">
                                        <hr class="my-3">
                                        <h6 class="text-muted mb-3">
                                            <i class="bx bx-bus me-1"></i> Transport Details
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="has_transport" class="form-label fw-bold">Has Transport?</label>
                                                    <select class="form-select @error('has_transport') is-invalid @enderror" id="has_transport" name="has_transport">
                                                        <option value="">Select Option</option>
                                                        <option value="yes" {{ old('has_transport') == 'yes' ? 'selected' : '' }}>Yes</option>
                                                        <option value="no" {{ old('has_transport') == 'no' ? 'selected' : '' }}>No</option>
                                                    </select>
                                                    @error('has_transport')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6" id="busStopSection" style="display: none;">
                                                <div class="mb-3">
                                                    <label for="bus_stop_id" class="form-label fw-bold">Bus Stop</label>
                                                    <select class="form-select @error('bus_stop_id') is-invalid @enderror" id="bus_stop_id" name="bus_stop_id">
                                                        <option value="">Select Bus Stop</option>
                                                        <!-- Bus stops will be loaded via AJAX -->
                                                    </select>
                                                    @error('bus_stop_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Photo Upload Section -->
                            <div class="card border-success mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-camera me-2"></i> Photo Upload
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="passport_photo" class="form-label fw-bold">Passport-size Photo</label>
                                                <input type="file" class="form-control @error('passport_photo') is-invalid @enderror"
                                                       id="passport_photo" name="passport_photo" accept="image/*">
                                                @error('passport_photo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Upload a passport-size photo (JPG, PNG, max 2MB)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('school.students.index') }}" class="btn btn-outline-secondary btn-lg">
                                            <i class="bx bx-arrow-back me-2"></i> Back to Students
                                        </a>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                                                <i class="bx bx-refresh me-1"></i> Reset Form
                                            </button>
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="bx bx-save me-2"></i> Create Student
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-text {
        font-size: 0.875rem;
    }

    h6 {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
    }

    /* Enhanced Card Styling */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.75rem;
        transition: box-shadow 0.15s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        font-weight: 600;
        background-color: #f8f9fa !important;
        color: #495057 !important;
    }

    /* Section-specific header backgrounds */
    .border-primary .card-header { background-color: #f8f9fa !important; }
    .border-info .card-header { background-color: #f8f9fa !important; }
    .border-warning .card-header { background-color: #f8f9fa !important; }
    .border-success .card-header { background-color: #f8f9fa !important; }

    .card-body {
        padding: 1.5rem;
    }

    /* Form Field Enhancements */
    .form-label {
        margin-bottom: 0.5rem;
        color: #495057;
        font-weight: 500;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Input Group Styling */
    .input-group-text {
        border-radius: 0 0.5rem 0.5rem 0;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
    }

    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    /* Alert Styling */
    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    /* Transport Section Animation */
    #transportSection {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.gap-2 {
            justify-content: center;
        }
    }

    /* Icon Styling */
    .bx {
        font-size: 1.1em;
    }

    /* Required Field Indicator */
    .text-danger {
        font-weight: bold;
    }

    /* Form Text Enhancement */
    .form-text {
        font-style: italic;
        margin-top: 0.25rem;
    }

    /* Card Border Colors - Subtle */
    .border-primary { border-color: #e3f2fd !important; }
    .border-info { border-color: #e0f2fe !important; }
    .border-warning { border-color: #fff3cd !important; }
    .border-success { border-color: #d1edff !important; }
    .border-secondary { border-color: #f8f9fa !important; }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Enhanced student creation form loaded');

        // Initialize form validation
        initializeFormValidation();

        // Auto-capitalize names
        $('#first_name, #last_name').on('input', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
        });

        // Handle boarding type change
        $('#boarding_type').on('change', function() {
            const boardingType = $(this).val();
            console.log('Boarding type changed to:', boardingType);
            toggleTransportSection(boardingType);
        });

        // Handle transport option change
        $('#has_transport').on('change', function() {
            const hasTransport = $(this).val();
            console.log('Has transport changed to:', hasTransport);
            toggleBusStopSection(hasTransport);
        });

        // Handle discount type change
        $('#discount_type').on('change', function() {
            const discountType = $(this).val();
            console.log('Discount type changed to:', discountType);
            updateDiscountUnit(discountType);
        });

        // Initialize state based on current values
        const initialBoardingType = $('#boarding_type').val();
        const initialTransport = $('#has_transport').val();
        const initialDiscountType = $('#discount_type').val();
        console.log('Initial boarding type:', initialBoardingType);
        console.log('Initial transport:', initialTransport);
        console.log('Initial discount type:', initialDiscountType);

        if (initialBoardingType) {
            toggleTransportSection(initialBoardingType);
        }
        if (initialTransport) {
            toggleBusStopSection(initialTransport);
        }
        if (initialDiscountType) {
            updateDiscountUnit(initialDiscountType);
        }

        // Handle class change to load streams dynamically and generate admission number
        $('#class_id').on('change', function() {
            const classId = $(this).val();
            const streamSelect = $('#stream_id');

            if (classId) {
                // Enable stream select and load streams for this class
                streamSelect.prop('disabled', false);
                
                // Clear current selection
                streamSelect.val('').trigger('change');
                
                // Load streams for the selected class
                loadStreamsForClass(classId);

                // Auto-generate admission number
                if (!$('#admission_number').val()) {
                    const timestamp = Date.now();
                    const random = Math.floor(Math.random() * 100).toString().padStart(2, '0');
                    const admissionNumber = 'ADM' + classId + timestamp.toString().slice(-4) + random;
                    $('#admission_number').val(admissionNumber);
                    showToast('Admission number generated: ' + admissionNumber, 'success');
                }
            } else {
                // Disable stream select when no class is selected
                streamSelect.prop('disabled', true);
                streamSelect.val('').trigger('change');
            }
        });

        // File upload preview
        $('#passport_photo').on('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // MB
                if (fileSize > 2) {
                    showToast('File size must be less than 2MB', 'error');
                    $(this).val('');
                    return;
                }

                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    showToast('Please select a valid image file (JPG, PNG)', 'error');
                    $(this).val('');
                    return;
                }

                showToast('Photo selected successfully', 'success');
            }
        });
    });

    // Function to toggle transport section
    function toggleTransportSection(boardingType) {
        if (boardingType === 'day') {
            $('#transportSection').slideDown(300);
        } else {
            $('#transportSection').slideUp(300, function() {
                $('#has_transport').val('').trigger('change');
                $('#busStopSection').slideUp(300);
                $('#bus_stop_id').val('').trigger('change');
            });
        }
    }

    // Function to toggle bus stop section
    function toggleBusStopSection(hasTransport) {
        if (hasTransport === 'yes') {
            $('#busStopSection').slideDown(300, function() {
                // Load bus stops when section becomes visible
                loadBusStops();
            });
        } else {
            $('#busStopSection').slideUp(300, function() {
                $('#bus_stop_id').val('').trigger('change');
            });
        }
    }

    // Function to load bus stops via AJAX
    function loadBusStops() {
        const busStopSelect = $('#bus_stop_id');

        // Clear any existing options and show loading
        busStopSelect.empty();
        busStopSelect.append('<option value="">Loading bus stops...</option>');
        busStopSelect.prop('disabled', true);

        // Make AJAX call to get bus stops
        $.ajax({
            url: '{{ route("school.api.students.bus-stops") }}',
            method: 'GET',
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Bus stops loaded:', response);

                // Clear loading option
                busStopSelect.empty();

                if (response.bus_stops && response.bus_stops.length > 0) {
                    // Add default option
                    busStopSelect.append('<option value="">Select Bus Stop</option>');
                    
                    // Add bus stop options
                    response.bus_stops.forEach(function(busStop) {
                        const optionText = busStop.stop_name + ' (' + busStop.stop_code + ') - ' + busStop.fare;
                        busStopSelect.append(`<option value="${busStop.id}">${optionText}</option>`);
                    });
                    
                    busStopSelect.prop('disabled', false);
                    showToast(`Loaded ${response.bus_stops.length} bus stop(s)`, 'success');
                } else {
                    busStopSelect.append('<option value="">No bus stops available</option>');
                    busStopSelect.prop('disabled', true);
                    showToast('No active bus stops found', 'warning');
                }

                // Re-initialize Select2 after populating options
                busStopSelect.select2({
                    placeholder: 'Select a bus stop...',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    minimumInputLength: 0
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading bus stops:', error);
                busStopSelect.empty();
                busStopSelect.append('<option value="">Error loading bus stops</option>');
                busStopSelect.prop('disabled', true);
                showToast('Error loading bus stops. Please try again.', 'error');
            }
        });
    }

    // Function to load streams for a class
    function loadStreamsForClass(classId) {
        const streamSelect = $('#stream_id');

        // Show loading state
        streamSelect.html('<option value="">Loading streams...</option>');
        streamSelect.prop('disabled', true);

        // Make AJAX call to get streams for this class
        $.ajax({
            url: '{{ route("school.api.students.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                let options = '<option value="">Select Stream</option>';

                if (response.streams && response.streams.length > 0) {
                    response.streams.forEach(function(stream) {
                        options += `<option value="${stream.id}">${stream.name}</option>`;
                    });
                    streamSelect.prop('disabled', false);
                    showToast(`Loaded ${response.streams.length} stream(s) for this class`, 'success');
                } else {
                    options = '<option value="">No streams available for this class</option>';
                    streamSelect.prop('disabled', true);
                    showToast('No streams found for this class', 'warning');
                }

                streamSelect.html(options);
                
                // Re-initialize Select2 after populating options
                streamSelect.select2({
                    placeholder: 'Select a stream...',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    minimumInputLength: 0
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.html('<option value="">Error loading streams</option>');
                streamSelect.prop('disabled', true);
                showToast('Error loading streams. Please try again.', 'error');
            }
        });
    }

    // Form validation initialization
    function initializeFormValidation() {
        $('#studentForm').on('submit', function(e) {
            let isValid = true;
            const requiredFields = ['class_id', 'stream_id', 'first_name'];

            requiredFields.forEach(field => {
                const element = $('#' + field);
                if (!element.val()) {
                    element.addClass('is-invalid');
                    isValid = false;
                } else {
                    element.removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                showToast('Please fill in all required fields', 'error');
                // Scroll to first error
                $('html, body').animate({
                    scrollTop: $('.is-invalid').first().offset().top - 100
                }, 500);
            }
        });
    }

    // Reset form function
    function resetForm() {
        if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
            $('#studentForm')[0].reset();

            // Reset all Select2 elements
            $('#class_id, #stream_id, #bus_stop_id, #boarding_type, #has_transport, #gender, #discount_type').val('').trigger('change');

            // Disable stream select since no class is selected
            $('#stream_id').prop('disabled', true);

            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').hide();

            // Reset all sections
            $('#transportSection').hide();
            $('#busStopSection').hide();

            showToast('Form has been reset', 'info');
        }
    }

    // Function to update discount unit based on type
    function updateDiscountUnit(discountType) {
        const discountUnit = $('#discount-unit');
        const discountValue = $('#discount_value');

        if (discountType === 'percentage') {
            discountUnit.text('%');
            discountValue.attr('max', '100');
            discountValue.attr('placeholder', '0.00');
        } else if (discountType === 'fixed') {
            discountUnit.text('Amount');
            discountValue.removeAttr('max');
            discountValue.attr('placeholder', '0.00');
        } else {
            discountUnit.text('Amount');
            discountValue.removeAttr('max');
            discountValue.attr('placeholder', '0.00');
        }
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        const toastColors = {
            success: '#198754',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#0dcaf0'
        };

        // Create toast element
        const toast = $(`
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                 style="background-color: ${toastColors[type]}; position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        // Add to body and show
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        // Remove after shown
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
</script>
@endpush