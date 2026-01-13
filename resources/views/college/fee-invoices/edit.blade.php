@extends('layouts.main')

@section('title', 'Edit Fee Invoice - ' . $feeInvoice->invoice_number)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumbs -->
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Fee Invoices', 'url' => route('college.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Invoice Details', 'url' => route('college.fee-invoices.show', $feeInvoice->hashid), 'icon' => 'bx bx-show'],
            ['label' => 'Edit Invoice', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="row">
            <div class="col-12">
                <!-- Edit Form Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bx bx-edit me-2"></i>Edit Fee Invoice
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
                        <!-- Status Warning -->
                        @if($feeInvoice->status !== 'draft')
                            <div class="alert alert-danger mb-4">
                                <i class="bx bx-error-circle me-2"></i>
                                <strong>Warning:</strong> Only draft invoices can be edited. This invoice is currently in "{{ ucfirst($feeInvoice->status) }}" status.
                            </div>
                        @else
                            <div class="alert alert-info mb-4">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Note:</strong> You can only modify the due date for draft invoices. Other invoice details are locked to maintain data integrity.
                            </div>
                        @endif

                        <form action="{{ route('college.fee-invoices.update', $feeInvoice->hashid) }}" method="POST" id="editInvoiceForm">
                            @csrf
                            @method('PUT')

                            <!-- Invoice Information Display -->
                            <div class="row g-4 mb-4">
                                <!-- Student Information (Read-only) -->
                                <div class="col-lg-6">
                                    <div class="card border-info h-100">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-user me-2"></i>Student Information
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-bold">Student Name</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $feeInvoice->student->full_name ?? 'N/A' }}" readonly>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label fw-bold">Student Number</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $feeInvoice->student->student_number ?? 'N/A' }}" readonly>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label fw-bold">Program</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $feeInvoice->program->name ?? 'N/A' }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Invoice Details (Read-only) -->
                                <div class="col-lg-6">
                                    <div class="card border-success h-100">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-receipt me-2"></i>Invoice Details
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-sm-6">
                                                    <label class="form-label fw-bold">Invoice Number</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $feeInvoice->invoice_number }}" readonly>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label fw-bold">Fee Group</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $feeInvoice->feeGroup->name ?? 'N/A' }}" readonly>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label fw-bold">Fee Period</label>
                                                    <input type="text" class="form-control bg-light" value="{{ $feePeriodOptions[$feeInvoice->period] ?? $feeInvoice->period }}" readonly>
                                                </div>
                                                <div class="col-sm-6">
                                                    <label class="form-label fw-bold">Total Amount</label>
                                                    <input type="text" class="form-control bg-light fw-bold text-primary" value="{{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->total_amount, 2) }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Editable Fields -->
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="bx bx-edit me-2"></i>Editable Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="due_date" class="form-label fw-bold">
                                                <i class="bx bx-calendar me-1"></i>Due Date <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" name="due_date" id="due_date" class="form-control form-control-lg @error('due_date') is-invalid @enderror"
                                                   value="{{ old('due_date', $feeInvoice->due_date ? $feeInvoice->due_date->format('Y-m-d') : '') }}"
                                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}" required {{ $feeInvoice->status !== 'draft' ? 'disabled' : '' }}>
                                            @error('due_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="bx bx-info-circle me-1"></i>The due date must be at least tomorrow.
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">
                                                <i class="bx bx-calendar-check me-1"></i>Current Due Date
                                            </label>
                                            <input type="text" class="form-control bg-light" value="{{ $feeInvoice->due_date ? $feeInvoice->due_date->format('M d, Y') : 'Not set' }}" readonly>
                                            <div class="form-text">
                                                <i class="bx bx-time me-1"></i>
                                                @if($feeInvoice->due_date)
                                                    @if($feeInvoice->due_date->isPast())
                                                        <span class="text-danger">Overdue by {{ $feeInvoice->due_date->diffForHumans() }}</span>
                                                    @else
                                                        <span class="text-success">Due {{ $feeInvoice->due_date->diffForHumans() }}</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">No due date set</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Items Preview -->
                            <div class="card border-dark mt-4">
                                <div class="card-header bg-dark text-white">
                                    <h6 class="card-title mb-0">
                                        <i class="bx bx-list-ul me-2"></i>Invoice Items (Read-only)
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th><i class="bx bx-hash me-1"></i>#</th>
                                                    <th><i class="bx bx-detail me-1"></i>Description</th>
                                                    <th class="text-center"><i class="bx bx-package me-1"></i>Quantity</th>
                                                    <th class="text-end"><i class="bx bx-money me-1"></i>Unit Price</th>
                                                    <th class="text-end"><i class="bx bx-calculator me-1"></i>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($feeInvoice->feeInvoiceItems as $index => $item)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $item->description }}</strong>
                                                        @if($item->notes)
                                                            <br><small class="text-muted">{{ $item->notes }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $item->quantity }}</td>
                                                    <td class="text-end">{{ config('app.currency', 'TZS') }} {{ number_format($item->unit_price, 2) }}</td>
                                                    <td class="text-end fw-bold">{{ config('app.currency', 'TZS') }} {{ number_format($item->amount, 2) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-active">
                                                    <th colspan="4" class="text-end">Total Amount</th>
                                                    <th class="text-end fs-5 fw-bold text-primary">{{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->total_amount, 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card border-secondary mt-4">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    @if($feeInvoice->status === 'draft')
                                                        <div class="alert alert-warning mb-0 py-2">
                                                            <i class="bx bx-info-circle me-1"></i>
                                                            Only the due date can be modified for draft invoices.
                                                        </div>
                                                    @else
                                                        <div class="alert alert-danger mb-0 py-2">
                                                            <i class="bx bx-lock me-1"></i>
                                                            This invoice cannot be edited because it is not in draft status.
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('college.fee-invoices.show', $feeInvoice->hashid) }}" class="btn btn-secondary">
                                                        <i class="bx bx-arrow-back me-1"></i> Cancel
                                                    </a>
                                                    @if($feeInvoice->status === 'draft')
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bx bx-save me-1"></i> Update Invoice
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
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
    .page-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    .breadcrumb {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #6c757d;
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

    .bg-dark {
        background: linear-gradient(135deg, #343a40 0%, #212529 100%) !important;
    }

    .form-control {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .form-control-lg {
        padding: 1rem 1.25rem;
        font-size: 1.1rem;
    }

    .form-control:disabled,
    .form-control[readonly] {
        background-color: #f8f9fa !important;
        opacity: 1;
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

    .table {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .table thead th {
        background: linear-gradient(135deg, #343a40 0%, #212529 100%) !important;
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: scale(1.01);
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

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        min-height: 48px;
    }

    .select2-container--bootstrap4 .select2-selection:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
</style>
@endpush

@push('scripts')
<script>
// Global AJAX setup for CSRF token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    console.log('Fee invoice edit form loaded');

    // Form validation
    $('#editInvoiceForm').on('submit', function(e) {
        const dueDate = $('#due_date').val();
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 1);
        const selectedDate = new Date(dueDate);

        if (selectedDate < minDate) {
            e.preventDefault();
            alert('Due date must be at least tomorrow.');
            $('#due_date').focus();
            return false;
        }
    });

    // Due date validation on change
    $('#due_date').on('change', function() {
        const selectedDate = new Date($(this).val());
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 1);

        if (selectedDate < minDate) {
            $(this).addClass('is-invalid');
            $('.invalid-feedback').text('Due date must be at least tomorrow.');
        } else {
            $(this).removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }
    });
});
</script>
@endpush