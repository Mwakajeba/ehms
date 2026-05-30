@php
    $audiometryCount = max(1, $audiometryItems->count());
    $deviceCount = max(1, $deviceItems->count());
    $totalCols = 3 + 2 + $audiometryCount + 1 + $deviceCount + 1;
@endphp

<table class="table table-bordered table-sm audiology-report-grid mb-0" style="font-size: 0.7rem;">
    <thead>
        <tr class="text-center align-middle">
            <th rowspan="2">NO</th>
            <th rowspan="2">PATIENT NAME</th>
            <th rowspan="2">IP No</th>
            <th colspan="2">PAYMENT</th>
            <th colspan="{{ $audiometryCount }}">AUDIOMETRY <small class="fw-normal">(Services)</small></th>
            <th rowspan="2">CONTACT</th>
            <th colspan="{{ $deviceCount }}">HEARING DEVICE AND ACCESSORIES <small class="fw-normal">(Products)</small></th>
            <th rowspan="2">DATE</th>
        </tr>
        <tr class="text-center align-middle">
            <th>CASH</th>
            <th>INSURANCE</th>
            @forelse($audiometryItems as $item)
                <th title="{{ $item->code ? 'Code: '.$item->code : '' }}">{{ $item->name }}</th>
            @empty
                <th class="text-muted">—</th>
            @endforelse
            @forelse($deviceItems as $item)
                <th title="{{ $item->code ? 'Code: '.$item->code : '' }}">{{ $item->name }}</th>
            @empty
                <th class="text-muted">—</th>
            @endforelse
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td class="text-center">{{ $row['no'] }}</td>
                <td>{{ $row['patient_name'] }}</td>
                <td class="text-center">{{ $row['ip_no'] }}</td>
                <td class="text-center">{{ $row['payment_cash'] }}</td>
                <td class="text-center">{{ $row['payment_insurance'] }}</td>
                @forelse($audiometryItems as $item)
                    <td class="text-end">
                        {{ \App\Services\Hospital\AudiologyReportService::amountForItem($row['service_amounts'], $item->id) }}
                    </td>
                @empty
                    <td></td>
                @endforelse
                <td>{{ $row['contact'] }}</td>
                @forelse($deviceItems as $item)
                    <td class="text-end">
                        {{ \App\Services\Hospital\AudiologyReportService::amountForItem($row['product_amounts'], $item->id) }}
                    </td>
                @empty
                    <td></td>
                @endforelse
                <td class="text-center">{{ $row['visit_date'] ? $row['visit_date']->format('d/m/Y') : '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="{{ $totalCols }}" class="text-center text-muted py-4">
                    No audiology visits found for the selected visiting date range.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
