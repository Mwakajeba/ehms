<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Average Marks per Assignment Report</title>
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
        
        .data-table th:nth-child(1) { width: 4%; }
        .data-table th:nth-child(2) { width: 8%; }
        .data-table th:nth-child(3) { width: 15%; }
        .data-table th:nth-child(4) { width: 10%; }
        .data-table th:nth-child(5) { width: 12%; }
        .data-table th:nth-child(6) { width: 8%; }
        .data-table th:nth-child(7) { width: 8%; }
        .data-table th:nth-child(8) { width: 8%; }
        .data-table th:nth-child(9) { width: 8%; }
        .data-table th:nth-child(10) { width: 8%; }
        .data-table th:nth-child(11) { width: 11%; }
        
        .data-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 10px;
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
            font-size: 10px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-success {
            color: #28a745;
            font-weight: 600;
        }
        
        .text-danger {
            color: #dc3545;
            font-weight: 600;
        }
        
        .text-warning {
            color: #ffc107;
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
                <h1>Average Marks per Assignment Report</h1>
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
            @if(!empty($filters))
                @foreach($filters as $label => $value)
                    <div class="info-row">
                        <div class="info-label">{{ $label }}:</div>
                        <div class="info-value">{{ $value }}</div>
                    </div>
                @endforeach
            @else
                <div class="info-row">
                    <div class="info-label">Filters:</div>
                    <div class="info-value">All Records</div>
                </div>
            @endif
        </div>
    </div>

    @if(count($data) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Assignment ID</th>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Class/Stream</th>
                    <th class="text-center">Total Marks</th>
                    <th class="text-center">Avg Marks</th>
                    <th class="text-center">Avg %</th>
                    <th class="text-center">Highest</th>
                    <th class="text-center">Lowest</th>
                    <th class="text-center">Submitted</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalAssignments = 0;
                    $totalAvgMarks = 0;
                    $totalAvgPercentage = 0;
                    $totalSubmitted = 0;
                @endphp
                @foreach($data as $index => $row)
                    @php
                        $totalAssignments++;
                        $totalAvgMarks += (float)$row['average_marks'];
                        $totalAvgPercentage += (float)$row['average_percentage'];
                        $totalSubmitted += (int)$row['submitted_count'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['assignment_id'] }}</td>
                        <td>{{ $row['title'] }}</td>
                        <td>{{ $row['subject'] }}</td>
                        <td>{{ $row['class_stream'] }}</td>
                        <td class="text-center">{{ $row['total_marks'] }}</td>
                        <td class="text-center">{{ $row['average_marks'] }}</td>
                        <td class="text-center">
                            @php
                                $percentage = (float)$row['average_percentage'];
                            @endphp
                            @if($percentage >= 80)
                                <span class="text-success">{{ $row['average_percentage'] }}%</span>
                            @elseif($percentage >= 60)
                                <span class="text-info">{{ $row['average_percentage'] }}%</span>
                            @elseif($percentage >= 40)
                                <span class="text-warning">{{ $row['average_percentage'] }}%</span>
                            @else
                                <span class="text-danger">{{ $row['average_percentage'] }}%</span>
                            @endif
                        </td>
                        <td class="text-center text-success">{{ $row['highest_marks'] }}</td>
                        <td class="text-center text-danger">{{ $row['lowest_marks'] }}</td>
                        <td class="text-center">{{ $row['submitted_count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: bold;">TOTAL ASSIGNMENTS:</td>
                    <td class="text-center" style="font-weight: bold;">{{ $totalAssignments }}</td>
                    <td class="text-center" style="font-weight: bold;">
                        @php
                            $overallAvgMarks = $totalAssignments > 0 ? ($totalAvgMarks / $totalAssignments) : 0;
                        @endphp
                        {{ number_format($overallAvgMarks, 2) }}
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        @php
                            $overallAvgPercentage = $totalAssignments > 0 ? ($totalAvgPercentage / $totalAssignments) : 0;
                        @endphp
                        {{ number_format($overallAvgPercentage, 2) }}%
                    </td>
                    <td colspan="2"></td>
                    <td class="text-center" style="font-weight: bold;">{{ $totalSubmitted }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No assignment marks data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>

