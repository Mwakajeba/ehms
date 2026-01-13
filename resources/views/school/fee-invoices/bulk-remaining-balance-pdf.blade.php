<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk Remaining Balance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 10px;
        }
        td {
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .total-row td {
            font-size: 12px;
        }
        .summary {
            margin-top: 20px;
        }
        .summary ul {
            list-style-type: none;
            padding: 0;
        }
        .summary li {
            margin-bottom: 5px;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-size: 14px;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company ? $company->name : 'School' }}</h1>
        <h2>Bulk Remaining Balance Report</h2>
    </div>

    <div class="info">
        <strong>Class:</strong> {{ $class->name }} |
        <strong>Academic Year:</strong> {{ $academicYear->year_name }}<br>
        <strong>Quarters:</strong> {{ implode(', ', array_map(function($q) { return 'Q'.$q; }, $quarters)) }} |
        <strong>Report Date:</strong> {{ date('Y-m-d') }}
    </div>

    @if(empty($reportData))
        <div class="no-data">
            No students with outstanding balances found for the selected criteria.
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Student Name</th>
                    <th style="width: 15%;">Admission No.</th>
                    @foreach($quarters as $quarter)
                        <th style="width: 12%;" class="text-center">Q{{ $quarter }} Outstanding</th>
                    @endforeach
                    <th style="width: 15%;" class="text-right">Total Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @foreach($reportData as $student)
                    <tr>
                        <td class="text-center">{{ $counter++ }}</td>
                        <td>{{ $student['name'] }}</td>
                        <td class="text-center">{{ $student['admission_number'] }}</td>
                        @foreach($quarters as $quarter)
                            <td class="text-center">
                                @php
                                    $quarterData = collect($student['quarters'])->firstWhere('quarter', 'Q' . $quarter);
                                @endphp
                                {{ $quarterData ? 'TZS ' . number_format($quarterData['outstanding'], 2) : '-' }}
                            </td>
                        @endforeach
                        <td class="text-right" style="font-weight: bold;">
                            TZS {{ number_format($student['total_outstanding'], 2) }}
                        </td>
                    </tr>
                @endforeach

                <!-- Total row -->
                <tr class="total-row">
                    <td colspan="{{ 3 + count($quarters) }}" class="text-right">TOTAL OUTSTANDING:</td>
                    <td class="text-right">
                        TZS {{ number_format($totalOutstanding, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="summary">
            <strong>Summary:</strong>
            <ul>
                <li>Total Students with Outstanding Balances: {{ count($reportData) }}</li>
                <li>Total Outstanding Amount: TZS {{ number_format($totalOutstanding, 2) }}</li>
                <li>Quarters Included: {{ implode(', ', array_map(function($q) { return 'Q'.$q; }, $quarters)) }}</li>
            </ul>
        </div>
    @endif
</body>
</html>