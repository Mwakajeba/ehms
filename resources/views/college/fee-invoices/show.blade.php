@extends('layouts.main')

@section('title', 'Fee Invoice Details - ' . $feeInvoice->invoice_number)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Fee Invoices', 'url' => route('college.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Invoice Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="row">
            <div class="col-12">
                <!-- Invoice Header Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bx bx-receipt me-2"></i>Fee Invoice Details
                            </h5>
                            <small class="text-white-50">Invoice #{{ $feeInvoice->invoice_number }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            @if($feeInvoice->status === 'draft')
                                <a href="{{ route('college.fee-invoices.edit', $feeInvoice->hashid) }}" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit Invoice
                                </a>
                            @endif
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

                        <!-- Invoice Details Grid -->
                        <div class="row g-4">
                            <!-- Student Information -->
                            <div class="col-lg-6">
                                <div class="card border-info h-100">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="bx bx-user me-2"></i>Student Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <strong class="text-muted d-block">Student Name</strong>
                                                <span class="fs-5">{{ $feeInvoice->student->full_name ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <strong class="text-muted d-block">Student Number</strong>
                                                <span class="fs-5">{{ $feeInvoice->student->student_number ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-12">
                                                <strong class="text-muted d-block">Program</strong>
                                                <span>{{ $feeInvoice->program->name ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-12">
                                                <strong class="text-muted d-block">Academic Year</strong>
                                                <span>{{ $feeInvoice->academicYear->year_name ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Information -->
                            <div class="col-lg-6">
                                <div class="card border-success h-100">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="bx bx-receipt me-2"></i>Invoice Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <strong class="text-muted d-block">Invoice Number</strong>
                                                <span class="fs-5">{{ $feeInvoice->invoice_number }}</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <strong class="text-muted d-block">Fee Group</strong>
                                                <span>{{ $feeInvoice->feeGroup->name ?? 'N/A' }}</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <strong class="text-muted d-block">Fee Period</strong>
                                                <span>{{ $feeInvoice->getFeePeriodOptions()[$feeInvoice->period] ?? $feeInvoice->period }}</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <strong class="text-muted d-block">Issue Date</strong>
                                                <span>{{ $feeInvoice->issue_date ? $feeInvoice->issue_date->format('M d, Y') : 'N/A' }}</span>
                                            </div>
                                            <div class="col-sm-6">
                                                <strong class="text-muted d-block">Due Date</strong>
                                                <span class="{{ $feeInvoice->due_date && $feeInvoice->due_date->isPast() && $feeInvoice->status !== 'paid' ? 'text-danger fw-bold' : '' }}">
                                                    {{ $feeInvoice->due_date ? $feeInvoice->due_date->format('M d, Y') : 'N/A' }}
                                                </span>
                                            </div>
                                            <div class="col-sm-6">
                                                <strong class="text-muted d-block">Total Amount</strong>
                                                <span class="fs-5 fw-bold text-primary">{{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->total_amount, 2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        @if($feeInvoice->status !== 'draft')
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="bx bx-money me-2"></i>Payment Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="fs-4 fw-bold text-success">{{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->paid_amount, 2) }}</div>
                                                    <small class="text-muted">Paid Amount</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="fs-4 fw-bold text-info">{{ config('app.currency', 'TZS') }} {{ number_format($feeInvoice->total_amount - $feeInvoice->paid_amount, 2) }}</div>
                                                    <small class="text-muted">Outstanding</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="fs-4 fw-bold text-primary">{{ number_format(($feeInvoice->paid_amount / $feeInvoice->total_amount) * 100, 1) }}%</div>
                                                    <small class="text-muted">Payment Progress</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <div class="fs-4 fw-bold {{ $feeInvoice->due_date && $feeInvoice->due_date->isPast() && $feeInvoice->status !== 'paid' ? 'text-danger' : 'text-success' }}">
                                                        {{ $feeInvoice->due_date ? $feeInvoice->due_date->diffForHumans() : 'N/A' }}
                                                    </div>
                                                    <small class="text-muted">Due Status</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-list-ul me-2"></i>Invoice Items
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
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        @if($feeInvoice->status === 'draft')
                                            <div class="alert alert-warning mb-0 py-2">
                                                <i class="bx bx-info-circle me-1"></i>
                                                This invoice is in draft status and can still be edited or deleted.
                                            </div>
                                        @elseif($feeInvoice->status === 'issued')
                                            <div class="alert alert-info mb-0 py-2">
                                                <i class="bx bx-send me-1"></i>
                                                This invoice has been sent to the student and is awaiting payment.
                                            </div>
                                        @elseif($feeInvoice->status === 'paid')
                                            <div class="alert alert-success mb-0 py-2">
                                                <i class="bx bx-check-circle me-1"></i>
                                                This invoice has been fully paid.
                                            </div>
                                        @elseif($feeInvoice->status === 'overdue')
                                            <div class="alert alert-danger mb-0 py-2">
                                                <i class="bx bx-error-circle me-1"></i>
                                                This invoice is overdue for payment.
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('college.fee-invoices.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to Invoices
                                        </a>
                                        @if($feeInvoice->status === 'draft')
                                            <a href="{{ route('college.fee-invoices.edit', $feeInvoice->hashid) }}" class="btn btn-warning">
                                                <i class="bx bx-edit me-1"></i> Edit Invoice
                                            </a>
                                            <form action="{{ route('college.fee-invoices.destroy', $feeInvoice->hashid) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this fee invoice? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="bx bx-trash me-1"></i> Delete Invoice
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
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

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border: none;
    }

    .btn-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
    }

    .btn-danger:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
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
</style>
@endpush