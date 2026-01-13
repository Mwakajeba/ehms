<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $order->order_number }}</title>
    <style>
        @page {
            size: A5;
            margin: 0.5in 0.5in 0.5in 0.5in;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 10px;
            color: #000;
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100vh;
        }

        .print-container {
            width: 100%;
            max-width: 100%;
            margin: 0;
            padding: 0;
            position: relative;
            top: 0;
            left: 0;
        }

        @media print {
            @page {
                size: A4;
                margin: 0.5in 0.5in 0.5in 0.5in;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .print-container {
                margin: 0 !important;
                padding: 0 !important;
                position: relative !important;
                top: 0 !important;
                left: 0 !important;
            }
        }

        /* === HEADER === */
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            padding-bottom: 5px;
            margin-top: 0;
            padding-top: 0;
        }

        .company-name {
            color: #b22222;
            font-size: 15px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .company-details {
            font-size: 8px;
            line-height: 1.3;
            margin-top: 2px;
        }

        /* === ORDER TITLE === */
        .order-title {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin: 8px 0;
            text-transform: uppercase;
        }

        /* === INFO SECTION === */
        .order-details {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            margin-bottom: 8px;
        }

        .supplier-info {
            flex: 1;
        }

        .order-info {
            flex: 1;
            text-align: right;
        }

        .order-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .order-info td {
            border: 1px solid #000;
            padding: 2px;
        }

        .order-info td:first-child {
            font-weight: bold;
            background-color: #f9f9f9;
        }

        /* === ITEMS TABLE === */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 8px;
        }

        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* === TOTALS === */
        .summary {
            margin-top: 5px;
            font-size: 9px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .summary-row.total {
            border-top: 1px solid #000;
            font-weight: bold;
            padding-top: 3px;
        }

        /* === TERMS & FOOTER === */
        .payment-terms {
            font-size: 8px;
            margin-top: 8px;
        }

        .payment-terms h6 {
            margin: 0 0 2px 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .footer {
            font-size: 8px;
            margin-top: 10px;
        }

        .signature-line {
            margin-top: 8px;
        }

        .page-info {
            text-align: center;
            font-size: 8px;
            margin-top: 8px;
        }

        /* === QUOTATION INFO === */
        .quotation-info {
            background-color: #fff3cd;
            border: 1px solid #000;
            padding: 5px;
            margin-bottom: 8px;
            font-size: 8px;
        }

        .quotation-info strong {
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="header">
            <div class="company-info">
                <h1 class="company-name">SMARTACCOUNTING</h1>
                <div class="company-details">
                    <div><strong>P.O. Box:</strong> P.O.BOX 00000, City, Country</div>
                    <div><strong>Phone:</strong> +255 000 000 000</div>
                    <div><strong>Email:</strong> company@email.com</div>
                </div>
            </div>
        </div>
        
        <div class="order-title">Purchase Order</div>
        
        @if($order->quotation)
        <div class="quotation-info">
            <strong>Converted from Quotation:</strong> {{ $order->quotation->reference ?? 'N/A' }}
        </div>
        @endif
        
        <div class="order-details">
            <div class="supplier-info">
                <div class="field-label">Supplier:</div>
                <div class="field-value">{{ $order->supplier->name }}</div>
                <br>
                <div class="field-value">{{ $order->supplier->address ?? 'N/A' }}</div>
                <div class="field-label">Created by:</div>
                <div class="field-value">{{ $order->createdBy->name ?? 'N/A' }}</div>
            </div>
            <div class="order-info">
                <table>
                    <tr>
                        <td>Order No:</td><td>{{ $order->order_number }}</td>
                        <td>Date:</td><td>{{ $order->order_date->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td>Currency:</td><td>TZS</td>
                        <td>Ex Rate:</td><td>1.00</td>
                    </tr>
                    <tr>
                        <td>Expected Delivery:</td><td>{{ $order->expected_delivery_date->format('M d, Y') }}</td>
                        <td>Status:</td><td>{{ ucfirst($order->status) }}</td>
                    </tr>
                    <tr>
                        <td>Payment Terms:</td><td>{{ ucfirst(str_replace('_', ' ', $order->payment_terms)) }}</td>
                        <td>Payment Days:</td><td>{{ $order->payment_days ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Time:</td><td colspan="3">{{ now()->format('g:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Qty</th>
                    <th style="width: 45%;">Description</th>
                    <th style="width: 12%;">VAT Rate</th>
                    <th style="width: 15%;">Unit price</th>
                    <th style="width: 10%;">VAT Amt</th>
                    <th style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->item->name }}</td>
                    <td class="text-center">
                        @if($item->vat_type === 'no_vat')
                            0.00%
                        @else
                            {{ number_format($item->vat_rate, 2) }}%
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->cost_price, 2) }}</td>
                    <td class="text-right">{{ number_format($item->vat_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    
        <div class="summary">
            <div class="summary-row"><span>Amount without tax:</span><span>TZS {{ number_format($order->subtotal, 2) }}</span></div>
            <div class="summary-row"><span>Total Tax:</span><span>TZS {{ number_format($order->vat_amount, 2) }}</span></div>
            @if($order->tax_amount > 0)
            <div class="summary-row"><span>Additional Tax:</span><span>TZS {{ number_format($order->tax_amount, 2) }}</span></div>
            @endif
            @if($order->discount_amount > 0)
            <div class="summary-row"><span>Total Discount:</span><span>TZS {{ number_format($order->discount_amount, 2) }}</span></div>
            @endif
            <div class="summary-row total"><span>Total Amount:</span><span>TZS {{ number_format($order->total_amount, 2) }}</span></div>
        </div>
        
        @if($order->notes || $order->terms_conditions)
        <div class="payment-terms">
            @if($order->notes)
            <h6>Notes</h6>
            <div>{{ $order->notes }}</div>
            @endif
            
            @if($order->terms_conditions)
            <h6>Terms & Conditions</h6>
            <div>{{ $order->terms_conditions }}</div>
            @endif
        </div>
        @endif
        
        <div class="footer">
            <div class="signature-line"><strong>Signature................................................</strong></div>
            <div class="page-info">Generated on {{ now()->format('M d, Y \a\t g:i A') }}</div>
        </div>
    </div>
</body>
</html>
