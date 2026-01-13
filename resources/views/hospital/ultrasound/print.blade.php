<!DOCTYPE html>
<html>
<head>
    <title>Ultrasound Result - {{ $ultrasoundResult->result_number }}</title>
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
        .images-section {
            margin-top: 20px;
        }
        .image-container {
            margin: 10px 0;
            text-align: center;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
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
        <h2>ULTRASOUND EXAMINATION RESULT</h2>
        <p>Result Number: <strong>{{ $ultrasoundResult->result_number }}</strong></p>
    </div>

    <div class="patient-info">
        <table>
            <tr>
                <td><strong>Patient Name:</strong> {{ $ultrasoundResult->patient->full_name }}</td>
                <td class="text-right"><strong>MRN:</strong> {{ $ultrasoundResult->patient->mrn }}</td>
            </tr>
            <tr>
                <td><strong>Age:</strong> {{ $ultrasoundResult->patient->age ? $ultrasoundResult->patient->age . ' years' : 'N/A' }}</td>
                <td class="text-right"><strong>Visit #:</strong> {{ $ultrasoundResult->visit->visit_number }}</td>
            </tr>
            <tr>
                <td><strong>Date:</strong> {{ $ultrasoundResult->created_at->format('d M Y, H:i') }}</td>
                <td class="text-right"><strong>Performed By:</strong> {{ $ultrasoundResult->performedBy->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="result-info">
        <h3>Examination Type: {{ $ultrasoundResult->examination_type }}</h3>
        
        @if($ultrasoundResult->findings)
            <div style="margin-top: 15px;">
                <strong>Findings:</strong>
                <p>{{ $ultrasoundResult->findings }}</p>
            </div>
        @endif

        @if($ultrasoundResult->impression)
            <div style="margin-top: 15px;">
                <strong>Impression:</strong>
                <p><strong>{{ $ultrasoundResult->impression }}</strong></p>
            </div>
        @endif

        @if($ultrasoundResult->recommendation)
            <div style="margin-top: 15px;">
                <strong>Recommendation:</strong>
                <p>{{ $ultrasoundResult->recommendation }}</p>
            </div>
        @endif

        @if(!empty($images))
            <div class="images-section">
                <strong>Images:</strong>
                @foreach($images as $image)
                    <div class="image-container">
                        <img src="{{ Storage::url($image) }}" alt="Ultrasound Image">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
        <p>Printed on: {{ now()->format('d M Y, H:i') }}</p>
    </div>
</body>
</html>
