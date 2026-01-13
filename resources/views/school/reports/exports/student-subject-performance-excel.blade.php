<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Subject Performance and Progress Analysis Report</title>
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
        }

        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
        }

        .student-section {
            margin-bottom: 25px;
        }

        .student-header {
            background: #17a2b8;
            color: white;
            padding: 8px 10px;
            margin-bottom: 12px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
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
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }

        .data-table th:nth-child(1) { width: 18%; }
        .data-table th:nth-child(2) { width: 12%; }
        .data-table th:nth-child(3) { width: 12%; }
        .data-table th:nth-child(4) { width: 12%; }
        .data-table th:nth-child(5) { width: 12%; }
        .data-table th:nth-child(6) { width: 12%; }
        .data-table th:nth-child(7) { width: 12%; }

        .data-table td {
            padding: 6px 6px;
            border-bottom: 1px solid #dee2e6;
            word-wrap: break-word;
            text-align: center;
        }

        .data-table tbody tr:hover {
            background: #f8f9fa;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .student-name {
            font-weight: bold;
            text-align: left;
            font-size: 11px;
        }

        .subject-name {
            font-weight: bold;
            color: #17a2b8;
        }

        .grade-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }

        .grade-current {
            background: #007bff;
        }

        .grade-previous {
            background: #6c757d;
        }

        .improvement-positive {
            color: #28a745;
            font-weight: bold;
        }

        .improvement-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .improvement-neutral {
            color: #ffc107;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
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
            <div class="title-section">
                <h1>Student Subject Performance & Progress Analysis</h1>
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
            @if($period1AcademicYear && $period1ExamType)
            <div class="info-row">
                <div class="info-label">Current Period:</div>
                <div class="info-value">{{ $period1AcademicYear->year_name }} - {{ $period1ExamType->name }}</div>
            </div>
            @endif
            @if($period2AcademicYear && $period2ExamType)
            <div class="info-row">
                <div class="info-label">Previous Period:</div>
                <div class="info-value">{{ $period2AcademicYear->year_name }} - {{ $period2ExamType->name }}</div>
            </div>
            @endif
            @if($selectedClass)
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ $selectedClass->name }}</div>
            </div>
            @endif
            @if($selectedStream)
            <div class="info-row">
                <div class="info-label">Stream:</div>
                <div class="info-value">{{ $selectedStream->name }}</div>
            </div>
            @endif
        </div>
    </div>

    @if(!empty($studentPerformanceData['students']))
        @foreach($studentPerformanceData['students'] as $studentData)
            @php
                $studentSubjects = array_filter($studentData['subjects'], function($subject) {
                    return $subject['current_period'] || $subject['previous_period'];
                });
            @endphp

            @if(count($studentSubjects) > 0)
                <div class="student-section">
                    <div class="student-header">
                        {{ $studentData['student']->first_name }} {{ $studentData['student']->last_name }}
                        @if($studentData['student']->class)
                            - {{ $studentData['student']->class->name }}
                            @if($studentData['student']->stream)
                                {{ $studentData['student']->stream->name }}
                            @endif
                        @endif
                    </div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>SUBJECT</th>
                                <th colspan="3">CURRENT PERFORMANCE</th>
                                <th colspan="3">PREVIOUS PERFORMANCE</th>
                                <th>Improvement/Decline</th>
                            </tr>
                            <tr>
                                <th></th>
                                <th>Grade</th>
                                <th>Marks (%)</th>
                                <th>Class Rank</th>
                                <th>Grade</th>
                                <th>Marks (%)</th>
                                <th>Class Rank</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentData['subjects'] as $subject)
                                @if($subject['current_period'] || $subject['previous_period'])
                                <tr>
                                    <td class="subject-name">{{ $subject['subject_name'] }}</td>

                                    <!-- Current Period -->
                                    <td>
                                        @if($subject['current_period'])
                                            <span class="grade-badge grade-current">{{ $subject['current_period']['grade'] }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($subject['current_period'])
                                            {{ $subject['current_period']['marks_percentage'] }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($subject['current_period'])
                                            {{ $subject['current_period']['class_rank'] }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <!-- Previous Period -->
                                    <td>
                                        @if($subject['previous_period'])
                                            <span class="grade-badge grade-previous">{{ $subject['previous_period']['grade'] }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($subject['previous_period'])
                                            {{ $subject['previous_period']['marks_percentage'] }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($subject['previous_period'])
                                            {{ $subject['previous_period']['class_rank'] }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <!-- Improvement/Decline -->
                                    <td>
                                        @if($subject['improvement'] !== null)
                                            @if($subject['improvement'] > 0)
                                                <span class="improvement-positive">+{{ $subject['improvement'] }}%</span>
                                            @elseif($subject['improvement'] < 0)
                                                <span class="improvement-negative">{{ $subject['improvement'] }}%</span>
                                            @else
                                                <span class="improvement-neutral">0%</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endforeach
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No student performance data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 9px; margin-top: 5px;">Green (+) indicates improvement, Red (-) indicates decline in performance.</p>
    </div>
</body>
</html>