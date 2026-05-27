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
                                <span class="badge bg-primary ms-2">{{ $activeVisitsCount ?? 0 }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="activeVisitsTable" class="table table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
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
                                    <tbody></tbody>
                                </table>
                            </div>
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
    $('#activeVisitsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('hospital.reception.active-visits.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'visit_number', name: 'visit_number' },
            { data: 'patient_name', name: 'patient_name' },
            { data: 'mrn', name: 'mrn' },
            { data: 'phone', name: 'phone' },
            { data: 'current_department', name: 'current_department', orderable: false, searchable: false },
            { data: 'dept_status', name: 'dept_status', orderable: false, searchable: false },
            { data: 'waiting_time', name: 'waiting_time', orderable: false, searchable: false },
            { data: 'service_time', name: 'service_time', orderable: false, searchable: false },
            { data: 'start_time', name: 'start_time', orderable: false, searchable: false },
            { data: 'visit_date', name: 'visit_date' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        dom: '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            lengthMenu: 'Show _MENU_ visits',
            search: '',
            searchPlaceholder: 'Search visit #, patient, MRN, phone...',
            processing: '<div class="d-flex justify-content-center py-3"><div class="spinner-border text-primary" role="status"></div></div>',
            emptyTable: 'No active visits at the moment.',
            zeroRecords: 'No matching visits found.'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        order: [[10, 'desc']],
    });

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
