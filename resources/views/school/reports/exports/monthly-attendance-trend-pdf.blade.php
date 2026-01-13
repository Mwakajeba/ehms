<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Attendance Trend Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .filters p {
            margin: 3px 0;
            font-size: 9px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: white;
            font-weight: bold;
            text-align: center;
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
        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .summary h3 {
            margin-top: 0;
            font-size: 12px;
        }
        .summary p {
            margin: 3px 0;
            font-size: 9px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MONTHLY ATTENDANCE TREND ANALYSIS REPORT</h1>
        @if($company)
            <p><strong>Company:</strong> {{ $company->name }}</p>
        @endif
        <p><strong>Generated On:</strong> {{ date('F d, Y h:i A') }}</p>
    </div>

    <div class="filters">
        <p><strong>Report Filters:</strong></p>
        <p>Academic Year: {{ $academicYear ? $academicYear->year_name : 'All' }}</p>
        <p>Class: {{ $class ? $class->name : 'All' }}</p>
        <p>Stream: {{ $stream ? $stream->name : 'All' }}</p>
        <p>Date Range: {{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-center">Sessions</th>
                <th class="text-center">Students</th>
                <th class="text-center">Present</th>
                <th class="text-center">Absent</th>
                <th class="text-center">Late</th>
                <th class="text-center">Sick</th>
                <th class="text-center">Total Records</th>
                <th class="text-center">Attendance Rate (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trendData['monthly_data'] as $month)
                <tr>
                    <td><strong>{{ $month['month_name'] }}</strong></td>
                    <td class="text-center">{{ number_format($month['total_sessions']) }}</td>
                    <td class="text-center">{{ number_format($month['unique_students']) }}</td>
                    <td class="text-center">{{ number_format($month['total_present']) }}</td>
                    <td class="text-center">{{ number_format($month['total_absent']) }}</td>
                    <td class="text-center">{{ number_format($month['total_late']) }}</td>
                    <td class="text-center">{{ number_format($month['total_sick']) }}</td>
                    <td class="text-center">{{ number_format($month['total_records']) }}</td>
                    <td class="text-center"><strong>{{ number_format($month['attendance_rate'], 2) }}%</strong></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #d3d3d3; font-weight: bold;">
                <td><strong>Grand Total</strong></td>
                <td class="text-center">{{ number_format($trendData['grand_totals']['total_sessions']) }}</td>
                <td class="text-center">{{ number_format($trendData['grand_totals']['total_students']) }}</td>
                <td class="text-center">{{ number_format($trendData['grand_totals']['total_present']) }}</td>
                <td class="text-center">{{ number_format($trendData['grand_totals']['total_absent']) }}</td>
                <td class="text-center">{{ number_format($trendData['grand_totals']['total_late']) }}</td>
                <td class="text-center">{{ number_format($trendData['grand_totals']['total_sick']) }}</td>
                <td class="text-center">{{ number_format($trendData['grand_totals']['total_records']) }}</td>
                <td class="text-center"><strong>{{ number_format($trendData['grand_totals']['overall_attendance_rate'], 2) }}%</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <h3>Summary Statistics</h3>
        <p><strong>Total Sessions:</strong> {{ number_format($trendData['grand_totals']['total_sessions']) }}</p>
        <p><strong>Total Students:</strong> {{ number_format($trendData['grand_totals']['total_students']) }}</p>
        <p><strong>Overall Attendance Rate:</strong> {{ number_format($trendData['grand_totals']['overall_attendance_rate'], 2) }}%</p>
        <p><strong>Total Present:</strong> {{ number_format($trendData['grand_totals']['total_present']) }}</p>
        <p><strong>Total Absent:</strong> {{ number_format($trendData['grand_totals']['total_absent']) }}</p>
        <p><strong>Total Late:</strong> {{ number_format($trendData['grand_totals']['total_late']) }}</p>
        <p><strong>Total Sick:</strong> {{ number_format($trendData['grand_totals']['total_sick']) }}</p>
    </div>
</body>
</html>

