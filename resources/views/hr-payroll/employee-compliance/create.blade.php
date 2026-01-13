@extends('layouts.main')

@section('title', 'Create Compliance Record')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumbs -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'HR & Payroll', 'url' => route('hr-payroll.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Employee Compliance', 'url' => route('hr.employee-compliance.index'), 'icon' => 'bx bx-check-circle'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0 text-uppercase">
                <i class="bx bx-check-circle me-1"></i>Create Compliance Record
            </h6>
            <a href="{{ route('hr.employee-compliance.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to List
            </a>
        </div>
        <hr />

        <!-- Info Alert -->
        <div class="alert alert-info border-0 bg-light-info alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-info-circle me-2 fs-4"></i>
                <div>
                    <strong>Why Compliance Records Matter:</strong>
                    <ul class="mb-0 mt-2 ps-3">
                        <li>Ensures employees meet statutory requirements for payroll processing</li>
                        <li>Prevents payroll errors and compliance violations</li>
                        <li>Tracks expiry dates to avoid service interruptions</li>
                        <li>Required for accurate tax and contribution calculations</li>
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>

        <!-- Existing Compliance Warning -->
        @if(isset($existingCompliance) && $existingCompliance)
        <div class="alert alert-warning border-0 bg-light-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="bx bx-error-circle me-2 fs-4"></i>
                <div>
                    <strong>Existing Compliance Record Found:</strong>
                    <p class="mb-1 mt-2">
                        This employee already has a <strong>{{ strtoupper($existingCompliance->compliance_type) }}</strong> compliance record.
                    </p>
                    <div class="small">
                        <strong>Current Details:</strong><br>
                        Compliance Number: {{ $existingCompliance->compliance_number ?? 'Not set' }}<br>
                        Status: <span class="badge bg-{{ $existingCompliance->isValid() ? 'success' : 'danger' }}">
                            {{ $existingCompliance->isValid() ? 'Valid' : 'Invalid' }}
                        </span><br>
                        @if($existingCompliance->expiry_date)
                            Expiry Date: {{ $existingCompliance->expiry_date->format('d M Y') }}
                        @else
                            Expiry Date: No expiry
                        @endif
                    </div>
                    <p class="mb-0 mt-2">
                        <strong>Note:</strong> Submitting this form will <strong>update</strong> the existing record instead of creating a new one. 
                        Each employee can only have one record per compliance type.
                    </p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Main Form Card -->
        <div class="card border-top border-0 border-4 border-primary">
            <div class="card-body">
                <form method="POST" action="{{ route('hr.employee-compliance.store') }}" id="complianceForm">
                    @csrf

                    <!-- Employee Selection Section -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bx bx-user me-1"></i>Employee Information
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    Employee <span class="text-danger">*</span>
                                    <i class="bx bx-info-circle text-muted ms-1" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Select the employee for whom you are creating this compliance record. Each employee can have multiple compliance records for different types (PAYE, Pension, NHIF, etc.)."></i>
                                </label>
                                <select name="employee_id" 
                                        id="employee_id" 
                                        class="form-select select2-single @error('employee_id') is-invalid @enderror" 
                                        required>
                                    <option value="">-- Search and Select Employee --</option>
                                    @foreach($employees ?? [] as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->full_name }} ({{ $employee->employee_number }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Start typing to search by name or employee number. This compliance record will be linked to the selected employee.
                                </div>
                                @error('employee_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" />

                    <!-- Compliance Details Section -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bx bx-file me-1"></i>Compliance Details
                        </h6>
                        <div class="row g-3">
                            <!-- Compliance Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Compliance Type <span class="text-danger">*</span>
                                    <i class="bx bx-info-circle text-muted ms-1" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Select the type of statutory compliance. Each type represents a different legal requirement for payroll processing."></i>
                                </label>
                                <select name="compliance_type" 
                                        id="compliance_type" 
                                        class="form-select @error('compliance_type') is-invalid @enderror" 
                                        required>
                                    <option value="">-- Select Compliance Type --</option>
                                    <option value="paye" {{ old('compliance_type') == 'paye' ? 'selected' : '' }}>
                                        PAYE (TIN) - Tax Identification Number
                                    </option>
                                    <option value="pension" {{ old('compliance_type') == 'pension' ? 'selected' : '' }}>
                                        Pension - Social Security Fund
                                    </option>
                                    <option value="nhif" {{ old('compliance_type') == 'nhif' ? 'selected' : '' }}>
                                        NHIF - National Health Insurance Fund
                                    </option>
                                    <option value="wcf" {{ old('compliance_type') == 'wcf' ? 'selected' : '' }}>
                                        WCF - Workers Compensation Fund
                                    </option>
                                    <option value="sdl" {{ old('compliance_type') == 'sdl' ? 'selected' : '' }}>
                                        SDL - Skills Development Levy
                                    </option>
                                </select>
                                <div class="form-text" id="compliance_type_help">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Select the type of compliance record you are creating.
                                </div>
                                @error('compliance_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Compliance Number -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Compliance Number
                                    <i class="bx bx-info-circle text-muted ms-1" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Enter the official registration or membership number issued by the relevant authority."></i>
                                </label>
                                <input type="text" 
                                       name="compliance_number" 
                                       id="compliance_number"
                                       class="form-control @error('compliance_number') is-invalid @enderror" 
                                       value="{{ old('compliance_number') }}" 
                                       placeholder="Enter compliance number (e.g., TIN, Membership No.)" />
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    <span id="compliance_number_help">Enter the official number if available. This helps with verification and reporting.</span>
                                </div>
                                @error('compliance_number')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4" />

                    <!-- Validity & Expiry Section -->
                    <div class="mb-4">
                        <h6 class="mb-3 text-primary">
                            <i class="bx bx-calendar-check me-1"></i>Validity & Expiry
                        </h6>
                        <div class="row g-3">
                            <!-- Expiry Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Expiry Date
                                    <i class="bx bx-info-circle text-muted ms-1" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Set the date when this compliance record expires. Leave blank if it does not expire. The system will alert you when expiry is approaching."></i>
                                </label>
                                <input type="date" 
                                       name="expiry_date" 
                                       id="expiry_date"
                                       class="form-control @error('expiry_date') is-invalid @enderror" 
                                       value="{{ old('expiry_date') }}" 
                                       min="{{ date('Y-m-d') }}" />
                                <div class="form-text">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Leave blank if the compliance does not expire. The system will send alerts 30 days before expiry.
                                </div>
                                @error('expiry_date')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Is Valid Toggle -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold d-block mb-2">
                                    Compliance Status
                                    <i class="bx bx-info-circle text-muted ms-1" 
                                       data-bs-toggle="tooltip" 
                                       data-bs-placement="top" 
                                       title="Mark this compliance record as valid if it is currently active and meets all requirements. Invalid records will block payroll processing."></i>
                                </label>
                                <div class="form-check form-switch form-switch-lg">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="is_valid" 
                                           id="is_valid" 
                                           value="1" 
                                           {{ old('is_valid', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-normal" for="is_valid">
                                        <span id="valid_status_text">Valid & Active</span>
                                    </label>
                                </div>
                                <div class="form-text mt-2">
                                    <i class="bx bx-info-circle me-1"></i>
                                    <span id="valid_status_help">
                                        When checked, this compliance is considered valid and the employee can be included in payroll. 
                                        Uncheck if the compliance is pending, expired, or invalid.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex gap-2 mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-save me-1"></i>Save Compliance Record
                        </button>
                        <a href="{{ route('hr.employee-compliance.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="bx bx-x me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }

    .border-primary {
        border-color: #0d6efd !important;
    }

    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }

    .form-switch-lg .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }

    .form-switch-lg .form-check-label {
        padding-left: 0.75rem;
        font-size: 1rem;
    }

    .select2-container {
        width: 100% !important;
    }

    .alert-info {
        background-color: #e7f3ff;
        border-color: #b3d9ff;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for employee selection
    if ($('#employee_id').length && typeof $.fn.select2 !== 'undefined') {
        $('#employee_id').select2({
            placeholder: 'Search and select employee...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });
    }

    // Check for existing compliance when employee and type are selected
    let checkExistingCompliance = function() {
        const employeeId = $('#employee_id').val();
        const complianceType = $('#compliance_type').val();

        if (employeeId && complianceType) {
            $.ajax({
                url: '{{ route("hr.employee-compliance.check-existing") }}',
                method: 'GET',
                data: {
                    employee_id: employeeId,
                    compliance_type: complianceType
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.exists) {
                        // Show warning alert
                        let alertHtml = `
                            <div class="alert alert-warning border-0 bg-light-warning alert-dismissible fade show existing-compliance-alert" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-error-circle me-2 fs-4"></i>
                                    <div>
                                        <strong>Existing Compliance Record Found:</strong>
                                        <p class="mb-1 mt-2">
                                            This employee already has a <strong>${response.compliance_type.toUpperCase()}</strong> compliance record.
                                        </p>
                                        <div class="small">
                                            <strong>Current Details:</strong><br>
                                            Compliance Number: ${response.compliance_number || 'Not set'}<br>
                                            Status: <span class="badge bg-${response.is_valid ? 'success' : 'danger'}">
                                                ${response.is_valid ? 'Valid' : 'Invalid'}
                                            </span><br>
                                            ${response.expiry_date ? 'Expiry Date: ' + response.expiry_date : 'Expiry Date: No expiry'}
                                        </div>
                                        <p class="mb-0 mt-2">
                                            <strong>Note:</strong> Submitting this form will <strong>update</strong> the existing record instead of creating a new one. 
                                            Each employee can only have one record per compliance type.
                                        </p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        
                        // Remove existing alert if any
                        $('.existing-compliance-alert').remove();
                        // Insert after info alert
                        $('.alert-info').after(alertHtml);
                    } else {
                        // Remove warning if no existing record
                        $('.existing-compliance-alert').remove();
                    }
                },
                error: function() {
                    // Silently fail - don't block user
                }
            });
        } else {
            // Remove warning if fields are cleared
            $('.existing-compliance-alert').remove();
        }
    };

    // Check on employee or compliance type change
    $('#employee_id, #compliance_type').on('change', checkExistingCompliance);

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Dynamic help text based on compliance type
    const complianceTypeHelp = {
        'paye': 'PAYE (Pay As You Earn) - Tax Identification Number issued by Tanzania Revenue Authority (TRA). Required for all employees earning above the tax threshold.',
        'pension': 'Pension - Social Security Fund membership (NSSF/PSSSF). Required for employees contributing to pension schemes. Both employee and employer contributions are tracked.',
        'nhif': 'NHIF - National Health Insurance Fund membership. Required for employees enrolled in the national health insurance scheme.',
        'wcf': 'WCF - Workers Compensation Fund. Employer contribution required for workplace injury coverage. Industry-based rates apply.',
        'sdl': 'SDL - Skills Development Levy. Employer-only contribution based on gross payroll percentage. Used for employee training and development programs.'
    };

    const complianceNumberPlaceholders = {
        'paye': 'Enter TIN (Tax Identification Number)',
        'pension': 'Enter Pension/Social Fund Membership Number',
        'nhif': 'Enter NHIF Membership Number',
        'wcf': 'Enter WCF Registration Number (if applicable)',
        'sdl': 'Enter SDL Registration Number (if applicable)'
    };

    $('#compliance_type').on('change', function() {
        const selectedType = $(this).val();
        const helpText = complianceTypeHelp[selectedType] || 'Select the type of compliance record you are creating.';
        const placeholder = complianceNumberPlaceholders[selectedType] || 'Enter compliance number (e.g., TIN, Membership No.)';
        
        $('#compliance_type_help').html('<i class="bx bx-info-circle me-1"></i>' + helpText);
        $('#compliance_number').attr('placeholder', placeholder);
        $('#compliance_number_help').text(helpText.includes('required') ? 
            'This number is typically required for this compliance type.' : 
            'Enter the official number if available. This helps with verification and reporting.');
    });

    // Trigger change on page load if value exists
    if ($('#compliance_type').val()) {
        $('#compliance_type').trigger('change');
    }

    // Toggle valid status text
    $('#is_valid').on('change', function() {
        if ($(this).is(':checked')) {
            $('#valid_status_text').text('Valid & Active');
            $('#valid_status_help').text('When checked, this compliance is considered valid and the employee can be included in payroll. Uncheck if the compliance is pending, expired, or invalid.');
        } else {
            $('#valid_status_text').text('Invalid or Pending');
            $('#valid_status_help').text('This compliance is marked as invalid. The employee may be excluded from payroll until this is resolved.');
        }
    });

    // Form validation
    $('#complianceForm').on('submit', function(e) {
        const employeeId = $('#employee_id').val();
        const complianceType = $('#compliance_type').val();

        if (!employeeId) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select an employee.',
                confirmButtonText: 'OK'
            });
            $('#employee_id').focus();
            return false;
        }

        if (!complianceType) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a compliance type.',
                confirmButtonText: 'OK'
            });
            $('#compliance_type').focus();
            return false;
        }

        // Check if expiry date is in the past
        const expiryDate = $('#expiry_date').val();
        if (expiryDate && new Date(expiryDate) < new Date()) {
            Swal.fire({
                icon: 'warning',
                title: 'Expired Date',
                text: 'The expiry date you entered is in the past. Are you sure you want to continue?',
                showCancelButton: true,
                confirmButtonText: 'Yes, Continue',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
});
</script>
@endpush
