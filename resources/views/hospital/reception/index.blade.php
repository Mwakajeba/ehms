@extends('layouts.main')

@section('title', 'Reception Dashboard')

@push('styles')
<style>
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .status-waiting { background-color: #ffc107; color: #000; }
    .status-in-service { background-color: #0d6efd; color: #fff; }
    .status-completed { background-color: #198754; color: #fff; }
    .patient-card {
        transition: transform 0.2s ease-in-out;
    }
    .patient-card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reception', 'url' => '#', 'icon' => 'bx bx-user-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECEPTION DASHBOARD</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('hospital.reception.patients.create') }}" class="btn btn-primary">
                                    <i class="bx bx-user-plus me-1"></i>Register New Patient
                                </a>
                                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#searchPatientModal">
                                    <i class="bx bx-search me-1"></i>Search Patient
                                </button>
                                <a href="{{ route('hospital.reception.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh me-1"></i>Refresh
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Active Visits -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>Active Visits
                                <span class="badge bg-primary ms-2">{{ $activeVisits->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($activeVisits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Visit #</th>
                                                <th>Patient</th>
                                                <th>MRN</th>
                                                <th>Phone</th>
                                                <th>Current Department</th>
                                                <th>Status</th>
                                                <th>Waiting Time</th>
                                                <th>Service Time</th>
                                                <th>Start Time</th>
                                                <th>Visit Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($activeVisits as $visit)
                                                @php
                                                    $currentDept = $visit->visitDepartments()
                                                        ->whereIn('status', ['waiting', 'in_service'])
                                                        ->orderBy('sequence')
                                                        ->first();
                                                    
                                                    // Calculate total time in hospital
                                                    $totalTime = 0;
                                                    foreach ($visit->visitDepartments as $vd) {
                                                        if ($vd->waiting_time_seconds) {
                                                            $totalTime += $vd->waiting_time_seconds;
                                                        }
                                                        if ($vd->service_time_seconds) {
                                                            $totalTime += $vd->service_time_seconds;
                                                        }
                                                    }
                                                    $totalHours = floor($totalTime / 3600);
                                                    $totalMinutes = floor(($totalTime % 3600) / 60);
                                                    $totalSeconds = $totalTime % 60;
                                                @endphp
                                                <tr>
                                                    <td><strong>{{ $visit->visit_number }}</strong></td>
                                                    <td>{{ $visit->patient->full_name }}</td>
                                                    <td>{{ $visit->patient->mrn }}</td>
                                                    <td>{{ $visit->patient->phone ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($currentDept)
                                                            <span class="badge bg-info">{{ $currentDept->department->name ?? 'N/A' }}</span>
                                                            <br><small class="text-muted">{{ ucfirst(str_replace('_', ' ', $currentDept->department->type ?? '')) }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-{{ str_replace('_', '-', $currentDept->status ?? 'waiting') }}">
                                                            {{ ucfirst(str_replace('_', ' ', $currentDept->status ?? 'waiting')) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($currentDept)
                                                            <span class="text-warning">
                                                                <i class="bx bx-time"></i> {{ $currentDept->waiting_time_formatted ?? '00:00:00' }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($currentDept && $currentDept->service_started_at)
                                                            <span class="text-primary">
                                                                <i class="bx bx-time-five"></i> {{ $currentDept->service_time_formatted ?? '00:00:00' }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($currentDept && $currentDept->service_started_at)
                                                            <small>{{ $currentDept->service_started_at->format('H:i') }}</small>
                                                        @elseif($currentDept && $currentDept->waiting_started_at)
                                                            <small class="text-muted">{{ $currentDept->waiting_started_at->format('H:i') }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $visit->visit_date->format('d M Y, H:i') }}</td>
                                                    <td>
                                                        <a href="{{ route('hospital.reception.visits.show', $visit->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                            <i class="bx bx-show"></i>
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
                                    <p class="text-muted mt-2">No active visits at the moment.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Waiting Patients by Department -->
                @if($waitingByDepartment->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-time-five me-2"></i>Waiting Patients by Department
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($waitingByDepartment as $deptType => $visits)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card border-warning">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        {{ ucfirst(str_replace('_', ' ', $deptType)) }}
                                                        <span class="badge bg-warning ms-2">{{ $visits->count() }}</span>
                                                    </h6>
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach($visits->take(5) as $visitDept)
                                                            <li class="mb-2">
                                                                <small>
                                                                    <strong>{{ $visitDept->visit->patient->full_name }}</strong>
                                                                    <br>
                                                                    <span class="text-muted">{{ $visitDept->visit->patient->mrn }}</span>
                                                                </small>
                                                            </li>
                                                        @endforeach
                                                        @if($visits->count() > 5)
                                                            <li class="text-muted">
                                                                <small>+ {{ $visits->count() - 5 }} more</small>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- In-Service Patients by Department -->
                @if($inServiceByDepartment->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-user-check me-2"></i>In-Service Patients by Department
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($inServiceByDepartment as $deptType => $visits)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h6 class="card-title">
                                                        {{ ucfirst(str_replace('_', ' ', $deptType)) }}
                                                        <span class="badge bg-primary ms-2">{{ $visits->count() }}</span>
                                                    </h6>
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach($visits->take(5) as $visitDept)
                                                            <li class="mb-2">
                                                                <small>
                                                                    <strong>{{ $visitDept->visit->patient->full_name }}</strong>
                                                                    <br>
                                                                    <span class="text-muted">{{ $visitDept->visit->patient->mrn }}</span>
                                                                </small>
                                                            </li>
                                                        @endforeach
                                                        @if($visits->count() > 5)
                                                            <li class="text-muted">
                                                                <small>+ {{ $visits->count() - 5 }} more</small>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Search Patient Modal -->
    <div class="modal fade" id="searchPatientModal" tabindex="-1" aria-labelledby="searchPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchPatientModalLabel">Search Patient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="searchTerm" class="form-label">Search by MRN, Name, or Phone</label>
                        <input type="text" class="form-control" id="searchTerm" placeholder="Enter MRN, name, or phone number">
                    </div>
                    <div id="searchResults" class="mt-3">
                        <!-- Results will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.getElementById('searchTerm').addEventListener('input', function(e) {
        const term = e.target.value;
        const resultsDiv = document.getElementById('searchResults');
        
        if (term.length < 2) {
            resultsDiv.innerHTML = '';
            return;
        }
        
        fetch(`{{ route('hospital.reception.patients.search') }}?term=${encodeURIComponent(term)}`)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    let html = '<div class="list-group">';
                    data.forEach(patient => {
                        html += `
                            <a href="{{ url('hospital/reception/patients') }}/${patient.id}" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${patient.first_name} ${patient.last_name}</h6>
                                    <small>${patient.mrn}</small>
                                </div>
                                <p class="mb-1">${patient.phone || 'No phone'}</p>
                            </a>
                        `;
                    });
                    html += '</div>';
                    resultsDiv.innerHTML = html;
                } else {
                    resultsDiv.innerHTML = '<p class="text-muted">No patients found.</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                resultsDiv.innerHTML = '<p class="text-danger">Error searching patients.</p>';
            });
    });
</script>
@endpush
@endsection
