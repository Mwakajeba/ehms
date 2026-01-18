@extends('layouts.main')

@section('title', 'Injection Dashboard')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Injection', 'url' => '#', 'icon' => 'bx bx-injection']
            ]" />
            <h6 class="mb-0 text-uppercase">INJECTION DASHBOARD</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Waiting</h6>
                                    <h4 class="mb-0">{{ $stats['waiting'] }}</h4>
                                </div>
                                <div class="text-warning" style="font-size: 2rem;">
                                    <i class="bx bx-time"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">In Service</h6>
                                    <h4 class="mb-0">{{ $stats['in_service'] }}</h4>
                                </div>
                                <div class="text-primary" style="font-size: 2rem;">
                                    <i class="bx bx-user-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Completed Today</h6>
                                    <h4 class="mb-0">{{ $stats['completed_today'] }}</h4>
                                </div>
                                <div class="text-success" style="font-size: 2rem;">
                                    <i class="bx bx-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Follow-up Required</h6>
                                    <h4 class="mb-0">{{ $stats['follow_up_required'] }}</h4>
                                </div>
                                <div class="text-info" style="font-size: 2rem;">
                                    <i class="bx bx-calendar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Waiting Patients -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-time me-2"></i>Waiting Patients
                                <span class="badge bg-warning ms-2">{{ $waitingVisits->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($waitingVisits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Visit #</th>
                                                <th>Patient</th>
                                                <th>MRN</th>
                                                <th>Age</th>
                                                <th>Waiting Time</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($waitingVisits as $visit)
                                                @php
                                                    $vaccinationDept = $visit->visitDepartments->firstWhere('department.type', 'vaccination');
                                                    $waitingTime = $vaccinationDept && $vaccinationDept->waiting_started_at 
                                                        ? $vaccinationDept->waiting_started_at->diffForHumans() 
                                                        : 'N/A';
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}</td>
                                                    <td>{{ $waitingTime }}</td>
                                                    <td>
                                                        <form action="{{ route('hospital.injection.start-service', $visit->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="bx bx-play me-1"></i>Start Service
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('hospital.injection.create', $visit->id) }}" class="btn btn-sm btn-danger">
                                                            <i class="bx bx-plus me-1"></i>Record Injection
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-check-circle text-success" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No patients waiting for injection services.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- In Service Patients -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user-check me-2"></i>In Service
                                <span class="badge bg-primary ms-2">{{ $inServiceVisits->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($inServiceVisits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Visit #</th>
                                                <th>Patient</th>
                                                <th>MRN</th>
                                                <th>Service Started</th>
                                                <th>Service Time</th>
                                                <th>Records</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($inServiceVisits as $visit)
                                                @php
                                                    $vaccinationDept = $visit->visitDepartments->firstWhere('department.type', 'vaccination');
                                                    $serviceTime = $vaccinationDept && $vaccinationDept->service_started_at 
                                                        ? $vaccinationDept->service_started_at->diffForHumans() 
                                                        : 'N/A';
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $vaccinationDept && $vaccinationDept->service_started_at ? $vaccinationDept->service_started_at->format('H:i') : 'N/A' }}</td>
                                                    <td>{{ $serviceTime }}</td>
                                                    <td>
                                                        <span class="badge bg-danger">{{ $visit->injectionRecords->count() }} record(s)</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hospital.injection.create', $visit->id) }}" class="btn btn-sm btn-danger">
                                                            <i class="bx bx-plus me-1"></i>Add Injection
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No patients currently in service.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Follow-up Required -->
            @if($followUpRequired->count() > 0)
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-calendar me-2"></i>Follow-up Required
                                    <span class="badge bg-info ms-2">{{ $followUpRequired->count() }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Record #</th>
                                                <th>Patient</th>
                                                <th>MRN</th>
                                                <th>Injection Type</th>
                                                <th>Next Appointment</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($followUpRequired as $record)
                                                <tr>
                                                    <td><strong>{{ $record->record_number }}</strong></td>
                                                    <td>{{ $record->patient->full_name }}</td>
                                                    <td>{{ $record->patient->mrn }}</td>
                                                    <td>{{ $record->injection_type }}</td>
                                                    <td>{{ $record->next_appointment_date ? $record->next_appointment_date->format('d M Y') : 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hospital.injection.show', $record->id) }}" class="btn btn-sm btn-danger">
                                                            <i class="bx bx-show me-1"></i>View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
