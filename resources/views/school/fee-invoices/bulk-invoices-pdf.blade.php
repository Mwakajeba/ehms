<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bulk Invoices for Outstanding Students</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .invoice {
            page-break-after: always;
            margin-bottom: 30px;
        }
        .invoice:last-child {
            page-break-after: avoid;
            margin-bottom: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 12px;
            color: #666;
        }
        .company-info {
            text-align: center;
            margin-bottom: 15px;
            font-size: 10px;
        }
        .invoice-info {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .invoice-info .left, .invoice-info .right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .invoice-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-info th, .invoice-info td {
            border: none;
            padding: 2px 0;
            text-align: left;
        }
        .invoice-info th {
            font-weight: bold;
            width: 40%;
            font-size: 10px;
        }
        .invoice-info td {
            font-size: 10px;
        }
        .fee-breakdown {
            margin-bottom: 15px;
        }
        .fee-breakdown table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .fee-breakdown th, .fee-breakdown td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 9px;
        }
        .fee-breakdown th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .fee-breakdown .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .fee-breakdown .total-row td {
            text-align: right;
        }
        .payment-info {
            margin-top: 15px;
            font-size: 9px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-issued { background-color: #007bff; color: white; }
        .status-paid { background-color: #28a745; color: white; }
        .status-partially_paid { background-color: #ffc107; color: black; }
        .status-overdue { background-color: #dc3545; color: white; }
        .status-cancelled { background-color: #6c757d; color: white; }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
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
    @if($invoices->isEmpty())
        <div class="no-data">
            No invoices found for students with outstanding balances.
        </div>
    @else
        @php
            // Group invoices by student
            $groupedInvoices = $invoices->groupBy('student_id');
        @endphp

        @foreach($groupedInvoices as $studentId => $studentInvoices)
            @php
                $student = $studentInvoices->first()->student;
                $totalOutstandingForStudent = 0;
            @endphp
        <div class="invoice">
            <div class="header">
                <div style="display: table; width: 100%; margin-bottom: 10px;">
                    <div style="display: table-cell; width: 20%; vertical-align: top;">
                        @if($company && $company->logo)
                            <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo" style="max-width: 80px; max-height: 80px;">
                        @endif
                    </div>
                    <div style="display: table-cell; width: 60%; text-align: center; vertical-align: top;">
                        <h1>{{ $company ? $company->name : 'School' }}</h1>
                        <h2>Consolidated Fee Statement</h2>
                    </div>
                    <div style="display: table-cell; width: 20%; text-align: right; vertical-align: top;">
                        <div style="font-size: 10px; color: #666;">
                            Generated: {{ date('M d, Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="company-info">
                @if($company)
                    <div>{{ $company->address }}</div>
                    <div>Phone: {{ $company->phone }} | Email: {{ $company->email }}</div>
                @endif
            </div>

            <!-- Student Info -->
            <div class="student-info" style="margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 25%; font-weight: bold; font-size: 10px;">Student Name:</td>
                        <td style="width: 25%; font-size: 10px;">{{ $student ? $student->first_name . ' ' . $student->last_name : 'N/A' }}</td>
                        <td style="width: 25%; font-weight: bold; font-size: 10px;">Admission Number:</td>
                        <td style="width: 25%; font-size: 10px;">{{ $student ? $student->admission_number : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; font-size: 10px;">Class:</td>
                        <td style="font-size: 10px;">{{ $studentInvoices->first()->classe ? $studentInvoices->first()->classe->name : 'N/A' }}</td>
                        <td style="font-weight: bold; font-size: 10px;">Academic Year:</td>
                        <td style="font-size: 10px;">{{ $studentInvoices->first()->academicYear ? $studentInvoices->first()->academicYear->year_name : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; font-size: 10px;">Boarding Type:</td>
                        <td style="font-size: 10px;">{{ ucfirst($student->boarding_type ?? 'day') }}</td>
                        <td style="font-weight: bold; font-size: 10px;">Fee Group:</td>
                        <td style="font-size: 10px;">{{ $studentInvoices->first()->feeGroup ? $studentInvoices->first()->feeGroup->name : 'N/A' }}</td>
                    </tr>
                    @if($student && ($student->discount_type && $student->discount_value))
                    <tr>
                        <td style="font-weight: bold; font-size: 10px;">Discount Type:</td>
                        <td style="font-size: 10px;">{{ ucfirst($student->discount_type) }}</td>
                        <td style="font-weight: bold; font-size: 10px;">Discount Value:</td>
                        <td style="font-size: 10px;">
                            @if($student->discount_type === 'percentage')
                                {{ $student->discount_value }}%
                            @else
                                TZS {{ number_format($student->discount_value, 2) }}
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- All Quarters for this Student in One Table -->
            <div class="fee-breakdown" style="margin-top: 20px;">
                <h4 style="margin: 0 0 15px 0; font-size: 14px; color: #007bff; text-align: center;">
                    Fee Summary - {{ count($outstandingQuarters) > 1 ? 'Quarters ' . implode(', ', $outstandingQuarters) : 'Quarter ' . implode('', $outstandingQuarters) }}
                </h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 6px; background-color: #f8f9fa; width: 25%; text-align: center; font-weight: bold;">Description</th>
                            @foreach($studentInvoices->sortBy('period') as $invoice)
                            <th style="border: 1px solid #ddd; padding: 6px; background-color: #f8f9fa; text-align: center; font-weight: bold;">
                                Quarter {{ $invoice->period }}<br>
                                <span style="font-size: 8px; font-weight: normal;">{{ $invoice->invoice_number }}</span>
                            </th>
                            @endforeach
                            <th style="border: 1px solid #ddd; padding: 6px; background-color: #007bff; color: white; text-align: center; font-weight: bold;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- School Fee Row -->
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 6px; font-weight: bold;">{{ $studentInvoices->first()->feeGroup ? $studentInvoices->first()->feeGroup->name . ' Fee' : 'School Fee' }} (Before Discount)</td>
                            @php $totalSchoolFee = 0; @endphp
                            @foreach($studentInvoices->sortBy('period') as $invoice)
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">
                                @if($invoice->subtotal > 0)
                                    TZS {{ number_format($invoice->subtotal, 2) }}
                                    @php $totalSchoolFee += $invoice->subtotal; @endphp
                                @else
                                    -
                                @endif
                            </td>
                            @endforeach
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; font-weight: bold; background-color: #e9ecef;">
                                TZS {{ number_format($totalSchoolFee, 2) }}
                            </td>
                        </tr>

                        <!-- Transport Fare Row -->
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 6px; font-weight: bold;">Transport Fare</td>
                            @php $totalTransportFare = 0; @endphp
                            @foreach($studentInvoices->sortBy('period') as $invoice)
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right;">
                                @if($invoice->transport_fare > 0)
                                    TZS {{ number_format($invoice->transport_fare, 2) }}
                                    @php $totalTransportFare += $invoice->transport_fare; @endphp
                                @else
                                    -
                                @endif
                            </td>
                            @endforeach
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; font-weight: bold; background-color: #e9ecef;">
                                TZS {{ number_format($totalTransportFare, 2) }}
                            </td>
                        </tr>

                        <!-- Net Fee Amount Row (After Discount) -->
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 6px; font-weight: bold; color: #17a2b8;">Net Fee Amount (After Discount)</td>
                            @php $totalNetFeeAmount = 0; @endphp
                            @foreach($studentInvoices->sortBy('period') as $invoice)
                            @php $netFeeAmount = $invoice->subtotal - $invoice->discount_amount; @endphp
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; color: #17a2b8;">
                                @if($netFeeAmount > 0)
                                    TZS {{ number_format($netFeeAmount, 2) }}
                                    @php $totalNetFeeAmount += $netFeeAmount; @endphp
                                @else
                                    -
                                @endif
                            </td>
                            @endforeach
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; font-weight: bold; color: #17a2b8; background-color: #e9ecef;">
                                TZS {{ number_format($totalNetFeeAmount, 2) }}
                            </td>
                        </tr>

                        <!-- Total Amount Row -->
                        <tr class="total-row">
                            <td style="border: 1px solid #ddd; padding: 6px; font-weight: bold;">Total Amount</td>
                            @php $grandTotalAmount = 0; @endphp
                            @foreach($studentInvoices->sortBy('period') as $invoice)
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; font-weight: bold;">
                                TZS {{ number_format($invoice->total_amount, 2) }}
                                @php $grandTotalAmount += $invoice->total_amount; @endphp
                            </td>
                            @endforeach
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; font-weight: bold; background-color: #007bff; color: white;">
                                TZS {{ number_format($grandTotalAmount, 2) }}
                            </td>
                        </tr>

                        <!-- Paid Amount Row -->
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 6px; font-weight: bold; color: #28a745;">Paid Amount</td>
                            @php $totalPaidAmount = 0; @endphp
                            @foreach($studentInvoices->sortBy('period') as $invoice)
                            @php $paidAmount = $invoice->paid_amount ?? 0; @endphp
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; color: #28a745;">
                                @if($paidAmount > 0)
                                    TZS {{ number_format($paidAmount, 2) }}
                                    @php $totalPaidAmount += $paidAmount; @endphp
                                @else
                                    -
                                @endif
                            </td>
                            @endforeach
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; font-weight: bold; color: #28a745; background-color: #e9ecef;">
                                TZS {{ number_format($totalPaidAmount, 2) }}
                            </td>
                        </tr>

                        <!-- Outstanding Amount Row -->
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 6px; font-weight: bold; color: #dc3545;">Outstanding Amount</td>
                            @php $totalOutstandingAmount = 0; @endphp
                            @foreach($studentInvoices->sortBy('period') as $invoice)
                            @php
                                $paidAmount = $invoice->paid_amount ?? 0;
                                $outstandingAmount = $invoice->total_amount - $paidAmount;
                                $totalOutstandingAmount += $outstandingAmount;
                            @endphp
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; color: #dc3545; font-weight: bold;">
                                TZS {{ number_format($outstandingAmount, 2) }}
                            </td>
                            @endforeach
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: right; font-weight: bold; color: white; background-color: #dc3545;">
                                TZS {{ number_format($totalOutstandingAmount, 2) }}
                            </td>
                        </tr>

                        <!-- Status Row -->
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 6px; font-weight: bold;">Status</td>
                            @foreach($studentInvoices->sortBy('period') as $invoice)
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center;">
                                <span class="status-badge status-{{ $invoice->status }}" style="font-size: 7px; padding: 1px 3px;">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                </span>
                            </td>
                            @endforeach
                            <td style="border: 1px solid #ddd; padding: 6px; text-align: center; font-weight: bold; background-color: #e9ecef;">
                                @if($totalOutstandingAmount <= 0)
                                    <span class="status-badge status-paid" style="font-size: 7px; padding: 1px 3px;">Paid</span>
                                @elseif($totalPaidAmount > 0)
                                    <span class="status-badge status-partially_paid" style="font-size: 7px; padding: 1px 3px;">Partial</span>
                                @else
                                    <span class="status-badge status-issued" style="font-size: 7px; padding: 1px 3px;">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Quarter Details Summary -->
            <div class="quarter-details" style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
                <h4 style="margin: 0 0 10px 0; font-size: 12px; color: #007bff;">Quarter Details</h4>
                <table style="width: 100%; border-collapse: collapse; font-size: 8px;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 4px; background-color: #e9ecef; width: 15%;">Quarter</th>
                            <th style="border: 1px solid #ddd; padding: 4px; background-color: #e9ecef; width: 25%;">Invoice #</th>
                            <th style="border: 1px solid #ddd; padding: 4px; background-color: #e9ecef; width: 20%;">Issue Date</th>
                            <th style="border: 1px solid #ddd; padding: 4px; background-color: #e9ecef; width: 20%;">Due Date</th>
                            <th style="border: 1px solid #ddd; padding: 4px; background-color: #e9ecef; width: 20%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentInvoices->sortBy('period') as $invoice)
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center; font-weight: bold;">Q{{ $invoice->period }}</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center;">{{ $invoice->invoice_number }}</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center;">{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') : 'N/A' }}</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center;">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}</td>
                            <td style="border: 1px solid #ddd; padding: 4px; text-align: center;">
                                <span class="status-badge status-{{ $invoice->status }}" style="font-size: 6px; padding: 1px 2px;">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <div>This is a computer-generated invoice. Generated on {{ date('Y-m-d H:i:s') }}</div>
                <div>For any queries, please contact the school administration.</div>
            </div>
        </div>
        @endforeach
    @endif
</body>
</html>