@extends('layouts.main')

@section('title', 'College Fee Group Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('college.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Groups', 'url' => route('college.fee-groups.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">COLLEGE FEE GROUP DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-show me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Fee Group Information</h5>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">ID</label>
                                    <p class="form-control-plaintext">{{ $feeGroup->id }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Fee Code</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-warning text-dark">{{ $feeGroup->fee_code }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Name</label>
                                    <p class="form-control-plaintext">{{ $feeGroup->name }}</p>
                                </div>
                            </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Type</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-info">{{ ucfirst($feeGroup->type) }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <p class="form-control-plaintext">
                                        @if($feeGroup->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Receivable Account</label>
                                    <p class="form-control-plaintext">{{ $feeGroup->receivableAccount ? $feeGroup->receivableAccount->account_name . ' (' . $feeGroup->receivableAccount->account_code . ')' : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Income Account</label>
                                    <p class="form-control-plaintext">{{ $feeGroup->incomeAccount ? $feeGroup->incomeAccount->account_name . ' (' . $feeGroup->incomeAccount->account_code . ')' : 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created At</label>
                                    <p class="form-control-plaintext">{{ $feeGroup->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Updated At</label>
                                    <p class="form-control-plaintext">{{ $feeGroup->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <a href="{{ route('college.fee-groups.edit', $feeGroup) }}" class="btn btn-warning">
                                    <i class="bx bx-edit me-1"></i> Edit Fee Group
                                </a>
                                <a href="{{ route('college.fee-groups.index') }}" class="btn btn-secondary ms-2">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    .form-control-plaintext {
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
    }
</style>
@endpush