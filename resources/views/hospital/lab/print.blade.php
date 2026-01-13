<!DOCTYPE html>
<html>
<head>
    <title>Lab Result - {{ $labResult->result_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .patient-info {
            margin-bottom: 20px;
        }
        .result-info {
            border: 1px solid #000;
            padding: 15px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table td {
            padding: 5px;
        }
        .text-right {
            text-align: right;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer;">
            Print
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; cursor: pointer;">
            Close
        </button>
    </div>

    <div class="header">
        <h2>LABORATORY RESULT</h2>
        <p>Result Number: <strong>{{ $labResult->result_number }}</strong></p>
    </div>

    <div class="patient-info">
        <table>
            <tr>
                <td><strong>Patient Name:</strong> {{ $labResult->patient->full_name }}</td>
                <td class="text-right"><strong>MRN:</strong> {{ $labResult->patient->mrn }}</td>
            </tr>
            <tr>
                <td><strong>Age:</strong> {{ $labResult->patient->age ? $labResult->patient->age . ' years' : 'N/A' }}</td>
                <td class="text-right"><strong>Visit #:</strong> {{ $labResult->visit->visit_number }}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong> {{ $labResult->created_at->format('d M Y, H:i') }}</td>
                <td class="text-right"><strong>Performed By:</strong> {{ $labResult->performedBy->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="result-info">
        <h3>Test: {{ $labResult->test_name }}</h3>
        <table>
            <tr>
                <td><strong>Result:</strong></td>
                <td>
                    <strong>{{ $labResult->result_value ?? 'N/A' }}</strong>
                    @if($labResult->unit)
                        <span>{{ $labResult->unit }}</span>
                    @endif
                </td>
            </tr>
            @if($labResult->reference_range)
                <tr>
                    <td><strong>Reference Range:</strong></td>
                    <td>{{ $labResult->reference_range }}</td>
                </tr>
            @endif
            @if($labResult->status)
                <tr>
                    <td><strong>Status:</strong></td>
                    <td>
                        <strong>{{ strtoupper($labResult->status) }}</strong>
                    </td>
                </tr>
            @endif
        </table>
        @if($labResult->notes)
            <div style="margin-top: 15px;">
                <strong>Notes:</strong>
                <p>{{ $labResult->notes }}</p>
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
        <p>Printed on: {{ now()->format('d M Y, H:i') }}</p>
    </div>
</body>
</html>
