@extends('layouts.main')

@section('title', 'Prepaid Account Settings')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Prepaid Account Settings', 'url' => '#', 'icon' => 'bx bx-cog']
        ]" />
        <h6 class="mb-0 text-uppercase">PREPAID ACCOUNT SETTINGS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-cog me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Prepaid Account Settings</h5>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="alert alert-info">
                            <h6 class="fw-bold"><i class="bx bx-info-circle me-1"></i> How Prepaid Accounts Work:</h6>
                            <ul class="mb-0">
                                <li>Each student has ONE running operation account</li>
                                <li>If a student pays more than the current quarter invoice and there is no other invoice yet for other quarter created → the extra amount becomes CREDIT (advance balance)</li>
                                <li>If there is no invoice yet → the full payment becomes CREDIT on that account</li>
                                <li>When a new invoice is created (for example Q2, Q3, Q4) → the system automatically uses available CREDIT to settle the invoice (fully or partially)</li>
                            </ul>
                        </div>

                        <form action="{{ route('school.prepaid-accounts.update-settings') }}" method="POST">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="auto_apply_credit" name="auto_apply_credit" value="1" {{ old('auto_apply_credit', $autoApplyCredit) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="auto_apply_credit">
                                            Automatically Apply Credit to New Invoices
                                        </label>
                                        <small class="text-muted d-block">When enabled, available credit will be automatically applied to new invoices</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="apply_credit_order" class="form-label fw-bold">Credit Application Order</label>
                                    <select class="form-select" id="apply_credit_order" name="apply_credit_order">
                                        <option value="oldest_first" {{ old('apply_credit_order', $applyCreditOrder) == 'oldest_first' ? 'selected' : '' }}>Oldest First (FIFO)</option>
                                        <option value="newest_first" {{ old('apply_credit_order', $applyCreditOrder) == 'newest_first' ? 'selected' : '' }}>Newest First (LIFO)</option>
                                    </select>
                                    <small class="text-muted d-block">Determines the order in which credit is applied to invoices</small>
                                </div>

                                <div class="col-md-12">
                                    <label for="prepaid_chart_account_id" class="form-label fw-bold">Prepaid Chart Account <span class="text-danger">*</span></label>
                                    <select class="form-select select2" id="prepaid_chart_account_id" name="prepaid_chart_account_id" required>
                                        <option value="">Select Chart Account</option>
                                        @foreach($chartAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('prepaid_chart_account_id', $prepaidChartAccountId) == $account->id ? 'selected' : '' }}>
                                                [{{ $account->account_code }}] {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted d-block">Chart account used for student prepaid credit balances (typically a liability account)</small>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i> Save Settings
                                    </button>
                                    <a href="{{ route('school.fee-management.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-x me-1"></i> Cancel
                                    </a>
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

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for chart account
    $('#prepaid_chart_account_id').select2({
        placeholder: 'Select Chart Account',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0
    });
});
</script>
@endpush

