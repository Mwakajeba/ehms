<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ultrasound Result - {{ $result->result_number }}</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                width: 80mm;
                margin: 0;
                padding: 5mm;
                font-size: 12px;
            }
        }
        body {
            font-family: 'Courier New', monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .header h2 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
        }
        .section {
            margin: 5px 0;
            padding: 3px 0;
            border-bottom: 1px dashed #ccc;
        }
        .section-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
            margin-bottom: 3px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 11px;
        }
        .label {
            font-weight: bold;
        }
        .value {
            text-align: right;
        }
        .result-box {
            border: 1px solid #000;
            padding: 5px;
            margin: 5px 0;
        }
        .result-title {
            font-weight: bold;
            text-align: center;
            font-size: 13px;
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #000;
            font-size: 9px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border: 1px solid #000;
            font-weight: bold;
            font-size: 10px;
        }
        .text-content {
            font-size: 10px;
            line-height: 1.3;
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ config('app.name', 'Hospital') }}</h2>
        <p>ULTRASOUND RESULT</p>
        <p>{{ now()->format('d M Y, H:i') }}</p>
    </div>

    <div class="section">
        <div class="info-row">
            <span class="label">Result #:</span>
            <span class="value">{{ $ultrasoundResult->result_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Patient:</span>
            <span class="value">{{ $ultrasoundResult->patient->full_name }}</span>
        </div>
        <div class="info-row">
            <span class="label">MRN:</span>
            <span class="value">{{ $ultrasoundResult->patient->mrn }}</span>
        </div>
        <div class="info-row">
            <span class="label">Visit #:</span>
            <span class="value">{{ $ultrasoundResult->visit->visit_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Date:</span>
            <span class="value">{{ $ultrasoundResult->created_at->format('d M Y') }}</span>
        </div>
    </div>

    <div class="section">
        <div class="result-box">
            <div class="result-title">{{ $ultrasoundResult->examination_type }}</div>
        </div>
    </div>

    @if($ultrasoundResult->findings)
    <div class="section">
        <div class="section-title">Findings:</div>
        <div class="text-content">{{ $ultrasoundResult->findings }}</div>
    </div>
    @endif

    @if($ultrasoundResult->impression)
    <div class="section">
        <div class="section-title">Impression:</div>
        <div class="text-content">{{ $ultrasoundResult->impression }}</div>
    </div>
    @endif

    @if($ultrasoundResult->recommendation)
    <div class="section">
        <div class="section-title">Recommendation:</div>
        <div class="text-content">{{ $ultrasoundResult->recommendation }}</div>
    </div>
    @endif

    <div class="section">
        <div class="info-row">
            <span class="label">Performed By:</span>
            <span class="value">{{ $ultrasoundResult->performedBy->name ?? 'N/A' }}</span>
        </div>
        @if($ultrasoundResult->completed_at)
        <div class="info-row">
            <span class="label">Completed:</span>
            <span class="value">{{ $ultrasoundResult->completed_at->format('d M Y, H:i') }}</span>
        </div>
        @endif
    </div>

    <div class="footer">
        <p>This is a computer-generated report</p>
        <p>Printed: {{ now()->format('d M Y, H:i') }}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
