@extends('layouts.main')

@section('title', 'Create New College Fee Setting')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Fee Settings', 'url' => route('college.fee-settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW COLLEGE FEE SETTING</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New College Fee Setting</h5>
                        </div>
                        <hr />

                        <form action="{{ route('college.fee-settings.store') }}" method="POST" id="fee-setting-form">
                            @csrf

                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="program_id" class="form-label">Program <span class="text-danger">*</span></label>
                                        <select class="form-control select2-single @error('program_id') is-invalid @enderror" id="program_id" name="program_id" required>
                                            <option value="">Select Program</option>
                                            @foreach($programs as $program)
                                                <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                                    {{ $program->name }} ({{ $program->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('program_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_from" class="form-label">Start Date</label>
                                        <input type="date" class="form-control @error('date_from') is-invalid @enderror" id="date_from" name="date_from" value="{{ old('date_from') }}">
                                        @error('date_from')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="date_to" class="form-label">End Date</label>
                                        <input type="date" class="form-control @error('date_to') is-invalid @enderror" id="date_to" name="date_to" value="{{ old('date_to') }}">
                                        @error('date_to')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Lines Section -->
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="bx bx-list-ul fs-5"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 fw-bold text-primary">Fee Configuration</h5>
                                                <small class="text-muted">Configure fee amounts for different groups and periods</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-lg px-4" id="add-fee-line" style="border-radius: 25px;">
                                            <i class="bx bx-plus me-2"></i> Add Fee Line
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="fee-lines-container">
                                        <!-- Fee lines will be added here dynamically -->
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Optional description of the fee setting">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Fee Setting
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('college.fee-settings.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Fee Settings
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Fee Setting
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
                            <h6>What is a College Fee Setting?</h6>
                            <p class="small text-muted">
                                College fee settings define the fee structure for specific programs and academic periods. Each setting can contain multiple fee lines,
                                allowing you to configure different amounts for various fee groups and student categories.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Fee Configuration:</h6>
                            <ul class="small text-muted mb-2">
                                <li><strong>Fee Lines:</strong> Add multiple fee entries per setting, each linked to a fee group</li>
                                <li><strong>Fee Groups:</strong> Select from predefined fee groups (Tuition, Registration, etc.)</li>
                                <li><strong>Student Categories:</strong> Regular, International, or Special student categories</li>
                                <li><strong>Transport Fees:</strong> Optional transport fee inclusion for applicable programs</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Fee Periods:</h6>
                            <ul class="small text-muted">
                                <li><strong>Semester 1:</strong> First semester fee collection</li>
                                <li><strong>Semester 2:</strong> Second semester fee collection</li>
                                <li><strong>Full year:</strong> Complete academic year fee payment</li>
                            </ul>
                        </div>
                        <div class="alert alert-light small">
                            <i class="bx bx-bulb me-1 text-warning"></i>
                            <strong>Tip:</strong> Use multiple fee lines to create comprehensive fee structures. For example, combine tuition fees with registration and other service charges.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .form-text {
        font-size: 0.875rem;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    /* Custom checkbox styling */
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .form-check-label {
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }

    /* Input focus styling */
    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    /* Select2 custom styling */
    .select2-container--bootstrap-5 .select2-selection {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        min-height: 48px;
    }

    .select2-container--bootstrap-5 .select2-selection:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #0d6efd;
        color: white;
    }

    .select2-container--bootstrap-5 .select2-results__option {
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
    }

    /* Alert styling */
    .alert-light {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }

    /* Fee line styling */
    .fee-line {
        transition: all 0.3s ease;
    }

    .fee-line:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .remove-fee-line:hover {
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
        color: white !important;
        transform: scale(1.1);
    }
</style>
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize Select2 for single select dropdowns
        $('.select2-single').select2({
            placeholder: 'Select an option',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5'
        });

        let feeLineCounter = 0;

        // Function to add a new fee line
        function addFeeLine(feeGroupId = '', amount = '', feePeriod = '') {
            feeLineCounter++;
            const feeLineHtml = `
                <div class="fee-line border rounded-3 p-4 mb-3" data-line-id="${feeLineCounter}" style="border-color: #dee2e6 !important;">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-group text-primary me-1"></i>Fee Group <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-lg fee-group-select" name="fee_lines[${feeLineCounter}][fee_group_id]" required>
                                <option value="">Select Fee Group</option>
                                @foreach($feeGroups as $feeGroup)
                                    <option value="{{ $feeGroup->id }}" ${feeGroupId == '{{ $feeGroup->id }}' ? 'selected' : ''}>
                                        {{ $feeGroup->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-calendar text-primary me-1"></i>Fee Period <span class="text-danger">*</span>
                            </label>
                            <select class="form-control form-control-lg fee-period-select" name="fee_lines[${feeLineCounter}][fee_period]" required>
                                <option value="">Select Period</option>
                                @foreach($feePeriodOptions as $value => $label)
                                    <option value="{{ $value }}" ${feePeriod == '{{ $value }}' ? 'selected' : ''}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-money text-primary me-1"></i>Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light">{{ config('app.currency', 'TZS') }}</span>
                                <input type="number" class="form-control form-control-lg amount-input" name="fee_lines[${feeLineCounter}][amount]" value="${amount}" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex justify-content-center">
                            <button type="button" class="btn btn-outline-danger btn-lg remove-fee-line d-flex align-items-center justify-content-center" data-line-id="${feeLineCounter}" style="width: 50px; height: 50px; border-radius: 50%; padding: 0;" title="Remove this fee line">
                                <i class="bx bx-trash fs-4"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#fee-lines-container').append(feeLineHtml);

            // Initialize Select2 for the new selects
            $(`.fee-line[data-line-id="${feeLineCounter}"] .fee-group-select, .fee-line[data-line-id="${feeLineCounter}"] .fee-period-select`).select2({
                width: '100%',
                theme: 'bootstrap-5'
            });
        }

        // Function to remove a fee line
        window.removeFeeLine = function(lineId) {
            $(`.fee-line[data-line-id="${lineId}"]`).remove();
        };

        // Add fee line button handler
        $('#add-fee-line').on('click', function() {
            addFeeLine();
        });

        // Event listener for remove fee line button
        $(document).on('click', '.remove-fee-line', function() {
            const lineId = $(this).data('line-id');
            removeFeeLine(lineId);
        });

        // Add initial fee line if none exist
        if ($('.fee-line').length === 0) {
            addFeeLine();
        }

        // Initialize Select2 for fee group and period selects
        $('.fee-group-select, .fee-period-select').select2({
            width: '100%',
            theme: 'bootstrap-5'
        });

        // Form validation
        $('#fee-setting-form').on('submit', function(e) {
            let isValid = true;
            const feeLines = $('.fee-line');

            if (feeLines.length === 0) {
                alert('Please add at least one fee line.');
                isValid = false;
            }

            feeLines.each(function() {
                const feeGroupId = $(this).find('.fee-group-select').val();
                const amount = $(this).find('.amount-input').val();

                if (!feeGroupId) {
                    $(this).find('.fee-group-select').addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).find('.fee-group-select').removeClass('is-invalid');
                }

                if (!amount || parseFloat(amount) <= 0) {
                    $(this).find('.amount-input').addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).find('.amount-input').removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields correctly.');
            }
        });

        console.log('Create college fee setting form loaded with Select2 and dynamic fee lines');
    });
</script>
@endpush