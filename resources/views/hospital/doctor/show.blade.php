@extends('layouts.main')

@section('title', 'Consultation Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Doctor', 'url' => route('hospital.doctor.index'), 'icon' => 'bx bx-user-md'],
                ['label' => 'Consultation Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">CONSULTATION DETAILS</h6>
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
                                        @if($visit->triageVitals)
                                            <tr>
                                                <th>Priority:</th>
                                                <td>
                                                    <span class="badge bg-{{ $visit->triageVitals->priority == 'critical' ? 'dark' : ($visit->triageVitals->priority == 'high' ? 'danger' : ($visit->triageVitals->priority == 'medium' ? 'warning' : 'success')) }}">
                                                        {{ ucfirst($visit->triageVitals->priority) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($visit->consultation)
                    <!-- Consultation Details -->
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-user-md me-2"></i>Consultation Details
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Doctor:</strong> {{ $visit->consultation->doctor->name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Date:</strong> {{ $visit->consultation->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>

                                @if($visit->consultation->chief_complaint)
                                    <div class="mb-3">
                                        <h6>Chief Complaint:</h6>
                                        <p class="mb-0">{{ $visit->consultation->chief_complaint }}</p>
                                    </div>
                                @endif

                                @if($visit->consultation->history_of_present_illness)
                                    <div class="mb-3">
                                        <h6>History of Present Illness:</h6>
                                        <p class="mb-0">{{ $visit->consultation->history_of_present_illness }}</p>
                                    </div>
                                @endif

                                @if($visit->consultation->physical_examination)
                                    <div class="mb-3">
                                        <h6>Physical Examination:</h6>
                                        <p class="mb-0">{{ $visit->consultation->physical_examination }}</p>
                                    </div>
                                @endif

                                @if($visit->consultation->diagnosis)
                                    <div class="mb-3">
                                        <h6>Diagnosis:</h6>
                                        <p class="mb-0 text-danger"><strong>{{ $visit->consultation->diagnosis }}</strong></p>
                                    </div>
                                @endif

                                @if($visit->consultation->treatment_plan)
                                    <div class="mb-3">
                                        <h6>Treatment Plan:</h6>
                                        <p class="mb-0">{{ $visit->consultation->treatment_plan }}</p>
                                    </div>
                                @endif

                                @if($visit->consultation->prescription)
                                    <div class="mb-3">
                                        <h6>Prescription:</h6>
                                        <p class="mb-0">{{ $visit->consultation->prescription }}</p>
                                    </div>
                                @endif

                                @if($visit->consultation->notes)
                                    <div class="mb-3">
                                        <h6>Additional Notes:</h6>
                                        <p class="mb-0">{{ $visit->consultation->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="bx bx-info-circle me-2"></i>
                            Consultation not yet recorded for this visit.
                            <a href="{{ route('hospital.doctor.create', $visit->id) }}" class="btn btn-sm btn-primary ms-2">
                                <i class="bx bx-plus me-1"></i>Record Consultation
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
