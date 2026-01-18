@extends('layouts.main')

@section('title', 'Injection Record Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Injection', 'url' => route('hospital.injection.index'), 'icon' => 'bx bx-injection'],
                ['label' => 'Record Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">INJECTION RECORD DETAILS</h6>
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
                        <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Patient Information
                            </h5>
                            <div>
                                @if($injectionRecord->status === 'pending')
                                    <form action="{{ route('hospital.injection.mark-completed', $injectionRecord->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bx bx-check me-1"></i>Mark as Completed
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('hospital.injection.index') }}" class="btn btn-sm btn-light">
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
                                            <td><strong>{{ $injectionRecord->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $injectionRecord->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $injectionRecord->patient->age ? $injectionRecord->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $injectionRecord->visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Record #:</th>
                                            <td><strong>{{ $injectionRecord->record_number }}</strong></td>
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
                                                    $statusColor = $statusColors[$injectionRecord->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $injectionRecord->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Performed By:</th>
                                            <td>{{ $injectionRecord->performedBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td>{{ $injectionRecord->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Injection Details -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-injection me-2"></i>Injection Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Item/Service:</th>
                                            <td><strong>{{ $injectionRecord->item->name ?? 'N/A' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Item Type:</th>
                                            <td>
                                                @if($injectionRecord->item)
                                                    <span class="badge bg-{{ $injectionRecord->item->item_type === 'service' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($injectionRecord->item->item_type) }}
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Injection Type:</th>
                                            <td><strong>{{ $injectionRecord->injection_type ?? 'N/A' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Medication Name:</th>
                                            <td>{{ $injectionRecord->medication_name ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Dosage:</th>
                                            <td>{{ $injectionRecord->dosage ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Injection Site:</th>
                                            <td>{{ $injectionRecord->site ?? 'N/A' }}</td>
                                        </tr>
                                        @if($injectionRecord->next_appointment_date)
                                            <tr>
                                                <th>Next Appointment:</th>
                                                <td>{{ $injectionRecord->next_appointment_date->format('d M Y') }}</td>
                                            </tr>
                                        @endif
                                        @if($injectionRecord->completed_at)
                                            <tr>
                                                <th>Completed At:</th>
                                                <td>{{ $injectionRecord->completed_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            @if($injectionRecord->injection_description)
                                <div class="mt-3">
                                    <h6>Description:</h6>
                                    <p class="text-muted">{{ $injectionRecord->injection_description }}</p>
                                </div>
                            @endif

                            @if($injectionRecord->notes)
                                <div class="mt-3">
                                    <h6>Notes:</h6>
                                    <p class="text-muted">{{ $injectionRecord->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
