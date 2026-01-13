@extends('layouts.main')

@section('title', 'Lab Result Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Lab', 'url' => route('hospital.lab.index'), 'icon' => 'bx bx-test-tube'],
                ['label' => 'Result Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">LAB RESULT DETAILS</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Patient Info -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Patient Information
                            </h5>
                            <div>
                                @if($labResult->result_status === 'ready')
                                    <a href="{{ route('hospital.lab.print', $labResult->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bx bx-printer me-1"></i>Print
                                    </a>
                                @elseif($labResult->result_status === 'pending')
                                    <form action="{{ route('hospital.lab.mark-ready', $labResult->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bx bx-check me-1"></i>Mark as Ready
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('hospital.lab.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Patient:</th>
                                            <td><strong>{{ $labResult->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $labResult->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $labResult->patient->age ? $labResult->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $labResult->visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Result #:</th>
                                            <td><strong>{{ $labResult->result_number }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Result Status:</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'ready' => 'success',
                                                        'printed' => 'info',
                                                        'delivered' => 'primary'
                                                    ];
                                                    $statusColor = $statusColors[$labResult->result_status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst($labResult->result_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Performed By:</th>
                                            <td>{{ $labResult->performedBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td>{{ $labResult->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-test-tube me-2"></i>Test Results
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Test Name:</th>
                                            <td><strong>{{ $labResult->test_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Result Value:</th>
                                            <td>
                                                <strong>{{ $labResult->result_value ?? 'N/A' }}</strong>
                                                @if($labResult->unit)
                                                    <span class="text-muted"> {{ $labResult->unit }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Reference Range:</th>
                                            <td>{{ $labResult->reference_range ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Status:</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'normal' => 'success',
                                                        'abnormal' => 'warning',
                                                        'critical' => 'danger'
                                                    ];
                                                    $statusColor = $statusColors[$labResult->status] ?? 'secondary';
                                                @endphp
                                                @if($labResult->status)
                                                    <span class="badge bg-{{ $statusColor }}">
                                                        {{ ucfirst($labResult->status) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($labResult->completed_at)
                                            <tr>
                                                <th>Completed At:</th>
                                                <td>{{ $labResult->completed_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                        @endif
                                        @if($labResult->printed_at)
                                            <tr>
                                                <th>Printed At:</th>
                                                <td>{{ $labResult->printed_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            @if($labResult->notes)
                                <div class="mt-3">
                                    <h6>Notes:</h6>
                                    <p class="mb-0">{{ $labResult->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
