<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gender Distribution Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            background: #fff;
            font-size: 12px;
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .data-table thead {
            background: #17a2b8;
            color: white;
        }

        .data-table th {
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 10px;
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
            padding: 12px 8px;
        }

        .number {
            text-align: center;
            font-family: 'Courier New', monospace;
        }

        .gender-male {
            color: #007bff;
            font-weight: 600;
        }

        .gender-female {
            color: #e83e8c;
            font-weight: 600;
        }

        .total-row {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .grand-total-row {
            background-color: #e9ecef;
            font-weight: 700;
            border-top: 2px solid #dee2e6;
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
            @if($logo_path)
                <div class="logo-section">
                    <img src="{{ $logo_path }}" alt="{{ $company->name ?? 'Company Logo' }}" class="company-logo">
                </div>
            @endif
            <div class="title-section">
                <h1>Gender Distribution Report</h1>
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
            @if(isset($filters['class']))
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ $filters['class'] }}</div>
            </div>
            @endif
            @if(isset($filters['stream']))
            <div class="info-row">
                <div class="info-label">Stream:</div>
                <div class="info-value">{{ $filters['stream'] }}</div>
            </div>
            @endif
            @if(isset($filters['academic_year']))
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value">{{ $filters['academic_year'] }}</div>
            </div>
            @endif
            @if(empty($filters))
            <div class="info-row">
                <div class="info-label">Filter:</div>
                <div class="info-value">All Records</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Summary Statistics -->
    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #17a2b8;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6;">
                    <div style="font-size: 18px; font-weight: bold; color: #17a2b8;">{{ $genderData['grandTotal']['male'] }}</div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">Total Male Students</div>
                </td>
                <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6;">
                    <div style="font-size: 18px; font-weight: bold; color: #17a2b8;">{{ $genderData['grandTotal']['female'] }}</div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">Total Female Students</div>
                </td>
                <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6;">
                    <div style="font-size: 18px; font-weight: bold; color: #17a2b8;">{{ $genderData['grandTotal']['total'] }}</div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">Total Students</div>
                </td>
                <td style="padding: 10px; text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: #17a2b8;">
                        @if($genderData['grandTotal']['total'] > 0)
                            {{ round(($genderData['grandTotal']['male'] / $genderData['grandTotal']['total']) * 100, 1) }}% / {{ round(($genderData['grandTotal']['female'] / $genderData['grandTotal']['total']) * 100, 1) }}%
                        @else
                            0% / 0%
                        @endif
                    </div>
                    <div style="font-size: 11px; color: #666; margin-top: 5px;">Male/Female Ratio</div>
                </td>
            </tr>
        </table>
    </div>

    @if(!empty($genderData['groupedData']))
        <table class="data-table">
            <thead>
                <tr>
                    <th>Class Level</th>
                    <th>Stream</th>
                    <th class="number">Male Students</th>
                    <th class="number">Female Students</th>
                    <th class="number">Total Students</th>
                </tr>
            </thead>
            <tbody>
                @foreach($genderData['groupedData'] as $className => $streams)
                    @foreach($streams as $streamName => $data)
                        <tr>
                            <td>{{ $className }}</td>
                            <td>{{ $streamName }}</td>
                            <td class="number gender-male">{{ $data['male'] }}</td>
                            <td class="number gender-female">{{ $data['female'] }}</td>
                            <td class="number"><strong>{{ $data['total'] }}</strong></td>
                        </tr>
                    @endforeach
                    <!-- Class Total Row -->
                    <tr class="total-row">
                        <td><strong>{{ $className }}</strong></td>
                        <td><strong>Total</strong></td>
                        <td class="number gender-male"><strong>{{ $genderData['classTotals'][$className]['male'] }}</strong></td>
                        <td class="number gender-female"><strong>{{ $genderData['classTotals'][$className]['female'] }}</strong></td>
                        <td class="number"><strong>{{ $genderData['classTotals'][$className]['total'] }}</strong></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="grand-total-row">
                    <td colspan="2"><strong>Grand Total</strong></td>
                    <td class="number gender-male"><strong>{{ $genderData['grandTotal']['male'] }}</strong></td>
                    <td class="number gender-female"><strong>{{ $genderData['grandTotal']['female'] }}</strong></td>
                    <td class="number"><strong>{{ $genderData['grandTotal']['total'] }}</strong></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No student data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 9px; margin-top: 5px;">Gender distribution shows male/female student counts by class and stream with totals.</p>
    </div>
</body>
</html>