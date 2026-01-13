@extends('layouts.main')

@section('title', 'Generate Fee Invoice')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumbs -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Fee Invoices', 'url' => route('college.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Generate Invoice', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">GENERATE COLLEGE FEE INVOICE</h6>
        <hr />

        <!-- Progress Steps -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="step active" id="step-config">
                                    <div class="step-circle bg-primary text-white">
                                        <i class="bx bx-cog"></i>
                                    </div>
                                    <div class="step-title">Settings</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="step" id="step-validation">
                                    <div class="step-circle bg-light text-muted">
                                        <i class="bx bx-check-circle"></i>
                                    </div>
                                    <div class="step-title">Validate</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="step" id="step-generation">
                                    <div class="step-circle bg-light text-muted">
                                        <i class="bx bx-receipt"></i>
                                    </div>
                                    <div class="step-title">Generate</div>
                                </div>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 25%; transition: width 0.5s ease;" id="progress-bar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="fee-invoice-form" method="POST" action="{{ route('college.fee-invoices.store') }}">
            @csrf

            <div class="row">
                <!-- Main Configuration Panel -->
                <div class="col-12 col-lg-8">
                    <!-- Basic Settings Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-cog me-2"></i>Invoice Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="program_id" class="form-label fw-bold">
                                            <i class="bx bx-building text-primary me-1"></i>Program <span class="text-danger">*</span>
                                        </label>
                                        <select name="program_id" id="program_id" class="form-control form-control-lg @error('program_id') is-invalid @enderror" required>
                                            <option value="">Select Program</option>
                                            @foreach($programs as $program)
                                                <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                                    <i class="bx bx-building"></i> {{ $program->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('program_id')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="fee_group_id" class="form-label fw-bold">
                                            <i class="bx bx-group text-success me-1"></i>Fee Group <span class="text-danger">*</span>
                                        </label>
                                        <select name="fee_group_id" id="fee_group_id" class="form-control form-control-lg @error('fee_group_id') is-invalid @enderror" required>
                                            <option value="">Select Fee Group</option>
                                            @foreach($feeGroups as $feeGroup)
                                                <option value="{{ $feeGroup->id }}" {{ old('fee_group_id') == $feeGroup->id ? 'selected' : '' }}>
                                                    <i class="bx bx-group"></i> {{ $feeGroup->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('fee_group_id')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text">
                                            <small class="text-muted">
                                                <i class="bx bx-info-circle me-1"></i>
                                                Fee group determines the accounting treatment for this invoice
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="fee_period" class="form-label fw-bold">
                                            <i class="bx bx-calendar text-warning me-1"></i>Fee Period <span class="text-danger">*</span>
                                        </label>
                                        <select name="fee_period" id="fee_period" class="form-control form-control-lg @error('fee_period') is-invalid @enderror" required>
                                            <option value="">Select Period</option>
                                            <option value="Semester 1" {{ old('fee_period') == 'Semester 1' ? 'selected' : '' }}>
                                                <i class="bx bx-calendar-week"></i> Semester 1
                                            </option>
                                            <option value="Semester 2" {{ old('fee_period') == 'Semester 2' ? 'selected' : '' }}>
                                                <i class="bx bx-calendar-week"></i> Semester 2
                                            </option>
                                            <option value="Full year" {{ old('fee_period') == 'Full year' ? 'selected' : '' }}>
                                                <i class="bx bx-calendar-week"></i> Full Year
                                            </option>
                                        </select>
                                        @error('fee_period')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="due_date" class="form-label fw-bold">
                                            <i class="bx bx-calendar-check text-info me-1"></i>Due Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" name="due_date" id="due_date" class="form-control form-control-lg @error('due_date') is-invalid @enderror"
                                               value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}" min="{{ date('Y-m-d') }}" required>
                                        @error('due_date')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="generation_type" class="form-label fw-bold">
                                            <i class="bx bx-select-multiple text-info me-1"></i>Generation Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="generation_type" id="generation_type" class="form-control form-control-lg @error('generation_type') is-invalid @enderror" required>
                                            <option value="">Select Type</option>
                                            <option value="all_students" {{ old('generation_type', 'all_students') == 'all_students' ? 'selected' : '' }}>
                                                <i class="bx bx-group"></i> All Students (Bulk)
                                            </option>
                                            <option value="specific_students" {{ old('generation_type') == 'specific_students' ? 'selected' : '' }}>
                                                <i class="bx bx-user"></i> Specific Students
                                            </option>
                                        </select>
                                        @error('generation_type')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Student Selection (Hidden by default) -->
                            <div class="row" id="student-selection" style="display: none;">
                                <div class="col-12">
                                    <div class="form-group mb-3">
                                        <label for="student_ids" class="form-label fw-bold">
                                            <i class="bx bx-user text-danger me-1"></i>Select Students <span class="text-danger">*</span>
                                        </label>
                                        <select name="student_ids[]" id="student_ids" class="form-control form-control-lg @error('student_ids') is-invalid @enderror" multiple>
                                            <option value="">Select Students</option>
                                        </select>
                                        @error('student_ids')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text">
                                            <small class="text-muted">
                                                <i class="bx bx-info-circle me-1"></i>
                                                Hold Ctrl (or Cmd on Mac) to select multiple students
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Card -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-show me-2 text-secondary"></i>Invoice Preview
                                <button id="manual-preview-btn" class="btn btn-sm btn-outline-primary float-end">
                                    <i class="bx bx-refresh me-1"></i>Refresh Preview
                                </button>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="invoice-preview" class="text-center py-4">
                                <i class="bx bx-receipt text-muted" style="font-size: 4rem;"></i>
                                <h5 class="text-muted mt-3">Invoice Preview</h5>
                                <p class="text-muted small">Configure your settings above to see the preview</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Information -->
                <div class="col-12 col-lg-4">
                    <!-- Quick Stats Card -->
                    <div class="card shadow-sm mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-bar-chart me-2"></i>Quick Statistics
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="p-2">
                                        <h4 class="text-success mb-1">{{ $programs->count() }}</h4>
                                        <small class="text-muted">Programs</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2">
                                        <h4 class="text-primary mb-1">{{ $feeGroups->count() }}</h4>
                                        <small class="text-muted">Fee Groups</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Information Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-info-circle me-2"></i>Generation Guide
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6 class="text-info"><i class="bx bx-bulb me-1"></i>All Students</h6>
                                <p class="small text-muted mb-2">Creates invoices for all active students in the selected program automatically.</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-warning"><i class="bx bx-user me-1"></i>Specific Students</h6>
                                <p class="small text-muted mb-2">Creates invoices for selected students only. Useful for individual cases.</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-success"><i class="bx bx-group me-1"></i>Fee Groups</h6>
                                <p class="small text-muted mb-2">Required selection that determines accounting treatment and fee categorization.</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-success"><i class="bx bx-calendar me-1"></i>Fee Periods</h6>
                                <p class="small text-muted mb-2">Semester-based fee collection periods for college students.</p>
                            </div>
                            <div class="alert alert-light small border">
                                <i class="bx bx-info-circle text-info me-1"></i>
                                <strong>Note:</strong> Invoices are generated based on active fee settings for the selected program and period.
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-play-circle me-2"></i>Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bx bx-check-circle me-2"></i>Start Validation Process
                                </button>
                                <a href="{{ route('college.fee-invoices.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Invoices
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .step {
        position: relative;
    }

    .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.2rem;
        border: 3px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .step.active .step-circle {
        border-color: #0d6efd;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
    }

    .step-title {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6c757d;
    }

    .step.completed .step-circle {
        background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
        border-color: #198754 !important;
    }

    .step.completed .step-title {
        color: #198754 !important;
        font-weight: 600 !important;
    }

    .card {
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border: none;
        padding: 1rem 1.25rem;
    }

    .form-control-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control-lg:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
    }

    .btn-outline-secondary {
        border: 2px solid #6c757d;
        color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        transform: translateY(-2px);
    }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        min-height: 48px;
    }

    .select2-container--bootstrap4 .select2-selection:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    .bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
    }

    .bg-success {
        background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
    }

    .bg-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%) !important;
    }

    .bg-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }

    .validation-results .table th {
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.75rem 0.5rem;
    }

    .validation-results .table td {
        padding: 0.5rem;
        vertical-align: middle;
    }

    .validation-results .table-success th {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%) !important;
        border-color: #8fd19e !important;
    }

    .validation-results .table-warning th {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
        border-color: #ffecb5 !important;
    }

    .validation-results .table-danger th {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%) !important;
        border-color: #f1aeb5 !important;
    }

    .validation-results .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
    }

    .validation-results .fw-bold {
        font-weight: 700 !important;
        color: #198754;
    }
