@extends('layouts.main')

@section('title', 'Doctor Dashboard')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Doctor', 'url' => '#', 'icon' => 'bx bx-user-md']
            ]" />
            <h6 class="mb-0 text-uppercase">DOCTOR DASHBOARD</h6>
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
                <div class="col-md-4">
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
                <div class="col-md-4">
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
                <div class="col-md-4">
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
            </div>

            <!-- Waiting Patients -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-time me-2"></i>Waiting Patients
                                <span class="badge bg-warning ms-2">{{ $stats['waiting'] }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="doctorWaitingTable" class="table table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Visit #</th>
                                            <th>Patient</th>
                                            <th>MRN</th>
                                            <th>Age</th>
                                            <th>Priority</th>
                                            <th>Waiting Time</th>
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

            <!-- In Service Patients -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user-check me-2"></i>In Service
                                <span class="badge bg-primary ms-2">{{ $stats['in_service'] }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="doctorInServiceTable" class="table table-hover w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Visit #</th>
                                            <th>Patient</th>
                                            <th>MRN</th>
                                            <th>Age</th>
                                            <th>Service Started</th>
                                            <th>Service Time</th>
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
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const dtLanguage = {
        lengthMenu: 'Show _MENU_ entries',
        search: '',
        searchPlaceholder: 'Search visit #, patient, MRN...',
        processing: '<div class="d-flex justify-content-center py-3"><div class="spinner-border text-primary" role="status"></div></div>',
        emptyTable: 'No records found.',
        zeroRecords: 'No matching records found.'
    };

    $('#doctorWaitingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('hospital.doctor.waiting-visits.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'visit_number', name: 'visit_number' },
            { data: 'patient_name', name: 'patient_name' },
            { data: 'mrn', name: 'mrn' },
            { data: 'age', name: 'age', orderable: false, searchable: false },
            { data: 'priority', name: 'priority', orderable: false, searchable: false },
            { data: 'waiting_time', name: 'waiting_time', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        dom: '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            ...dtLanguage,
            emptyTable: 'No patients waiting for consultation.',
            zeroRecords: 'No matching patients found.'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        order: [[1, 'asc']],
    });

    $('#doctorInServiceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('hospital.doctor.in-service-visits.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'visit_number', name: 'visit_number' },
            { data: 'patient_name', name: 'patient_name' },
            { data: 'mrn', name: 'mrn' },
            { data: 'age', name: 'age', orderable: false, searchable: false },
            { data: 'service_started', name: 'service_started', orderable: false, searchable: false },
            { data: 'service_time', name: 'service_time', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        dom: '<"row mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            ...dtLanguage,
            emptyTable: 'No patients currently in service.',
            zeroRecords: 'No matching patients found.'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        order: [[1, 'asc']],
    });
});
</script>
@endpush
