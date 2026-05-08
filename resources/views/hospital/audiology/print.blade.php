@extends('layouts.main')

@section('title', 'Print Audiology Result')

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .page-wrapper, .page-content { padding: 0 !important; margin: 0 !important; }
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="d-flex justify-content-end mb-3 no-print">
                <button class="btn btn-dark" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i>Print
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4 class="mb-0">Audiology Report</h4>
                        <small class="text-muted">Result #: {{ $audiologyResult->result_number }}</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Patient:</strong> {{ $audiologyResult->patient->full_name ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>MRN:</strong> {{ $audiologyResult->patient->mrn ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Visit #:</strong> {{ $audiologyResult->visit->visit_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Test Type:</strong> {{ $audiologyResult->test_type ?? 'N/A' }}</p>
                            <p class="mb-1"><strong>Status:</strong> {{ ucfirst($audiologyResult->result_status) }}</p>
                            <p class="mb-1"><strong>Completed At:</strong> {{ $audiologyResult->completed_at ? $audiologyResult->completed_at->format('d M Y, H:i') : 'N/A' }}</p>
                        </div>
                    </div>

                    <hr />

                    <div class="mb-3">
                        <h6 class="fw-bold">Findings</h6>
                        <p class="mb-0">{{ $audiologyResult->findings ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Impression</h6>
                        <p class="mb-0">{{ $audiologyResult->impression ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Recommendation</h6>
                        <p class="mb-0">{{ $audiologyResult->recommendation ?? 'N/A' }}</p>
                    </div>

                    <div class="mt-4 text-muted">
                        <small><strong>Performed By:</strong> {{ $audiologyResult->performedBy->name ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

