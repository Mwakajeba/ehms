@extends('layouts.main')

@section('title', 'Pay Fee Invoice')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Fee Invoices', 'url' => route('college.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Pay Invoice', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />

        <h6 class="mb-0 text-uppercase">PAY FEE INVOICE</h6>
        <hr />

        <!-- Invoice Header Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">
                        <i class="bx bx-credit-card me-2"></i>Pay Fee Invoice
                    </h5>
                    <small class="text-white-50">Invoice #{{ $feeInvoice->invoice_number }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('college.fee-invoices.show', $feeInvoice->hashid) }}" class="btn btn-light btn-sm">
                        <i class="bx bx-show me-1"></i> View Invoice
                    </a>
                    <a href="{{ route('college.fee-invoices.index') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Back to List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Status Badge -->
                <div class="mb-4 text-center">
                    @php
                        $statusColors = [
                            'draft' => 'secondary',
                            'issued' => 'info',
                            'paid' => 'success',
                            'overdue' => 'danger',
                            'cancelled' => 'warning'
                        ];
                        $statusColor = $statusColors[$feeInvoice->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusColor }} fs-6 px-3 py-2">
                        <i class="bx bx-info-circle me-1"></i>{{ ucfirst($feeInvoice->status) }}
                    </span>
                </div>

                <!-- Invoice Summary Grid -->
                <div class="row g-4 mb-4">
                    <!-- Student Information -->
                    <div class="col-lg-4">
                        <div class="card border-info h-100">
                            <div class="card-header bg-info text-white">
                                <h6 class="card-title mb-0">
                                    <i class="bx bx-user me-2"></i>Student Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Student Name</strong>
                                        <span class="fs-5">{{ $feeInvoice->student->full_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Student Number</strong>
                                        <span>{{ $feeInvoice->student->student_number ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Program</strong>
                                        <span>{{ $feeInvoice->program->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Fee Group</strong>
                                        <span>{{ $feeInvoice->feeGroup->name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Details -->
                    <div class="col-lg-4">
                        <div class="card border-success h-100">
                            <div class="card-header bg-success text-white">
                                <h6 class="card-title mb-0">
                                    <i class="bx bx-receipt me-2"></i>Invoice Details
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Invoice Number</strong>
                                        <span class="fs-5">{{ $feeInvoice->invoice_number }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Fee Period</strong>
                                        <span>{{ $feeInvoice->getFeePeriodOptions()[$feeInvoice->period] ?? $feeInvoice->period }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Due Date</strong>
                                        <span class="{{ $feeInvoice->due_date && $feeInvoice->due_date->isPast() && $feeInvoice->status !== 'paid' ? 'text-danger fw-bold' : '' }}">
                                            {{ $feeInvoice->due_date ? $feeInvoice->due_date->format('M d, Y') : 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Issue Date</strong>
                                        <span>{{ $feeInvoice->issue_date ? $feeInvoice->issue_date->format('M d, Y') : 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="col-lg-4">
                        <div class="card border-warning h-100">
                            <div class="card-header bg-warning text-white">
                                <h6 class="card-title mb-0">
                                    <i class="bx bx-money me-2"></i>Payment Summary
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Total Amount</strong>
                                        <span class="fs-4 fw-bold text-primary">{{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->total_amount, 2) }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Paid Amount</strong>
                                        <span class="fs-5 text-success">{{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->paid_amount, 2) }}</span>
                                    </div>
                                    <div class="col-12">
                                        <strong class="text-muted d-block">Balance Due</strong>
                                        <span class="fs-4 fw-bold text-danger">{{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->total_amount - $feeInvoice->paid_amount, 2) }}</span>
                                    </div>
                                    <div class="col-12">
                                        <div class="progress mt-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                 style="width: {{ $feeInvoice->total_amount > 0 ? ($feeInvoice->paid_amount / $feeInvoice->total_amount) * 100 : 0 }}%"
                                                 aria-valuenow="{{ $feeInvoice->paid_amount }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="{{ $feeInvoice->total_amount }}"></div>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            {{ number_format(($feeInvoice->paid_amount / $feeInvoice->total_amount) * 100, 1) }}% Paid
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <form action="{{ route('college.fee-invoices.pay', $feeInvoice->hashid) }}" method="POST" id="payment-form">
            @csrf
            @method('POST')
            <div class="row">
                <!-- Main Payment Form -->
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-credit-card me-2"></i>Payment Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Payment Amount -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_amount" class="form-label fw-bold">
                                            <i class="bx bx-money text-success me-1"></i>Payment Amount <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light">
                                                <i class="bx bx-money"></i>
                                            </span>
                                            <input type="number" class="form-control form-control-lg @error('payment_amount') is-invalid @enderror"
                                                   id="payment_amount" name="payment_amount"
                                                   value="{{ old('payment_amount', $feeInvoice->total_amount - $feeInvoice->paid_amount) }}"
                                                   step="0.01" min="0.01"
                                                   max="{{ $feeInvoice->total_amount - $feeInvoice->paid_amount }}"
                                                   required>
                                            <span class="input-group-text bg-light">{{ config('app.currency', 'TZS') }}</span>
                                        </div>
                                        @error('payment_amount')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text">
                                            <small class="text-muted">
                                                <i class="bx bx-info-circle me-1"></i>
                                                Maximum amount: {{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->total_amount - $feeInvoice->paid_amount, 2) }}
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Date -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_date" class="form-label fw-bold">
                                            <i class="bx bx-calendar text-info me-1"></i>Payment Date <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light">
                                                <i class="bx bx-calendar"></i>
                                            </span>
                                            <input type="date" class="form-control form-control-lg @error('payment_date') is-invalid @enderror"
                                                   id="payment_date" name="payment_date"
                                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                        </div>
                                        @error('payment_date')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="payment_method" class="form-label fw-bold">
                                            <i class="bx bx-select-multiple text-warning me-1"></i>Payment Method <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light">
                                                <i class="bx bx-credit-card"></i>
                                            </span>
                                            <select class="form-select form-select-lg @error('payment_method') is-invalid @enderror"
                                                    id="payment_method" name="payment_method" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>
                                                    <i class="bx bx-money"></i> Cash
                                                </option>
                                                <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>
                                                    <i class="bx bx-transfer"></i> Bank Transfer
                                                </option>
                                                <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>
                                                    <i class="bx bx-check"></i> Cheque
                                                </option>
                                                <option value="Mobile Money" {{ old('payment_method') == 'Mobile Money' ? 'selected' : '' }}>
                                                    <i class="bx bx-mobile"></i> Mobile Money
                                                </option>
                                                <option value="Credit Card" {{ old('payment_method') == 'Credit Card' ? 'selected' : '' }}>
                                                    <i class="bx bx-credit-card"></i> Credit Card
                                                </option>
                                            </select>
                                        </div>
                                        @error('payment_method')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Bank Account -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_account_id" class="form-label fw-bold">
                                            <i class="bx bx-building-house text-secondary me-1"></i>Bank Account
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light">
                                                <i class="bx bx-building-house"></i>
                                            </span>
                                            <select class="form-select form-select-lg @error('bank_account_id') is-invalid @enderror"
                                                    id="bank_account_id" name="bank_account_id">
                                                <option value="">Select Bank Account (leave empty for cash)</option>
                                                @foreach($bankAccounts ?? [] as $account)
                                                    <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                                        {{ $account->name }} ({{ $account->account_number }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('bank_account_id')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text">
                                            <small class="text-muted">
                                                <i class="bx bx-info-circle me-1"></i>
                                                Required for bank transfers, cheques, and card payments
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reference Number -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="reference_number" class="form-label fw-bold">
                                            <i class="bx bx-hash text-primary me-1"></i>Reference Number
                                        </label>
                                        <div class="input-group input-group-lg">
                                            <span class="input-group-text bg-light">
                                                <i class="bx bx-hash"></i>
                                            </span>
                                            <input type="text" class="form-control form-control-lg @error('reference_number') is-invalid @enderror"
                                                   id="reference_number" name="reference_number"
                                                   value="{{ old('reference_number') }}"
                                                   placeholder="Transaction/Receipt number">
                                        </div>
                                        @error('reference_number')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-label fw-bold">
                                            <i class="bx bx-note text-info me-1"></i>Payment Notes
                                        </label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                                  id="notes" name="notes" rows="3"
                                                  placeholder="Optional payment notes, remarks, or additional information">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-error-circle me-1"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Preview & Actions -->
                <div class="col-12 col-lg-4">
                    <!-- Payment Preview Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-show me-2"></i>Payment Preview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="payment-preview" class="text-center py-3">
                                <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                <h6 class="text-muted mt-3">Payment Preview</h6>
                                <p class="text-muted small">Enter payment details to see preview</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-flash me-2"></i>Quick Actions
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" id="full-payment-btn">
                                    <i class="bx bx-money me-1"></i> Pay Full Amount
                                </button>
                                <button type="button" class="btn btn-outline-info" id="clear-form-btn">
                                    <i class="bx bx-refresh me-1"></i> Clear Form
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="card-title mb-0">
                                <i class="bx bx-play-circle me-2"></i>Process Payment
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bx bx-check-circle me-2"></i>Process Payment
                                </button>
                                <a href="{{ route('college.fee-invoices.show', $feeInvoice->hashid) }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Invoice
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
<style>
    .page-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 20px 0;
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

    .bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
    }

    .bg-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%) !important;
    }

    .bg-success {
        background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
    }

    .bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    }

    .form-control-lg, .form-select-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control-lg:focus, .form-select-lg:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .input-group-lg .input-group-text {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        background: #f8f9fa;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-success {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
    }

    .btn-outline-primary:hover, .btn-outline-info:hover, .btn-outline-secondary:hover {
        transform: translateY(-2px);
    }

    .progress {
        border-radius: 4px;
        background-color: #e9ecef;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
        border-radius: 0.375rem;
    }

    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    .text-end {
        text-align: right !important;
    }

    .text-center {
        text-align: center !important;
    }

    /* Payment preview animations */
    #payment-preview {
        transition: all 0.3s ease;
    }

    /* Form validation styling */
    .is-invalid {
        border-color: #dc3545 !important;
    }

    .invalid-feedback {
        display: block;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .card-header {
            padding: 0.75rem 1rem;
        }

        .form-control-lg, .form-select-lg {
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
        }

        .btn-lg {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Fee invoice payment form loaded');

    // Global AJAX setup for CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Update payment preview when form changes
    $('#payment_amount, #payment_date, #payment_method, #reference_number, #bank_account_id').on('input change', function() {
        updatePaymentPreview();
    });

    // Full payment button
    $('#full-payment-btn').on('click', function() {
        const maxAmount = {{ $feeInvoice->total_amount - $feeInvoice->paid_amount }};
        $('#payment_amount').val(maxAmount.toFixed(2));
        updatePaymentPreview();
    });

    // Clear form button
    $('#clear-form-btn').on('click', function() {
        $('#payment-form')[0].reset();
        $('#payment-preview').html(`
            <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
            <h6 class="text-muted mt-3">Payment Preview</h6>
            <p class="text-muted small">Enter payment details to see preview</p>
        `);
    });

    // Update bank account visibility based on payment method
    $('#payment_method').on('change', function() {
        const method = $(this).val();
        const bankAccountField = $('#bank_account_id').closest('.form-group');

        if (method === 'Cash') {
            bankAccountField.slideUp(300);
            $('#bank_account_id').prop('required', false);
        } else {
            bankAccountField.slideDown(300);
            $('#bank_account_id').prop('required', true);
        }
    });

    function updatePaymentPreview() {
        const amount = parseFloat($('#payment_amount').val()) || 0;
        const paymentDate = $('#payment_date').val();
        const paymentMethod = $('#payment_method').find('option:selected').text().trim();
        const referenceNumber = $('#reference_number').val();
        const bankAccount = $('#bank_account_id').find('option:selected').text();

        if (amount > 0) {
            const currency = '{{ config("app.currency", "TZS") }}';
            const formattedAmount = amount.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            let previewHtml = `
                <div class="alert alert-info border-0">
                    <h6 class="alert-heading mb-3">
                        <i class="bx bx-show me-1"></i>Payment Preview
                    </h6>
                    <div class="row g-2 text-start">
                        <div class="col-12">
                            <strong class="text-primary">Amount:</strong>
                            <span class="fs-5 fw-bold text-success ms-2">${currency} ${formattedAmount}</span>
                        </div>
                        <div class="col-12">
                            <strong>Date:</strong>
                            <span class="ms-2">${paymentDate ? new Date(paymentDate).toLocaleDateString() : '<span class="text-warning">Not set</span>'}</span>
                        </div>
                        <div class="col-12">
                            <strong>Method:</strong>
                            <span class="ms-2">${paymentMethod || '<span class="text-warning">Not selected</span>'}</span>
                        </div>`;

            if (referenceNumber) {
                previewHtml += `
                        <div class="col-12">
                            <strong>Reference:</strong>
                            <span class="ms-2">${referenceNumber}</span>
                        </div>`;
            }

            if (bankAccount && bankAccount !== 'Select Bank Account (leave empty for cash)') {
                previewHtml += `
                        <div class="col-12">
                            <strong>Bank Account:</strong>
                            <span class="ms-2">${bankAccount}</span>
                        </div>`;
            }

            previewHtml += `
                    </div>
                </div>`;

            $('#payment-preview').html(previewHtml);
        } else {
            $('#payment-preview').html(`
                <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                <h6 class="text-muted mt-3">Payment Preview</h6>
                <p class="text-muted small">Enter payment details to see preview</p>
            `);
        }
    }

    // Initialize preview
    updatePaymentPreview();

    // Initialize bank account field visibility
    $('#payment_method').trigger('change');

    // Form validation - simplified
    $('#payment-form').on('submit', function(e) {
        console.log('Form submission started');
        console.log('Form data:', $(this).serialize());

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Processing...');

        console.log('Form validation passed, submitting...');
        return true; // Allow form submission
    });
});
</script>
@endpush