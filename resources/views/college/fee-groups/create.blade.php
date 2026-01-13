@extends('layouts.main')

@section('title', 'Create College Fee Group')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('college.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Groups', 'url' => route('college.fee-groups.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE COLLEGE FEE GROUP</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Create Fee Group</h5>
                        </div>
                        <hr />

                        <form action="{{ route('college.fee-groups.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fee_code" class="form-label">Fee Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('fee_code') is-invalid @enderror" id="fee_code" name="fee_code" value="{{ old('fee_code') }}" placeholder="e.g., TUITION, EXAM, LAB" required>
                                        @error('fee_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Fee Group Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., Tuition Fees, Examination Fees, Lab Fees" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Enter description for this fee group">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="receivable_account_id" class="form-label">Receivable Account <span class="text-danger">*</span></label>
                                        <select class="form-select @error('receivable_account_id') is-invalid @enderror" id="receivable_account_id" name="receivable_account_id" required>
                                            <option value="">Select Receivable Account</option>
                                            @foreach($chartAccounts as $account)
                                                <option value="{{ $account->id }}" {{ old('receivable_account_id') == $account->id ? 'selected' : '' }}
                                                        data-name="{{ $account->account_name }}"
                                                        data-code="{{ $account->account_code }}">
                                                    {{ $account->account_name }} ({{ $account->account_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('receivable_account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="income_account_id" class="form-label">Income Account <span class="text-danger">*</span></label>
                                        <select class="form-select @error('income_account_id') is-invalid @enderror" id="income_account_id" name="income_account_id" required>
                                            <option value="">Select Income Account</option>
                                            @foreach($chartAccounts as $account)
                                                <option value="{{ $account->id }}" {{ old('income_account_id') == $account->id ? 'selected' : '' }}
                                                        data-name="{{ $account->account_name }}"
                                                        data-code="{{ $account->account_code }}">
                                                    {{ $account->account_name }} ({{ $account->account_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('income_account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bx bx-save me-1"></i> Create Fee Group
                                    </button>
                                    <a href="{{ route('college.fee-groups.index') }}" class="btn btn-secondary ms-2">
                                        <i class="bx bx-arrow-back me-1"></i> Cancel
                                    </a>
                                </div>
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
                            <h6>What is a Fee Group?</h6>
                            <p class="small text-muted">
                                A fee group categorizes different types of fees charged to college students.
                                Each fee group is linked to specific chart accounts for proper financial tracking.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Account Types:</h6>
                            <ul class="small text-muted mb-2">
                                <li><strong>Receivable Account:</strong> Where student fees are recorded as owed</li>
                                <li><strong>Income Account:</strong> Where fee income is recognized when collected</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Examples:</h6>
                            <ul class="small text-muted">
                                <li>Tuition Fees</li>
                                <li>Examination Fees</li>
                                <li>Lab Fees</li>
                                <li>Library Fees</li>
                                <li>Registration Fees</li>
                            </ul>
                        </div>
                        <div class="alert alert-light small">
                            <i class="bx bx-bulb me-1 text-warning"></i>
                            <strong>Tip:</strong> Choose appropriate chart accounts that match your accounting structure for accurate financial reporting.
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

    .font-22 {
        font-size: 1.375rem !important;
    }

    /* Select2 custom styling */
    .select2-container--bootstrap-5 .select2-selection {
        border-color: #ced4da;
    }

    .select2-container--bootstrap-5 .select2-selection:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted {
        background-color: #ffc107;
        color: #000;
    }

    /* Alert styling */
    .alert-light {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }
</style>
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Auto-uppercase for fee code
        $('#fee_code').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Auto-capitalize first letter for fee group name
        $('#name').on('input', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
        });

        // Initialize Select2 for chart account selects with basic search
        $('#receivable_account_id, #income_account_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search for an account...',
            allowClear: true,
            width: '100%'
        });

        console.log('College fee group create form loaded with Select2');
    });
</script>
@endpush