@extends('layouts.main')

@section('title', 'Deletion Request Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Hospital Admin', 'url' => route('hospital.admin.index'), 'icon' => 'bx bx-cog'],
                ['label' => 'Deletion Requests', 'url' => route('hospital.admin.deletion-requests.index'), 'icon' => 'bx bx-trash'],
                ['label' => 'Request Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 text-uppercase">
                    <i class="bx bx-show me-1"></i>Deletion Request Details
                </h6>
                <a href="{{ route('hospital.admin.deletion-requests.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Back to Requests
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

            <div class="row">
                <div class="col-md-8">
                    <!-- Request Information -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-info-circle me-2"></i>Request Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Status:</div>
                                <div class="col-md-8">
                                    @if($request->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($request->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($request->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($request->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Requested At:</div>
                                <div class="col-md-8">{{ $request->created_at->format('Y-m-d H:i:s') }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Initiated By:</div>
                                <div class="col-md-8">{{ $request->initiator->name ?? 'N/A' }}</div>
                            </div>
                            @if($request->status !== 'pending')
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Processed By:</div>
                                    <div class="col-md-8">{{ $request->approver->name ?? 'N/A' }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Processed At:</div>
                                    <div class="col-md-8">{{ $request->approved_at ? $request->approved_at->format('Y-m-d H:i:s') : 'N/A' }}</div>
                                </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Reason for Deletion:</div>
                                <div class="col-md-8">
                                    <div class="border p-3 rounded bg-light">
                                        {{ $request->reason }}
                                    </div>
                                </div>
                            </div>
                            @if($request->approval_notes)
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Approval Notes:</div>
                                    <div class="col-md-8">
                                        <div class="border p-3 rounded bg-light">
                                            {{ $request->approval_notes }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Patient Information -->
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-user me-2"></i>Patient Information
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($request->patient)
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Name:</div>
                                    <div class="col-md-8">{{ $request->patient->first_name }} {{ $request->patient->last_name }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">MRN:</div>
                                    <div class="col-md-8">{{ $request->patient->mrn }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Date of Birth:</div>
                                    <div class="col-md-8">{{ $request->patient->date_of_birth ? \Carbon\Carbon::parse($request->patient->date_of_birth)->format('Y-m-d') : 'N/A' }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Gender:</div>
                                    <div class="col-md-8">{{ ucfirst($request->patient->gender ?? 'N/A') }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Phone:</div>
                                    <div class="col-md-8">{{ $request->patient->phone ?? 'N/A' }}</div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4 fw-bold">Email:</div>
                                    <div class="col-md-8">{{ $request->patient->email ?? 'N/A' }}</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 fw-bold">Address:</div>
                                    <div class="col-md-8">{{ $request->patient->address ?? 'N/A' }}</div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bx bx-error-circle me-2"></i>Patient information not available. Patient may have already been deleted.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Actions -->
                    @if($request->status === 'pending')
                        <div class="card">
                            <div class="card-header bg-warning text-white">
                                <h5 class="mb-0">
                                    <i class="bx bx-cog me-2"></i>Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <button class="btn btn-success w-100 mb-2 approve-btn" data-id="{{ $request->id }}">
                                    <i class="bx bx-check me-1"></i>Approve Request
                                </button>
                                <button class="btn btn-danger w-100 reject-btn" data-id="{{ $request->id }}">
                                    <i class="bx bx-x me-1"></i>Reject Request
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Company & Branch Info -->
                    <div class="card mt-3">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="bx bx-building me-2"></i>Organization
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Company:</strong><br>
                                {{ $request->company->name ?? 'N/A' }}
                            </div>
                            <div>
                                <strong>Branch:</strong><br>
                                {{ $request->branch->name ?? 'N/A' }}
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
        // Approve button
        $('.approve-btn').on('click', function() {
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
                                Swal.fire('Approved!', response.message, 'success').then(() => {
                                    window.location.reload();
                                });
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
        $('.reject-btn').on('click', function() {
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
                                Swal.fire('Rejected!', response.message, 'success').then(() => {
                                    window.location.reload();
                                });
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
