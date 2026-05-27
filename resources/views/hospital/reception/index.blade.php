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
                                <a href="{{ route('hospital.reception.patients.index') }}" class="btn btn-success">
                                    <i class="bx bx-list-ul me-1"></i>All Patients (full list)
                                </a>
                                <a href="{{ route('hospital.reception.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-refresh me-1"></i>Refresh
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient search (Ajax DataTable) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-search me-2"></i>Find Patient
                            </h5>
                            <small class="text-muted">Search by MRN, name, phone, or email</small>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="receptionPatientsTable" class="table table-striped table-hover w-100">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>MRN</th>
                                            <th>Full Name</th>
                                            <th>Phone</th>
                                            <th>Insurance</th>
                                            <th>Visits</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
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

@push('scripts')
<script>
$(document).ready(function() {
    $('#receptionPatientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('hospital.reception.patients.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'mrn', name: 'mrn' },
            { data: 'full_name', name: 'full_name' },
            { data: 'phone', name: 'phone' },
            { data: 'insurance_display', name: 'insurance_type', orderable: false },
            { data: 'visits_count', name: 'visits_count', searchable: false },
            { data: 'registered_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        dom: '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            lengthMenu: 'Show _MENU_ patients',
            search: '',
            searchPlaceholder: 'Search MRN, name, phone, email...',
            processing: '<div class="d-flex justify-content-center py-3"><div class="spinner-border text-primary" role="status"></div></div>',
            emptyTable: 'No patients registered yet.',
            zeroRecords: 'No matching patients found.'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        order: [[6, 'desc']]
    });
});
</script>
@endpush
@endsection
