<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note - {{ $delivery->delivery_number }}</title>
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

        .company-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 3px;
        }

        .company-logo img {
            width: 35px;
            height: 35px;
            margin-right: 8px;
        }

        .company-details {
            font-size: 8px;
            line-height: 1.3;
            margin-top: 2px;
        }

        .mobile-money {
            font-size: 9px;
            margin-top: 5px;
        }

        .mobile-money strong {
            color: #000;
        }

        /* === DELIVERY TITLE === */
        .delivery-title {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            margin: 8px 0;
            text-transform: uppercase;
        }

        /* === INFO SECTION === */
        .delivery-details {
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            margin-bottom: 8px;
        }

        .deliver-to {
            flex: 1;
        }

        .delivery-info {
            flex: 1;
            text-align: right;
        }

        .delivery-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .delivery-info td {
            border: 1px solid #000;
            padding: 2px;
        }

        .delivery-info td:first-child {
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

        .amount-in-words {
            font-size: 8px;
            font-style: italic;
            margin-top: 3px;
        }

        /* === DELIVERY SECTION === */
        .delivery-section {
            font-size: 9px;
            margin-top: 8px;
        }

        .delivery-row {
            display: flex;
            justify-content: space-between;
        }

        /* === TERMS & FOOTER === */
        .delivery-terms {
            font-size: 8px;
            margin-top: 8px;
        }

        .delivery-terms h6 {
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

        .terms ol {
            margin: 2px 0 0 15px;
            padding: 0;
        }

        .terms li {
            margin-bottom: 2px;
        }

        .page-info {
            text-align: center;
            font-size: 8px;
            margin-top: 8px;
        }

        .expiry-date {
            font-size: 7px;
        }

        /* Print-specific styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            .document-container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
                margin: 0;
            }
            
            .header {
                background: #667eea !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .items-table th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            
            .summary-row.total {
                border-top: 1px solid #000 !important;
            }
        }

    </style>

</head>
<body>
    <div class="print-container">
        <div class="header">
        <div class="company-info">
            <div class="company-logo">
                @if($delivery->company && $delivery->company->logo)
                <img src="{{ asset('storage/' . $delivery->company->logo) }}" alt="Logo">
                @endif
                <div>
                    <h1 class="company-name">{{ $delivery->company->name ?? 'SMARTACCOUNTING' }}</h1>
                </div>
            </div>
            <div class="company-details">
                <div><strong>P.O. Box:</strong> {{ $delivery->company->address ?? 'P.O.BOX 00000, City, Country' }}</div>
                <div><strong>Phone:</strong> {{ $delivery->company->phone ?? '+255 000 000 000' }}</div>
                <div><strong>Email:</strong> {{ $delivery->company->email ?? 'company@email.com' }}</div>
            </div>
            <div class="mobile-money">
                <div style="font-weight: bold; margin-bottom: 3px; font-size: 8px;">Please pay through one of the following methods:</div>
                @if(isset($bankAccounts) && $bankAccounts && $bankAccounts->count() > 0)
                <div style="display: flex; flex-wrap: wrap; gap: 8px; font-size: 8px;">
                    @foreach($bankAccounts as $account)
                    <div style="flex: 1; min-width: 120px;">
                        <strong>{{ strtoupper($account->name) }}:</strong> {{ $account->account_number }}
                    </div>
                    @endforeach
                </div>
                @else
                <div style="font-size: 8px;">No payment methods available</div>
                @endif
            </div>
        </div>
    </div>

    <div class="delivery-title">Delivery Note</div>

    <div class="delivery-details">
        <div class="deliver-to">
            <div class="field-label">Deliver To:</div>
            <div class="field-value">{{ $delivery->customer->name ?? 'Customer Name' }}</div>
            <br>
            <div class="field-value">{{ $delivery->delivery_address ?? 'Delivery Address' }}</div>
            <div class="field-label">Contact Person:</div>
            <div class="field-value">{{ $delivery->contact_person ?? 'N/A' }}</div>
            <div class="field-label">Phone:</div>
            <div class="field-value">{{ $delivery->contact_phone ?? 'N/A' }}</div>
        </div>
        <div class="delivery-info">
            <table style="width: 100%; border-collapse: collapse; font-size: 8px;">
                <tr>
                    <td style="padding: 2px; border: 1px solid #000; font-weight: bold; width: 20%;">Delivery No:</td>
                    <td style="padding: 2px; border: 1px solid #000; width: 30%;">{{ $delivery->delivery_number }}</td>
                    <td style="padding: 2px; border: 1px solid #000; font-weight: bold; width: 20%;">Date:</td>
                    <td style="padding: 2px; border: 1px solid #000; width: 30%;">{{ $delivery->delivery_date ? \Carbon\Carbon::parse($delivery->delivery_date)->format('d/m/Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px; border: 1px solid #000; font-weight: bold;">Currency:</td>
                    <td style="padding: 2px; border: 1px solid #000;">TZS</td>
                    <td style="padding: 2px; border: 1px solid #000; font-weight: bold;">Time:</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ $delivery->delivery_time ? \Carbon\Carbon::parse($delivery->delivery_time)->format('h:i A') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px; border: 1px solid #000; font-weight: bold;">TIN:</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ $delivery->company->tin ?? 'N/A' }}</td>
                    <td style="padding: 2px; border: 1px solid #000; font-weight: bold;">VRN:</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ $delivery->company->vrn ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 2px; border: 1px solid #000; font-weight: bold;">Status:</td>
                    <td style="padding: 2px; border: 1px solid #000;" colspan="3">{{ ucfirst($delivery->status) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 8%;">Qty</th>
                <th style="width: 45%;">Description</th>
                <th style="width: 12%;">Unit Price</th>
                <th style="width: 10%;">VAT</th>
                <th style="width: 15%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($delivery->items as $item)
            <tr>
                <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                <td>{{ $item->item_name }}</td>
                <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->vat_amount, 2) }}</td>
                <td class="text-right">{{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-row">
            <span>Subtotal:</span>
            <span>{{ number_format($delivery->items->sum('line_total') - $delivery->items->sum('vat_amount'), 2) }}</span>
        </div>
        @if($delivery->items->sum('vat_amount') > 0)
        <div class="summary-row">
            <span>VAT:</span>
            <span>{{ number_format($delivery->items->sum('vat_amount'), 2) }}</span>
        </div>
        @endif
        @if($delivery->has_transport_cost && $delivery->transport_cost > 0)
        <div class="summary-row">
            <span>Transport Cost:</span>
            <span>{{ number_format($delivery->transport_cost, 2) }}</span>
        </div>
        @endif
        <div class="summary-row total">
            <span>Total:</span>
            <span>{{ number_format($delivery->items->sum('line_total') + $delivery->transport_cost, 2) }}</span>
        </div>
    </div>

    @if($delivery->delivery_instructions)
    <div class="delivery-terms">
        <h6>Delivery Instructions:</h6>
        <div>{{ $delivery->delivery_instructions }}</div>
    </div>
    @endif

    @if($delivery->notes)
    <div class="delivery-terms">
        <h6>Notes:</h6>
        <div>{{ $delivery->notes }}</div>
    </div>
    @endif

    @if($delivery->sales_invoice_id)
    @php
    // Get the linked invoice and its payment history
    $invoice = \App\Models\Sales\SalesInvoice::find($delivery->sales_invoice_id);
    if($invoice) {
        $payments = $invoice->payments()->with(['user', 'bankAccount', 'cashDeposit.type'])->get();
        $receipts = $invoice->receipts()->with(['user', 'bankAccount'])->get();

        // Combine and sort by date
        $allPayments = collect();

        // Add payments
        foreach($payments as $payment) {
            $allPayments->push([
                'type' => 'payment',
                'data' => $payment,
                'date' => $payment->date,
                'amount' => $payment->amount,
                'description' => $payment->description,
                'user' => $payment->user,
                'bank_account_id' => $payment->bank_account_id,
                'cash_deposit_id' => $payment->cash_deposit_id,
                'bank_account' => $payment->bankAccount,
                'approved' => $payment->approved,
                'id' => $payment->id,
                'encoded_id' => $payment->hash_id
            ]);
        }

        // Add receipts
        foreach($receipts as $receipt) {
            $allPayments->push([
                'type' => 'receipt',
                'data' => $receipt,
                'date' => $receipt->date,
                'amount' => $receipt->amount,
                'description' => $receipt->description,
                'user' => $receipt->user,
                'bank_account_id' => $receipt->bank_account_id,
                'bank_account' => $receipt->bankAccount,
                'approved' => $receipt->approved,
                'id' => $receipt->id,
                'encoded_id' => $receipt->hash_id
            ]);
        }

        // Sort by date
        $allPayments = $allPayments->sortBy('date');
    }
    @endphp

    @if($invoice && $allPayments->count() > 0)
    <div class="payment-history">
        <h3 style="font-size: 11px; font-weight: bold; margin: 8px 0 5px 0; text-align: center; border-bottom: 1px solid #000; padding-bottom: 3px;">PAYMENT HISTORY (Invoice: {{ $invoice->invoice_number }})</h3>
        <table style="width: 100%; border-collapse: collapse; font-size: 8px; margin-bottom: 10px;">
            <thead>
                <tr style="background-color: #f5f5f5;">
                    <th style="border: 1px solid #000; padding: 3px; text-align: left; font-size: 8px;">Date</th>
                    <th style="border: 1px solid #000; padding: 3px; text-align: left; font-size: 8px;">Type</th>
                    <th style="border: 1px solid #000; padding: 3px; text-align: left; font-size: 8px;">Description</th>
                    <th style="border: 1px solid #000; padding: 3px; text-align: right; font-size: 8px;">Amount</th>
                    <th style="border: 1px solid #000; padding: 3px; text-align: left; font-size: 8px;">Method</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allPayments as $payment)
                <tr>
                    <td style="border: 1px solid #000; padding: 3px; font-size: 8px;">{{ $payment['date']->format('d/m/Y') }}</td>
                    <td style="border: 1px solid #000; padding: 3px; font-size: 8px; text-transform: uppercase;">{{ $payment['type'] }}</td>
                    <td style="border: 1px solid #000; padding: 3px; font-size: 8px;">{{ $payment['description'] ?? 'N/A' }}</td>
                    <td style="border: 1px solid #000; padding: 3px; font-size: 8px; text-align: right;">{{ number_format($payment['amount'], 2) }}</td>
                    <td style="border: 1px solid #000; padding: 3px; font-size: 8px;">
                        @if($payment['bank_account'])
                            {{ $payment['bank_account']->name }}
                        @elseif($payment['cash_deposit_id'] && isset($payment['data']->cashDeposit))
                            {{ $payment['data']->cashDeposit->type->name ?? 'Cash Deposit' }}
                        @else
                            Cash
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f0f0f0; font-weight: bold;">
                    <td colspan="3" style="border: 1px solid #000; padding: 3px; font-size: 8px; text-align: right;">Total Payments:</td>
                    <td style="border: 1px solid #000; padding: 3px; font-size: 8px; text-align: right;">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td style="border: 1px solid #000; padding: 3px; font-size: 8px;"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
    @endif

    @if($delivery->sales_invoice_id)
    @php
    // Get the linked invoice for early payment and amount due information
    $linkedInvoice = \App\Models\Sales\SalesInvoice::find($delivery->sales_invoice_id);
    @endphp

    @if($linkedInvoice && $linkedInvoice->early_payment_discount_enabled && $linkedInvoice->isEarlyPaymentDiscountValid() && $linkedInvoice->calculateEarlyPaymentDiscount() > 0)
    <div class="early-payment-section">
        <h3 style="font-size: 11px; font-weight: bold; margin: 8px 0 5px 0; text-align: center; border-bottom: 1px solid #000; padding-bottom: 3px;">EARLY PAYMENT DISCOUNT (Invoice: {{ $linkedInvoice->invoice_number }})</h3>
        <div style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px; margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span style="font-weight: bold;">Early Payment Discount:</span>
                <span style="font-weight: bold; color: #28a745;">{{ number_format($linkedInvoice->calculateEarlyPaymentDiscount(), 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Valid until:</span>
                <span>{{ $linkedInvoice->getEarlyPaymentDiscountExpiryDate()->format('d/m/Y') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Discount rate:</span>
                <span>{{ $linkedInvoice->early_payment_discount_rate }}{{ $linkedInvoice->early_payment_discount_type === 'percentage' ? '%' : ' TZS' }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px;">
                <span>Amount due with early payment discount:</span>
                <span style="color: #28a745; font-size: 12px;">{{ number_format($linkedInvoice->getAmountDueWithEarlyDiscount(), 2) }}</span>
            </div>
        </div>
    </div>
    @endif

    @if($linkedInvoice && $linkedInvoice->paid_amount > 0)
    <div class="amount-due-section">
        <h3 style="font-size: 11px; font-weight: bold; margin: 8px 0 5px 0; text-align: center; border-bottom: 1px solid #000; padding-bottom: 3px;">AMOUNT DUE (Invoice: {{ $linkedInvoice->invoice_number }})</h3>
        <div style="background-color: #fff3cd; border: 1px solid #000; padding: 8px; margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Total Invoice Amount:</span>
                <span>{{ number_format($linkedInvoice->total_amount, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                <span>Amount Paid:</span>
                <span style="color: #28a745;">{{ number_format($linkedInvoice->paid_amount, 2) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; border-top: 1px solid #000; padding-top: 5px; margin-top: 5px;">
                <span>Balance Due:</span>
                <span style="color: #dc3545; font-size: 12px;">{{ number_format($linkedInvoice->balance_due, 2) }}</span>
            </div>
        </div>
    </div>
    @endif
    @endif

    <div class="footer">
        <div class="signature-line">
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <div style="text-align: center; width: 45%;">
                    <div style="border-top: 1px solid #000; margin-top: 20px; padding-top: 5px;">
                        <strong>Driver Signature</strong>
                    </div>
                </div>
                <div style="text-align: center; width: 45%;">
                    <div style="border-top: 1px solid #000; margin-top: 20px; padding-top: 5px;">
                        <strong>Customer Signature</strong>
                    </div>
                </div>
            </div>
        </div>
        
        @if($delivery->driver_name)
        <div style="margin-top: 10px; font-size: 8px;">
            <strong>Driver:</strong> {{ $delivery->driver_name }}
            @if($delivery->driver_phone)
            | <strong>Phone:</strong> {{ $delivery->driver_phone }}
            @endif
        </div>
        @endif

        @if($delivery->vehicle_number)
        <div style="margin-top: 5px; font-size: 8px;">
            <strong>Vehicle:</strong> {{ $delivery->vehicle_number }}
        </div>
        @endif

        @if($delivery->received_by_name)
        <div style="margin-top: 10px; font-size: 8px;">
            <strong>Received By:</strong> {{ $delivery->received_by_name }}
            @if($delivery->received_at)
            | <strong>Date:</strong> {{ \Carbon\Carbon::parse($delivery->received_at)->format('d/m/Y h:i A') }}
            @endif
        </div>
        @endif
    </div>

    <div class="page-info">
        <div>Generated on {{ now()->format('d/m/Y h:i A') }}</div>
    </div>

    <!-- Print Button -->
    <div style="text-align: center; margin-top: 20px; padding: 20px;">
        <button onclick="window.print()" style="
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            margin-right: 10px;
        ">Print</button>
        <button onclick="window.close()" style="
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
        ">Close</button>
    </div>
    </div> <!-- Close print-container -->

    <script>
        // Auto-print when opened in print window
        if (window.opener) {
            setTimeout(function() {
                window.print();
            }, 1000);
        }
    </script>

</body>
</html>