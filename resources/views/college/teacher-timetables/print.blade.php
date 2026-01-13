<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Timetable - {{ $employee->full_name }}</title>
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
            border-bottom: 2px solid #1e3c72;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #1e3c72;
        }
        .header h2 {
            font-size: 18px;
            font-weight: normal;
            color: #333;
            margin-bottom: 10px;
        }
        .header-info {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 12px;
        }
        .header-info span {
            padding: 3px 10px;
            background: #f0f0f0;
            border-radius: 3px;
        }
        
        .stats-row {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
            gap: 15px;
        }
        .stat-box {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1e3c72;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
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
            background: #1e3c72;
            color: #fff;
            text-align: center;
            font-weight: bold;
            padding: 10px 5px;
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
            border-radius: 4px;
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
        .slot-info {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }
        .slot-program {
            font-size: 9px;
            background: rgba(0,0,0,0.05);
            padding: 2px 5px;
            border-radius: 3px;
            display: inline-block;
            margin-top: 2px;
        }
        .slot-type {
            display: inline-block;
            font-size: 8px;
            padding: 2px 5px;
            border-radius: 3px;
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
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        .summary-table th {
            background: #1e3c72;
            color: white;
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
        <h1>Teacher Timetable</h1>
        <h2>{{ $employee->full_name }}</h2>
        <div class="header-info">
            @if($employee->employee_number)
                <span><strong>ID:</strong> {{ $employee->employee_number }}</span>
            @endif
            @if($employee->department)
                <span><strong>Department:</strong> {{ $employee->department->name }}</span>
            @endif
            @if($currentAcademicYear)
                <span><strong>Academic Year:</strong> {{ $currentAcademicYear->name }}</span>
            @endif
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-value">{{ $totalSlots }}</div>
            <div class="stat-label">Total Sessions</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ number_format($totalHours, 1) }}</div>
            <div class="stat-label">Hours/Week</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $courseSummary->count() }}</div>
            <div class="stat-label">Courses</div>
        </div>
        <div class="stat-box">
            <div class="stat-value">{{ $slots->pluck('timetable.program_id')->unique()->count() }}</div>
            <div class="stat-label">Programs</div>
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
                @php
                    $cellHour = (int)substr($time, 0, 2);
                @endphp
                <tr>
                    <td class="time-cell">
                        {{ \Carbon\Carbon::parse($time)->format('h:i A') }}
                    </td>
                    @foreach($days as $day)
                        <td>
                            @if(isset($slotsByDay[$day]))
                                @foreach($slotsByDay[$day] as $slot)
                                    @php
                                        $slotStartHour = (int)date('G', strtotime($slot->start_time));
                                    @endphp
                                    @if($slotStartHour == $cellHour)
                                        <div class="slot {{ $slot->slot_type }}">
                                            <div class="slot-code">{{ $slot->course->code }}</div>
                                            <div class="slot-info">
                                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                @if($slot->venue)
                                                    | {{ $slot->venue->code }}
                                                @endif
                                            </div>
                                            <div class="slot-program">
                                                {{ $slot->timetable->program->code }} Y{{ $slot->timetable->year_of_study }}
                                            </div>
                                            <span class="slot-type">{{ ucfirst($slot->slot_type) }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
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

        <table class="summary-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Course Name</th>
                    <th style="text-align: center;">Sessions</th>
                    <th style="text-align: center;">Hours/Week</th>
                    <th>Programs</th>
                </tr>
            </thead>
            <tbody>
                @foreach($courseSummary as $course)
                    <tr>
                        <td><strong>{{ $course['code'] }}</strong></td>
                        <td>{{ Str::limit($course['name'], 35) }}</td>
                        <td style="text-align: center;">{{ $course['sessions'] }}</td>
                        <td style="text-align: center;">{{ number_format($course['hours'], 1) }}</td>
                        <td>{{ $course['programs'] }}</td>
                    </tr>
                @endforeach
                <tr style="font-weight: bold; background: #f0f0f0;">
                    <td colspan="2">Total</td>
                    <td style="text-align: center;">{{ $totalSlots }}</td>
                    <td style="text-align: center;">{{ number_format($totalHours, 1) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

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
