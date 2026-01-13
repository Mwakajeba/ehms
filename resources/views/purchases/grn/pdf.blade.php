<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Receipt Note - {{ $grn->grn_number ?? ('GRN-' . str_pad($grn->id, 6, '0', STR_PAD_LEFT)) }}</title>
    <style>
        @php
            $docPageSize = \App\Models\SystemSetting::getValue('document_page_size', 'A5');
            $docOrientation = \App\Models\SystemSetting::getValue('document_orientation', 'portrait');
            $docMarginTop = \App\Models\SystemSetting::getValue('document_margin_top', '2.54cm');
            $docMarginRight = \App\Models\SystemSetting::getValue('document_margin_right', '2.54cm');
            $docMarginBottom = \App\Models\SystemSetting::getValue('document_margin_bottom', '2.54cm');
            $docMarginLeft = \App\Models\SystemSetting::getValue('document_margin_left', '2.54cm');
            $docFontFamily = \App\Models\SystemSetting::getValue('document_font_family', 'DejaVu Sans');
            $docFontSize = (int) (\App\Models\SystemSetting::getValue('document_base_font_size', 10));
            $docLineHeight = \App\Models\SystemSetting::getValue('document_line_height', '1.4');
            $docTextColor = \App\Models\SystemSetting::getValue('document_text_color', '#000000');
            $docBgColor = \App\Models\SystemSetting::getValue('document_background_color', '#FFFFFF');
            $docHeaderColor = \App\Models\SystemSetting::getValue('document_header_color', '#000000');
            $docAccentColor = \App\Models\SystemSetting::getValue('document_accent_color', '#b22222');
            $docTableHeaderBg = \App\Models\SystemSetting::getValue('document_table_header_bg', '#f2f2f2');
            $docTableHeaderText = \App\Models\SystemSetting::getValue('document_table_header_text', '#000000');
            $pageSizeCss = $docPageSize . ' ' . $docOrientation;
            $company = $grn->company;
        @endphp
        @page { size: {{ $pageSizeCss }}; margin: {{ $docMarginTop }} {{ $docMarginRight }} {{ $docMarginBottom }} {{ $docMarginLeft }}; }
        body { font-family: '{{ $docFontFamily }}', sans-serif; font-size: {{ $docFontSize }}px; line-height: {{ $docLineHeight }}; color: {{ $docTextColor }}; background-color: {{ $docBgColor }}; margin:0; }
        .header { text-align:center; border-bottom:1px solid #000; margin-bottom:8px; padding-bottom:6px; }
        .company-logo { display:flex; justify-content:center; align-items:center; gap:8px; margin-bottom:4px; }
        .company-logo img { width:35px; height:35px; }
        .company-name { color: {{ $docAccentColor }}; font-size: 15px; font-weight:bold; margin:0; }
        .company-details { font-size: 9px; line-height:1.3; margin-top:2px; }
        .doc-title { text-transform: uppercase; font-weight: bold; margin: 6px 0 0 0; font-size: 13px; color: {{ $docHeaderColor }}; }
        .row { display:flex; gap:12px; }
        .col { flex:1; }
        .section { margin-bottom:8px; }
        .section h4 { font-size: 11px; margin: 0 0 6px 0; color: {{ $docHeaderColor }}; }
        .meta { font-size:10px; }
        .table { width:100%; border-collapse: collapse; }
        .table th, .table td { border:1px solid #000; padding:4px; font-size:10px; }
        .table thead th { background: {{ $docTableHeaderBg }}; color: {{ $docTableHeaderText }}; }
        .text-right { text-align:right; }
        .text-center { text-align:center; }
        .mt-2 { margin-top:8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-logo">
            @if($company && $company->logo)
                <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo" />
            @endif
            <div>
                <div class="company-name">{{ $company->name ?? 'Company' }}</div>
                <div class="company-details">
                    {{ $company->address ?? '' }}<br>
                    {{ $company->email ?? '' }} {{ $company->phone ? ' | ' . $company->phone : '' }}
                </div>
            </div>
        </div>
        <div class="doc-title">Goods Receipt Note</div>
    </div>

    <div class="section meta">
        <div class="row">
            <div class="col">
                <strong>GRN No:</strong> {{ $grn->grn_number ?? ('GRN-' . str_pad($grn->id, 6, '0', STR_PAD_LEFT)) }}<br>
                <strong>Receipt Date:</strong> {{ $grn->receipt_date?->format('M d, Y') }}<br>
                <strong>Status:</strong> {{ strtoupper(str_replace('_',' ',$grn->status)) }}
            </div>
            <div class="col">
                <strong>Branch:</strong> {{ $grn->branch->name ?? 'N/A' }}<br>
                <strong>Location:</strong> {{ $grn->warehouse->name ?? 'N/A' }}<br>
                <strong>Received By:</strong> {{ $grn->receivedByUser->name ?? 'N/A' }}
            </div>
            <div class="col">
                <strong>Supplier:</strong> {{ optional(optional($grn->purchaseOrder)->supplier)->name ?? 'N/A' }}<br>
                <strong>Order:</strong> @if($grn->purchaseOrder)
                    {{ $grn->purchaseOrder->order_number ?? ('PO-' . str_pad($grn->purchaseOrder->id, 6, '0', STR_PAD_LEFT)) }}
                @else
                    Standalone GRN
                @endif
            </div>
        </div>
    </div>

    <div class="section meta">
        <h4>History</h4>
        @if($grn->purchaseOrder)
            <div>
                <strong>Origin:</strong> Converted from Purchase Order
                {{ $grn->purchaseOrder->order_number ?? ('PO-' . str_pad($grn->purchaseOrder->id, 6, '0', STR_PAD_LEFT)) }}
                dated {{ $grn->purchaseOrder->order_date?->format('M d, Y') ?? 'N/A' }} for supplier {{ $grn->purchaseOrder->supplier->name ?? 'N/A' }}.
            </div>
        @else
            <div>
                <strong>Origin:</strong> Standalone GRN (not created from a Purchase Order).
            </div>
        @endif
        <div>
            <strong>Created On:</strong> {{ $grn->created_at?->format('M d, Y H:i') ?? 'N/A' }}
            @if($grn->receivedByUser)
                &nbsp;|&nbsp; <strong>Created/Received By:</strong> {{ $grn->receivedByUser->name }}
            @endif
        </div>
    </div>

    <div class="section">
        <h4>Items</h4>
        <table class="table">
            <thead>
                <tr>
                    <th style="width:40%">Item</th>
                    <th class="text-right" style="width:12%">Qty Ordered</th>
                    <th class="text-right" style="width:12%">Qty Received</th>
                    <th class="text-right" style="width:14%">Unit Cost</th>
                    <th class="text-right" style="width:14%">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grn->items as $it)
                <tr>
                    <td>{{ optional($it->inventoryItem)->name ?? '-' }}</td>
                    <td class="text-right">{{ number_format($it->quantity_ordered, 2) }}</td>
                    <td class="text-right">{{ number_format($it->quantity_received, 2) }}</td>
                    <td class="text-right">{{ number_format($it->unit_cost, 2) }}</td>
                    <td class="text-right">{{ number_format($it->total_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Total Quantity</th>
                    <th class="text-right">{{ number_format($grn->total_quantity ?? $grn->items->sum('quantity_received'), 2) }}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">Total Amount</th>
                    <th class="text-right">TZS {{ number_format($grn->total_amount, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @if($grn->notes)
    <div class="section">
        <h4>Notes</h4>
        <div class="meta">{{ $grn->notes }}</div>
    </div>
    @endif

    <script>
        // Auto-print in browser
        if (typeof window !== 'undefined') {
            window.onload = function(){ window.print(); }
        }
    </script>
</body>
</html>


