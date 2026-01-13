<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedule - {{ $examSchedule->exam_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
            background: white;
            padding: 20mm;
        }

        .print-container {
            max-width: 210mm;
            margin: 0 auto;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .institution-name {
            font-size: 20pt;
            font-weight: bold;
            color: #1a365d;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 16pt;
            font-weight: bold;
            color: #2d3748;
            margin-top: 15px;
            padding: 8px 20px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            display: inline-block;
            border-radius: 5px;
        }

        /* Exam Type Badge */
        .exam-type-badge {
            display: inline-block;
            padding: 5px 15px;
            background: #667eea;
            color: white;
            border-radius: 20px;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .exam-type-badge.continuous_assessment { background: #3b82f6; }
        .exam-type-badge.midterm { background: #8b5cf6; }
        .exam-type-badge.final { background: #ef4444; }
        .exam-type-badge.practical { background: #10b981; }
        .exam-type-badge.oral { background: #f59e0b; }
        .exam-type-badge.supplementary { background: #ec4899; }
        .exam-type-badge.retake { background: #f97316; }
        .exam-type-badge.makeup { background: #06b6d4; }
        .exam-type-badge.project { background: #6366f1; }
        .exam-type-badge.internship { background: #14b8a6; }
        .exam-type-badge.online { background: #64748b; }

        /* Info Sections */
        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1a365d;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .info-item {
            padding: 10px 15px;
            background: #f7fafc;
            border-left: 4px solid #667eea;
            border-radius: 0 5px 5px 0;
        }

        .info-item.full-width {
            grid-column: span 2;
        }

        .info-label {
            font-size: 9pt;
            color: #718096;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .info-value {
            font-size: 12pt;
            color: #1a202c;
            font-weight: 600;
        }

        .info-value.large {
            font-size: 14pt;
        }

        /* Schedule Box */
        .schedule-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }

        .schedule-date {
            font-size: 28pt;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .schedule-time {
            font-size: 16pt;
            opacity: 0.9;
        }

        .schedule-duration {
            font-size: 11pt;
            opacity: 0.8;
            margin-top: 5px;
        }

        /* Venue Box */
        .venue-box {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #edf2f7;
            border-radius: 8px;
            margin: 15px 0;
        }

        .venue-icon {
            width: 50px;
            height: 50px;
            background: #667eea;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20pt;
        }

        .venue-details {
            flex: 1;
        }

        .venue-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1a202c;
        }

        .venue-building {
            font-size: 11pt;
            color: #718096;
        }

        /* Marks Table */
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        .marks-table th,
        .marks-table td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }

        .marks-table th {
            background: #667eea;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 10pt;
        }

        .marks-table td {
            font-size: 11pt;
        }

        .marks-table tr:nth-child(even) {
            background: #f7fafc;
        }

        /* Instructions Box */
        .instructions-box {
            background: #fffbeb;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .instructions-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            font-size: 11pt;
        }

        .instructions-content {
            color: #78350f;
            font-size: 10pt;
            white-space: pre-line;
        }

        /* Materials List */
        .materials-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }

        .material-item {
            padding: 5px 12px;
            background: #e6fffa;
            border: 1px solid #38b2ac;
            border-radius: 15px;
            font-size: 10pt;
            color: #234e52;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft { background: #e2e8f0; color: #4a5568; }
        .status-scheduled { background: #bee3f8; color: #2b6cb0; }
        .status-ongoing { background: #fef3c7; color: #92400e; }
        .status-completed { background: #c6f6d5; color: #276749; }
        .status-postponed { background: #e9d8fd; color: #553c9a; }
        .status-cancelled { background: #fed7d7; color: #c53030; }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            color: #718096;
        }

        .signature-section {
            margin-top: 40px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 50px;
        }

        .signature-box {
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 10pt;
        }

        /* Print specific styles */
        @media print {
            body {
                padding: 10mm;
            }

            .no-print {
                display: none !important;
            }

            .schedule-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Print button */
        .print-actions {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }

        .btn-print {
            padding: 10px 25px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print:hover {
            background: #5a67d8;
        }

        .btn-back {
            padding: 10px 25px;
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-back:hover {
            background: #cbd5e0;
        }
    </style>
</head>
<body>
    <!-- Print Actions -->
    <div class="print-actions no-print">
        <a href="{{ route('college.exam-schedules.show', $examSchedule) }}" class="btn-back">
            ← Back
        </a>
        <button onclick="window.print()" class="btn-print">
            🖨️ Print
        </button>
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="institution-name">{{ config('app.name', 'Smart Accounting') }}</div>
            <div class="document-title">EXAMINATION SCHEDULE</div>
            <div class="exam-type-badge {{ $examSchedule->exam_type }}">
                {{ $examSchedule->exam_type_name }}
            </div>
        </div>

        <!-- Exam Name -->
        <div class="section">
            <h2 style="font-size: 18pt; color: #1a365d; text-align: center; margin-bottom: 20px;">
                {{ $examSchedule->exam_name }}
            </h2>
        </div>

        <!-- Schedule Box -->
        <div class="schedule-box">
            <div class="schedule-date">
                {{ $examSchedule->exam_date->format('l, F d, Y') }}
            </div>
            <div class="schedule-time">
                {{ \Carbon\Carbon::parse($examSchedule->start_time)->format('h:i A') }} - 
                {{ \Carbon\Carbon::parse($examSchedule->end_time)->format('h:i A') }}
            </div>
            <div class="schedule-duration">
                Duration: {{ $examSchedule->duration_minutes }} minutes
            </div>
        </div>

        <!-- Academic Information -->
        <div class="section">
            <div class="section-title">Academic Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Academic Year</div>
                    <div class="info-value">{{ $examSchedule->academicYear->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Semester</div>
                    <div class="info-value">{{ $examSchedule->semester->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Program</div>
                    <div class="info-value">{{ $examSchedule->program->name ?? 'N/A' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Course</div>
                    <div class="info-value">
                        {{ $examSchedule->course->code ?? 'N/A' }} - {{ $examSchedule->course->name ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Venue Information -->
        <div class="section">
            <div class="section-title">Venue Information</div>
            <div class="venue-box">
                <div class="venue-icon">📍</div>
                <div class="venue-details">
                    <div class="venue-name">{{ $examSchedule->venue ?? 'To Be Announced' }}</div>
                    @if($examSchedule->building)
                        <div class="venue-building">Building: {{ $examSchedule->building }}</div>
                    @endif
                    @if($examSchedule->capacity)
                        <div class="venue-building">Capacity: {{ $examSchedule->capacity }} students</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Marks Configuration -->
        <div class="section">
            <div class="section-title">Marks Configuration</div>
            <table class="marks-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Marks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Total Marks</td>
                        <td><strong>{{ number_format($examSchedule->total_marks, 0) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Pass Marks</td>
                        <td><strong>{{ number_format($examSchedule->pass_marks, 0) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Pass Percentage</td>
                        <td>{{ number_format(($examSchedule->pass_marks / $examSchedule->total_marks) * 100, 1) }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Invigilator -->
        @if($examSchedule->invigilator || $examSchedule->invigilator_name)
        <div class="section">
            <div class="section-title">Invigilator</div>
            <div class="info-grid">
                <div class="info-item full-width">
                    <div class="info-label">Assigned Invigilator</div>
                    <div class="info-value">
                        {{ $examSchedule->invigilator->name ?? $examSchedule->invigilator_name ?? 'Not Assigned' }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Materials Allowed -->
        @if($examSchedule->materials_allowed && count($examSchedule->materials_allowed) > 0)
        <div class="section">
            <div class="section-title">Materials Allowed</div>
            <div class="materials-list">
                @foreach($examSchedule->materials_allowed as $material)
                    <span class="material-item">✓ {{ $material }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Instructions -->
        @if($examSchedule->instructions)
        <div class="instructions-box">
            <div class="instructions-title">⚠️ IMPORTANT INSTRUCTIONS</div>
            <div class="instructions-content">{{ $examSchedule->instructions }}</div>
        </div>
        @endif

        <!-- Status -->
        <div class="section" style="text-align: center; margin-top: 20px;">
            <span class="status-badge status-{{ $examSchedule->status }}">
                {{ $examSchedule->status_name }}
            </span>
            @if($examSchedule->is_published)
                <span class="status-badge" style="background: #c6f6d5; color: #276749; margin-left: 10px;">
                    ✓ Published
                </span>
            @endif
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">Examiner's Signature</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Head of Department</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div>
                Printed on: {{ now()->format('F d, Y \a\t h:i A') }}
            </div>
            <div>
                Schedule ID: #{{ $examSchedule->id }}
            </div>
        </div>
    </div>

    <script>
        // Auto-print on page load (optional - uncomment if desired)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
