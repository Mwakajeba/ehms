@extends('layouts.main')

@section('title', 'Family Planning Dashboard')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Family Planning', 'url' => '#', 'icon' => 'bx bx-heart']
            ]" />
            <h6 class="mb-0 text-uppercase">FAMILY PLANNING DASHBOARD</h6>
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
                                                    $fpDept = $visit->visitDepartments->firstWhere('department.type', 'family_planning');
                                                    $waitingTime = $fpDept && $fpDept->waiting_started_at 
                                                        ? $fpDept->waiting_started_at->diffForHumans() 
                                                        : 'N/A';
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}</td>
                                                    <td>{{ $waitingTime }}</td>
                                                    <td>
                                                        <form action="{{ route('hospital.family-planning.start-service', $visit->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="bx bx-play me-1"></i>Start Service
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('hospital.family-planning.create', $visit->id) }}" class="btn btn-sm btn-warning">
                                                            <i class="bx bx-plus me-1"></i>Record Service
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
                                    <p class="text-muted mt-2">No patients waiting for family planning services.</p>
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
                                                    $fpDept = $visit->visitDepartments->firstWhere('department.type', 'family_planning');
                                                    $serviceTime = $fpDept && $fpDept->service_started_at 
                                                        ? $fpDept->service_started_at->diffForHumans() 
                                                        : 'N/A';
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $fpDept && $fpDept->service_started_at ? $fpDept->service_started_at->format('H:i') : 'N/A' }}</td>
                                                    <td>{{ $serviceTime }}</td>
                                                    <td>
                                                        <span class="badge bg-warning">{{ $visit->familyPlanningRecords->count() }} record(s)</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hospital.family-planning.create', $visit->id) }}" class="btn btn-sm btn-warning">
                                                            <i class="bx bx-plus me-1"></i>Add Service
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
                                                <th>Service Type</th>
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
                                                    <td>{{ $record->service_type }}</td>
                                                    <td>{{ $record->next_appointment_date ? $record->next_appointment_date->format('d M Y') : 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hospital.family-planning.show', $record->id) }}" class="btn btn-sm btn-warning">
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
