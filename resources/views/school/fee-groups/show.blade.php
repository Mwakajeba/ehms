@extends('layouts.main')

@section('title', 'Fee Group Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Groups', 'url' => route('school.fee-groups.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">FEE GROUP DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-10 offset-lg-1">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-group me-1 font-22 text-warning"></i></div>
                                <h5 class="mb-0 text-warning">{{ $feeGroup->name }}</h5>
                            </div>
                            <div>
                                <a href="{{ route('school.fee-groups.edit', $feeGroup->hashid) }}" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.fee-groups.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Fee Groups
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="180">Fee Code:</th>
                                        <td>
                                            <span class="badge bg-warning text-dark">{{ $feeGroup->fee_code }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Fee Group Name:</th>
                                        <td>{{ $feeGroup->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge bg-{{ $feeGroup->is_active ? 'success' : 'secondary' }}">
                                                {{ $feeGroup->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created By:</th>
                                        <td>{{ $feeGroup->creator ? $feeGroup->creator->name : 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="180">Receivable Account:</th>
                                        <td>
                                            @if($feeGroup->receivableAccount)
                                                <span class="text-primary">{{ $feeGroup->receivableAccount->account_code }} - {{ $feeGroup->receivableAccount->account_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Income Account:</th>
                                        <td>
                                            @if($feeGroup->incomeAccount)
                                                <span class="text-success">{{ $feeGroup->incomeAccount->account_code }} - {{ $feeGroup->incomeAccount->account_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Transport Income Account:</th>
                                        <td>
                                            @if($feeGroup->transportIncomeAccount)
                                                <span class="text-info">{{ $feeGroup->transportIncomeAccount->account_code }} - {{ $feeGroup->transportIncomeAccount->account_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Discount Account:</th>
                                        <td>
                                            @if($feeGroup->discountAccount)
                                                <span class="text-warning">{{ $feeGroup->discountAccount->account_code }} - {{ $feeGroup->discountAccount->account_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Opening Balance Account:</th>
                                        <td>
                                            @if($feeGroup->openingBalanceAccount)
                                                <span class="text-primary">{{ $feeGroup->openingBalanceAccount->account_code }} - {{ $feeGroup->openingBalanceAccount->account_name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At:</th>
                                        <td>{{ $feeGroup->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Last Updated:</th>
                                        <td>{{ $feeGroup->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($feeGroup->description)
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-warning mb-2">
                                    <i class="bx bx-info-circle me-1"></i>Description
                                </h6>
                                <p class="text-muted">{{ $feeGroup->description }}</p>
                            </div>
                        </div>
                        @endif

                        <!-- Account Information Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-warning mb-3">
                                    <i class="bx bx-money me-1"></i>Chart Account Details
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="bx bx-receipt me-1"></i>Receivable Account
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @if($feeGroup->receivableAccount)
                                                    <p class="mb-1"><strong>Code:</strong> {{ $feeGroup->receivableAccount->account_code }}</p>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $feeGroup->receivableAccount->account_name }}</p>
                                                    <p class="mb-1"><strong>Type:</strong> {{ $feeGroup->receivableAccount->account_type }}</p>
                                                    <p class="mb-0"><strong>Status:</strong>
                                                        <span class="badge bg-{{ $feeGroup->receivableAccount->is_active ? 'success' : 'secondary' }} ms-1">
                                                            {{ $feeGroup->receivableAccount->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </p>
                                                @else
                                                    <p class="text-muted">No receivable account assigned</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-success">
                                            <div class="card-header bg-success text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="bx bx-trending-up me-1"></i>Income Account
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @if($feeGroup->incomeAccount)
                                                    <p class="mb-1"><strong>Code:</strong> {{ $feeGroup->incomeAccount->account_code }}</p>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $feeGroup->incomeAccount->account_name }}</p>
                                                    <p class="mb-1"><strong>Type:</strong> {{ $feeGroup->incomeAccount->account_type }}</p>
                                                    <p class="mb-0"><strong>Status:</strong>
                                                        <span class="badge bg-{{ $feeGroup->incomeAccount->is_active ? 'success' : 'secondary' }} ms-1">
                                                            {{ $feeGroup->incomeAccount->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </p>
                                                @else
                                                    <p class="text-muted">No income account assigned</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card border-info">
                                            <div class="card-header bg-info text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="bx bx-bus me-1"></i>Transport Income Account
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @if($feeGroup->transportIncomeAccount)
                                                    <p class="mb-1"><strong>Code:</strong> {{ $feeGroup->transportIncomeAccount->account_code }}</p>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $feeGroup->transportIncomeAccount->account_name }}</p>
                                                    <p class="mb-1"><strong>Type:</strong> {{ $feeGroup->transportIncomeAccount->account_type }}</p>
                                                    <p class="mb-0"><strong>Status:</strong>
                                                        <span class="badge bg-{{ $feeGroup->transportIncomeAccount->is_active ? 'success' : 'secondary' }} ms-1">
                                                            {{ $feeGroup->transportIncomeAccount->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </p>
                                                @else
                                                    <p class="text-muted">No transport income account assigned</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-warning">
                                            <div class="card-header bg-warning text-dark">
                                                <h6 class="card-title mb-0">
                                                    <i class="bx bx-discount me-1"></i>Discount Account
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @if($feeGroup->discountAccount)
                                                    <p class="mb-1"><strong>Code:</strong> {{ $feeGroup->discountAccount->account_code }}</p>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $feeGroup->discountAccount->account_name }}</p>
                                                    <p class="mb-1"><strong>Type:</strong> {{ $feeGroup->discountAccount->account_type }}</p>
                                                    <p class="mb-0"><strong>Status:</strong>
                                                        <span class="badge bg-{{ $feeGroup->discountAccount->is_active ? 'success' : 'secondary' }} ms-1">
                                                            {{ $feeGroup->discountAccount->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </p>
                                                @else
                                                    <p class="text-muted">No discount account assigned</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="card border-primary">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="bx bx-wallet me-1"></i>Opening Balance Account
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                @if($feeGroup->openingBalanceAccount)
                                                    <p class="mb-1"><strong>Code:</strong> {{ $feeGroup->openingBalanceAccount->account_code }}</p>
                                                    <p class="mb-1"><strong>Name:</strong> {{ $feeGroup->openingBalanceAccount->account_name }}</p>
                                                    <p class="mb-1"><strong>Type:</strong> {{ $feeGroup->openingBalanceAccount->account_type }}</p>
                                                    <p class="mb-0"><strong>Status:</strong>
                                                        <span class="badge bg-{{ $feeGroup->openingBalanceAccount->is_active ? 'success' : 'secondary' }} ms-1">
                                                            {{ $feeGroup->openingBalanceAccount->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </p>
                                                @else
                                                    <p class="text-muted">No opening balance account assigned</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('school.fee-groups.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to Fee Groups
                            </a>
                            <div>
                                <a href="{{ route('school.fee-groups.edit', $feeGroup->hashid) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit me-1"></i> Edit Fee Group
                                </a>
                                <form action="{{ route('school.fee-groups.destroy', $feeGroup->hashid) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this fee group? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bx bx-trash me-1"></i> Delete Fee Group
                                    </button>
                                </form>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        color: #6c757d;
    }

    .table td {
        font-size: 0.875rem;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    .badge {
        font-size: 0.75rem;
    }

    .card {
        border-radius: 0.75rem;
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
    }
</style>
@endpush