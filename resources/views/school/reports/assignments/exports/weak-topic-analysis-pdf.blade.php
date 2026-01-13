<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weak Topic Analysis Report</title>
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
            border-bottom: 3px solid #ffc107;
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
            color: #ffc107;
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
            border-left: 4px solid #ffc107;
        }
        
        .report-info h3 {
            margin: 0 0 10px 0;
            color: #ffc107;
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
            background: #ffc107;
            color: #333;
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
        .data-table th:nth-child(6) { width: 10%; }
        .data-table th:nth-child(7) { width: 10%; }
        .data-table th:nth-child(8) { width: 9%; }
        .data-table th:nth-child(9) { width: 9%; }
        
        .data-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 10px;
            word-wrap: break-word;
        }
        
        .data-table tbody tr:hover {
            background: #fffbf0;
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tfoot {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .data-table tfoot td {
            border-top: 2px solid #ffc107;
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
                <h1>Weak Topic Analysis Report</h1>
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
        <div class="info-row" style="margin-top: 10px;">
            <div class="info-label">Note:</div>
            <div class="info-value">This report shows topics/assignments with average score &lt; 60% or pass rate &lt; 50%</div>
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
                    <th class="text-center">Avg Score</th>
                    <th class="text-center">Pass Rate</th>
                    <th class="text-center">Struggling</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalWeakTopics = 0;
                    $totalStruggling = 0;
                    $totalStudents = 0;
                    $totalAvgScore = 0;
                    $totalPassRate = 0;
                @endphp
                @foreach($data as $index => $row)
                    @php
                        $totalWeakTopics++;
                        $totalStruggling += (int)$row['struggling_count'];
                        $totalStudents += (int)$row['total_students'];
                        $totalAvgScore += (float)$row['average_score'];
                        $totalPassRate += (float)$row['pass_rate'];
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $row['assignment_id'] }}</td>
                        <td>{{ $row['title'] }}</td>
                        <td>{{ $row['subject'] }}</td>
                        <td>{{ $row['class_stream'] }}</td>
                        <td class="text-center">
                            @php
                                $score = (float)$row['average_score'];
                            @endphp
                            @if($score >= 60)
                                <span class="text-success">{{ $row['average_score'] }}%</span>
                            @elseif($score >= 40)
                                <span class="text-warning">{{ $row['average_score'] }}%</span>
                            @else
                                <span class="text-danger">{{ $row['average_score'] }}%</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $rate = (float)$row['pass_rate'];
                            @endphp
                            @if($rate >= 70)
                                <span class="text-success">{{ $row['pass_rate'] }}%</span>
                            @elseif($rate >= 50)
                                <span class="text-warning">{{ $row['pass_rate'] }}%</span>
                            @else
                                <span class="text-danger">{{ $row['pass_rate'] }}%</span>
                            @endif
                        </td>
                        <td class="text-center text-danger">{{ $row['struggling_count'] }}</td>
                        <td class="text-center">{{ $row['total_students'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: bold;">TOTAL WEAK TOPICS:</td>
                    <td class="text-center" style="font-weight: bold;">
                        @php
                            $overallAvgScore = $totalWeakTopics > 0 ? ($totalAvgScore / $totalWeakTopics) : 0;
                        @endphp
                        {{ number_format($overallAvgScore, 2) }}%
                    </td>
                    <td class="text-center" style="font-weight: bold;">
                        @php
                            $overallPassRate = $totalWeakTopics > 0 ? ($totalPassRate / $totalWeakTopics) : 0;
                        @endphp
                        {{ number_format($overallPassRate, 2) }}%
                    </td>
                    <td class="text-center text-danger" style="font-weight: bold;">{{ $totalStruggling }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ $totalStudents }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Weak Topics Found</h3>
            <p>All topics are performing well. No weak topics identified for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>

