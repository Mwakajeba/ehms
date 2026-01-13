@extends('layouts.main')

@section('title', 'Ultrasound Result Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Ultrasound', 'url' => route('hospital.ultrasound.index'), 'icon' => 'bx bx-scan'],
                ['label' => 'Result Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">ULTRASOUND RESULT DETAILS</h6>
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
                                @if($ultrasoundResult->result_status === 'ready')
                                    <a href="{{ route('hospital.ultrasound.print', $ultrasoundResult->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="bx bx-printer me-1"></i>Print
                                    </a>
                                @elseif($ultrasoundResult->result_status === 'pending')
                                    <form action="{{ route('hospital.ultrasound.mark-ready', $ultrasoundResult->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="bx bx-check me-1"></i>Mark as Ready
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('hospital.ultrasound.index') }}" class="btn btn-sm btn-secondary">
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
                                            <td><strong>{{ $ultrasoundResult->patient->full_name }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $ultrasoundResult->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Age:</th>
                                            <td>{{ $ultrasoundResult->patient->age ? $ultrasoundResult->patient->age . ' years' : 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit #:</th>
                                            <td>{{ $ultrasoundResult->visit->visit_number }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Result #:</th>
                                            <td><strong>{{ $ultrasoundResult->result_number }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Result Status:</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'ready' => 'success',
                                                        'printed' => 'info',
                                                        'delivered' => 'primary'
                                                    ];
                                                    $statusColor = $statusColors[$ultrasoundResult->result_status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $statusColor }}">
                                                    {{ ucfirst($ultrasoundResult->result_status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Performed By:</th>
                                            <td>{{ $ultrasoundResult->performedBy->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td>{{ $ultrasoundResult->created_at->format('d M Y, H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Examination Results -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-scan me-2"></i>Examination Results
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h6>Examination Type:</h6>
                                    <p class="mb-0"><strong>{{ $ultrasoundResult->examination_type }}</strong></p>
                                </div>
                            </div>

                            @if($ultrasoundResult->findings)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Findings:</h6>
                                        <p class="mb-0">{{ $ultrasoundResult->findings }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($ultrasoundResult->impression)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Impression:</h6>
                                        <p class="mb-0"><strong class="text-primary">{{ $ultrasoundResult->impression }}</strong></p>
                                    </div>
                                </div>
                            @endif

                            @if($ultrasoundResult->recommendation)
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <h6>Recommendation:</h6>
                                        <p class="mb-0">{{ $ultrasoundResult->recommendation }}</p>
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
                                                             alt="Ultrasound Image"
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
                    <h5 class="modal-title">Ultrasound Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="Ultrasound Image" class="img-fluid">
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
