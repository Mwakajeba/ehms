@extends('layouts.print')

@section('title', 'Other Income Report')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Income Report</title>
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
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }

        .data-table th:nth-child(1) { width: 6%; }  /* # */
        .data-table th:nth-child(2) { width: 10%; } /* Date */
        .data-table th:nth-child(3) { width: 8%; }  /* Type */
        .data-table th:nth-child(4) { width: 15%; } /* Student/Party */
        .data-table th:nth-child(5) { width: 12%; } /* Class/Stream */
        .data-table th:nth-child(6) { width: 15%; } /* Description */
        .data-table th:nth-child(7) { width: 12%; } /* Received In */
        .data-table th:nth-child(8) { width: 12%; } /* Income Account */
        .data-table th:nth-child(9) { width: 10%; } /* Amount */

        .data-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
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

        .income-type-student {
            background-color: #e8f5e8;
            color: #2e7d32;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .income-type-other {
            background-color: #fff3e0;
            color: #ef6c00;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
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
                <h1>Other Income Report</h1>
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
            @if(isset($filters['date_from']) && !empty($filters['date_from']))
            <div class="info-row">
                <div class="info-label">Date From:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($filters['date_from'])->format('M d, Y') }}</div>
            </div>
            @endif
            @if(isset($filters['date_to']) && !empty($filters['date_to']))
            <div class="info-row">
                <div class="info-label">Date To:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($filters['date_to'])->format('M d, Y') }}</div>
            </div>
            @endif
            @if(isset($filters['income_type']) && !empty($filters['income_type']))
            <div class="info-row">
                <div class="info-label">Income Type:</div>
                <div class="info-value">{{ ucfirst($filters['income_type']) }}</div>
            </div>
            @endif
            @if(isset($filters['class_id']) && !empty($filters['class_id']))
                @php
                    $class = \App\Models\School\Classe::find($filters['class_id']);
                @endphp
                @if($class)
                <div class="info-row">
                    <div class="info-label">Class:</div>
                    <div class="info-value">{{ $class->name }}</div>
                </div>
                @endif
            @endif
            @if(isset($filters['income_account_id']) && !empty($filters['income_account_id']))
                @php
                    $account = \App\Models\ChartAccount::find($filters['income_account_id']);
                @endphp
                @if($account)
                <div class="info-row">
                    <div class="info-label">Income Account:</div>
                    <div class="info-value">{{ $account->account_code }} - {{ $account->account_name }}</div>
                </div>
                @endif
            @endif
            <div class="info-row">
                <div class="info-label">Total Records:</div>
                <div class="info-value">{{ $otherIncomes->count() }}</div>
            </div>
        </div>
    </div>

    @if($otherIncomes->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Student/Party</th>
                    <th>Class/Stream</th>
                    <th>Description</th>
                    <th>Received In</th>
                    <th>Income Account</th>
                    <th class="number">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($otherIncomes as $index => $income)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $income->transaction_date ? $income->transaction_date->format('d/m/Y') : 'N/A' }}</td>
                    <td>
                        @if($income->income_type === 'student')
                            <span class="income-type-student">{{ ucfirst($income->income_type) }}</span>
                        @else
                            <span class="income-type-other">{{ ucfirst($income->income_type) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($income->income_type === 'student')
                            {{ $income->student->name ?? 'N/A' }}
                        @else
                            {{ $income->other_party ?? 'N/A' }}
                        @endif
                    </td>
                    <td>
                        @if($income->income_type === 'student' && $income->student)
                            {{ ($income->student->class->name ?? 'N/A') . ($income->student->stream ? ' - ' . $income->student->stream->name : '') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $income->description }}</td>
                    <td>{{ $income->receivedInDisplay }}</td>
                    <td>{{ $income->incomeAccount->account_name ?? 'N/A' }}</td>
                    <td class="number">{{ number_format($income->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8" style="text-align: right; font-weight: bold;">TOTAL AMOUNT:</td>
                    <td class="number" style="font-weight: bold; color: #17a2b8;">
                        {{ number_format($otherIncomes->sum('amount'), 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No other income records found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 10px; margin-top: 5px;">Income types are color-coded: Green for Student income, Orange for Other income.</p>
    </div>
</body>
</html>
@endsection