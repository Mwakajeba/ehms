@extends('layouts.main')

@section('title', 'Dental Record Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Dental', 'url' => route('hospital.dental.index'), 'icon' => 'bx bx-smile'],
                ['label' => 'Record Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">DENTAL RECORD DETAILS</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Patient Info -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Patient Information
                            </h5>
                            <div>
                                @if($dentalRecord->status === 'pending')
                                    <form action="{{ route('hospital.dental.mark-completed', $dentalRecord->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bx bx-check me-1"></i>Mark as Completed
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('hospital.dental.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Patient:</th>
                                            <td><strong>{{ $dentalRecord->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $dentalRecord->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $dentalRecord->patient->age ? $dentalRecord->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $dentalRecord->visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Record #:</th>
                                            <td><strong>{{ $dentalRecord->record_number }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'completed' => 'success',
                                                        'follow_up_required' => 'info'
                                                    ];
                                                    $statusColor = $statusColors[$dentalRecord->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $dentalRecord->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Performed By:</th>
                                            <td>{{ $dentalRecord->performedBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td>{{ $dentalRecord->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Procedure Details -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-smile me-2"></i>Procedure Details
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6>Procedure Type:</h6>
                                    <p class="mb-0"><strong>{{ $dentalRecord->procedure_type }}</strong></p>
                                </div>
                            </div>

                            @if($dentalRecord->procedure_description)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Procedure Description:</h6>
                                        <p class="mb-0">{{ $dentalRecord->procedure_description }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($dentalRecord->findings)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Examination Findings:</h6>
                                        <p class="mb-0">{{ $dentalRecord->findings }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($dentalRecord->treatment_plan)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Treatment Plan:</h6>
                                        <p class="mb-0">{{ $dentalRecord->treatment_plan }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($dentalRecord->treatment_performed)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Treatment Performed:</h6>
                                        <p class="mb-0"><strong class="text-primary">{{ $dentalRecord->treatment_performed }}</strong></p>
                                    </div>
                                </div>
                            @endif

                            @if($dentalRecord->notes)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Additional Notes:</h6>
                                        <p class="mb-0">{{ $dentalRecord->notes }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($dentalRecord->next_appointment_date)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Next Appointment:</h6>
                                        <p class="mb-0">
                                            <strong class="text-info">{{ $dentalRecord->next_appointment_date->format('d M Y') }}</strong>
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Images -->
                            @if(!empty($images))
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h6>Images:</h6>
                                        <div class="row">
                                            @foreach($images as $image)
                                                <div class="col-md-3 mb-3">
                                                    <div class="card">
                                                        <img src="{{ Storage::url($image) }}" 
                                                             class="card-img-top" 
                                                             alt="Dental Image"
                                                             style="height: 200px; object-fit: cover; cursor: pointer;"
                                                             onclick="openImageModal('{{ Storage::url($image) }}')">
                                                        <div class="card-body p-2">
                                                            <a href="{{ Storage::url($image) }}" 
                                                               download 
                                                               class="btn btn-sm btn-outline-primary w-100">
                                                                <i class="bx bx-download me-1"></i>Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dental Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Dental Image" class="img-fluid">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function openImageModal(imageUrl) {
    document.getElementById('modalImage').src = imageUrl;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endpush
