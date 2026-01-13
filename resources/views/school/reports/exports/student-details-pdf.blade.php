<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details Report</title>
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border: 1px solid #dee2e6;
        }

        .data-table th {
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            background-color: #17a2b8;
            color: white;
            border: 1px solid #dee2e6;
        }

        .data-table td {
            padding: 8px 6px;
            border: 1px solid #dee2e6;
            font-size: 9px;
            word-wrap: break-word;
            text-align: center;
        }

        .number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 500;
        }

        .status-outstanding {
            background-color: #f8d7da;
            color: #721c24;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 500;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 9px;
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
                    <img src="{{ asset('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
                </div>
            @endif
            <div class="title-section">
                <h1>{{ $title }}</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                <div class="subtitle">Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <h3 class="section-title">Student Details</h3>
    @if(!empty($students))
        <table class="data-table">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Quarter</th>
                    <th>Total Amount</th>
                    <th>Paid Amount</th>
                    <th>Outstanding</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                    @php
                        $totalAmount = 0;
                        $paidAmount = 0;
                        $quarters = [];

                        foreach($student['fee_invoices'] as $invoice) {
                            $totalAmount += $invoice['total_amount'];
                            $paidAmount += $invoice['paid_amount'];
                            if (isset($invoice['period']) && $invoice['period'] && !in_array($invoice['period'], $quarters)) {
                                $quarters[] = $invoice['period'];
                            }
                        }

                        $outstanding = $totalAmount - $paidAmount;
                        $quarterDisplay = !empty($quarters) ? implode(', ', array_map(function($q) { return 'Q' . $q; }, $quarters)) : 'N/A';
                    @endphp
                    <tr>
                        <td>{{ $student['admission_number'] ?? 'N/A' }}</td>
                        <td>{{ $student['first_name'] }} {{ $student['last_name'] }}</td>
                        <td>{{ $quarterDisplay }}</td>
                        <td class="number">{{ number_format($totalAmount, 2) }}</td>
                        <td class="number">{{ number_format($paidAmount, 2) }}</td>
                        <td class="number">{{ number_format($outstanding, 2) }}</td>
                        <td>
                            @if($outstanding > 0)
                                <span class="status-outstanding">Outstanding</span>
                            @else
                                <span class="status-paid">Paid</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No student details found.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>