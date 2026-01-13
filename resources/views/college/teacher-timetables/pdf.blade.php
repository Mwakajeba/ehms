<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Teacher Timetable - {{ $employee->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #1e3c72;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 3px;
            text-transform: uppercase;
            color: #1e3c72;
        }
        .header h2 {
            font-size: 14px;
            font-weight: normal;
            color: #333;
            margin-bottom: 8px;
        }
        .header-info {
            font-size: 10px;
        }
        .header-info span {
            display: inline-block;
            padding: 2px 8px;
            background: #f0f4f8;
            border-radius: 3px;
            margin: 0 5px;
        }
        
        .stats-row {
            margin-bottom: 15px;
        }
        .stats-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stats-table td {
            width: 25%;
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #1e3c72;
        }
        .stat-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        
        .timetable {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .timetable th,
        .timetable td {
            border: 1px solid #333;
            padding: 3px;
            vertical-align: middle;
        }
        .timetable th {
            background: #1e3c72;
            color: #fff;
            text-align: center;
            font-weight: bold;
            padding: 5px 2px;
            font-size: 7px;
        }
        .timetable td {
            min-width: 50px;
            height: 40px;
        }
        .time-cell {
            background: #1e3c72;
            color: #fff;
            text-align: center;
            font-weight: bold;
            width: 70px;
            min-width: 70px;
            vertical-align: middle;
            font-size: 8px;
        }
        .slot {
            padding: 2px 3px;
            margin-bottom: 2px;
            font-size: 8px;
            border-left: 2px solid #2196F3;
            background: #e3f2fd;
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
            font-size: 9px;
        }
        .slot-info {
            font-size: 7px;
            color: #555;
        }
        .slot-program {
            font-size: 7px;
            background: rgba(0,0,0,0.05);
            padding: 1px 3px;
            display: inline-block;
        }
        .slot-type {
            display: inline-block;
            font-size: 6px;
            padding: 1px 3px;
            border-radius: 2px;
            background: #333;
            color: #fff;
        }
        
        .footer {
            border-top: 1px solid #ccc;
            padding-top: 10px;
            margin-top: 10px;
        }
        .legend {
            margin-bottom: 10px;
        }
        .legend-item {
            display: inline-block;
            margin-right: 10px;
            font-size: 8px;
        }
        .legend-color {
            display: inline-block;
            width: 12px;
            height: 12px;
            vertical-align: middle;
            margin-right: 3px;
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
            font-size: 8px;
            margin-top: 10px;
        }
        .summary-table th,
        .summary-table td {
            border: 1px solid #ddd;
            padding: 4px 6px;
            text-align: left;
        }
        .summary-table th {
            background: #1e3c72;
            color: white;
            font-size: 8px;
        }
        .print-date {
            text-align: right;
            font-size: 8px;
            color: #888;
            margin-top: 10px;
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
        <table class="stats-table">
            <tr>
                <td>
                    <div class="stat-value">{{ $totalSlots }}</div>
                    <div class="stat-label">Total Sessions</div>
                </td>
                <td>
                    <div class="stat-value">{{ number_format($totalHours, 1) }}</div>
                    <div class="stat-label">Hours/Week</div>
                </td>
                <td>
                    <div class="stat-value">{{ $courseSummary->count() }}</div>
                    <div class="stat-label">Courses</div>
                </td>
                <td>
                    <div class="stat-value">{{ $slots->pluck('timetable.program_id')->unique()->count() }}</div>
                    <div class="stat-label">Programs</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="timetable">
        @php
            // Define time periods for columns
            $timePeriods = [];
            for ($hour = 7; $hour <= 20; $hour++) {
                $timePeriods[] = [
                    'start' => sprintf('%02d:00', $hour),
                    'end' => sprintf('%02d:00', $hour + 1),
                    'label' => sprintf('%d-%d', $hour, $hour + 1)
                ];
            }
            
            // Day labels in Swahili
            $dayLabels = [
                'Monday' => 'Jumatatu',
                'Tuesday' => 'Jumanne',
                'Wednesday' => 'Jumatano',
                'Thursday' => 'Alhamisi',
                'Friday' => 'Ijumaa',
                'Saturday' => 'Jumamosi',
            ];
            
            // Function to calculate colspan for PDF
            function getPdfSlotColspan($slot, $timePeriods) {
                $sStartH = (int)substr($slot->start_time, 0, 2);
                $sStartM = (int)substr($slot->start_time, 3, 2);
                $sEndH = (int)substr($slot->end_time, 0, 2);
                $sEndM = (int)substr($slot->end_time, 3, 2);
                $sStart = $sStartH * 60 + $sStartM;
                $sEnd = $sEndH * 60 + $sEndM;
                
                $colspan = 0;
                $startCol = -1;
                
                foreach($timePeriods as $idx => $period) {
                    $pStart = (int)substr($period['start'], 0, 2) * 60;
                    $pEnd = (int)substr($period['end'], 0, 2) * 60;
                    
                    if ($sStart < $pEnd && $sEnd > $pStart) {
                        if ($startCol === -1) $startCol = $idx;
                        $colspan++;
                    }
                }
                
                return ['startCol' => $startCol, 'colspan' => $colspan];
            }
        @endphp
        <thead>
            <tr>
                <th style="width: 70px;">Siku</th>
                @foreach($timePeriods as $period)
                    <th>{{ $period['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($dayLabels as $dayEng => $daySwahili)
                @php
                    $daySlots = $slotsByDay[$dayEng] ?? collect([]);
                    $skipCols = [];
                    
                    // Calculate colspan info for each slot
                    $slotInfo = [];
                    foreach($daySlots as $slot) {
                        $info = getPdfSlotColspan($slot, $timePeriods);
                        if ($info['startCol'] >= 0) {
                            $slotInfo[$info['startCol']] = [
                                'slot' => $slot,
                                'colspan' => $info['colspan']
                            ];
                            for ($i = $info['startCol'] + 1; $i < $info['startCol'] + $info['colspan']; $i++) {
                                $skipCols[$i] = true;
                            }
                        }
                    }
                @endphp
                <tr>
                    <td class="time-cell">{{ $daySwahili }}</td>
                    @foreach($timePeriods as $colIdx => $period)
                        @if(!isset($skipCols[$colIdx]))
                            @if(isset($slotInfo[$colIdx]))
                                @php
                                    $slot = $slotInfo[$colIdx]['slot'];
                                    $colspan = $slotInfo[$colIdx]['colspan'];
                                @endphp
                                <td colspan="{{ $colspan }}" style="padding: 2px;">
                                    <div class="slot {{ $slot->slot_type }}">
                                        <div class="slot-code">{{ $slot->course->code ?? 'N/A' }}</div>
                                        <div class="slot-info">
                                            {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                            @if($slot->venue)
                                                | {{ $slot->venue->code }}
                                            @endif
                                        </div>
                                        <div class="slot-program">
                                            {{ $slot->timetable->program->code ?? '' }} Y{{ $slot->timetable->year_of_study ?? '' }}
                                        </div>
                                        <span class="slot-type">{{ ucfirst($slot->slot_type) }}</span>
                                    </div>
                                </td>
                            @else
                                <td></td>
                            @endif
                        @endif
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
                        <td>{{ Str::limit($course['name'], 30) }}</td>
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
</body>
</html>
