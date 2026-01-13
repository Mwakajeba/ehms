@extends('layouts.main')

@section('title', 'Bill Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Cashier', 'url' => route('hospital.cashier.index'), 'icon' => 'bx bx-money'],
                ['label' => 'Bill Details', 'url' => '#', 'icon' => 'bx bx-receipt']
            ]" />
            <h6 class="mb-0 text-uppercase">BILL DETAILS</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">
                <!-- Bill Information -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-receipt me-2"></i>Bill #{{ $bill->bill_number }}
                            </h5>
                            <div>
                                @if($bill->payment_status === 'paid' && $bill->clearance_status === 'pending')
                                    <form action="{{ route('hospital.cashier.clear-bill', $bill->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bx bx-check me-1"></i>Clear Bill
                                        </button>
                                    </form>
                                @endif
                                @if($bill->balance > 0)
                                    <a href="{{ route('hospital.cashier.payments.create', $bill->id) }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-money me-1"></i>Record Payment
                                    </a>
                                @endif
                                <a href="{{ route('hospital.cashier.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Patient:</th>
                                            <td>
                                                <a href="{{ route('hospital.reception.patients.show', $bill->patient->id) }}">
                                                    {{ $bill->patient->full_name }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $bill->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>
                                                <a href="{{ route('hospital.reception.visits.show', $bill->visit->id) }}">
                                                    {{ $bill->visit->visit_number }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Bill Type:</th>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst(str_replace('_', ' ', $bill->bill_type)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Payment Status:</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'partial' => 'info',
                                                        'paid' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $color = $statusColors[$bill->payment_status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst($bill->payment_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Clearance Status:</th>
                                            <td>
                                                @php
                                                    $clearanceColors = [
                                                        'pending' => 'warning',
                                                        'cleared' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $clearanceColor = $clearanceColors[$bill->clearance_status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $clearanceColor }}">
                                                    {{ ucfirst($bill->clearance_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount:</th>
                                            <td><strong>{{ number_format($bill->total, 2) }} TZS</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Paid:</th>
                                            <td>{{ number_format($bill->paid, 2) }} TZS</td>
                                        </tr>
                                        <tr>
                                            <th>Balance:</th>
                                            <td>
                                                <strong class="{{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($bill->balance, 2) }} TZS
                                                </strong>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bill Items -->
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>Bill Items
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($bill->items->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bill->items as $item)
                                                <tr>
                                                    <td>{{ $item->item_name }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ number_format($item->unit_price, 2) }} TZS</td>
                                                    <td>{{ number_format($item->total, 2) }} TZS</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3">Subtotal:</th>
                                                <th>{{ number_format($bill->subtotal, 2) }} TZS</th>
                                            </tr>
                                            <tr>
                                                <th colspan="3">Discount:</th>
                                                <th>{{ number_format($bill->discount, 2) }} TZS</th>
                                            </tr>
                                            <tr>
                                                <th colspan="3">Tax:</th>
                                                <th>{{ number_format($bill->tax, 2) }} TZS</th>
                                            </tr>
                                            <tr class="table-primary">
                                                <th colspan="3">Total:</th>
                                                <th>{{ number_format($bill->total, 2) }} TZS</th>
                                            </tr>
                                            <tr>
                                                <th colspan="3">Paid:</th>
                                                <th>{{ number_format($bill->paid, 2) }} TZS</th>
                                            </tr>
                                            <tr>
                                                <th colspan="3">Balance:</th>
                                                <th class="{{ $bill->balance > 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($bill->balance, 2) }} TZS
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted">No items in this bill.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Payments History -->
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-money me-2"></i>Payments
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($bill->payments->count() > 0)
                                <div class="list-group">
                                    @foreach($bill->payments as $payment)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">
                                                    {{ number_format($payment->amount, 2) }} TZS
                                                </h6>
                                                <small>{{ $payment->payment_date->format('d M Y, H:i') }}</small>
                                            </div>
                                            <p class="mb-1">
                                                <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                                                @if($payment->reference_number)
                                                    <br><small class="text-muted">Ref: {{ $payment->reference_number }}</small>
                                                @endif
                                            </p>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No payments recorded yet.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
