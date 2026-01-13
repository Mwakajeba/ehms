<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Waivers & Discounts Report</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            background: #fff;
            font-size: 8px;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #ffc107;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #ffc107;
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
        }

        .company-name {
            color: #333;
            margin: 5px 0;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }

        .report-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
            font-size: 9px;
        }

        .summary-table th {
            background: #ffc107;
            color: #333;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #fff;
        }

        .summary-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        .class-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .class-header {
            background: #ffc107;
            color: #333;
            padding: 10px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 11px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
            font-size: 7px;
        }

        .data-table thead {
            background: #ffc107;
            color: #333;
        }

        .data-table th {
            padding: 6px 3px;
            text-align: center;
            font-weight: bold;
            font-size: 7px;
            border: 1px solid #fff;
        }

        .data-table td {
            padding: 5px 3px;
            border: 1px solid #dee2e6;
            font-size: 7px;
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-success {
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FEE WAIVERS & DISCOUNTS REPORT</h1>
        @if($company)
        <div class="company-name">{{ $company->name }}</div>
        @endif
        <div style="text-align: center; color: #666; font-size: 11px; margin-top: 5px;">
            Generated on: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <div class="report-info">
        <h3>Report Filters</h3>
        <div style="font-size: 9px;">
            Date From: {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} | 
            Date To: {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
            @if($academicYear) | Academic Year: {{ $academicYear->year_name }} @endif
            @if($class) | Class: {{ $class->name }} @endif
            @if($stream) | Stream: {{ $stream->name }} @endif
            @if($discountType) | Discount Type: {{ ucfirst($discountType) }} @endif
            @if($period) | Period: {{ $period }} @endif
        </div>
    </div>

    @if(!empty($waiversDiscountsData) && !empty($waiversDiscountsData['grand_totals']))
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total Invoices</th>
                <th>Total Subtotal</th>
                <th>Total Discounts</th>
                <th>After Discount</th>
                <th>Discount Rate (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($waiversDiscountsData['grand_totals']['total_invoices']) }}</td>
                <td class="text-end">{{ number_format($waiversDiscountsData['grand_totals']['total_subtotal'], 2) }}</td>
                <td class="text-end text-danger">{{ number_format($waiversDiscountsData['grand_totals']['total_discount_amount'], 2) }}</td>
                <td class="text-end text-success">{{ number_format($waiversDiscountsData['grand_totals']['total_after_discount'], 2) }}</td>
                <td style="font-weight: bold;">{{ number_format($waiversDiscountsData['grand_totals']['discount_percentage'], 2) }}%</td>
            </tr>
        </tbody>
    </table>
    @endif

    @if(!empty($waiversDiscountsData) && !empty($waiversDiscountsData['classes']))
        @foreach($waiversDiscountsData['classes'] as $classData)
        <div class="class-section">
            <div class="class-header">
                {{ $classData['class_name'] }} - Invoices: {{ $classData['total_invoices'] }} | 
                Subtotal: {{ number_format($classData['total_subtotal'], 2) }} | 
                Discounts: {{ number_format($classData['total_discount_amount'], 2) }} | 
                Rate: {{ number_format($classData['discount_percentage'], 2) }}%
            </div>

            @if(!empty($classData['streams']))
                @foreach($classData['streams'] as $streamData)
                <div style="margin-bottom: 10px;">
                    <div style="background: #ffd54f; padding: 8px; margin-bottom: 5px; font-weight: 500; font-size: 9px;">
                        {{ $streamData['stream_name'] }}
                    </div>

                    @if(!empty($streamData['invoices']))
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hash ID</th>
                                <th>Invoice #</th>
                                <th>Student</th>
                                <th>Admission #</th>
                                <th>Period</th>
                                <th>Issue Date</th>
                                <th>Subtotal</th>
                                <th>Discount Type</th>
                                <th>Discount Value</th>
                                <th>Discount Amount</th>
                                <th>After Discount</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($streamData['invoices'] as $invoice)
                            <tr>
                                <td>{{ $invoice['hash_id'] }}</td>
                                <td>{{ $invoice['invoice_number'] }}</td>
                                <td>{{ $invoice['student_name'] }}</td>
                                <td>{{ $invoice['admission_number'] }}</td>
                                <td>{{ $invoice['period'] }}</td>
                                <td>{{ $invoice['issue_date'] }}</td>
                                <td class="text-end">{{ number_format($invoice['subtotal'], 2) }}</td>
                                <td>{{ ucfirst($invoice['discount_type']) }}</td>
                                <td>
                                    @if($invoice['discount_type'] == 'percentage')
                                        {{ number_format($invoice['discount_value'], 2) }}%
                                    @elseif($invoice['discount_type'] == 'fixed')
                                        {{ number_format($invoice['discount_value'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-end text-danger">{{ number_format($invoice['discount_amount'], 2) }}</td>
                                <td class="text-end text-success">{{ number_format($invoice['after_discount'], 2) }}</td>
                                <td class="text-end">{{ number_format($invoice['total_amount'], 2) }}</td>
                            </tr>
                            @endforeach
                            <tr style="font-weight: bold; background-color: #f8f9fa;">
                                <td colspan="6" class="text-end">Stream Total</td>
                                <td class="text-end">{{ number_format($streamData['total_subtotal'], 2) }}</td>
                                <td colspan="2"></td>
                                <td class="text-end text-danger">{{ number_format($streamData['total_discount_amount'], 2) }}</td>
                                <td class="text-end text-success">{{ number_format($streamData['total_after_discount'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                    @endif
                </div>
                @endforeach
            @endif
        </div>
        @endforeach
    @else
        <p style="text-align: center; padding: 20px; color: #666;">No waivers or discounts found for the selected filters.</p>
    @endif
</body>
</html>

