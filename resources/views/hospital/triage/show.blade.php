@extends('layouts.main')

@section('title', 'Triage Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Triage', 'url' => route('hospital.triage.index'), 'icon' => 'bx bx-pulse'],
                ['label' => 'Triage Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">TRIAGE DETAILS</h6>
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
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Patient Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Patient:</th>
                                            <td><strong>{{ $visit->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $visit->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Visit Type:</th>
                                            <td>{{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit Date:</th>
                                            <td>{{ $visit->visit_date->format('d M Y, H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Chief Complaint:</th>
                                            <td>{{ $visit->chief_complaint ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($visit->triageVitals)
                    <!-- Vital Signs -->
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-pulse me-2"></i>Vital Signs
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="50%">Temperature:</th>
                                                <td>{{ $visit->triageVitals->temperature ? $visit->triageVitals->temperature . ' °C' : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Blood Pressure:</th>
                                                <td>{{ $visit->triageVitals->blood_pressure_formatted ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Pulse Rate:</th>
                                                <td>{{ $visit->triageVitals->pulse_rate ? $visit->triageVitals->pulse_rate . ' bpm' : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Respiratory Rate:</th>
                                                <td>{{ $visit->triageVitals->respiratory_rate ? $visit->triageVitals->respiratory_rate . ' /min' : 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th width="50%">Oxygen Saturation:</th>
                                                <td>{{ $visit->triageVitals->oxygen_saturation ? $visit->triageVitals->oxygen_saturation . ' %' : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Weight:</th>
                                                <td>{{ $visit->triageVitals->weight ? $visit->triageVitals->weight . ' kg' : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Height:</th>
                                                <td>{{ $visit->triageVitals->height ? $visit->triageVitals->height . ' cm' : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>BMI:</th>
                                                <td>{{ $visit->triageVitals->bmi ?? 'N/A' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assessment -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-clipboard me-2"></i>Assessment
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Priority:</th>
                                        <td>
                                            @php
                                                $priorityColors = [
                                                    'low' => 'success',
                                                    'medium' => 'warning',
                                                    'high' => 'danger',
                                                    'critical' => 'dark'
                                                ];
                                                $priorityColor = $priorityColors[$visit->triageVitals->priority] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $priorityColor }}">
                                                {{ ucfirst($visit->triageVitals->priority) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Taken By:</th>
                                        <td>{{ $visit->triageVitals->takenBy->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date:</th>
                                        <td>{{ $visit->triageVitals->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                </table>
                                @if($visit->triageVitals->chief_complaint)
                                    <div class="mt-3">
                                        <strong>Chief Complaint:</strong>
                                        <p class="mb-0">{{ $visit->triageVitals->chief_complaint }}</p>
                                    </div>
                                @endif
                                @if($visit->triageVitals->triage_notes)
                                    <div class="mt-3">
                                        <strong>Triage Notes:</strong>
                                        <p class="mb-0">{{ $visit->triageVitals->triage_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            Triage vitals not yet recorded for this visit.
                            <a href="{{ route('hospital.triage.create', $visit->id) }}" class="btn btn-sm btn-primary ms-2">
                                <i class="bx bx-plus me-1"></i>Record Vitals
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
