<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Income Collection Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            background: #fff;
            font-size: 10px;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #28a745;
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
            color: #28a745;
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .company-name {
            color: #333;
            margin: 5px 0;
            font-size: 14px;
            font-weight: 600;
        }

        .header .subtitle {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 12px;
        }

        .report-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }

        .report-info h3 {
            margin: 0 0 10px 0;
            color: #28a745;
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
            background: #28a745;
            color: white;
        }

        .data-table th {
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }

        .data-table th:nth-child(1) { width: 12%; }
        .data-table th:nth-child(2) { width: 18%; }
        .data-table th:nth-child(3) { width: 12%; }
        .data-table th:nth-child(4) { width: 12%; }
        .data-table th:nth-child(5) { width: 18%; }
        .data-table th:nth-child(6) { width: 18%; }
        .data-table th:nth-child(7) { width: 10%; }

        .data-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
            word-wrap: break-word;
            text-align: center;
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
            border-top: 2px solid #28a745;
            padding: 10px 6px;
        }

        .number {
            text-align: right;
            font-family: 'Courier New', monospace;
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
                <h1>Other Income Collection Report</h1>
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
                <div class="info-label">Date From:</div>
                <div class="info-value">{{ $dateFrom }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date To:</div>
                <div class="info-value">{{ $dateTo }}</div>
            </div>
            @if($accountId)
            <div class="info-row">
                <div class="info-label">Account:</div>
                <div class="info-value">{{ \App\Models\ChartAccount::find($accountId) ? \App\Models\ChartAccount::find($accountId)->account_code . ' - ' . \App\Models\ChartAccount::find($accountId)->account_name : 'N/A' }}</div>
            </div>
            @endif
            @if($classId)
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ \App\Models\School\Classe::find($classId) ? \App\Models\School\Classe::find($classId)->name : 'N/A' }}</div>
            </div>
            @endif
            @if(isset($streamId) && $streamId)
            <div class="info-row">
                <div class="info-label">Stream:</div>
                <div class="info-value">{{ \App\Models\School\Stream::find($streamId) ? \App\Models\School\Stream::find($streamId)->name : 'N/A' }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($otherIncomeData->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($otherIncomeData as $income)
                    <tr>
                        <td>{{ $income->transaction_date->format('Y-m-d') }}</td>
                        <td>{{ $income->student ? $income->student->first_name . ' ' . $income->student->last_name : $income->other_party }}</td>
                        <td>{{ $income->student && $income->student->class ? $income->student->class->name : '-' }}</td>
                        <td>{{ $income->student && $income->student->stream ? $income->student->stream->name : '-' }}</td>
                        <td>{{ $income->incomeAccount ? $income->incomeAccount->account_name : '-' }}</td>
                        <td>{{ $income->description }}</td>
                        <td class="number">{{ number_format($income->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold;">Total:</td>
                    <td class="number">{{ number_format($totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No other income data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>