<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Results Report</title>
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
            margin-bottom: 10px;
        }

        .logo-section {
            flex-shrink: 0;
        }

        .company-logo {
            max-height: 70px;
            max-width: 100px;
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
            font-size: 32px;
            font-weight: bold;
        }

        .company-name {
            color: #333;
            margin: 5px 0;
            font-size: 20px;
            font-weight: 600;
        }

        .header .subtitle {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 18px;
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

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #17a2b8;
        }

        .data-table thead {
            background: #17a2b8;
            color: white;
        }

        .data-table th {
            padding: 12px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            border: 1px solid #fff;
        }

        .data-table td {
            padding: 12px 4px;
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

        .summary-section {
            margin-bottom: 20px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border: 2px solid #17a2b8;
        }

        .summary-table th,
        .summary-table td {
            padding: 14px 12px;
            border: 1px solid #dee2e6;
            text-align: center;
            font-size: 13px;
        }

        .summary-table th {
            background: #17a2b8;
            color: white;
            font-weight: bold;
        }

        .summary-table .total-row {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        // Ensure examData is set and has the expected structure
        if (!isset($examData) || !is_array($examData)) {
            $examData = ['results' => [], 'subjects' => collect(), 'gradeLetters' => ['A', 'B', 'C', 'D', 'E']];
        }
        if (!isset($examData['results'])) {
            $examData['results'] = [];
        }
        if (!isset($examData['subjects'])) {
            $examData['subjects'] = collect();
        }
        if (!isset($examData['gradeLetters'])) {
            $examData['gradeLetters'] = ['A', 'B', 'C', 'D', 'E'];
        }
    @endphp
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
                <h1>Examination Results Report</h1>
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
                <div class="company-info">
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
        <div style="font-size: 12px; line-height: 1.5;">
            @if(isset($filters['branch']))
                <strong>Branch:</strong> {{ $filters['branch'] }}
            @endif
            @if(isset($filters['academic_year']))
                | <strong>Academic Year:</strong> {{ $filters['academic_year'] }}
            @endif
            @if(isset($filters['exam_type']))
                | <strong>Exam Type:</strong> {{ $filters['exam_type'] }}
            @endif
            @if(isset($filters['class']))
                | <strong>Class:</strong> {{ $filters['class'] }}
            @endif
            @if(isset($filters['stream']))
                | <strong>Stream:</strong> {{ $filters['stream'] }}
            @endif
        </div>
    </div>

    <!-- Overall Performance Summary -->
    @if(isset($examData['results']) && is_array($examData['results']) && !empty($examData['results']))
    <div class="summary-section">
        <h3 style="color: #17a2b8; margin-bottom: 15px;">OVERALL PERFORMANCE LEVELS SUMMARY</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>GENDER</th>
                    @php
                        $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'E'];
                    @endphp
                    @foreach($gradeLetters as $grade)
                        <th>{{ $grade }}</th>
                    @endforeach
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $girlsGrades = [];
                    $boysGrades = [];
                    foreach ($gradeLetters as $letter) {
                        $girlsGrades[$letter] = 0;
                        $boysGrades[$letter] = 0;
                    }
                    $totalGirls = 0;
                    $totalBoys = 0;

                    foreach($examData['results'] as $result) {
                        $grade = $result['grade'] ?? 'N/A';
                        $gender = strtolower($result['student']->gender ?? '');

                        // Handle different gender representations
                        $isFemale = in_array($gender, ['f', 'female', 'woman', 'girl']);
                        $isMale = in_array($gender, ['m', 'male', 'man', 'boy']);

                        if ($isFemale && isset($girlsGrades[$grade])) {
                            $girlsGrades[$grade]++;
                            $totalGirls++;
                        } elseif ($isMale && isset($boysGrades[$grade])) {
                            $boysGrades[$grade]++;
                            $totalBoys++;
                        }
                    }

                    $totalGrades = [];
                    foreach ($gradeLetters as $letter) {
                        $totalGrades[$letter] = ($girlsGrades[$letter] ?? 0) + ($boysGrades[$letter] ?? 0);
                    }
                    $grandTotal = $totalGirls + $totalBoys;
                @endphp
                <tr>
                    <td><strong>GIRLS</strong></td>
                    @foreach($gradeLetters as $grade)
                        <td>{{ $girlsGrades[$grade] ?? 0 }}</td>
                    @endforeach
                    <td><strong>{{ $totalGirls }}</strong></td>
                </tr>
                <tr>
                    <td><strong>BOYS</strong></td>
                    @foreach($gradeLetters as $grade)
                        <td>{{ $boysGrades[$grade] ?? 0 }}</td>
                    @endforeach
                    <td><strong>{{ $totalBoys }}</strong></td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    @foreach($gradeLetters as $grade)
                        <td><strong>{{ $totalGrades[$grade] ?? 0 }}</strong></td>
                    @endforeach
                    <td><strong>{{ $grandTotal }}</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($examData['results']) && is_array($examData['results']) && count($examData['results']) > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 15%;">Student Name</th>
                    <th style="width: 6%;">Stream</th>
                    <th style="width: 4%;">Sex</th>
                    @foreach($examData['subjects'] as $subject)
                        <th style="width: 4%;" class="text-center">{{ $subject->short_name ?? substr($subject->name, 0, 4) }}</th>
                    @endforeach
                    <th style="width: 7%;" class="text-center">Total</th>
                    <th style="width: 6%;" class="text-center">Avg</th>
                    <th style="width: 5%;" class="text-center">Grade</th>
                    <th style="width: 4%;" class="text-center">Pos</th>
                    <th style="width: 12%;">Remark</th>
                </tr>
            </thead>
            <tbody>
                @foreach($examData['results'] as $index => $result)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $result['student']->first_name }} {{ $result['student']->last_name }}</td>
                    <td class="text-center">{{ $result['student']->stream ? $result['student']->stream->name : '-' }}</td>
                    <td class="text-center">{{ ucfirst(substr($result['student']->gender, 0, 1)) }}</td>
                    @foreach($examData['subjects'] as $subject)
                        <td class="text-center" style="border: 1px solid #dee2e6;">
                            @if(isset($result['marks'][$subject->id]) && $result['marks'][$subject->id] !== 'ABS' && $result['marks'][$subject->id] !== 'EXEMPT')
                                {{ $result['marks'][$subject->id] }}
                            @else
                                <span style="color: #999;">{{ $result['marks'][$subject->id] ?? '-' }}</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="text-center" style="border: 1px solid #dee2e6; font-weight: bold;"><strong>{{ $result['total'] }}</strong></td>
                    <td class="text-center" style="border: 1px solid #dee2e6;">{{ number_format($result['average'], 1) }}</td>
                    <td class="text-center" style="border: 1px solid #dee2e6; font-weight: bold;"><strong>{{ $result['grade'] }}</strong></td>
                    <td class="text-center" style="border: 1px solid #dee2e6;">{{ $result['position'] }}</td>
                    <td style="border: 1px solid #dee2e6;">{{ $result['remark'] }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">SUBJECT TOTALS:</td>
                    @foreach($examData['subjects'] as $subject)
                        <td class="number" style="font-weight: bold;">{{ $examData['subjectTotals'][$subject->id] ?? 0 }}</td>
                    @endforeach
                    <td class="number" style="font-weight: bold;">{{ $examData['classTotal'] }}</td>
                    <td colspan="4"></td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">CLASS AVERAGE:</td>
                    @foreach($examData['subjects'] as $subject)
                        <td class="number" style="font-weight: bold;">{{ isset($examData['subjectAverages'][$subject->id]) ? number_format($examData['subjectAverages'][$subject->id], 1) : '-' }}</td>
                    @endforeach
                    <td class="number" style="font-weight: bold;">{{ number_format($examData['classAverage'], 1) }}</td>
                    <td colspan="4"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Subject Performance Analysis -->
        @if(isset($examData['subjectPerformance']['by_stream']) && $examData['subjectPerformance']['by_stream'])
            @foreach($examData['subjectPerformance']['streams'] as $streamId => $streamData)
                <div style="page-break-before: always; margin-top: 40px;">
                    <h4 style="color: #17a2b8; margin-bottom: 15px;">SUBJECTS PERFORMANCE ANALYSIS - {{ $streamData['stream']->name ?? 'Unknown Stream' }} - TERMINAL EXAMINATION</h4>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th style="width: 20%; font-size: 13px; border: 1px solid #fff;">Subject Name</th>
                                <th style="width: 15%; font-size: 13px; border: 1px solid #fff;">Teacher's Name</th>
                                @php
                                    $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'E'];
                                @endphp
                                @foreach($gradeLetters as $grade)
                                    <th style="width: 5%; font-size: 13px; border: 1px solid #fff;" class="text-center">{{ $grade }}</th>
                                @endforeach
                                <th style="width: 6%; font-size: 13px; border: 1px solid #fff;" class="number">Total</th>
                                <th style="width: 6%; font-size: 13px; border: 1px solid #fff;" class="number">GPA</th>
                                <th style="width: 6%; font-size: 13px; border: 1px solid #fff;" class="text-center">Grade</th>
                                <th style="width: 15%; font-size: 13px; border: 1px solid #fff;">Competency Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($streamData['subjects'] as $subjectId => $performance)
                                <tr>
                                    <td>{{ $performance['subject']->name }}</td>
                                    <td>
                                        {{ $performance['teacher'] }}
                                        @if(isset($performance['teacher_stream']) && $performance['teacher_stream'])
                                            <br><small style="color: #666;">({{ $performance['teacher_stream'] }})</small>
                                        @endif
                                    </td>
                                    @php
                                        $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'E'];
                                    @endphp
                                    @foreach($gradeLetters as $grade)
                                        <td class="text-center">{{ $performance['gradeCounts'][$grade] ?? 0 }}</td>
                                    @endforeach
                                    <td class="number">{{ $performance['total'] }}</td>
                                    <td class="number">{{ number_format($performance['gpa'], 4) }}</td>
                                    <td class="text-center">
                                        @if(isset($performance['subjectGrade']))
                                            <strong>{{ $performance['subjectGrade'] }}</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $performance['competencyLevel'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @elseif(isset($examData['subjectPerformance']) && !empty($examData['subjectPerformance']))
            <!-- Class-level Subject Performance Analysis -->
            <div style="page-break-before: always; margin-top: 40px;">
                <h4 style="color: #17a2b8; margin-bottom: 15px;">CLASS SUBJECTS PERFORMANCE ANALYSIS - TERMINAL EXAMINATION</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 20%; font-size: 13px; border: 1px solid #fff;">Subject Name</th>
                            <th style="width: 15%; font-size: 13px; border: 1px solid #fff;">Teacher's Name</th>
                            @php
                                $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'E'];
                            @endphp
                            @foreach($gradeLetters as $grade)
                                <th style="width: 5%; font-size: 13px; border: 1px solid #fff;" class="text-center">{{ $grade }}</th>
                            @endforeach
                            <th style="width: 6%; font-size: 13px; border: 1px solid #fff;" class="number">Total</th>
                            <th style="width: 6%; font-size: 13px; border: 1px solid #fff;" class="number">GPA</th>
                            <th style="width: 6%; font-size: 13px; border: 1px solid #fff;" class="text-center">Grade</th>
                            <th style="width: 15%; font-size: 13px; border: 1px solid #fff;">Competency Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examData['subjects'] as $subject)
                            @php
                                $performance = $examData['subjectPerformance'][$subject->id] ?? null;
                                $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'E'];
                            @endphp
                            <tr>
                                <td>{{ $subject->name }}</td>
                                <td>-</td>
                                @foreach($gradeLetters as $grade)
                                    <td class="text-center">{{ $performance ? ($performance['gradeCounts'][$grade] ?? 0) : '-' }}</td>
                                @endforeach
                                <td class="number">{{ $performance ? $performance['total'] : '-' }}</td>
                                <td class="number">{{ $performance ? number_format($performance['gpa'], 4) : '-' }}</td>
                                <td class="text-center">
                                    @if($performance && isset($performance['subjectGrade']))
                                        <strong>{{ $performance['subjectGrade'] }}</strong>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $performance ? $performance['competencyLevel'] : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Absent Students -->
        @if(isset($examData['absentStudents']) && !empty($examData['absentStudents']))
            <div style="page-break-before: always; margin-top: 40px;">
                <h4 style="color: #17a2b8; margin-bottom: 15px;">STUDENTS ABSENT FROM EXAMINATIONS</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 5%; font-size: 13px; border: 1px solid #fff;" class="text-center">#</th>
                            <th style="width: 25%; font-size: 13px; border: 1px solid #fff;">Student Name</th>
                            <th style="width: 15%; font-size: 13px; border: 1px solid #fff;" class="text-center">Class</th>
                            <th style="width: 15%; font-size: 13px; border: 1px solid #fff;" class="text-center">Stream</th>
                            <th style="width: 30%; font-size: 13px; border: 1px solid #fff;">Absent Subjects</th>
                            <th style="width: 10%; font-size: 13px; border: 1px solid #fff;" class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($examData['absentStudents'] as $index => $absentStudent)
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
            <p>No examination results found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 10px; margin-top: 5px;">Grade colors: Green for high performance, Red for low performance. ABS = Absent, EXEMPT = Exempted.</p>
    </div>
</body>
</html>