<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance by Class Report</title>
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

        .summary-cards {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .summary-card {
            flex: 1;
            min-width: 120px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #17a2b8;
        }

        .summary-card .icon {
            font-size: 24px;
            color: #17a2b8;
            margin-bottom: 8px;
        }

        .summary-card .value {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .summary-card .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 10px 8px;
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

        .text-center {
            text-align: center;
        }

        .table-warning {
            background: #fff3cd !important;
        }

        .table-primary {
            background: #cce5ff !important;
            font-weight: bolder;
            font-size: 1.1em;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .font-weight-bolder {
            font-weight: bolder;
        }

        .absent-students {
            margin-top: 30px;
            page-break-before: always;
        }

        .absent-students h4 {
            color: #17a2b8;
            margin-bottom: 15px;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 8px;
        }

        .absent-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .absent-table th {
            background: #dc3545;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .absent-table td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .bg-danger {
            background: #dc3545;
            color: white;
        }

        .bg-warning {
            background: #ffc107;
            color: #212529;
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

        @media print {
            body {
                padding: 10px;
            }

            .data-table {
                box-shadow: none;
            }
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
                <h1>Performance by Class Report</h1>
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
            @if($examType)
            <div class="info-row">
                <div class="info-label">Exam Type:</div>
                <div class="info-value">{{ $examType->name }}</div>
            </div>
            @endif
            @if($selectedClass)
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ $selectedClass->name }}</div>
            </div>
            @endif
        </div>
    </div>

    @if(!empty($performanceData['performance']))
        <!-- Summary Statistics -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="icon">👥</div>
                <div class="value">{{ $performanceData['grandTotal']['total_students'] }}</div>
                <div class="label">Total Students</div>
            </div>
            <div class="summary-card">
                <div class="icon">✅</div>
                <div class="value">{{ $performanceData['grandTotal']['passed'] }}</div>
                <div class="label">Passed</div>
            </div>
            <div class="summary-card">
                <div class="icon">❌</div>
                <div class="value">{{ $performanceData['grandTotal']['failed'] }}</div>
                <div class="label">Failed</div>
            </div>
            <div class="summary-card">
                <div class="icon">⏳</div>
                <div class="value">{{ $performanceData['grandTotal']['not_attempted'] }}</div>
                <div class="label">Not Attempted</div>
            </div>
            <div class="summary-card">
                <div class="icon">📊</div>
                <div class="value">{{ $performanceData['grandTotal']['pass_rate'] ?? 0 }}%</div>
                <div class="label">Pass Rate</div>
            </div>
            <div class="summary-card">
                <div class="icon">🏫</div>
                <div class="value">{{ $performanceData['grandTotal']['classes_count'] ?? 0 }}</div>
                <div class="label">Classes</div>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center">Class Level</th>
                    <th class="text-center">Stream</th>
                    <th class="text-center">Passed</th>
                    <th class="text-center">Failed</th>
                    <th class="text-center">Not Attempted</th>
                    <th class="text-center">Total Students</th>
                </tr>
            </thead>
            <tbody>
                @foreach($performanceData['performance'] as $className => $streams)
                    @foreach($streams as $streamData)
                    <tr>
                        <td class="text-center">{{ $streamData['class']->name }}</td>
                        <td class="text-center">{{ $streamData['stream']->name }}</td>
                        <td class="text-center number">{{ $streamData['passed'] }}</td>
                        <td class="text-center number">{{ $streamData['failed'] }}</td>
                        <td class="text-center number">{{ $streamData['not_attempted'] }}</td>
                        <td class="text-center number">{{ $streamData['total_students'] }}</td>
                    </tr>
                    @endforeach

                    <!-- Subtotal for this class -->
                    <tr class="table-warning">
                        <td colspan="2" class="text-center font-weight-bold">SUBTOTAL - {{ $className }}</td>
                        <td class="text-center number font-weight-bold">{{ $performanceData['subtotals'][$className]['passed'] }}</td>
                        <td class="text-center number font-weight-bold">{{ $performanceData['subtotals'][$className]['failed'] }}</td>
                        <td class="text-center number font-weight-bold">{{ $performanceData['subtotals'][$className]['not_attempted'] }}</td>
                        <td class="text-center number font-weight-bold">{{ $performanceData['subtotals'][$className]['total_students'] }}</td>
                    </tr>
                @endforeach

                <!-- Grand Total -->
                <tr class="table-primary">
                    <td colspan="2" class="text-center font-weight-bolder">GRAND TOTAL</td>
                    <td class="text-center number font-weight-bolder">{{ $performanceData['grandTotal']['passed'] }}</td>
                    <td class="text-center number font-weight-bolder">{{ $performanceData['grandTotal']['failed'] }}</td>
                    <td class="text-center number font-weight-bolder">{{ $performanceData['grandTotal']['not_attempted'] }}</td>
                    <td class="text-center number font-weight-bolder">{{ $performanceData['grandTotal']['total_students'] }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Absent Students -->
        @if(!empty($performanceData['absentStudents']))
            <div class="absent-students">
                <h4>STUDENTS ABSENT FROM EXAMINATIONS</h4>
                <table class="absent-table">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>STUDENT NAME</th>
                            <th class="text-center">CLASS</th>
                            <th class="text-center">STREAM</th>
                            <th>ABSENT SUBJECTS</th>
                            <th class="text-center">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($performanceData['absentStudents'] as $index => $absentStudent)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $absentStudent['student']->first_name }} {{ $absentStudent['student']->last_name }}</td>
                                <td class="text-center">{{ $absentStudent['student']->class->name ?? '-' }}</td>
                                <td class="text-center">{{ $absentStudent['student']->stream->name ?? '-' }}</td>
                                <td>
                                    @if(!empty($absentStudent['absent_subjects']))
                                        <span class="badge bg-danger">{{ implode(', ', $absentStudent['absent_subjects']) }}</span>
                                    @else
                                        <span style="color: #666;">Not registered for some subjects</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning">ABSENT</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No performance data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 10px; margin-top: 5px;">Performance metrics exclude absent students. Pass/Fail determination based on grade scale passing point.</p>
    </div>
</body>
</html>