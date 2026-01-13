@extends('layouts.main')

@section('title', 'Student Fee Statement')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Invoices', 'url' => route('school.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Student Statement', 'url' => '#', 'icon' => 'bx bx-user']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT FEE STATEMENT - {{ $student->first_name . ' ' . $student->last_name }}</h6>
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
                            <tr>
                                <th>Boarding Type:</th>
                                <td>{{ ucfirst($student->boarding_type ?? 'day') }}</td>
                            </tr>
                            @if($student->discount_type && $student->discount_value)
                            <tr>
                                <th>Discount:</th>
                                <td>
                                    <span class="badge bg-success">
                                        @if($student->discount_type === 'percentage')
                                            {{ $student->discount_value }}%
                                        @else
                                            TZS {{ number_format($student->discount_value, 2) }}
                                        @endif
                                        ({{ ucfirst($student->discount_type) }})
                                    </span>
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th>Academic Year:</th>
                                <td>{{ $student->academicYear->year_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $student->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($student->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Invoices:</th>
                                <td>{{ $invoices->count() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Discount Management Card -->
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bx bx-discount me-2"></i> Discount Management
                </h6>
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="toggleDiscountForm()">
                    <i class="bx bx-edit me-1"></i> Manage Discount
                </button>
            </div>
            <div class="card-body">
                <!-- Current Discount Display -->
                <div id="discount-display">
                    @if($student->discount_type && $student->discount_value)
                        <div class="alert alert-success">
                            <h6 class="alert-heading mb-2">
                                <i class="bx bx-check-circle me-2"></i> Active Discount
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Type:</strong> {{ ucfirst($student->discount_type) }}</p>
                                    <p class="mb-1">
                                        <strong>Value:</strong>
                                        @if($student->discount_type === 'percentage')
                                            {{ $student->discount_value }}%
                                        @else
                                            TZS {{ number_format($student->discount_value, 2) }}
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-success">Active</span></p>
                                    <p class="mb-0"><strong>Applied to:</strong> All future fee invoices</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h6 class="alert-heading mb-2">
                                <i class="bx bx-info-circle me-2"></i> No Discount Applied
                            </h6>
                            <p class="mb-0">This student currently has no discount settings. Click "Manage Discount" to add one.</p>
                        </div>
                    @endif
                </div>

                <!-- Discount Form (Hidden by default) -->
                <div id="discount-form" style="display: none;">
                    <form id="discountForm" method="POST" action="{{ route('school.students.update', $student) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_type" class="form-label fw-bold">Discount Type</label>
                                    <select class="form-select" id="discount_type" name="discount_type">
                                        <option value="">No Discount</option>
                                        <option value="fixed" {{ $student->discount_type == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                        <option value="percentage" {{ $student->discount_type == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    </select>
                                    <div class="form-text text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Select the type of discount to apply
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount_value" class="form-label fw-bold">Discount Value</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="discount_value" name="discount_value"
                                               value="{{ $student->discount_value }}" min="0" step="0.01" placeholder="0.00">
                                        <span class="input-group-text" id="discount-unit">
                                            {{ $student->discount_type === 'percentage' ? '%' : 'TZS' }}
                                        </span>
                                    </div>
                                    <div class="form-text text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Enter the discount amount or percentage
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" onclick="toggleDiscountForm()">
                                <i class="bx bx-x me-1"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-success" id="saveDiscountBtn">
                                <i class="bx bx-save me-1"></i> Save Discount
                            </button>
                        </div>
                    </form>
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
                        <p class="text-muted small mb-0">Total Fees Invoiced</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success shadow-sm">
                    <div class="card-body text-center">
                        <i class="bx bx-check-circle text-success" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-1">TZS {{ number_format($paidAmount + ($openingBalance ? $openingBalance->paid_amount : 0), 2) }}</h4>
                        <p class="text-muted small mb-0">Total Paid</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning shadow-sm">
                    <div class="card-body text-center">
                        <i class="bx bx-time-five text-warning" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-1">TZS {{ number_format($pendingAmount + ($openingBalance && $openingBalance->balance_due > 0 ? $openingBalance->balance_due : 0), 2) }}</h4>
                        <p class="text-muted small mb-0">Total Outstanding</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info shadow-sm">
                    <div class="card-body text-center">
                        <i class="bx bx-wallet text-info" style="font-size: 2rem;"></i>
                        <h4 class="mt-2 mb-1">TZS {{ number_format($pendingAmount + ($openingBalance && $openingBalance->balance_due > 0 ? $openingBalance->balance_due : 0), 2) }}</h4>
                        <p class="text-muted small mb-0">Total Balance Due</p>
                    </div>
                </div>
            </div>
        </div>
        @if($openingBalance && $openingBalance->balance_due > 0)
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-warning">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-wallet me-2" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <strong>Opening Balance:</strong> 
                            TZS {{ number_format($openingBalance->balance_due, 2) }} outstanding
                            @if($openingBalance->lipisha_control_number)
                                | Control Number: <span class="badge bg-info">{{ $openingBalance->lipisha_control_number }}</span>
                            @endif
                        </div>
                        <a href="{{ route('school.student-fee-opening-balance.show', $openingBalance->getRouteKey()) }}" class="btn btn-sm btn-outline-warning">
                            <i class="bx bx-show me-1"></i> View Details
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Opening Balance Card -->
        @if($openingBalance)
        <div class="card border-warning shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="card-title mb-0">
                    <i class="bx bx-wallet me-2"></i>Opening Balance
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bx bx-wallet text-warning" style="font-size: 2rem;"></i>
                            <h5 class="mt-2 mb-1">TZS {{ number_format($openingBalance->amount, 2) }}</h5>
                            <p class="text-muted small mb-0">Opening Balance Amount</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bx bx-check-circle text-success" style="font-size: 2rem;"></i>
                            <h5 class="mt-2 mb-1">TZS {{ number_format($openingBalance->paid_amount, 2) }}</h5>
                            <p class="text-muted small mb-0">Paid Amount</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bx bx-time-five text-danger" style="font-size: 2rem;"></i>
                            <h5 class="mt-2 mb-1">TZS {{ number_format($openingBalance->balance_due, 2) }}</h5>
                            <p class="text-muted small mb-0">Balance Due</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 bg-light rounded">
                            <i class="bx bx-hash text-info" style="font-size: 2rem;"></i>
                            <h5 class="mt-2 mb-1">
                                @if($openingBalance->lipisha_control_number)
                                    <span class="badge bg-info">{{ $openingBalance->lipisha_control_number }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </h5>
                            <p class="text-muted small mb-0">Control Number</p>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <table class="table table-borderless">
                            <tr>
                                <th width="150">Opening Date:</th>
                                <td>{{ $openingBalance->opening_date ? \Carbon\Carbon::parse($openingBalance->opening_date)->format('M d, Y') : 'N/A' }}</td>
                                <th width="150">Status:</th>
                                <td>
                                    @if($openingBalance->status === 'closed')
                                        <span class="badge bg-success">Closed</span>
                                    @elseif($openingBalance->status === 'posted')
                                        <span class="badge bg-warning">Posted</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($openingBalance->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($openingBalance->notes)
                            <tr>
                                <th>Notes:</th>
                                <td colspan="3">{{ $openingBalance->notes }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                @if($openingBalance->balance_due > 0 && !$lipishaEnabled)
                <div class="row mt-3">
                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addOpeningBalancePaymentModal" data-opening-balance-id="{{ $openingBalance->getRouteKey() }}" title="Add Payment for Opening Balance">
                            <i class="bx bx-plus me-1"></i> Add Payment for Opening Balance
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Quarterly Fee Breakdown -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
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
                                <th><i class="bx bx-cog me-1"></i>Actions</th>
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
                                <td>
                                    @if(!$lipishaEnabled)
                                    <div class="btn-group" role="group">
                                        @if($data['outstanding'] > 0)
                                            <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModal" data-student-id="{{ $student->getRouteKey() }}" title="Add Payment">
                                                <i class="bx bx-plus me-1"></i>Add Payment
                                            </button>
                                        @endif
                                        @if($data['payments']->count() > 0)
                                            <button type="button" class="btn btn-outline-info btn-sm" onclick="viewReceipts('{{ $quarter }}')" title="View Receipts">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="editReceipts('{{ $quarter }}')" title="Edit Receipts">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteReceipts('{{ $quarter }}')" title="Delete Receipts">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        @elseif($data['outstanding'] == 0)
                                            <span class="text-muted small">No receipts available</span>
                                        @endif
                                    </div>
                                    @else
                                    <span class="text-muted small">Payments via LIPISHA</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th><strong>TOTAL INVOICES</strong></th>
                                <th>TZS {{ number_format($totalAmount, 2) }}</th>
                                <th class="text-success">TZS {{ number_format($paidAmount, 2) }}</th>
                                <th class="text-warning">TZS {{ number_format($pendingAmount, 2) }}</th>
                                <th class="text-info">TZS {{ number_format($pendingAmount, 2) }}</th>
                                <th colspan="3"></th>
                            </tr>
                            @if($openingBalance && $openingBalance->balance_due > 0)
                            <tr class="table-warning">
                                <th><strong><i class="bx bx-wallet me-1"></i>OPENING BALANCE</strong></th>
                                <th>TZS {{ number_format($openingBalance->amount, 2) }}</th>
                                <th class="text-success">TZS {{ number_format($openingBalance->paid_amount, 2) }}</th>
                                <th class="text-warning">TZS {{ number_format($openingBalance->balance_due, 2) }}</th>
                                <th class="text-danger"><strong>TZS {{ number_format($openingBalance->balance_due, 2) }}</strong></th>
                                <th>
                                    @if($openingBalance->status === 'closed')
                                        <span class="badge bg-success">Closed</span>
                                    @else
                                        <span class="badge bg-warning">Outstanding</span>
                                    @endif
                                </th>
                                <th>
                                    @if($openingBalance->lipisha_control_number)
                                        <span class="badge bg-info">{{ $openingBalance->lipisha_control_number }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </th>
                                <th>
                                    @if(!$lipishaEnabled)
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#addOpeningBalancePaymentModal" data-opening-balance-id="{{ $openingBalance->getRouteKey() }}" title="Add Payment">
                                            <i class="bx bx-plus me-1"></i>Add Payment
                                        </button>
                                        <a href="{{ route('school.student-fee-opening-balance.show', $openingBalance->getRouteKey()) }}" class="btn btn-outline-info btn-sm" title="View Opening Balance">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </div>
                                    @else
                                    <a href="{{ route('school.student-fee-opening-balance.show', $openingBalance->getRouteKey()) }}" class="btn btn-outline-info btn-sm" title="View Opening Balance">
                                        <i class="bx bx-show"></i> View
                                    </a>
                                    @endif
                                </th>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th><strong><i class="bx bx-calculator me-1"></i>GRAND TOTAL</strong></th>
                                <th><strong>TZS {{ number_format($totalAmount + ($openingBalance && $openingBalance->balance_due > 0 ? $openingBalance->amount : 0), 2) }}</strong></th>
                                <th class="text-success"><strong>TZS {{ number_format($paidAmount + ($openingBalance ? $openingBalance->paid_amount : 0), 2) }}</strong></th>
                                <th class="text-warning"><strong>TZS {{ number_format($pendingAmount + ($openingBalance && $openingBalance->balance_due > 0 ? $openingBalance->balance_due : 0), 2) }}</strong></th>
                                <th class="text-danger"><strong>TZS {{ number_format($pendingAmount + ($openingBalance && $openingBalance->balance_due > 0 ? $openingBalance->balance_due : 0), 2) }}</strong></th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detailed Invoice List -->
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h5 class="card-title mb-0 me-3">
                        <i class="bx bx-receipt me-2"></i>Invoice Details
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm" onclick="viewDetailedInvoices()">
                        <i class="bx bx-show me-1"></i> View Invoice(s)
                    </button>
                </div>
                <a href="{{ route('school.fee-invoices.index') }}" class="btn btn-light btn-sm">
                    <i class="bx bx-arrow-back me-1"></i> Back to Invoices
                </a>
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
                                <th><i class="bx bx-discount me-1"></i>Discount</th>
                                <th><i class="bx bx-bus me-1"></i>Transport Fare</th>
                                <th><i class="bx bx-money me-1"></i>Total Amount</th>
                                <th><i class="bx bx-info-circle me-1"></i>Status</th>
                                <th><i class="bx bx-calendar-check me-1"></i>Issue Date</th>
                                <th><i class="bx bx-calendar-x me-1"></i>Due Date</th>
                                <th><i class="bx bx-cog me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($openingBalance && $openingBalance->balance_due > 0)
                            <tr class="table-warning">
                                <td>
                                    <strong><i class="bx bx-wallet me-1"></i>Opening Balance</strong>
                                    @if($openingBalance->lipisha_control_number)
                                        <br><small class="text-muted">Control: <span class="badge bg-info">{{ $openingBalance->lipisha_control_number }}</span></small>
                                    @endif
                                </td>
                                <td>{{ $openingBalance->academicYear->year_name ?? 'N/A' }}</td>
                                <td>-</td>
                                <td>{{ $openingBalance->feeGroup->name ?? 'N/A' }}</td>
                                <td>TZS {{ number_format($openingBalance->amount, 2) }}</td>
                                <td>
                                    <span class="text-success">
                                        -TZS {{ number_format($openingBalance->paid_amount, 2) }}
                                    </span>
                                </td>
                                <td>-</td>
                                <td><strong>TZS {{ number_format($openingBalance->balance_due, 2) }}</strong></td>
                                <td>
                                    @if($openingBalance->status === 'closed')
                                        <span class="badge bg-success">Closed</span>
                                    @else
                                        <span class="badge bg-warning">Outstanding</span>
                                    @endif
                                </td>
                                <td>{{ $openingBalance->opening_date ? \Carbon\Carbon::parse($openingBalance->opening_date)->format('M d, Y') : 'N/A' }}</td>
                                <td>-</td>
                                <td>
                                    @if(!$lipishaEnabled)
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addOpeningBalancePaymentModal" data-opening-balance-id="{{ $openingBalance->getRouteKey() }}" title="Add Payment">
                                            <i class="bx bx-plus me-1"></i>Add Payment
                                        </button>
                                        <a href="{{ route('school.student-fee-opening-balance.show', $openingBalance->getRouteKey()) }}" class="btn btn-info btn-sm" title="View Opening Balance">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </div>
                                    @else
                                    <a href="{{ route('school.student-fee-opening-balance.show', $openingBalance->getRouteKey()) }}" class="btn btn-info btn-sm" title="View Opening Balance">
                                        <i class="bx bx-show"></i> View
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endif
                            @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->academicYear->year_name ?? 'N/A' }}</td>
                                <td>Q{{ $invoice->period }}</td>
                                <td>{{ $invoice->feeGroup->name ?? 'N/A' }}</td>
                                <td>TZS {{ number_format($invoice->subtotal, 2) }}</td>
                                <td>
                                    @if($invoice->discount_amount > 0)
                                        <span class="text-success">
                                            -TZS {{ number_format($invoice->discount_amount, 2) }}
                                        </span>
                                        @if($invoice->discount_type)
                                            <br><small class="text-muted">
                                                ({{ ucfirst($invoice->discount_type) }}: {{ $invoice->discount_value }}{{ $invoice->discount_type === 'percentage' ? '%' : ' TZS' }})
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>TZS {{ number_format($invoice->transport_fare, 2) }}</td>
                                <td><strong>TZS {{ number_format($invoice->total_amount, 2) }}</strong></td>
                                <td>
                                    @php
                                        $paidAmount = $invoice->paid_amount ?? 0;
                                        $totalAmount = $invoice->total_amount;
                                        $outstandingAmount = $totalAmount - $paidAmount;

                                        if ($paidAmount >= $totalAmount) {
                                            $statusText = 'Paid';
                                            $statusClass = 'badge-status-paid';
                                        } elseif ($paidAmount > 0) {
                                            $statusText = 'Partially Paid';
                                            $statusClass = 'badge-status-partial';
                                        } elseif ($invoice->status === 'overdue') {
                                            $statusText = 'Overdue';
                                            $statusClass = 'badge-status-overdue';
                                        } elseif ($invoice->status === 'cancelled') {
                                            $statusText = 'Cancelled';
                                            $statusClass = 'badge-status-cancelled';
                                        } elseif ($invoice->status === 'draft') {
                                            $statusText = 'Draft';
                                            $statusClass = 'badge-status-draft';
                                        } else {
                                            $statusText = 'Issued';
                                            $statusClass = 'badge-status-issued';
                                        }
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td>{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') : 'N/A' }}</td>
                                <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" onclick="viewSingleInvoice('{{ $invoice->hashid }}')" title="View">
                                        <i class="bx bx-show"></i>
                                    </button>
                                    @if(in_array($invoice->status, ['draft', 'issued']))
                                    <a href="{{ route('school.fee-invoices.edit', $invoice->hashid) }}" class="btn btn-warning btn-sm" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    @endif
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteInvoice('{{ $invoice->hashid }}', '{{ $invoice->invoice_number }}')" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted">
                                    <i class="bx bx-info-circle me-2"></i>
                                    No fee invoices found for this student.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($invoices->count() > 0 || ($openingBalance && $openingBalance->balance_due > 0))
                        <tfoot>
                            <tr class="table-dark">
                                <th colspan="4" class="text-end">TOTAL INVOICES:</th>
                                <th>TZS {{ number_format($invoices->sum('subtotal'), 2) }}</th>
                                <th>TZS {{ number_format($invoices->sum('discount_amount'), 2) }}</th>
                                <th>TZS {{ number_format($invoices->sum('transport_fare'), 2) }}</th>
                                <th><strong>TZS {{ number_format($totalAmount, 2) }}</strong></th>
                                <th colspan="4"></th>
                            </tr>
                            @if($openingBalance && $openingBalance->balance_due > 0)
                            <tr class="table-warning">
                                <th colspan="4" class="text-end"><strong><i class="bx bx-wallet me-1"></i>OPENING BALANCE:</strong></th>
                                <th>TZS {{ number_format($openingBalance->amount, 2) }}</th>
                                <th>TZS {{ number_format($openingBalance->paid_amount, 2) }}</th>
                                <th>-</th>
                                <th><strong>TZS {{ number_format($openingBalance->balance_due, 2) }}</strong></th>
                                <th>
                                    @if($openingBalance->status === 'closed')
                                        <span class="badge bg-success">Closed</span>
                                    @else
                                        <span class="badge bg-warning">Outstanding</span>
                                    @endif
                                </th>
                                <th colspan="3"></th>
                            </tr>
                            @endif
                            <tr class="table-primary">
                                <th colspan="4" class="text-end"><strong><i class="bx bx-calculator me-1"></i>GRAND TOTAL:</strong></th>
                                <th><strong>TZS {{ number_format($invoices->sum('subtotal') + ($openingBalance && $openingBalance->balance_due > 0 ? $openingBalance->amount : 0), 2) }}</strong></th>
                                <th><strong>TZS {{ number_format($invoices->sum('discount_amount') + ($openingBalance ? $openingBalance->paid_amount : 0), 2) }}</strong></th>
                                <th><strong>TZS {{ number_format($invoices->sum('transport_fare'), 2) }}</strong></th>
                                <th><strong>TZS {{ number_format($totalAmount + ($openingBalance && $openingBalance->balance_due > 0 ? $openingBalance->balance_due : 0), 2) }}</strong></th>
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

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addPaymentModalLabel">
                    <i class="bx bx-plus me-2"></i>Add Payment for Outstanding Invoice
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Student Information in Modal -->
                <div class="card border-info mb-3">
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
                                        <th>Outstanding Invoices:</th>
                                        <td><strong id="modalTotalInvoices"></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Total Outstanding:</th>
                                        <td id="modalTotalAmount"></td>
                                    </tr>
                                    <tr>
                                        <th>Selected Invoice:</th>
                                        <td><strong id="selected_invoice_name_short"></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form id="paymentForm" method="POST">
                    @csrf

                    <!-- Hidden Invoice ID -->
                    <input type="hidden" id="invoice_id" name="invoice_id" value="">

                    <!-- Selected Invoice Details -->
                    <div class="mb-3">
                        <label class="form-label">Selected Invoice Details</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Invoice:</strong> <span id="selected_invoice_name">Loading...</span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Outstanding Amount:</strong> <span id="selected_outstanding_amount">Loading...</span>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <strong>Period:</strong> <span id="selected_invoice_period">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                <label for="bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                                <select class="form-select @error('bank_account_id') is-invalid @enderror"
                                        id="bank_account_id" name="bank_account_id" required>
                                    <option value="">Select Bank Account</option>
                                    @php
                                        // Get bank accounts using same method as accounting/bank-accounts page
                                        $user = Auth::user();
                                        $invoiceBankAccounts = \App\Models\BankAccount::with([
                                            'chartAccount.accountClassGroup.accountClass'
                                        ])
                                            ->where(function($query) use ($user) {
                                                $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($user) {
                                                    $subQuery->where('company_id', $user->company_id);
                                                })
                                                ->orWhere('company_id', $user->company_id); // Also check direct company_id for backward compatibility
                                            })
                                            ->orderBy('name')
                                            ->get();
                                    @endphp
                                    @foreach($invoiceBankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                        {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                    </option>
                                    @endforeach
                                </select>
                                @if($invoiceBankAccounts->count() == 0)
                                    <small class="text-danger">
                                        <i class="bx bx-error-circle me-1"></i>No bank accounts configured. Please add bank accounts first.
                                    </small>
                                @endif
                                @error('bank_account_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
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
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button type="submit" form="paymentForm" class="btn btn-success">
                    <i class="bx bx-check me-1"></i> Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Opening Balance Payment Modal -->
<div class="modal fade" id="addOpeningBalancePaymentModal" tabindex="-1" aria-labelledby="addOpeningBalancePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="addOpeningBalancePaymentModalLabel">
                    <i class="bx bx-wallet me-2"></i>Add Payment for Opening Balance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Student Information in Modal -->
                <div class="card border-info mb-3">
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
                                        <th>Opening Balance Amount:</th>
                                        <td><strong>TZS {{ $openingBalance ? number_format($openingBalance->amount, 2) : '0.00' }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Paid Amount:</th>
                                        <td class="text-success">TZS {{ $openingBalance ? number_format($openingBalance->paid_amount, 2) : '0.00' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Balance Due:</th>
                                        <td class="text-danger"><strong id="openingBalanceDue">TZS {{ $openingBalance ? number_format($openingBalance->balance_due, 2) : '0.00' }}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <form id="openingBalancePaymentForm" method="POST" action="{{ route('school.fee-invoices.opening-balance-payment.store', $student->getRouteKey()) }}">
                    @csrf
                    <input type="hidden" name="opening_balance_id" id="opening_balance_id" value="{{ $openingBalance ? $openingBalance->id : '' }}">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ob_payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="ob_payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ob_bank_account_id" class="form-label">Bank Account <span class="text-danger">*</span></label>
                                <select class="form-select" id="ob_bank_account_id" name="bank_account_id" required>
                                    <option value="">Select Bank Account</option>
                                    @php
                                        // Get bank accounts using same method as accounting/bank-accounts page
                                        $user = Auth::user();
                                        $obBankAccounts = \App\Models\BankAccount::with([
                                            'chartAccount.accountClassGroup.accountClass'
                                        ])
                                            ->where(function($query) use ($user) {
                                                $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($user) {
                                                    $subQuery->where('company_id', $user->company_id);
                                                })
                                                ->orWhere('company_id', $user->company_id); // Also check direct company_id for backward compatibility
                                            })
                                            ->orderBy('name')
                                            ->get();
                                    @endphp
                                    @foreach($obBankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}">
                                        {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                    </option>
                                    @endforeach
                                </select>
                                @if($obBankAccounts->count() == 0)
                                    <small class="text-danger">
                                        <i class="bx bx-error-circle me-1"></i>No bank accounts configured. Please add bank accounts first.
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ob_reference_number" class="form-label">Reference Number</label>
                                <input type="text" class="form-control" id="ob_reference_number" name="reference_number" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="ob_amount" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" class="form-control" id="ob_amount" name="amount" step="0.01" min="0.01" max="{{ $openingBalance ? $openingBalance->balance_due : 0 }}" value="{{ $openingBalance ? $openingBalance->balance_due : 0 }}" required>
                                </div>
                                <small class="text-muted">Maximum: TZS {{ $openingBalance ? number_format($openingBalance->balance_due, 2) : '0.00' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ob_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="ob_notes" name="notes" rows="3" placeholder="Payment notes (optional)"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <button type="submit" form="openingBalancePaymentForm" class="btn btn-success">
                    <i class="bx bx-check me-1"></i> Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Pass quarterly fees data to JavaScript
window.quarterlyFees = @json($quarterlyFees);

// Pass student information to JavaScript
window.studentInfo = {
    name: '{{ $student->first_name . " " . $student->last_name }}',
    admission_number: '{{ $student->admission_number }}',
    class: '{{ $student->class->name ?? "N/A" }}',
    stream: '{{ $student->stream->name ?? "N/A" }}',
    academic_year: '{{ $student->academicYear->year_name ?? "N/A" }}',
    discount_type: '{{ $student->discount_type }}',
    discount_value: '{{ $student->discount_value }}'
};

// Pass company information to JavaScript
window.companyInfo = {
    name: '{{ $company->name ?? "School Management System" }}',
    address: '{{ $company->address ?? "" }}',
    phone: '{{ $company->phone ?? "" }}',
    email: '{{ $company->email ?? "" }}',
    logo: '{{ $company->logo ? asset("storage/" . $company->logo) : asset("images/default-logo.svg") }}'
};

// Pass bank accounts to JavaScript
window.bankAccounts = @json($bankAccounts);

// Pass opening balance information to JavaScript
window.openingBalance = @json($openingBalance);

// Add custom CSS for SweetAlert and invoice status badges
const style = document.createElement('style');
style.textContent = `
    .swal-wide {
        width: 600px !important;
    }
    .swal-wide .swal2-html-container {
        text-align: left !important;
    }
    .payment-option:hover {
        background-color: #f8f9fa !important;
        border-color: #007bff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }

    /* Custom invoice status badge colors */
    .badge-status-draft {
        background-color: #6c757d !important;
        color: #ffffff !important;
        font-weight: bold !important;
    }
    .badge-status-issued {
        background-color: #007bff !important;
        color: #ffffff !important;
        font-weight: bold !important;
    }
    .badge-status-paid {
        background-color: #28a745 !important;
        color: #ffffff !important;
        font-weight: bold !important;
    }
    .badge-status-overdue {
        background-color: #dc3545 !important;
        color: #ffffff !important;
        font-weight: bold !important;
    }
    .badge-status-cancelled {
        background-color: #343a40 !important;
        color: #ffffff !important;
        font-weight: bold !important;
    }
    .badge-status-partial {
        background-color: #ffc107 !important;
        color: #000000 !important;
        font-weight: bold !important;
    }

    /* Compact information sections */
    .compact-info-card .card-header {
        padding: 0.5rem 1rem !important;
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
    }

    .compact-info-card .card-header h6 {
        font-size: 0.9rem !important;
        margin-bottom: 0 !important;
        font-weight: 600 !important;
    }

    .compact-info-card .float-end {
        font-size: 0.8rem !important;
        color: #6c757d !important;
        font-weight: normal !important;
    }
`;
document.head.appendChild(style);

// Handle modal show event to populate data
document.getElementById('addPaymentModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const studentId = button.getAttribute('data-student-id');

    console.log('Student ID:', studentId);
    console.log('URL:', `/school/fee-invoices/student/${studentId}/single-payment-data`);

    // Update modal title and display

    // Update form action
    const form = document.getElementById('paymentForm');
    form.action = `/school/fee-invoices/student/${studentId}/single-payment-modal`;

    // Reset form fields
    document.getElementById('selected_invoice_name').textContent = 'Loading...';
    document.getElementById('selected_outstanding_amount').textContent = 'Loading...';
    document.getElementById('selected_invoice_period').textContent = 'Loading...';
    document.getElementById('amount').value = '';

    // Auto-select first bank account
    const bankAccountSelect = document.getElementById('bank_account_id');
    if (bankAccountSelect.options.length > 1) {
        bankAccountSelect.selectedIndex = 1; // Select the first bank account
    }

    // Fetch student invoices via AJAX
    const url = `/school/fee-invoices/student/${studentId}/single-payment-data`;
    console.log('URL:', url);
    $.ajax({
        url: url,
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.success && data.invoices.length > 0) {
                // Automatically select the first outstanding invoice
                const firstInvoice = data.invoices[0];
                const outstandingAmount = firstInvoice.outstanding_amount;

                // Set hidden invoice ID
                document.getElementById('invoice_id').value = firstInvoice.id;

                // Populate invoice details
                document.getElementById('selected_invoice_name').textContent = firstInvoice.invoice_number;
                document.getElementById('selected_invoice_name_short').textContent = firstInvoice.invoice_number;
                document.getElementById('selected_outstanding_amount').textContent = 'TZS ' + outstandingAmount.toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById('selected_invoice_period').textContent = firstInvoice.period;

                // Auto-fill payment amount
                document.getElementById('amount').value = outstandingAmount;

                // Update summary info
                document.getElementById('modalTotalInvoices').textContent = data.summary.total_invoices;
                document.getElementById('modalTotalAmount').innerHTML = `<strong>TZS ${data.summary.total_outstanding.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>`;
            } else {
                // No outstanding invoices
                document.getElementById('selected_invoice_name').textContent = 'No outstanding invoices';
                document.getElementById('selected_invoice_name_short').textContent = 'None';
                document.getElementById('selected_outstanding_amount').textContent = 'TZS 0.00';
                document.getElementById('selected_invoice_period').textContent = 'N/A';
                document.getElementById('modalTotalInvoices').textContent = '0';
                document.getElementById('modalTotalAmount').innerHTML = '<strong>TZS 0.00</strong>';
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.log('XHR:', xhr);
            document.getElementById('selected_invoice_name').textContent = 'Error loading data';
            document.getElementById('selected_invoice_name_short').textContent = 'Error';
            document.getElementById('selected_outstanding_amount').textContent = 'Error';
            document.getElementById('selected_invoice_period').textContent = 'Error';
        }
    });
});

// Remove payment method handling since we removed that field
document.addEventListener('DOMContentLoaded', function() {
    // No payment method handling needed anymore
});

// Function to view detailed invoices modal
function viewDetailedInvoices() {
    // Get invoice data from the global variables
    const quarterlyFees = window.quarterlyFees || {};
    const studentInfo = window.studentInfo || {};
    const companyInfo = window.companyInfo || {};
    const openingBalance = window.openingBalance || null;

    // Calculate totals
    let totalInvoiced = 0;
    let totalPaid = 0;
    let totalOutstanding = 0;

    Object.values(quarterlyFees).forEach(quarterData => {
        totalInvoiced += quarterData.total || 0;
        totalPaid += quarterData.paid || 0;
        totalOutstanding += quarterData.outstanding || 0;
    });

    // Add opening balance to totals if exists
    let openingBalanceAmount = 0;
    let openingBalancePaid = 0;
    let openingBalanceDue = 0;
    if (openingBalance && openingBalance.balance_due > 0) {
        openingBalanceAmount = parseFloat(openingBalance.amount) || 0;
        openingBalancePaid = parseFloat(openingBalance.paid_amount) || 0;
        openingBalanceDue = parseFloat(openingBalance.balance_due) || 0;
        totalInvoiced += openingBalanceAmount;
        totalPaid += openingBalancePaid;
        totalOutstanding += openingBalanceDue;
    }

    // Create detailed invoice modal content
    let modalContent = `
        <div class="modal fade" id="detailedInvoicesModal" tabindex="-1" aria-labelledby="detailedInvoicesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="detailedInvoicesModalLabel">
                            <i class="bx bx-file me-2"></i>Detailed Invoice Information
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="invoice-container" id="invoice-print-content">
                            <!-- Company Header -->
                            <div class="company-header text-center mb-4 pb-3 border-bottom">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <img src="${companyInfo.logo}" alt="Company Logo" class="company-logo img-fluid" style="max-height: 80px; max-width: 80px;">
                                    </div>
                                    <div class="col-md-6">
                                        <h3 class="company-name mb-1">${companyInfo.name || 'School Management System'}</h3>
                                        <p class="company-details mb-0 text-muted small">
                                            ${companyInfo.address ? companyInfo.address + '<br>' : ''}
                                            ${companyInfo.phone ? 'Phone: ' + companyInfo.phone + ' | ' : ''}
                                            ${companyInfo.email ? 'Email: ' + companyInfo.email : ''}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="invoice-type">
                                            <h4 class="mb-0 text-primary">STUDENT INVOICE</h4>
                                            <small class="text-muted">Generated: ${new Date().toLocaleDateString('en-US')}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Information -->
                            <div class="student-info mb-4">
                                <h5 class="section-title border-bottom pb-2 mb-3">
                                    <i class="bx bx-user me-2"></i>Student Information
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="fw-bold" style="width: 40%;">Name:</td>
                                                <td>${studentInfo.name || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Admission Number:</td>
                                                <td>${studentInfo.admission_number || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Class:</td>
                                                <td>${studentInfo.class || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Stream:</td>
                                                <td>${studentInfo.stream || 'N/A'}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td class="fw-bold" style="width: 40%;">Academic Year:</td>
                                                <td>${studentInfo.academic_year || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Total Invoiced:</td>
                                                <td class="fw-bold text-primary">TZS ${totalInvoiced.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Total Paid:</td>
                                                <td class="fw-bold text-success">TZS ${totalPaid.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Outstanding Balance:</td>
                                                <td class="fw-bold text-warning">TZS ${totalOutstanding.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>
                                            ${studentInfo.discount_type && studentInfo.discount_value ? `
                                            <tr>
                                                <td class="fw-bold">Discount:</td>
                                                <td class="fw-bold text-success">
                                                    ${studentInfo.discount_type === 'percentage' ? studentInfo.discount_value + '%' : 'TZS ' + parseFloat(studentInfo.discount_value).toLocaleString('en-US', {minimumFractionDigits: 2})}
                                                    <small class="text-muted">(${studentInfo.discount_type === 'percentage' ? 'Percentage' : 'Fixed Amount'})</small>
                                                </td>
                                            </tr>` : ''}
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Opening Balance Section -->
                            ${openingBalance && openingBalance.balance_due > 0 ? `
                            <div class="opening-balance-section mb-4 p-3 border rounded bg-warning bg-opacity-10">
                                <h5 class="section-title border-bottom pb-2 mb-3">
                                    <i class="bx bx-wallet me-2"></i>Opening Balance
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <td class="fw-bold" style="width: 40%;">Opening Date:</td>
                                                <td>${openingBalance.opening_date || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Academic Year:</td>
                                                <td>${openingBalance.academic_year_name || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Fee Group:</td>
                                                <td>${openingBalance.fee_group_name || 'N/A'}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Status:</td>
                                                <td>
                                                    ${openingBalance.status === 'closed' ? 
                                                        '<span class="badge bg-success">Closed</span>' : 
                                                        '<span class="badge bg-warning">Outstanding</span>'}
                                                </td>
                                            </tr>
                                            ${openingBalance.lipisha_control_number ? `
                                            <tr>
                                                <td class="fw-bold">Control Number:</td>
                                                <td><span class="badge bg-info">${openingBalance.lipisha_control_number}</span></td>
                                            </tr>` : ''}
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Description</th>
                                                    <th class="text-end">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Opening Balance Amount</strong></td>
                                                    <td class="text-end fw-bold text-primary">TZS ${openingBalanceAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Amount Paid</strong></td>
                                                    <td class="text-end fw-bold text-success">TZS ${openingBalancePaid.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Balance Due</strong></td>
                                                    <td class="text-end fw-bold text-danger">TZS ${openingBalanceDue.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                ${openingBalance.notes ? `
                                <div class="mt-2">
                                    <strong>Notes:</strong> <span class="text-muted">${openingBalance.notes}</span>
                                </div>` : ''}
                            </div>` : ''}

                            <!-- Invoice Details by Quarter -->
                            <div class="invoice-details mb-4">
                                <h5 class="section-title border-bottom pb-2 mb-3">
                                    <i class="bx bx-calendar-week me-2"></i>Quarterly Invoice Breakdown
                                </h5>`;

    // Add each quarter's details
    Object.entries(quarterlyFees).forEach(([quarter, quarterData]) => {
        modalContent += `
                                <div class="quarter-section mb-4 p-3 border rounded">
                                    <h6 class="quarter-title text-primary mb-3">
                                        <i class="bx bx-calendar me-2"></i>${quarter}
                                    </h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th><i class="bx bx-money me-1"></i>Description</th>
                                                    <th class="text-end"><i class="bx bx-calculator me-1"></i>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>Total Amount</strong></td>
                                                    <td class="text-end fw-bold text-primary">TZS ${(quarterData.total || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Amount Paid</strong></td>
                                                    <td class="text-end fw-bold text-success">TZS ${(quarterData.paid || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Outstanding</strong></td>
                                                    <td class="text-end fw-bold text-warning">TZS ${(quarterData.outstanding || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>`;

        // Add invoice details for this quarter
        if (quarterData.invoices && quarterData.invoices.length > 0) {
            modalContent += `
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Invoice #</th>
                                                    <th>Period</th>
                                                    <th>Fee Group</th>
                                                    <th>Subtotal</th>
                                                    <th>Discount</th>
                                                    <th>Transport</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;

            quarterData.invoices.forEach(invoice => {
                const paidAmount = invoice.paid_amount || 0;
                const outstandingAmount = invoice.total_amount - paidAmount;
                let statusBadge = '<span class="badge badge-status-paid">Paid</span>';

                if (outstandingAmount > 0 && paidAmount > 0) {
                    statusBadge = '<span class="badge badge-status-partial">Partially Paid</span>';
                } else if (outstandingAmount > 0) {
                    statusBadge = '<span class="badge badge-status-overdue">Unpaid</span>';
                }

                modalContent += `
                                                <tr>
                                                    <td class="fw-bold">${invoice.invoice_number}</td>
                                                    <td>Q${invoice.period}</td>
                                                    <td>${invoice.fee_group_name || 'N/A'}</td>
                                                    <td>TZS ${(invoice.subtotal || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                    <td>
                                                        ${invoice.discount_amount > 0 ? 
                                                            `<span class="text-success">-TZS ${(invoice.discount_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</span>` + 
                                                            (invoice.discount_type ? `<br><small class="text-muted">(${invoice.discount_type}: ${invoice.discount_value}${invoice.discount_type === 'percentage' ? '%' : ' TZS'})</small>` : '') 
                                                            : '<span class="text-muted">-</span>'}
                                                    </td>
                                                    <td>TZS ${(invoice.transport_fare || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                    <td class="fw-bold">TZS ${(invoice.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                    <td>${statusBadge}</td>
                                                </tr>`;
            });

            modalContent += `
                                            </tbody>
                                        </table>
                                    </div>`;
        }

        // Add payment history for this quarter
        if (quarterData.payments && quarterData.payments.length > 0) {
            modalContent += `
                                    <div class="payment-history">
                                        <h6 class="mb-2">
                                            <i class="bx bx-credit-card me-2"></i>Payment History
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Reference</th>
                                                        <th>Bank Account</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;

            quarterData.payments.forEach(payment => {
                const paymentDate = payment.date ? new Date(payment.date).toLocaleDateString('en-US') : 'N/A';
                modalContent += `
                                                    <tr>
                                                        <td>${paymentDate}</td>
                                                        <td>${payment.reference || 'N/A'}</td>
                                                        <td>${payment.bank_account ? payment.bank_account.name : 'N/A'}</td>
                                                        <td class="fw-bold text-success">TZS ${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                    </tr>`;
            });

            modalContent += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>`;
        }

        modalContent += `
                                </div>`;
    });

    modalContent += `
                            </div>

                            <!-- Summary Footer -->
                            <div class="summary-footer mt-4 p-3 bg-light rounded">
                                <h6 class="section-title border-bottom pb-2 mb-3">
                                    <i class="bx bx-calculator me-2"></i>Payment Summary
                                </h6>
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead class="table-dark">
                                            <tr>
                                                <th><i class="bx bx-money me-1"></i>Description</th>
                                                <th class="text-end"><i class="bx bx-calculator me-1"></i>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Total Invoices Amount</strong></td>
                                                <td class="text-end fw-bold text-primary">TZS ${(totalInvoiced - openingBalanceAmount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>
                                            ${openingBalance && openingBalance.balance_due > 0 ? `
                                            <tr class="table-warning">
                                                <td><strong><i class="bx bx-wallet me-1"></i>Opening Balance</strong></td>
                                                <td class="text-end fw-bold text-warning">TZS ${openingBalanceAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>` : ''}
                                            <tr class="table-primary">
                                                <td><strong><i class="bx bx-calculator me-1"></i>Grand Total Invoiced</strong></td>
                                                <td class="text-end fw-bold text-primary">TZS ${totalInvoiced.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Amount Paid</strong></td>
                                                <td class="text-end fw-bold text-success">TZS ${totalPaid.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Outstanding Balance</strong></td>
                                                <td class="text-end fw-bold ${totalOutstanding > 0 ? 'text-danger' : 'text-success'}">TZS ${Math.abs(totalOutstanding).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Signature Section -->
                            <div class="signature-section mt-4 pt-3 border-top text-center">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="border-top pt-2 mt-3">
                                            <small class="text-muted">Student/Parent Signature</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border-top pt-2 mt-3">
                                            <small class="text-muted">Authorized Signature</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Close
                        </button>
                        <button type="button" class="btn btn-primary" onclick="printDetailedInvoices()">
                            <i class="bx bx-printer me-1"></i> Print Invoice
                        </button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remove existing modal if present
    const existingModal = document.getElementById('detailedInvoicesModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalContent);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('detailedInvoicesModal'));
    modal.show();
}

// Function to print detailed invoices
function printDetailedInvoices() {
    const printContent = document.getElementById('invoice-print-content').innerHTML;

    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    if (!printWindow) {
        alert('Please allow popups for this website to print invoices.');
        return;
    }

    // Create print-specific styles
    const printStyles = `
        <style>
            @media print {
                body {
                    font-family: 'Arial', sans-serif;
                    margin: 0;
                    padding: 15px;
                    font-size: 12px;
                    line-height: 1.4;
                    color: #000;
                }

                .invoice-container {
                    max-width: none !important;
                }

                .company-header {
                    border-bottom: 2px solid #000 !important;
                    margin-bottom: 20px !important;
                    padding-bottom: 15px !important;
                }

                .company-logo {
                    max-height: 60px !important;
                    max-width: 60px !important;
                }

                .company-name {
                    font-size: 18px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                    margin-bottom: 5px !important;
                }

                .company-details {
                    font-size: 10px !important;
                    color: #666 !important;
                }

                .invoice-type h4 {
                    font-size: 14px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                }

                .section-title {
                    font-size: 14px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                    border-bottom: 1px solid #ccc !important;
                    padding-bottom: 5px !important;
                    margin-bottom: 10px !important;
                }

                .quarter-section {
                    border: 1px solid #ddd !important;
                    margin-bottom: 15px !important;
                    page-break-inside: avoid;
                }

                .quarter-title {
                    font-size: 13px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                    margin-bottom: 10px !important;
                }

                .card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                }

                .card.bg-light {
                    background: #f8f9fa !important;
                }

                .card.bg-success {
                    background: #28a745 !important;
                    color: white !important;
                }

                .card.bg-warning {
                    background: #ffc107 !important;
                    color: white !important;
                }

                .table {
                    font-size: 10px !important;
                    margin-bottom: 10px !important;
                }

                .table th, .table td {
                    padding: 4px 6px !important;
                    border: 1px solid #ddd !important;
                }

                .table-borderless td, .table-borderless th {
                    border: none !important;
                    padding: 2px 0 !important;
                }

                .fw-bold {
                    font-weight: bold !important;
                }

                .text-primary {
                    color: #007bff !important;
                }

                .text-success {
                    color: #28a745 !important;
                }

                .text-warning {
                    color: #ffc107 !important;
                }

                .badge {
                    background: #6c757d !important;
                    color: white !important;
                    padding: 2px 6px !important;
                    font-size: 8px !important;
                    border-radius: 3px !important;
                }

                .bg-success .badge {
                    background: #28a745 !important;
                }

                .bg-warning .badge {
                    background: #ffc107 !important;
                }

                .bg-danger .badge {
                    background: #dc3545 !important;
                }

                .summary-footer {
                    background: #f8f9fa !important;
                    border: 1px solid #ddd !important;
                    padding: 15px !important;
                }

                .signature-section {
                    border-top: 1px solid #000 !important;
                    padding-top: 20px !important;
                }

                /* Hide elements not needed for printing */
                .modal, .modal-backdrop, .btn, .btn-close {
                    display: none !important;
                }
            }

            @page {
                margin: 0.5cm;
                size: A4;
            }

            /* Ensure proper page breaks */
            .quarter-section {
                page-break-inside: avoid;
            }
        </style>
    `;

    // Write content to print window
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Fee Invoice Details</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            ${printStyles}
        </head>
        <body>
            <div class="invoice-container">
                ${printContent}
            </div>
        </body>
        </html>
    `);

    printWindow.document.close();

    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.print();
        // Close the print window after printing
        setTimeout(() => {
            printWindow.close();
        }, 1000);
    };
}

// Function to view single invoice details in modal
function viewSingleInvoice(invoiceId) {
    // Show loading state
    Swal.fire({
        title: 'Loading Invoice Details...',
        text: 'Please wait while we fetch the invoice information.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Make AJAX request to get invoice details
    $.ajax({
        url: `/school/fee-invoices/${invoiceId}/details`,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            Swal.close();

            if (response.success && response.invoice) {
                const invoice = response.invoice;
                const student = response.student || window.studentInfo;
                const company = response.company || window.companyInfo;

                // Format dates
                const invoiceDate = new Date(invoice.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                const dueDate = invoice.due_date ? new Date(invoice.due_date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : 'N/A';

                // Calculate totals
                const totalAmount = parseFloat(invoice.total_amount || 0);
                const paidAmount = parseFloat(invoice.paid_amount || 0);
                const outstandingAmount = totalAmount - paidAmount;

                // Determine status badge
                let statusBadge = '';
                let statusClass = '';
                if (outstandingAmount <= 0) {
                    statusBadge = '<span class="badge badge-status-paid">Paid</span>';
                    statusClass = 'bg-success';
                } else if (paidAmount > 0) {
                    statusBadge = '<span class="badge badge-status-partial">Partially Paid</span>';
                    statusClass = 'bg-warning';
                } else {
                    statusBadge = '<span class="badge badge-status-overdue">Unpaid</span>';
                    statusClass = 'bg-danger';
                }

                // Build transactions table
                let transactionsHtml = '';
                if (invoice.payments && invoice.payments.length > 0) {
                    invoice.payments.forEach(payment => {
                        const paymentDate = new Date(payment.date).toLocaleDateString('en-US');
                        const paymentAmount = parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2});

                        transactionsHtml += `
                            <tr>
                                <td>${paymentDate}</td>
                                <td>${payment.reference || 'N/A'}</td>
                                <td>${payment.bank_account ? payment.bank_account.name : 'N/A'}</td>
                                <td class="text-end">TZS ${paymentAmount}</td>
                            </tr>`;
                    });
                } else {
                    transactionsHtml = '<tr><td colspan="4" class="text-center text-muted">No payments recorded</td></tr>';
                }

                // Build invoice items table
                let itemsHtml = '';
                if (invoice.items && invoice.items.length > 0) {
                    invoice.items.forEach(item => {
                        const itemAmount = parseFloat(item.amount).toLocaleString('en-US', {minimumFractionDigits: 2});

                        itemsHtml += `
                            <tr>
                                <td>${item.fee_name || item.description}</td>
                                <td class="text-end">TZS ${itemAmount}</td>
                            </tr>`;
                    });

                    // Add discount row if discount exists
                    if (invoice.discount_amount > 0) {
                        const discountAmount = parseFloat(invoice.discount_amount).toLocaleString('en-US', {minimumFractionDigits: 2});
                        itemsHtml += `
                            <tr class="table-light">
                                <td><strong>Discount Applied</strong></td>
                                <td class="text-end text-success"><strong>-TZS ${discountAmount}</strong></td>
                            </tr>`;
                    }
                } else {
                    itemsHtml = '<tr><td colspan="2" class="text-center text-muted">No fee items found</td></tr>';
                }

                // Create modal content
                let modalContent = `
                    <div class="modal fade" id="singleInvoiceModal" tabindex="-1" aria-labelledby="singleInvoiceModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header ${statusClass} text-white">
                                    <h5 class="modal-title" id="singleInvoiceModalLabel">
                                        <i class="bx bx-file me-2"></i>Invoice Details - ${invoice.invoice_number}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="invoice-container" id="single-invoice-print-content">
                                        <!-- Company Header -->
                                        <div class="company-header bg-light border-bottom p-3 mb-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-2 text-center">
                                                    <img src="${company.logo}" alt="Company Logo" class="company-logo img-fluid" style="max-height: 80px; max-width: 80px;">
                                                </div>
                                                <div class="col-md-8 text-center">
                                                    <h3 class="company-name mb-1 text-primary">${company.name}</h3>
                                                    <p class="company-details mb-0 text-muted small">
                                                        ${company.address ? company.address + ' | ' : ''}
                                                        ${company.phone ? 'Phone: ' + company.phone + ' | ' : ''}
                                                        ${company.email ? 'Email: ' + company.email : ''}
                                                    </p>
                                                </div>
                                                <div class="col-md-2 text-center">
                                                    <div class="invoice-type">
                                                        <h4 class="mb-0 text-primary">FEE INVOICE</h4>
                                                        <small class="text-muted">${invoice.invoice_number}</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <div class="col-12">
                                                <div class="card compact-info-card">
                                                    <div class="card-header bg-light py-2">
                                                        <h6 class="mb-0 d-inline"><i class="bx bx-user me-2"></i>Student Information</h6>
                                                        <span class="float-end text-muted small">${student.name} | Stream: ${student.stream} | Class: ${student.class_name || student.class} | Year: ${student.academic_year}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <div class="col-12">
                                                <div class="card compact-info-card">
                                                    <div class="card-header bg-light py-2">
                                                        <h6 class="mb-0 d-inline"><i class="bx bx-info-circle me-2"></i>Invoice Information</h6>
                                                        <span class="float-end text-muted small">Invoice: ${invoice.invoice_number} | Date: ${invoiceDate} | Due: ${dueDate} | Period: Q${invoice.period} | Status: ${statusBadge}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fee Items -->
                                        <div class="card mb-4">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Fee Breakdown</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Description</th>
                                                                <th class="text-end">Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            ${itemsHtml}
                                                        </tbody>
                                                        <tfoot class="table-light">
                                                            <tr class="fw-bold">
                                                                <td>Total Amount</td>
                                                                <td class="text-end">TZS ${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Payment Transactions -->
                                        <div class="card mb-4">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="bx bx-credit-card me-2"></i>Payment Transactions</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Reference</th>
                                                                <th>Bank Account</th>
                                                                <th class="text-end">Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            ${transactionsHtml}
                                                        </tbody>
                                                        <tfoot class="table-light">
                                                            <tr class="fw-bold">
                                                                <td colspan="3">Total Paid</td>
                                                                <td class="text-end text-success">TZS ${paidAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Balance Summary -->
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Balance Summary</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Description</th>
                                                                <th class="text-end">Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td>Subtotal</td>
                                                                <td class="text-end">TZS ${(invoice.subtotal || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            </tr>
                                                            ${invoice.discount_amount > 0 ? `
                                                            <tr>
                                                                <td>Discount</td>
                                                                <td class="text-end text-success">-TZS ${(invoice.discount_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            </tr>` : ''}
                                                            <tr>
                                                                <td>Transport Fare</td>
                                                                <td class="text-end">TZS ${(invoice.transport_fare || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            </tr>
                                                            <tr class="border-top">
                                                                <td><strong>Total Amount</strong></td>
                                                                <td class="text-end fw-bold text-primary">TZS ${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Amount Paid</strong></td>
                                                                <td class="text-end fw-bold text-success">TZS ${paidAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                            </tr>
                                                            <tr>
                                                                <td><strong>Outstanding Balance</strong></td>
                                                                <td class="text-end fw-bold ${outstandingAmount > 0 ? 'text-danger' : 'text-success'}">
                                                                    TZS ${Math.abs(outstandingAmount).toLocaleString('en-US', {minimumFractionDigits: 2})}
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        <i class="bx bx-x me-1"></i> Close
                                    </button>
                                    <button type="button" class="btn btn-primary" onclick="printSingleInvoice()">
                                        <i class="bx bx-printer me-1"></i> Print Invoice
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>`;

                // Remove existing modal if present
                const existingModal = document.getElementById('singleInvoiceModal');
                if (existingModal) {
                    existingModal.remove();
                }

                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', modalContent);

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('singleInvoiceModal'));
                modal.show();

            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message || 'Failed to load invoice details',
                    icon: 'error'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.close();
            console.error('Load invoice details error:', xhr, status, error);

            let errorMessage = 'Failed to load invoice details. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error'
            });
        }
    });
}

// Function to print single invoice
function printSingleInvoice() {
    const printContent = document.getElementById('single-invoice-print-content').innerHTML;

    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=800,height=600');
    if (!printWindow) {
        alert('Please allow popups for this website to print invoices.');
        return;
    }

    // Create print-specific styles
    const printStyles = `
        <style>
            @media print {
                body {
                    font-family: 'Arial', sans-serif;
                    margin: 0;
                    padding: 15px;
                    font-size: 12px;
                    line-height: 1.4;
                    color: #000;
                }

                .invoice-container {
                    max-width: none !important;
                }

                .company-header {
                    border-bottom: 2px solid #000 !important;
                    margin-bottom: 20px !important;
                    padding-bottom: 15px !important;
                }

                .company-logo {
                    max-height: 60px !important;
                    max-width: 60px !important;
                }

                .company-name {
                    font-size: 18px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                    margin-bottom: 5px !important;
                }

                .company-details {
                    font-size: 10px !important;
                    color: #666 !important;
                }

                .invoice-type h4 {
                    font-size: 14px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                }

                .card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                    margin-bottom: 6px !important;
                }

                .card-header {
                    background: #f8f9fa !important;
                    border-bottom: 1px solid #ddd !important;
                    padding: 3px 6px !important;
                }

                .card-header h6 {
                    font-size: 10px !important;
                    margin-bottom: 0 !important;
                    font-weight: 600 !important;
                }

                .card-header .float-end {
                    font-size: 8px !important;
                    margin-top: 1px !important;
                }

                .table {
                    font-size: 10px !important;
                    margin-bottom: 10px !important;
                }

                .table th, .table td {
                    padding: 4px 6px !important;
                    border: 1px solid #ddd !important;
                }

                .table-borderless td, .table-borderless th {
                    border: none !important;
                    padding: 2px 0 !important;
                }

                .fw-bold {
                    font-weight: bold !important;
                }

                .text-primary {
                    color: #007bff !important;
                }

                .text-success {
                    color: #28a745 !important;
                }

                .text-danger {
                    color: #dc3545 !important;
                }

                .badge {
                    background: #6c757d !important;
                    color: white !important;
                    padding: 2px 6px !important;
                    font-size: 8px !important;
                    border-radius: 3px !important;
                }

                .bg-success .badge {
                    background: #28a745 !important;
                }

                .bg-warning .badge {
                    background: #ffc107 !important;
                }

                .bg-danger .badge {
                    background: #dc3545 !important;
                }

                .border {
                    border: 1px solid #ddd !important;
                }

                .rounded {
                    border-radius: 4px !important;
                }

                /* Hide elements not needed for printing */
                .modal, .modal-backdrop, .btn, .btn-close {
                    display: none !important;
                }
            }

            @page {
                margin: 0.5cm;
                size: A4;
            }

            /* Ensure proper page breaks */
            .card {
                page-break-inside: avoid;
            }
        </style>
    `;

    // Write content to print window
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Fee Invoice</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            ${printStyles}
        </head>
        <body>
            <div class="invoice-container">
                ${printContent}
            </div>
        </body>
        </html>
    `);

    printWindow.document.close();

    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.print();
        // Close the print window after printing
        setTimeout(() => {
            printWindow.close();
        }, 1000);
    };
}

// Function to delete invoice
function deleteInvoice(invoiceId, invoiceNumber) {
    Swal.fire({
        title: 'Delete Invoice?',
        html: `
            <div class="text-start">
                <p class="mb-3">Are you sure you want to delete invoice <strong>${invoiceNumber}</strong>?</p>
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This action will permanently delete the invoice and cannot be undone.
                    <br><br>
                    <strong>Note:</strong> Only invoices with no payment amounts can be deleted.
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Invoice',
        cancelButtonText: '<i class="bx bx-x me-1"></i> Cancel',
        customClass: {
            popup: 'swal-wide'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Deleting Invoice...',
                text: 'Please wait while we delete the invoice.',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Make AJAX request to delete invoice
            $.ajax({
                url: `/school/fee-invoices/${invoiceId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Invoice has been deleted successfully.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload the page to reflect changes
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Failed to delete invoice',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete invoice error:', xhr, status, error);
                    let errorMessage = 'Failed to delete invoice. Please try again.';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        title: 'Error!',
                        text: errorMessage,
                        icon: 'error'
                    });
                }
            });
        }
    });
}
// Function to view receipts for a quarter
function viewReceipts(quarter) {
    // Get the quarterly fees data from the global variable
    const quarterlyFees = window.quarterlyFees || {};
    const quarterData = quarterlyFees[quarter];

    if (!quarterData || !quarterData.payments || quarterData.payments.length === 0) {
        alert('No receipts found for ' + quarter);
        return;
    }

    // If there's only one payment, proceed directly to view
    if (quarterData.payments.length === 1) {
        showSingleReceiptModal(quarter, quarterData.payments[0]);
        return;
    }

    // If there are multiple payments, show selection modal
    showReceiptSelectionModal(quarter, quarterData.payments);
}

// Function to show receipt selection modal for multiple receipts
function showReceiptSelectionModal(quarter, payments) {
    // Store payments globally for access in click handlers
    window.tempReceipts = payments;

    let receiptOptions = '';
    payments.forEach((payment, index) => {
        const paymentDate = payment.date ? new Date(payment.date).toLocaleDateString('en-US') : 'N/A';
        const amount = parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2});
        const reference = payment.reference || 'N/A';

        receiptOptions += `
            <div class="receipt-option mb-3 p-3 border rounded" onclick="selectReceiptToView('${quarter}', ${index})" style="cursor: pointer; transition: background-color 0.2s;">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <strong>Receipt #${index + 1}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Date:</small><br>
                        ${paymentDate}
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Amount:</small><br>
                        <strong>TZS ${amount}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Reference:</small><br>
                        ${reference}
                    </div>
                </div>
            </div>`;
    });

    let modalContent = `
        <div class="modal fade" id="receiptSelectionModal" tabindex="-1" aria-labelledby="receiptSelectionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="receiptSelectionModalLabel">
                            <i class="bx bx-list-ul me-2"></i>Select Receipt to View for ${quarter}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Multiple Receipts Found:</strong> Please select which receipt you want to view from the list below.
                        </div>

                        <!-- Student Information -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-user me-2"></i>Student Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Name:</strong> ${window.studentInfo.name}<br>
                                        <strong>Admission Number:</strong> ${window.studentInfo.admission_number}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Class:</strong> ${window.studentInfo.class}<br>
                                        <strong>Academic Year:</strong> ${window.studentInfo.academic_year}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Receipt Options -->
                        <div class="receipt-list">
                            <h6 class="mb-3">Available Receipts:</h6>
                            ${receiptOptions}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remove existing modal if present
    const existingModal = document.getElementById('receiptSelectionModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalContent);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('receiptSelectionModal'));
    modal.show();
}

// Function to edit receipts for a quarter
function editReceipts(quarter) {
    // Get the quarterly fees data from the global variable
    const quarterlyFees = window.quarterlyFees || {};
    const quarterData = quarterlyFees[quarter];

    if (!quarterData || !quarterData.payments || quarterData.payments.length === 0) {
        alert('No receipts found for ' + quarter);
        return;
    }

    // If there's only one payment, proceed directly to edit
    if (quarterData.payments.length === 1) {
        showEditPaymentModal(quarter, quarterData.payments[0]);
        return;
    }

    // If there are multiple payments, show selection modal
    showPaymentSelectionModalForEdit(quarter, quarterData.payments);
}

// Function to show payment selection modal for editing
function showPaymentSelectionModalForEdit(quarter, payments) {
    // Store payments globally for access in click handlers
    window.tempPaymentsForEdit = payments;

    let paymentOptions = '';
    payments.forEach((payment, index) => {
        const paymentDate = payment.date ? new Date(payment.date).toLocaleDateString('en-US') : 'N/A';
        const amount = parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2});
        const reference = payment.reference || 'N/A';

        paymentOptions += `
            <div class="payment-option mb-3 p-3 border rounded" onclick="selectPaymentToEdit('${quarter}', ${index})" style="cursor: pointer; transition: background-color 0.2s;">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <strong>Payment #${index + 1}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Date:</small><br>
                        ${paymentDate}
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Amount:</small><br>
                        <strong>TZS ${amount}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Reference:</small><br>
                        ${reference}
                    </div>
                </div>
            </div>`;
    });

    let modalContent = `
        <div class="modal fade" id="paymentSelectionModalForEdit" tabindex="-1" aria-labelledby="paymentSelectionModalForEditLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="paymentSelectionModalForEditLabel">
                            <i class="bx bx-edit me-2"></i>Select Payment to Edit for ${quarter}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Multiple Payments Found:</strong> Please select which payment you want to edit from the list below.
                        </div>

                        <!-- Student Information -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-user me-2"></i>Student Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Name:</strong> ${window.studentInfo.name}<br>
                                        <strong>Admission Number:</strong> ${window.studentInfo.admission_number}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Class:</strong> ${window.studentInfo.class}<br>
                                        <strong>Academic Year:</strong> ${window.studentInfo.academic_year}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Options -->
                        <div class="payment-list">
                            <h6 class="mb-3">Available Payments:</h6>
                            ${paymentOptions}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remove existing modal if present
    const existingModal = document.getElementById('paymentSelectionModalForEdit');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalContent);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('paymentSelectionModalForEdit'));
    modal.show();
}

// Function to handle payment selection for editing
function selectPaymentToEdit(quarter, paymentIndex) {
    const payment = window.tempPaymentsForEdit[paymentIndex];
    showEditPaymentModal(quarter, payment);
}

// Function to show edit payment modal
function showEditPaymentModal(quarter, payment) {
    // Get the quarterly fees data from the global variable
    const quarterlyFees = window.quarterlyFees || {};
    const quarterData = quarterlyFees[quarter];

    const paymentDate = payment.date ? new Date(payment.date).toISOString().split('T')[0] : '';
    const amount = parseFloat(payment.amount) || 0;
    const reference = payment.reference || '';
    const notes = payment.description || '';

    // Get available bank accounts for dropdown
    const bankAccounts = window.bankAccounts || [];

    let bankAccountOptions = '<option value="">Select Bank Account (Optional)</option>';
    bankAccounts.forEach(account => {
        const selected = payment.bank_account_id == account.id ? 'selected' : '';
        bankAccountOptions += `<option value="${account.id}" ${selected}>${account.name} (${account.account_number})</option>`;
    });

    let modalContent = `
        <div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="editPaymentModalLabel">
                            <i class="bx bx-edit me-2"></i>Edit Payment for ${quarter}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Student Information -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-user me-2"></i>Student Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Name:</strong> ${window.studentInfo.name}<br>
                                        <strong>Admission Number:</strong> ${window.studentInfo.admission_number}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Class:</strong> ${window.studentInfo.class}<br>
                                        <strong>Academic Year:</strong> ${window.studentInfo.academic_year}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <form id="editPaymentForm">
                            <input type="hidden" name="payment_id" value="${payment.id}">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_payment_date" class="form-label">
                                            <i class="bx bx-calendar me-1"></i>Payment Date <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control" id="edit_payment_date" name="date" value="${paymentDate}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_payment_amount" class="form-label">
                                            <i class="bx bx-money me-1"></i>Amount (TZS) <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" class="form-control" id="edit_payment_amount" name="amount" value="${amount}" step="0.01" min="0.01" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_payment_reference" class="form-label">
                                            <i class="bx bx-hash me-1"></i>Reference Number
                                        </label>
                                        <input type="text" class="form-control" id="edit_payment_reference" name="reference_number" value="${reference}" placeholder="Optional reference number">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="edit_payment_bank_account" class="form-label">
                                            <i class="bx bx-credit-card me-1"></i>Bank Account
                                        </label>
                                        <select class="form-select" id="edit_payment_bank_account" name="bank_account_id">
                                            ${bankAccountOptions}
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_payment_notes" class="form-label">
                                    <i class="bx bx-note me-1"></i>Notes
                                </label>
                                <textarea class="form-control" id="edit_payment_notes" name="notes" rows="3" placeholder="Optional notes about this payment">${notes}</textarea>
                            </div>

                            <!-- Payment Summary -->
                            <div class="card border-info mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Payment Summary</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Quarter:</strong> ${quarter}<br>
                                            <strong>Original Amount:</strong> TZS ${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Total Quarter Amount:</strong> TZS ${parseFloat(quarterData.total).toLocaleString('en-US', {minimumFractionDigits: 2})}<br>
                                            <strong>Outstanding:</strong> TZS ${parseFloat(quarterData.outstanding).toLocaleString('en-US', {minimumFractionDigits: 2})}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-warning" onclick="updatePayment()">
                            <i class="bx bx-save me-1"></i> Update Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remove existing modal if present
    const existingModal = document.getElementById('editPaymentModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalContent);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
    modal.show();
}

// Function to update payment
function updatePayment() {
    const form = document.getElementById('editPaymentForm');
    const formData = new FormData(form);

    // Validate required fields
    const amount = formData.get('amount');
    const date = formData.get('date');

    if (!amount || amount <= 0) {
        Swal.fire({
            title: 'Validation Error!',
            text: 'Please enter a valid payment amount.',
            icon: 'error'
        });
        return;
    }

    if (!date) {
        Swal.fire({
            title: 'Validation Error!',
            text: 'Please select a payment date.',
            icon: 'error'
        });
        return;
    }

    // Show loading
    Swal.fire({
        title: 'Updating Payment...',
        text: 'Please wait while we update the payment.',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Prepare data for AJAX
    const paymentData = {
        amount: parseFloat(amount),
        date: date,
        reference_number: formData.get('reference_number') || '',
        bank_account_id: formData.get('bank_account_id') || '',
        notes: formData.get('notes') || ''
    };

    // Make AJAX request
    fetch(`/school/fee-invoices/payment/${formData.get('payment_id')}/update`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(paymentData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Close modal and reload page
                const modal = bootstrap.Modal.getInstance(document.getElementById('editPaymentModal'));
                if (modal) {
                    modal.hide();
                }
                location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Error updating payment:', error);
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred while updating the payment. Please try again.',
            icon: 'error'
        });
    });
}

// Function to handle receipt selection
function selectReceiptToView(quarter, receiptIndex) {
    const receipt = window.tempReceipts[receiptIndex];
    showSingleReceiptModal(quarter, receipt);
}

// Function to show single receipt modal
function showSingleReceiptModal(quarter, payment) {
    // Get the quarterly fees data from the global variable
    const quarterlyFees = window.quarterlyFees || {};
    const quarterData = quarterlyFees[quarter];

    const paymentDate = new Date(payment.date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    // Create single receipt modal content
    let modalContent = `
        <div class="modal fade" id="singleReceiptModal" tabindex="-1" aria-labelledby="singleReceiptModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="singleReceiptModalLabel">
                            <i class="bx bx-receipt me-2"></i>Payment Receipt for ${quarter}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="receipt-container" id="single-receipt-print-content">
                            <!-- Receipt Card -->
                            <div class="receipt-card border rounded shadow-sm">
                                <!-- Company Header -->
                                <div class="receipt-header bg-light border-bottom p-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-2 text-center">
                                            <img src="${window.companyInfo.logo}" alt="Company Logo" class="company-logo img-fluid" style="max-height: 80px; max-width: 80px;">
                                        </div>
                                        <div class="col-md-8 text-center">
                                            <h3 class="company-name mb-1 text-primary">${window.companyInfo.name}</h3>
                                            <p class="company-details mb-0 text-muted small">
                                                ${window.companyInfo.address ? window.companyInfo.address + ' | ' : ''}
                                                ${window.companyInfo.phone ? 'Phone: ' + window.companyInfo.phone + ' | ' : ''}
                                                ${window.companyInfo.email ? 'Email: ' + window.companyInfo.email : ''}
                                            </p>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <div class="receipt-type">
                                                <h4 class="mb-0 text-success">PAYMENT RECEIPT</h4>
                                                <small class="text-muted">Receipt #${payment.hash_id || payment.id}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Receipt Body -->
                                <div class="receipt-body p-4">
                                    <div class="row">
                                        <!-- Student & Payment Info -->
                                        <div class="col-md-6">
                                            <div class="info-section mb-3">
                                                <h6 class="section-title text-primary border-bottom pb-1 mb-2">
                                                    <i class="bx bx-user me-1"></i>Student Information
                                                </h6>
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <td class="fw-bold" style="width: 45%;">Name:</td>
                                                        <td>${window.studentInfo.name}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Admission No:</td>
                                                        <td>${window.studentInfo.admission_number}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Class:</td>
                                                        <td>${window.studentInfo.class}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Academic Year:</td>
                                                        <td>${window.studentInfo.academic_year}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="info-section mb-3">
                                                <h6 class="section-title text-primary border-bottom pb-1 mb-2">
                                                    <i class="bx bx-credit-card me-1"></i>Payment Details
                                                </h6>
                                                <table class="table table-sm table-borderless">
                                                    <tr>
                                                        <td class="fw-bold" style="width: 45%;">Date:</td>
                                                        <td>${paymentDate}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Reference:</td>
                                                        <td>${payment.reference || 'N/A'}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Bank Account:</td>
                                                        <td>${payment.bank_account ? payment.bank_account.name : 'N/A'}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Amount Paid:</td>
                                                        <td class="fw-bold text-success fs-5">TZS ${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="fw-bold">Outstanding Balance:</td>
                                                        <td class="fw-bold text-warning fs-5">TZS ${parseFloat(quarterData.outstanding).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Invoice Details -->
                                    <div class="invoice-section mt-4">
                                        <h6 class="section-title text-primary border-bottom pb-1 mb-3">
                                            <i class="bx bx-file me-1"></i>Invoice Information
                                        </h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Invoice #</th>
                                                        <th>Period</th>
                                                        <th>Fee Group</th>
                                                        <th>Amount</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;

        // Add invoice details if available
        if (quarterData.invoices && quarterData.invoices.length > 0) {
            quarterData.invoices.forEach(invoice => {
                const invoicePaid = invoice.paid_amount || 0;
                const invoiceOutstanding = invoice.total_amount - invoicePaid;
                let statusBadge = '<span class="badge badge-status-paid">Paid</span>';

                if (invoiceOutstanding > 0 && invoicePaid > 0) {
                    statusBadge = '<span class="badge badge-status-partial">Partially Paid</span>';
                } else if (invoiceOutstanding > 0) {
                    statusBadge = '<span class="badge badge-status-overdue">Unpaid</span>';
                }

                modalContent += `
                                                    <tr>
                                                        <td class="fw-bold">${invoice.invoice_number}</td>
                                                        <td>Q${invoice.period}</td>
                                                        <td>${invoice.fee_group_name || 'N/A'}</td>
                                                        <td>TZS ${parseFloat(invoice.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                                        <td>${statusBadge}</td>
                                                    </tr>`;
            });
        } else {
            modalContent += `
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted">Invoice details not available</td>
                                                    </tr>`;
        }

        modalContent += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Receipt Footer -->
                                    <div class="receipt-footer mt-4 pt-3 border-top">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="payment-confirmation">
                                                    <div class="alert alert-success py-2 mb-0">
                                                        <i class="bx bx-check-circle me-2"></i>
                                                        <strong>Payment Received & Recorded</strong>
                                                        <br>
                                                        <small class="text-muted">This payment has been successfully processed and recorded in our system.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <div class="signature-section">
                                                    <div class="border-top pt-2 mt-3">
                                                        <small class="text-muted">Authorized Signature</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Close
                        </button>
                        <button type="button" class="btn btn-primary" onclick="printSingleReceipt()">
                            <i class="bx bx-printer me-1"></i> Print Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remove existing modal if present
    const existingModal = document.getElementById('singleReceiptModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalContent);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('singleReceiptModal'));
    modal.show();
}

// Function to print single receipt
function printSingleReceipt() {
    const printContent = document.getElementById('single-receipt-print-content').innerHTML;

    // Create a new window for printing
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    if (!printWindow) {
        alert('Please allow popups for this website to print receipts.');
        return;
    }

    // Create print-specific styles
    const printStyles = `
        <style>
            @media print {
                body {
                    font-family: 'Courier New', monospace;
                    margin: 0;
                    padding: 5px;
                    font-size: 10px;
                    line-height: 1.2;
                    color: #000;
                }

                .receipt-card {
                    page-break-inside: avoid;
                    margin-bottom: 10px;
                    border: 1px solid #000 !important;
                    box-shadow: none !important;
                    width: 80mm !important;
                    max-width: 80mm !important;
                }

                .receipt-header {
                    background: #f8f9fa !important;
                    border-bottom: 1px solid #000 !important;
                    padding: 8px !important;
                }

                .company-logo {
                    max-height: 30px !important;
                    max-width: 30px !important;
                }

                .company-name {
                    font-size: 14px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                    margin-bottom: 2px !important;
                }

                .company-details {
                    font-size: 8px !important;
                    color: #666 !important;
                }

                .receipt-type h4 {
                    font-size: 12px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                }

                .receipt-body {
                    padding: 10px !important;
                }

                .section-title {
                    font-size: 11px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                    border-bottom: 1px solid #ccc !important;
                    padding-bottom: 2px !important;
                    margin-bottom: 5px !important;
                }

                .table {
                    font-size: 9px !important;
                    margin-bottom: 8px !important;
                }

                .table th, .table td {
                    padding: 2px 3px !important;
                    border: 1px solid #ddd !important;
                }

                .table-borderless td, .table-borderless th {
                    border: none !important;
                    padding: 1px 0 !important;
                }

                .fw-bold {
                    font-weight: bold !important;
                }

                .text-success {
                    color: #000 !important;
                    font-weight: bold !important;
                }

                .fs-5 {
                    font-size: 12px !important;
                }

                .badge {
                    background: #28a745 !important;
                    color: white !important;
                    padding: 1px 4px !important;
                    font-size: 7px !important;
                    border-radius: 2px !important;
                }

                .alert-success {
                    background: #d4edda !important;
                    border: 1px solid #c3e6cb !important;
                    color: #155724 !important;
                    padding: 5px !important;
                }

                .border-top {
                    border-top: 1px solid #000 !important;
                }

                .text-muted {
                    color: #666 !important;
                }

                .receipt-footer .row {
                    display: block !important;
                }

                .receipt-footer .col-md-8,
                .receipt-footer .col-md-4 {
                    display: block !important;
                    width: 100% !important;
                    text-align: center !important;
                    margin-bottom: 10px !important;
                }

                /* Hide elements not needed for printing */
                .modal, .modal-backdrop, .btn, .btn-close {
                    display: none !important;
                }
            }

            @page {
                margin: 0.2cm;
                size: 80mm auto;
            }

            /* Ensure proper page breaks */
            .receipt-card {
                page-break-inside: avoid;
            }
        </style>
    `;

    // Write content to print window
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Payment Receipt</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            ${printStyles}
        </head>
        <body>
            <div class="receipt-container">
                ${printContent}
            </div>
        </body>
        </html>
    `);

    printWindow.document.close();

    // Wait for content to load then print
    printWindow.onload = function() {
        printWindow.print();
        // Close the print window after printing
        setTimeout(() => {
            printWindow.close();
        }, 1000);
    };
}

// Function to print receipts
function printReceipts() {
    const printContent = document.querySelector('.receipt-container').innerHTML;
    const originalContent = document.body.innerHTML;

    // Create print-specific styles
    const printStyles = `
        <style>
            @media print {
                body {
                    font-family: 'Courier New', monospace;
                    margin: 0;
                    padding: 5px;
                    font-size: 10px;
                    line-height: 1.2;
                    color: #000;
                }

                .receipt-card {
                    page-break-inside: avoid;
                    margin-bottom: 10px;
                    border: 1px solid #000 !important;
                    box-shadow: none !important;
                    width: 80mm !important;
                    max-width: 80mm !important;
                }

                .receipt-header {
                    background: #f8f9fa !important;
                    border-bottom: 1px solid #000 !important;
                    padding: 8px !important;
                }

                .company-logo {
                    max-height: 30px !important;
                    max-width: 30px !important;
                }

                .company-name {
                    font-size: 14px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                    margin-bottom: 2px !important;
                }

                .company-details {
                    font-size: 8px !important;
                    color: #666 !important;
                }

                .receipt-type h4 {
                    font-size: 12px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                }

                .receipt-body {
                    padding: 10px !important;
                }

                .section-title {
                    font-size: 11px !important;
                    font-weight: bold !important;
                    color: #000 !important;
                    border-bottom: 1px solid #ccc !important;
                    padding-bottom: 2px !important;
                    margin-bottom: 5px !important;
                }

                .table {
                    font-size: 9px !important;
                    margin-bottom: 8px !important;
                }

                .table th, .table td {
                    padding: 2px 3px !important;
                    border: 1px solid #ddd !important;
                }

                .table-borderless td, .table-borderless th {
                    border: none !important;
                    padding: 1px 0 !important;
                }

                .fw-bold {
                    font-weight: bold !important;
                }

                .text-success {
                    color: #000 !important;
                    font-weight: bold !important;
                }

                .fs-5 {
                    font-size: 12px !important;
                }

                .badge {
                    background: #28a745 !important;
                    color: white !important;
                    padding: 1px 4px !important;
                    font-size: 7px !important;
                    border-radius: 2px !important;
                }

                .alert-success {
                    background: #d4edda !important;
                    border: 1px solid #c3e6cb !important;
                    color: #155724 !important;
                    padding: 5px !important;
                }

                .border-top {
                    border-top: 1px solid #000 !important;
                }

                .text-muted {
                    color: #666 !important;
                }

                .receipt-footer .row {
                    display: block !important;
                }

                .receipt-footer .col-md-8,
                .receipt-footer .col-md-4 {
                    display: block !important;
                    width: 100% !important;
                    text-align: center !important;
                    margin-bottom: 10px !important;
                }

                /* Hide elements not needed for printing */
                .modal, .modal-backdrop, .btn, .btn-close {
                    display: none !important;
                }
            }

            @page {
                margin: 0.2cm;
                size: 80mm auto;
            }

            /* Ensure proper page breaks */
            .receipt-card {
                page-break-inside: avoid;
            }
        </style>
    `;

    document.body.innerHTML = printStyles + '<div class="receipt-container">' + printContent + '</div>';
    window.print();
    document.body.innerHTML = originalContent;
}

// Function to delete receipts for a quarter
function deleteReceipts(quarter) {
    // Get the quarterly fees data from the global variable
    const quarterlyFees = window.quarterlyFees || {};
    const quarterData = quarterlyFees[quarter];

    if (!quarterData || !quarterData.payments || quarterData.payments.length === 0) {
        alert('No receipts found for ' + quarter);
        return;
    }

    // If there's only one payment, proceed directly to delete
    if (quarterData.payments.length === 1) {
        showDeletePaymentModal(quarter, quarterData.payments[0]);
        return;
    }

    // If there are multiple payments, show selection modal
    showPaymentSelectionModalForDelete(quarter, quarterData.payments);
}

// Function to show payment selection modal for deletion
function showPaymentSelectionModalForDelete(quarter, payments) {
    // Store payments globally for access in click handlers
    window.tempPayments = payments;

    let paymentOptions = '';
    payments.forEach((payment, index) => {
        const paymentDate = payment.date ? new Date(payment.date).toLocaleDateString('en-US') : 'N/A';
        const amount = parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2});
        const reference = payment.reference || 'N/A';

        paymentOptions += `
            <div class="payment-option mb-3 p-3 border rounded" onclick="selectPaymentToDelete('${quarter}', ${index})" style="cursor: pointer; transition: background-color 0.2s;">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <strong>Payment #${index + 1}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Date:</small><br>
                        ${paymentDate}
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Amount:</small><br>
                        <strong>TZS ${amount}</strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Reference:</small><br>
                        ${reference}
                    </div>
                </div>
            </div>`;
    });

    let modalContent = `
        <div class="modal fade" id="paymentSelectionModal" tabindex="-1" aria-labelledby="paymentSelectionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="paymentSelectionModalLabel">
                            <i class="bx bx-list-ul me-2"></i>Select Payment to Delete for ${quarter}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bx bx-exclamation-triangle me-2"></i>
                            <strong>Multiple Payments Found:</strong> Please select which payment you want to delete from the list below.
                        </div>

                        <!-- Student Information -->
                        <div class="card bg-light mb-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bx bx-user me-2"></i>Student Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Name:</strong> ${window.studentInfo.name}<br>
                                        <strong>Admission Number:</strong> ${window.studentInfo.admission_number}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Class:</strong> ${window.studentInfo.class}<br>
                                        <strong>Academic Year:</strong> ${window.studentInfo.academic_year}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Options -->
                        <div class="payment-list">
                            <h6 class="mb-3">Available Payments:</h6>
                            ${paymentOptions}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>`;

    // Remove existing modal if present
    const existingModal = document.getElementById('paymentSelectionModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalContent);

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('paymentSelectionModal'));
    modal.show();
}

// Function to handle payment selection for deletion
function selectPaymentToDelete(quarter, paymentIndex) {
    const payment = window.tempPayments[paymentIndex];
    showDeletePaymentModal(quarter, payment);
}

// Function to show delete payment modal for a specific payment
function showDeletePaymentModal(quarter, payment) {
    // Close selection modal if open
    const selectionModal = bootstrap.Modal.getInstance(document.getElementById('paymentSelectionModal'));
    if (selectionModal) {
        selectionModal.hide();
    }

    // Show SweetAlert confirmation dialog
    Swal.fire({
        title: 'Delete Payment Receipt?',
        html: `
            <div class="text-start">
                <p class="mb-3">Are you sure you want to delete this payment receipt?</p>
                <div class="alert alert-warning">
                    <strong>Payment Details:</strong><br>
                    • Amount: <strong>TZS ${parseFloat(payment.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong><br>
                    • Date: <strong>${payment.date ? new Date(payment.date).toLocaleDateString('en-US') : 'N/A'}</strong><br>
                    • Reference: <strong>${payment.reference || 'N/A'}</strong>
                </div>
                <div class="alert alert-danger">
                    <strong>This action will:</strong><br>
                    1. Delete the payment record<br>
                    2. Reverse the associated GL transactions<br>
                    3. Update the invoice status back to unpaid<br>
                    4. Log the deletion for audit purposes<br>
                    <strong>This action cannot be undone!</strong>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete Receipt',
        cancelButtonText: '<i class="bx bx-x me-1"></i> Cancel',
        customClass: {
            popup: 'swal-wide'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            const originalButton = event.target;
            const originalText = originalButton.innerHTML;
            originalButton.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Deleting...';
            originalButton.disabled = true;

            // Make AJAX request to delete payment
            $.ajax({
                url: `/school/fee-invoices/payment/${payment.id}/delete`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Payment receipt has been deleted successfully.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload the page to reflect changes
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message || 'Failed to delete payment receipt',
                            icon: 'error'
                        });
                        // Restore button
                        originalButton.innerHTML = originalText;
                        originalButton.disabled = false;
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete payment error:', xhr, status, error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete payment receipt. Please try again.',
                        icon: 'error'
                    });
                    // Restore button
                    originalButton.innerHTML = originalText;
                    originalButton.disabled = false;
                }
            });
        }
    });
}

// Function to toggle discount form visibility
function toggleDiscountForm() {
    const displayDiv = document.getElementById('discount-display');
    const formDiv = document.getElementById('discount-form');

    if (formDiv.style.display === 'none') {
        displayDiv.style.display = 'none';
        formDiv.style.display = 'block';
    } else {
        formDiv.style.display = 'none';
        displayDiv.style.display = 'block';
    }
}

// Function to update discount unit display based on type selection
document.getElementById('discount_type').addEventListener('change', function() {
    const unitSpan = document.getElementById('discount-unit');
    const valueInput = document.getElementById('discount_value');

    if (this.value === 'percentage') {
        unitSpan.textContent = '%';
        valueInput.max = 100;
        valueInput.placeholder = '0.00';
    } else {
        unitSpan.textContent = 'TZS';
        valueInput.removeAttribute('max');
        valueInput.placeholder = '0.00';
    }
});

// Handle discount form submission
document.getElementById('discountForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);
    const saveBtn = document.getElementById('saveDiscountBtn');
    const originalText = saveBtn.innerHTML;

    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...';

    // Make AJAX request
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            Swal.fire({
                title: 'Success!',
                text: 'Student discount updated successfully.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Reload the page to show updated discount
                location.reload();
            });
        } else {
            // Show validation errors
            let errorMessage = 'Please check the following errors:\n';
            if (data.errors) {
                for (let field in data.errors) {
                    errorMessage += '• ' + data.errors[field].join(', ') + '\n';
                }
            } else if (data.message) {
                errorMessage = data.message;
            }

            Swal.fire({
                title: 'Validation Error!',
                text: errorMessage,
                icon: 'error'
            });
        }
    })
    .catch(error => {
        console.error('Error updating discount:', error);
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred while updating the discount. Please try again.',
            icon: 'error'
        });
    })
    .finally(() => {
        // Restore button
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
});
</script>
@endsection