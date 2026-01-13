<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject-Wise Homework Performance Report</title>
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
            border-bottom: 3px solid #007bff;
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
            color: #007bff;
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
            border-left: 4px solid #007bff;
        }
        
        .report-info h3 {
            margin: 0 0 10px 0;
            color: #007bff;
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
            background: #007bff;
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
        
        .data-table th:nth-child(1) { width: 5%; }
        .data-table th:nth-child(2) { width: 18%; }
        .data-table th:nth-child(3) { width: 10%; }
        .data-table th:nth-child(4) { width: 12%; }
        .data-table th:nth-child(5) { width: 12%; }
        .data-table th:nth-child(6) { width: 20%; }
        .data-table th:nth-child(7) { width: 23%; }
        
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
            border-top: 2px solid #007bff;
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
                <h1>Subject-Wise Homework Performance Report</h1>
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
                    <th>Subject</th>
                    <th class="text-center">Total Homework</th>
                    <th class="text-center">Avg Score</th>
                    <th class="text-center">Completion Rate</th>
                    <th>Top Performer</th>
                    <th>Needs Improvement</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalSubjects = 0;
                    $totalHomework = 0;
                    $totalAvgScore = 0;
                    $totalCompletionRate = 0;
                @endphp
                @foreach($data as $index => $row)
                    @php
                        $totalSubjects++;
                        $totalHomework += (int)$row['total_homework'];
                        $totalAvgScore += (float)$row['average_score'];
                        $totalCompletionRate += (float)$row['completion_rate'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['subject'] }}</td>
                        <td class="text-center">{{ $row['total_homework'] }}</td>
                        <td class="text-center">
                            @php
                                $score = (float)$row['average_score'];
                            @endphp
                            @if($score >= 80)
                                <span class="text-success">{{ $row['average_score'] }}%</span>
                            @elseif($score >= 60)
                                <span class="text-info">{{ $row['average_score'] }}%</span>
                            @elseif($score >= 40)
                                <span class="text-warning">{{ $row['average_score'] }}%</span>
                            @else
                                <span class="text-danger">{{ $row['average_score'] }}%</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $rate = (float)$row['completion_rate'];
                            @endphp
                            @if($rate >= 80)
                                <span class="text-success">{{ $row['completion_rate'] }}%</span>
                            @elseif($rate >= 60)
                                <span class="text-info">{{ $row['completion_rate'] }}%</span>
                            @elseif($rate >= 40)
                                <span class="text-warning">{{ $row['completion_rate'] }}%</span>
                            @else
                                <span class="text-danger">{{ $row['completion_rate'] }}%</span>
                            @endif
                        </td>
                        <td>{{ $row['top_performer'] }}</td>
                        <td>
                            @if($row['needs_improvement_count'] > 0)
                                <span class="text-danger">{{ $row['needs_improvement'] }}</span>
                                <br><small>({{ $row['needs_improvement_count'] }} student(s))</small>
                            @else
                                <span class="text-success">None</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" style="text-align: right; font-weight: bold;">TOTAL SUBJECTS:</td>
                    <td class="text-center" style="font-weight: bold;">{{ $totalSubjects }}</td>
                    <td class="text-center" style="font-weight: bold;">
                        @php
                            $overallAvgScore = $totalSubjects > 0 ? ($totalAvgScore / $totalSubjects) : 0;
                        @endphp
                        {{ number_format($overallAvgScore, 2) }}%
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        @php
                            $overallCompletionRate = $totalSubjects > 0 ? ($totalCompletionRate / $totalSubjects) : 0;
                        @endphp
                        {{ number_format($overallCompletionRate, 2) }}%
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No homework performance data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>

