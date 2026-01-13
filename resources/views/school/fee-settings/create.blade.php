@extends('layouts.main')

@section('title', 'Create New Fee Setting')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Settings', 'url' => route('school.fee-settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW FEE SETTING</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Fee Setting</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.fee-settings.store') }}" method="POST" id="fee-setting-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                        <select class="form-control select2-single @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
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
                                        <label for="fee_period" class="form-label">Fee Period <span class="text-danger">*</span></label>
                                        <select class="form-control select2-single @error('fee_period') is-invalid @enderror" id="fee_period" name="fee_period" required>
                                            <option value="">Select Fee Period</option>
                                            @foreach($feePeriodOptions as $value => $label)
                                                <option value="{{ $value }}" {{ old('fee_period') == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('fee_period')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('date_from') is-invalid @enderror" id="start_date" name="date_from" value="{{ old('date_from') }}" required>
                                        @error('date_from')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('date_to') is-invalid @enderror" id="end_date" name="date_to" value="{{ old('date_to') }}" required>
                                        @error('date_to')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Lines Section -->
                            <div class="mb-4">
                                <h6 class="text-primary mb-3">
                                    <i class="bx bx-list-ul me-1"></i> Fee Configuration
                                </h6>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <p class="text-muted small mb-0">Add fee lines for different student categories</p>
                                    <button type="button" class="btn btn-primary btn-sm" id="add-fee-line">
                                        <i class="bx bx-plus me-1"></i> Add Fee Line
                                    </button>
                                </div>

                                <div id="fee-lines-container">
                                    <!-- Fee lines will be added here dynamically -->
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
                                <a href="{{ route('school.fee-settings.index') }}" class="btn btn-secondary">
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
                            <h6>What is a Fee Setting?</h6>
                            <p class="small text-muted">
                                Fee settings define the fee structure for specific classes and academic periods. Each setting can contain multiple fee lines,
                                allowing you to configure different amounts for Day and Boarding students, with optional transport fees for Day students.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Fee Configuration:</h6>
                            <ul class="small text-muted mb-2">
                                <li><strong>Fee Lines:</strong> Add multiple fee entries per setting, each with its own category and amount</li>
                                <li><strong>Day Students:</strong> Can include transport fees by checking "Include Transport" for relevant fee lines</li>
                                <li><strong>Boarding Students:</strong> Standard boarding fees without transport options</li>
                                <li><strong>Flexible Setup:</strong> Mix Day and Boarding fee lines within the same setting</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Fee Periods:</h6>
                            <ul class="small text-muted">
                                <li><strong>Q1, Q2, Q3, Q4:</strong> Quarterly fee collection periods</li>
                                <li><strong>Semi Annual:</strong> Two-term academic year structure</li>
                                <li><strong>Annual:</strong> Full year fee payment option</li>
                            </ul>
                        </div>
                        <div class="alert alert-light small">
                            <i class="bx bx-bulb me-1 text-warning"></i>
                            <strong>Tip:</strong> Use multiple fee lines to create comprehensive fee structures. For example, combine tuition fees with additional service charges for different student categories.
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
        min-height: 38px;
    }

    .select2-container--bootstrap-5 .select2-selection:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #0d6efd;
        color: white;
    }

    /* Alert styling */
    .alert-light {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }

    /* Fee line styling */
    .fee-line {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .fee-line .btn-remove {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    .fee-line .btn-remove:hover {
        background-color: #c82333;
        border-color: #bd2130;
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
        function addFeeLine(category = '', amount = '', includeTransport = false, itemName = '') {
            feeLineCounter++;
            const feeLineHtml = `
                <div class="fee-line card border mb-3" data-line-id="${feeLineCounter}">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-control category-select" name="fee_lines[${feeLineCounter}][category]" required>
                                    <option value="">Select Category</option>
                                    @foreach($categoryOptions as $value => $label)
                                        <option value="{{ $value }}" ${category === '{{ $value }}' ? 'selected' : ''}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fee Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ config('app.currency', 'TZS') }}</span>
                                    <input type="number" class="form-control amount-input" name="fee_lines[${feeLineCounter}][amount]" value="${amount}" step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-3 transport-col" style="display: none;">
                                <div class="form-check">
                                    <input class="form-check-input transport-checkbox" type="checkbox" name="fee_lines[${feeLineCounter}][include_transport]" value="1" ${includeTransport ? 'checked' : ''} id="transport_${feeLineCounter}">
                                    <label class="form-check-label" for="transport_${feeLineCounter}">
                                        Include Transport
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger btn-sm remove-fee-line" data-line-id="${feeLineCounter}">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#fee-lines-container').append(feeLineHtml);
            updateTransportVisibility();
        }

        // Function to remove a fee line
        window.removeFeeLine = function(lineId) {
            $(`.fee-line[data-line-id="${lineId}"]`).remove();
            updateTransportVisibility();
        };

        // Function to update transport checkbox visibility based on category
        function updateTransportVisibility() {
            $('.fee-line').each(function() {
                const category = $(this).find('.category-select').val();
                const transportCol = $(this).find('.transport-col');

                if (category === 'day') {
                    transportCol.show();
                } else {
                    transportCol.hide();
                    $(this).find('.transport-checkbox').prop('checked', false);
                }
            });
        }

        // Add fee line button handler
        $('#add-fee-line').on('click', function() {
            addFeeLine();
        });

        // Event listener for remove fee line button
        $(document).on('click', '.remove-fee-line', function() {
            const lineId = $(this).data('line-id');
            removeFeeLine(lineId);
        });

        // Event listener for category change
        $(document).on('change', '.category-select', function() {
            updateTransportVisibility();
        });

        // Add initial fee line if none exist
        if ($('.fee-line').length === 0) {
            addFeeLine();
        }

        // Form validation
        $('#fee-setting-form').on('submit', function(e) {
            let isValid = true;
            const feeLines = $('.fee-line');

            if (feeLines.length === 0) {
                alert('Please add at least one fee line.');
                isValid = false;
            }

            feeLines.each(function() {
                const category = $(this).find('.category-select').val();
                const amount = $(this).find('.amount-input').val();

                if (!category) {
                    $(this).find('.category-select').addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).find('.category-select').removeClass('is-invalid');
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

        console.log('Create fee setting form loaded with Select2 and dynamic fee lines');
    });
</script>
@endpush