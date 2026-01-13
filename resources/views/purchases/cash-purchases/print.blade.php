<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Purchase Voucher</title>
    <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; color: #000; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #6c757d; }
        .fw-bold { font-weight: bold; }
        .mb-0 { margin-bottom: 0; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mt-3 { margin-top: 1rem; }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-start { align-items: flex-start; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #dee2e6; padding: 6px 8px; }
        .table thead th { background: #f8f9fa; }
        .table tfoot th { background: #f8f9fa; }
        .table-info th { background: #e7f1ff; }
        .border { border: 1px solid #dee2e6; }
        .p-3 { padding: 1rem; }
    </style>
</head>
<body>
    <div>
        <div class="border p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="mb-0">Payment Voucher - Cash Purchase</h4>
                        <div class="text-muted">Date: {{ optional($purchase->purchase_date)->format('Y-m-d') }}</div>
                        <div class="text-muted">Reference: CP-{{ optional($purchase->purchase_date)->format('Ymd') }}-{{ str_pad($purchase->id, 4, '0', STR_PAD_LEFT) }}</div>
                        <div class="text-muted">Branch: {{ $purchase->branch->name ?? 'N/A' }}</div>
                    </div>
                    <div class="text-end">
                        <h5 class="mb-0">{{ $purchase->company->name ?? config('app.name') }}</h5>
                        @if($purchase->company && $purchase->company->address)
                        <div class="text-muted">{{ $purchase->company->address }}</div>
                        @endif
                    </div>
                </div>

                <div class="mb-2" style="border-top:1px solid #dee2e6;"></div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="mb-2">Payee (Supplier)</h6>
                        <div class="fw-bold">{{ $purchase->supplier->name ?? 'N/A' }}</div>
                        @if($purchase->supplier)
                        <div class="text-muted small">{{ $purchase->supplier->phone ?? '' }}</div>
                        <div class="text-muted small">{{ $purchase->supplier->email ?? '' }}</div>
                        @endif
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="mb-2">Payment Details</h6>
                        <div>Method: <strong>{{ ucfirst($purchase->payment_method) }}</strong></div>
                        @if($purchase->bankAccount)
                        <div class="text-muted small">Bank: {{ $purchase->bankAccount->name }} ({{ $purchase->bankAccount->account_number }})</div>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Expiry Date</th>
                                <th>Batch Number</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Cost</th>
                                <th class="text-end">VAT</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $line)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $line->inventoryItem->name ?? 'Item' }}</div>
                                    <small class="text-muted">{{ $line->description }}</small>
                                </td>
                                <td>
                                    @if($line->expiry_date)
                                        {{ $line->expiry_date->format('d M Y') }}
                                        @if($line->expiry_date < now())
                                            <br><small class="text-danger">(Expired)</small>
                                        @elseif($line->expiry_date < now()->addDays(30))
                                            <br><small class="text-warning">(Expiring Soon)</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($line->batch_number)
                                        {{ $line->batch_number }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($line->quantity,2) }}</td>
                                <td class="text-end">TZS {{ number_format($line->unit_cost,2) }}</td>
                                <td class="text-end">
                                    @if($line->vat_type === 'no_vat')
                                        <span class="text-muted">No VAT</span>
                                    @else
                                        TZS {{ number_format($line->vat_amount,2) }}<br>
                                        <small class="text-muted">{{ number_format($line->vat_rate,2) }}% {{ $line->vat_type }}</small>
                                    @endif
                                </td>
                                <td class="text-end">TZS {{ number_format($line->line_total,2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Subtotal</th>
                                <th class="text-end">TZS {{ number_format($purchase->subtotal,2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end">VAT</th>
                                <th class="text-end">TZS {{ number_format($purchase->vat_amount,2) }}</th>
                            </tr>
                            @if(($purchase->discount_amount ?? 0) > 0)
                            <tr>
                                <th colspan="6" class="text-end">Discount</th>
                                <th class="text-end">- TZS {{ number_format($purchase->discount_amount,2) }}</th>
                            </tr>
                            @endif
                            <tr class="table-info">
                                <th colspan="6" class="text-end">Total</th>
                                <th class="text-end">TZS {{ number_format($purchase->total_amount,2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


