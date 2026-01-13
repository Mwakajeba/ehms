<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Aging Report</title>
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
            font-size: 9px;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #667eea;
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
            border-left: 4px solid #667eea;
        }

        .report-info h3 {
            margin: 0 0 10px 0;
            color: #667eea;
            font-size: 14px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 15px 5px 0;
            width: 120px;
            color: #555;
            font-size: 10px;
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
            font-size: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background: #fff;
        }

        .summary-table th {
            background: #667eea;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #fff;
        }

        .summary-table td {
            padding: 8px;
            border: 1px solid #dee2e6;
            text-align: center;
            font-size: 10px;
        }

        .fee-group-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .fee-group-header {
            background: #667eea;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
            font-size: 8px;
        }

        .data-table thead {
            background: #667eea;
            color: white;
        }

        .data-table th {
            padding: 8px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 8px;
            border: 1px solid #fff;
        }

        .data-table td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            font-size: 8px;
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

        .text-warning {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>FEE AGING REPORT</h1>
        @if($company)
        <div class="company-name">{{ $company->name }}</div>
        @endif
        <div style="text-align: center; color: #666; font-size: 11px; margin-top: 5px;">
            Generated on: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <div class="report-info">
        <h3>Report Filters</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">As of Date:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</div>
            </div>
            @if($academicYear)
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value">{{ $academicYear->year_name }}</div>
            </div>
            @endif
            @if($class)
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ $class->name }}</div>
            </div>
            @endif
            @if($stream)
            <div class="info-row">
                <div class="info-label">Stream:</div>
                <div class="info-value">{{ $stream->name }}</div>
            </div>
            @endif
            @if($feeGroup)
            <div class="info-row">
                <div class="info-label">Fee Group:</div>
                <div class="info-value">{{ $feeGroup->name }}</div>
            </div>
            @endif
        </div>
    </div>

    @if(!empty($agingData) && !empty($agingData['grand_totals']))
    <table class="summary-table">
        <thead>
            <tr>
                <th>Current</th>
                <th>0-30 Days</th>
                <th>31-60 Days</th>
                <th>61-90 Days</th>
                <th>91+ Days</th>
                <th>Total Outstanding</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-success">{{ number_format($agingData['grand_totals']['current'], 2) }}</td>
                <td class="text-warning">{{ number_format($agingData['grand_totals']['0-30'], 2) }}</td>
                <td>{{ number_format($agingData['grand_totals']['31-60'], 2) }}</td>
                <td>{{ number_format($agingData['grand_totals']['61-90'], 2) }}</td>
                <td class="text-danger">{{ number_format($agingData['grand_totals']['91+'], 2) }}</td>
                <td style="font-weight: bold;">{{ number_format($agingData['grand_totals']['total_outstanding'], 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @if(!empty($agingData) && !empty($agingData['fee_groups']))
        @foreach($agingData['fee_groups'] as $feeGroup)
        <div class="fee-group-section">
            <div class="fee-group-header">
                {{ $feeGroup['fee_group_name'] }} @if($feeGroup['fee_group_code'])({{ $feeGroup['fee_group_code'] }})@endif
            </div>

            <table class="summary-table">
                <thead>
                    <tr>
                        <th>Current</th>
                        <th>0-30 Days</th>
                        <th>31-60 Days</th>
                        <th>61-90 Days</th>
                        <th>91+ Days</th>
                        <th>Total Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-success">{{ number_format($feeGroup['current'], 2) }}</td>
                        <td class="text-warning">{{ number_format($feeGroup['0-30'], 2) }}</td>
                        <td>{{ number_format($feeGroup['31-60'], 2) }}</td>
                        <td>{{ number_format($feeGroup['61-90'], 2) }}</td>
                        <td class="text-danger">{{ number_format($feeGroup['91+'], 2) }}</td>
                        <td style="font-weight: bold;">{{ number_format($feeGroup['total_outstanding'], 2) }}</td>
                    </tr>
                </tbody>
            </table>

            @if(!empty($feeGroup['invoices']))
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Stream</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Outstanding</th>
                        <th>Aging</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($feeGroup['invoices'] as $invoice)
                    <tr>
                        <td>{{ $invoice['invoice_number'] }}</td>
                        <td>{{ $invoice['student_name'] }}</td>
                        <td>{{ $invoice['class_name'] }}</td>
                        <td>{{ $invoice['stream_name'] }}</td>
                        <td>{{ $invoice['issue_date'] }}</td>
                        <td>{{ $invoice['due_date'] }}</td>
                        <td class="{{ $invoice['days_overdue'] > 0 ? 'text-danger' : 'text-success' }}">{{ $invoice['days_overdue'] }}</td>
                        <td class="text-end">{{ number_format($invoice['total_amount'], 2) }}</td>
                        <td class="text-end">{{ number_format($invoice['paid_amount'], 2) }}</td>
                        <td class="text-end" style="font-weight: bold;">{{ number_format($invoice['outstanding_amount'], 2) }}</td>
                        <td>{{ ucfirst(str_replace('-', ' ', $invoice['aging_bucket'])) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        @endforeach
    @else
        <p style="text-align: center; padding: 20px; color: #666;">No outstanding fees found for the selected filters.</p>
    @endif
</body>
</html>

