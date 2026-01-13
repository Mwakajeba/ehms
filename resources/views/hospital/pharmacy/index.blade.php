@extends('layouts.main')

@section('title', 'Pharmacy Dashboard')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Pharmacy', 'url' => '#', 'icon' => 'bx bx-capsule']
            ]" />
            <h6 class="mb-0 text-uppercase">PHARMACY DASHBOARD</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(!$locationId)
                <div class="alert alert-warning">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Warning:</strong> No location selected. Stock tracking may not work correctly. Please select a location.
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
                    <div class="card border-info">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h6 class="text-muted mb-0">Pending Dispensations</h6>
                                    <h4 class="mb-0">{{ $stats['pending_dispensations'] }}</h4>
                                </div>
                                <div class="text-info" style="font-size: 2rem;">
                                    <i class="bx bx-capsule"></i>
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
                                    <h6 class="text-muted mb-0">Dispensed Today</h6>
                                    <h4 class="mb-0">{{ $stats['dispensed_today'] }}</h4>
                                </div>
                                <div class="text-success" style="font-size: 2rem;">
                                    <i class="bx bx-check-circle"></i>
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
                                                <th>Has Prescription</th>
                                                <th>Waiting Time</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($waitingVisits as $visit)
                                                @php
                                                    $pharmacyDept = $visit->visitDepartments->firstWhere('department.type', 'pharmacy');
                                                    $waitingTime = $pharmacyDept && $pharmacyDept->waiting_started_at 
                                                        ? $pharmacyDept->waiting_started_at->diffForHumans() 
                                                        : 'N/A';
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}</td>
                                                    <td>
                                                        @if($visit->consultation && $visit->consultation->prescription)
                                                            <span class="badge bg-success">Yes</span>
                                                        @else
                                                            <span class="badge bg-secondary">No</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $waitingTime }}</td>
                                                    <td>
                                                        <form action="{{ route('hospital.pharmacy.start-service', $visit->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="bx bx-play me-1"></i>Start Service
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('hospital.pharmacy.create', $visit->id) }}" class="btn btn-sm btn-info">
                                                            <i class="bx bx-plus me-1"></i>Create Dispensation
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
                                    <p class="text-muted mt-2">No patients waiting for pharmacy.</p>
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
                                                <th>Dispensations</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($inServiceVisits as $visit)
                                                @php
                                                    $pharmacyDept = $visit->visitDepartments->firstWhere('department.type', 'pharmacy');
                                                    $serviceTime = $pharmacyDept && $pharmacyDept->service_started_at 
                                                        ? $pharmacyDept->service_started_at->diffForHumans() 
                                                        : 'N/A';
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $pharmacyDept && $pharmacyDept->service_started_at ? $pharmacyDept->service_started_at->format('H:i') : 'N/A' }}</td>
                                                    <td>{{ $serviceTime }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $visit->pharmacyDispensations->count() }} dispensation(s)</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hospital.pharmacy.create', $visit->id) }}" class="btn btn-sm btn-info">
                                                            <i class="bx bx-plus me-1"></i>Add Dispensation
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

            <!-- Pending Dispensations -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-capsule me-2"></i>Pending Dispensations
                                <span class="badge bg-warning ms-2">{{ $pendingDispensations->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($pendingDispensations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Dispensation #</th>
                                                <th>Patient</th>
                                                <th>MRN</th>
                                                <th>Items</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingDispensations as $dispensation)
                                                <tr>
                                                    <td><strong>{{ $dispensation->dispensation_number }}</strong></td>
                                                    <td>{{ $dispensation->patient->full_name }}</td>
                                                    <td>{{ $dispensation->patient->mrn }}</td>
                                                    <td>{{ $dispensation->items->count() }} item(s)</td>
                                                    <td>
                                                        <span class="badge bg-warning">
                                                            {{ ucfirst($dispensation->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $dispensation->created_at->format('d M Y, H:i') }}</td>
                                                    <td>
                                                        <a href="{{ route('hospital.pharmacy.show', $dispensation->id) }}" class="btn btn-sm btn-info">
                                                            <i class="bx bx-show me-1"></i>View
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
                                    <p class="text-muted mt-2">No pending dispensations at the moment.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
