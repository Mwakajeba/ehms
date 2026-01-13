@extends('layouts.main')

@section('title', 'Edit Fee Invoice')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Invoices', 'url' => route('school.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Edit Invoice', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT FEE INVOICE</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <!-- Invoice Details Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-edit me-2"></i>Edit Invoice #{{ $feeInvoice->invoice_number }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="edit-invoice-form" method="POST" action="{{ route('school.fee-invoices.update', $feeInvoice->hashid) }}">
                            @csrf
                            @method('PUT')

                            <!-- Invoice Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="border rounded p-3 bg-light">
                                        <h6 class="text-primary mb-2"><i class="bx bx-user me-1"></i>Student Information</h6>
                                        <p class="mb-1"><strong>Name:</strong> {{ $feeInvoice->student->first_name }} {{ $feeInvoice->student->last_name }}</p>
                                        <p class="mb-1"><strong>Admission No:</strong> {{ $feeInvoice->student->admission_number }}</p>
                                        <p class="mb-1"><strong>Class:</strong> {{ $feeInvoice->classe->name }}</p>
                                        <p class="mb-1"><strong>Stream:</strong> {{ $feeInvoice->student->stream->name ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Period:</strong> {{ $feeInvoice->period }} Quarter{{ $feeInvoice->period > 1 ? 's' : '' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 bg-light">
                                        <h6 class="text-success mb-2"><i class="bx bx-money me-1"></i>Current Amounts</h6>
                                        <p class="mb-1"><strong>Base Fee:</strong> TZS {{ number_format($feeInvoice->subtotal, 2) }}</p>
                                        <p class="mb-1"><strong>Transport Fare:</strong> TZS {{ number_format($feeInvoice->transport_fare, 2) }}</p>
                                        <p class="mb-1"><strong>Total Amount:</strong> TZS {{ number_format($feeInvoice->total_amount, 2) }}</p>
                                        <p class="mb-1"><strong>Status:</strong>
                                            <span class="badge bg-{{ $feeInvoice->status == 'paid' ? 'success' : ($feeInvoice->status == 'cancelled' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($feeInvoice->status) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Editable Fields -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="subtotal" class="form-label fw-bold">
                                            <i class="bx bx-money text-success me-1"></i>Base Fee Amount (TZS) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="subtotal" id="subtotal" class="form-control form-control-lg @error('subtotal') is-invalid @enderror"
                                               value="{{ old('subtotal', $feeInvoice->subtotal) }}" step="0.01" min="0" required>
                                        <div class="form-text">
                                            <small class="text-muted">The base tuition/school fee amount</small>
                                        </div>
                                        @error('subtotal')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="transport_fare" class="form-label fw-bold">
                                            <i class="bx bx-bus text-info me-1"></i>Transport Fare (TZS)
                                        </label>
                                        <input type="number" name="transport_fare" id="transport_fare" class="form-control form-control-lg @error('transport_fare') is-invalid @enderror"
                                               value="{{ old('transport_fare', $feeInvoice->transport_fare) }}" step="0.01" min="0">
                                        <div class="form-text">
                                            <small class="text-muted">Transport/bus fare amount (leave 0 if not applicable)</small>
                                        </div>
                                        @error('transport_fare')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Discount Management -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <h6 class="mb-0">
                                                <i class="bx bx-discount me-2"></i>Discount Management
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="discount_type" class="form-label fw-bold">
                                                            <i class="bx bx-tag text-warning me-1"></i>Discount Type
                                                        </label>
                                                        <select name="discount_type" id="discount_type" class="form-control form-control-lg @error('discount_type') is-invalid @enderror">
                                                            <option value="">No Discount</option>
                                                            <option value="fixed" {{ old('discount_type', $feeInvoice->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                            <option value="percentage" {{ old('discount_type', $feeInvoice->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                        </select>
                                                        <div class="form-text">
                                                            <small class="text-muted">Select the type of discount to apply</small>
                                                        </div>
                                                        @error('discount_type')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group mb-3">
                                                        <label for="discount_value" class="form-label fw-bold">
                                                            <i class="bx bx-money text-success me-1"></i>Discount Value
                                                        </label>
                                                        <div class="input-group">
                                                            <input type="number" name="discount_value" id="discount_value" class="form-control form-control-lg @error('discount_value') is-invalid @enderror"
                                                                   value="{{ old('discount_value', $feeInvoice->discount_value) }}" step="0.01" min="0">
                                                            <span class="input-group-text" id="discount-unit">
                                                                {{ $feeInvoice->discount_type === 'percentage' ? '%' : 'TZS' }}
                                                            </span>
                                                        </div>
                                                        <div class="form-text">
                                                            <small class="text-muted">Enter the discount amount or percentage</small>
                                                        </div>
                                                        @error('discount_value')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            @if($feeInvoice->discount_amount > 0)
                                            <div class="alert alert-success">
                                                <strong>Current Discount:</strong>
                                                @if($feeInvoice->discount_type === 'percentage')
                                                    {{ $feeInvoice->discount_value }}% (TZS {{ number_format($feeInvoice->discount_amount, 2) }})
                                                @else
                                                    TZS {{ number_format($feeInvoice->discount_amount, 2) }}
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label fw-bold">
                                            <i class="bx bx-check-circle text-warning me-1"></i>Invoice Status <span class="text-danger">*</span>
                                        </label>
                                        <select name="status" id="status" class="form-control form-control-lg @error('status') is-invalid @enderror" required>
                                            <option value="draft" {{ old('status', $feeInvoice->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="issued" {{ old('status', $feeInvoice->status) == 'issued' ? 'selected' : '' }}>Issued</option>
                                            <option value="paid" {{ old('status', $feeInvoice->status) == 'paid' ? 'selected' : '' }}>Paid</option>
                                            <option value="overdue" {{ old('status', $feeInvoice->status) == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                            <option value="cancelled" {{ old('status', $feeInvoice->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="due_date" class="form-label fw-bold">
                                            <i class="bx bx-calendar text-danger me-1"></i>Due Date
                                        </label>
                                        <input type="date" name="due_date" id="due_date" class="form-control form-control-lg @error('due_date') is-invalid @enderror"
                                               value="{{ old('due_date', $feeInvoice->due_date ? $feeInvoice->due_date->format('Y-m-d') : '') }}">
                                        <div class="form-text">
                                            <small class="text-muted">When this invoice is due for payment</small>
                                        </div>
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Total Calculation Display -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info border">
                                        <h6 class="alert-heading mb-2"><i class="bx bx-calculator me-1"></i>Total Calculation</h6>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Base Fee:</strong> TZS <span id="display-subtotal">{{ number_format($feeInvoice->subtotal, 2) }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Transport Fare:</strong> TZS <span id="display-transport">{{ number_format($feeInvoice->transport_fare, 2) }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Discount:</strong> TZS <span id="display-discount" class="text-success">{{ number_format($feeInvoice->discount_amount, 2) }}</span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Amount:</strong> TZS <span id="display-total" class="fw-bold text-primary">{{ number_format($feeInvoice->total_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Invoice Items (if any) -->
                @if($feeInvoice->feeInvoiceItems && $feeInvoice->feeInvoiceItems->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-ul me-2"></i>Invoice Items
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Amount</th>
                                        <th>Includes Transport</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($feeInvoice->feeInvoiceItems as $item)
                                    <tr>
                                        <td>{{ $item->item_name }}</td>
                                        <td><span class="badge bg-secondary">{{ ucfirst($item->category) }}</span></td>
                                        <td>TZS {{ number_format($item->amount, 2) }}</td>
                                        <td>
                                            @if($item->includes_transport)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar Actions -->
            <div class="col-12 col-lg-4">
                <!-- Action Buttons -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-play-circle me-2"></i>Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" form="edit-invoice-form" class="btn btn-success btn-lg">
                                <i class="bx bx-save me-2"></i>Update Invoice
                            </button>
                            <a href="{{ route('school.fee-invoices.show', $feeInvoice->hashid) }}" class="btn btn-info">
                                <i class="bx bx-show me-1"></i>View Invoice
                            </a>
                            <a href="{{ route('school.fee-invoices.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to Invoices
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-bar-chart me-2"></i>Invoice Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="p-2">
                                    <h4 class="text-primary mb-1">{{ $feeInvoice->period }}</h4>
                                    <small class="text-muted">Period</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2">
                                    <h4 class="text-success mb-1">{{ $feeInvoice->academicYear->year_name ?? 'N/A' }}</h4>
                                    <small class="text-muted">Academic Year</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Card -->
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i>Important Notes
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning small">
                            <i class="bx bx-error-circle me-1"></i>
                            <strong>Caution:</strong> Modifying invoice amounts may affect financial records and student payments.
                        </div>
                        <div class="alert alert-info small">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Tip:</strong> Changes to base fees or transport fares will automatically recalculate the total amount.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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

    .btn-success {
        background: linear-gradient(135deg, #198754 0%, #157347 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
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

    .bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate total when amounts change
    $('#subtotal, #transport_fare, #discount_type, #discount_value').on('input change', function() {
        calculateTotal();
    });

    // Update discount unit display when type changes
    $('#discount_type').on('change', function() {
        const discountType = $(this).val();
        const unitSpan = $('#discount-unit');

        if (discountType === 'percentage') {
            unitSpan.text('%');
            $('#discount_value').attr('max', 100);
        } else {
            unitSpan.text('TZS');
            $('#discount_value').removeAttr('max');
        }

        calculateTotal();
    });

    // Calculate and display total
    function calculateTotal() {
        const subtotal = parseFloat($('#subtotal').val()) || 0;
        const transportFare = parseFloat($('#transport_fare').val()) || 0;
        const discountType = $('#discount_type').val();
        const discountValue = parseFloat($('#discount_value').val()) || 0;

        let discountAmount = 0;
        const baseAmount = subtotal + transportFare;

        if (discountType && discountValue > 0) {
            if (discountType === 'percentage') {
                discountAmount = (baseAmount * discountValue) / 100;
            } else if (discountType === 'fixed') {
                discountAmount = Math.min(discountValue, baseAmount);
            }
        }

        const total = baseAmount - discountAmount;

        $('#display-subtotal').text(subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#display-transport').text(transportFare.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#display-discount').text(discountAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#display-total').text(total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    }

    // Initialize calculation on page load
    calculateTotal();

    // Form submission with confirmation
    $('#edit-invoice-form').on('submit', function(e) {
        e.preventDefault();

        const subtotal = parseFloat($('#subtotal').val()) || 0;
        const transportFare = parseFloat($('#transport_fare').val()) || 0;
        const discountType = $('#discount_type').val();
        const discountValue = parseFloat($('#discount_value').val()) || 0;
        const baseAmount = subtotal + transportFare;

        let discountAmount = 0;
        if (discountType && discountValue > 0) {
            if (discountType === 'percentage') {
                discountAmount = (baseAmount * discountValue) / 100;
            } else if (discountType === 'fixed') {
                discountAmount = Math.min(discountValue, baseAmount);
            }
        }

        const total = baseAmount - discountAmount;
        const status = $('#status').val();

        let discountText = '';
        if (discountAmount > 0) {
            discountText = `<p class="mb-2"><strong>Discount:</strong> TZS ${discountAmount.toLocaleString()} ${discountType === 'percentage' ? `(${discountValue}%)` : ''}</p>`;
        }

        Swal.fire({
            title: 'Confirm Invoice Update',
            html: `
                <div class="text-left">
                    <p class="mb-2"><strong>New Base Fee:</strong> TZS ${subtotal.toLocaleString()}</p>
                    <p class="mb-2"><strong>New Transport Fare:</strong> TZS ${transportFare.toLocaleString()}</p>
                    ${discountText}
                    <p class="mb-2"><strong>New Total Amount:</strong> TZS ${total.toLocaleString()}</p>
                    <p class="mb-2"><strong>Status:</strong> ${status.charAt(0).toUpperCase() + status.slice(1)}</p>
                    <p class="mt-3 text-warning"><i class="bx bx-error-circle me-1"></i>This will update the invoice permanently. Are you sure?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bx bx-check me-1"></i>Yes, Update Invoice',
            cancelButtonText: '<i class="bx bx-x me-1"></i>Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form
                $('#edit-invoice-form')[0].submit();
            }
        });
    });
});
</script>
@endpush