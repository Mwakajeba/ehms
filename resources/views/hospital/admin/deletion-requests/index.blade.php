@extends('layouts.main')

@section('title', 'Patient Deletion Requests')

@push('styles')
<style>
    .filter-badge {
        cursor: pointer;
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Hospital Admin', 'url' => route('hospital.admin.index'), 'icon' => 'bx bx-cog'],
                ['label' => 'Deletion Requests', 'url' => '#', 'icon' => 'bx bx-trash']
            ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">
                    <i class="bx bx-trash me-1"></i>Patient Deletion Requests
                </h6>
                <a href="{{ route('hospital.admin.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Back to Admin
                </a>
            </div>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Filter Badges -->
            <div class="mb-3">
                <span class="badge filter-badge bg-primary me-2" data-status="">All</span>
                <span class="badge filter-badge bg-warning me-2" data-status="pending">Pending</span>
                <span class="badge filter-badge bg-success me-2" data-status="approved">Approved</span>
                <span class="badge filter-badge bg-danger me-2" data-status="rejected">Rejected</span>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="deletionRequestsTable" class="table table-striped table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Patient Name</th>
                                    <th>MRN</th>
                                    <th>Reason</th>
                                    <th>Initiated By</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
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
        let table = $('#deletionRequestsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('hospital.admin.deletion-requests.index') }}",
                data: function(d) {
                    d.status = $('.filter-badge.active').data('status') || '';
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'patient_name', name: 'patient_name' },
                { data: 'patient_mrn', name: 'patient_mrn' },
                { data: 'reason', name: 'reason' },
                { data: 'initiator_name', name: 'initiator_name' },
                { data: 'status_badge', name: 'status' },
                { data: 'created_at_formatted', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[6, 'desc']]
        });

        // Filter badges
        $('.filter-badge').on('click', function() {
            $('.filter-badge').removeClass('active');
            $(this).addClass('active');
            table.ajax.reload();
        });

        // Set default filter
        $('.filter-badge[data-status="pending"]').addClass('active');

        // Approve button
        $(document).on('click', '.approve-btn', function() {
            let requestId = $(this).data('id');
            
            Swal.fire({
                title: 'Approve Deletion Request?',
                text: 'This will permanently delete the patient. This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Approve',
                cancelButtonText: 'Cancel',
                input: 'textarea',
                inputPlaceholder: 'Approval notes (optional)',
                inputAttributes: {
                    maxlength: 1000
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('hospital.admin.deletion-requests.approve', ':id') }}".replace(':id', requestId),
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            approval_notes: result.value || ''
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Approved!', response.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let message = xhr.responseJSON?.message || 'Failed to approve request';
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                }
            });
        });

        // Reject button
        $(document).on('click', '.reject-btn', function() {
            let requestId = $(this).data('id');
            
            Swal.fire({
                title: 'Reject Deletion Request?',
                text: 'Please provide a reason for rejection',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Reject',
                cancelButtonText: 'Cancel',
                input: 'textarea',
                inputPlaceholder: 'Rejection reason (required, min 10 characters)',
                inputAttributes: {
                    required: true,
                    minlength: 10,
                    maxlength: 1000
                },
                inputValidator: (value) => {
                    if (!value || value.length < 10) {
                        return 'Please provide a reason with at least 10 characters';
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('hospital.admin.deletion-requests.reject', ':id') }}".replace(':id', requestId),
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            approval_notes: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Rejected!', response.message, 'success');
                                table.ajax.reload();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let message = xhr.responseJSON?.message || 'Failed to reject request';
                            Swal.fire('Error!', message, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
