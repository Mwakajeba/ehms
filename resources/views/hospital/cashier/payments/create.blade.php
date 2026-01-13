@extends('layouts.main')

@section('title', 'Record Payment')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Cashier', 'url' => route('hospital.cashier.index'), 'icon' => 'bx bx-money'],
                ['label' => 'Record Payment', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD PAYMENT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-money me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Record Payment for Bill #{{ $bill->bill_number }}</h5>
                            </div>
                            <hr />

                            <!-- Bill Summary -->
                            <div class="alert alert-info mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Patient:</strong> {{ $bill->patient->full_name }}<br>
                                        <strong>MRN:</strong> {{ $bill->patient->mrn }}<br>
                                        <strong>Total Amount:</strong> {{ number_format($bill->total, 2) }} TZS
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Paid:</strong> {{ number_format($bill->paid, 2) }} TZS<br>
                                        <strong>Balance:</strong> 
                                        <strong class="{{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($bill->balance, 2) }} TZS
                                        </strong>
                                    </div>
                                </div>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('hospital.cashier.payments.store', $bill->id) }}" method="POST" id="paymentForm">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="amount" class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                                            <input type="number" 
                                                   class="form-control @error('amount') is-invalid @enderror" 
                                                   id="amount" 
                                                   name="amount" 
                                                   step="0.01" 
                                                   min="0.01" 
                                                   max="{{ $bill->balance }}"
                                                   value="{{ old('amount', $bill->balance) }}" 
                                                   required>
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Maximum: {{ number_format($bill->balance, 2) }} TZS</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_method" class="form-label fw-bold">Payment Method <span class="text-danger">*</span></label>
                                            <select class="form-select @error('payment_method') is-invalid @enderror" 
                                                    id="payment_method" 
                                                    name="payment_method" 
                                                    required>
                                                <option value="">Select Payment Method</option>
                                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                                <option value="nhif" {{ old('payment_method') == 'nhif' ? 'selected' : '' }}>NHIF</option>
                                                <option value="chf" {{ old('payment_method') == 'chf' ? 'selected' : '' }}>CHF</option>
                                                <option value="jubilee" {{ old('payment_method') == 'jubilee' ? 'selected' : '' }}>Jubilee</option>
                                                <option value="strategy" {{ old('payment_method') == 'strategy' ? 'selected' : '' }}>Strategy</option>
                                                <option value="mobile_payment" {{ old('payment_method') == 'mobile_payment' ? 'selected' : '' }}>Mobile Payment</option>
                                            </select>
                                            @error('payment_method')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="reference_number" class="form-label fw-bold">Reference Number</label>
                                            <input type="text" 
                                                   class="form-control @error('reference_number') is-invalid @enderror" 
                                                   id="reference_number" 
                                                   name="reference_number" 
                                                   value="{{ old('reference_number') }}"
                                                   placeholder="Insurance card number, mobile payment reference, etc.">
                                            @error('reference_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Required for insurance and mobile payments</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label fw-bold">Notes</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                      id="notes" 
                                                      name="notes" 
                                                      rows="2">{{ old('notes') }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.cashier.bills.show', $bill->id) }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Record Payment
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

@push('scripts')
<script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const amount = parseFloat(document.getElementById('amount').value);
    const balance = {{ $bill->balance }};
    
    if (amount > balance) {
        e.preventDefault();
        alert('Payment amount cannot exceed the balance of ' + balance.toFixed(2) + ' TZS');
        return false;
    }
});
</script>
@endpush
