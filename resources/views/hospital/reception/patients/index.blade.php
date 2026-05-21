@extends('layouts.main')

@section('title', 'All Patients')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reception', 'url' => route('hospital.reception.index'), 'icon' => 'bx bx-user-plus'],
                ['label' => 'All Patients', 'url' => '#', 'icon' => 'bx bx-list-ul']
            ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">
                    <i class="bx bx-list-ul me-1"></i>All Patients
                </h6>
                <div class="d-flex gap-2">
                    <a href="{{ route('hospital.reception.patients.create') }}" class="btn btn-primary">
                        <i class="bx bx-user-plus me-1"></i>Register New Patient
                    </a>
                    <a href="{{ route('hospital.reception.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to Reception
                    </a>
                </div>
            </div>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="patientsTable" class="table table-striped table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>MRN</th>
                                    <th>Full Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Date of Birth</th>
                                    <th>Blood Group</th>
                                    <th>Insurance</th>
                                    <th>Visits</th>
                                    <th>Status</th>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('hospital.reception.patients.index') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'mrn', name: 'mrn' },
            { data: 'full_name', name: 'full_name' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'gender_display', name: 'gender', orderable: false },
            { data: 'age', name: 'age' },
            { data: 'date_of_birth_display', name: 'date_of_birth', orderable: false },
            { data: 'blood_group', name: 'blood_group' },
            { data: 'insurance_display', name: 'insurance_type', orderable: false },
            { data: 'visits_count', name: 'visits_count', searchable: false },
            { data: 'status', name: 'is_active', orderable: false },
            { data: 'registered_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
        language: {
            lengthMenu: 'Show _MENU_ entries',
            search: 'Search patients:',
            searchPlaceholder: 'MRN, name, phone, email...',
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>',
            emptyTable: 'No patients registered yet.',
            zeroRecords: 'No matching patients found.'
        },
        pageLength: 25,
        responsive: true,
        order: [[12, 'desc']]
    });
});
</script>
@endpush