</style>
@endpush

@push('scripts')
<script>
// Basic script load check
console.log('Scripts section loaded successfully');

// Global AJAX setup for CSRF token
console.log('Fee invoice create script loaded');
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Helper function to get period display text
function getPeriodText(period) {
    const periodNames = {
        'Semester 1': 'Semester 1',
        'Semester 2': 'Semester 2',
        'Full year': 'Full year'
    };
    return periodNames[period] || period;
}
</script>
<script>
console.log('Main script starting');
try {
    console.log('Checking jQuery availability...');
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded!');
        throw new Error('jQuery not available');
    }
    console.log('jQuery version:', $.fn.jquery);

    console.log('Checking Select2 availability...');
    if (typeof $.fn.select2 === 'undefined') {
        console.error('Select2 is not loaded!');
        throw new Error('Select2 not available');
    }
    console.log('Select2 available');

    $(document).ready(function() {
        console.log('Document ready, initializing fee invoice form');
        try {
    // Initialize Select2 with custom formatting
    $('#program_id, #fee_group_id, #fee_period, #generation_type').select2({
        theme: 'bootstrap4',
        placeholder: function() {
            return $(this).data('placeholder') || 'Please select...';
        },
        allowClear: true,
        width: '100%',
        escapeMarkup: function (markup) {
            return markup;
        },
        templateResult: function (data) {
            if (data.loading) return data.text;
            return data.text;
        },
        templateSelection: function (data) {
            return data.text;
        }
    });

    // Initialize Select2 for student selection
    $('#student_ids').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: 'Select students...',
        allowClear: true
    });

    // Generation type change handler
    $('#generation_type').change(function() {
        const generationType = $(this).select2('val') || $(this).val();
        console.log('Generation type changed to:', generationType);

        if (generationType === 'specific_students') {
            $('#student-selection').slideDown(300);
            $('#student_ids').prop('required', true);
            loadStudents();
        } else {
            $('#student-selection').slideUp(300);
            $('#student_ids').prop('required', false);
            $('#student_ids').val(null).trigger('change');
        }

        updatePreview();
    });

    // Program or fee period change handler
    $('#program_id, #fee_period, #fee_group_id, #due_date, #student_ids').change(function() {
        console.log('Field changed:', $(this).attr('id'));
        if ($('#generation_type').select2('val') === 'specific_students') {
            loadStudents();
        }
        updatePreview();
    });

    // Also listen for Select2 change events
    $('#program_id, #fee_period, #fee_group_id, #generation_type').on('select2:select', function() {
        console.log('Select2 changed:', $(this).attr('id'));
        if ($(this).attr('id') === 'generation_type' && $(this).select2('val') === 'specific_students') {
            $('#student-selection').slideDown(300);
            $('#student_ids').prop('required', true);
            loadStudents();
        } else if ($(this).attr('id') === 'generation_type' && $(this).select2('val') === 'all_students') {
            $('#student-selection').slideUp(300);
            $('#student_ids').prop('required', false);
            $('#student_ids').val(null).trigger('change');
        }
        updatePreview();
    });

    // Fallback: trigger preview on any form input change
    $('#fee-invoice-form input, #fee-invoice-form select').on('change input', function() {
        console.log('Form input changed:', $(this).attr('id'));
        setTimeout(updatePreview, 500); // Debounce
    });

    // Load students via AJAX
    function loadStudents() {
        const programId = $('#program_id').select2('val') || $('#program_id').val();
        const feePeriod = $('#fee_period').select2('val') || $('#fee_period').val();
        console.log('Loading students for program:', programId, 'period:', feePeriod);

        if (programId && feePeriod) {
            $.ajax({
                url: '{{ route("college.fee-invoices.get-students") }}',
                type: 'GET',
                data: { program_id: programId, fee_period: feePeriod },
                beforeSend: function() {
                    $('#student_ids').prop('disabled', true);
                },
                success: function(data) {
                    $('#student_ids').empty().append('<option value="">Select Students</option>');
                    $.each(data.students || data, function(key, student) {
                        $('#student_ids').append('<option value="' + student.id + '">' + student.name + ' (' + (student.student_number || student.admission_number) + ')</option>');
                    });
                    $('#student_ids').trigger('change').prop('disabled', false);
                },
                error: function() {
                    $('#student_ids').prop('disabled', false);
                    toastr.error('Failed to load students. Please try again.');
                }
            });
        }
    }

    // Update invoice preview
    function updatePreview() {
        // For Select2, we need to check the selected values differently
        const programId = $('#program_id').select2('val') || $('#program_id').val();
        const feeGroupId = $('#fee_group_id').select2('val') || $('#fee_group_id').val();
        const feePeriod = $('#fee_period').select2('val') || $('#fee_period').val();
        const dueDate = $('#due_date').val();
        const generationType = $('#generation_type').select2('val') || $('#generation_type').val();

        console.log('updatePreview called with:', {
            programId: programId || 'empty',
            feeGroupId: feeGroupId || 'empty',
            feePeriod: feePeriod || 'empty',
            dueDate: dueDate || 'empty',
            generationType: generationType || 'empty'
        });

        // Check if all required fields are filled
        const allFieldsFilled = programId && feeGroupId && feePeriod && dueDate && generationType;
        console.log('All fields filled:', allFieldsFilled);

        if (allFieldsFilled) {
            console.log('All fields filled, calling preview endpoint');
            // Show loading state
            $('#invoice-preview').html(`
                <div class="text-center py-4">
                    <i class="bx bx-loader-alt bx-spin text-primary" style="font-size: 2rem;"></i>
                    <h6 class="text-primary mt-3">Loading Preview...</h6>
                    <p class="text-muted small">Fetching invoice details</p>
                </div>
            `);

            // Prepare form data for preview
            const previewData = {
                program_id: programId,
                fee_group_id: feeGroupId,
                fee_period: feePeriod,
                due_date: dueDate,
                generation_type: generationType
            };

            // Add student IDs if specific students selected
            if (generationType === 'specific_students') {
                const selectedStudents = $('#student_ids').val();
                if (selectedStudents && selectedStudents.length > 0) {
                    previewData.student_ids = selectedStudents;
                }
            }

            // Call preview endpoint
            $.ajax({
                url: '{{ route("college.fee-invoices.preview") }}',
                type: 'POST',
                data: previewData,
                timeout: 10000,
                success: function(response) {
                    console.log('Preview response:', response);
                    if (response.html) {
                        $('#invoice-preview').html(response.html);
                    } else {
                        // Fallback to basic preview if no HTML returned
                        showBasicPreview(programId, feeGroupId, feePeriod, dueDate, generationType);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Preview error:', xhr, status, error);
                    // Show basic preview on error
                    showBasicPreview(programId, feeGroupId, feePeriod, dueDate, generationType);
                }
            });
        } else {
            $('#invoice-preview').html(`
                <i class="bx bx-receipt text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Invoice Preview</h5>
                <p class="text-muted small">Configure your settings above to see the preview</p>
            `);
        }
    }

    // Fallback basic preview function
    function showBasicPreview(programId, feeGroupId, feePeriod, dueDate, generationType) {
        const programText = $('#program_id option:selected').text();
        const feeGroupText = $('#fee_group_id option:selected').text();
        console.log('Showing basic preview for:', programText, feeGroupText, feePeriod, generationType);

        let previewText = '';

        if (generationType === 'all_students') {
            previewText = `
                <div class="text-center">
                    <i class="bx bx-group text-success" style="font-size: 3rem;"></i>
                    <h5 class="text-success mt-3">Bulk Invoice Generation</h5>
                    <p class="text-muted">Will generate invoices for all active students in the selected program</p>
                    <div class="alert alert-info">
                        <strong>Program:</strong> ${programText}<br>
                        <strong>Fee Group:</strong> ${feeGroupText}<br>
                        <strong>Period:</strong> ${getPeriodText(feePeriod)}<br>
                        <strong>Due Date:</strong> ${new Date(dueDate).toLocaleDateString()}<br>
                        <strong>Generation Type:</strong> All Students (Bulk)
                    </div>
                </div>
            `;
        } else {
            previewText = `
                <div class="text-center">
                    <i class="bx bx-user text-primary" style="font-size: 3rem;"></i>
                    <h5 class="text-primary mt-3">Specific Students Invoice</h5>
                    <p class="text-muted">Will generate invoices for selected students only</p>
                    <div class="alert alert-warning">
                        <strong>Program:</strong> ${programText}<br>
                        <strong>Fee Group:</strong> ${feeGroupText}<br>
                        <strong>Period:</strong> ${getPeriodText(feePeriod)}<br>
                        <strong>Due Date:</strong> ${new Date(dueDate).toLocaleDateString()}<br>
                        <strong>Generation Type:</strong> Specific Students
                    </div>
                </div>
            `;
        }

        $('#invoice-preview').html(previewText);
    }

    // Form validation and submission
    $('#fee-invoice-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        console.log('Form submitted, starting validation process');

        const generationType = $('#generation_type').select2('val') || $('#generation_type').val();
        const programId = $('#program_id').select2('val') || $('#program_id').val();
        const feeGroupId = $('#fee_group_id').select2('val') || $('#fee_group_id').val();
        const feePeriod = $('#fee_period').select2('val') || $('#fee_period').val();
        const dueDate = $('#due_date').val();

        console.log('Form values:', {
            generationType,
            programId,
            feeGroupId,
            feePeriod,
            dueDate
        });

        if (!programId || !feeGroupId || !feePeriod || !dueDate || !generationType) {
            console.log('Missing required fields');
            toastr.error('Please fill in all required fields.');
            return false;
        }

        if (generationType === 'specific_students' && !$('#student_ids').val()) {
            console.log('No students selected for specific students');
            toastr.error('Please select at least one student.');
            return false;
        }

        console.log('Starting invoice generation');
        startInvoiceGeneration();

        function startInvoiceGeneration() {
            console.log('startInvoiceGeneration called');
            // Prepare form data
            let formData = $('#fee-invoice-form').serializeArray();

            // Convert back to query string
            const formDataString = $.param(formData);
            console.log('Form data:', formDataString);

            // Start validation step immediately
            updateProgress('validation');

            // Disable form
            const submitBtn = $('#fee-invoice-form').find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Validating...');

            // Disable all form inputs
            $('#fee-invoice-form input, #fee-invoice-form select, #fee-invoice-form button').prop('disabled', true);

            // Show processing notification
            Swal.fire({
                title: 'Processing...',
                text: 'Validating invoice generation request',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            console.log('Making AJAX request to validate endpoint');
            // Submit form via AJAX to validation endpoint first
            $.ajax({
                url: '{{ route("college.fee-invoices.validate-invoices") }}',
                type: 'POST',
                data: formDataString,
                timeout: 30000,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('Validation response:', response);
                    // Close loading alert
                    Swal.close();

                    // Show validation success
                    updateProgress('validation');
                    submitBtn.html('<i class="bx bx-check me-2"></i>Validation Complete!');

                    // Check if validation failed
                    if (!response.success) {
                        // Show error message with debug info
                        let errorHtml = `
                            <div class="alert alert-danger">
                                <h6 class="alert-heading mb-2">
                                    <i class="bx bx-error-circle me-1"></i>Validation Failed
                                </h6>
                                <p class="mb-2">${response.message}</p>
                        `;

                        if (response.debug_info) {
                            errorHtml += `
                                <div class="small text-muted">
                                    <strong>Debug Information:</strong><br>
                                    Program ID: ${response.debug_info.program_id}<br>
                                    Company ID: ${response.debug_info.company_id}<br>
                                    Branch ID: ${response.debug_info.branch_id || 'None'}<br>
                                    Total students in program: ${response.debug_info.total_students_in_program}<br>
                                    Active students in program: ${response.debug_info.active_students_in_program}<br>
                                    Student statuses found: ${response.debug_info.student_statuses.join(', ')}<br>
                                </div>
                            `;
                        }

                        errorHtml += `</div>`;

                        $('#invoice-preview').html(errorHtml);

                        // Reset button
                        submitBtn.prop('disabled', false)
                            .removeClass('btn-success')
                            .addClass('btn-primary')
                            .attr('type', 'submit')
                            .html(originalText);

                        // Re-enable form
                        $('#fee-invoice-form input, #fee-invoice-form select').prop('disabled', false);

                        return;
                    }

                    // Display validation results
                    displayValidationResults(response);

                    // Check if there are any students that will be created
                    let hasStudentsToCreate = false;
                    if (response.type === 'bulk') {
                        hasStudentsToCreate = response.students.some(student => student.status === 'will_create');
                    } else {
                        hasStudentsToCreate = response.selected_students && response.selected_students.some(student => student.status === 'will_create');
                    }

                    // Only show "Generate Invoices" button if there are students to create
                    if (hasStudentsToCreate) {
                        submitBtn.prop('disabled', false)
                            .removeClass('btn-primary')
                            .addClass('btn-success')
                            .attr('type', 'button')
                            .attr('id', 'generate-btn')
                            .html('<i class="bx bx-plus me-2"></i>Generate Invoices');

                        // Set up generate button click handler
                        $('#generate-btn').off('click').on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            startActualGeneration(response);
                        });
                    } else {
                        // No students to create, show message and keep button disabled
                        submitBtn.prop('disabled', true)
                            .removeClass('btn-primary')
                            .addClass('btn-secondary')
                            .attr('type', 'button')
                            .html('<i class="bx bx-check-circle me-2"></i>All Invoices Already Exist');
                    }

                    // Re-enable form inputs for potential modifications
                    $('#fee-invoice-form input, #fee-invoice-form select').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Validation error:', xhr, status, error);
                    // Close loading alert
                    Swal.close();

                    // Reset form on error
                    updateProgress('config');
                    submitBtn.prop('disabled', false)
                        .removeAttr('id')
                        .attr('type', 'submit')
                        .removeClass('btn-success')
                        .addClass('btn-primary')
                        .html(originalText);
                    $('#fee-invoice-form input, #fee-invoice-form select, #fee-invoice-form button').prop('disabled', false);

                    // Reset preview
                    updatePreview();

                    let errorMessage = 'An error occurred. Please try again.';
                    let errorTitle = 'Error';

                    if (xhr.status === 422) {
                        // Validation errors
                        const errors = xhr.responseJSON.errors;
                        errorMessage = 'Validation failed:\n';
                        for (let field in errors) {
                            errorMessage += `- ${errors[field][0]}\n`;
                        }
                        errorTitle = 'Validation Error';
                    } else if (status === 'timeout') {
                        errorMessage = 'Request timed out. Please try again.';
                        errorTitle = 'Timeout Error';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    console.log('Showing error:', errorTitle, errorMessage);
                    Swal.fire({
                        title: errorTitle,
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        }
    });

    // Function to display validation results
    function displayValidationResults(validationData) {
        let html = `
            <div class="validation-results">
                <div class="text-center mb-4">
                    <i class="bx bx-check-circle text-success" style="font-size: 2rem;"></i>
                    <h5 class="text-success mt-2">Validation Complete</h5>
                    <p class="text-muted">Review the invoice details below before generating</p>
                </div>
        `;

        if (validationData.type === 'bulk') {
            html += `
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bx bx-group me-2"></i>Bulk Invoice Generation Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Program:</strong> ${validationData.program_name}</p>
                                <p><strong>Fee Group:</strong> ${validationData.fee_group_name}</p>
                                <p><strong>Period:</strong> ${getPeriodText(validationData.fee_period)}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Students:</strong> ${validationData.total_students}</p>
                                <p><strong>Due Date:</strong> ${new Date(validationData.due_date).toLocaleDateString()}</p>
                                <p><strong>Total Amount:</strong> ${validationData.currency_symbol}${validationData.total_amount}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Student Details:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-start">Student Name</th>
                                            <th class="text-start">Student Number</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">Due Date</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;

            validationData.students.forEach(student => {
                let statusBadge = '';
                let statusClass = '';

                switch(student.status) {
                    case 'will_create':
                        statusBadge = '<span class="badge bg-success">Ready to Create</span>';
                        statusClass = '';
                        break;
                    case 'already_exists':
                        statusBadge = '<span class="badge bg-warning">Already Exists</span>';
                        statusClass = 'table-warning';
                        break;
                    case 'no_fee_settings':
                        statusBadge = '<span class="badge bg-danger">No Fee Settings</span>';
                        statusClass = 'table-danger';
                        break;
                    default:
                        statusBadge = '<span class="badge bg-secondary">' + student.status + '</span>';
                        statusClass = 'table-secondary';
                }

                html += `
                    <tr class="${statusClass}">
                        <td class="text-start">${student.name}</td>
                        <td class="text-start">${student.student_number}</td>
                        <td class="text-end fw-bold">${validationData.currency_symbol}${(student.amount || 0).toLocaleString()}</td>
                        <td class="text-center">${new Date(validationData.due_date).toLocaleDateString()}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `;
            });

            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Display students who will receive new invoices
            const newStudents = validationData.students.filter(student => student.status === 'will_create');
            if (newStudents.length > 0) {
                html += '<div class="card mb-3">';
                html += '<div class="card-header bg-success text-white">';
                html += '<h5 class="mb-0"><i class="bx bx-user-plus me-2"></i> Students Receiving New Invoices (' + newStudents.length + ')</h5>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<div class="table-responsive">';
                html += '<table class="table table-striped">';
                html += '<thead class="table-success"><tr><th class="text-start">Student Number</th><th class="text-start">Name</th><th class="text-end">Amount</th></tr></thead>';
                html += '<tbody>';
                newStudents.forEach(function(student) {
                    html += '<tr>';
                    html += '<td class="text-start">' + student.student_number + '</td>';
                    html += '<td class="text-start">' + student.name + '</td>';
                    html += '<td class="text-end fw-bold">' + validationData.currency_symbol + student.amount.toLocaleString() + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div></div>';
            }

            // Display students with existing invoices
            const existingStudents = validationData.students.filter(student => student.status === 'already_exists');
            if (existingStudents.length > 0) {
                html += '<div class="card mb-3">';
                html += '<div class="card-header bg-warning text-dark">';
                html += '<h5 class="mb-0"><i class="bx bx-info-circle me-2"></i> Students with Existing Invoices (' + existingStudents.length + ')</h5>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<div class="alert alert-warning">';
                html += '<i class="bx bx-info-circle me-2"></i>';
                html += 'These students already have invoices for the selected period and will be skipped.';
                html += '</div>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-striped">';
                html += '<thead class="table-success"><tr><th class="text-start">Student Number</th><th class="text-start">Name</th><th class="text-end">Amount</th></tr></thead>';
                html += '<tbody>';
                existingStudents.forEach(function(student) {
                    html += '<tr>';
                    html += '<td class="text-start">' + student.student_number + '</td>';
                    html += '<td class="text-start">' + student.name + '</td>';
                    html += '<td class="text-end fw-bold">' + validationData.currency_symbol + student.amount.toLocaleString() + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div></div>';
            }

            // Display students with no fee settings
            const noFeeStudents = validationData.students.filter(student => student.status === 'no_fee_settings');
            if (noFeeStudents.length > 0) {
                html += '<div class="card mb-3">';
                html += '<div class="card-header bg-danger text-white">';
                html += '<h5 class="mb-0"><i class="bx bx-error-circle me-2"></i> Students with No Fee Settings (' + noFeeStudents.length + ')</h5>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<div class="alert alert-danger">';
                html += '<i class="bx bx-error-circle me-2"></i>';
                html += 'These students do not have fee settings configured for the selected period and will be skipped.';
                html += '</div>';

                // Show debug info if available
                const debugInfo = noFeeStudents[0].debug_info;
                if (debugInfo) {
                    html += '<div class="small text-muted mt-2">';
                    html += '<strong>Debug Information:</strong><br>';
                    html += 'Total fee settings for program: ' + (debugInfo.fee_settings_count || 'N/A') + '<br>';
                    html += 'Active fee settings for program: ' + (debugInfo.active_fee_settings_count || 'N/A') + '<br>';
                    html += 'Fee settings for this period: ' + (debugInfo.fee_settings_for_period || 'N/A') + '<br>';
                    html += 'Active fee settings for this period: ' + (debugInfo.active_fee_settings_for_period || 'N/A') + '<br>';
                    if (debugInfo.fee_setting_items_count !== undefined) {
                        html += 'Fee setting items in found setting: ' + debugInfo.fee_setting_items_count + '<br>';
                        html += 'Fee setting items for this group: ' + debugInfo.fee_setting_items_for_group + '<br>';
                    }
                    html += '</div>';
                }

                html += '<div class="table-responsive">';
                html += '<table class="table table-striped">';
                html += '<thead class="table-danger"><tr><th class="text-start">Student Number</th><th class="text-start">Name</th><th class="text-center">Status</th></tr></thead>';
                html += '<tbody>';
                noFeeStudents.forEach(function(student) {
                    html += '<tr>';
                    html += '<td class="text-start">' + student.student_number + '</td>';
                    html += '<td class="text-start">' + student.name + '</td>';
                    html += '<td><span class="badge bg-danger">No Fee Settings</span></td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div></div>';
            }
        } else {
            // Specific students validation
            html += `
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bx bx-user me-2"></i>Specific Students Invoice Generation Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Program:</strong> ${validationData.program_name}</p>
                                <p><strong>Fee Group:</strong> ${validationData.fee_group_name}</p>
                                <p><strong>Period:</strong> ${getPeriodText(validationData.fee_period)}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Selected Students:</strong> ${validationData.selected_students.length}</p>
                                <p><strong>Due Date:</strong> ${new Date(validationData.due_date).toLocaleDateString()}</p>
                                <p><strong>Total Amount:</strong> ${validationData.currency_symbol}${validationData.total_amount}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Selected Student Details:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-start">Student Name</th>
                                            <th class="text-start">Student Number</th>
                                            <th class="text-end">Amount</th>
                                            <th class="text-center">Due Date</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;

            validationData.selected_students.forEach(student => {
                let statusBadge = '';
                let statusClass = '';

                switch(student.status) {
                    case 'will_create':
                        statusBadge = '<span class="badge bg-success">Ready to Create</span>';
                        statusClass = '';
                        break;
                    case 'already_exists':
                        statusBadge = '<span class="badge bg-warning">Already Exists</span>';
                        statusClass = 'table-warning';
                        break;
                    case 'no_fee_settings':
                        statusBadge = '<span class="badge bg-danger">No Fee Settings</span>';
                        statusClass = 'table-danger';
                        break;
                    default:
                        statusBadge = '<span class="badge bg-secondary">' + student.status + '</span>';
                        statusClass = 'table-secondary';
                }

                html += `
                    <tr class="${statusClass}">
                        <td class="text-start">${student.name}</td>
                        <td class="text-start">${student.student_number}</td>
                        <td class="text-end fw-bold">${validationData.currency_symbol}${(student.amount || 0).toLocaleString()}</td>
                        <td class="text-center">${new Date(validationData.due_date).toLocaleDateString()}</td>
                        <td class="text-center">${statusBadge}</td>
                    </tr>
                `;
            });

            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Display students who will receive new invoices
            const newStudents = validationData.selected_students.filter(student => student.status === 'will_create');
            if (newStudents.length > 0) {
                html += '<div class="card mb-3">';
                html += '<div class="card-header bg-success text-white">';
                html += '<h5 class="mb-0"><i class="bx bx-user-plus me-2"></i> Students Receiving New Invoices (' + newStudents.length + ')</h5>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<div class="alert alert-success">';
                html += '<i class="bx bx-check-circle me-2"></i>';
                html += 'These selected students will receive new invoices.';
                html += '</div>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-striped">';
                html += '<thead class="table-success"><tr><th class="text-start">Student Number</th><th class="text-start">Name</th><th class="text-end">Amount</th></tr></thead>';
                html += '<tbody>';
                newStudents.forEach(function(student) {
                    html += '<tr>';
                    html += '<td class="text-start">' + student.student_number + '</td>';
                    html += '<td class="text-start">' + student.name + '</td>';
                    html += '<td class="text-end fw-bold">' + validationData.currency_symbol + student.amount.toLocaleString() + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div></div>';
            }

            // Display students with existing invoices
            const existingStudents = validationData.selected_students.filter(student => student.status === 'already_exists');
            if (existingStudents.length > 0) {
                html += '<div class="card mb-3">';
                html += '<div class="card-header bg-warning text-dark">';
                html += '<h5 class="mb-0"><i class="bx bx-info-circle me-2"></i> Students with Existing Invoices (' + existingStudents.length + ')</h5>';
                html += '</div>';
                html += '<div class="card-body">';
                html += '<div class="alert alert-warning">';
                html += '<i class="bx bx-info-circle me-2"></i>';
                html += 'These selected students already have invoices for the selected period and will be skipped.';
                html += '</div>';
                html += '<div class="table-responsive">';
                html += '<table class="table table-striped">';
                html += '<thead class="table-success"><tr><th class="text-start">Student Number</th><th class="text-start">Name</th><th class="text-end">Amount</th></tr></thead>';
                html += '<tbody>';
                existingStudents.forEach(function(student) {
                    html += '<tr>';
                    html += '<td class="text-start">' + student.student_number + '</td>';
                    html += '<td class="text-start">' + student.name + '</td>';
                    html += '<td class="text-end fw-bold">' + validationData.currency_symbol + student.amount.toLocaleString() + '</td>';
                    html += '</tr>';
                });
                html += '</tbody></table></div></div></div>';
            }
        }

        html += `
            </div>
        `;

        $('#invoice-preview').html(html);
    }

    // Function to handle actual invoice generation after validation
    function startActualGeneration(validationData) {
        // Show confirmation for actual generation
        Swal.fire({
            title: 'Confirm Invoice Generation',
            text: 'Are you sure you want to generate the invoice(s) based on the validated data?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bx bx-check me-1"></i>Yes, Generate Now',
            cancelButtonText: '<i class="bx bx-x me-1"></i>Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performActualGeneration(validationData);
            }
        });
    }

    // Function to perform the actual invoice generation
    function performActualGeneration(validationData) {
        // Prepare form data
        let formData = $('#fee-invoice-form').serializeArray();

        // Convert back to query string
        const formDataString = $.param(formData);

        // Update progress to generation
        updateProgress('generation');

        // Disable form and buttons
        const generateBtn = $('#generate-btn');
        const originalText = generateBtn.html();
        generateBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Generating...');
        $('#fee-invoice-form input, #fee-invoice-form select, #fee-invoice-form button').prop('disabled', true);

        // Show processing notification
        Swal.fire({
            title: 'Generating Invoices...',
            text: 'Please wait while we create the invoice(s)',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Submit to actual store endpoint
        $.ajax({
            url: $('#fee-invoice-form').attr('action'),
            type: 'POST',
            data: formDataString,
            timeout: 60000, // Longer timeout for generation
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                // Close loading alert
                Swal.close();

                // Update progress to generation (final step)
                updateProgress('generation');
                generateBtn.html('<i class="bx bx-check me-2"></i>Complete!');

                // Update preview to show completion
                $('#invoice-preview').html(`
                    <div class="text-center">
                        <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                        <h5 class="text-success mt-3">Generation Complete!</h5>
                        <p class="text-muted">${response.message || 'Invoice(s) generated successfully!'}</p>
                    </div>
                `);

                // Show success message
                Swal.fire({
                    title: 'Success!',
                    text: response.message || 'Invoice(s) generated successfully!',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });

                // Redirect after a short delay
                setTimeout(function() {
                    window.location.href = '{{ route("college.fee-invoices.index") }}';
                }, 3000);
            },
            error: function(xhr, status, error) {
                // Close loading alert
                Swal.close();

                // Reset to validation state on error
                updateProgress('validation');
                generateBtn.prop('disabled', false).html(originalText);
                $('#fee-invoice-form input, #fee-invoice-form select').prop('disabled', false);

                let errorMessage = 'An error occurred during generation. Please try again.';
                let errorTitle = 'Generation Error';

                if (xhr.status === 422) {
                    // Validation errors
                    const errors = xhr.responseJSON.errors;
                    errorMessage = 'Generation failed:\n';
                    for (let field in errors) {
                        errorMessage += `- ${errors[field][0]}\n`;
                    }
                    errorTitle = 'Validation Error';
                } else if (status === 'timeout') {
                    errorMessage = 'Request timed out. Please try again.';
                    errorTitle = 'Timeout Error';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    title: errorTitle,
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    }

    // Update progress steps
    function updateProgress(step) {
        // Reset all steps
        $('.step').removeClass('active completed');
        $('.step-circle').removeClass('bg-primary bg-success text-white').addClass('bg-light text-muted');

        // Update progress bar
        let progressWidth = '33%';
        let activeSteps = [];

        switch(step) {
            case 'config':
                activeSteps = ['config'];
                progressWidth = '33%';
                break;
            case 'validation':
                activeSteps = ['config', 'validation'];
                progressWidth = '66%';
                $('#step-config .step-circle').removeClass('bg-light text-muted').addClass('bg-success text-white');
                $('#step-config').addClass('completed');
                // Mark validation as completed when showing validation success
                $('#step-validation .step-circle').removeClass('bg-light text-muted').addClass('bg-success text-white');
                $('#step-validation').addClass('completed');
                break;
            case 'generation':
                activeSteps = ['config', 'validation', 'generation'];
                progressWidth = '100%';
                $('.step-circle').removeClass('bg-light text-muted').addClass('bg-success text-white');
                $('.step').addClass('completed');
                break;
        }

        // Set active step
        if (activeSteps.length > 0) {
            const lastStep = activeSteps[activeSteps.length - 1];
            $(`#step-${lastStep}`).addClass('active');
            $(`#step-${lastStep} .step-circle`).removeClass('bg-light text-muted').addClass('bg-primary text-white');
        }

        $('#progress-bar').css('width', progressWidth);
    }

    // Initialize form state
    const initialGenerationType = $('#generation_type').select2('val') || $('#generation_type').val() || 'all_students';
    console.log('Initial generation type:', initialGenerationType);
    if (initialGenerationType === 'all_students') {
        $('#student-selection').hide();
    } else if (initialGenerationType === 'specific_students') {
        $('#student-selection').show();
        loadStudents();
    }

    // Force preview update after a short delay to ensure Select2 is ready
    setTimeout(function() {
        console.log('Delayed preview update');
        updatePreview();
    }, 1000);

    // Initialize progress to config step
    updateProgress('config');

    // Add manual preview trigger for debugging
    $('#manual-preview-btn').on('click', function() {
        console.log('Manual preview trigger clicked');
        updatePreview();
    });
        } catch (error) {
            console.error('Error in document ready initialization:', error);
            console.error('Error stack:', error.stack);
        }
    });
} catch (error) {
    console.error('Critical error in main script:', error);
    console.error('Error stack:', error.stack);
}
</script>
<script>
// Final load check
window.addEventListener('load', function() {
    console.log('Window fully loaded');
    console.log('All scripts should be executed now');
});

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global JavaScript error:', e.error);
    console.error('Error message:', e.message);
    console.error('Error file:', e.filename);
    console.error('Error line:', e.lineno);
});
</script>
@endpush