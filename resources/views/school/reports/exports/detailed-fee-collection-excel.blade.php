<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Fee Collection Report</title>
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
        }

        .data-table th {
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
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
            font-size: 11px;
            word-wrap: break-word;
            text-align: center;
        }

        .number {
            text-align: center;
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

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #17a2b8;
            margin: 30px 0 15px 0;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 5px;
        }

        .class-total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .grand-total-row {
            background-color: #f8f9fa;
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
                @php $grandTotalStudents = 0; $grandTotalPaid = 0; $grandTotalOutstanding = 0; @endphp
                @foreach($feeCollectionData as $className => $streams)
                    @php $classTotalStudents = 0; $classPaidFull = 0; $classOutstanding = 0; @endphp
                    @foreach($streams as $streamName => $data)
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
                                    {{ number_format(($data['paid_full_fees'] / $data['total_students']) * 100, 1) }}
                                @else
                                    0.0
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if(count($streams) > 1)
                        <tr class="class-total-row">
                            <td colspan="2" style="text-align: right; font-weight: bold;">{{ $className }} Total:</td>
                            <td class="number">{{ $classTotalStudents }}</td>
                            <td class="number">{{ $classPaidFull }}</td>
                            <td class="number">{{ $classOutstanding }}</td>
                            <td class="number">
                                @if($classTotalStudents > 0)
                                    {{ number_format(($classPaidFull / $classTotalStudents) * 100, 1) }}
                                @else
                                    0.0
                                @endif
                            </td>
                        </tr>
                    @endif
                    @php
                        $grandTotalStudents += $classTotalStudents;
                        $grandTotalPaid += $classPaidFull;
                        $grandTotalOutstanding += $classOutstanding;
                    @endphp
                @endforeach
                <tr class="grand-total-row">
                    <td colspan="2" style="text-align: right; font-weight: bold;">Grand Total:</td>
                    <td class="number">{{ $grandTotalStudents }}</td>
                    <td class="number">{{ $grandTotalPaid }}</td>
                    <td class="number">{{ $grandTotalOutstanding }}</td>
                    <td class="number">
                        @if($grandTotalStudents > 0)
                            {{ number_format(($grandTotalPaid / $grandTotalStudents) * 100, 1) }}
                        @else
                            0.0
                        @endif
                    </td>
                </tr>
            </tbody>
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