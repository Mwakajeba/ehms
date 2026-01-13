@extends('layouts.main')

@section('title', 'Generate Fee Invoice')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Invoices', 'url' => route('school.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Generate Invoice', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">GENERATE FEE INVOICE</h6>
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
                                    <div class="step-title">Configuration</div>
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

        <form id="fee-invoice-form" method="POST" action="{{ route('school.fee-invoices.store') }}">
            @csrf

            <div class="row">
                <!-- Main Configuration Panel -->
                <div class="col-12 col-lg-8">
                    <!-- Invoice Configuration Card -->
                    <div class="card shadow-sm mb-4 radius-10">
                        <div class="card-header card-header-gradient">
                            <h5 class="card-title mb-0 text-white">
                                <i class="bx bx-cog me-2"></i>Invoice Configuration
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Basic Information Section -->
                            <div class="form-section mb-4">
                                <h6 class="form-section-title">
                                    <i class="bx bx-info-circle"></i>Basic Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="class_id" class="form-label fw-bold">
                                                <i class="bx bx-school text-primary me-1"></i>Class <span class="text-danger">*</span>
                                            </label>
                                            <select name="class_id" id="class_id" class="form-select select2-single @error('class_id') is-invalid @enderror" required>
                                                <option value="">Select Class</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }} ({{ $class->students_count }} students)
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('class_id')
                                                <div class="invalid-feedback">
                                                    <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <small class="text-muted d-block mt-1">
                                                <i class="bx bx-info-circle me-1"></i>Select the class for invoice generation
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="fee_group_id" class="form-label fw-bold">
                                                <i class="bx bx-group text-success me-1"></i>Fee Group <span class="text-danger">*</span>
                                            </label>
                                            <select name="fee_group_id" id="fee_group_id" class="form-select select2-single @error('fee_group_id') is-invalid @enderror" required>
                                                <option value="">Select Fee Group</option>
                                                @foreach($feeGroups as $feeGroup)
                                                    <option value="{{ $feeGroup->id }}" {{ old('fee_group_id') == $feeGroup->id ? 'selected' : '' }}>
                                                        {{ $feeGroup->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('fee_group_id')
                                                <div class="invalid-feedback">
                                                    <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <small class="text-muted d-block mt-1">
                                                <i class="bx bx-info-circle me-1"></i>Determines accounting treatment for this invoice
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Period & Generation Section -->
                            <div class="form-section mb-4">
                                <h6 class="form-section-title">
                                    <i class="bx bx-calendar"></i>Period & Generation Settings
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="period" class="form-label fw-bold">
                                                <i class="bx bx-calendar text-warning me-1"></i>Fee Period <span class="text-danger">*</span>
                                            </label>
                                            <select name="period" id="period" class="form-select select2-single @error('period') is-invalid @enderror" required>
                                                <option value="">Select Period</option>
                                                <option value="1" {{ old('period') == '1' ? 'selected' : '' }}>Quarter 1</option>
                                                <option value="2" {{ old('period') == '2' ? 'selected' : '' }}>Quarter 2</option>
                                                <option value="3" {{ old('period') == '3' ? 'selected' : '' }}>Quarter 3</option>
                                                <option value="4" {{ old('period') == '4' ? 'selected' : '' }}>Quarter 4</option>
                                                <option value="6" {{ old('period') == '6' ? 'selected' : '' }}>Term 1</option>
                                                <option value="7" {{ old('period') == '7' ? 'selected' : '' }}>Term 2</option>
                                                <option value="5" {{ old('period') == '5' ? 'selected' : '' }}>Full Year</option>
                                            </select>
                                            @error('period')
                                                <div class="invalid-feedback">
                                                    <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <small class="text-muted d-block mt-1">
                                                <i class="bx bx-info-circle me-1"></i>Select the billing period for fees
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label for="generation_type" class="form-label fw-bold">
                                                <i class="bx bx-select-multiple text-info me-1"></i>Generation Type <span class="text-danger">*</span>
                                            </label>
                                            <select name="generation_type" id="generation_type" class="form-select select2-single @error('generation_type') is-invalid @enderror" required>
                                                <option value="">Select Type</option>
                                                <option value="bulk" {{ old('generation_type', 'bulk') == 'bulk' ? 'selected' : '' }}>
                                                    Bulk Generation (All Students)
                                                </option>
                                                <option value="single" {{ old('generation_type') == 'single' ? 'selected' : '' }}>
                                                    Single Student
                                                </option>
                                            </select>
                                            @error('generation_type')
                                                <div class="invalid-feedback">
                                                    <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <small class="text-muted d-block mt-1">
                                                <i class="bx bx-info-circle me-1"></i>Choose bulk or single student invoice generation
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Selection (Hidden by default) -->
                            <div class="form-section" id="student-selection" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="form-section-title mb-0">
                                    <i class="bx bx-user"></i>Student Selection
                                </h6>
                                    <button type="button" class="btn btn-primary btn-sm" id="add-student-line-btn">
                                        <i class="bx bx-plus me-1"></i>Add Student
                                    </button>
                                </div>
                                
                                <!-- Student Lines Container -->
                                <div id="student-lines-container">
                                    <div class="student-line mb-3 p-3 border rounded">
                                <div class="row">
                                            <div class="col-md-11">
                                        <div class="mb-3">
                                                    <label class="form-label fw-bold">
                                                <i class="bx bx-user text-danger me-1"></i>Select Student <span class="text-danger">*</span>
                                            </label>
                                                    <select name="student_ids[]" class="form-select student-select select2-single @error('student_id') is-invalid @enderror" required data-placeholder="Select Student">
                                                        <option value="">Select Student</option>
                                                        @foreach($students as $student)
                                                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->admission_number ?? 'N/A' }})</option>
                                                        @endforeach
                                            </select>
                                            @error('student_id')
                                                <div class="invalid-feedback">
                                                    <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <small class="text-muted d-block mt-1">
                                                        <i class="bx bx-info-circle me-1"></i>All active students are shown. Select a class above to filter by class.
                                            </small>
                                                </div>
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm remove-student-line-btn" style="display: none !important;">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
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
                                        <h4 class="text-success mb-1">{{ $classes->count() }}</h4>
                                        <small class="text-muted">Classes Available</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2">
                                        <h4 class="text-primary mb-1">{{ $classes->sum('students_count') }}</h4>
                                        <small class="text-muted">Total Active Students</small>
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
                                <h6 class="text-info"><i class="bx bx-bulb me-1"></i>Bulk Generation</h6>
                                <p class="small text-muted mb-2">Creates invoices for all active students in the selected class automatically.</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-warning"><i class="bx bx-user me-1"></i>Single Generation</h6>
                                <p class="small text-muted mb-2">Creates invoice for one specific student. Useful for individual cases.</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-success"><i class="bx bx-group me-1"></i>Fee Groups</h6>
                                <p class="small text-muted mb-2">Required selection that determines accounting treatment and fee categorization.</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-success"><i class="bx bx-calendar me-1"></i>Fee Periods</h6>
                                <p class="small text-muted mb-2">Quarterly fees are calculated proportionally. Full year generates 4 quarterly invoices simultaneously.</p>
                            </div>
                            <div class="alert alert-light small border">
                                <i class="bx bx-info-circle text-info me-1"></i>
                                <strong>Transport Fees:</strong> Automatically included if student has transport setup and fee settings allow it.
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
                                <a href="{{ route('school.fee-invoices.index') }}" class="btn btn-outline-secondary">
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

