<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Invoices Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            background: #fff;
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
            max-height: 80px;
            max-width: 120px;
            object-fit: contain;
        }
        
        .title-section {
            text-align: center;
            flex-grow: 1;
        }
        
        .header h1 {
            color: #17a2b8;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .company-name {
            color: #333;
            margin: 5px 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .header .subtitle {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
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
            font-size: 16px;
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
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            table-layout: fixed;
        }
        
        .data-table thead {
            background: #17a2b8;
            color: white;
        }
        
        .data-table th {
            padding: 3px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.1px;
            word-wrap: break-word;
            line-height: 1.1;
            vertical-align: middle;
        }
        
        .data-table th:nth-child(1) { width: 11%; }
        .data-table th:nth-child(2) { width: 7%; }
        .data-table th:nth-child(3) { width: 7%; }
        .data-table th:nth-child(4) { width: 7%; }
        .data-table th:nth-child(5) { width: 9%; }
        .data-table th:nth-child(6) { width: 9%; }
        .data-table th:nth-child(7) { width: 11%; }
        .data-table th:nth-child(8) { width: 9%; }
        .data-table th:nth-child(9) { width: 9%; }
        .data-table th:nth-child(10) { width: 9%; }
        .data-table th:nth-child(11) { width: 6%; }
        .data-table th:nth-child(12) { width: 6%; }
        
        .data-table td {
            padding: 4px 4px;
            border-bottom: 1px solid #dee2e6;
            font-size: 8px;
            word-wrap: break-word;
            line-height: 1.2;
            vertical-align: top;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tfoot {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .data-table tfoot td {
            border-top: 2px solid #17a2b8;
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
        
        .text-warning {
            color: #ffc107;
            font-weight: 600;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .reference-info {
            font-size: 8px;
            color: #666;
        }
        
        .status-badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        
        .status-issued {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-partial {
            background: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            @if($company && $company->logo)
                <div class="logo-section">
                    <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
                </div>
            @endif
            <div class="title-section">
                <h1>Fee Invoices Report</h1>
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
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ $filters['class'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Stream:</div>
                <div class="info-value">{{ $filters['stream'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fee Group:</div>
                <div class="info-value">{{ $filters['fee_group'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value">{{ $filters['academic_year'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ $filters['status'] }}</div>
            </div>
        </div>
    </div>

    @if($invoices->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Admission No</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Academic Year</th>
                    <th>Fee Group</th>
                    <th>Invoice Numbers</th>
                    <th>Control Number</th>
                    <th class="number">Total Amount</th>
                    <th class="number">Paid Amount</th>
                    <th class="number">Outstanding</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice['student_name'] }}</td>
                    <td>{{ $invoice['admission_number'] }}</td>
                    <td>{{ $invoice['class_name'] }}</td>
                    <td>{{ $invoice['stream_name'] }}</td>
                    <td>{{ $invoice['academic_year'] }}</td>
                    <td>{{ $invoice['fee_group'] }}</td>
                    <td class="reference-info">{{ $invoice['invoice_numbers'] }}</td>
                    <td class="reference-info">{{ $invoice['control_number'] }}</td>
                    <td class="number">{{ number_format($invoice['total_amount'], 2) }}</td>
                    <td class="number text-success">{{ number_format($invoice['paid_amount'], 2) }}</td>
                    <td class="number text-danger">{{ number_format($invoice['outstanding_amount'], 2) }}</td>
                    <td>
                        @php
                            $statusClass = 'status-issued';
                            $statusText = ucfirst(str_replace('_', ' ', $invoice['status']));
                            if ($invoice['status'] === 'paid') {
                                $statusClass = 'status-paid';
                            } elseif ($invoice['status'] === 'overdue' || $invoice['outstanding_amount'] > 0 && $invoice['paid_amount'] > 0) {
                                $statusClass = 'status-overdue';
                            } elseif ($invoice['paid_amount'] > 0 && $invoice['paid_amount'] < $invoice['total_amount']) {
                                $statusClass = 'status-partial';
                            }
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" style="text-align: right; font-weight: bold;">TOTAL:</td>
                    <td class="number" style="font-weight: bold;">{{ number_format($totalAmount, 2) }}</td>
                    <td class="number text-success" style="font-weight: bold;">{{ number_format($totalPaid, 2) }}</td>
                    <td class="number text-danger" style="font-weight: bold;">{{ number_format($totalOutstanding, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
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
        <p style="font-size: 10px; margin-top: 5px;">Total Records: {{ $invoices->count() }} | Total Amount: {{ number_format($totalAmount, 2) }} TZS | Outstanding: {{ number_format($totalOutstanding, 2) }} TZS</p>
    </div>
</body>
</html>

