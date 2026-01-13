@extends('layouts.main')

@section('title', 'Prepaid Account Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Student Prepaid Accounts', 'url' => route('school.prepaid-accounts.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Account Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">PREPAID ACCOUNT DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-credit-card me-1 font-22 text-primary"></i>
                                <span class="h5 mb-0 text-primary">Prepaid Account Details</span>
                            </div>
                            <div>
                                <a href="{{ route('school.prepaid-accounts.edit', $account->hashid) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.prepaid-accounts.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Student Information -->
                        <div class="card border-primary mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-user me-2"></i> Student Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Admission Number:</strong><br>
                                        {{ $account->student->admission_number ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Student Name:</strong><br>
                                        {{ $account->student->first_name }} {{ $account->student->last_name }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Class:</strong><br>
                                        {{ $account->student->class->name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Stream:</strong><br>
                                        {{ $account->student->stream->name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h4 class="text-success mb-1">{{ config('app.currency', 'TZS') }} {{ number_format($account->credit_balance, 2) }}</h4>
                                        <small class="text-muted">Current Credit Balance</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h4 class="text-info mb-1">{{ config('app.currency', 'TZS') }} {{ number_format($account->total_deposited, 2) }}</h4>
                                        <small class="text-muted">Total Deposited</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h4 class="text-warning mb-1">{{ config('app.currency', 'TZS') }} {{ number_format($account->total_used, 2) }}</h4>
                                        <small class="text-muted">Total Used</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction History -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-history me-2"></i> Transaction History
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Amount</th>
                                                <th>Balance Before</th>
                                                <th>Balance After</th>
                                                <th>Reference</th>
                                                <th>Invoice</th>
                                                <th>Notes</th>
                                                <th>Created By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($account->transactions as $transaction)
                                                <tr>
                                                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $transaction->type == 'deposit' ? 'success' : ($transaction->type == 'withdrawal' ? 'danger' : 'info') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ config('app.currency', 'TZS') }} {{ number_format($transaction->amount, 2) }}</td>
                                                    <td>{{ config('app.currency', 'TZS') }} {{ number_format($transaction->balance_before, 2) }}</td>
                                                    <td>{{ config('app.currency', 'TZS') }} {{ number_format($transaction->balance_after, 2) }}</td>
                                                    <td>{{ $transaction->reference ?? '-' }}</td>
                                                    <td>
                                                        @if($transaction->feeInvoice)
                                                            <a href="{{ route('school.fee-invoices.show', $transaction->feeInvoice->hashid ?? $transaction->feeInvoice->id) }}">
                                                                {{ $transaction->feeInvoice->invoice_number }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ $transaction->notes ?? '-' }}</td>
                                                    <td>{{ $transaction->creator->name ?? 'N/A' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="9" class="text-center">No transactions found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
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

