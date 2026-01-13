@extends('layouts.main')

@section('title', 'Transfer Details - ' . $transfer->transfer_number)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => $transfer->transfer_number, 'url' => '#', 'icon' => 'bx bx-file']
        ]" />
        <h6 class="mb-0 text-uppercase">TRANSFER DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <!-- Transfer Header -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <i class="bx bx-transfer me-2"></i>
                                    Transfer #{{ $transfer->transfer_number }}
                                </h4>
                                <small>{{ $transfer->transfer_date ? $transfer->transfer_date->format('F d, Y') : 'N/A' }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-info btn-sm" onclick="window.print()">
                                    <i class="bx bx-printer me-1"></i> Print
                                </button>
                                <a href="{{ route('school.student-transfers.pdf', $transfer->getRouteKey()) }}" class="btn btn-success btn-sm" target="_blank">
                                    <i class="bx bx-download me-1"></i> Download PDF
                                </a>
                                <a href="{{ route('school.student-transfers.pdf-preview', $transfer->getRouteKey()) }}" class="btn btn-secondary btn-sm" target="_blank">
                                    <i class="bx bx-file me-1"></i> Preview PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="transfer-type-icon mb-2">
                                        @if($transfer->transfer_type === 'transfer_out')
                                            <i class="bx bx-log-out text-danger" style="font-size: 3rem;"></i>
                                        @elseif($transfer->transfer_type === 'transfer_in')
                                            <i class="bx bx-log-in text-success" style="font-size: 3rem;"></i>
                                        @else
                                            <i class="bx bx-refresh text-warning" style="font-size: 3rem;"></i>
                                        @endif
                                    </div>
                                    <h5 class="transfer-type-label">
                                        @switch($transfer->transfer_type)
                                            @case('transfer_out')
                                                Transfer Out
                                                @break
                                            @case('transfer_in')
                                                Transfer In
                                                @break
                                            @case('re_admission')
                                                Re-admission
                                                @break
                                        @endswitch
                                    </h5>
                                    <span class="badge fs-6
                                        @if($transfer->status === 'pending') badge-warning
                                        @elseif($transfer->status === 'approved') badge-info
                                        @elseif($transfer->status === 'completed') badge-success
                                        @else badge-secondary
                                        @endif">
                                        {{ ucfirst($transfer->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">Student Name</label>
                                            <div class="info-value">
                                                @if($transfer->student)
                                                    <a href="{{ route('school.students.show', $transfer->student->getRouteKey()) }}" class="text-decoration-none">
                                                        <i class="bx bx-user me-1"></i>{{ $transfer->student->first_name }} {{ $transfer->student->last_name }}
                                                        @if($transfer->student->admission_number)
                                                            <small class="text-muted">({{ $transfer->student->admission_number }})</small>
                                                        @endif
                                                    </a>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="bx bx-user-x me-1"></i>
                                                        @if($transfer->student_name && $transfer->student_name !== 'N/A')
                                                            {{ $transfer->student_name }}
                                                        @else
                                                            Student record not available
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">Transfer Date</label>
                                            <div class="info-value">{{ $transfer->transfer_date ? $transfer->transfer_date->format('F d, Y') : 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">From School</label>
                                            <div class="info-value">{{ $transfer->previous_school ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">To School</label>
                                            <div class="info-value">{{ $transfer->new_school ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    @if($transfer->transfer_certificate_number)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">Certificate Number</label>
                                            <div class="info-value">{{ $transfer->transfer_certificate_number }}</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($transfer->reason)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">Reason</label>
                                            <div class="info-value">{{ ucfirst(str_replace('_', ' ', $transfer->reason)) }}</div>
                                        </div>
                                    </div>
                                    @endif
                                    @if($transfer->processedBy)
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">Processed By</label>
                                            <div class="info-value">{{ $transfer->processedBy->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Transfer Details -->
                    <div class="col-lg-8">
                        <!-- Academic Records -->
                        @if($transfer->academic_records)
                        <div class="card border-success mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-book me-2"></i> Academic Records
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="academic-records-content">
                                    {!! nl2br(e($transfer->academic_records)) !!}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Additional Notes -->
                        @if($transfer->notes)
                        <div class="card border-info mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-note me-2"></i> Additional Notes
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="notes-content">
                                    {!! nl2br(e($transfer->notes)) !!}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Transfer History -->
                        @if($transfer->student)
                        <div class="card border-warning mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-history me-2"></i> Student Transfer History
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>From/To School</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transfer->student->transfers->sortByDesc('transfer_date') as $historyTransfer)
                                            <tr class="{{ $historyTransfer->id === $transfer->id ? 'table-primary' : '' }}">
                                                <td>{{ $historyTransfer->transfer_date ? $historyTransfer->transfer_date->format('M d, Y') : 'N/A' }}</td>
                                                <td>
                                                    <span class="badge
                                                        @if($historyTransfer->transfer_type === 'transfer_out') badge-transfer-out
                                                        @elseif($historyTransfer->transfer_type === 'transfer_in') badge-transfer-in
                                                        @else badge-re-admission
                                                        @endif">
                                                        @switch($historyTransfer->transfer_type)
                                                            @case('transfer_out') Transfer Out @break
                                                            @case('transfer_in') Transfer In @break
                                                            @case('re_admission') Re-admission @break
                                                        @endswitch
                                                    </span>
                                                </td>
                                                <td>{{ $historyTransfer->previous_school ?? 'N/A' }} → {{ $historyTransfer->new_school ?? 'N/A' }}</td>
                                                <td>
                                                    <span class="badge
                                                        @if($historyTransfer->status === 'pending') badge-warning
                                                        @elseif($historyTransfer->status === 'approved') badge-info
                                                        @elseif($historyTransfer->status === 'completed') badge-success
                                                        @else badge-secondary
                                                        @endif">
                                                        {{ ucfirst($historyTransfer->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Transfer Documents -->
                        <div class="card border-secondary mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-file me-2"></i> Transfer Documents
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($transfer->transfer_certificate || $transfer->academic_report)
                                    @if($transfer->transfer_certificate)
                                    <div class="document-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-file text-primary me-2"></i>
                                            <div class="flex-grow-1">
                                                <strong>Transfer Certificate</strong>
                                                <br><small class="text-muted">{{ basename($transfer->transfer_certificate) }}</small>
                                            </div>
                                            <a href="{{ asset('storage/' . $transfer->transfer_certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @endif

                                    @if($transfer->academic_report)
                                    <div class="document-item">
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-file text-success me-2"></i>
                                            <div class="flex-grow-1">
                                                <strong>Academic Report</strong>
                                                <br><small class="text-muted">{{ basename($transfer->academic_report) }}</small>
                                            </div>
                                            <a href="{{ asset('storage/' . $transfer->academic_report) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </div>
                                    </div>
                                    @endif
                                @else
                                    <div class="text-center text-muted py-3">
                                        <i class="bx bx-file-blank" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No documents uploaded</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Transfer Timeline -->
                        <div class="card border-info">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-time me-2"></i> Transfer Timeline
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker created"></div>
                                        <div class="timeline-content">
                                            <h6>Transfer Created</h6>
                                            <small>{{ $transfer->created_at ? $transfer->created_at->format('M d, Y H:i') : 'N/A' }} by {{ $transfer->processedBy->name ?? 'System' }}</small>
                                        </div>
                                    </div>

                                    @if($transfer->status !== 'pending')
                                    <div class="timeline-item">
                                        <div class="timeline-marker approved"></div>
                                        <div class="timeline-content">
                                            <h6>Status Updated to {{ ucfirst($transfer->status) }}</h6>
                                            <small>{{ $transfer->updated_at ? $transfer->updated_at->format('M d, Y H:i') : 'N/A' }}</small>
                                        </div>
                                    </div>
                                    @endif

                                    @if($transfer->status === 'completed')
                                    <div class="timeline-item">
                                        <div class="timeline-marker completed"></div>
                                        <div class="timeline-content">
                                            <h6>Transfer Completed</h6>
                                            <small>{{ $transfer->updated_at ? $transfer->updated_at->format('M d, Y H:i') : 'N/A' }}</small>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .transfer-type-icon {
        margin-bottom: 1rem;
    }

    .transfer-type-label {
        color: #333;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .info-item {
        margin-bottom: 1rem;
    }

    .info-label {
        display: block;
        font-weight: 600;
        color: #555;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .info-value {
        color: #333;
        font-size: 1rem;
    }

    .academic-records-content, .notes-content {
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .badge-transfer-out {
        background-color: #dc3545;
        color: white;
    }

    .badge-transfer-in {
        background-color: #198754;
        color: white;
    }

    .badge-re-admission {
        background-color: #ffc107;
        color: #000;
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 20px;
        padding-left: 40px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -22px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
    }

    .timeline-marker.created {
        background: #0d6efd;
    }

    .timeline-marker.approved {
        background: #0dcaf0;
    }

    .timeline-marker.completed {
        background: #198754;
    }

    .timeline-content h6 {
        margin: 0 0 5px 0;
        color: #333;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .timeline-content small {
        color: #666;
        font-size: 0.8rem;
    }

    .document-item {
        padding: 10px;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
    }

    .card-header {
        font-weight: 600;
    }

    .table-primary {
        background-color: rgba(13, 110, 253, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Transfer details page loaded');
});
</script>
@endpush

<style media="print">
    .btn, .d-flex.gap-2 {
        display: none !important;
    }
    .card-header {
        background: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
    }
</style>