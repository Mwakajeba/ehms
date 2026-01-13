<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Payment Status Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            background: #fff;
            font-size: 11px;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #17a2b8;
            padding-bottom: 15px;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .logo-section {
            flex-shrink: 0;
        }

        .company-logo {
            max-height: 60px;
            max-width: 100px;
            object-fit: contain;
        }

        .title-section {
            text-align: center;
            flex-grow: 1;
        }

        .header h1 {
            color: #17a2b8;
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .company-name {
            color: #333;
            margin: 5px 0;
            font-size: 13px;
            font-weight: 600;
        }

        .header .subtitle {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 11px;
        }

        .report-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #17a2b8;
        }

        .report-info h3 {
            margin: 0 0 10px 0;
            color: #17a2b8;
            font-size: 13px;
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
            font-size: 11px;
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
            font-size: 11px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border: 1px solid #dee2e6;
            table-layout: fixed;
        }

        .data-table thead {
            background: #17a2b8;
            color: white;
        }

        .data-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            border: 1px solid #dee2e6;
        }

        .data-table th:nth-child(1) { width: 10%; }
        .data-table th:nth-child(2) { width: 15%; }
        .data-table th:nth-child(3) { width: 10%; }
        .data-table th:nth-child(4) { width: 12%; }
        .data-table th:nth-child(5) { width: 10%; }
        .data-table th:nth-child(6) { width: 10%; }
        .data-table th:nth-child(7) { width: 10%; }
        .data-table th:nth-child(8) { width: 10%; }
        .data-table th:nth-child(9) { width: 13%; }

        .data-table td {
            padding: 8px 6px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            word-wrap: break-word;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .data-table tfoot {
            background: #f8f9fa;
            font-weight: bold;
        }

        .data-table tfoot td {
            border: 1px solid #dee2e6;
            padding: 10px 6px;
        }

        .number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .text-success {
            color: #28a745;
            font-weight: 600;
        }

        .text-danger {
            color: #dc3545;
            font-weight: 600;
        }

        .text-info {
            color: #17a2b8;
            font-weight: 600;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 500;
        }

        .status-overdue {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: 500;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #17a2b8;
            margin: 30px 0 15px 0;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 5px;
        }

        .summary-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-row {
            display: table-row;
        }

        .summary-cell {
            display: table-cell;
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 11px;
        }

        .summary-header {
            background-color: #17a2b8;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            @php
                $company = \App\Models\Company::find(auth()->user()->company_id);
                $generatedAt = now();
            @endphp
            @if($company && $company->logo)
                <div class="logo-section">
                    <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
                </div>
            @endif
            <div class="title-section">
                <h1>Fee Payment Status Report</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                <div class="subtitle">Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="report-info">
        <h3>Report Parameters</h3>
        <div class="info-grid">
            @if($academicYearId)
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value">{{ \App\Models\School\AcademicYear::find($academicYearId)->year_name ?? 'N/A' }}</div>
            </div>
            @endif
            @if($classId)
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ \App\Models\School\Classe::find($classId)->name ?? 'N/A' }}</div>
            </div>
            @endif
            @if($streamId)
            <div class="info-row">
                <div class="info-label">Stream:</div>
                <div class="info-value">{{ \App\Models\School\Stream::find($streamId)->name ?? 'N/A' }}</div>
            </div>
            @endif
            @if($quarter)
            <div class="info-row">
                <div class="info-label">Quarter:</div>
                <div class="info-value">Quarter {{ $quarter }}</div>
            </div>
            @endif
            @if($status)
            <div class="info-row">
                <div class="info-label">Status Filter:</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $status)) }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="summary-section">
        <h3 style="margin: 0 0 15px 0; color: #17a2b8;">Summary Statistics</h3>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell summary-header">Total Invoices</div>
                <div class="summary-cell">{{ number_format($feeData['summary']['total_invoices']) }}</div>
                <div class="summary-cell summary-header">Paid Invoices</div>
                <div class="summary-cell">{{ number_format($feeData['summary']['paid_invoices']) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-header">Partial Paid</div>
                <div class="summary-cell">{{ number_format($feeData['summary']['partial_paid_invoices']) }}</div>
                <div class="summary-cell summary-header">Overdue Invoices</div>
                <div class="summary-cell">{{ number_format($feeData['summary']['overdue_invoices']) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-header">Total Amount</div>
                <div class="summary-cell">{{ number_format($feeData['summary']['total_amount'], 2) }}</div>
                <div class="summary-cell summary-header">Total Paid</div>
                <div class="summary-cell">{{ number_format($feeData['summary']['total_paid'], 2) }}</div>
            </div>
            <div class="summary-row">
                <div class="summary-cell summary-header">Outstanding Amount</div>
                <div class="summary-cell" colspan="3">{{ number_format($feeData['summary']['total_outstanding'], 2) }}</div>
            </div>
        </div>
    </div>

    @if($feeData['class_summary']->isNotEmpty())
    <h3 class="section-title">Class-wise Summary</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Class</th>
                <th class="number">Total Invoices</th>
                <th class="number">Paid</th>
                <th class="number">Partial Paid</th>
                <th class="number">Overdue</th>
                <th class="number">Total Amount</th>
                <th class="number">Paid Amount</th>
                <th class="number">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @foreach($feeData['class_summary'] as $className => $summary)
                <tr>
                    <td>{{ $className }}</td>
                    <td class="number">{{ number_format($summary['total_invoices']) }}</td>
                    <td class="number">{{ number_format($summary['paid_count']) }}</td>
                    <td class="number">{{ number_format($summary['partial_paid_count']) }}</td>
                    <td class="number">{{ number_format($summary['overdue_count']) }}</td>
                    <td class="number">{{ number_format($summary['total_amount'], 2) }}</td>
                    <td class="number">{{ number_format($summary['total_paid'], 2) }}</td>
                    <td class="number">{{ number_format($summary['total_outstanding'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <h3 class="section-title">Detailed Fee Invoices</h3>
    @if($feeData['invoices']->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Quarter</th>
                    <th>Academic Year</th>
                    <th class="number">Total Amount</th>
                    <th class="number">Paid Amount</th>
                    <th class="number">Outstanding</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($feeData['invoices'] as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_number }}</td>
                    <td>{{ $invoice->student ? $invoice->student->first_name . ' ' . $invoice->student->last_name : 'N/A' }}</td>
                    <td>{{ $invoice->classe ? $invoice->classe->name : 'N/A' }}</td>
                    <td>{{ $invoice->student && $invoice->student->stream ? $invoice->student->stream->name : 'N/A' }}</td>
                    <td>{{ $invoice->period ? 'Quarter ' . $invoice->period : 'N/A' }}</td>
                    <td>{{ $invoice->academicYear ? $invoice->academicYear->year_name : 'N/A' }}</td>
                    <td class="number">{{ number_format($invoice->total_amount, 2) }}</td>
                    <td class="number">{{ number_format($invoice->paid_amount, 2) }}</td>
                    <td class="number">{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</td>
                    <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @if($invoice->paid_amount >= $invoice->total_amount)
                            <span class="status-paid">Paid</span>
                        @elseif($invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount)
                            <span class="status-pending">Partial Paid</span>
                        @elseif($invoice->due_date && $invoice->due_date < now())
                            <span class="status-overdue">Overdue</span>
                        @else
                            <span class="status-pending">Pending</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No fee invoices found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 9px; margin-top: 5px;">Status indicators: Green (Paid), Yellow (Partial Paid), Red (Overdue).</p>
    </div>
</body>
</html>