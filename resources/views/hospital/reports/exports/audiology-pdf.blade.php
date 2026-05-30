@php
    $audiometryCount = max(1, $audiometryItems->count());
    $deviceCount = max(1, $deviceItems->count());
    $company = \App\Models\Company::find(auth()->user()->company_id);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audiology Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 7px; margin: 8px; color: #333; }
        h1 { text-align: center; font-size: 12px; margin: 0 0 4px 0; }
        .subtitle { text-align: center; font-size: 8px; color: #666; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 2px 3px; text-align: center; vertical-align: middle; }
        th { background: #eee; font-weight: bold; font-size: 6px; }
        td.name { text-align: left; }
        td.amount { text-align: right; }
    </style>
</head>
<body>
    <h1>AUDIOLOGY REPORT — {{ $periodLabel }}</h1>
    <p class="subtitle">
        @if($company){{ $company->name }} — @endif
        Visiting dates: {{ $startDate->format('d/m/Y') }} to {{ $endDate->format('d/m/Y') }}
        — Generated {{ now()->format('d/m/Y H:i') }}
    </p>

    <table>
        <thead>
            <tr>
                <th rowspan="2">NO</th>
                <th rowspan="2">PATIENT NAME</th>
                <th rowspan="2">IP No</th>
                <th colspan="2">PAYMENT</th>
                <th colspan="{{ $audiometryCount }}">AUDIOMETRY (Services)</th>
                <th rowspan="2">CONTACT</th>
                <th colspan="{{ $deviceCount }}">DEVICES (Products)</th>
                <th rowspan="2">DATE</th>
            </tr>
            <tr>
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
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row['no'] }}</td>
                    <td class="name">{{ $row['patient_name'] }}</td>
                    <td>{{ $row['ip_no'] }}</td>
                    <td>{{ $row['payment_cash'] }}</td>
                    <td>{{ $row['payment_insurance'] }}</td>
                    @forelse($audiometryItems as $item)
                        <td class="amount">{{ \App\Services\Hospital\AudiologyReportService::amountForItem($row['service_amounts'], $item->id) }}</td>
                    @empty
                        <td></td>
                    @endforelse
                    <td>{{ $row['contact'] }}</td>
                    @forelse($deviceItems as $item)
                        <td class="amount">{{ \App\Services\Hospital\AudiologyReportService::amountForItem($row['product_amounts'], $item->id) }}</td>
                    @empty
                        <td></td>
                    @endforelse
                    <td>{{ $row['visit_date'] ? $row['visit_date']->format('d/m/Y') : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 3 + 2 + $audiometryCount + 1 + $deviceCount + 1 }}">No audiology visits in this period.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
            <tfoot>
                <tr style="font-weight: bold; background: #eee;">
                    <td colspan="3" style="text-align: right;">TOTAL ({{ $totals['visit_count'] }})</td>
                    <td></td>
                    <td></td>
                    @forelse($audiometryItems as $item)
                        <td class="amount">{{ \App\Services\Hospital\AudiologyReportService::formatTotalAmount($totals['service_totals'][$item->id] ?? 0) }}</td>
                    @empty
                        <td></td>
                    @endforelse
                    <td></td>
                    @forelse($deviceItems as $item)
                        <td class="amount">{{ \App\Services\Hospital\AudiologyReportService::formatTotalAmount($totals['product_totals'][$item->id] ?? 0) }}</td>
                    @empty
                        <td></td>
                    @endforelse
                    <td class="amount">{{ \App\Services\Hospital\AudiologyReportService::formatTotalAmount($totals['grand_total']) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
