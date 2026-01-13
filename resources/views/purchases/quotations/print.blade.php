<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Quotation - {{ $quotation->reference }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .company-details {
            font-size: 10px;
            color: #666;
        }
        
        .quotation-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            color: #333;
        }
        
        .quotation-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .info-section {
            width: 48%;
        }
        
        .info-section h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #007bff;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        
        .info-section p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .info-section strong {
            font-weight: bold;
        }
        
        .quotation-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .quotation-details h3 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #007bff;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { background-color: #6c757d; color: white; }
        .status-sent { background-color: #17a2b8; color: white; }
        .status-approved { background-color: #28a745; color: white; }
        .status-rejected { background-color: #dc3545; color: white; }
        .status-expired { background-color: #ffc107; color: black; }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }
        
        .items-table td {
            font-size: 10px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .totals-section {
            margin-top: 20px;
        }
        
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .totals-table td {
            padding: 5px 10px;
            border: none;
        }
        
        .totals-table .label {
            text-align: right;
            font-weight: bold;
            width: 70%;
        }
        
        .totals-table .amount {
            text-align: right;
            width: 30%;
        }
        
        .total-row {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 14px;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .notes-section {
            margin-top: 30px;
        }
        
        .notes-section h3 {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #007bff;
        }
        
        .notes-section p {
            font-size: 10px;
            line-height: 1.4;
            margin: 5px 0;
        }
        
        .rfq-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .rfq-notice h3 {
            color: #856404;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .rfq-notice p {
            color: #856404;
            font-size: 11px;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ config('app.name', 'Smart Accounting') }}</div>
            <div class="company-details">
                Purchase Quotation System<br>
                Generated on {{ date('F j, Y \a\t g:i A') }}
            </div>
        </div>
        
        <div class="quotation-title">
            @if($quotation->is_request_for_quotation)
                REQUEST FOR QUOTATION (RFQ)
            @else
                PURCHASE QUOTATION
            @endif
        </div>
    </div>

    @if($quotation->is_request_for_quotation)
    <div class="rfq-notice">
        <h3>Request for Quotation</h3>
        <p>This is a request for quotation. Please provide your pricing for the items listed below.</p>
    </div>
    @endif

    <div class="quotation-info">
        <div class="info-section">
            <h3>Supplier Information</h3>
            <p><strong>Name:</strong> {{ $quotation->supplier->name }}</p>
            <p><strong>Email:</strong> {{ $quotation->supplier->email ?? 'N/A' }}</p>
            <p><strong>Phone:</strong> {{ $quotation->supplier->phone ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $quotation->supplier->address ?? 'N/A' }}</p>
        </div>
        
        <div class="info-section">
            <h3>Quotation Details</h3>
            <p><strong>Reference:</strong> {{ $quotation->reference }}</p>
            <p><strong>Start Date:</strong> {{ $quotation->start_date->format('M j, Y') }}</p>
            <p><strong>Due Date:</strong> {{ $quotation->due_date->format('M j, Y') }}</p>
            <p><strong>Status:</strong> 
                <span class="status-badge status-{{ $quotation->status }}">
                    {{ ucfirst($quotation->status) }}
                </span>
            </p>
            <p><strong>Created By:</strong> {{ $quotation->user->name }}</p>
            <p><strong>Branch:</strong> {{ $quotation->branch->name }}</p>
        </div>
    </div>

    <div class="quotation-details">
        <h3>Items</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Item Description</th>
                    <th style="width: 15%;" class="text-center">Quantity</th>
                    @if(!$quotation->is_request_for_quotation)
                        <th style="width: 15%;" class="text-right">Unit Price</th>
                        <th style="width: 15%;" class="text-right">VAT</th>
                        <th style="width: 15%;" class="text-right">Total</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($quotation->quotationItems as $item)
                <tr>
                    <td>
                        <strong>{{ $item->item->name }}</strong><br>
                        <small style="color: #666;">{{ $item->item->code }}</small>
                    </td>
                    <td class="text-center">{{ number_format($item->quantity, 2) }} {{ $item->item->unit_of_measure ?? 'units' }}</td>
                    @if(!$quotation->is_request_for_quotation)
                        <td class="text-right">TZS {{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">TZS {{ number_format($item->tax_amount, 2) }}</td>
                        <td class="text-right">TZS {{ number_format($item->total_amount, 2) }}</td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $quotation->is_request_for_quotation ? 2 : 5 }}" class="text-center" style="color: #666;">
                        No items found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(!$quotation->is_request_for_quotation)
    <div class="totals-section">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="amount">TZS {{ number_format($quotation->quotationItems->sum('total_amount'), 2) }}</td>
            </tr>
            @if($quotation->discount_amount > 0)
            <tr>
                <td class="label">Discount:</td>
                <td class="amount">TZS {{ number_format($quotation->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label">Total Amount:</td>
                <td class="amount">TZS {{ number_format($quotation->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>
    @endif

    @if($quotation->notes || $quotation->terms_conditions)
    <div class="notes-section">
        @if($quotation->notes)
        <h3>Notes</h3>
        <p>{{ $quotation->notes }}</p>
        @endif
        
        @if($quotation->terms_conditions)
        <h3>Terms & Conditions</h3>
        <p>{{ $quotation->terms_conditions }}</p>
        @endif
    </div>
    @endif

    <div class="footer">
        <p><strong>Generated by:</strong> {{ config('app.name', 'Smart Accounting') }}</p>
        <p><strong>Date:</strong> {{ date('F j, Y \a\t g:i A') }}</p>
        <p><strong>User:</strong> {{ $quotation->user->name }}</p>
        <p><strong>Branch:</strong> {{ $quotation->branch->name }}</p>
    </div>
</body>
</html>
