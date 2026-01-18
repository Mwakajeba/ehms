@extends('layouts.main')

@section('title', 'Vaccination Record Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Vaccination', 'url' => route('hospital.vaccination.index'), 'icon' => 'bx bx-shield'],
                ['label' => 'Record Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">VACCINATION RECORD DETAILS</h6>
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
                        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Patient Information
                            </h5>
                            <div>
                                @if($vaccinationRecord->status === 'pending')
                                    <form action="{{ route('hospital.vaccination.mark-completed', $vaccinationRecord->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bx bx-check me-1"></i>Mark as Completed
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('hospital.vaccination.index') }}" class="btn btn-sm btn-light">
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
                                            <td><strong>{{ $vaccinationRecord->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $vaccinationRecord->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $vaccinationRecord->patient->age ? $vaccinationRecord->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $vaccinationRecord->visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Record #:</th>
                                            <td><strong>{{ $vaccinationRecord->record_number }}</strong></td>
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
                                                    $statusColor = $statusColors[$vaccinationRecord->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $vaccinationRecord->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Performed By:</th>
                                            <td>{{ $vaccinationRecord->performedBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td>{{ $vaccinationRecord->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vaccination Details -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-shield me-2"></i>Vaccination Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Item/Service:</th>
                                            <td><strong>{{ $vaccinationRecord->item->name ?? 'N/A' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Item Type:</th>
                                            <td>
                                                @if($vaccinationRecord->item)
                                                    <span class="badge bg-{{ $vaccinationRecord->item->item_type === 'service' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($vaccinationRecord->item->item_type) }}
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Vaccine Type:</th>
                                            <td><strong>{{ $vaccinationRecord->vaccine_type ?? 'N/A' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Vaccine Name:</th>
                                            <td>{{ $vaccinationRecord->vaccine_name ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Dosage:</th>
                                            <td>{{ $vaccinationRecord->dosage ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Vaccination Site:</th>
                                            <td>{{ $vaccinationRecord->site ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Batch Number:</th>
                                            <td>{{ $vaccinationRecord->batch_number ?? 'N/A' }}</td>
                                        </tr>
                                        @if($vaccinationRecord->vaccination_date)
                                            <tr>
                                                <th>Vaccination Date:</th>
                                                <td>{{ $vaccinationRecord->vaccination_date->format('d M Y') }}</td>
                                            </tr>
                                        @endif
                                        @if($vaccinationRecord->next_appointment_date)
                                            <tr>
                                                <th>Next Appointment:</th>
                                                <td>{{ $vaccinationRecord->next_appointment_date->format('d M Y') }}</td>
                                            </tr>
                                        @endif
                                        @if($vaccinationRecord->completed_at)
                                            <tr>
                                                <th>Completed At:</th>
                                                <td>{{ $vaccinationRecord->completed_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            @if($vaccinationRecord->vaccination_description)
                                <div class="mt-3">
                                    <h6>Description:</h6>
                                    <p class="text-muted">{{ $vaccinationRecord->vaccination_description }}</p>
                                </div>
                            @endif

                            @if($vaccinationRecord->notes)
                                <div class="mt-3">
                                    <h6>Notes:</h6>
                                    <p class="text-muted">{{ $vaccinationRecord->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
