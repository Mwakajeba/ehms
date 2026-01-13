<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Teacher Assignment Frequency Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #333; }
        .filters { margin-bottom: 15px; padding: 10px; background-color: #f5f5f5; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #333; color: white; font-weight: bold; text-align: center; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>TEACHER ASSIGNMENT FREQUENCY REPORT</h1>
        @if($company)
            <p><strong>Company:</strong> {{ $company->name }}</p>
        @endif
        <p><strong>Generated On:</strong> {{ $generatedAt->format('F d, Y h:i A') }}</p>
    </div>

    @if(!empty($filters))
    <div class="filters">
        <p><strong>Report Filters:</strong></p>
        @foreach($filters as $key => $value)
            <p>{{ $key }}: {{ $value }}</p>
        @endforeach
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Teacher Name</th>
                <th>Employee ID</th>
                <th class="text-center">Total Assignments</th>
                <th class="text-center">Homework</th>
                <th class="text-center">Classwork</th>
                <th class="text-center">Project</th>
                <th class="text-center">Revision Task</th>
                <th>Subjects</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row['teacher_name'] }}</td>
                    <td>{{ $row['employee_id'] }}</td>
                    <td class="text-center">{{ $row['total_assignments'] }}</td>
                    <td class="text-center">{{ $row['homework_count'] }}</td>
                    <td class="text-center">{{ $row['classwork_count'] }}</td>
                    <td class="text-center">{{ $row['project_count'] }}</td>
                    <td class="text-center">{{ $row['revision_task_count'] }}</td>
                    <td>{{ $row['subjects'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

