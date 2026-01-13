@extends('layouts.main')

@section('title', 'RCH Record Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'RCH', 'url' => route('hospital.rch.index'), 'icon' => 'bx bx-heart'],
                ['label' => 'Record Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">RCH RECORD DETAILS</h6>
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
                                @if($rchRecord->status === 'pending')
                                    <form action="{{ route('hospital.rch.mark-completed', $rchRecord->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bx bx-check me-1"></i>Mark as Completed
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('hospital.rch.index') }}" class="btn btn-sm btn-secondary">
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
                                            <td><strong>{{ $rchRecord->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $rchRecord->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $rchRecord->patient->age ? $rchRecord->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Gender:</th>
                                            <td>{{ ucfirst($rchRecord->patient->gender ?? 'N/A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $rchRecord->visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Record #:</th>
                                            <td><strong>{{ $rchRecord->record_number }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Service Type:</th>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst(str_replace('_', ' ', $rchRecord->service_type)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'completed' => 'success',
                                                        'follow_up_required' => 'info'
                                                    ];
                                                    $statusColor = $statusColors[$rchRecord->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $rchRecord->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Performed By:</th>
                                            <td>{{ $rchRecord->performedBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td>{{ $rchRecord->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                        @if($rchRecord->completed_at)
                                            <tr>
                                                <th>Completed At:</th>
                                                <td>{{ $rchRecord->completed_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Service Details -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-heart me-2"></i>Service Details
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($rchRecord->service_description)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Service Description:</h6>
                                        <p class="mb-0">{{ $rchRecord->service_description }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($rchRecord->findings)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Clinical Findings:</h6>
                                        <p class="mb-0">{{ $rchRecord->findings }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($rchRecord->recommendations)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Recommendations:</h6>
                                        <p class="mb-0"><strong class="text-primary">{{ $rchRecord->recommendations }}</strong></p>
                                    </div>
                                </div>
                            @endif

                            @if($rchRecord->counseling_notes)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Counseling Notes:</h6>
                                        <p class="mb-0">{{ $rchRecord->counseling_notes }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($rchRecord->health_education_topics)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Health Education Topics:</h6>
                                        <p class="mb-0">{{ $rchRecord->health_education_topics }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($rchRecord->notes)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Additional Notes:</h6>
                                        <p class="mb-0">{{ $rchRecord->notes }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($rchRecord->next_appointment_date)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Next Appointment:</h6>
                                        <p class="mb-0">
                                            <strong class="text-info">{{ $rchRecord->next_appointment_date->format('d M Y') }}</strong>
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Vital Signs -->
                @if($rchRecord->vitals && !empty($rchRecord->vitals))
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-pulse me-2"></i>Vital Signs & Measurements
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @if(isset($rchRecord->vitals['weight']))
                                        <div class="col-md-3 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h6 class="text-muted mb-1">Weight</h6>
                                                <h4 class="mb-0">{{ $rchRecord->vitals['weight'] }} kg</h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($rchRecord->vitals['height']))
                                        <div class="col-md-3 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h6 class="text-muted mb-1">Height</h6>
                                                <h4 class="mb-0">{{ $rchRecord->vitals['height'] }} cm</h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($rchRecord->vitals['blood_pressure']))
                                        <div class="col-md-3 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h6 class="text-muted mb-1">Blood Pressure</h6>
                                                <h4 class="mb-0">{{ $rchRecord->vitals['blood_pressure'] }}</h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($rchRecord->vitals['temperature']))
                                        <div class="col-md-3 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h6 class="text-muted mb-1">Temperature</h6>
                                                <h4 class="mb-0">{{ $rchRecord->vitals['temperature'] }} °C</h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($rchRecord->vitals['pulse']))
                                        <div class="col-md-3 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h6 class="text-muted mb-1">Pulse</h6>
                                                <h4 class="mb-0">{{ $rchRecord->vitals['pulse'] }} bpm</h4>
                                            </div>
                                        </div>
                                    @endif
                                    @if(isset($rchRecord->vitals['respiratory_rate']))
                                        <div class="col-md-3 mb-3">
                                            <div class="border rounded p-3 text-center">
                                                <h6 class="text-muted mb-1">Respiratory Rate</h6>
                                                <h4 class="mb-0">{{ $rchRecord->vitals['respiratory_rate'] }} /min</h4>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
