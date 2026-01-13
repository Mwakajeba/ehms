<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ ucfirst($type) }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .filters {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .filters h6 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .filters p {
            margin: 2px 0;
            font-size: 11px;
        }

        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-card {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 15px;
            margin: 0 5px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .summary-card h4 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .summary-card p {
            margin: 5px 0 0 0;
            font-size: 11px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .student-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .student-info h5 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .student-stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-item {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 10px;
        }

        .stat-item h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .stat-item p {
            margin: 5px 0 0 0;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report - {{ ucfirst($type) }}</h1>
        <p>Generated on: {{ $generated_at->format('M d, Y H:i:s') }}</p>
    </div>

    @if($filters)
    <div class="filters">
        <h6>Applied Filters:</h6>
        @if(isset($filters['class_id']) && $filters['class_id'])
            <p><strong>Class:</strong> {{ \App\Models\School\Classe::find($filters['class_id'])->name ?? 'N/A' }}</p>
        @endif
        @if(isset($filters['stream_id']) && $filters['stream_id'])
            <p><strong>Stream:</strong> {{ \App\Models\School\Stream::find($filters['stream_id'])->name ?? 'N/A' }}</p>
        @endif
        @if(isset($filters['academic_year_id']) && $filters['academic_year_id'])
            <p><strong>Academic Year:</strong> {{ \App\Models\School\AcademicYear::find($filters['academic_year_id'])->year_name ?? 'N/A' }}</p>
        @endif
        @if(isset($filters['start_date']) && isset($filters['end_date']))
            <p><strong>Date Range:</strong> {{ \Carbon\Carbon::parse($filters['start_date'])->format('M d, Y') }} to {{ \Carbon\Carbon::parse($filters['end_date'])->format('M d, Y') }}</p>
        @endif
    </div>
    @endif

    @if($type === 'summary')
        <div class="summary-cards">
            <div class="summary-card">
                <h4>{{ $data['total_sessions'] ?? 0 }}</h4>
                <p>Total Sessions</p>
            </div>
            <div class="summary-card">
                <h4>{{ $data['total_present'] ?? 0 }}</h4>
                <p>Total Present</p>
            </div>
            <div class="summary-card">
                <h4>{{ $data['total_absent'] ?? 0 }}</h4>
                <p>Total Absent</p>
            </div>
            <div class="summary-card">
                <h4>{{ $data['total_late'] ?? 0 }}</h4>
                <p>Total Late</p>
            </div>
            <div class="summary-card">
                <h4>{{ $data['overall_attendance_rate'] ?? 0 }}%</h4>
                <p>Avg Attendance Rate</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Academic Year</th>
                    <th class="text-center">Total Students</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Absent</th>
                    <th class="text-center">Late</th>
                    <th class="text-center">Sick</th>
                    <th class="text-center">Attendance Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td>{{ $row['session_date_formatted'] ?? '' }}</td>
                    <td>{{ $row['class_name'] ?? '' }}</td>
                    <td>{{ $row['stream_name'] ?? '' }}</td>
                    <td>{{ $row['academic_year_name'] ?? 'N/A' }}</td>
                    <td class="text-center">{{ $row['total_students'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['present'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['absent'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['late'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['sick'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['attendance_rate'] ?? '0%' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type === 'student')
        <div class="student-info">
            <h5>Student Information</h5>
            <p><strong>Name:</strong> {{ $data['student']['first_name'] }} {{ $data['student']['last_name'] }}</p>
            <p><strong>Admission Number:</strong> {{ $data['student']['admission_number'] }}</p>
            <p><strong>Class:</strong> {{ $data['student']['class']['name'] ?? 'N/A' }}</p>
            <p><strong>Stream:</strong> {{ $data['student']['stream']['name'] ?? 'N/A' }}</p>
        </div>

        <div class="student-stats">
            <div class="stat-item">
                <h4>{{ $data['stats']['total_days'] }}</h4>
                <p>Total Days</p>
            </div>
            <div class="stat-item">
                <h4>{{ $data['stats']['present'] }}</h4>
                <p>Present</p>
            </div>
            <div class="stat-item">
                <h4>{{ $data['stats']['absent'] }}</h4>
                <p>Absent</p>
            </div>
            <div class="stat-item">
                <h4>{{ $data['stats']['late'] }}</h4>
                <p>Late</p>
            </div>
            <div class="stat-item">
                <h4>{{ $data['stats']['attendance_rate'] }}%</h4>
                <p>Attendance Rate</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['attendances'] as $attendance)
                <tr>
                    <td>{{ $attendance['date'] }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower($attendance['status']) }}">
                            {{ $attendance['formatted_status'] }}
                        </span>
                    </td>
                    <td>{{ $attendance['time_in'] ?: '-' }}</td>
                    <td>{{ $attendance['time_out'] ?: '-' }}</td>
                    <td>{{ $attendance['notes'] ?: '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @elseif($type === 'class')
        <table>
            <thead>
                <tr>
                    <th>Class</th>
                    <th class="text-center">Total Sessions</th>
                    <th class="text-center">Total Students</th>
                    <th class="text-center">Present</th>
                    <th class="text-center">Attendance Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                <tr>
                    <td>{{ $row['class_name'] ?? '' }}</td>
                    <td class="text-center">{{ $row['total_sessions'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['total_students'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['present'] ?? 0 }}</td>
                    <td class="text-center">{{ $row['attendance_rate'] ?? 0 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Report generated by Smart Accounting System on {{ $generated_at->format('M d, Y H:i:s') }}</p>
    </div>
</body>
</html>