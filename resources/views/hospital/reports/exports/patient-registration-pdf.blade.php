<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            background: #fff;
            font-size: 10px;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 15px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 12px;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }

        .company-logo {
            max-height: 55px;
            max-width: 95px;
            object-fit: contain;
        }

        .title-section {
            text-align: center;
            flex-grow: 1;
        }

        .header h1 {
            color: #0d6efd;
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .company-name {
            color: #333;
            margin: 4px 0;
            font-size: 13px;
            font-weight: 600;
        }

        .subtitle {
            color: #666;
            margin: 0;
            font-size: 11px;
        }

        .report-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 12px;
            border-left: 4px solid #0d6efd;
        }

        .report-info h3 {
            margin: 0 0 8px 0;
            color: #0d6efd;
            font-size: 13px;
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
            padding: 3px 10px 3px 0;
            width: 110px;
            color: #555;
        }

        .info-value {
            display: table-cell;
            padding: 3px 0;
            color: #333;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        .data-table thead {
            background: #0d6efd;
            color: #fff;
        }

        .data-table th,
        .data-table td {
            padding: 6px 6px;
            border: 1px solid #dee2e6;
            font-size: 9px;
            word-wrap: break-word;
        }

        .data-table th {
            font-weight: bold;
            text-align: left;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .muted { color: #666; }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    @php
        $company = \App\Models\Company::find(auth()->user()->company_id);
        $generatedAt = now();
    @endphp

    <div class="header">
        <div class="header-content">
            @if($company && $company->logo)
                <div>
                    <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
                </div>
            @endif
            <div class="title-section">
                <h1>Patient Registration Report</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                <p class="subtitle">Generated on {{ $generatedAt->format('F d, Y \\a\\t g:i A') }}</p>
            </div>
        </div>
    </div>

    <div class="report-info">
        <h3>Report Parameters</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Start Date:</div>
                <div class="info-value">{{ $startDate->format('Y-m-d') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">End Date:</div>
                <div class="info-value">{{ $endDate->format('Y-m-d') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total:</div>
                <div class="info-value">{{ number_format($summary['total']) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Active:</div>
                <div class="info-value">{{ number_format($summary['active']) }}</div>
            </div>
        </div>
    </div>

    @if($patients->isEmpty())
        <div class="empty-state">No patients registered in this date range.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%;">#</th>
                    <th style="width: 10%;">MRN</th>
                    <th style="width: 18%;">Full Name</th>
                    <th style="width: 7%;">Gender</th>
                    <th style="width: 6%;">Age</th>
                    <th style="width: 12%;">Phone</th>
                    <th style="width: 13%;">Insurance</th>
                    <th style="width: 10%;">Admitted</th>
                    <th style="width: 12%;">Registered At</th>
                    <th style="width: 8%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $i => $patient)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td>{{ $patient->mrn }}</td>
                        <td>{{ $patient->full_name }}</td>
                        <td class="text-center">{{ $patient->gender ? ucfirst($patient->gender) : '—' }}</td>
                        <td class="text-right">{{ $patient->age ?? '—' }}</td>
                        <td>{{ $patient->phone ?? '—' }}</td>
                        <td>{{ $patient->insurance_type_name }}</td>
                        <td>{{ $patient->admitted_date ? $patient->admitted_date->format('Y-m-d') : '—' }}</td>
                        <td>{{ $patient->created_at ? $patient->created_at->format('Y-m-d H:i') : '—' }}</td>
                        <td class="text-center">{{ $patient->is_active ? 'Active' : 'Inactive' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>

