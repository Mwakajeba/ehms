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
                                <div class="card border-warning shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-group text-warning" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-warning mb-2">Fee Group</h5>
                                        <p class="card-text text-muted small mb-3">Manage and organize fee groups for different categories of students</p>
                                        <a href="{{ route('college.fee-groups.index') }}" class="btn btn-outline-warning btn-sm">
                                            <i class="bx bx-plus me-1"></i> Manage Groups
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Settings -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-info shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-cog text-info" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-info mb-2">Fee Settings</h5>
                                        <p class="card-text text-muted small mb-3">Configure fee structures, discounts, and payment policies</p>
                                        <a href="{{ route('college.fee-settings.index') }}" class="btn btn-outline-info btn-sm">
                                            <i class="bx bx-cog me-1"></i> Configure
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Generate Fee Invoice -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-success shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-receipt text-success" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-success mb-2">Generate Fee Invoice</h5>
                                        <p class="card-text text-muted small mb-3">Create and send fee invoices to students and parents</p>
                                        <a href="{{ route('college.fee-invoices.create') }}" class="btn btn-outline-success btn-sm">
                                            <i class="bx bx-plus me-1"></i> Generate Invoice
                                        </a>
                                        <br>
                                        <a href="{{ route('college.fee-invoices.index') }}" class="btn btn-link btn-sm text-success p-0 mt-1">
                                            <small><i class="bx bx-list-ul me-1"></i> View All Invoices</small>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Bulk Send Invoice -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-primary shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-send text-primary" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-primary mb-2">Bulk Send Invoice</h5>
                                        <p class="card-text text-muted small mb-3">Send multiple draft invoices to students in bulk</p>
                                        <a href="{{ route('college.fee-invoices.bulk-send-form') }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bx bx-send me-1"></i> Bulk Send
                                        </a>
                                        <br>
                                        <a href="{{ route('college.fee-invoices.index') }}" class="btn btn-link btn-sm text-primary p-0 mt-1">
                                            <small><i class="bx bx-list-ul me-1"></i> View All Invoices</small>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-primary shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-money text-primary" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-primary mb-2">Other Income Collection</h5>
                                        <p class="card-text text-muted small mb-3">Track and manage additional income sources and collections</p>
                                        <button class="btn btn-outline-primary btn-sm" disabled>
                                            <i class="bx bx-plus me-1"></i> Manage Income
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Fee Reminder -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-danger shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-bell text-danger" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-danger mb-2">Fee Reminder</h5>
                                        <p class="card-text text-muted small mb-3">Send automated reminders for overdue fee payments</p>
                                        <button class="btn btn-outline-danger btn-sm" disabled>
                                            <i class="bx bx-bell me-1"></i> Send Reminders
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Full Paid Invoices -->
                            <div class="col-xl-4 col-lg-6">
                                <div class="card border-secondary shadow-sm h-100">
                                    <div class="card-body text-center">
                                        <div class="feature-icon mb-3">
                                            <i class="bx bx-check-circle text-secondary" style="font-size: 3rem;"></i>
                                        </div>
                                        <h5 class="card-title text-secondary mb-2">Full Paid Invoices</h5>
                                        <p class="card-text text-muted small mb-3">View and manage completely paid fee invoices</p>
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="bx bx-check me-1"></i> View Paid
                                        </button>
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
                                                    <h4 class="text-primary mb-1">{{ config('app.currency', 'TZS') }} {{ number_format($totalCollected, 2) }}</h4>
                                                    <small class="text-muted">Total Collected</small>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <div class="p-2">
                                                    <h4 class="text-warning mb-1">{{ config('app.currency', 'TZS') }} {{ number_format($pendingAmount, 2) }}</h4>
                                                    <small class="text-muted">Pending Amount</small>
                                                </div>
                                            </div>
                                            <div class="col-md-2 col-6">
                                                <div class="p-2">
                                                    <h4 class="text-danger mb-1">{{ config('app.currency', 'TZS') }} {{ number_format($overdueAmount, 2) }}</h4>
                                                    <small class="text-muted">Overdue Amount</small>
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
                                                    <h4 class="text-secondary mb-1">{{ $collectionRate }}%</h4>
                                                    <small class="text-muted">Collection Rate</small>
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