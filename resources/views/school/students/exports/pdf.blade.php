<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Report</title>
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
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }

        .data-table th:nth-child(1) { width: 6%; }  /* # */
        .data-table th:nth-child(2) { width: 12%; } /* Admission No */
        .data-table th:nth-child(3) { width: 18%; } /* Student Name */
        .data-table th:nth-child(4) { width: 10%; } /* Class */
        .data-table th:nth-child(5) { width: 10%; } /* Stream */
        .data-table th:nth-child(6) { width: 8%; }  /* Gender */
        .data-table th:nth-child(7) { width: 10%; } /* Date of Birth */
        .data-table th:nth-child(8) { width: 10%; } /* Admission Date */
        .data-table th:nth-child(9) { width: 8%; }  /* Boarding */
        .data-table th:nth-child(10) { width: 8%; } /* Transport */
        .data-table th:nth-child(11) { width: 12%; } /* Bus Stop */
        .data-table th:nth-child(12) { width: 16%; } /* Guardian Name */
        .data-table th:nth-child(13) { width: 12%; } /* Guardian Phone */
        .data-table th:nth-child(14) { width: 16%; } /* Guardian Email */

        .data-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
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

        .text-success {
            color: #28a745;
            font-weight: 600;
        }

        .text-danger {
            color: #dc3545;
            font-weight: 600;
        }

        .text-info {
            color: #17a2b8;
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 7px;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }

        .badge-male {
            background-color: #007bff;
            color: white;
        }

        .badge-female {
            background-color: #28a745;
            color: white;
        }

        .badge-other {
            background-color: #17a2b8;
            color: white;
        }

        .badge-day {
            background-color: #6c757d;
            color: white;
        }

        .badge-boarding {
            background-color: #ffc107;
            color: black;
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
            @if($logo_path)
                <div class="logo-section">
                    <img src="{{ $logo_path }}" alt="{{ $company->name ?? 'Company Logo' }}" class="company-logo">
                </div>
            @endif
            <div class="title-section">
                <h1>Students Report</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                <div class="subtitle">Generated on {{ $generated_at->format('F d, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="report-info">
        <h3>Report Parameters</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Class:</div>
                <div class="info-value">{{ $filters['class'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Stream:</div>
                <div class="info-value">{{ $filters['stream'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Academic Year:</div>
                <div class="info-value">{{ $filters['academic_year'] }}</div>
            </div>
        </div>
    </div>

    @if($students->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Admission No</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Stream</th>
                    <th>Gender</th>
                    <th>Date of Birth</th>
                    <th>Admission Date</th>
                    <th>Boarding</th>
                    <th>Transport</th>
                    <th>Bus Stop</th>
                    <th>Guardian Name</th>
                    <th>Guardian Phone</th>
                    <th>Guardian Email</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                <tr>
                    <td class="number">{{ $index + 1 }}</td>
                    <td>{{ $student->admission_number ?? 'N/A' }}</td>
                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                    <td>{{ $student->class->name ?? 'N/A' }}</td>
                    <td>{{ $student->stream->name ?? 'N/A' }}</td>
                    <td>
                        @if($student->gender)
                            <span class="badge badge-{{ $student->gender }}">
                                {{ ucfirst($student->gender) }}
                            </span>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $student->admission_date ? \Carbon\Carbon::parse($student->admission_date)->format('M d, Y') : 'N/A' }}</td>
                    <td>
                        @if($student->boarding_type)
                            <span class="badge badge-{{ $student->boarding_type }}">
                                {{ ucfirst($student->boarding_type) }}
                            </span>
                        @else
                            <span class="badge badge-day">Day</span>
                        @endif
                    </td>
                    <td>{{ $student->has_transport === 'yes' ? 'Yes' : 'No' }}</td>
                    <td>{{ $student->busStop->stop_name ?? 'N/A' }}</td>
                    <td>
                        @if($student->guardians->isNotEmpty())
                            {{ $student->guardians->first()->name }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($student->guardians->isNotEmpty())
                            {{ $student->guardians->first()->phone }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($student->guardians->isNotEmpty())
                            {{ $student->guardians->first()->email ?? 'N/A' }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="13" style="text-align: right; font-weight: bold;">TOTAL STUDENTS:</td>
                    <td class="number" style="font-weight: bold;">{{ $students->count() }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No students found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by SmartGrant School Management System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
        <p style="font-size: 10px; margin-top: 5px;">Gender and boarding types are color-coded for easy identification.</p>
    </div>
</body>
</html>