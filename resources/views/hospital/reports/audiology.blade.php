@extends('layouts.main')

@section('title', 'Audiology Report')

@push('styles')
<style>
    .audiology-report-grid th,
    .audiology-report-grid td {
        vertical-align: middle;
        white-space: nowrap;
    }
    .audiology-report-grid thead th {
        background: #f8f9fa;
        font-weight: 600;
    }
    @media print {
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reports', 'url' => route('hospital.reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Audiology', 'url' => '#', 'icon' => 'bx bx-volume-full']
            ]" />
            <h6 class="mb-0 text-uppercase">AUDIOLOGY REPORT</h6>
            <hr />

            <div class="card mb-4 no-print">
                <div class="card-body">
                    <form method="GET" action="{{ route('hospital.reports.audiology') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Visiting Date From</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="{{ $startDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">Visiting Date To</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="{{ $endDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-filter me-1"></i>Generate Report
                            </button>
                            <a href="{{ route('hospital.reports.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 text-uppercase">
                        Audiology Report — {{ $periodLabel }}
                        <span class="badge bg-primary ms-2">{{ count($rows) }}</span>
                    </h5>
                    <div class="no-print">
                        <a class="btn btn-sm btn-outline-success me-1"
                           href="{{ route('hospital.reports.audiology.export.excel', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}">
                            <i class="bx bx-spreadsheet me-1"></i>Excel
                        </a>
                        <a class="btn btn-sm btn-outline-danger me-1"
                           href="{{ route('hospital.reports.audiology.export.pdf', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}">
                            <i class="bx bxs-file-pdf me-1"></i>PDF
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bx bx-printer me-1"></i>Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small no-print mb-3">
                        Filtered by <strong>visiting date</strong> from {{ $startDate->format('d M Y') }} to {{ $endDate->format('d M Y') }}.
                        <strong>Audiometry</strong> columns: all active inventory items with type <em>service</em> ({{ $audiometryItems->count() }}).
                        <strong>Devices</strong> columns: all active inventory items with type <em>product</em> ({{ $deviceItems->count() }}).
                        Amounts are filled from audiology invoice lines linked to each item.
                    </p>
                    <div class="table-responsive">
                        @include('hospital.reports.partials.audiology-report-table', [
                            'rows' => $rows,
                            'audiometryItems' => $audiometryItems,
                            'deviceItems' => $deviceItems,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
