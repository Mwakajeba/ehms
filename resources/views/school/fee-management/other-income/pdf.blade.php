<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Other Income Record</title>
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

        .data-table th:nth-child(1) { width: 15%; }
        .data-table th:nth-child(2) { width: 35%; }
        .data-table th:nth-child(3) { width: 30%; }
        .data-table th:nth-child(4) { width: 20%; text-align: right; }
        .data-table th:nth-child(5) { width: 15%; }
        .data-table th:nth-child(6) { width: 15%; }

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

        .income-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }

        .income-details h3 {
            margin: 0 0 15px 0;
            color: #17a2b8;
            font-size: 13px;
        }

        .detail-row {
            display: table-row;
            margin-bottom: 8px;
        }

        .detail-label {
            display: table-cell;
            font-weight: bold;
            color: #555;
            font-size: 11px;
            text-transform: uppercase;
            padding: 4px 15px 4px 0;
            width: 140px;
            vertical-align: top;
        }

        .detail-value {
            display: table-cell;
            color: #333;
            font-size: 12px;
            padding: 4px 0;
            vertical-align: top;
        }

        .amount-highlight {
            font-size: 14px;
            font-weight: bold;
            color: #28a745;
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
                <h1>Other Income Record</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                <div class="subtitle">Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="income-details">
        <h3>Income Details</h3>
        <div style="display: table; width: 100%;">
            <div class="detail-row">
                <div class="detail-label">Transaction Date</div>
                <div class="detail-value">{{ $otherIncome->transaction_date->format('M d, Y') }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Income Type</div>
                <div class="detail-value">
                    @if($otherIncome->income_type === 'student')
                        Student
                    @else
                        Other Party
                    @endif
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">
                    {{ $otherIncome->income_type === 'student' ? 'Student' : 'Other Party' }}
                </div>
                <div class="detail-value">
                    @if($otherIncome->income_type === 'student')
                        {{ $otherIncome->student->name ?? 'N/A' }}
                        @if($otherIncome->student)
                            <br><small>Student ID: {{ $otherIncome->student->student_number }}</small>
                        @endif
                    @else
                        {{ $otherIncome->other_party ?? 'N/A' }}
                    @endif
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Description</div>
                <div class="detail-value">{{ $otherIncome->description }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Received In</div>
                <div class="detail-value">{{ $otherIncome->received_in_display }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Income Account</div>
                <div class="detail-value">{{ $otherIncome->incomeAccount->account_name ?? 'N/A' }}</div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Amount</div>
                <div class="detail-value amount-highlight">{{ config('app.currency', 'TZS') }} {{ number_format($otherIncome->amount, 2) }}</div>
            </div>

            @if($company)
            <div class="detail-row">
                <div class="detail-label">Company</div>
                <div class="detail-value">{{ $company->name }}</div>
            </div>
            @endif

            @if($otherIncome->branch)
            <div class="detail-row">
                <div class="detail-label">Branch</div>
                <div class="detail-value">{{ $otherIncome->branch->name }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($glTransactions && $glTransactions->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Account</th>
                    <th>Description</th>
                    <th class="number">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $otherIncome->transaction_date->format('Y-m-d') }}</td>
                    <td>{{ $otherIncome->incomeAccount->account_name ?? 'N/A' }}</td>
                    <td>{{ $otherIncome->description }}</td>
                    <td class="number">
                        <span style="color: #28a745;">{{ number_format($otherIncome->amount, 2) }}</span>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right; font-weight: bold;">GRAND TOTAL:</td>
                    <td class="number" style="font-weight: bold;">
                        <span style="color: #28a745;">{{ number_format($otherIncome->amount, 2) }}</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 10px; margin-top: 5px;">This is an official Other Income record document.</p>
    </div>
</body>
</html>