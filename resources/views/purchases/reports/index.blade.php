@extends('layouts.main')

@section('title','Purchases Reports')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Reports', 'url' => '#', 'icon' => 'bx bx-bar-chart']
        ]" />

        <h6 class="mb-0 text-uppercase">Purchases Reports</h6>
        <hr />

        <div class="row g-3">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-primary position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-file-blank fs-1 text-primary"></i>
                        </div>
                        <h5 class="card-title">Purchase Order Register</h5>
                        <p class="card-text">PO list with status and totals</p>
                        <a href="{{ route('purchases.reports.purchase-order-register') }}" class="btn btn-primary">
                            <i class="bx bx-file-blank me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-success position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-transfer fs-1 text-success"></i>
                        </div>
                        <h5 class="card-title">PO vs GRN (Fulfillment)</h5>
                        <p class="card-text">Ordered vs received, pending deliveries</p>
                        <a href="{{ route('purchases.reports.po-vs-grn') }}" class="btn btn-success">
                            <i class="bx bx-transfer me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-warning position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-error fs-1 text-warning"></i>
                        </div>
                        <h5 class="card-title">GRN vs Invoice Variance</h5>
                        <p class="card-text">Received vs invoiced mismatch</p>
                        <a href="{{ route('purchases.reports.grn-variance') }}" class="btn btn-warning">
                            <i class="bx bx-error me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-danger position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-receipt fs-1 text-danger"></i>
                        </div>
                        <h5 class="card-title">Supplier Invoice Register</h5>
                        <p class="card-text">All AP invoices and totals</p>
                        <a href="{{ route('purchases.reports.invoice-register') }}" class="btn btn-danger">
                            <i class="bx bx-receipt me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-info position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-file fs-1 text-info"></i>
                        </div>
                        <h5 class="card-title">Supplier Statement</h5>
                        <p class="card-text">Running balances and transactions</p>
                        <a href="{{ route('purchases.reports.supplier-statement.index') }}" class="btn btn-info">
                            <i class="bx bx-file me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-secondary position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-time-five fs-1 text-secondary"></i>
                        </div>
                        <h5 class="card-title">Payables Aging</h5>
                        <p class="card-text">Buckets 0–30, 31–60, 61–90, 90+</p>
                        <a href="{{ route('purchases.reports.payables-aging') }}" class="btn btn-secondary">
                            <i class="bx bx-time-five me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-dark position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-hourglass fs-1 text-dark"></i>
                        </div>
                        <h5 class="card-title">Outstanding Invoices</h5>
                        <p class="card-text">Unpaid and partial AP invoices</p>
                        <a href="{{ route('purchases.reports.outstanding-invoices') }}" class="btn btn-dark">
                            <i class="bx bx-hourglass me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-success position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-check-circle fs-1 text-success"></i>
                        </div>
                        <h5 class="card-title">Paid Invoices</h5>
                        <p class="card-text">Fully settled AP invoices</p>
                        <a href="{{ route('purchases.reports.paid-invoices') }}" class="btn btn-success">
                            <i class="bx bx-check-circle me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-secondary position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-note fs-1 text-secondary"></i>
                        </div>
                        <h5 class="card-title">Supplier Credit Notes</h5>
                        <p class="card-text">Returns and adjustments</p>
                        <a href="{{ route('purchases.reports.supplier-credit-notes') }}" class="btn btn-secondary">
                            <i class="bx bx-note me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-warning position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-git-compare fs-1 text-warning"></i>
                        </div>
                        <h5 class="card-title">PO vs Invoice Variance</h5>
                        <p class="card-text">Ordered/received vs invoiced</p>
                        <a href="{{ route('purchases.reports.po-invoice-variance') }}" class="btn btn-warning">
                            <i class="bx bx-git-compare me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-primary position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-user fs-1 text-primary"></i>
                        </div>
                        <h5 class="card-title">Purchase Analysis by Supplier</h5>
                        <p class="card-text">Spend, ranking, discounts</p>
                        <a href="{{ route('purchases.reports.purchase-by-supplier') }}" class="btn btn-primary">
                            <i class="bx bx-user me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-primary position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-basket fs-1 text-primary"></i>
                        </div>
                        <h5 class="card-title">Purchase Analysis by Item</h5>
                        <p class="card-text">Item/category trends</p>
                        <a href="{{ route('purchases.reports.purchase-by-item') }}" class="btn btn-primary">
                            <i class="bx bx-basket me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-info position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-line-chart fs-1 text-info"></i>
                        </div>
                        <h5 class="card-title">Purchase Forecast</h5>
                        <p class="card-text">Moving average, suggested buys</p>
                        <a href="{{ route('purchases.reports.purchase-forecast') }}" class="btn btn-info">
                            <i class="bx bx-line-chart me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-success position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-receipt fs-1 text-success"></i>
                        </div>
                        <h5 class="card-title">Supplier Invoice Tax</h5>
                        <p class="card-text">Input VAT/GST summary</p>
                        <a href="{{ route('purchases.reports.supplier-tax') }}" class="btn btn-success">
                            <i class="bx bx-receipt me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-dark position-relative">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bx bx-calendar-event fs-1 text-dark"></i>
                        </div>
                        <h5 class="card-title">Supplier Payment Schedule</h5>
                        <p class="card-text">Upcoming obligations</p>
                        <a href="{{ route('purchases.reports.payment-schedule') }}" class="btn btn-dark">
                            <i class="bx bx-calendar-event me-1"></i> View Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


