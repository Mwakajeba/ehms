@extends('layouts.main')

@section('title', 'Edit College Fee Setting')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Fee Settings', 'url' => route('college.fee-settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT COLLEGE FEE SETTING</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-edit me-1 font-22 text-warning"></i></div>
                                <h5 class="mb-0 text-warning">Edit College Fee Setting</h5>
                            </div>
                            <div>
                                <a href="{{ route('college.fee-settings.show', $feeSetting) }}" class="btn btn-info me-2">
                                    <i class="bx bx-show me-1"></i> View Details
                                </a>
                                <a href="{{ route('college.fee-settings.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <hr />

                        <form id="feeSettingForm" action="{{ route('college.fee-settings.update', $feeSetting) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-info-circle me-1"></i> Basic Information
                                    </h6>

                                    <div class="mb-3">
                                        <label for="program_id" class="form-label">Program <span class="text-danger">*</span></label>
                                        <select class="form-select select2" id="program_id" name="program_id" required>
                                            <option value="">Select Program</option>
                                            @foreach($programs as $program)
                                                <option value="{{ $program->id }}" {{ $feeSetting->program_id == $program->id ? 'selected' : '' }}>
                                                    {{ $program->name }} ({{ $program->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('program_id')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="date_from" class="form-label">Start Date</label>
                                                <input type="date" class="form-control @error('date_from') is-invalid @enderror" id="date_from" name="date_from" value="{{ old('date_from', $feeSetting->date_from ? $feeSetting->date_from->format('Y-m-d') : '') }}">
                                                @error('date_from')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="date_to" class="form-label">End Date</label>
                                                <input type="date" class="form-control @error('date_to') is-invalid @enderror" id="date_to" name="date_to" value="{{ old('date_to', $feeSetting->date_to ? $feeSetting->date_to->format('Y-m-d') : '') }}">
                                                @error('date_to')
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                                   {{ $feeSetting->is_active ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Lines -->
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="bx bx-list-ul fs-5"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 fw-bold text-primary">Fee Configuration</h5>
                                                <small>Configure fee amounts for different groups and periods</small>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-lg px-4" id="addFeeLineBtn" style="border-radius: 25px;">
                                            <i class="bx bx-plus me-2"></i> Add Fee Line
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="feeLinesContainer">
                                        @if($feeSetting->collegeFeeSettingItems && $feeSetting->collegeFeeSettingItems->count() > 0)
                                            @foreach($feeSetting->collegeFeeSettingItems as $index => $item)
                                                <div class="fee-line border rounded-3 p-4 mb-3" data-index="{{ $index }}" style="border-color: #dee2e6 !important;">
                                                    <div class="row g-3 align-items-end">
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold">
                                                                <i class="bx bx-group text-primary me-1"></i>Fee Group <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control form-control-lg select2 fee-group-select" name="fee_lines[{{ $index }}][fee_group_id]" required>
                                                                <option value="">Select Fee Group</option>
                                                                @foreach($feeGroups as $feeGroup)
                                                                    <option value="{{ $feeGroup->id }}" {{ $item->fee_group_id == $feeGroup->id ? 'selected' : '' }}>
                                                                        {{ $feeGroup->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label class="form-label fw-semibold">
                                                                <i class="bx bx-calendar text-primary me-1"></i>Fee Period <span class="text-danger">*</span>
                                                            </label>
                                                            <select class="form-control form-control-lg fee-period-select" name="fee_lines[{{ $index }}][fee_period]" required>
                                                                <option value="">Select Period</option>
                                                                @foreach($feePeriodOptions as $key => $value)
                                                                    <option value="{{ $key }}" {{ $item->fee_period == $key ? 'selected' : '' }}>
                                                                        {{ $value }}
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
                                                                <input type="number" class="form-control form-control-lg amount-input" name="fee_lines[{{ $index }}][amount]"
                                                                       value="{{ $item->amount }}" step="0.01" min="0" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 d-flex justify-content-center">
                                                            <button type="button" class="btn btn-outline-danger btn-lg remove-fee-line d-flex align-items-center justify-content-center" data-index="{{ $index }}" style="width: 50px; height: 50px; border-radius: 50%; padding: 0;" title="Remove this fee line">
                                                                <i class="bx bx-trash fs-4"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-file me-1"></i> Description
                                    </h6>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                              placeholder="Optional description for this fee setting">{{ $feeSetting->description }}</textarea>
                                    @error('description')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="row">
                                <div class="col-12">
                                    <hr />
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                            <i class="bx bx-x me-1"></i> Cancel
                                        </button>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bx bx-save me-1"></i> Update Fee Setting
                                        </button>
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
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .font-22 {
        font-size: 1.375rem !important;
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

    .amount-input:focus,
    .form-control:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Select an option';
        }
    });

    let feeLineIndex = {{ $feeSetting->collegeFeeSettingItems ? $feeSetting->collegeFeeSettingItems->count() : 0 }};

    // Add fee line function
    window.addFeeLine = function() {
        const container = $('#feeLinesContainer');
        const newIndex = feeLineIndex++;

        const feeLineHtml = `
            <div class="fee-line border rounded-3 p-4 mb-3" data-index="${newIndex}" style="border-color: #dee2e6 !important;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bx bx-group text-primary me-1"></i>Fee Group <span class="text-danger">*</span>
                        </label>
                        <select class="form-control form-control-lg select2 fee-group-select" name="fee_lines[${newIndex}][fee_group_id]" required>
                            <option value="">Select Fee Group</option>
                            @foreach($feeGroups as $feeGroup)
                                <option value="{{ $feeGroup->id }}">{{ $feeGroup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">
                            <i class="bx bx-calendar text-primary me-1"></i>Fee Period <span class="text-danger">*</span>
                        </label>
                        <select class="form-control form-control-lg fee-period-select" name="fee_lines[${newIndex}][fee_period]" required>
                            <option value="">Select Period</option>
                            @foreach($feePeriodOptions as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="bx bx-money text-primary me-1"></i>Amount <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light">{{ config('app.currency', 'TZS') }}</span>
                            <input type="number" class="form-control form-control-lg amount-input" name="fee_lines[${newIndex}][amount]" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex justify-content-center">
                        <button type="button" class="btn btn-outline-danger btn-lg remove-fee-line d-flex align-items-center justify-content-center" data-index="${newIndex}" style="width: 50px; height: 50px; border-radius: 50%; padding: 0;" title="Remove this fee line">
                            <i class="bx bx-trash fs-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.append(feeLineHtml);

        // Initialize Select2 for the new element
        container.find('.fee-line:last .select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    };

    // Add fee line button click
    $('#addFeeLineBtn').on('click', function() {
        addFeeLine();
    });

    // Remove fee line
    $(document).on('click', '.remove-fee-line', function() {
        $(this).closest('.fee-line').remove();
    });

    // Initialize Select2 for existing fee selects
    $('.fee-group-select, .fee-period-select').select2({
        width: '100%',
        theme: 'bootstrap-5'
    });

    // Form validation
    $('#feeSettingForm').on('submit', function(e) {
        const feeLines = $('.fee-line');
        if (feeLines.length === 0) {
            e.preventDefault();
            alert('Please add at least one fee line.');
            return false;
        }

        // Validate amounts
        let hasError = false;
        $('.amount-input').each(function() {
            const value = parseFloat($(this).val());
            if (isNaN(value) || value < 0) {
                hasError = true;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (hasError) {
            e.preventDefault();
            alert('Please enter valid amounts for all fee lines.');
            return false;
        }

        return true;
    });

    // Format amount inputs
    $(document).on('input', '.amount-input', function() {
        const value = $(this).val();
        if (value && !isNaN(value)) {
            $(this).val(parseFloat(value).toFixed(2));
        }
    });
});
</script>
@endpush