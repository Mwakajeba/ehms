@extends('layouts.main')

@section('title', 'Family Planning Record Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Family Planning', 'url' => route('hospital.family-planning.index'), 'icon' => 'bx bx-heart'],
                ['label' => 'Record Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">FAMILY PLANNING RECORD DETAILS</h6>
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
                                @if($familyPlanningRecord->status === 'pending')
                                    <form action="{{ route('hospital.family-planning.mark-completed', $familyPlanningRecord->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bx bx-check me-1"></i>Mark as Completed
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('hospital.family-planning.index') }}" class="btn btn-sm btn-light">
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
                                            <td><strong>{{ $familyPlanningRecord->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $familyPlanningRecord->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $familyPlanningRecord->patient->age ? $familyPlanningRecord->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $familyPlanningRecord->visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Record #:</th>
                                            <td><strong>{{ $familyPlanningRecord->record_number }}</strong></td>
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
                                                    $statusColor = $statusColors[$familyPlanningRecord->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $familyPlanningRecord->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Performed By:</th>
                                            <td>{{ $familyPlanningRecord->performedBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td>{{ $familyPlanningRecord->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
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
                                <i class="bx bx-heart me-2"></i>Family Planning Service Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Item/Service:</th>
                                            <td><strong>{{ $familyPlanningRecord->item->name ?? 'N/A' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Item Type:</th>
                                            <td>
                                                @if($familyPlanningRecord->item)
                                                    <span class="badge bg-{{ $familyPlanningRecord->item->item_type === 'service' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($familyPlanningRecord->item->item_type) }}
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Service Type:</th>
                                            <td><strong>{{ $familyPlanningRecord->service_type ?? 'N/A' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Method Provided:</th>
                                            <td>{{ $familyPlanningRecord->method_provided ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        @if($familyPlanningRecord->service_date)
                                            <tr>
                                                <th width="40%">Service Date:</th>
                                                <td>{{ $familyPlanningRecord->service_date->format('d M Y') }}</td>
                                            </tr>
                                        @endif
                                        @if($familyPlanningRecord->next_appointment_date)
                                            <tr>
                                                <th>Next Appointment:</th>
                                                <td>{{ $familyPlanningRecord->next_appointment_date->format('d M Y') }}</td>
                                            </tr>
                                        @endif
                                        @if($familyPlanningRecord->completed_at)
                                            <tr>
                                                <th>Completed At:</th>
                                                <td>{{ $familyPlanningRecord->completed_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            @if($familyPlanningRecord->counseling_notes)
                                <div class="mt-3">
                                    <h6>Counseling Notes:</h6>
                                    <p class="text-muted">{{ $familyPlanningRecord->counseling_notes }}</p>
                                </div>
                            @endif

                            @if($familyPlanningRecord->medical_history)
                                <div class="mt-3">
                                    <h6>Medical History:</h6>
                                    <p class="text-muted">{{ $familyPlanningRecord->medical_history }}</p>
                                </div>
                            @endif

                            @if($familyPlanningRecord->contraindications)
                                <div class="mt-3">
                                    <h6>Contraindications:</h6>
                                    <p class="text-muted">{{ $familyPlanningRecord->contraindications }}</p>
                                </div>
                            @endif

                            @if($familyPlanningRecord->notes)
                                <div class="mt-3">
                                    <h6>Notes:</h6>
                                    <p class="text-muted">{{ $familyPlanningRecord->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
