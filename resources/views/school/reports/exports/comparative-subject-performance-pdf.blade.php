<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparative Subject Performance Analysis Report</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            background: #fff;
            font-size: 10px;
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
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 3px solid #17a2b8;
        }

        .report-info h3 {
            margin: 0 0 8px 0;
            color: #17a2b8;
            font-size: 14px;
        }

        .info-grid {
            display: table;
            width: 100%;
            font-size: 10px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 3px 10px 3px 0;
            width: 100px;
            color: #555;
        }

        .info-value {
            display: table-cell;
            padding: 3px 0;
            color: #333;
        }

        .subject-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .subject-title {
            background: #17a2b8;
            color: white;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 4px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 4px;
            overflow: hidden;
            table-layout: fixed;
        }

        .data-table thead {
            background: #17a2b8;
            color: white;
        }

        .data-table th {
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }

        .data-table th:nth-child(1) { width: 12%; }
        .data-table th:nth-child(2) { width: 11%; }
        .data-table th:nth-child(3) { width: 11%; }
        .data-table th:nth-child(4) { width: 11%; }
        .data-table th:nth-child(5) { width: 11%; }
        .data-table th:nth-child(6) { width: 11%; }
        .data-table th:nth-child(7) { width: 11%; }
        .data-table th:nth-child(8) { width: 22%; }

        .data-table td {
            padding: 6px 4px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
            word-wrap: break-word;
            text-align: center;
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
            padding: 8px 4px;
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
            margin-top: 30px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            .page-break {
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
                <h1>Comparative Subject Performance Analysis</h1>
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
                <div class="info-label">Period 1:</div>
                <div class="info-value">{{ $period1AcademicYear->year_name }} - {{ $period1ExamType->name }}</div>
            </div>
            @endif
            @if($period2AcademicYear && $period2ExamType)
            <div class="info-row">
                <div class="info-label">Period 2:</div>
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

    @if(!empty($comparativeData['comparison']))
        @foreach($comparativeData['comparison'] as $index => $comparison)
            @if($index > 0)
                <div class="page-break"></div>
            @endif

            <div class="subject-section">
                <div class="subject-title">{{ $comparison['subject_name'] }}</div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th rowspan="2">GRADE</th>
                            <th colspan="3">CURRENT PERIOD</th>
                            <th colspan="3">PREVIOUS PERIOD</th>
                            <th rowspan="2">Improvement/<br>Decline</th>
                        </tr>
                        <tr>
                            <th>MALE</th>
                            <th>FEMALE</th>
                            <th>TOTAL</th>
                            <th>MALE</th>
                            <th>FEMALE</th>
                            <th>TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grades = $gradeLetters ?? ['A', 'B', 'C', 'D', 'F'];
                        @endphp
                        @foreach($grades as $grade)
                        <tr>
                            <td class="font-weight-bold">{{ $grade }}</td>
                            <td>{{ $comparison['period1'] ? ($comparison['period1']['grade_breakdown'][$grade]['male'] ?? 0) : '-' }}</td>
                            <td>{{ $comparison['period1'] ? ($comparison['period1']['grade_breakdown'][$grade]['female'] ?? 0) : '-' }}</td>
                            <td class="font-weight-bold">{{ $comparison['period1'] ? ($comparison['period1']['grade_breakdown'][$grade]['total'] ?? 0) : '-' }}</td>
                            <td>{{ $comparison['period2'] ? ($comparison['period2']['grade_breakdown'][$grade]['male'] ?? 0) : '-' }}</td>
                            <td>{{ $comparison['period2'] ? ($comparison['period2']['grade_breakdown'][$grade]['female'] ?? 0) : '-' }}</td>
                            <td class="font-weight-bold">{{ $comparison['period2'] ? ($comparison['period2']['grade_breakdown'][$grade]['total'] ?? 0) : '-' }}</td>
                            <td class="{{ ($comparison['period1'] && $comparison['period2']) ? (($comparison['period2']['grade_breakdown'][$grade]['total'] ?? 0) > ($comparison['period1']['grade_breakdown'][$grade]['total'] ?? 0) ? 'text-success' : 'text-danger') : '' }}">
                                @if($comparison['period1'] && $comparison['period2'])
                                    @php
                                        $diff = ($comparison['period2']['grade_breakdown'][$grade]['total'] ?? 0) - ($comparison['period1']['grade_breakdown'][$grade]['total'] ?? 0);
                                    @endphp
                                    {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        <!-- Total Row -->
                        <tr class="table-info font-weight-bold">
                            <td>TOTAL</td>
                            @php
                                $totalCurrentMale = 0;
                                $totalCurrentFemale = 0;
                                $totalCurrentTotal = 0;
                                $totalPreviousMale = 0;
                                $totalPreviousFemale = 0;
                                $totalPreviousTotal = 0;

                                if ($comparison['period1']) {
                                    foreach ($grades as $grade) {
                                        $totalCurrentMale += $comparison['period1']['grade_breakdown'][$grade]['male'] ?? 0;
                                        $totalCurrentFemale += $comparison['period1']['grade_breakdown'][$grade]['female'] ?? 0;
                                        $totalCurrentTotal += $comparison['period1']['grade_breakdown'][$grade]['total'] ?? 0;
                                    }
                                }

                                if ($comparison['period2']) {
                                    foreach ($grades as $grade) {
                                        $totalPreviousMale += $comparison['period2']['grade_breakdown'][$grade]['male'] ?? 0;
                                        $totalPreviousFemale += $comparison['period2']['grade_breakdown'][$grade]['female'] ?? 0;
                                        $totalPreviousTotal += $comparison['period2']['grade_breakdown'][$grade]['total'] ?? 0;
                                    }
                                }
                            @endphp
                            <td>{{ $comparison['period1'] ? $totalCurrentMale : '-' }}</td>
                            <td>{{ $comparison['period1'] ? $totalCurrentFemale : '-' }}</td>
                            <td>{{ $comparison['period1'] ? $totalCurrentTotal : '-' }}</td>
                            <td>{{ $comparison['period2'] ? $totalPreviousMale : '-' }}</td>
                            <td>{{ $comparison['period2'] ? $totalPreviousFemale : '-' }}</td>
                            <td>{{ $comparison['period2'] ? $totalPreviousTotal : '-' }}</td>
                            <td class="{{ ($comparison['period1'] && $comparison['period2']) ? ($totalPreviousTotal > $totalCurrentTotal ? 'text-success' : 'text-danger') : '' }}">
                                @if($comparison['period1'] && $comparison['period2'])
                                    @php
                                        $totalDiff = $totalPreviousTotal - $totalCurrentTotal;
                                    @endphp
                                    {{ $totalDiff > 0 ? '+' : '' }}{{ $totalDiff }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach

        <!-- Absent Students Section -->
        @if(!empty($comparativeData['absent_students_period1']) || !empty($comparativeData['absent_students_period2']))
            <div class="page-break"></div>

            <!-- Period 1 Absent Students -->
            @if(!empty($comparativeData['absent_students_period1']))
                <div class="subject-section">
                    <div class="subject-title">STUDENTS ABSENT FROM EXAMINATIONS - CURRENT PERIOD</div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">#</th>
                                <th style="width: 25%;">STUDENT NAME</th>
                                <th style="width: 15%;">CLASS</th>
                                <th style="width: 15%;">STREAM</th>
                                <th style="width: 37%;">ABSENT SUBJECTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparativeData['absent_students_period1'] as $index => $absentStudent)
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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Period 2 Absent Students -->
            @if(!empty($comparativeData['absent_students_period2']))
                <div class="subject-section">
                    <div class="subject-title">STUDENTS ABSENT FROM EXAMINATIONS - PREVIOUS PERIOD</div>

                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 8%;">#</th>
                                <th style="width: 25%;">STUDENT NAME</th>
                                <th style="width: 15%;">CLASS</th>
                                <th style="width: 15%;">STREAM</th>
                                <th style="width: 37%;">ABSENT SUBJECTS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparativeData['absent_students_period2'] as $index => $absentStudent)
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
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No comparative data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 9px; margin-top: 5px;">Green (+) indicates improvement, Red (-) indicates decline in performance.</p>
    </div>
</body>
</html>