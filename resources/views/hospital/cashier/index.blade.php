@extends('layouts.main')

@section('title', 'Cashier Dashboard')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Cashier', 'url' => '#', 'icon' => 'bx bx-money']
            ]" />
            <h6 class="mb-0 text-uppercase">CASHIER DASHBOARD</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Pending Invoices</h6>
                                    <h4 class="mb-0">{{ $stats['pending_bills'] }}</h4>
                                </div>
                                <div class="text-primary" style="font-size: 2rem;">
                                    <i class="bx bx-receipt"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Pending Amount</h6>
                                    <h4 class="mb-0">{{ number_format($stats['pending_amount'], 2) }} TZS</h4>
                                </div>
                                <div class="text-warning" style="font-size: 2rem;">
                                    <i class="bx bx-money"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Paid Today</h6>
                                    <h4 class="mb-0">{{ $stats['paid_today'] }}</h4>
                                </div>
                                <div class="text-success" style="font-size: 2rem;">
                                    <i class="bx bx-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Revenue Today</h6>
                                    <h4 class="mb-0">{{ number_format($stats['revenue_today'], 2) }} TZS</h4>
                                </div>
                                <div class="text-info" style="font-size: 2rem;">
                                    <i class="bx bx-trending-up"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="input-group">
                                <input type="text" class="form-control" id="searchTerm" placeholder="Search by Invoice #, Customer Name, Phone, or Email...">
                                <button class="btn btn-primary" type="button" onclick="searchBills()">
                                    <i class="bx bx-search me-1"></i>Search
                                </button>
                            </div>
                            <div id="searchResults" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Bills -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>Pending Bills (Sales Invoices)
                                <span class="badge bg-warning ms-2">{{ $pendingInvoices->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($pendingInvoices->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Customer/Patient</th>
                                                <th>Phone</th>
                                                <th>Invoice Date</th>
                                                <th>Total</th>
                                                <th>Paid</th>
                                                <th>Balance</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingInvoices as $invoice)
                                                <tr>
                                                    <td><strong>{{ $invoice->invoice_number }}</strong></td>
                                                    <td>{{ $invoice->customer->name }}</td>
                                                    <td>{{ $invoice->customer->phone ?? 'N/A' }}</td>
                                                    <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                                                    <td>{{ number_format($invoice->total_amount, 2) }} TZS</td>
                                                    <td>{{ number_format($invoice->paid_amount, 2) }} TZS</td>
                                                    <td>
                                                        <strong class="{{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                                            {{ number_format($invoice->balance_due, 2) }} TZS
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'draft' => 'secondary',
                                                                'sent' => 'warning',
                                                                'partial' => 'info',
                                                                'paid' => 'success',
                                                                'cancelled' => 'danger'
                                                            ];
                                                            $color = $statusColors[$invoice->status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $color }}">
                                                            {{ ucfirst($invoice->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('sales.invoices.show', $invoice->encoded_id) }}" class="btn btn-sm btn-info">
                                                            <i class="bx bx-show me-1"></i>View & Pay
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No pending bills at the moment.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function searchBills() {
    const term = document.getElementById('searchTerm').value;
    const resultsDiv = document.getElementById('searchResults');
    
    if (term.length < 2) {
        resultsDiv.innerHTML = '';
        return;
    }
    
    fetch(`{{ route('hospital.cashier.search') }}?term=${encodeURIComponent(term)}`)
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                let html = '<div class="list-group">';
                data.forEach(invoice => {
                    html += `
                        <a href="{{ url('sales/invoices') }}/${invoice.encoded_id}" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${invoice.invoice_number} - ${invoice.customer.name}</h6>
                                <small>${invoice.customer.phone || 'N/A'}</small>
                            </div>
                            <p class="mb-1">Balance: ${parseFloat(invoice.balance_due).toFixed(2)} TZS | Status: ${invoice.status}</p>
                        </a>
                    `;
                });
                html += '</div>';
                resultsDiv.innerHTML = html;
            } else {
                resultsDiv.innerHTML = '<p class="text-muted">No invoices found.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultsDiv.innerHTML = '<p class="text-danger">Error searching invoices.</p>';
        });
}

document.getElementById('searchTerm').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchBills();
    }
});
</script>
@endpush