<!-- Edit Invoice Modal -->
<div class="modal fade" id="editInvoiceModal" tabindex="-1" aria-labelledby="editInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editInvoiceModalLabel">
                    <i class="bx bx-edit me-2"></i>Edit Invoice
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="editInvoiceModalBody">
                <!-- Edit form will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <i class="bx bx-loader-alt bx-spin text-primary" style="font-size: 2rem;"></i>
                    <p class="text-muted mt-2">Loading edit form...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveEditBtn">
                    <i class="bx bx-save me-1"></i>Update Invoice
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    
    .card-header-gradient {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
        color: white;
    }
    
    .form-section {
        background: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border-radius: 6px;
        transition: all 0.2s ease;
    }
    
    .form-section:hover {
        background: #f0f4f8;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .form-section-title {
        font-weight: 600;
        color: #212529;
        font-size: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
    }
    
    .form-section-title i {
        margin-right: 0.5rem;
        color: #0d6efd;
        font-size: 1.1rem;
    }
    
    .radius-10 {
        border-radius: 10px;
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

    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        min-height: 48px;
    }

    .select2-container--bootstrap-5 .select2-selection:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
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

    @media (max-width: 768px) {
        .step-circle {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .card-body {
            padding: 1rem;
        }

        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
    }
</style>

<!-- Modal Styles -->
<style>
    .modal-content {
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border: none;
        padding: 1.25rem 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }

    .modal-footer {
        border: none;
        padding: 1rem 1.5rem;
    }

    .edit-invoice-btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .edit-invoice-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced table styling */
    .table-bordered.border-dark {
        border: 2px solid #000 !important;
    }

    .table-bordered.border-dark th,
    .table-bordered.border-dark td {
        border: 1px solid #000 !important;
        vertical-align: middle;
    }

    .table-bordered.border-dark thead th {
        background-color: #343a40 !important;
        color: white !important;
        font-weight: 600;
        border-bottom: 2px solid #000 !important;
        text-align: center;
    }

    .table-bordered.border-dark tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table-bordered.border-dark .text-end {
        text-align: right !important;
        font-family: 'Courier New', monospace;
        font-weight: 500;
    }

    .table-bordered.border-dark .fw-bold {
        font-weight: 700 !important;
        color: #0d6efd;
    }

    .table-responsive {
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Global AJAX setup for CSRF token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Helper function to get period display text
function getPeriodText(period) {
    const periodNames = {
        '1': 'Quarter 1',
        '2': 'Quarter 2',
        '3': 'Quarter 3',
        '4': 'Quarter 4',
        '5': 'Full Year'
    };
    return periodNames[period] || period;
}
</script>
<script>
$(document).ready(function() {
    console.log('Fee invoice form page loaded');
    
    // Initialize Select2 for all select2-single elements (including student selects)
    $('.select2-single').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Please select...';
        },
        allowClear: true
    });
    
    // Add click handler to submit button - ensure form validation runs
    $(document).on('click', '#fee-invoice-form button[type="submit"]', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Submit button clicked directly');
        console.log('Form element:', $('#fee-invoice-form').length);
        // Manually trigger form submit event
        $('#fee-invoice-form').trigger('submit');
    });

    // Initialize remove buttons visibility on page load
    updateRemoveButtons();

    // Generation type change handler
    $('#generation_type').change(function() {
        const generationType = $(this).val();

        if (generationType === 'single') {
            $('#student-selection').slideDown(300);
            // Load students if class is already selected
            if ($('#class_id').val()) {
                loadStudents();
            }
        } else {
            $('#student-selection').slideUp(300);
            // Clear all student lines
            $('.student-select').val('').trigger('change');
        }

        updatePreview();
    });

    // Store students data for filtering (will be populated via AJAX)
    let allStudents = [];

    // Function to load students via AJAX
    function loadStudents() {
        const classId = $('#class_id').val();
        const generationType = $('#generation_type').val();
        
        if (!classId || generationType !== 'single') {
            return;
        }

        // Show loading state
            $('.student-select').each(function() {
                const $select = $(this);
            $select.prop('disabled', true);
            $select.html('<option value="">Loading students...</option>');
        });

        $.ajax({
            url: '{{ route("school.fee-invoices.get-students") }}',
            type: 'GET',
            data: {
                class_id: classId
            },
            success: function(response) {
                // Update allStudents array
                allStudents = response || [];
                
                // Update all student selects
                $('.student-select').each(function() {
                    const $select = $(this);
                    const currentValue = $select.data('previous-value') || $select.val();
                    
                    // Clear and rebuild options
                    $select.html('<option value="">Select Student</option>');
                    
                    // Add students
                    allStudents.forEach(function(student) {
                        $select.append('<option value="' + student.id + '">' + student.name + '</option>');
                });
        
                // Restore previous selection if still available
                if (currentValue && $select.find('option[value="' + currentValue + '"]').length) {
                    $select.val(currentValue).trigger('change');
                    }
                    
                    $select.prop('disabled', false);
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading students:', error);
                $('.student-select').each(function() {
                    const $select = $(this);
                    $select.html('<option value="">Error loading students</option>');
                    $select.prop('disabled', false);
                });
            }
        });
    }

    // Class change handler - load students via AJAX when class is selected
    $('#class_id').change(function() {
        const generationType = getSelectValue('#generation_type');
        
        if (generationType === 'single') {
            // Store current values before reloading
            $('.student-select').each(function() {
                $(this).data('previous-value', $(this).val());
            });
            
            // Load students via AJAX
            loadStudents();
        }
        
        updatePreview();
    });

    // Add new student line
    let studentLineCount = 1;
    $('#add-student-line-btn').on('click', function() {
        let studentOptions = '<option value="">Select Student</option>';
        
        // Use allStudents array (populated via AJAX, already filtered by class)
        allStudents.forEach(function(student) {
            studentOptions += '<option value="' + student.id + '">' + student.name + '</option>';
        });
        
        const lineHtml = `
            <div class="student-line mb-3 p-3 border rounded">
                <div class="row">
                    <div class="col-md-11">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bx bx-user text-danger me-1"></i>Select Student <span class="text-danger">*</span>
                            </label>
                            <select name="student_ids[]" class="form-select student-select select2-single" required data-placeholder="Select Student">
                                ${studentOptions}
                            </select>
                            <small class="text-muted d-block mt-1">
                                <i class="bx bx-info-circle me-1"></i>All active students are shown. Select a class above to filter by class.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-student-line-btn">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        $('#student-lines-container').append(lineHtml);
        studentLineCount++;
                    
        // Initialize Select2 for the new select
        $('#student-lines-container .student-select').last().select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Select Student',
                        allowClear: true
                    });
                    
        // Show remove buttons if more than one line
        updateRemoveButtons();
    });

    // Remove student line
    $(document).on('click', '.remove-student-line-btn', function() {
        $(this).closest('.student-line').remove();
        updateRemoveButtons();
        updatePreview();
    });

    // Function to update remove buttons visibility
    function updateRemoveButtons() {
        const lineCount = $('.student-line').length;
        $('.remove-student-line-btn').toggle(lineCount > 1);
    }

    // Students are now pre-loaded from the controller, no AJAX needed

    // Period change handler
    $('#period, #fee_group_id').change(function() {
        updatePreview();
    });
    
    // Student selection change handler
    $(document).on('change', '.student-select', function() {
        updatePreview();
    });

    // Update invoice preview
    function updatePreview() {
        const classId = $('#class_id').val();
        const classText = $('#class_id option:selected').text();
        const feeGroupId = $('#fee_group_id').val();
        const feeGroupText = $('#fee_group_id option:selected').text();
        const period = $('#period').val();
        const generationType = getSelectValue('#generation_type');
        
        // Get selected students for single invoice
        const selectedStudents = [];
        $('.student-select').each(function() {
            const studentId = $(this).val();
            if (studentId && studentId !== '') {
                const studentText = $(this).find('option:selected').text();
                selectedStudents.push({id: studentId, text: studentText});
            }
        });

        if (classId && feeGroupId && period && generationType) {
            let previewText = '';

            if (generationType === 'bulk') {
                let periodText = getPeriodText(period);
                let periodDescription = '';

                if (period == 5) {
                    periodDescription = ' (Will generate 4 quarterly invoices: Q1, Q2, Q3, Q4)';
                    periodText = 'Full Year';
                }

                previewText = `
                    <div class="text-center">
                        <i class="bx bx-group text-success" style="font-size: 3rem;"></i>
                        <h5 class="text-success mt-3">Bulk Invoice Generation</h5>
                        <p class="text-muted">Will generate invoices for all active students in the selected class</p>
                        <div class="alert alert-info">
                            <strong>Period:</strong> ${periodText}${periodDescription}<br>
                            <strong>Fee Group:</strong> ${feeGroupText}<br>
                            <strong>Class:</strong> ${classText}<br>
                            <strong>Estimated Invoices:</strong> All active students ${period == 5 ? '× 4 quarters' : ''}
                        </div>
                    </div>
                `;
            } else {
                // Build student list for preview
                let studentList = '';
                if (selectedStudents.length > 0) {
                    selectedStudents.forEach(function(student, index) {
                        studentList += `${index + 1}. ${student.text}<br>`;
                    });
                } else {
                    studentList = 'Select student(s) above';
                }
                
                previewText = `
                    <div class="text-center">
                        <i class="bx bx-user text-primary" style="font-size: 3rem;"></i>
                        <h5 class="text-primary mt-3">Single Invoice Generation</h5>
                        <p class="text-muted">Will generate invoice(s) for ${selectedStudents.length} selected student(s)</p>
                        <div class="alert alert-warning">
                            <strong>Period:</strong> ${getPeriodText(period)}<br>
                            <strong>Fee Group:</strong> ${feeGroupText}<br>
                            <strong>Selected Students (${selectedStudents.length}):</strong><br>
                            ${studentList}
                            <strong>Type:</strong> Individual invoice(s)
                        </div>
                    </div>
                `;
            }

            $('#invoice-preview').html(previewText);
        } else {
            $('#invoice-preview').html(`
                <i class="bx bx-receipt text-muted" style="font-size: 4rem;"></i>
                <h5 class="text-muted mt-3">Invoice Preview</h5>
                <p class="text-muted small">Configure your settings above to see the preview</p>
            `);
        }
    }

    // Helper function to get value from Select2 or regular select
    function getSelectValue(selector) {
        const $element = $(selector);
        // Check if Select2 is initialized
        if ($element.data('select2')) {
            return $element.select2('val');
        }
        return $element.val();
    }

    // Form validation and submission
    $('#fee-invoice-form').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        console.log('Form submit event triggered');

        // Get values from Select2 or regular select
        const generationType = getSelectValue('#generation_type');
        const classId = getSelectValue('#class_id');
        const feeGroupId = getSelectValue('#fee_group_id');
        const period = getSelectValue('#period');

        console.log('Form validation - Values:', {
            generationType: generationType,
            classId: classId,
            feeGroupId: feeGroupId,
            period: period
        });

        if (!classId || !feeGroupId || !period || !generationType) {
            console.log('Validation failed - missing required fields');
            console.log('Missing fields:', {
                classId: !classId ? 'MISSING' : 'OK',
                feeGroupId: !feeGroupId ? 'MISSING' : 'OK',
                period: !period ? 'MISSING' : 'OK',
                generationType: !generationType ? 'MISSING' : 'OK'
            });
            
            let missingFields = [];
            if (!classId) missingFields.push('Class');
            if (!feeGroupId) missingFields.push('Fee Group');
            if (!period) missingFields.push('Period');
            if (!generationType) missingFields.push('Generation Type');
            
            const errorMsg = 'Please fill in all required fields. Missing: ' + missingFields.join(', ');
            
            if (typeof toastr !== 'undefined') {
                toastr.error(errorMsg);
            } else {
                alert(errorMsg);
            }
            return false;
        }
        
        console.log('All required fields validated successfully');

        if (generationType === 'single') {
            // Check if at least one student is selected from any student line
            let hasSelectedStudent = false;
            $('.student-select').each(function() {
                if ($(this).val() && $(this).val() !== '') {
                    hasSelectedStudent = true;
                    return false; // break the loop
                }
            });
            
            if (!hasSelectedStudent) {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Please select at least one student for single invoice generation.');
                } else {
                    alert('Please select at least one student for single invoice generation.');
                }
            return false;
            }
        }

        // Show confirmation dialog
        // Get text from Select2 or regular select
        const $classSelect = $('#class_id');
        const className = $classSelect.data('select2') ? $classSelect.select2('data')[0]?.text : $classSelect.find('option:selected').text();
        
        const $feeGroupSelect = $('#fee_group_id');
        const feeGroupName = $feeGroupSelect.data('select2') ? $feeGroupSelect.select2('data')[0]?.text : $feeGroupSelect.find('option:selected').text();
        
        const periodText = getPeriodText(period);
        
        // Get selected students count for single invoice
        let selectedStudentsCount = 0;
        if (generationType === 'single') {
            $('.student-select').each(function() {
                if ($(this).val() && $(this).val() !== '') {
                    selectedStudentsCount++;
                }
            });
        }
        
        const generationText = generationType === 'bulk' 
            ? 'all active students in the selected class' 
            : `${selectedStudentsCount} selected student(s)`;

        console.log('About to show confirmation dialog');
        console.log('SweetAlert2 available:', typeof Swal !== 'undefined');
        console.log('Values for dialog:', { className, feeGroupName, periodText, generationText });
        
        // Check if SweetAlert2 is available
        if (typeof Swal !== 'undefined') {
            console.log('Showing SweetAlert2 confirmation dialog');
            try {
                Swal.fire({
                    title: 'Confirm Invoice Validation',
                    html: `
                        <div class="text-left">
                            <p class="mb-2"><strong>Class:</strong> ${className || 'N/A'}</p>
                            <p class="mb-2"><strong>Fee Group:</strong> ${feeGroupName || 'N/A'}</p>
                            <p class="mb-2"><strong>Period:</strong> ${periodText || 'N/A'}</p>
                            <p class="mb-2"><strong>Generation Type:</strong> ${generationText || 'N/A'}</p>
                            <p class="mt-3 text-info"><i class="bx bx-info-circle me-1"></i>This action will validate the invoice generation criteria and show a preview before actual creation.</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bx bx-check me-1"></i>Yes, Validate First',
                    cancelButtonText: '<i class="bx bx-x me-1"></i>Cancel'
                }).then((result) => {
                    console.log('SweetAlert result:', result);
                    if (result.isConfirmed) {
                        console.log('User confirmed, starting invoice generation');
                        startInvoiceGeneration();
                    } else {
                        console.log('User cancelled');
                    }
                }).catch((error) => {
                    console.error('SweetAlert error:', error);
                    // Fallback to native confirm
                    if (confirm(`Confirm Invoice Validation\n\nClass: ${className}\nFee Group: ${feeGroupName}\nPeriod: ${periodText}\nGeneration Type: ${generationText}\n\nThis action will validate the invoice generation criteria and show a preview before actual creation.`)) {
                        startInvoiceGeneration();
                    }
                });
            } catch (error) {
                console.error('Error showing SweetAlert:', error);
                // Fallback to native confirm
                if (confirm(`Confirm Invoice Validation\n\nClass: ${className}\nFee Group: ${feeGroupName}\nPeriod: ${periodText}\nGeneration Type: ${generationText}\n\nThis action will validate the invoice generation criteria and show a preview before actual creation.`)) {
                    startInvoiceGeneration();
                }
            }
        } else {
            console.log('SweetAlert2 not available, using native confirm');
            // Fallback if SweetAlert2 is not loaded
            if (confirm(`Confirm Invoice Validation\n\nClass: ${className}\nFee Group: ${feeGroupName}\nPeriod: ${periodText}\nGeneration Type: ${generationText}\n\nThis action will validate the invoice generation criteria and show a preview before actual creation.`)) {
                startInvoiceGeneration();
            }
        }

        function startInvoiceGeneration() {
            // Prepare form data
            let formData = $('#fee-invoice-form').serializeArray();
            const generationType = getSelectValue('#generation_type');

            if (generationType === 'bulk') {
                // Remove student_ids[] from form data for bulk generation
                formData = formData.filter(field => field.name !== 'student_id' && field.name !== 'student_ids[]');
            } else if (generationType === 'single') {
                // For single invoice, ensure we have student_ids[] array
                // Remove any old student_id field
                formData = formData.filter(field => field.name !== 'student_id');
                
                // Collect all selected student IDs
                const selectedStudentIds = [];
                $('.student-select').each(function() {
                    const studentId = $(this).val();
                    if (studentId && studentId !== '') {
                        selectedStudentIds.push(studentId);
                    }
                });
                
                // Remove existing student_ids[] entries and add new ones
                formData = formData.filter(field => field.name !== 'student_ids[]');
                selectedStudentIds.forEach(function(studentId) {
                    formData.push({name: 'student_ids[]', value: studentId});
                });
            }

            // Convert to object for proper array handling
            const formDataObj = {};
            formData.forEach(function(field) {
                if (field.name.endsWith('[]')) {
                    // Handle array fields
                    const key = field.name.replace('[]', '');
                    if (!formDataObj[key]) {
                        formDataObj[key] = [];
                    }
                    formDataObj[key].push(field.value);
                } else {
                    formDataObj[field.name] = field.value;
                }
            });
            
            // Add CSRF token if not already present
            const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
            if (csrfToken && !formDataObj._token) {
                formDataObj._token = csrfToken;
            }

            // Convert back to query string with proper array format
            const formDataString = $.param(formDataObj);

            console.log('Form data string:', formDataString);

            // Start validation step immediately
            updateProgress('validation');

            // Disable form
            const submitBtn = $('#fee-invoice-form').find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Validating...');

            // Disable all form inputs
            $('#fee-invoice-form input, #fee-invoice-form select, #fee-invoice-form button').prop('disabled', true);

            // Show processing notification with longer timeout message for bulk
            const isBulk = getSelectValue('#generation_type') === 'bulk';
            const processingMessage = isBulk 
                ? 'Validating invoice generation request for all students. This may take a few minutes for large classes...'
                : 'Validating invoice generation request';
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Processing...',
                    html: processingMessage + '<br><small class="text-muted">Please wait, do not close this window</small>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            } else {
                console.log('Processing: ' + processingMessage);
            }

            console.log('Submitting to:', '{{ route("school.fee-invoices.validate-invoices") }}');
            console.log('Form data:', formDataString);

            // Submit form via AJAX to validation endpoint first
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                }
            });

            $.ajax({
                url: '{{ route("school.fee-invoices.validate-invoices") }}',
                type: 'POST',
                data: formDataString,
                timeout: 300000, // 5 minutes for bulk processing (70+ students)
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                success: function(response) {
                    console.log('Validation success response:', response);

                    // Close loading alert
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

                    // Show validation success
                    updateProgress('validation');
                    submitBtn.html('<i class="bx bx-check me-2"></i>Validation Complete!');

                    // Display validation results
                    displayValidationResults(response);

                    // Check if there are any students that will be created
                    let hasStudentsToCreate = false;
                    if (response.type === 'bulk') {
                        hasStudentsToCreate = response.students.some(student => student.status === 'will_create');
                    } else {
                        // For single invoice, check if any student in the array will be created
                        const students = response.students || (response.student ? [response.student] : []);
                        hasStudentsToCreate = students.some(student => student.status === 'will_create');
                    }

                    // Only show "Generate Invoices" button if there are students to create
                    if (hasStudentsToCreate) {
                        submitBtn.prop('disabled', false)
                            .removeClass('btn-primary')
                            .addClass('btn-success')
                            .attr('type', 'button')
                            .html('<i class="bx bx-plus me-2"></i>Generate Invoices')
                            .attr('id', 'generate-btn');

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
                    console.error('Validation error:', {
                        status: status,
                        error: error,
                        response: xhr.responseJSON,
                        statusCode: xhr.status
                    });

                    // Close loading alert
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }

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

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: errorTitle,
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#dc3545'
                        });
                    } else {
                        alert(errorTitle + ': ' + errorMessage);
                    }
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
                                <p><strong>Class:</strong> ${validationData.class_name}</p>
                                <p><strong>Fee Group:</strong> ${validationData.fee_group_name}</p>
                                <p><strong>Period:</strong> ${getPeriodText(validationData.period)}${validationData.period == 5 ? ' (4 Quarters)' : ''}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Students:</strong> ${validationData.total_students}</p>
                                <p><strong>Academic Year:</strong> ${validationData.academic_year_name}</p>
                                <p><strong>Subtotal:</strong> ${validationData.subtotal.toLocaleString()} TZS</p>
                                <p><strong>Transport Total:</strong> ${validationData.transport_total.toLocaleString()} TZS</p>
                                <p><strong>Total Discount:</strong> <span class="text-danger">${validationData.total_discount.toLocaleString()} TZS</span></p>
                                <p><strong>Total Amount:</strong> ${validationData.total_amount.toLocaleString()} TZS</p>
                                ${validationData.period == 5 ? `<p><strong>Expected Invoices:</strong> ${validationData.total_students * 4}</p>` : ''}
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Student Details:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover border-dark shadow-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-start">Student Name</th>
                                            <th class="text-start">Admission No</th>
                                            <th>Fee Amount (TZS)</th>
                                            <th class="text-start">Category</th>
                                            <th>Transport Fare (TZS)</th>
                                            <th>Discount (TZS)</th>
                                            <th>Total Amount (TZS)</th>
                                            <th>Status</th>
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
                        // For Full Year, don't show edit button since multiple periods might exist
                        if (validationData.period != 5 && student.existing_invoice_id) {
                            statusBadge += ' <button type="button" class="btn btn-sm btn-outline-primary ms-1 edit-invoice-btn" data-invoice-id="' + student.existing_invoice_id + '" data-student-name="' + student.name + '" title="Edit Invoice"><i class="bx bx-edit"></i> Edit</button>';
                        }
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
                
                // Determine fare information
                let fareInfo = '0';
                if (student.boarding_type === 'day' && student.has_transport && student.bus_stop_name) {
                    if (validationData.period == 5) {
                        // For Full Year, show total transport fare
                        fareInfo = `${student.bus_stop_fare.toLocaleString()}<br><small class="text-muted">Total for 4 quarters</small>`;
                    } else {
                        fareInfo = `${student.bus_stop_fare.toLocaleString()}<br><small class="text-muted">${student.bus_stop_name}</small>`;
                    }
                }
                
                // Determine discount information
                let discountInfo = '0';
                let discountTooltip = '';
                if (student.discount_amount && student.discount_amount > 0) {
                    discountInfo = `${student.discount_amount.toLocaleString()}`;
                    if (student.discount_type === 'percentage') {
                        discountTooltip = `Percentage discount: ${student.discount_value}% of ${(student.subtotal + student.transport_fare).toLocaleString()} TZS`;
                    } else if (student.discount_type === 'fixed') {
                        discountTooltip = `Fixed discount: ${student.discount_value.toLocaleString()} TZS`;
                    }
                    discountInfo = `<span class="text-danger fw-bold" title="${discountTooltip}">${discountInfo}</span>`;
                }
                
                // Determine category
                let category = student.boarding_type === 'boarding' ? 'Boarder' : 'Day Scholar';
                
                html += `
                    <tr class="${statusClass}">
                        <td class="text-start">${student.name}</td>
                        <td class="text-start">${student.admission_number}</td>
                        <td class="text-end">${(student.subtotal || 0).toLocaleString()}</td>
                        <td class="text-start">${category}</td>
                        <td class="text-end">${fareInfo}</td>
                        <td class="text-end">${discountInfo}</td>
                        <td class="text-end fw-bold">${student.total_amount.toLocaleString()}</td>
                        <td>${statusBadge}</td>
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
        } else {
            // Single invoice validation - handle array of students
            const students = validationData.students || (validationData.student ? [validationData.student] : []);
            
            // Calculate totals
            let totalSubtotal = 0;
            let totalTransportFare = 0;
            let totalDiscountAmount = 0;
            let totalAmount = 0;
            let willCreate = 0;
            let willSkip = 0;
            
            students.forEach(student => {
                totalSubtotal += student.subtotal || student.amount || 0;
                totalTransportFare += student.transport_fare || 0;
                totalDiscountAmount += student.discount_amount || 0;
                totalAmount += student.total_amount || 0;
                if (student.status === 'will_create') {
                    willCreate++;
                } else {
                    willSkip++;
                }
            });
            
            html += `
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bx bx-user me-2"></i>Single Invoice Generation Summary (${students.length} student(s))</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Class:</strong> ${validationData.class_name}</p>
                                <p><strong>Fee Group:</strong> ${validationData.fee_group_name}</p>
                                <p><strong>Period:</strong> ${getPeriodText(validationData.period)}${validationData.period == 5 ? ' (4 Quarters)' : ''}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Students:</strong> ${students.length}</p>
                                <p><strong>Academic Year:</strong> ${validationData.academic_year_name}</p>
                                <p><strong>Subtotal:</strong> ${totalSubtotal.toLocaleString()} TZS</p>
                                <p><strong>Transport Total:</strong> ${totalTransportFare.toLocaleString()} TZS</p>
                                <p><strong>Total Discount:</strong> <span class="text-danger">${totalDiscountAmount.toLocaleString()} TZS</span></p>
                                <p><strong>Total Amount:</strong> ${totalAmount.toLocaleString()} TZS</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>Student Details:</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover border-dark shadow-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-start">Student Name</th>
                                            <th class="text-start">Admission No</th>
                                            <th>Fee Amount (TZS)</th>
                                            <th>Transport Fare (TZS)</th>
                                            <th>Discount (TZS)</th>
                                            <th>Total Amount (TZS)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
            `;
            
            students.forEach((student, index) => {
                const studentName = student.name || student.student_name || 'N/A';
                const admissionNumber = student.admission_number || 'N/A';
                const subtotal = student.subtotal || student.amount || 0;
                const transportFare = student.transport_fare || 0;
                const discountAmount = student.discount_amount || 0;
                const totalAmount = student.total_amount || 0;
                
                let statusBadge = '';
                switch(student.status) {
                    case 'will_create':
                        statusBadge = '<span class="badge bg-success">Ready to Create</span>';
                        break;
                    case 'already_exists':
                        statusBadge = '<span class="badge bg-warning">Already Exists</span>';
                        break;
                    default:
                        statusBadge = '<span class="badge bg-secondary">Unknown</span>';
                }
                
                html += `
                                        <tr>
                                            <td>${studentName}</td>
                                            <td>${admissionNumber}</td>
                                            <td class="text-end">${subtotal.toLocaleString()}</td>
                                            <td class="text-end">${transportFare.toLocaleString()}</td>
                                            <td class="text-end">${discountAmount.toLocaleString()}</td>
                                            <td class="text-end"><strong>${totalAmount.toLocaleString()}</strong></td>
                                            <td class="text-center">${statusBadge}</td>
                                        </tr>
                `;
            });
            
            html += `
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                ${students.length} invoice(s) ready to be generated for the selected student(s).
                                ${willCreate > 0 ? `${willCreate} will be created.` : ''}
                                ${willSkip > 0 ? `${willSkip} already exist.` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
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
        const generationType = getSelectValue('#generation_type');

        if (generationType === 'bulk') {
            // Remove student_ids[] from form data for bulk generation
            formData = formData.filter(field => field.name !== 'student_id' && field.name !== 'student_ids[]');
        } else if (generationType === 'single') {
            // For single invoice, ensure we have student_ids[] array
            formData = formData.filter(field => field.name !== 'student_id');
            
            // Collect all selected student IDs
            const selectedStudentIds = [];
            $('.student-select').each(function() {
                const studentId = $(this).val();
                if (studentId && studentId !== '') {
                    selectedStudentIds.push(studentId);
                }
            });
            
            // Remove existing student_ids[] entries and add new ones
            formData = formData.filter(field => field.name !== 'student_ids[]');
            selectedStudentIds.forEach(function(studentId) {
                formData.push({name: 'student_ids[]', value: studentId});
            });
        }

        // Convert to object for proper array handling
        const formDataObj = {};
        formData.forEach(function(field) {
            if (field.name.endsWith('[]')) {
                // Handle array fields
                const key = field.name.replace('[]', '');
                if (!formDataObj[key]) {
                    formDataObj[key] = [];
                }
                formDataObj[key].push(field.value);
            } else {
                formDataObj[field.name] = field.value;
            }
        });
        
        // Convert back to query string with proper array format
        const formDataString = $.param(formDataObj);

        // Update progress to generation
        updateProgress('generation');

        // Disable form and buttons
        const generateBtn = $('#generate-btn');
        const originalText = generateBtn.html();
        generateBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Generating...');
        $('#fee-invoice-form input, #fee-invoice-form select, #fee-invoice-form button').prop('disabled', true);

        // Show processing notification with longer timeout message for bulk
        const isBulk = getSelectValue('#generation_type') === 'bulk';
        const processingMessage = isBulk 
            ? 'Please wait while we create the invoice(s). This may take several minutes for large classes...'
            : 'Please wait while we create the invoice(s)';
        
        Swal.fire({
            title: 'Generating Invoices...',
            html: processingMessage + '<br><small class="text-muted">Do not close this window</small>',
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
            timeout: 600000, // 10 minutes timeout for bulk generation (70+ students)
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
                    window.location.href = '{{ route("school.fee-invoices.index") }}';
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
                    errorMessage = 'Request timed out. The invoice generation is taking longer than expected. For large classes (70+ students), this is normal. Please try again or contact support if the issue persists.';
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

    // Add smooth scrolling for better UX
    $('a[href^="#"]').on('click', function(event) {
        const target = $(this.getAttribute('href'));
        if (target.length) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });

    // Initialize form state
    const initialGenerationType = $('#generation_type').val() || 'bulk';
    if (initialGenerationType === 'bulk') {
        $('#student-selection').hide();
    } else if (initialGenerationType === 'single') {
        $('#student-selection').show();
        // Only load students if a class is already selected
        if ($('#class_id').val()) {
            loadStudents();
        }
    }
    updatePreview();

    // Initialize progress to config step
    updateProgress('config');

    // Handle edit invoice button clicks
    $(document).on('click', '.edit-invoice-btn', function() {
        const invoiceId = $(this).data('invoice-id');
        const studentName = $(this).data('student-name');

        // Update modal title
        $('#editInvoiceModalLabel').html('<i class="bx bx-edit me-2"></i>Edit Invoice - ' + studentName);

        // Show modal
        $('#editInvoiceModal').modal('show');

        // Load edit form via AJAX
        loadEditForm(invoiceId);
    });

    // Function to load edit form
    function loadEditForm(invoiceId) {
        $('#editInvoiceModalBody').html(`
            <div class="text-center py-4">
                <i class="bx bx-loader-alt bx-spin text-primary" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">Loading edit form...</p>
            </div>
        `);

        // For debugging - let's check what URL we're trying to access
        console.log('Invoice ID:', invoiceId);
        const editUrl = '{{ route("school.fee-invoices.edit", ":invoiceId") }}'.replace(':invoiceId', invoiceId);
        console.log('Edit URL:', editUrl);

        $.ajax({
            url: editUrl,
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8'
            },
            success: function(response) {
                console.log('AJAX Success - Response received');
                // Extract the form content from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(response, 'text/html');
                const formContent = doc.querySelector('.card.shadow-sm.mb-4 .card-body');

                if (formContent) {
                    console.log('Form content found');
                    // Update modal body with form content
                    $('#editInvoiceModalBody').html(formContent.innerHTML);

                    // Remove the submit button from the form (we'll use modal footer button)
                    $('#editInvoiceModalBody form button[type="submit"]').remove();

                    // Initialize form elements
                    initializeEditForm();
                } else {
                    console.log('Form content not found');
                    $('#editInvoiceModalBody').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle me-1"></i>
                            Failed to load edit form. Form content not found in response.
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr.status, xhr.statusText, error);
                console.log('Response:', xhr.responseText);
                $('#editInvoiceModalBody').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-1"></i>
                        Failed to load edit form. Error: ${xhr.status} ${xhr.statusText}
                    </div>
                `);
            }
        });
    }

    // Function to initialize edit form elements
    function initializeEditForm() {
        // Auto-calculate total when amounts change
        $('#subtotal, #transport_fare').on('input', function() {
            const subtotal = parseFloat($('#subtotal').val()) || 0;
            const transportFare = parseFloat($('#transport_fare').val()) || 0;
            const total = subtotal + transportFare;

            $('#display-subtotal').text(subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#display-transport').text(transportFare.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $('#display-total').text(total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        });

        // Handle save button click
        $('#saveEditBtn').off('click').on('click', function() {
            saveEditForm();
        });

        // Handle form submission on Enter key
        $('#editInvoiceModalBody form').on('submit', function(e) {
            e.preventDefault();
            saveEditForm();
        });
    }

    // Function to refresh validation results
    function refreshValidationResults() {
        // Re-run validation to refresh the results
        let validationFormData = $('#fee-invoice-form').serializeArray();
        const generationType = getSelectValue('#generation_type');

        if (generationType === 'bulk') {
            validationFormData = validationFormData.filter(field => field.name !== 'student_id' && field.name !== 'student_ids[]');
        } else if (generationType === 'single') {
            // For single invoice, ensure we have student_ids[] array
            validationFormData = validationFormData.filter(field => field.name !== 'student_id');
            
            // Collect all selected student IDs
            const selectedStudentIds = [];
            $('.student-select').each(function() {
                const studentId = $(this).val();
                if (studentId && studentId !== '') {
                    selectedStudentIds.push(studentId);
                }
            });
            
            // Remove existing student_ids[] entries and add new ones
            validationFormData = validationFormData.filter(field => field.name !== 'student_ids[]');
            selectedStudentIds.forEach(function(studentId) {
                validationFormData.push({name: 'student_ids[]', value: studentId});
            });
        }

        // Convert to object for proper array handling
        const validationFormDataObj = {};
        validationFormData.forEach(function(field) {
            if (field.name.endsWith('[]')) {
                // Handle array fields
                const key = field.name.replace('[]', '');
                if (!validationFormDataObj[key]) {
                    validationFormDataObj[key] = [];
                }
                validationFormDataObj[key].push(field.value);
            } else {
                validationFormDataObj[field.name] = field.value;
            }
        });
        
        // Convert back to query string with proper array format
        const validationFormDataString = $.param(validationFormDataObj);

        // Show loading indicator
        $('#invoice-preview').html(`
            <div class="text-center py-4">
                <i class="bx bx-loader-alt bx-spin text-primary" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">Refreshing validation results...</p>
            </div>
        `);

        $.ajax({
            url: '{{ route("school.fee-invoices.validate-invoices") }}',
            type: 'POST',
            data: validationFormDataString,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(validationResponse) {
                displayValidationResults(validationResponse);
                
                // Update the button state based on refreshed validation results
                let hasStudentsToCreate = false;
                if (validationResponse.type === 'bulk') {
                    hasStudentsToCreate = validationResponse.students.some(student => student.status === 'will_create');
                } else {
                    // For single invoice, check if any student in the array will be created
                    const students = validationResponse.students || (validationResponse.student ? [validationResponse.student] : []);
                    hasStudentsToCreate = students.some(student => student.status === 'will_create');
                }

                const submitBtn = $('#fee-invoice-form').find('button[type="submit"], #generate-btn');
                if (hasStudentsToCreate) {
                    submitBtn.prop('disabled', false)
                        .removeClass('btn-primary btn-secondary')
                        .addClass('btn-success')
                        .attr('type', 'button')
                        .attr('id', 'generate-btn')
                        .html('<i class="bx bx-plus me-2"></i>Generate Invoices');

                    // Set up generate button click handler
                    $('#generate-btn').off('click').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        startActualGeneration(validationResponse);
                    });
                } else {
                    submitBtn.prop('disabled', true)
                        .removeClass('btn-primary btn-success')
                        .addClass('btn-secondary')
                        .attr('type', 'button')
                        .removeAttr('id')
                        .html('<i class="bx bx-check-circle me-2"></i>All Invoices Already Exist');
                }
            },
            error: function(xhr, status, error) {
                console.error('Failed to refresh validation results:', error);
                console.error('Status:', xhr.status, 'Response:', xhr.responseText);
                
                // Show error and reload page as fallback
                Swal.fire({
                    title: 'Refresh Failed',
                    text: 'Could not refresh validation results. Reloading page...',
                    icon: 'warning',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        });
    }

    // Function to save edit form
    function saveEditForm() {
        const form = $('#editInvoiceModalBody form');
        const formData = form.serialize();

        // Disable save button
        $('#saveEditBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Saving...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Close modal
                $('#editInvoiceModal').modal('hide');

                // Show success message
                Swal.fire({
                    title: 'Success!',
                    text: 'Invoice updated successfully!',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    // Wait a bit more after modal closes, then refresh
                    setTimeout(() => {
                        refreshValidationResults();
                    }, 500);
                });
            },
            error: function(xhr, status, error) {
                // Re-enable save button
                $('#saveEditBtn').prop('disabled', false).html('<i class="bx bx-save me-1"></i>Update Invoice');

                let errorMessage = 'An error occurred while updating the invoice.';
                let errorTitle = 'Update Error';

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = 'Validation failed:\n';
                    for (let field in errors) {
                        errorMessage += `- ${errors[field][0]}\n`;
                    }
                    errorTitle = 'Validation Error';
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

    // Reset modal when closed
    $('#editInvoiceModal').on('hidden.bs.modal', function() {
        $('#editInvoiceModalBody').html(`
            <div class="text-center py-4">
                <i class="bx bx-loader-alt bx-spin text-primary" style="font-size: 2rem;"></i>
                <p class="text-muted mt-2">Loading edit form...</p>
            </div>
        `);
        $('#saveEditBtn').prop('disabled', false).html('<i class="bx bx-save me-1"></i>Update Invoice');
    });

});
</script>
@endpush