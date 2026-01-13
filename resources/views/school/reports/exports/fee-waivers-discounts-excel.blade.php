<table>
    <thead>
        <tr>
            <th colspan="12" style="text-align: center; font-size: 16px; font-weight: bold; background-color: #ffc107; color: #333; padding: 10px;">
                FEE WAIVERS & DISCOUNTS REPORT
            </th>
        </tr>
        @if($company)
        <tr>
            <th colspan="12" style="text-align: center; font-size: 14px; font-weight: bold; padding: 5px;">
                {{ $company->name }}
            </th>
        </tr>
        @endif
        <tr>
            <th colspan="12" style="text-align: center; font-size: 11px; padding: 5px;">
                Generated on: {{ now()->format('F d, Y h:i A') }}
            </th>
        </tr>
        <tr>
            <th colspan="12" style="text-align: left; font-size: 11px; padding: 5px;">
                Date From: {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} | 
                Date To: {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
                @if($academicYear) | Academic Year: {{ $academicYear->year_name }} @endif
                @if($class) | Class: {{ $class->name }} @endif
                @if($stream) | Stream: {{ $stream->name }} @endif
                @if($discountType) | Discount Type: {{ ucfirst($discountType) }} @endif
                @if($period) | Period: {{ $period }} @endif
            </th>
        </tr>
        @if(!empty($waiversDiscountsData) && !empty($waiversDiscountsData['grand_totals']))
        <tr>
            <th colspan="12" style="text-align: left; font-size: 12px; font-weight: bold; padding: 10px 5px; background-color: #f8f9fa;">
                Grand Totals Summary
            </th>
        </tr>
        <tr style="background-color: #ffc107; color: #333;">
            <th>Total Invoices</th>
            <th>Total Subtotal</th>
            <th>Total Discounts</th>
            <th>After Discount</th>
            <th>Discount Rate (%)</th>
            <th colspan="7"></th>
        </tr>
        <tr>
            <td>{{ number_format($waiversDiscountsData['grand_totals']['total_invoices']) }}</td>
            <td>{{ number_format($waiversDiscountsData['grand_totals']['total_subtotal'], 2) }}</td>
            <td>{{ number_format($waiversDiscountsData['grand_totals']['total_discount_amount'], 2) }}</td>
            <td>{{ number_format($waiversDiscountsData['grand_totals']['total_after_discount'], 2) }}</td>
            <td style="font-weight: bold;">{{ number_format($waiversDiscountsData['grand_totals']['discount_percentage'], 2) }}%</td>
            <td colspan="7"></td>
        </tr>
        @endif
        <tr></tr>
        @if(!empty($waiversDiscountsData) && !empty($waiversDiscountsData['classes']))
            @foreach($waiversDiscountsData['classes'] as $classData)
            <tr>
                <th colspan="12" style="text-align: left; font-size: 12px; font-weight: bold; padding: 10px 5px; background-color: #ffc107; color: #333;">
                    {{ $classData['class_name'] }} - Invoices: {{ $classData['total_invoices'] }} | 
                    Subtotal: {{ number_format($classData['total_subtotal'], 2) }} | 
                    Discounts: {{ number_format($classData['total_discount_amount'], 2) }} | 
                    Rate: {{ number_format($classData['discount_percentage'], 2) }}%
                </th>
            </tr>
            @if(!empty($classData['streams']))
                @foreach($classData['streams'] as $streamData)
                <tr>
                    <th colspan="12" style="text-align: left; font-size: 11px; font-weight: bold; padding: 8px 5px; background-color: #ffd54f; color: #333;">
                        {{ $streamData['stream_name'] }}
                    </th>
                </tr>
                <tr style="background-color: #e9ecef;">
                    <th>Hash ID</th>
                    <th>Invoice #</th>
                    <th>Student</th>
                    <th>Admission #</th>
                    <th>Period</th>
                    <th>Issue Date</th>
                    <th>Subtotal</th>
                    <th>Discount Type</th>
                    <th>Discount Value</th>
                    <th>Discount Amount</th>
                    <th>After Discount</th>
                    <th>Total Amount</th>
                </tr>
                @if(!empty($streamData['invoices']))
                    @foreach($streamData['invoices'] as $invoice)
                    <tr>
                        <td>{{ $invoice['hash_id'] }}</td>
                        <td>{{ $invoice['invoice_number'] }}</td>
                        <td>{{ $invoice['student_name'] }}</td>
                        <td>{{ $invoice['admission_number'] }}</td>
                        <td>{{ $invoice['period'] }}</td>
                        <td>{{ $invoice['issue_date'] }}</td>
                        <td>{{ number_format($invoice['subtotal'], 2) }}</td>
                        <td>{{ ucfirst($invoice['discount_type']) }}</td>
                        <td>
                            @if($invoice['discount_type'] == 'percentage')
                                {{ number_format($invoice['discount_value'], 2) }}%
                            @elseif($invoice['discount_type'] == 'fixed')
                                {{ number_format($invoice['discount_value'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ number_format($invoice['discount_amount'], 2) }}</td>
                        <td>{{ number_format($invoice['after_discount'], 2) }}</td>
                        <td>{{ number_format($invoice['total_amount'], 2) }}</td>
                    </tr>
                    @endforeach
                    <tr style="font-weight: bold; background-color: #f8f9fa;">
                        <td colspan="6">Stream Total</td>
                        <td>{{ number_format($streamData['total_subtotal'], 2) }}</td>
                        <td colspan="2"></td>
                        <td>{{ number_format($streamData['total_discount_amount'], 2) }}</td>
                        <td>{{ number_format($streamData['total_after_discount'], 2) }}</td>
                        <td></td>
                    </tr>
                @endif
                @endforeach
            @endif
            <tr></tr>
            @endforeach
        @else
        <tr>
            <td colspan="12" style="text-align: center; padding: 20px; color: #666;">
                No waivers or discounts found for the selected filters.
            </td>
        </tr>
        @endif
    </thead>
</table>

