@extends('layouts.main')

@section('title', 'Fee Invoice Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Invoices', 'url' => route('school.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Invoice Details', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />
        <h6 class="mb-0 text-uppercase">FEE INVOICE DETAILS - {{ $feeInvoice->invoice_number }}</h6>
        <hr />

        <!-- Invoice Information Card -->
        <div class="card border-info mb-4">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bx bx-receipt me-2"></i> Invoice Information
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('school.fee-invoices.edit', $feeInvoice->hashid) }}" class="btn btn-warning btn-sm">
                        <i class="bx bx-edit me-1"></i> Edit
                    </a>
                    <a href="{{ route('school.fee-invoices.index') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Back to Invoices
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th><i class="bx bx-hash me-1"></i>Invoice Number:</th>
                                <td><strong>{{ $feeInvoice->invoice_number }}</strong></td>
                            </tr>
                            <tr>
                                <th><i class="bx bx-user me-1"></i>Student Name:</th>
                                <td>{{ $feeInvoice->student ? $feeInvoice->student->first_name . ' ' . $feeInvoice->student->last_name : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th><i class="bx bx-id-card me-1"></i>Admission Number:</th>
                                <td>{{ $feeInvoice->student ? $feeInvoice->student->admission_number : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th><i class="bx bx-school me-1"></i>Class:</th>
                                <td>{{ $feeInvoice->classe ? $feeInvoice->classe->name : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th><i class="bx bx-bed me-1"></i>Boarding Type:</th>
                                <td>{{ ucfirst($feeInvoice->student->boarding_type ?? 'day') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th><i class="bx bx-calendar me-1"></i>Academic Year:</th>
                                <td>{{ $feeInvoice->academicYear ? $feeInvoice->academicYear->year_name : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th><i class="bx bx-group me-1"></i>Fee Group:</th>
                                <td>{{ $feeInvoice->feeGroup ? $feeInvoice->feeGroup->name : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th><i class="bx bx-calendar-week me-1"></i>Period:</th>
                                <td>{{ $feeInvoice->period }} Quarter{{ $feeInvoice->period > 1 ? 's' : '' }}</td>
                            </tr>
                            <tr>
                                <th><i class="bx bx-info-circle me-1"></i>Status:</th>
                                <td>
                                    @php
                                        $statusClasses = [
                                            'draft' => 'secondary',
                                            'issued' => 'primary',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                        $statusClass = $statusClasses[$feeInvoice->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">
                                        {{ ucfirst($feeInvoice->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><i class="bx bx-money me-1"></i>Total Amount:</th>
                                <td><strong class="text-primary">TZS {{ number_format($feeInvoice->total_amount, 2) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary shadow-sm">
                    <div class="card-body text-center">
                        <i class="bx bx-money text-primary" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-1">TZS {{ number_format($totalAmount, 2) }}</h4>
                        <p class="text-muted small mb-0">Total Fees</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success shadow-sm">
                    <div class="card-body text-center">
                        <i class="bx bx-check-circle text-success" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-1">TZS {{ number_format($paidAmount, 2) }}</h4>
                        <p class="text-muted small mb-0">Paid</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning shadow-sm">
                    <div class="card-body text-center">
                        <i class="bx bx-time-five text-warning" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-1">TZS {{ number_format($outstandingAmount, 2) }}</h4>
                        <p class="text-muted small mb-0">Outstanding</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info shadow-sm">
                    <div class="card-body text-center">
                        <i class="bx bx-wallet text-info" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-1">TZS {{ number_format($outstandingAmount, 2) }}</h4>
                        <p class="text-muted small mb-0">Balance</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quarterly Fee Breakdown -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bx bx-calendar-week me-2"></i>Quarterly Fee Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="bx bx-calendar me-1"></i>Quarter</th>
                                <th><i class="bx bx-money me-1"></i>Total Amount</th>
                                <th><i class="bx bx-check me-1"></i>Paid Amount</th>
                                <th><i class="bx bx-time me-1"></i>Outstanding Amount</th>
                                <th><i class="bx bx-wallet me-1"></i>Balance</th>
                                <th><i class="bx bx-info-circle me-1"></i>Status</th>
                                <th><i class="bx bx-receipt me-1"></i>Invoices</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quarterlyFees as $quarter => $data)
                            <tr>
                                <td><strong>{{ $quarter }}</strong></td>
                                <td>TZS {{ number_format($data['total'], 2) }}</td>
                                <td class="text-success">TZS {{ number_format($data['paid'], 2) }}</td>
                                <td class="text-warning">TZS {{ number_format($data['outstanding'], 2) }}</td>
                                <td class="text-info">TZS {{ number_format($data['outstanding'], 2) }}</td>
                                <td>
                                    @if($data['outstanding'] == 0)
                                        <span class="badge bg-success">Fully Paid</span>
                                    @elseif($data['paid'] > 0)
                                        <span class="badge bg-warning">Partially Paid</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td>{{ count($data['invoices']) }} invoice(s)</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th><strong>TOTAL</strong></th>
                                <th>TZS {{ number_format($totalAmount, 2) }}</th>
                                <th class="text-success">TZS {{ number_format($paidAmount, 2) }}</th>
                                <th class="text-warning">TZS {{ number_format($outstandingAmount, 2) }}</th>
                                <th class="text-info">TZS {{ number_format($outstandingAmount, 2) }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Current Invoice Fee Breakdown -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">
                    <i class="bx bx-list me-2"></i>Current Invoice Fee Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><i class="bx bx-tag me-1"></i>Fee Category</th>
                                <th><i class="bx bx-money me-1"></i>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($feeInvoice->items as $item)
                                <tr>
                                    <td>
                                        @if($item->category === 'opening_balance')
                                            <strong><i class="bx bx-wallet me-1"></i>{{ $item->fee_name }}</strong>
                                        @else
                                            {{ $item->feeSettingItem ? $item->feeSettingItem->description : ($item->fee_name ?? 'N/A') }}
                                        @endif
                                    </td>
                                    <td>TZS {{ number_format($item->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted">No fee items found</td>
                                </tr>
                            @endforelse
                            @if($feeInvoice->transport_fare > 0)
                                <tr>
                                    <td><strong><i class="bx bx-bus me-1"></i>Transport Fare</strong></td>
                                    <td><strong>TZS {{ number_format($feeInvoice->transport_fare, 2) }}</strong></td>
                                </tr>
                            @endif
                            @if($feeInvoice->discount_amount > 0)
                                <tr class="table-warning">
                                    <td><strong><i class="bx bx-discount me-1"></i>Discount ({{ ucfirst($feeInvoice->discount_type ?? 'N/A') }})</strong></td>
                                    <td><strong class="text-danger">- TZS {{ number_format($feeInvoice->discount_amount, 2) }}</strong></td>
                                </tr>
                            @endif
                            <tr class="table-primary">
                                <td><strong><i class="bx bx-calculator me-1"></i>Total</strong></td>
                                <td><strong>TZS {{ number_format($feeInvoice->total_amount, 2) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detailed Invoice List -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bx bx-receipt me-2"></i>All Student Invoices
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><i class="bx bx-hash me-1"></i>Invoice Number</th>
                                <th><i class="bx bx-calendar me-1"></i>Academic Year</th>
                                <th><i class="bx bx-calendar-week me-1"></i>Period</th>
                                <th><i class="bx bx-group me-1"></i>Fee Group</th>
                                <th><i class="bx bx-money me-1"></i>Subtotal</th>
                                <th><i class="bx bx-bus me-1"></i>Transport Fare</th>
                                <th><i class="bx bx-discount me-1"></i>Discount</th>
                                <th><i class="bx bx-money me-1"></i>Total Amount</th>
                                <th><i class="bx bx-info-circle me-1"></i>Status</th>
                                <th><i class="bx bx-calendar-check me-1"></i>Issue Date</th>
                                <th><i class="bx bx-calendar-x me-1"></i>Due Date</th>
                                <th><i class="bx bx-cog me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentInvoices as $invoice)
                            <tr class="{{ $invoice->id === $feeInvoice->id ? 'table-warning' : '' }}">
                                <td>
                                    {{ $invoice->invoice_number }}
                                    @if($invoice->id === $feeInvoice->id)
                                        <small class="text-muted">(Current)</small>
                                    @endif
                                </td>
                                <td>{{ $invoice->academicYear->year_name ?? 'N/A' }}</td>
                                <td>Q{{ $invoice->period }}</td>
                                <td>{{ $invoice->feeGroup->name ?? 'N/A' }}</td>
                                <td>TZS {{ number_format($invoice->subtotal, 2) }}</td>
                                <td>TZS {{ number_format($invoice->transport_fare, 2) }}</td>
                                <td>
                                    @if($invoice->discount_amount > 0)
                                        <span class="text-danger">- TZS {{ number_format($invoice->discount_amount, 2) }}</span>
                                        <br><small class="text-muted">{{ ucfirst($invoice->discount_type ?? 'N/A') }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
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
                                <td>{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <a href="{{ route('school.fee-invoices.show', $invoice->hashid) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @if(in_array($invoice->status, ['draft', 'issued']))
                                    <a href="{{ route('school.fee-invoices.edit', $invoice->hashid) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">
                                    <i class="bx bx-info-circle me-2"></i>
                                    No fee invoices found for this student.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($studentInvoices->count() > 0)
                        <tfoot>
                            <tr class="table-dark">
                                <th colspan="4" class="text-end">TOTALS:</th>
                                <th>TZS {{ number_format($studentInvoices->sum('subtotal'), 2) }}</th>
                                <th>TZS {{ number_format($studentInvoices->sum('transport_fare'), 2) }}</th>
                                <th><strong>TZS {{ number_format($totalAmount, 2) }}</strong></th>
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection