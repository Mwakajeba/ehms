@php
    $audiometryCount = max(1, $audiometryItems->count());
    $deviceCount = max(1, $deviceItems->count());
@endphp
<table border="1" cellpadding="4" cellspacing="0">
    <thead>
        <tr>
            <th colspan="{{ 3 + 2 + $audiometryCount + 1 + $deviceCount + 1 }}" style="font-weight: bold; text-align: center; font-size: 14px;">
                AUDIOLOGY REPORT — {{ $periodLabel }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ 3 + 2 + $audiometryCount + 1 + $deviceCount + 1 }}" style="text-align: center;">
                Visiting dates: {{ $startDate->format('d/m/Y') }} — {{ $endDate->format('d/m/Y') }}
            </th>
        </tr>
        <tr></tr>
        <tr style="text-align: center; font-weight: bold;">
            <th rowspan="2">NO</th>
            <th rowspan="2">PATIENT NAME</th>
            <th rowspan="2">IP No</th>
            <th colspan="2">PAYMENT</th>
            <th colspan="{{ $audiometryCount }}">AUDIOMETRY (Services)</th>
            <th rowspan="2">CONTACT</th>
            <th colspan="{{ $deviceCount }}">HEARING DEVICE AND ACCESSORIES (Products)</th>
            <th rowspan="2">DATE</th>
        </tr>
        <tr style="text-align: center; font-weight: bold;">
            <th>CASH</th>
            <th>INSURANCE</th>
            @forelse($audiometryItems as $item)
                <th>{{ $item->name }}</th>
            @empty
                <th>—</th>
            @endforelse
            @forelse($deviceItems as $item)
                <th>{{ $item->name }}</th>
            @empty
                <th>—</th>
            @endforelse
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td style="text-align: center;">{{ $row['no'] }}</td>
                <td>{{ $row['patient_name'] }}</td>
                <td style="text-align: center;">{{ $row['ip_no'] }}</td>
                <td style="text-align: center;">{{ $row['payment_cash'] }}</td>
                <td style="text-align: center;">{{ $row['payment_insurance'] }}</td>
                @forelse($audiometryItems as $item)
                    <td style="text-align: right;">
                        {{ \App\Services\Hospital\AudiologyReportService::amountForItem($row['service_amounts'], $item->id) }}
                    </td>
                @empty
                    <td></td>
                @endforelse
                <td>{{ $row['contact'] }}</td>
                @forelse($deviceItems as $item)
                    <td style="text-align: right;">
                        {{ \App\Services\Hospital\AudiologyReportService::amountForItem($row['product_amounts'], $item->id) }}
                    </td>
                @empty
                    <td></td>
                @endforelse
                <td style="text-align: center;">{{ $row['visit_date'] ? $row['visit_date']->format('d/m/Y') : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
