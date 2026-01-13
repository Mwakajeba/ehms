@extends('layouts.main')

@section('title', 'College Fee Setting Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Fee Settings', 'url' => route('college.fee-settings.index'), 'icon' => 'bx bx-cog'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">COLLEGE FEE SETTING DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-cog me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">College Fee Setting Information</h5>
                            </div>
                            <div>
                                <a href="{{ route('college.fee-settings.edit', $feeSetting) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('college.fee-settings.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bx bx-info-circle me-1"></i> Basic Information
                                </h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Program:</td>
                                        <td>{{ $feeSetting->program->name ?? 'N/A' }} ({{ $feeSetting->program->code ?? '' }})</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Fee Period:</td>
                                        <td>
                                            <span class="badge bg-info">{{ $feeSetting->getFeePeriodOptions()[$feeSetting->fee_period] ?? $feeSetting->fee_period }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Category:</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $feeSetting->getCategoryOptions()[$feeSetting->category] ?? $feeSetting->category }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status:</td>
                                        <td>
                                            @if($feeSetting->is_active)
                                                <span class="badge bg-success">
                                                    <i class="bx bx-check me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bx bx-x me-1"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="bx bx-calendar me-1"></i> Date Information
                                </h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold">Start Date:</td>
                                        <td>{{ $feeSetting->date_from ? $feeSetting->date_from->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">End Date:</td>
                                        <td>{{ $feeSetting->date_to ? $feeSetting->date_to->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Created:</td>
                                        <td>{{ $feeSetting->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Updated:</td>
                                        <td>{{ $feeSetting->updated_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Fee Configuration -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary mb-3">
                                    <i class="bx bx-list-ul me-1"></i> Fee Configuration
                                </h6>

                                @if($feeSetting->collegeFeeSettingItems && $feeSetting->collegeFeeSettingItems->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Fee Group</th>
                                                    <th>Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($feeSetting->collegeFeeSettingItems as $item)
                                                    <tr>
                                                        <td>
                                                            <span class="badge bg-primary">
                                                                {{ $item->feeGroup->name ?? 'N/A' }}
                                                            </span>
                                                        </td>
                                                        <td>{{ config('app.currency', 'TZS') }} {{ number_format($item->amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-1"></i>
                                        No fee lines configured for this setting.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Description -->
                        @if($feeSetting->description)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="bx bx-file me-1"></i> Description
                                    </h6>
                                    <div class="card">
                                        <div class="card-body">
                                            <p class="mb-0">{{ $feeSetting->description }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Summary -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary mb-3">
                                            <i class="bx bx-calculator me-1"></i> Fee Summary
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="text-center">
                                                    <h5 class="text-primary">{{ $feeSetting->collegeFeeSettingItems ? $feeSetting->collegeFeeSettingItems->count() : 0 }}</h5>
                                                    <small class="text-muted">Fee Lines</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="text-center">
                                                    <h5 class="text-success">{{ config('app.currency', 'TZS') }} {{ number_format($feeSetting->collegeFeeSettingItems ? $feeSetting->collegeFeeSettingItems->sum('amount') : 0, 2) }}</h5>
                                                    <small class="text-muted">Total Amount</small>
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

    .table-borderless td {
        border: none;
        padding: 0.5rem 0;
    }

    .badge {
        font-size: 0.75rem;
    }

    .card.border-primary {
        border-color: #0d6efd !important;
    }

    .card.border-success {
        border-color: #198754 !important;
    }

    .card.border-info {
        border-color: #0dcaf0 !important;
    }
</style>
@endpush