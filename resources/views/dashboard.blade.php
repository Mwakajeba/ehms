@extends('layouts.main')

@section('title', __('app.dashboard'))

@php
use Vinkla\Hashids\Facades\Hashids;
@endphp

<style>
    .financial-section {
        margin-bottom: 20px;
    }

    .section-header {
        border-radius: 8px 8px 0 0 !important;
    }

    .section-content {
        border-radius: 0 0 8px 8px !important;
        border-top: none !important;
    }

    .account-row:hover {
        background-color: #f8f9fa;
        transition: background-color 0.2s ease;
    }

    .account-row a:hover {
        color: #007bff !important;
        text-decoration: underline !important;
    }

    .table-sm td {
        padding: 0.5rem;
        vertical-align: middle;
    }

    .section-title {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .widgets-icons-2 {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #ededed;
        font-size: 27px;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(45deg, #0d6efd, #0a58ca) !important;
    }
    
    .bg-gradient-success {
        background: linear-gradient(45deg, #198754, #146c43) !important;
    }
    
    .bg-gradient-info {
        background: linear-gradient(45deg, #0dcaf0, #0aa2c0) !important;
    }
    
    .bg-gradient-warning {
        background: linear-gradient(45deg, #ffc107, #ffb300) !important;
    }
    
    .bg-gradient-danger {
        background: linear-gradient(45deg, #dc3545, #bb2d3b) !important;
    }
    
    .bg-gradient-secondary {
        background: linear-gradient(45deg, #6c757d, #5c636a) !important;
    }
    
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .radius-10 {
        border-radius: 10px;
    }
    
    .border-start {
        border-left-width: 3px !important;
    }

    @media print {

        .btn,
        .overlay,
        .back-to-top,
        footer {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }

        .section-header {
            background: #333 !important;
            color: white !important;
        }
    }
</style>

@section('content')
@can('view dashboard')
<div class="page-wrapper">
    <div class="page-content">

        <div class="page-content">
        <!-- Welcome Section -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="card-title d-flex align-items-center">
                                    <div><i class="bx bx-home me-1 font-22 text-primary"></i></div>
                                    <!-- <h5 class="mb-0 text-primary">Welcome back, {{ auth()->user()->name }}
                                    </h5> -->
                                    <h5 class="mb-0 text-primary">Dashboard
                                    </h5>
                                </div>
                                <p class="mb-0 text-muted">Here's what's happening with your financial data today</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('hospital.reception.patients.create') }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-user-plus"></i> Create Patient
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Filter -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center">
                            <div class="me-3">
                                <label for="branch_id" class="form-label mb-0"><strong>Filter Dashboard By Branch:</strong></label>
                            </div>
                            <div class="me-3">
                                <select name="branch_id" id="branch_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($selectedBranchId)
                                <div class="me-3">
                                    <span class="badge bg-primary">
                                        Showing: {{ $branches->where('id', $selectedBranchId)->first()->name ?? 'Selected Branch' }}
                                    </span>
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="row mt-3">
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('hospital.reception.patients.index') }}" class="text-decoration-none">
                    <div class="card radius-10 border-start border-0 border-3 border-info">
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-secondary">Total Patients</p>
                                    <h4 class="my-1 text-info">{{ $totalPatients ?? 0 }}</h4>
                                    <p class="mb-0 font-13">
                                        <span class="text-info"><i class="bx bx-user-plus align-middle"></i> Active patients</span>
                                    </p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-info text-white ms-auto">
                                    <i class='bx bx-user-plus'></i>
                                </div>
                            </div>
                            <span class="stretched-link"></span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card radius-10 border-start border-0 border-3 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0 text-secondary">Patients Admitted Today</p>
                                <h4 class="my-1 text-primary">{{ $patientsAdmittedToday ?? 0 }}</h4>
                                <p class="mb-0 font-13">
                                    <span class="text-primary"><i class="bx bx-bed align-middle"></i> New admissions today</span>
                                </p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-primary text-white ms-auto">
                                <i class='bx bx-bed'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <a href="{{ route('hospital.reception.index') }}" class="text-decoration-none">
                    <div class="card radius-10 border-start border-0 border-3 border-secondary">
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-secondary">Total Visits Today</p>
                                    <h4 class="my-1 text-secondary">{{ $totalVisitsToday ?? 0 }}</h4>
                                    <p class="mb-0 font-13">
                                        <span class="text-secondary"><i class="bx bx-calendar-check align-middle"></i> Patient visits today</span>
                                    </p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blooker text-white ms-auto">
                                    <i class='bx bx-calendar-check'></i>
                                </div>
                            </div>
                            <span class="stretched-link"></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- <div class="col-xl-3 col-md-6">
                <a href="{{ route('approvals.queue') }}" class="text-decoration-none">
                    <div class="card radius-10 border-start border-0 border-3 border-warning">
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-secondary">Pending Approvals</p>
                                    <h4 class="my-1 text-warning">{{ $pendingApprovalsCount ?? 0 }}</h4>
                                    <p class="mb-0 font-13">
                                        <span class="text-warning"><i class="bx bx-check-shield align-middle"></i> Awaiting your review</span>
                                    </p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-warning text-white ms-auto">
                                    <i class='bx bx-check-shield'></i>
                                </div>
                            </div>
                            <span class="stretched-link"></span>
                        </div>
                    </div>
                </a>
            </div> -->

            <div class="col-xl-3 col-md-6">
                <div class="card radius-10 border-start border-0 border-3 border-success">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0 text-secondary">Cash Collected Today</p>
                                <h4 class="my-1 text-success">TZS {{ number_format($cashCollectedToday ?? 0, 2) }}</h4>
                                <p class="mb-0 font-13">
                                    <span class="text-success"><i class="bx bx-money align-middle"></i> Cash & mobile payments today</span>
                                </p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-success text-white ms-auto">
                                <i class='bx bx-money'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card radius-10 border-start border-0 border-3 border-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0 text-secondary">Total Paid by Insurance Today</p>
                                <h4 class="my-1 text-warning">TZS {{ number_format($insurancePaidToday ?? 0, 2) }}</h4>
                                <p class="mb-0 font-13">
                                    <span class="text-warning"><i class="bx bx-shield-quarter align-middle"></i> NHIF, CHF & other insurance</span>
                                </p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-warning text-white ms-auto">
                                <i class='bx bx-shield-quarter'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @can('view revenue this month card')
            <div class="col-xl-3 col-md-6">
                <div class="card radius-10 border-start border-0 border-3 border-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="mb-0 text-secondary">Revenue This Month</p>
                                <h4 class="my-1 text-primary">TZS {{ number_format($revenueThisMonth ?? 0, 2) }}</h4>
                                <p class="mb-0 font-13">
                                    <span class="text-primary"><i class="bx bx-trending-up align-middle"></i> Cash + insurance this month</span>
                                </p>
                            </div>
                            <div class="widgets-icons-2 rounded-circle bg-gradient-primary text-white ms-auto">
                                <i class='bx bx-trending-up'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endcan

            @can('view inventory value card')
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('inventory.reports.stock-valuation') }}" class="text-decoration-none">
                    <div class="card radius-10 border-start border-0 border-3 border-info">
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-secondary">Total Value of Inventory</p>
                                    <h4 class="my-1 text-info">TZS {{ number_format($totalInventoryValue ?? 0, 2) }}</h4>
                                    <p class="mb-0 font-13">
                                        <span class="text-info"><i class="bx bx-package align-middle"></i> Inventory items ({{ $totalInventoryItemsCount ?? 0 }})</span>
                                    </p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-info text-white ms-auto">
                                    <i class='bx bx-package'></i>
                                </div>
                            </div>
                            <span class="stretched-link"></span>
                        </div>
                    </div>
                </a>
            </div>
            @endcan

            @can('view total outstanding invoices card')
            <div class="col-xl-3 col-md-6">
                <a href="{{ route('hospital.cashier.index') }}" class="text-decoration-none">
                    <div class="card radius-10 border-start border-0 border-3 border-warning">
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-secondary">Total Outstanding In Cashier</p>
                                    <h4 class="my-1 text-warning">TZS {{ number_format($cashierOutstandingAmount ?? 0, 2) }}</h4>
                                    <p class="mb-0 font-13">
                                        <span class="text-warning"><i class="bx bx-money align-middle"></i> Unpaid balance ({{ $cashierOutstandingCount ?? 0 }})</span>
                                    </p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-warning text-white ms-auto">
                                    <i class='bx bx-money'></i>
                                </div>
                            </div>
                            <span class="stretched-link"></span>
                        </div>
                    </div>
                </a>
            </div>
            @endcan
        </div>
        <!--end second row-->



        {{-- Charts Row: Top 10 Items Sold
        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body">
                        <h5 class="mb-3">Top 10 Items Sold (This Year)</h5>
                        <div id="topItemsChartWrapper" style="height: 220px; position: relative;">
                            <canvas id="topItemsChart" style="height: 220px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load KPI Data
            fetch(`/dashboard/kpis?branch_id=${encodeURIComponent(document.getElementById('branch_id')?.value || '')}`)
                .then(response => response.json())
                .then(data => {
                    // Update KPI cards with null checks
                    const totalRevenueEl = document.getElementById('totalRevenue');
                    if (totalRevenueEl) {
                        totalRevenueEl.textContent = '$' + Number(data.totalRevenue || 0).toLocaleString();
                    }
                    
                    const totalOrdersEl = document.getElementById('totalOrders');
                    if (totalOrdersEl) {
                        totalOrdersEl.textContent = Number(data.totalOrders || 0).toLocaleString();
                    }
                    
                    const avgOrderValueEl = document.getElementById('avgOrderValue');
                    if (avgOrderValueEl) {
                        avgOrderValueEl.textContent = '$' + Number(data.avgOrderValue || 0).toFixed(0);
                    }
                    
                    const customerSatisfactionEl = document.getElementById('customerSatisfaction');
                    if (customerSatisfactionEl) {
                        customerSatisfactionEl.textContent = Number(data.customerSatisfaction || 0).toFixed(1) + '/5';
                    }

                    // Update change indicators
                    if (typeof updateChangeIndicator === 'function') {
                        updateChangeIndicator('revenueChange', data.revenueChange);
                        updateChangeIndicator('ordersChange', data.ordersChange);
                        updateChangeIndicator('avgOrderChange', data.avgOrderChange);
                        updateChangeIndicator('satisfactionChange', data.satisfactionChange);
                    }
                })
                .catch(err => console.error('KPI data error:', err));

            // Revenue Trend Chart
            const revenueChartEl = document.getElementById('revenueTrendChart');
            if (revenueChartEl) {
                fetch(`/dashboard/revenue-trend?branch_id=${encodeURIComponent(document.getElementById('branch_id')?.value || '')}`)
                    .then(response => response.json())
                    .then(data => {
                        const ctx = revenueChartEl.getContext('2d');
                        if (ctx && typeof Chart !== 'undefined') {
                            new Chart(ctx, {
                                type: 'line',
                                data: data,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false }
                                    },
                                    scales: {
                                        y: { beginAtZero: true }
                                    }
                                }
                            });
                        }
                    })
                    .catch(err => console.error('Revenue trend error:', err));
            }

            // Order Status Distribution Chart
            const orderStatusChartEl = document.getElementById('orderStatusChart');
            if (orderStatusChartEl) {
                fetch(`/dashboard/order-status?branch_id=${encodeURIComponent(document.getElementById('branch_id')?.value || '')}`)
                    .then(response => response.json())
                    .then(data => {
                        const ctx = orderStatusChartEl.getContext('2d');
                        if (ctx && typeof Chart !== 'undefined') {
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: data,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { position: 'bottom' }
                                    }
                                }
                            });
                        }
                    })
                    .catch(err => console.error('Order status error:', err));
            }

            // Top Products Chart
            const topProductsChartEl = document.getElementById('topProductsChart');
            if (topProductsChartEl) {
                fetch(`/dashboard/top-products?branch_id=${encodeURIComponent(document.getElementById('branch_id')?.value || '')}`)
                    .then(response => response.json())
                    .then(data => {
                        const ctx = topProductsChartEl.getContext('2d');
                        if (ctx && typeof Chart !== 'undefined') {
                            new Chart(ctx, {
                                type: 'bar',
                                data: data,
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    indexAxis: 'y',
                                    plugins: {
                                        legend: { display: false }
                                    },
                                    scales: {
                                        x: { beginAtZero: true }
                                    }
                                }
                            });
                        }
                    })
                    .catch(err => console.error('Top products error:', err));
            }

            /*
            // Top 10 Items Sold (This Year)
            const topItemsChartEl = document.getElementById('topItemsChart');
            if (topItemsChartEl) {
                fetch(`/dashboard/top-items-sold?branch_id=${encodeURIComponent(document.getElementById('branch_id')?.value || '')}`)
                    .then(response => response.ok ? response.json() : Promise.reject('Network error'))
                    .then(data => {
                        const canvas = topItemsChartEl;
                        const ctx = canvas.getContext('2d');
                        const items = (data && data.items) ? data.items : [];
                        const quantities = (data && data.quantities) ? data.quantities : [];
                        const isEmpty = items.length === 0 || quantities.length === 0 || quantities.every(q => q == 0);
                        if (isEmpty) {
                            canvas.style.display = 'none';
                            const fallback = document.createElement('div');
                            fallback.style.textAlign = 'center';
                            fallback.style.padding = '30px 0';
                            fallback.style.color = '#888';
                            fallback.innerHTML = '<b>No sales data available for this year.</b>';
                            canvas.parentNode.appendChild(fallback);
                            return;
                        }
                        if (ctx && typeof Chart !== 'undefined') {
                            new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: items,
                                    datasets: [{
                                        label: 'Quantity Sold',
                                        data: quantities,
                                        backgroundColor: '#27ae60'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: false },
                                        title: {
                                            display: true,
                                            text: 'Top 10 Items Sold (This Year)'
                                        }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            title: { display: true, text: 'Qty' }
                                        },
                                        x: {
                                            title: { display: true, text: 'Item' },
                                            ticks: { autoSkip: true, maxTicksLimit: 10 }
                                        }
                                    }
                                }
                            });
                        }
                    })
                    .catch(err => {
                        const canvas = document.getElementById('topItemsChart');
                        if (canvas) {
                            canvas.style.display = 'none';
                            const fallback = document.createElement('div');
                            fallback.style.textAlign = 'center';
                            fallback.style.padding = '30px 0';
                            fallback.style.color = '#888';
                            fallback.innerHTML = '<b>Unable to load items data.</b>';
                            if (canvas.parentNode) {
                                canvas.parentNode.appendChild(fallback);
                            }
                        }
                        console.error('Top items chart error:', err);
                    });
            }
            */

            // Revenue, Expenses, Net Profit (Monthly in Selected Year)
            let profitChartInstance = null;
            function loadProfitChartForYear(year) {
                const branchVal = document.getElementById('branch_id')?.value || '';
                return fetch(`/dashboard/profit-by-year?year=${encodeURIComponent(year)}&branch_id=${encodeURIComponent(branchVal)}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        const canvas = document.getElementById('monthlyCollectionsChart');
                        const ctx = canvas.getContext('2d');
                        const labels = data.labels || ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                        const revenue = (data.revenue || []).map(v => Number(v) || 0);
                        const expenses = (data.expenses || []).map(v => Number(v) || 0);
                        const profit = (data.profit || []).map(v => Number(v) || 0);

                        const isEmpty = labels.length === 0 ||
                            (revenue.every(v => v == 0) && expenses.every(v => v == 0) && profit.every(v => v == 0));
                        if (isEmpty) {
                            canvas.style.display = 'none';
                            const fallback = document.createElement('div');
                            fallback.style.textAlign = 'center';
                            fallback.style.padding = '40px 0';
                            fallback.style.color = '#888';
                            fallback.innerHTML = '<b>No data available for the selected year.</b>';
                            ctx.canvas.parentNode.appendChild(fallback);
                            return;
                        } else {
                            canvas.style.display = '';
                            const parent = ctx.canvas.parentNode;
                            const fallbacks = parent.querySelectorAll('div');
                            fallbacks.forEach(el => { if (el && el.innerText && el.innerText.includes('No data')) el.remove(); });
                        }

                        const minVal = Math.min(...revenue, ...expenses, ...profit);
                        const maxVal = Math.max(...revenue, ...expenses, ...profit);

                        if (profitChartInstance) {
                            profitChartInstance.destroy();
                            profitChartInstance = null;
                        }

                        profitChartInstance = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [
                                    { label: 'Revenue', data: revenue, backgroundColor: 'rgba(46, 204, 113, 0.6)' },
                                    { label: 'Expenses', data: expenses, backgroundColor: 'rgba(231, 76, 60, 0.6)' },
                                    { label: 'Net Profit', data: profit, backgroundColor: 'rgba(52, 152, 219, 0.6)' }
                                ]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { display: true },
                                    title: { display: true, text: `Revenue, Expenses and Net Profit (${year})` },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                let label = context.dataset.label || '';
                                                let raw = context.parsed;
                                                let value = (raw && typeof raw === 'object') ? Number(raw.y ?? 0) : Number(raw ?? 0);
                                                if (!isFinite(value)) value = 0;
                                                return `${label}: TZS ${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: { stacked: false, title: { display: true, text: 'Month' } },
                                    y: {
                                        stacked: false,
                                        beginAtZero: false,
                                        suggestedMin: Math.min(0, minVal * 1.1),
                                        suggestedMax: Math.max(0, maxVal * 1.1),
                                        title: { display: true, text: 'Amount (TZS)' }
                                    }
                                },
                                barThickness: 12
                            }
                        });
                    })
                    .catch((err) => {
                        const canvas = document.getElementById('monthlyCollectionsChart');
                        if (canvas) {
                            canvas.style.display = 'none';
                            const fallback = document.createElement('div');
                            fallback.style.textAlign = 'center';
                            fallback.style.padding = '40px 0';
                            fallback.style.color = '#888';
                            fallback.innerHTML = '<b>Unable to load monthly profit data.</b>';
                            canvas.parentNode.appendChild(fallback);
                        }
                        console && console.error && console.error('profit-by-year fetch failed', err);
                    });
            }

            const yearSelect = document.getElementById('profitYearSelect');
            if (yearSelect) {
                loadProfitChartForYear(yearSelect.value);
                yearSelect.addEventListener('change', function() {
                    loadProfitChartForYear(this.value);
                });
            }
        });

           // Removed yearly aggregate chart init to avoid double-initialization; monthly per-year loader below handles charting

            // Helper function to update change indicators
            function updateChangeIndicator(elementId, change) {
                const element = document.getElementById(elementId);
                if (!element) return;
                
                const icon = element.querySelector('i');
                const text = element.querySelector('small');
                
                if (change > 0) {
                    icon.className = 'bx bx-up-arrow-alt text-success me-1';
                    text.className = 'text-success';
                    text.textContent = '+' + change.toFixed(1) + '%';
                } else if (change < 0) {
                    icon.className = 'bx bx-down-arrow-alt text-danger me-1';
                    text.className = 'text-danger';
                    text.textContent = change.toFixed(1) + '%';
                } else {
                    icon.className = 'bx bx-minus text-muted me-1';
                    text.className = 'text-muted';
                    text.textContent = '0.0%';
                }
            }
        </script>
        <!--end row-->
        @can('view graphs')
        <!-- Balance Sheet Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10 w-100">
                    <div class="card-body">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <div class="card-header bg-white border-bottom-0 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-bar-chart-alt-2 text-primary me-2 font-20"></i>
                                        <h6 class="mb-0 text-dark">Revenue, Expenses and Net Profit</h6>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <label for="profitYearSelect" class="me-2 mb-0 small text-muted">Year</label>
                                        <select id="profitYearSelect" class="form-select form-select-sm" style="width: auto;">
                                            @php $cy = date('Y'); @endphp
                                            @for ($y = $cy; $y >= $cy - 4; $y--)
                                                <option value="{{ $y }}" {{ $y == $cy ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body pt-3 pb-2">
                                    <canvas id="monthlyCollectionsChart" height="120"></canvas>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
        @endcan
    </div>
</div>
@endcan
<!--end page wrapper -->
<!--start overlay-->
<div class="overlay toggle-icon"></div>
<!--end overlay-->
<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<!--End Back To Top Button-->
<footer class="page-footer">
    <p class="mb-0">Copyright © 2021. All right reserved.</p>
</footer>
@endsection