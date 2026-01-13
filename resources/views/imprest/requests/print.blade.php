<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imprest Request - {{ $imprestRequest->request_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 15px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            display: inline-block;
        }
        
        .info-value {
            flex: 1;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .amount-cell {
            text-align: right;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        .signatures {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
        }
        
        .purpose-section {
            margin-bottom: 20px;
        }
        
        .purpose-text {
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
            min-height: 60px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-checked {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #99d6ff;
        }
        
        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #a3d977;
        }
        
        .status-disbursed {
            background-color: #e7f3ff;
            color: #0056b3;
            border: 1px solid #b3d9ff;
        }
        
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f1aeb5;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 15px;
            }
            
            .signatures {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ config('app.name', 'Smart Accounting') }}</div>
        <div>P.O. Box 12345, Dar es Salaam, Tanzania</div>
        <div>Tel: +255 123 456 789 | Email: info@smartaccounting.co.tz</div>
        <div class="document-title">IMPREST REQUEST FORM</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Request Number:</span>
            <span class="info-value">{{ $imprestRequest->request_number }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Employee:</span>
            <span class="info-value">{{ $imprestRequest->employee->name ?? 'N/A' }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Department:</span>
            <span class="info-value">{{ $imprestRequest->department->name ?? 'N/A' }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Date Required:</span>
            <span class="info-value">{{ $imprestRequest->date_required ? $imprestRequest->date_required->format('F j, Y') : 'N/A' }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Date Created:</span>
            <span class="info-value">{{ $imprestRequest->created_at->format('F j, Y') }}</span>
        </div>
        
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $imprestRequest->getStatusLabel())) }}">
                    {{ $imprestRequest->getStatusLabel() }}
                </span>
            </span>
        </div>
    </div>

    <div class="purpose-section">
        <div class="info-label">Purpose:</div>
        <div class="purpose-text">{{ $imprestRequest->purpose }}</div>
    </div>

    <h3>Imprest Items</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="25%">Chart Account</th>
                <th width="50%">Description</th>
                <th width="20%">Amount (TZS)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($imprestRequest->imprestItems as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ $item->chartAccount->account_code ?? 'N/A' }}<br>
                    <small>{{ $item->chartAccount->account_name ?? 'N/A' }}</small>
                </td>
                <td>{{ $item->notes }}</td>
                <td class="amount-cell">{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" style="text-align: right;"><strong>Total Amount:</strong></td>
                <td class="amount-cell"><strong>{{ number_format($imprestRequest->amount_requested, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($imprestRequest->disbursed_amount)
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Amount Disbursed:</span>
            <span class="info-value"><strong>TZS {{ number_format($imprestRequest->disbursed_amount, 2) }}</strong></span>
        </div>
        
        @if($imprestRequest->disbursed_at)
        <div class="info-row">
            <span class="info-label">Disbursed Date:</span>
            <span class="info-value">{{ $imprestRequest->disbursed_at->format('F j, Y') }}</span>
        </div>
        @endif
    </div>
    @endif

    @if($imprestRequest->check_comments || $imprestRequest->approval_comments)
    <div class="info-section">
        <h3>Comments</h3>
        
        @if($imprestRequest->check_comments)
        <div class="info-row">
            <span class="info-label">Check Comments:</span>
            <span class="info-value">{{ $imprestRequest->check_comments }}</span>
        </div>
        @endif
        
        @if($imprestRequest->approval_comments)
        <div class="info-row">
            <span class="info-label">Approval Comments:</span>
            <span class="info-value">{{ $imprestRequest->approval_comments }}</span>
        </div>
        @endif
    </div>
    @endif

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                Requested By<br>
                <strong>{{ $imprestRequest->employee->name ?? 'N/A' }}</strong><br>
                Date: {{ $imprestRequest->created_at->format('d/m/Y') }}
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                Checked By<br>
                <strong>{{ $imprestRequest->checker->name ?? '________________' }}</strong><br>
                Date: {{ $imprestRequest->checked_at ? $imprestRequest->checked_at->format('d/m/Y') : '___________' }}
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                Approved By<br>
                <strong>{{ $imprestRequest->approver->name ?? '________________' }}</strong><br>
                Date: {{ $imprestRequest->approved_at ? $imprestRequest->approved_at->format('d/m/Y') : '___________' }}
            </div>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
        <p>This document was generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>{{ config('app.name', 'Smart Accounting System') }} - Imprest Management Module</p>
    </div>
</body>
</html>