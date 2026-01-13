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
            justify-content: space-between;
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

        .company-info {
            text-align: right;
            font-size: 11px;
            color: #666;
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
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }

        .data-table th:nth-child(1) { width: 20%; }
        .data-table th:nth-child(2) { width: 20%; }
        .data-table th:nth-child(3) { width: 12%; }
        .data-table th:nth-child(4) { width: 12%; }
        .data-table th:nth-child(5) { width: 18%; }
        .data-table th:nth-child(6) { width: 18%; }

        .data-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
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

        .text-center {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .font-weight-bold {
            font-weight: bold;
        }

        .table-warning {
            background: #fff3cd !important;
        }

        .table-primary {
            background: #cce5ff !important;
        }

        .summary-row {
            background: #f8f9fa;
            font-weight: bold;
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
            @if(isset($filters['branch']) && $filters['branch'] !== 'Unknown')
                @php
                    $branch = \App\Models\Branch::where('name', $filters['branch'])->first();
                @endphp
                @if($branch && $branch->logo)
                    <div class="logo-section">
                        <img src="{{ public_path('storage/' . $branch->logo) }}" alt="{{ $branch->name ?? 'Branch Logo' }}" class="company-logo">
                    </div>
                @elseif($company && $company->logo)
                    <div class="logo-section">
                        <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
                    </div>
                @endif
            @elseif($company && $company->logo)
                <div class="logo-section">
                    <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
                </div>
            @endif
            <div class="title-section">
                <h1>Performance by Class Report</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                @if(isset($filters['branch']) && $filters['branch'] !== 'Unknown')
                    <div class="branch-name" style="font-size: 14px; color: #666; margin: 2px 0;">Branch: {{ $filters['branch'] }}</div>
                    @if(isset($branch) && $branch && $branch->location)
                        <div class="branch-location" style="font-size: 12px; color: #888; margin: 2px 0;">Location: {{ $branch->location }}</div>
                    @endif
                @endif
                <div class="subtitle">Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}</div>
            </div>
            @if($company)
                <div class="company-info" style="text-align: right; font-size: 11px; color: #666;">
                    @if($company->address)
                        <div><strong>Address:</strong> {{ $company->address }}</div>
                    @endif
                    @if($company->phone)
                        <div><strong>Phone:</strong> {{ $company->phone }}</div>
                    @endif
                    @if($company->email)
                        <div><strong>Email:</strong> {{ $company->email }}</div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="report-info">
        <h3>Report Parameters</h3>
        <div class="info-grid">
            @if(isset($filters['academic_year']))
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value">{{ $filters['academic_year'] }}</div>
            </div>
            @endif
            @if(isset($filters['exam_type']))
            <div class="info-row">
                <div class="info-label">Exam Type:</div>
                <div class="info-value">{{ $filters['exam_type'] }}</div>
            </div>
            @endif
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
        </div>
    </div>

    @php
        $performanceData = $performanceData ?? [];
        $performance = $performanceData['performance'] ?? [];
        $subtotals = $performanceData['subtotals'] ?? [];
        $grandTotal = $performanceData['grandTotal'] ?? [];
    @endphp

    @if(!empty($performance))
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
                @foreach($performance as $className => $streams)
                    @foreach($streams as $streamData)
                    <tr>
                        <td class="text-center">{{ $streamData['class']->name }}</td>
                        <td class="text-center">{{ $streamData['stream']->name }}</td>
                        <td class="text-center">{{ $streamData['passed'] }}</td>
                        <td class="text-center">{{ $streamData['failed'] }}</td>
                        <td class="text-center">{{ $streamData['not_attempted'] }}</td>
                        <td class="text-center">{{ $streamData['total_students'] }}</td>
                    </tr>
                    @endforeach

                    <!-- Subtotal for this class -->
                    @if(isset($subtotals[$className]))
                    <tr class="summary-row">
                        <td colspan="2" class="text-right font-weight-bold">SUBTOTAL - {{ $className }}</td>
                        <td class="text-center font-weight-bold">{{ $subtotals[$className]['passed'] }}</td>
                        <td class="text-center font-weight-bold">{{ $subtotals[$className]['failed'] }}</td>
                        <td class="text-center font-weight-bold">{{ $subtotals[$className]['not_attempted'] }}</td>
                        <td class="text-center font-weight-bold">{{ $subtotals[$className]['total_students'] }}</td>
                    </tr>
                    @endif
                @endforeach

                <!-- Grand Total -->
                <tr class="table-primary" style="font-weight: bolder; font-size: 1.1em;">
                    <td colspan="2" class="text-right font-weight-bolder">GRAND TOTAL</td>
                    <td class="text-center font-weight-bolder">{{ $grandTotal['passed'] ?? 0 }}</td>
                    <td class="text-center font-weight-bolder">{{ $grandTotal['failed'] ?? 0 }}</td>
                    <td class="text-center font-weight-bolder">{{ $grandTotal['not_attempted'] ?? 0 }}</td>
                    <td class="text-center font-weight-bolder">{{ $grandTotal['total_students'] ?? 0 }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Absent Students -->
        @if(isset($performanceData['absentStudents']) && !empty($performanceData['absentStudents']))
            <div style="page-break-before: always; margin-top: 40px;">
                <h4 style="color: #17a2b8; margin-bottom: 15px;">STUDENTS ABSENT FROM EXAMINATIONS</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Student Name</th>
                            <th class="text-center">Class</th>
                            <th class="text-center">Stream</th>
                            <th>Absent Subjects</th>
                            <th class="text-center">Status</th>
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
                                        <span style="color: #dc3545; font-weight: 600;">{{ implode(', ', $absentStudent['absent_subjects']) }}</span>
                                    @else
                                        <span style="color: #999;">Not registered for some subjects</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span style="color: #fd7e14; font-weight: 600;">ABSENT</span>
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
    </div>
</body>
</html>

