<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overall Analysis Report</title>
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
            min-width: 200px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .summary-card .icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: #17a2b8;
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

        .data-table th:nth-child(1) { width: 5%; } /* S/N */
        .data-table th:nth-child(2) { width: 12%; } /* Class */
        .data-table th:nth-child(3) { width: 12%; } /* Stream */
        .data-table th:nth-child(4) { width: 8%; } /* Students */
        .data-table th:nth-child(5) { width: 6%; } /* A */
        .data-table th:nth-child(6) { width: 6%; } /* B */
        .data-table th:nth-child(7) { width: 6%; } /* C */
        .data-table th:nth-child(8) { width: 6%; } /* D */
        .data-table th:nth-child(9) { width: 6%; } /* E */
        .data-table th:nth-child(10) { width: 8%; } /* Mean */
        .data-table th:nth-child(11) { width: 8%; } /* Grade */
        .data-table th:nth-child(12) { width: 17%; } /* Teacher */

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

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 3px;
            color: white;
        }

        .badge-success { background-color: #28a745; }
        .badge-primary { background-color: #007bff; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; }
        .badge-info { background-color: #17a2b8; }

        .grade-distribution {
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .grade-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .grade-table th {
            background: #17a2b8;
            color: white;
            padding: 8px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
        }

        .grade-table td {
            padding: 8px;
            text-align: center;
            border-bottom: 1px solid #dee2e6;
        }

        .grade-table tfoot td {
            background: #f8f9fa;
            font-weight: bold;
            border-top: 2px solid #17a2b8;
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
                <h1>Overall Analysis Report</h1>
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
        </div>
    </div>

    @if(count($analysis) > 0)
        @php
            $gradeLetters = $gradeLetters ?? ['A', 'B', 'C', 'D', 'E'];
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th class="number">Students</th>
                    @foreach($gradeLetters as $grade)
                        <th class="number">{{ $grade }}</th>
                    @endforeach
                    <th class="number">Mean</th>
                    <th>Grade</th>
                    <th>Class Teacher</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $serial = 1;
                    $currentClassId = null;
                    $classSubtotals = $classSubtotals ?? [];
                @endphp
                @foreach($analysis as $item)
                    @if($currentClassId !== null && $currentClassId != $item['class']->id)
                        @php
                            $classSubtotal = $classSubtotals[$currentClassId] ?? null;
                        @endphp
                        @if($classSubtotal)
                        <tr class="table-info">
                            <td colspan="3" class="text-right font-weight-bold">SUBTOTAL - {{ $classSubtotal['class']->name }}</td>
                            <td class="number font-weight-bold">{{ $classSubtotal['students'] }}</td>
                            @foreach($gradeLetters as $grade)
                                <td class="number font-weight-bold">{{ $classSubtotal['grade_counts'][$grade] ?? 0 }}</td>
                            @endforeach
                            <td class="number font-weight-bold">{{ number_format($classSubtotal['total_mean'], 2) }}</td>
                            <td class="text-center font-weight-bold">
                                {{ str_replace('>', '', $classSubtotal['grade']) }}
                            </td>
                            <td></td>
                        </tr>
                        @endif
                    @endif
                <tr>
                    <td class="text-center">{{ $serial++ }}</td>
                    <td class="text-left">{{ $item['class']->name }}</td>
                    <td class="text-left">{{ $item['stream']->name }}</td>
                    <td class="number">{{ $item['students'] }}</td>
                    @foreach($gradeLetters as $grade)
                        <td class="number">{{ $item['grade_counts'][$grade] ?? 0 }}</td>
                    @endforeach
                    <td class="number">{{ number_format($item['class_mean'], 2) }}</td>
                    <td class="text-center font-weight-bold">
                        {{ str_replace('>', '', $item['grade']) }}
                    </td>
                    <td class="text-left">{{ $item['class_teacher'] }}</td>
                </tr>
                @php
                    $currentClassId = $item['class']->id;
                @endphp
                @endforeach
                
                @if($currentClassId !== null)
                    @php
                        $classSubtotal = $classSubtotals[$currentClassId] ?? null;
                    @endphp
                    @if($classSubtotal)
                    <tr class="table-info">
                        <td colspan="3" class="text-right font-weight-bold">SUBTOTAL - {{ $classSubtotal['class']->name }}</td>
                        <td class="number font-weight-bold">{{ $classSubtotal['students'] }}</td>
                        @foreach($gradeLetters as $grade)
                            <td class="number font-weight-bold">{{ $classSubtotal['grade_counts'][$grade] ?? 0 }}</td>
                        @endforeach
                        <td class="number font-weight-bold">{{ number_format($classSubtotal['total_mean'], 2) }}</td>
                        <td class="text-center font-weight-bold">
                            {{ str_replace('>', '', $classSubtotal['grade']) }}
                        </td>
                        <td></td>
                    </tr>
                    @endif
                @endif
            </tbody>
            <tfoot>
                <!-- Subtotals -->
                @foreach($subtotals as $categoryName => $subtotal)
                <tr class="table-warning">
                    <td colspan="3" class="text-right font-weight-bold">SUBTOTAL - {{ $categoryName }}</td>
                    <td class="number font-weight-bold">{{ $subtotal['students'] }}</td>
                    @foreach($gradeLetters as $grade)
                        <td class="number font-weight-bold">{{ $subtotal['grade_counts'][$grade] ?? 0 }}</td>
                    @endforeach
                    <td class="number font-weight-bold">{{ number_format($subtotal['total_mean'], 2) }}</td>
                    <td class="text-center font-weight-bold">
                        {{ str_replace('>', '', $subtotal['grade']) }}
                    </td>
                    <td></td>
                </tr>
                @endforeach

                <!-- Grand Total -->
                <tr class="table-primary">
                    <td colspan="3" class="text-right font-weight-bold">GRAND TOTAL</td>
                    <td class="number font-weight-bold">{{ $grandTotal['students'] }}</td>
                    @foreach($gradeLetters as $grade)
                        <td class="number font-weight-bold">{{ $grandTotal['grade_counts'][$grade] ?? 0 }}</td>
                    @endforeach
                    <td class="number font-weight-bold">{{ number_format($grandTotal['total_mean'], 2) }}</td>
                    <td class="text-center font-weight-bold">
                        {{ str_replace('>', '', $grandTotal['grade']) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <!-- Grade Distribution Summary -->
        <div class="grade-distribution">
            <h3 style="color: #17a2b8; margin-bottom: 15px;">Grade Distribution Summary</h3>
            <table class="grade-table">
                <thead>
                    <tr>
                        <th>Grade</th>
                        <th>Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalStudents = $grandTotal['students'];
                        $gradeLetters = $gradeLetters ?? ['A', 'B', 'C', 'D', 'E'];
                    @endphp
                    @foreach($gradeLetters as $grade)
                    <tr>
                        <td>{{ $grade }}</td>
                        <td>{{ $grandTotal['grade_counts'][$grade] ?? 0 }}</td>
                        <td>{{ $totalStudents > 0 ? number_format((($grandTotal['grade_counts'][$grade] ?? 0) / $totalStudents) * 100, 1) : 0 }}%</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="font-weight: bold;">Total</td>
                        <td style="font-weight: bold;">{{ $totalStudents }}</td>
                        <td style="font-weight: bold;">100%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No analysis data found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 10px; margin-top: 5px;">Grade badges are color-coded: Green (A), Blue (B), Yellow (C), Orange (D), Red (E).</p>
    </div>
</body>
</html>