@extends('layouts.main')

@section('title', 'Lab Dashboard')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Lab', 'url' => '#', 'icon' => 'bx bx-test-tube']
            ]" />
            <h6 class="mb-0 text-uppercase">LAB DASHBOARD</h6>
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
                                    <h6 class="text-muted mb-0">Ready Results</h6>
                                    <h4 class="mb-0">{{ $stats['ready_results'] }}</h4>
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
                                    <h6 class="text-muted mb-0">Completed Today</h6>
                                    <h4 class="mb-0">{{ $stats['completed_today'] }}</h4>
                                </div>
                                <div class="text-info" style="font-size: 2rem;">
                                    <i class="bx bx-test-tube"></i>
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
                                                    $labDept = $visit->visitDepartments->firstWhere('department.type', 'lab');
                                                    $waitingTime = $labDept && $labDept->waiting_started_at 
                                                        ? $labDept->waiting_started_at->diffForHumans() 
                                                        : 'N/A';
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}</td>
                                                    <td>{{ $waitingTime }}</td>
                                                    <td>
                                                        <form action="{{ route('hospital.lab.start-service', $visit->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary">
                                                                <i class="bx bx-play me-1"></i>Start Service
                                                            </button>
                                                        </form>
                                                        <a href="{{ route('hospital.lab.create', $visit->id) }}" class="btn btn-sm btn-info">
                                                            <i class="bx bx-plus me-1"></i>Record Test
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
                                    <p class="text-muted mt-2">No patients waiting for lab tests.</p>
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
                                                <th>Tests</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($inServiceVisits as $visit)
                                                @php
                                                    $labDept = $visit->visitDepartments->firstWhere('department.type', 'lab');
                                                    $serviceTime = $labDept && $labDept->service_started_at 
                                                        ? $labDept->service_started_at->diffForHumans() 
                                                        : 'N/A';
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $labDept && $labDept->service_started_at ? $labDept->service_started_at->format('H:i') : 'N/A' }}</td>
                                                    <td>{{ $serviceTime }}</td>
                                                    <td>
                                                        @php
                                                            $testsCount = $visit->lab_tests_count ?? 0;
                                                            $resultsCount = $visit->labResults->count();
                                                        @endphp
                                                        <span class="badge bg-info">{{ $testsCount }} test(s)</span>
                                                        @if($resultsCount > 0)
                                                            <br><small class="text-muted">{{ $resultsCount }} result(s) recorded</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('hospital.lab.create', $visit->id) }}" class="btn btn-sm btn-info">
                                                            <i class="bx bx-edit me-1"></i>Add Results
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

            <!-- Ready Results -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-check-circle me-2"></i>Ready Results
                                <span class="badge bg-success ms-2">{{ $readyResults->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($readyResults->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Result #</th>
                                                <th>Patient</th>
                                                <th>MRN</th>
                                                <th>Test Name</th>
                                                <th>Result</th>
                                                <th>Status</th>
                                                <th>Completed</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($readyResults as $result)
                                                <tr>
                                                    <td><strong>{{ $result->result_number }}</strong></td>
                                                    <td>{{ $result->patient->full_name }}</td>
                                                    <td>{{ $result->patient->mrn }}</td>
                                                    <td>{{ $result->test_name }}</td>
                                                    <td>
                                                        {{ $result->result_value }} 
                                                        @if($result->unit) <small class="text-muted">{{ $result->unit }}</small> @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'normal' => 'success',
                                                                'abnormal' => 'warning',
                                                                'critical' => 'danger'
                                                            ];
                                                            $statusColor = $statusColors[$result->status] ?? 'secondary';
                                                        @endphp
                                                        @if($result->status)
                                                            <span class="badge bg-{{ $statusColor }}">
                                                                {{ ucfirst($result->status) }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $result->completed_at ? $result->completed_at->format('d M Y, H:i') : 'N/A' }}</td>
                                                    <td>
                                                        <a href="{{ route('hospital.lab.show', $result->id) }}" class="btn btn-sm btn-info">
                                                            <i class="bx bx-show me-1"></i>View
                                                        </a>
                                                        <a href="{{ route('hospital.lab.print', $result->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                                            <i class="bx bx-printer me-1"></i>Print
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
                                    <p class="text-muted mt-2">No ready results at the moment.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
