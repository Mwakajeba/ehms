<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Opening Balance Report</title>
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
            font-size: 8px;
        }
        
        .data-table thead {
            background: #17a2b8;
            color: white;
        }
        
        .data-table th {
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }
        
        .data-table th:nth-child(1) { width: 5%; }
        .data-table th:nth-child(2) { width: 8%; }
        .data-table th:nth-child(3) { width: 12%; }
        .data-table th:nth-child(4) { width: 8%; }
        .data-table th:nth-child(5) { width: 8%; }
        .data-table th:nth-child(6) { width: 8%; }
        .data-table th:nth-child(7) { width: 10%; }
        .data-table th:nth-child(8) { width: 10%; }
        .data-table th:nth-child(9) { width: 10%; }
        .data-table th:nth-child(10) { width: 11%; }
        
        .data-table td {
            padding: 6px 4px;
            border-bottom: 1px solid #dee2e6;
            font-size: 7px;
            word-wrap: break-word;
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
            padding: 8px 4px;
            font-size: 8px;
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
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 3px;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
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
                <h1>Student Fee Opening Balance Report</h1>
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
            @if($branch)
            <div class="info-row">
                <div class="info-label">Branch:</div>
                <div class="info-value">{{ $branch->name }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($openingBalances->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No.</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Opening Date</th>
                    <th>Fee Group</th>
                    <th class="number">Amount</th>
                    <th class="number">Paid</th>
                    <th class="number">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($openingBalances as $index => $balance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $balance->student ? $balance->student->admission_number : 'N/A' }}</td>
                    <td>{{ $balance->student ? $balance->student->first_name . ' ' . $balance->student->last_name : 'N/A' }}</td>
                    <td>{{ $balance->student && $balance->student->class ? $balance->student->class->name : 'N/A' }}</td>
                    <td>{{ $balance->student && $balance->student->stream ? $balance->student->stream->name : 'N/A' }}</td>
                    <td>{{ $balance->opening_date ? \Carbon\Carbon::parse($balance->opening_date)->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $balance->feeGroup ? $balance->feeGroup->name : 'N/A' }}</td>
                    <td class="number">{{ number_format($balance->amount, 2) }}</td>
                    <td class="number">{{ number_format($balance->paid_amount, 2) }}</td>
                    <td class="number text-danger"><strong>{{ number_format($balance->balance_due, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="7" style="text-align: right; font-weight: bold;">TOTAL:</td>
                    <td class="number" style="font-weight: bold;">{{ number_format($totalAmount, 2) }}</td>
                    <td class="number" style="font-weight: bold;">{{ number_format($totalPaid, 2) }}</td>
                    <td class="number text-danger" style="font-weight: bold;">{{ number_format($totalBalance, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No opening balances found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>

