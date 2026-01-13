<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Performance Analysis Report</title>
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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
            text-align: center;
        }

        .summary-card h4 {
            margin: 0 0 8px 0;
            color: #17a2b8;
            font-size: 14px;
            font-weight: 600;
        }

        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin: 0;
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
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            border: 1px solid #dee2e6;
        }

        .data-table th.subject-header {
            width: 15%;
        }

        .data-table th.grade-header {
            width: 8%;
            text-align: center;
            color: #000;
        }

        .data-table th.totals-header {
            width: 10%;
            text-align: center;
        }

        .data-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
            word-wrap: break-word;
            border: 1px solid #dee2e6;
        }

        .data-table tbody tr:hover {
            background: #f8f9fa;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .subject-header {
            background-color: #f8f9fa;
            font-weight: 700;
            font-size: 15px;
            vertical-align: middle !important;
            text-align: center;
        }

        .grade-header {
            background-color: #e9ecef;
            font-weight: 600;
            text-align: center;
        }

        .totals-row {
            background: #f8f9fa;
            font-weight: bold;
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

        .absent-students-section {
            margin-top: 30px;
            page-break-before: always;
        }

        .absent-students-section h3 {
            color: #17a2b8;
            margin-bottom: 15px;
            border-bottom: 2px solid #17a2b8;
            padding-bottom: 8px;
        }

        .absent-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .absent-table th {
            background: #dc3545;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            border: 1px solid #dee2e6;
        }

        .absent-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #dee2e6;
            font-size: 12px;
            border: 1px solid #dee2e6;
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

        .reference-info {
            font-size: 8px;
            color: #666;
        }

        @media print {
            body {
                padding: 10px;
            }

            .data-table {
                page-break-inside: avoid;
            }

            .absent-students-section {
                page-break-before: always;
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
                <h1>Subject Performance Analysis Report</h1>
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

    <!-- Summary Statistics -->
    <div class="summary-cards">
        <div class="summary-card">
            <h4>Total Subjects</h4>
            <div class="value">{{ $subjectWiseData['summary']['total_subjects'] }}</div>
        </div>
        <div class="summary-card">
            <h4>Total Students</h4>
            <div class="value">{{ $subjectWiseData['summary']['total_students'] }}</div>
        </div>
        <div class="summary-card">
            <h4>Overall Pass Rate</h4>
            <div class="value">{{ $subjectWiseData['summary']['overall_pass_rate'] }}%</div>
        </div>
        <div class="summary-card">
            <h4>Average Score</h4>
            <div class="value">{{ $subjectWiseData['summary']['average_score'] }}</div>
        </div>
    </div>

    @if(!empty($subjectWiseData['subjects']))
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" class="subject-header">Subject</th>
                    @if(!empty($subjectWiseData['subjects']) && !empty($subjectWiseData['subjects'][0]['grade_breakdown']))
                        @foreach(array_keys($subjectWiseData['subjects'][0]['grade_breakdown']) as $grade)
                            <th colspan="3" class="grade-header">{{ $grade }}</th>
                        @endforeach
                    @endif
                    <th colspan="3" class="grade-header">Totals</th>
                </tr>
                <tr>
                    @if(!empty($subjectWiseData['subjects']) && !empty($subjectWiseData['subjects'][0]['grade_breakdown']))
                        @foreach(array_keys($subjectWiseData['subjects'][0]['grade_breakdown']) as $grade)
                            <th class="grade-header">F</th>
                            <th class="grade-header">M</th>
                            <th class="grade-header">Total</th>
                        @endforeach
                    @endif
                    <th class="grade-header">F</th>
                    <th class="grade-header">M</th>
                    <th class="grade-header">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjectWiseData['subjects'] as $subject)
                <tr>
                    <td class="subject-header">{{ $subject['subject_name'] }}</td>
                    @if(!empty($subject['grade_breakdown']))
                        @foreach($subject['grade_breakdown'] as $grade => $counts)
                            <td class="number">{{ $counts['female'] }}</td>
                            <td class="number">{{ $counts['male'] }}</td>
                            <td class="number"><strong>{{ $counts['total'] }}</strong></td>
                        @endforeach
                    @endif
                    <td class="number">{{ $subject['totals']['female'] }}</td>
                    <td class="number">{{ $subject['totals']['male'] }}</td>
                    <td class="number"><strong>{{ $subject['totals']['total'] }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No subject performance data found for the selected criteria.</p>
        </div>
    @endif

    <!-- Absent Students Section -->
    @if(!empty($subjectWiseData['absentStudents']))
    <div class="absent-students-section">
        <h3>STUDENTS ABSENT FROM EXAMINATIONS</h3>
        <table class="absent-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Absent Subjects</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjectWiseData['absentStudents'] as $absentStudent)
                <tr>
                    <td>{{ $absentStudent['student']->first_name }} {{ $absentStudent['student']->last_name }}</td>
                    <td>{{ $absentStudent['student']->class->name ?? '-' }}</td>
                    <td>{{ $absentStudent['student']->stream->name ?? '-' }}</td>
                    <td>
                        @if(!empty($absentStudent['absent_subjects']))
                            {{ implode(', ', $absentStudent['absent_subjects']) }}
                        @else
                            ABSENT
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 10px; margin-top: 5px;">Subject Performance Analysis by Grades and Gender</p>
    </div>
</body>
</html>