<table>
    <thead>
        <tr>
            <th colspan="11" style="text-align: center; font-size: 16px; font-weight: bold; background-color: #667eea; color: white; padding: 10px;">
                FEE AGING REPORT
            </th>
        </tr>
        @if($company)
        <tr>
            <th colspan="11" style="text-align: center; font-size: 14px; font-weight: bold; padding: 5px;">
                {{ $company->name }}
            </th>
        </tr>
        @endif
        <tr>
            <th colspan="11" style="text-align: center; font-size: 11px; padding: 5px;">
                Generated on: {{ now()->format('F d, Y h:i A') }}
            </th>
        </tr>
        <tr>
            <th colspan="11" style="text-align: left; font-size: 11px; padding: 5px;">
                As of Date: {{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}
                @if($academicYear) | Academic Year: {{ $academicYear->year_name }} @endif
                @if($class) | Class: {{ $class->name }} @endif
                @if($stream) | Stream: {{ $stream->name }} @endif
                @if($feeGroup) | Fee Group: {{ $feeGroup->name }} @endif
            </th>
        </tr>
        @if(!empty($agingData) && !empty($agingData['grand_totals']))
        <tr>
            <th colspan="11" style="text-align: left; font-size: 12px; font-weight: bold; padding: 10px 5px; background-color: #f8f9fa;">
                Grand Totals Summary
            </th>
        </tr>
        <tr style="background-color: #667eea; color: white;">
            <th>Current</th>
            <th>0-30 Days</th>
            <th>31-60 Days</th>
            <th>61-90 Days</th>
            <th>91+ Days</th>
            <th>Total Outstanding</th>
            <th colspan="5"></th>
        </tr>
        <tr>
            <td>{{ number_format($agingData['grand_totals']['current'], 2) }}</td>
            <td>{{ number_format($agingData['grand_totals']['0-30'], 2) }}</td>
            <td>{{ number_format($agingData['grand_totals']['31-60'], 2) }}</td>
            <td>{{ number_format($agingData['grand_totals']['61-90'], 2) }}</td>
            <td>{{ number_format($agingData['grand_totals']['91+'], 2) }}</td>
            <td style="font-weight: bold;">{{ number_format($agingData['grand_totals']['total_outstanding'], 2) }}</td>
            <td colspan="5"></td>
        </tr>
        @endif
        <tr></tr>
        @if(!empty($agingData) && !empty($agingData['fee_groups']))
            @foreach($agingData['fee_groups'] as $feeGroup)
            <tr>
                <th colspan="11" style="text-align: left; font-size: 12px; font-weight: bold; padding: 10px 5px; background-color: #667eea; color: white;">
                    {{ $feeGroup['fee_group_name'] }} @if($feeGroup['fee_group_code'])({{ $feeGroup['fee_group_code'] }})@endif
                </th>
            </tr>
            <tr style="background-color: #e9ecef;">
                <th>Current</th>
                <th>0-30 Days</th>
                <th>31-60 Days</th>
                <th>61-90 Days</th>
                <th>91+ Days</th>
                <th>Total Outstanding</th>
                <th colspan="5"></th>
            </tr>
            <tr>
                <td>{{ number_format($feeGroup['current'], 2) }}</td>
                <td>{{ number_format($feeGroup['0-30'], 2) }}</td>
                <td>{{ number_format($feeGroup['31-60'], 2) }}</td>
                <td>{{ number_format($feeGroup['61-90'], 2) }}</td>
                <td>{{ number_format($feeGroup['91+'], 2) }}</td>
                <td style="font-weight: bold;">{{ number_format($feeGroup['total_outstanding'], 2) }}</td>
                <td colspan="5"></td>
            </tr>
            <tr style="background-color: #667eea; color: white;">
                <th>Invoice #</th>
                <th>Student</th>
                <th>Class</th>
                <th>Stream</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Days Overdue</th>
                <th>Total Amount</th>
                <th>Paid</th>
                <th>Outstanding</th>
                <th>Aging</th>
            </tr>
            @if(!empty($feeGroup['invoices']))
                @foreach($feeGroup['invoices'] as $invoice)
                <tr>
                    <td>{{ $invoice['invoice_number'] }}</td>
                    <td>{{ $invoice['student_name'] }}</td>
                    <td>{{ $invoice['class_name'] }}</td>
                    <td>{{ $invoice['stream_name'] }}</td>
                    <td>{{ $invoice['issue_date'] }}</td>
                    <td>{{ $invoice['due_date'] }}</td>
                    <td>{{ $invoice['days_overdue'] }}</td>
                    <td>{{ number_format($invoice['total_amount'], 2) }}</td>
                    <td>{{ number_format($invoice['paid_amount'], 2) }}</td>
                    <td style="font-weight: bold;">{{ number_format($invoice['outstanding_amount'], 2) }}</td>
                    <td>{{ ucfirst(str_replace('-', ' ', $invoice['aging_bucket'])) }}</td>
                </tr>
                @endforeach
            @endif
            <tr></tr>
            @endforeach
        @else
        <tr>
            <td colspan="11" style="text-align: center; padding: 20px; color: #666;">
                No outstanding fees found for the selected filters.
            </td>
        </tr>
        @endif
    </thead>
</table>

