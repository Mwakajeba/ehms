<table>
    <thead>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 16px; font-weight: bold; background-color: #667eea; color: white; padding: 10px;">
                CLASS-WISE REVENUE COLLECTION REPORT
            </th>
        </tr>
        @if($company)
        <tr>
            <th colspan="10" style="text-align: center; font-size: 14px; font-weight: bold; padding: 5px;">
                {{ $company->name }}
            </th>
        </tr>
        @endif
        <tr>
            <th colspan="10" style="text-align: center; font-size: 11px; padding: 5px;">
                Generated on: {{ now()->format('F d, Y h:i A') }}
            </th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: left; font-size: 11px; padding: 5px;">
                Date From: {{ \Carbon\Carbon::parse($dateFrom)->format('F d, Y') }} | 
                Date To: {{ \Carbon\Carbon::parse($dateTo)->format('F d, Y') }}
                @if($academicYear) | Academic Year: {{ $academicYear->year_name }} @endif
                @if($class) | Class: {{ $class->name }} @endif
                @if($stream) | Stream: {{ $stream->name }} @endif
                @if($period) | Period: {{ $period }} @endif
            </th>
        </tr>
        @if(!empty($revenueData) && !empty($revenueData['grand_totals']))
        <tr>
            <th colspan="10" style="text-align: left; font-size: 12px; font-weight: bold; padding: 10px 5px; background-color: #f8f9fa;">
                Grand Totals Summary
            </th>
        </tr>
        <tr style="background-color: #667eea; color: white;">
            <th>Total Invoices</th>
            <th>Total Billed</th>
            <th>Total Collected</th>
            <th>Total Outstanding</th>
            <th>Collection Rate (%)</th>
            <th colspan="5"></th>
        </tr>
        <tr>
            <td>{{ number_format($revenueData['grand_totals']['total_invoices']) }}</td>
            <td>{{ number_format($revenueData['grand_totals']['total_billed'], 2) }}</td>
            <td>{{ number_format($revenueData['grand_totals']['total_collected'], 2) }}</td>
            <td>{{ number_format($revenueData['grand_totals']['total_outstanding'], 2) }}</td>
            <td style="font-weight: bold;">{{ number_format($revenueData['grand_totals']['collection_rate'], 2) }}%</td>
            <td colspan="5"></td>
        </tr>
        @endif
        <tr></tr>
        @if(!empty($revenueData) && !empty($revenueData['classes']))
            @foreach($revenueData['classes'] as $classData)
            <tr>
                <th colspan="10" style="text-align: left; font-size: 12px; font-weight: bold; padding: 10px 5px; background-color: #667eea; color: white;">
                    {{ $classData['class_name'] }} - Invoices: {{ $classData['total_invoices'] }} | 
                    Billed: {{ number_format($classData['total_billed'], 2) }} | 
                    Collected: {{ number_format($classData['total_collected'], 2) }} | 
                    Rate: {{ number_format($classData['collection_rate'], 2) }}%
                </th>
            </tr>
            @if(!empty($classData['streams']))
                @foreach($classData['streams'] as $streamData)
                <tr>
                    <th colspan="10" style="text-align: left; font-size: 11px; font-weight: bold; padding: 8px 5px; background-color: #f093fb; color: white;">
                        {{ $streamData['stream_name'] }}
                    </th>
                </tr>
                <tr style="background-color: #e9ecef;">
                    <th>Period</th>
                    <th>Billed</th>
                    <th>Collected</th>
                    <th>Outstanding</th>
                    <th>Collection Rate (%)</th>
                    <th colspan="5"></th>
                </tr>
                @foreach(['Q1', 'Q2', 'Q3', 'Q4', 'Annual'] as $periodKey)
                    @if($streamData['periods'][$periodKey]['billed'] > 0 || $streamData['periods'][$periodKey]['collected'] > 0)
                    <tr>
                        <td>{{ $periodKey }}</td>
                        <td>{{ number_format($streamData['periods'][$periodKey]['billed'], 2) }}</td>
                        <td>{{ number_format($streamData['periods'][$periodKey]['collected'], 2) }}</td>
                        <td>{{ number_format($streamData['periods'][$periodKey]['outstanding'], 2) }}</td>
                        <td>{{ number_format($streamData['periods'][$periodKey]['collection_rate'], 2) }}%</td>
                        <td colspan="5"></td>
                    </tr>
                    @endif
                @endforeach
                <tr style="font-weight: bold; background-color: #f8f9fa;">
                    <td>Stream Total</td>
                    <td>{{ number_format($streamData['total_billed'], 2) }}</td>
                    <td>{{ number_format($streamData['total_collected'], 2) }}</td>
                    <td>{{ number_format($streamData['total_outstanding'], 2) }}</td>
                    <td>{{ number_format($streamData['collection_rate'], 2) }}%</td>
                    <td colspan="5"></td>
                </tr>
                @endforeach
            @endif
            <tr>
                <th colspan="10" style="text-align: left; font-size: 11px; font-weight: bold; padding: 8px 5px; background-color: #e9ecef;">
                    Class Period Summary
                </th>
            </tr>
            <tr style="background-color: #e9ecef;">
                <th>Period</th>
                <th>Billed</th>
                <th>Collected</th>
                <th>Outstanding</th>
                <th>Collection Rate (%)</th>
                <th colspan="5"></th>
            </tr>
            @foreach(['Q1', 'Q2', 'Q3', 'Q4', 'Annual'] as $periodKey)
                @if($classData['periods'][$periodKey]['billed'] > 0 || $classData['periods'][$periodKey]['collected'] > 0)
                <tr>
                    <td><strong>{{ $periodKey }}</strong></td>
                    <td>{{ number_format($classData['periods'][$periodKey]['billed'], 2) }}</td>
                    <td>{{ number_format($classData['periods'][$periodKey]['collected'], 2) }}</td>
                    <td>{{ number_format($classData['periods'][$periodKey]['outstanding'], 2) }}</td>
                    <td>{{ number_format($classData['periods'][$periodKey]['collection_rate'], 2) }}%</td>
                    <td colspan="5"></td>
                </tr>
                @endif
            @endforeach
            <tr></tr>
            @endforeach
        @else
        <tr>
            <td colspan="10" style="text-align: center; padding: 20px; color: #666;">
                No revenue data found for the selected filters.
            </td>
        </tr>
        @endif
    </thead>
</table>

