@extends('layouts.main')

@section('title', 'Fee Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        <h6 class="mb-0 text-uppercase">FEE MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-money me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Fee Management Module</h5>
                        </div>
                        <hr />

                        <div class="row g-3">
                            <!-- Fee Group -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-warning shadow-sm h-100 position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning">
                                        {{ $feeGroupsCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-group text-warning" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-warning mb-2">Fee Group</h5>
                                        <p class="card-text text-muted small mb-3">Manage and organize fee groups for different categories of students</p>
                                        <a href="{{ route('school.fee-groups.index') }}" class="btn btn-outline-warning btn-sm">
                                            <i class="bx bx-plus me-1"></i> Manage Groups
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Settings -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-info shadow-sm h-100 position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info">
                                        {{ $feeSettingsCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-cog text-info" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-info mb-2">Fee Settings</h5>
                                        <p class="card-text text-muted small mb-3">Configure fee structures, discounts, and payment policies</p>
                                        <a href="{{ route('school.fee-settings.index') }}" class="btn btn-outline-info btn-sm">
                                            <i class="bx bx-cog me-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Generate Fee Invoice -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-success shadow-sm h-100 position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                                        {{ $totalInvoices }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-receipt text-success" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-success mb-2">Generate Fee Invoice</h5>
                                        <p class="card-text text-muted small mb-3">Create and send fee invoices to students and parents</p>
                                        <a href="{{ route('school.fee-invoices.create') }}" class="btn btn-outline-success btn-sm">
                                            <i class="bx bx-plus me-1"></i> Generate Bulk Invoice
                                        </a>
                                        <br>
                                        <a href="{{ route('school.fee-invoices.index') }}" class="btn btn-link btn-sm text-success p-0 mt-1">
                                            <small><i class="bx bx-list-ul me-1"></i> View All Invoices</small>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Other Income Collection -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-primary shadow-sm h-100 position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                                        {{ $otherIncomeCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-money text-primary" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-primary mb-2">Other Income Collection</h5>
                                        <p class="card-text text-muted small mb-3">Track and manage additional income sources and collections</p>
                                        <a href="{{ route('school.other-income.index') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bx bx-plus me-1"></i> Manage Income
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Reminder -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-danger shadow-sm h-100 position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $overdueAmount > 0 ? number_format($overdueAmount) : 0 }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-bell text-danger" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-danger mb-2">Fee Reminder</h5>
                                        <p class="card-text text-muted small mb-3">Send automated reminders for overdue fee payments</p>
                                        <a href="#" class="btn btn-outline-danger btn-sm">
                                            <i class="bx bx-bell me-1"></i> Send Reminders
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Full Paid Invoices -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-secondary shadow-sm h-100 position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                                        {{ $paidInvoices }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-check-circle text-secondary" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-secondary mb-2">Full Paid Invoices</h5>
                                        <p class="card-text text-muted small mb-3">View and manage completely paid fee invoices</p>
                                        <a href="{{ route('school.fee-invoices.index') }}?status=paid" class="btn btn-outline-secondary btn-sm">
                                            <i class="bx bx-check me-1"></i> View Paid
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Students Opening Balance -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-dark shadow-sm h-100 position-relative">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
                                        {{ $studentOpeningBalanceCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-wallet text-dark" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-dark mb-2">Students Opening Balance</h5>
                                        <p class="card-text text-muted small mb-3">Manage opening balances for students at the start of academic year</p>
                                        <a href="{{ route('school.student-fee-opening-balance.index') }}" class="btn btn-outline-dark btn-sm">
                                            <i class="bx bx-wallet me-1"></i> Manage Opening Balance
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Prepaid Account Settings -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-purple shadow-sm h-100 position-relative" style="border-color: #6f42c1 !important;">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background-color: #6f42c1;">
                                        <i class="bx bx-cog"></i>
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-cog" style="font-size: 3rem; color: #6f42c1;"></i>
                                        </div>
                                        <h5 class="card-title mb-2" style="color: #6f42c1;">Prepaid Account Settings</h5>
                                        <p class="card-text text-muted small mb-3">Configure prepaid account settings and automatic credit application rules</p>
                                        <a href="{{ route('school.prepaid-accounts.settings') }}" class="btn btn-sm" style="border-color: #6f42c1; color: #6f42c1;">
                                            <i class="bx bx-cog me-1"></i> Configure Settings
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Prepaid Account -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-teal shadow-sm h-100 position-relative" style="border-color: #20c997 !important;">
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background-color: #20c997;">
                                        {{ $prepaidAccountsCount }}
                                    </span>
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-credit-card" style="font-size: 3rem; color: #20c997;"></i>
                                        </div>
                                        <h5 class="card-title mb-2" style="color: #20c997;">Student Prepaid Account</h5>
                                        <p class="card-text text-muted small mb-3">Manage student prepaid accounts, add credits, and track advance payments</p>
                                        <a href="{{ route('school.prepaid-accounts.index') }}" class="btn btn-sm" style="border-color: #20c997; color: #20c997;">
                                            <i class="bx bx-list-ul me-1"></i> Manage Accounts
                                        </a>
                                        <br>
                                        <a href="{{ route('school.prepaid-accounts.create') }}" class="btn btn-link btn-sm p-0 mt-1" style="color: #20c997;">
                                            <small><i class="bx bx-plus me-1"></i> Create Account</small>
                                        </a>
                                        <a href="{{ route('school.prepaid-accounts.import') }}" class="btn btn-link btn-sm p-0 mt-1" style="color: #20c997;">
                                            <small><i class="bx bx-import me-1"></i> Import</small>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Stats Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-bar-chart me-2"></i>Quick Statistics
                                        </h6>
                                        <div class="row text-center">
                                            <div class="col-md-2 col-6">
                                                <div class="p-2">
                                                    <h4 class="text-success mb-1">{{ number_format($totalInvoices) }}</h4>
                                                    <small class="text-muted">Total Invoices</small>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <div class="p-2">
                                                    <h4 class="text-primary mb-1">{{ config('app.currency', 'TZS') }} {{ number_format($totalCollection, 2) }}</h4>
                                                    <small class="text-muted">Total Collection</small>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <div class="p-2">
                                                    <h4 class="text-info mb-1">{{ number_format($activeStudents) }}</h4>
                                                    <small class="text-muted">Active Students</small>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <div class="p-2">
                                                    <h4 class="text-success mb-1">{{ number_format($paidInvoices) }}</h4>
                                                    <small class="text-muted">Full Paid Invoices</small>
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
    .feature-icon {
        transition: transform 0.3s ease;
    }

    .card:hover .feature-icon {
        transform: scale(1.1);
    }

    .card {
        transition: all 0.3s ease;
        border-radius: 0.75rem;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .card-title {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .btn-outline-warning:hover,
    .btn-outline-info:hover,
    .btn-outline-success:hover,
    .btn-outline-primary:hover,
    .btn-outline-danger:hover,
    .btn-outline-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .border-purple {
        border-color: #6f42c1 !important;
    }

    .border-teal {
        border-color: #20c997 !important;
    }

    .bg-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    }

    .h-100 {
        height: 100%;
    }

    @media (max-width: 768px) {
        .card-title {
            font-size: 1rem;
        }

        .feature-icon i {
            font-size: 2.5rem !important;
        }
    }
</style>
@endpush