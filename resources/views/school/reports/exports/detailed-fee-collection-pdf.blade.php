<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Fee Collection Report</title>
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
            border-left: 4px solid #17a2b8;
        }

        .report-info h3 {
            margin: 0 0 10px 0;
            color: #17a2b8;
            font-size: 15px;
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
            font-size: 15px;
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
            font-size: 15px;
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
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            border: 1px solid #fff;
        }

        .data-table th:nth-child(1) { width: 20%; }
        .data-table th:nth-child(2) { width: 20%; }
        .data-table th:nth-child(3) { width: 15%; }
        .data-table th:nth-child(4) { width: 15%; }
        .data-table th:nth-child(5) { width: 15%; }
        .data-table th:nth-child(6) { width: 15%; }

        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            font-size: 12px;
            word-wrap: break-word;
            text-align: center;
        }

        .data-table td:first-child {
            border-left: 1px solid #dee2e6;
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
            text-align: center;
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
            font-size: 16px;
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
            font-size: 10px;
        }

        .summary-header {
            background-color: #17a2b8;
            color: white;
            font-weight: bold;
        }

        .page-break {
            page-break-before: always;
        }

        .class-total-row {
            background-color: #e9ecef;
            font-weight: bold;
            border-top: 2px solid #17a2b8;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
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
                <h1>Detailed Fee Collection Report</h1>
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
        </div>
    </div>

    <h3 class="section-title">Detailed Fee Collection Summary</h3>
    @if($feeCollectionData->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr>
                    <th>Class Level</th>
                    <th>Stream</th>
                    <th>Total Students</th>
                    <th>Paid Full Fees</th>
                    <th>Outstanding Fees</th>
                    <th>Collection Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $grandTotalStudents = 0; 
                    $grandTotalPaid = 0; 
                    $grandTotalOutstanding = 0; 
                @endphp
                @foreach($feeCollectionData as $className => $streams)
                    @php 
                        $classTotalStudents = 0; 
                        $classPaidFull = 0; 
                        $classOutstanding = 0; 
                    @endphp
                    @foreach($streams as $streamName => $data)
                        @if($streamName !== 'class_totals')
                            @php
                                $classTotalStudents += $data['total_students'];
                                $classPaidFull += $data['paid_full_fees'];
                                $classOutstanding += $data['outstanding_fees'];
                            @endphp
                            <tr>
                                <td>{{ $className }}</td>
                                <td>{{ $streamName }}</td>
                                <td class="number">{{ $data['total_students'] }}</td>
                                <td class="number">{{ $data['paid_full_fees'] }}</td>
                                <td class="number">{{ $data['outstanding_fees'] }}</td>
                                <td class="number">
                                    @if($data['total_students'] > 0)
                                        {{ number_format(($data['paid_full_fees'] / $data['total_students']) * 100, 1) }}%
                                    @else
                                        0.0%
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    
                    @if(isset($streams['class_totals']))
                        @php
                            $classTotals = $streams['class_totals'];
                            $grandTotalStudents += $classTotals['total_students'];
                            $grandTotalPaid += $classTotals['paid_full_fees'];
                            $grandTotalOutstanding += $classTotals['outstanding_fees'];
                        @endphp
                        <tr class="class-total-row">
                            <td colspan="2" style="text-align: right; font-weight: bold; padding-right: 15px;">Class Total - {{ $className }}:</td>
                            <td class="number" style="font-weight: bold;">{{ $classTotals['total_students'] }}</td>
                            <td class="number" style="font-weight: bold;">{{ $classTotals['paid_full_fees'] }}</td>
                            <td class="number" style="font-weight: bold;">{{ $classTotals['outstanding_fees'] }}</td>
                            <td class="number" style="font-weight: bold;">
                                @if($classTotals['total_students'] > 0)
                                    {{ number_format(($classTotals['paid_full_fees'] / $classTotals['total_students']) * 100, 1) }}%
                                @else
                                    0.0%
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #cce5ff; font-weight: bolder;">
                    <td colspan="2" style="text-align: right; font-weight: bold; padding-right: 15px;">Grand Total:</td>
                    <td class="number" style="font-weight: bold;">{{ $grandTotalStudents }}</td>
                    <td class="number" style="font-weight: bold;">{{ $grandTotalPaid }}</td>
                    <td class="number" style="font-weight: bold;">{{ $grandTotalOutstanding }}</td>
                    <td class="number" style="font-weight: bold;">
                        @if($grandTotalStudents > 0)
                            {{ number_format(($grandTotalPaid / $grandTotalStudents) * 100, 1) }}%
                        @else
                            0.0%
                        @endif
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No fee collection data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>