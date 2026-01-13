<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .container { width: 100%; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
        .brand { display: flex; gap: 10px; align-items: center; }
        .logo { width: 44px; height: 44px; border-radius: 6px; background: #111827; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; }
        .company { font-weight: 700; font-size: 16px; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 22px; letter-spacing: .5px; }
        .muted { color: #6b7280; }
        .grid { width: 100%; display: table; table-layout: fixed; }
        .grid > div { display: table-cell; vertical-align: top; }
        .w-50 { width: 50%; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        .mb-4 { margin-bottom: 16px; }
        .table { width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb; }
        .table th, .table td { border-bottom: 1px solid #e5e7eb; padding: 8px; }
        .table th { background: #f9fafb; text-align: left; font-weight: 700; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { width: 45%; margin-left: auto; border: 1px solid #e5e7eb; border-top: none; }
        .totals td { padding: 8px 10px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 9999px; background: #eef2ff; color: #3730a3; font-size: 11px; font-weight: 700; }
        .small { font-size: 11px; }
        .border { border: 1px solid #e5e7eb; }
        .p-2 { padding: 8px; }
        .note { background: #f9fafb; border: 1px solid #e5e7eb; padding: 8px; border-radius: 4px; }
        .divider { height: 2px; background: #111827; margin: 8px 0 12px; opacity: .06; }
    </style>
    </head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">
                <div class="logo">SF</div>
                <div class="company">
                    {{ optional($invoice->company)->name ?? 'Company' }}<br>
                    <span class="muted small">{{ optional($invoice->branch)->name }}</span>
                </div>
            </div>
            <div class="doc-title">
                <h1>Purchase Invoice</h1>
                <div class="muted">No: {{ $invoice->invoice_number }}</div>
                <div class="muted">Date: {{ optional($invoice->invoice_date)->format('Y-m-d') }}</div>
                @if($invoice->due_date)
                    <div class="muted">Due: {{ optional($invoice->due_date)->format('Y-m-d') }}</div>
                @endif
                <div class="badge">{{ strtoupper($invoice->status) }}</div>
            </div>
        </div>

        <div class="grid mb-4">
            <div class="w-50">
                <div class="border p-2">
                    <strong>Supplier</strong><br>
                    {{ optional($invoice->supplier)->name ?? 'N/A' }}<br>
                    <span class="muted small">TIN: {{ optional($invoice->supplier)->tin ?? '—' }}</span>
                </div>
            </div>
            <div class="w-50">
                <div class="border p-2">
                    <strong>Currency</strong><br>
                    {{ $invoice->currency }} @ {{ number_format((float)$invoice->exchange_rate, 6) }}
                </div>
            </div>
        </div>

        <div class="divider"></div>
        <table class="table mb-3">
            <thead>
                <tr>
                    <th style="width: 40%">Item</th>
                    <th class="text-right" style="width: 12%">Qty</th>
                    <th class="text-right" style="width: 16%">Unit Cost</th>
                    <th class="text-right" style="width: 12%">VAT</th>
                    <th class="text-right" style="width: 20%">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $line)
                    @php
                        $name = optional($line->inventoryItem)->name ?: ($line->description ?: 'Line item');
                        $vatLabel = $line->vat_type === 'no_vat' ? 'No VAT' : (($line->vat_type ?? 'exclusive') . ' ' . (float)$line->vat_rate . '%');
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $name }}</strong>
                            @if($line->description)
                                <div class="muted small">{{ $line->description }}</div>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format((float)$line->quantity, 2) }}</td>
                        <td class="text-right">{{ number_format((float)$line->unit_cost, 2) }}</td>
                        <td class="text-right">{{ $vatLabel }}</td>
                        <td class="text-right">{{ number_format((float)$line->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="text-right muted">Subtotal</td>
                <td class="text-right">{{ number_format((float)$invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="text-right muted">VAT</td>
                <td class="text-right">{{ number_format((float)$invoice->vat_amount, 2) }}</td>
            </tr>
            @if((float)($invoice->discount_amount ?? 0) > 0)
            <tr>
                <td class="text-right muted">Discount</td>
                <td class="text-right">-{{ number_format((float)$invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format((float)$invoice->total_amount, 2) }}</strong></td>
            </tr>
        </table>

        <div class="mb-3">
            <strong>Notes</strong>
            <div class="note small">{{ $invoice->notes ?? '—' }}</div>
        </div>

        @if(($payments ?? collect())->count() > 0)
            <div class="mb-3">
                <strong>Payment History</strong>
                <table class="table" style="margin-top:6px">
                    <thead>
                        <tr>
                            <th style="width: 25%">Date</th>
                            <th style="width: 35%">Reference</th>
                            <th style="width: 25%">Method</th>
                            <th class="text-right" style="width: 15%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                            <tr>
                                <td>{{ optional($p->date)->format('Y-m-d') }}</td>
                                <td>{{ $p->reference }}</td>
                                <td>
                                    @php
                                        $m = $p->method ?? null;
                                        if (!$m && $p->bank_account_id) { $m = 'bank'; }
                                        $methodLabel = $m ? ucfirst($m) : 'N/A';
                                        $bankName = optional($p->bankAccount)->name;
                                    @endphp
                                    {{ $methodLabel }}
                                    @if($m === 'bank' && $bankName)
                                        <span class="muted small">({{ $bankName }})</span>
                                    @endif
                                </td>
                                <td class="text-right">{{ number_format((float)$p->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total Paid</strong></td>
                            <td class="text-right"><strong>{{ number_format((float)($totalPaid ?? 0), 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right">Balance Due</td>
                            <td class="text-right">{{ number_format((float)($balanceDue ?? 0), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <div class="small muted">Generated on {{ now()->format('Y-m-d H:i') }}</div>
    </div>
</body>
</html>


