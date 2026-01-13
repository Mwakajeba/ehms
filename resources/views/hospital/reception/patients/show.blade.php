@extends('layouts.main')

@section('title', 'Patient Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reception', 'url' => route('hospital.reception.index'), 'icon' => 'bx bx-user-plus'],
                ['label' => 'Patient Details', 'url' => '#', 'icon' => 'bx bx-user']
            ]" />
            <h6 class="mb-0 text-uppercase">PATIENT DETAILS</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Patient Information Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Patient Information
                            </h5>
                            <div>
                                <a href="{{ route('hospital.reception.patients.edit', $patient->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-edit me-1"></i>Edit
                                </a>
                                <a href="{{ route('hospital.reception.visits.create', $patient->id) }}" class="btn btn-sm btn-success">
                                    <i class="bx bx-plus me-1"></i>Create Visit
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">MRN:</th>
                                            <td><strong class="text-primary">{{ $patient->mrn }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Full Name:</th>
                                            <td>{{ $patient->full_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date of Birth:</th>
                                            <td>{{ $patient->date_of_birth ? $patient->date_of_birth->format('d M Y') : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $patient->age ? $patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Gender:</th>
                                            <td>{{ ucfirst($patient->gender ?? 'N/A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Blood Group:</th>
                                            <td>{{ $patient->blood_group ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Phone:</th>
                                            <td>{{ $patient->phone ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td>{{ $patient->email ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Address:</th>
                                            <td>{{ $patient->address ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>ID Number:</th>
                                            <td>{{ $patient->id_number ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Insurance Type:</th>
                                            <td>
                                                @if($patient->insurance_type && $patient->insurance_type !== 'None')
                                                    <span class="badge bg-info">{{ $patient->insurance_type }}</span>
                                                @else
                                                    None
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Insurance Number:</th>
                                            <td>{{ $patient->insurance_number ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next of Kin Information -->
                @if($patient->next_of_kin_name || $patient->next_of_kin_phone)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-user-circle me-2"></i>Next of Kin
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Name:</th>
                                        <td>{{ $patient->next_of_kin_name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td>{{ $patient->next_of_kin_phone ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Relationship:</th>
                                        <td>{{ $patient->next_of_kin_relationship ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Medical Information -->
                @if($patient->medical_history || $patient->allergies)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-heart me-2"></i>Medical Information
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($patient->medical_history)
                                    <div class="mb-3">
                                        <strong>Medical History:</strong>
                                        <p class="mb-0">{{ $patient->medical_history }}</p>
                                    </div>
                                @endif
                                @if($patient->allergies)
                                    <div>
                                        <strong>Allergies:</strong>
                                        <p class="mb-0 text-danger">{{ $patient->allergies }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Visit History -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-list-ul me-2"></i>Visit History
                                <span class="badge bg-primary ms-2">{{ $patient->visits->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($patient->visits->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Visit #</th>
                                                <th>Visit Date</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Chief Complaint</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($patient->visits->sortByDesc('visit_date') as $visit)
                                                <tr>
                                                    <td>{{ $visit->visit_number }}</td>
                                                    <td>{{ $visit->visit_date->format('d M Y, H:i') }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}</span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusColors = [
                                                                'pending' => 'warning',
                                                                'in_progress' => 'primary',
                                                                'completed' => 'success',
                                                                'cancelled' => 'danger'
                                                            ];
                                                            $color = $statusColors[$visit->status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $visit->status)) }}</span>
                                                    </td>
                                                    <td>{{ $visit->chief_complaint ?? 'N/A' }}</td>
                                                    <td>
                                                        <a href="{{ route('hospital.reception.visits.show', $visit->id) }}" class="btn btn-sm btn-info">
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
                                    <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No visits recorded for this patient.</p>
                                    <a href="{{ route('hospital.reception.visits.create', $patient->id) }}" class="btn btn-primary">
                                        <i class="bx bx-plus me-1"></i>Create First Visit
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        <i class="bx bx-calendar me-1"></i>
                                        Registered: {{ $patient->created_at->format('d M Y, H:i') }}
                                        @if($patient->updated_at != $patient->created_at)
                                            | Last Updated: {{ $patient->updated_at->format('d M Y, H:i') }}
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    <a href="{{ route('hospital.reception.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i>Back to Reception
                                    </a>
                                    <a href="{{ route('hospital.reception.patients.edit', $patient->id) }}" class="btn btn-primary">
                                        <i class="bx bx-edit me-1"></i>Edit Patient
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteRequestModal">
                                        <i class="bx bx-trash me-1"></i>Request Deletion
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Request Modal -->
    <div class="modal fade" id="deleteRequestModal" tabindex="-1" aria-labelledby="deleteRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('hospital.reception.patients.request-deletion', $patient->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteRequestModalLabel">Request Patient Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="bx bx-error-circle me-2"></i>
                            <strong>Note:</strong> Patient deletion requires supervisor/admin approval. This action cannot be undone once approved.
                        </div>
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Deletion <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" 
                                      id="reason" name="reason" rows="4" required 
                                      placeholder="Please provide a detailed reason for requesting patient deletion (minimum 10 characters)">{{ old('reason') }}</textarea>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Deletion Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
