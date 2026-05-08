@extends('layouts.main')

@section('title', 'Audiology Result')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Audiology', 'url' => route('hospital.audiology.index'), 'icon' => 'bx bx-volume-full'],
                ['label' => 'Result', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">AUDIOLOGY RESULT</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bx bx-volume-full me-2"></i>{{ $audiologyResult->result_number }}</h5>
                    <div class="d-flex gap-2">
                        @if($audiologyResult->result_status !== 'ready')
                            <form action="{{ route('hospital.audiology.mark-ready', $audiologyResult->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-sm btn-success">
                                    <i class="bx bx-check me-1"></i>Mark Ready
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('hospital.audiology.print', $audiologyResult->id) }}" class="btn btn-sm btn-light" target="_blank">
                            <i class="bx bx-printer me-1"></i>Print
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Patient:</strong> {{ $audiologyResult->patient->full_name ?? 'N/A' }}</p>
                            <p><strong>MRN:</strong> {{ $audiologyResult->patient->mrn ?? 'N/A' }}</p>
                            <p><strong>Visit #:</strong> {{ $audiologyResult->visit->visit_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Test Type:</strong> {{ $audiologyResult->test_type ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> <span class="badge bg-info">{{ ucfirst($audiologyResult->result_status) }}</span></p>
                            <p><strong>Completed At:</strong> {{ $audiologyResult->completed_at ? $audiologyResult->completed_at->format('d M Y, H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                    <hr />
                    <div class="mb-3">
                        <h6 class="fw-bold">Findings</h6>
                        <p class="text-muted mb-0">{{ $audiologyResult->findings ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Impression</h6>
                        <p class="text-muted mb-0">{{ $audiologyResult->impression ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <h6 class="fw-bold">Recommendation</h6>
                        <p class="text-muted mb-0">{{ $audiologyResult->recommendation ?? 'N/A' }}</p>
                    </div>
                    <div class="text-muted">
                        <small><strong>Performed By:</strong> {{ $audiologyResult->performedBy->name ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

