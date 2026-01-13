<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $timetable->name }} - Timetable</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            background: #fff;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 16px;
            font-weight: normal;
            color: #555;
            margin-bottom: 10px;
        }
        .header-info {
            display: flex;
            justify-content: center;
            gap: 30px;
            font-size: 12px;
        }
        .header-info span {
            padding: 3px 10px;
            background: #f0f0f0;
            border-radius: 3px;
        }
        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .timetable th,
        .timetable td {
            border: 1px solid #333;
            padding: 5px;
            vertical-align: top;
        }
        .timetable th {
            background: #333;
            color: #fff;
            text-align: center;
            font-weight: bold;
            padding: 8px 5px;
        }
        .timetable td {
            min-width: 100px;
            height: 50px;
        }
        .time-cell {
            background: #f5f5f5;
            text-align: center;
            font-weight: bold;
            width: 70px;
            vertical-align: middle;
        }
        .slot {
            background: #e8f4fd;
            border-left: 3px solid #2196F3;
            padding: 4px 6px;
            margin-bottom: 3px;
            font-size: 10px;
        }
        .slot.lecture { border-color: #2196F3; background: #e3f2fd; }
        .slot.tutorial { border-color: #4CAF50; background: #e8f5e9; }
        .slot.practical { border-color: #FF9800; background: #fff3e0; }
        .slot.lab { border-color: #00BCD4; background: #e0f7fa; }
        .slot.seminar { border-color: #9E9E9E; background: #f5f5f5; }
        .slot.workshop { border-color: #607D8B; background: #eceff1; }
        .slot.exam { border-color: #F44336; background: #ffebee; }
        .slot-code {
            font-weight: bold;
            font-size: 11px;
        }
        .slot-name {
            color: #555;
            font-size: 9px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 90px;
        }
        .slot-info {
            font-size: 9px;
            color: #666;
            margin-top: 2px;
        }
        .slot-type {
            display: inline-block;
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 2px;
            background: #333;
            color: #fff;
            margin-top: 2px;
        }
        .footer {
            border-top: 1px solid #ccc;
            padding-top: 15px;
            margin-top: 20px;
        }
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
        }
        .legend-color {
            width: 15px;
            height: 15px;
            border-left: 3px solid;
        }
        .legend-color.lecture { border-color: #2196F3; background: #e3f2fd; }
        .legend-color.tutorial { border-color: #4CAF50; background: #e8f5e9; }
        .legend-color.practical { border-color: #FF9800; background: #fff3e0; }
        .legend-color.lab { border-color: #00BCD4; background: #e0f7fa; }
        .legend-color.seminar { border-color: #9E9E9E; background: #f5f5f5; }
        .legend-color.workshop { border-color: #607D8B; background: #eceff1; }
        .legend-color.exam { border-color: #F44336; background: #ffebee; }
        .summary {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .summary-section {
            flex: 1;
        }
        .summary-section h4 {
            font-size: 12px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
        }
        .summary-table th {
            background: #f5f5f5;
        }
        .print-date {
            text-align: right;
            font-size: 9px;
            color: #888;
            margin-top: 10px;
        }
        @media print {
            body {
                padding: 0;
                font-size: 10px;
            }
            .slot {
                font-size: 9px;
            }
            .slot-name {
                font-size: 8px;
            }
            @page {
                size: landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $timetable->program->name }}</h1>
        <h2>{{ $timetable->name }}</h2>
        <div class="header-info">
            <span><strong>Year:</strong> {{ $timetable->year_of_study }}</span>
            <span><strong>Semester:</strong> {{ $timetable->semester->name }}</span>
            <span><strong>Academic Year:</strong> {{ $timetable->academicYear->name }}</span>
            @if($timetable->effective_from && $timetable->effective_to)
                <span><strong>Period:</strong> {{ $timetable->effective_from->format('M d') }} - {{ $timetable->effective_to->format('M d, Y') }}</span>
            @endif
        </div>
    </div>

    <table class="timetable">
        <thead>
            <tr>
                <th>Time</th>
                @foreach($days as $day)
                    <th>{{ $day }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($timeSlots as $time)
                <tr>
                    <td class="time-cell">
                        {{ \Carbon\Carbon::parse($time)->format('h:i A') }}
                    </td>
                    @foreach($days as $dayIndex => $day)
                        <td>
                            @php
                                $slotsForCell = $timetable->slots->filter(function($slot) use ($dayIndex, $time) {
                                    return $slot->day_of_week == $dayIndex && 
                                           $slot->start_time <= $time && 
                                           $slot->end_time > $time;
                                });
                            @endphp
                            @foreach($slotsForCell as $slot)
                                @if($slot->start_time == $time)
                                    <div class="slot {{ $slot->slot_type }}">
                                        <div class="slot-code">{{ $slot->course->code }}</div>
                                        <div class="slot-name" title="{{ $slot->course->name }}">{{ $slot->course->name }}</div>
                                        <div class="slot-info">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                            @if($slot->venue)
                                                | {{ $slot->venue->code }}
                                            @endif
                                        </div>
                                        @if($slot->instructor)
                                            <div class="slot-info">{{ $slot->instructor->full_name }}</div>
                                        @endif
                                        <span class="slot-type">{{ ucfirst($slot->slot_type) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="legend">
            <span class="legend-item"><span class="legend-color lecture"></span> Lecture</span>
            <span class="legend-item"><span class="legend-color tutorial"></span> Tutorial</span>
            <span class="legend-item"><span class="legend-color practical"></span> Practical</span>
            <span class="legend-item"><span class="legend-color lab"></span> Lab</span>
            <span class="legend-item"><span class="legend-color seminar"></span> Seminar</span>
            <span class="legend-item"><span class="legend-color workshop"></span> Workshop</span>
            <span class="legend-item"><span class="legend-color exam"></span> Exam</span>
        </div>

        <div class="summary">
            <div class="summary-section">
                <h4>Course Summary</h4>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course</th>
                            <th>Sessions</th>
                            <th>Hrs/Wk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $courseSummary = $timetable->slots->groupBy('course_id');
                        @endphp
                        @foreach($courseSummary as $courseId => $courseSlots)
                            @php
                                $course = $courseSlots->first()->course;
                                $totalHours = $courseSlots->sum(function($slot) {
                                    return \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time)) / 60;
                                });
                            @endphp
                            <tr>
                                <td>{{ $course->code }}</td>
                                <td>{{ Str::limit($course->name, 25) }}</td>
                                <td>{{ $courseSlots->count() }}</td>
                                <td>{{ number_format($totalHours, 1) }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: bold; background: #f0f0f0;">
                            <td colspan="2">Total</td>
                            <td>{{ $timetable->slots->count() }}</td>
                            <td>{{ number_format($timetable->getTotalHoursPerWeek(), 1) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="summary-section">
                <h4>Venue Summary</h4>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Venue</th>
                            <th>Building</th>
                            <th>Sessions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $venueSummary = $timetable->slots->whereNotNull('venue_id')->groupBy('venue_id');
                        @endphp
                        @forelse($venueSummary as $venueId => $venueSlots)
                            @php
                                $venue = $venueSlots->first()->venue;
                            @endphp
                            <tr>
                                <td>{{ $venue->code }}</td>
                                <td>{{ $venue->building ?? '-' }}</td>
                                <td>{{ $venueSlots->count() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" style="text-align: center;">No venues assigned</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="print-date">
            Generated on: {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
