<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Master Examination Timetable</title>
    <style>
        @page {
            margin: 20px 25px;
            size: A4 landscape;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
            line-height: 1.5;
            color: #2d3436;
            background: white;
        }

        /* Header Section */
        .header {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 15px;
            border-bottom: 3px solid #2c3e50;
            position: relative;
        }

        .header::after {
            content: '';
            display: block;
            width: 100%;
            height: 1px;
            background: #2c3e50;
            position: absolute;
            bottom: -6px;
            left: 0;
        }

        .logo-section {
            margin-bottom: 8px;
        }

        .institution-name {
            font-size: 18pt;
            font-weight: bold;
            color: #1a252f;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 3px;
        }

        .institution-subtitle {
            font-size: 9pt;
            color: #636e72;
            letter-spacing: 1px;
        }

        .document-title {
            font-size: 14pt;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            background: #2c3e50;
            margin: 12px auto 0;
            padding: 8px 30px;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-radius: 3px;
        }

        /* Info Section */
        .info-section {
            margin-bottom: 15px;
            padding: 12px 15px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
            border-radius: 0 5px 5px 0;
        }

        .info-section table {
            width: 100%;
        }

        .info-section td {
            padding: 4px 15px;
            font-size: 9pt;
        }

        .info-label {
            font-weight: bold;
            color: #7f8c8d;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-weight: bold;
            color: #2c3e50;
            font-size: 10pt;
        }

        /* Main Timetable */
        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
            border: 2px solid #2c3e50;
        }

        .timetable thead th {
            background: #2c3e50;
            color: white;
            padding: 10px 6px;
            text-transform: uppercase;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            border: 1px solid #34495e;
            text-align: center;
            vertical-align: middle;
        }

        .timetable tbody td {
            padding: 8px 6px;
            border: 1px solid #bdc3c7;
            vertical-align: middle;
            text-align: left;
        }

        .timetable tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .timetable tbody tr:nth-child(even) {
            background: #f7f9fc;
        }

        .timetable tbody tr:hover {
            background: #eef2f7;
        }

        /* Table Cell Styling */
        .sn-cell {
            text-align: center;
            font-weight: bold;
            color: #7f8c8d;
            width: 30px;
        }

        .date-cell {
            font-weight: bold;
            white-space: nowrap;
            color: #2c3e50;
            text-align: center;
        }

        .day-cell {
            font-weight: 600;
            text-align: center;
            color: #34495e;
        }

        .day-monday { color: #3498db; }
        .day-tuesday { color: #9b59b6; }
        .day-wednesday { color: #27ae60; }
        .day-thursday { color: #e67e22; }
        .day-friday { color: #e74c3c; }
        .day-saturday { color: #1abc9c; }
        .day-sunday { color: #e91e63; }

        .time-cell {
            white-space: nowrap;
            font-weight: bold;
            color: #2980b9;
            text-align: center;
            font-size: 8.5pt;
        }

        .program-cell {
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
        }

        .level-cell {
            text-align: center;
            font-weight: bold;
            color: #8e44ad;
        }

        .course-cell {
            font-weight: 500;
            color: #2d3436;
        }

        .code-cell {
            font-family: DejaVu Sans Mono, monospace;
            font-size: 7.5pt;
            text-align: center;
            color: #636e72;
            background: #ecf0f1;
            padding: 4px 6px;
        }

        .venue-cell {
            font-weight: 600;
            color: #27ae60;
            text-align: center;
        }

        .supervisor-cell {
            font-weight: 500;
            color: #34495e;
        }

        /* Footer Section */
        .footer {
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .footer-notes {
            font-size: 8pt;
            color: #555;
            margin-bottom: 20px;
            padding: 12px 15px;
            background: #fef9e7;
            border: 1px solid #f7dc6f;
            border-radius: 5px;
        }

        .footer-notes-title {
            font-weight: bold;
            color: #d68910;
            font-size: 9pt;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-notes ul {
            margin-left: 18px;
            color: #7d6608;
        }

        .footer-notes li {
            margin-bottom: 4px;
            line-height: 1.5;
        }

        /* Signatures Section */
        .signatures {
            margin-top: 25px;
            padding-top: 15px;
        }

        .signatures table {
            width: 100%;
        }

        .signature-box {
            text-align: center;
            padding: 0 15px;
            vertical-align: bottom;
        }

        .signature-space {
            height: 35px;
            border-bottom: 2px solid #2c3e50;
            margin: 0 20px;
        }

        .signature-name {
            font-size: 9pt;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .signature-title {
            font-size: 8pt;
            color: #7f8c8d;
            margin-top: 2px;
        }

        .signature-date {
            font-size: 7.5pt;
            color: #95a5a6;
            margin-top: 5px;
        }

        /* Print Date */
        .print-info {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #bdc3c7;
            display: table;
            width: 100%;
        }

        .print-date {
            display: table-cell;
            text-align: left;
            font-size: 7.5pt;
            color: #95a5a6;
        }

        .page-info {
            display: table-cell;
            text-align: right;
            font-size: 7.5pt;
            color: #95a5a6;
        }

        /* Empty State */
        .empty-message {
            text-align: center;
            padding: 50px 20px;
            color: #95a5a6;
            font-size: 12pt;
            border: 2px dashed #bdc3c7;
            border-radius: 10px;
            margin: 30px 0;
        }

        .empty-message p {
            margin-bottom: 5px;
        }

        /* Watermark Effect (Optional) */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80pt;
            color: rgba(0,0,0,0.03);
            font-weight: bold;
            z-index: -1;
            text-transform: uppercase;
            letter-spacing: 15px;
        }

        /* Stats Summary */
        .stats-bar {
            display: table;
            width: 100%;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 5px 10px;
            border-right: 1px solid #ecf0f1;
        }

        .stat-item:last-child {
            border-right: none;
        }

        .stat-number {
            font-size: 14pt;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-label {
            font-size: 7pt;
            color: #95a5a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <!-- Optional Watermark -->
    <div class="watermark">EXAM</div>

    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <div class="institution-name">{{ $branch->name ?? 'Institution Name' }}</div>
            <div class="institution-subtitle">Excellence in Education</div>
        </div>
        <div class="document-title">Master Examination Timetable</div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <table>
            <tr>
                <td><span class="info-label">Academic Year:</span> <span class="info-value">{{ $academicYear->name ?? '2024/2025' }}</span></td>
                <td><span class="info-label">Semester:</span> <span class="info-value">{{ $semester->name ?? 'II' }}</span></td>
                <td><span class="info-label">Exam Period:</span> <span class="info-value">{{ $examDuration ?? '2 Hours per Paper' }}</span></td>
                <td style="text-align: right;"><span class="info-label">Total Exams:</span> <span class="info-value">{{ $schedules->count() }}</span></td>
            </tr>
        </table>
    </div>

    <!-- Timetable -->
    @if($schedules->count() > 0)
    <table class="timetable">
        <thead>
            <tr>
                <th style="width: 25px;">S/N</th>
                <th>Date</th>
                <th>Day</th>
                <th>Time</th>
                <th>Program</th>
                <th>Level</th>
                <th>Subject/Course</th>
                <th>Code</th>
                <th>Room/Venue</th>
                <th>Supervisor/Invigilator</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $index => $schedule)
            @php
                $dayName = $schedule->exam_date ? $schedule->exam_date->format('l') : '';
                $dayClass = 'day-' . strtolower($dayName);
                $programName = $schedule->program->short_name ?? ($schedule->program->name ?? 'N/A');
            @endphp
            <tr>
                <td class="sn-cell">{{ $index + 1 }}</td>
                <td class="date-cell">{{ $schedule->exam_date ? $schedule->exam_date->format('d/m/Y') : 'N/A' }}</td>
                <td class="day-cell {{ $dayClass }}">{{ $dayName }}</td>
                <td class="time-cell">{{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '00:00' }} - {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '00:00' }}</td>
                <td class="program-cell">{{ $programName }}</td>
                <td class="level-cell">{{ $schedule->level_short ?? ($schedule->level ?? '-') }}</td>
                <td class="course-cell">{{ $schedule->course->name ?? $schedule->exam_name ?? 'N/A' }}</td>
                <td class="code-cell">{{ $schedule->course->code ?? 'N/A' }}</td>
                <td class="venue-cell">{{ $schedule->venue ?? 'TBA' }}</td>
                <td class="supervisor-cell">
                    @if($schedule->invigilator)
                        {{ $schedule->invigilator->first_name }} {{ $schedule->invigilator->last_name }}
                    @elseif($schedule->invigilator_name)
                        {{ $schedule->invigilator_name }}
                    @else
                        TBA
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="empty-message">
        <p><strong>No Examination Schedules Found</strong></p>
        <p style="font-size: 9pt; color: #bdc3c7;">Please create exam schedules to generate the timetable.</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <div class="footer-notes">
            <div class="footer-notes-title">⚠ Important Instructions for Candidates:</div>
            <ul>
                <li>All candidates must arrive at the examination venue at least <strong>30 minutes</strong> before the scheduled start time.</li>
                <li>Candidates must carry their <strong>valid Student ID Card</strong> and <strong>Examination Permit</strong> to be admitted into the examination room.</li>
                <li><strong>Mobile phones</strong>, smart watches, and other electronic devices are <strong>strictly prohibited</strong> in the examination room.</li>
                <li>Any form of <strong>cheating or malpractice</strong> will result in immediate disqualification and disciplinary action.</li>
                <li>No candidate will be allowed to leave the examination room during the <strong>first 30 minutes</strong> or <strong>last 15 minutes</strong> of the examination.</li>
                <li>For any queries or clarifications, please contact the <strong>Examinations Office</strong>.</li>
            </ul>
        </div>

        <div class="signatures">
            <table>
                <tr>
                    <td class="signature-box">
                        <div class="signature-space"></div>
                        <div class="signature-name">Academic Registrar</div>
                        <div class="signature-title">Examinations Department</div>
                        <div class="signature-date">Date: _________________</div>
                    </td>
                    <td class="signature-box">
                        <div class="signature-space"></div>
                        <div class="signature-name">Dean of Students</div>
                        <div class="signature-title">Student Affairs</div>
                        <div class="signature-date">Date: _________________</div>
                    </td>
                    <td class="signature-box">
                        <div class="signature-space"></div>
                        <div class="signature-name">Principal</div>
                        <div class="signature-title">Chief Executive</div>
                        <div class="signature-date">Date: _________________</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="print-info">
            <span class="print-date">Generated on: {{ now()->format('l, d F Y') }} at {{ now()->format('H:i') }}</span>
            <span class="page-info">This document is computer generated | Ref: MT-{{ now()->format('YmdHis') }}</span>
        </div>
    </div>
</body>
</html>
