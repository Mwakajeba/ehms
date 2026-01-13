<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class-Wise Revenue Collection Report</title>
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

        .class-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .class-header {
            background: #667eea;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 12px;
        }

        .stream-header {
            background: #f093fb;
            color: white;
            padding: 8px;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 11px;
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

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .text-warning {
            color: #ffc107;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 600;
        }

        .bg-success {
            background-color: #28a745;
            color: white;
        }

        .bg-info {
            background-color: #17a2b8;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107;
            color: #333;
        }

        .bg-danger {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CLASS-WISE REVENUE COLLECTION REPORT</h1>
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
                <div class="info-label">Date From:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date To:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}</div>
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
            @if($period)
            <div class="info-row">
                <div class="info-label">Period:</div>
                <div class="info-value">{{ $period }}</div>
            </div>
            @endif
        </div>
    </div>

    @if(!empty($revenueData) && !empty($revenueData['grand_totals']))
    <table class="summary-table">
        <thead>
            <tr>
                <th>Total Invoices</th>
                <th>Total Billed</th>
                <th>Total Collected</th>
                <th>Total Outstanding</th>
                <th>Collection Rate (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($revenueData['grand_totals']['total_invoices']) }}</td>
                <td class="text-end">{{ number_format($revenueData['grand_totals']['total_billed'], 2) }}</td>
                <td class="text-end text-success">{{ number_format($revenueData['grand_totals']['total_collected'], 2) }}</td>
                <td class="text-end text-danger">{{ number_format($revenueData['grand_totals']['total_outstanding'], 2) }}</td>
                <td style="font-weight: bold;">{{ number_format($revenueData['grand_totals']['collection_rate'], 2) }}%</td>
            </tr>
        </tbody>
    </table>
    @endif

    @if(!empty($revenueData) && !empty($revenueData['classes']))
        @foreach($revenueData['classes'] as $classData)
        <div class="class-section">
            <div class="class-header">
                {{ $classData['class_name'] }} - Invoices: {{ $classData['total_invoices'] }} | 
                Billed: {{ number_format($classData['total_billed'], 2) }} | 
                Collected: {{ number_format($classData['total_collected'], 2) }} | 
                Rate: {{ number_format($classData['collection_rate'], 2) }}%
            </div>

            @if(!empty($classData['streams']))
                @foreach($classData['streams'] as $streamData)
                <div class="stream-header">
                    {{ $streamData['stream_name'] }}
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Billed</th>
                            <th>Collected</th>
                            <th>Outstanding</th>
                            <th>Collection Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['Q1', 'Q2', 'Q3', 'Q4', 'Annual'] as $periodKey)
                            @if($streamData['periods'][$periodKey]['billed'] > 0 || $streamData['periods'][$periodKey]['collected'] > 0)
                            <tr>
                                <td>{{ $periodKey }}</td>
                                <td class="text-end">{{ number_format($streamData['periods'][$periodKey]['billed'], 2) }}</td>
                                <td class="text-end text-success">{{ number_format($streamData['periods'][$periodKey]['collected'], 2) }}</td>
                                <td class="text-end text-danger">{{ number_format($streamData['periods'][$periodKey]['outstanding'], 2) }}</td>
                                <td class="text-center">{{ number_format($streamData['periods'][$periodKey]['collection_rate'], 2) }}%</td>
                            </tr>
                            @endif
                        @endforeach
                        <tr style="font-weight: bold; background-color: #f8f9fa;">
                            <td>Stream Total</td>
                            <td class="text-end">{{ number_format($streamData['total_billed'], 2) }}</td>
                            <td class="text-end text-success">{{ number_format($streamData['total_collected'], 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($streamData['total_outstanding'], 2) }}</td>
                            <td class="text-center">{{ number_format($streamData['collection_rate'], 2) }}%</td>
                        </tr>
                    </tbody>
                </table>
                @endforeach
            @endif

            <!-- Class Period Summary -->
            <table class="data-table" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Billed</th>
                        <th>Collected</th>
                        <th>Outstanding</th>
                        <th>Collection Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['Q1', 'Q2', 'Q3', 'Q4', 'Annual'] as $periodKey)
                        @if($classData['periods'][$periodKey]['billed'] > 0 || $classData['periods'][$periodKey]['collected'] > 0)
                        <tr>
                            <td><strong>{{ $periodKey }}</strong></td>
                            <td class="text-end">{{ number_format($classData['periods'][$periodKey]['billed'], 2) }}</td>
                            <td class="text-end text-success">{{ number_format($classData['periods'][$periodKey]['collected'], 2) }}</td>
                            <td class="text-end text-danger">{{ number_format($classData['periods'][$periodKey]['outstanding'], 2) }}</td>
                            <td class="text-center">{{ number_format($classData['periods'][$periodKey]['collection_rate'], 2) }}%</td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    @else
        <p style="text-align: center; padding: 20px; color: #666;">No revenue data found for the selected filters.</p>
    @endif
</body>
</html>

