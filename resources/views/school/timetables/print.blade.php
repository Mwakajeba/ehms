<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Timetable</title>
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
            font-size: 14px;
            font-weight: bold;
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
            padding: 4px 15px 4px 0;
            width: 120px;
            color: #555;
            font-size: 10px;
        }
        
        .info-value {
            display: table-cell;
            padding: 4px 0;
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
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
            vertical-align: middle;
            border: 1px solid #0d6efd;
        }
        
        .data-table th:nth-child(1) { width: 10%; }
        .data-table th:nth-child(2) { width: 15%; }
        .data-table th:nth-child(3) { width: 15%; }
        .data-table th:nth-child(4) { width: 15%; }
        .data-table th:nth-child(5) { width: 15%; }
        .data-table th:nth-child(6) { width: 15%; }
        .data-table th:nth-child(7) { width: 15%; }
        .data-table th:nth-child(8) { width: 15%; }
        
        .data-table td {
            padding: 8px 6px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            word-wrap: break-word;
            vertical-align: top;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .period-cell {
            text-align: center;
            font-weight: bold;
            background: #e9ecef;
            font-size: 10px;
        }
        
        .period-cell small {
            font-size: 8px;
        }
        
        .entry-cell {
            padding: 4px;
        }
        
        .subject-name {
            font-weight: 600;
            color: #333;
            font-size: 10px;
        }
        
        .subject-code {
            color: #666;
            font-size: 9px;
        }
        
        .teacher-name {
            color: #17a2b8;
            font-size: 9px;
            margin-top: 2px;
        }
        
        .room-name {
            color: #28a745;
            font-size: 9px;
            margin-top: 2px;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: 600;
            border-radius: 3px;
            margin-top: 2px;
        }
        
        .badge-warning {
            background: #ffc107;
            color: #000;
        }
        
        .empty-cell {
            text-align: center;
            color: #999;
            font-style: italic;
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
                <h1>School Timetable</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                <div class="subtitle">Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="report-info">
        <h3>Timetable Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Timetable Name:</div>
                <div class="info-value">{{ $timetable->name }}</div>
            </div>
            @if($timetable->academicYear)
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value">{{ $timetable->academicYear->year_name }}</div>
            </div>
            @endif
            @if($timetable->classe)
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ $timetable->classe->name }}</div>
            </div>
            @endif
            @if($timetable->stream)
            <div class="info-row">
                <div class="info-label">Stream:</div>
                <div class="info-value">{{ $timetable->stream->name }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Type:</div>
                <div class="info-value">{{ ucfirst($timetable->timetable_type) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($timetable->status) }}</div>
            </div>
            @if($timetable->settings)
            <div class="info-row">
                <div class="info-label">School Hours:</div>
                <div class="info-value">
                    @if($timetable->settings->school_start_time)
                        {{ \Carbon\Carbon::parse($timetable->settings->school_start_time)->format('g:i A') }}
                    @else
                        8:00 AM
                    @endif
                    - 
                    @if($timetable->settings->school_end_time)
                        {{ \Carbon\Carbon::parse($timetable->settings->school_end_time)->format('g:i A') }}
                    @else
                        3:00 PM
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Period Duration:</div>
                <div class="info-value">{{ $timetable->settings->period_duration_minutes ?? 40 }} minutes</div>
            </div>
            @endif
        </div>
    </div>

    @if($timetable->entries->count() > 0 || $maxPeriods > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Period / Time</th>
                    @foreach($days as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for($period = 1; $period <= $maxPeriods; $period++)
                    @php
                        $periodData = $periodsData[$period] ?? null;
                    @endphp
                    <tr>
                        <td class="period-cell">
                            <div><strong>Period {{ $period }}</strong></div>
                            @if($periodData)
                                <small style="font-size: 7px; color: #666;">{{ $periodData['time_range'] }}</small>
                            @endif
                        </td>
                        @foreach($days as $day)
                            @php
                                $key = $day . '-' . $period;
                                $entryCollection = $entriesByDayPeriod->get($key);
                                $entry = $entryCollection ? $entryCollection->first() : null;
                            @endphp
                            <td class="entry-cell">
                                @if($entry)
                                    <div class="subject-name">{{ $entry->subject->name ?? 'N/A' }}</div>
                                    @if($entry->subject && $entry->subject->code)
                                        <div class="subject-code">({{ $entry->subject->code }})</div>
                                    @endif
                                    @if($timetable->timetable_type == 'teacher' && $entry->classe)
                                        <div class="teacher-name" style="color: #0d6efd;">
                                            <strong>C:</strong> {{ $entry->classe->name }}
                                            @if($entry->stream)
                                                - {{ $entry->stream->name }}
                                            @endif
                                        </div>
                                    @endif
                                    @if($entry->teacher)
                                        <div class="teacher-name">
                                            <strong>T:</strong> {{ $entry->teacher->first_name ?? '' }} {{ $entry->teacher->last_name ?? '' }}
                                        </div>
                                    @endif
                                    @if($entry->room)
                                        <div class="room-name">
                                            <strong>R:</strong> {{ $entry->room->room_name }}
                                        </div>
                                    @endif
                                    @if($entry->is_double_period)
                                        <span class="badge badge-warning">Double</span>
                                    @endif
                                @else
                                    <div class="empty-cell">-</div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endfor
            </tbody>
        </table>
    @else
        <div class="no-data">
            <h3>No Timetable Data Available</h3>
            <p>No timetable entries found for this timetable.</p>
        </div>
    @endif

    @if($timetable->description)
    <div class="report-info" style="margin-top: 20px;">
        <h3>Description</h3>
        <p style="margin: 0; color: #333;">{{ $timetable->description }}</p>
    </div>
    @endif

    <div class="footer">
        <p>This timetable was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 10px; margin-top: 5px;">Legend: <strong>T</strong> = Teacher, <strong>R</strong> = Room</p>
    </div>
</body>
</html>

