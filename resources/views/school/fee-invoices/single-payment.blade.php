@extends('layouts.main')

@section('title', 'Single Payment - ' . $student->first_name . ' ' . $student->last_name . ' - ' . $quarter)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Invoices', 'url' => route('school.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Student Statement', 'url' => route('school.fee-invoices.student', $student->getRouteKey()), 'icon' => 'bx bx-user'],
            ['label' => 'Single Payment - ' . $quarter, 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <h6 class="mb-0 text-uppercase">SINGLE PAYMENT - {{ $student->first_name . ' ' . $student->last_name }} - {{ $quarter }}</h6>
        <hr />

        <!-- Student Information Card -->
        <div class="card border-info mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bx bx-user me-2"></i> Student Information
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Student Name:</th>
                                <td>{{ $student->first_name . ' ' . $student->last_name }}</td>
                            </tr>
                            <tr>
                                <th>Admission Number:</th>
                                <td>{{ $student->admission_number }}</td>
                            </tr>
                            <tr>
                                <th>Class:</th>
                                <td>{{ $student->class->name ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Quarter:</th>
                                <td><strong>{{ $quarter }}</strong></td>
                            </tr>
                            <tr>
                                <th>Total Invoices:</th>
                                <td>{{ $quarterInvoices->count() }}</td>
                            </tr>
                            <tr>
                                <th>Total Amount:</th>
                                <td><strong>TZS {{ number_format($quarterInvoices->sum('total_amount'), 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bx bx-plus me-2"></i>Record Single Payment
                </h5>
            </div>
            <div class="card-body">
                    <form method="POST" action="{{ route('school.fee-invoices.single-payment.store', ['encodedId' => $student->getRouteKey(), 'quarter' => $quarter]) }}">
                    @csrf

                    <!-- Invoice Selection -->
                    <div class="mb-3">
                        <label for="invoice_id" class="form-label">Select Invoice <span class="text-danger">*</span></label>
                        <select class="form-select @error('invoice_id') is-invalid @enderror" id="invoice_id" name="invoice_id" required>
                            <option value="">Choose an invoice to pay</option>
                            @foreach($quarterInvoices as $invoice)
                            <option value="{{ $invoice->id }}" {{ old('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                {{ $invoice->invoice_number }} - TZS {{ number_format($invoice->total_amount, 2) }} ({{ ucfirst($invoice->status) }})
                            </option>
                            @endforeach
                        </select>
                        @error('invoice_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                                       id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                @error('payment_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_method') is-invalid @enderror"
                                        id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                    <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row bank-account-row" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                                <select class="form-select @error('bank_account_id') is-invalid @enderror"
                                        id="bank_account_id" name="bank_account_id">
                                    <option value="">Select Bank Account</option>
                                    @php
                                        $bankAccounts = \App\Models\BankAccount::orderBy('name')->get();
                                    @endphp
                                    @foreach($bankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                        {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                    </option>
                                    @endforeach
                                </select>
                                @error('bank_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Empty column for spacing -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_number" class="form-label">Reference Number</label>
                                <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                                       id="reference_number" name="reference_number" value="{{ old('reference_number') }}">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                           id="amount" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('school.fee-invoices.student', $student->getRouteKey()) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i> Back to Student Statement
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-check me-1"></i> Record Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quarter Invoices Table -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bx bx-receipt me-2"></i>{{ $quarter }} Invoices
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><i class="bx bx-hash me-1"></i>Invoice Number</th>
                                <th><i class="bx bx-calendar me-1"></i>Academic Year</th>
                                <th><i class="bx bx-group me-1"></i>Fee Group</th>
                                <th><i class="bx bx-money me-1"></i>Subtotal</th>
                                <th><i class="bx bx-bus me-1"></i>Transport Fare</th>
                                <th><i class="bx bx-money me-1"></i>Total Amount</th>
                                <th><i class="bx bx-info-circle me-1"></i>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quarterInvoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->academicYear->year_name ?? 'N/A' }}</td>
                                <td>{{ $invoice->feeGroup->name ?? 'N/A' }}</td>
                                <td>TZS {{ number_format($invoice->subtotal, 2) }}</td>
                                <td>TZS {{ number_format($invoice->transport_fare, 2) }}</td>
                                <td><strong>TZS {{ number_format($invoice->total_amount, 2) }}</strong></td>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'draft' => 'secondary',
                                            'issued' => 'primary',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                        $statusClass = $statusClasses[$invoice->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="bx bx-info-circle me-2"></i>
                                    No invoices found for this quarter.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($quarterInvoices->count() > 0)
                        <tfoot>
                            <tr class="table-dark">
                                <th colspan="5" class="text-end">TOTAL:</th>
                                <th><strong>TZS {{ number_format($quarterInvoices->sum('total_amount'), 2) }}</strong></th>
                                <th></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('invoice_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const invoiceText = selectedOption.text;
    const amountMatch = invoiceText.match(/TZS ([\d,]+\.\d{2})/);

    if (amountMatch) {
        const amount = amountMatch[1].replace(/,/g, '');
        document.getElementById('amount').value = amount;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelect = document.getElementById('payment_method');
    const bankAccountRow = document.querySelector('.bank-account-row');
    const bankAccountSelect = document.getElementById('bank_account_id');

    function toggleBankAccountField() {
        const selectedMethod = paymentMethodSelect.value;
        if (selectedMethod === 'bank_transfer' || selectedMethod === 'cheque') {
            bankAccountRow.style.display = 'block';
            bankAccountSelect.required = true;
        } else {
            bankAccountRow.style.display = 'none';
            bankAccountSelect.required = false;
            bankAccountSelect.value = '';
        }
    }

    paymentMethodSelect.addEventListener('change', toggleBankAccountField);

    // Initialize on page load
    toggleBankAccountField();
});
</script>
@endsection