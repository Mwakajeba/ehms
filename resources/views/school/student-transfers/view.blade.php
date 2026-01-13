@extends('layouts.main')

@section('title', 'View Transfer - ' . $transfer->transfer_number)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Student Transfers', 'url' => route('school.student-transfers.index'), 'icon' => 'bx bx-transfer'],
            ['label' => $transfer->transfer_number, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">VIEW TRANSFER RECORD</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <!-- Transfer Overview Card -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bx bx-transfer me-2"></i>
                                Transfer #{{ $transfer->transfer_number }}
                            </h5>
                            <small>Created on {{ $transfer->created_at ? $transfer->created_at->format('M d, Y H:i') : 'N/A' }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            @if($transfer->getRouteKey())
                            <a href="{{ route('school.student-transfers.edit', $transfer->getRouteKey()) }}" class="btn btn-light btn-sm">
                                <i class="bx bx-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('school.student-transfers.print', $transfer->getRouteKey()) }}" class="btn btn-info btn-sm" target="_blank">
                                <i class="bx bx-printer me-1"></i> Print
                            </a>
                            @endif
                            <a href="{{ route('school.student-transfers.index') }}" class="btn btn-secondary btn-sm">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Transfer Status Badge -->
                        <div class="text-center mb-4">
                            <span class="badge fs-5 px-4 py-2
                                @if($transfer->status === 'pending') badge-warning
                                @elseif($transfer->status === 'approved') badge-info
                                @elseif($transfer->status === 'completed') badge-success
                                @else badge-secondary
                                @endif">
                                <i class="bx bx-check-circle me-1"></i>
                                {{ ucfirst($transfer->status) }}
                            </span>
                        </div>

                        <!-- Transfer Details Grid -->
                        <div class="row g-4">
                            <!-- Transfer Type & Basic Info -->
                            <div class="col-md-6">
                                <div class="card h-100 border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-info-circle me-2"></i>
                                            Transfer Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong class="text-muted">Transfer Type:</strong><br>
                                            <span class="badge
                                                @if($transfer->transfer_type === 'transfer_out') badge-danger
                                                @elseif($transfer->transfer_type === 'transfer_in') badge-success
                                                @else badge-warning
                                                @endif">
                                                @switch($transfer->transfer_type)
                                                    @case('transfer_out')
                                                        <i class="bx bx-log-out me-1"></i> Transfer Out
                                                        @break
                                                    @case('transfer_in')
                                                        <i class="bx bx-log-in me-1"></i> Transfer In
                                                        @break
                                                    @case('re_admission')
                                                        <i class="bx bx-refresh me-1"></i> Re-admission
                                                        @break
                                                @endswitch
                                            </span>
                                        </div>

                                        <div class="mb-3">
                                            <strong class="text-muted">Transfer Date:</strong><br>
                                            <span class="fs-6">{{ $transfer->transfer_date ? $transfer->transfer_date->format('F d, Y') : 'N/A' }}</span>
                                        </div>

                                        @if($transfer->transfer_certificate_number)
                                        <div class="mb-3">
                                            <strong class="text-muted">Certificate Number:</strong><br>
                                            <span class="fs-6">{{ $transfer->transfer_certificate_number }}</span>
                                        </div>
                                        @endif

                                        @if($transfer->reason)
                                        <div class="mb-3">
                                            <strong class="text-muted">Reason:</strong><br>
                                            <span class="fs-6">{{ ucfirst(str_replace('_', ' ', $transfer->reason)) }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Student & School Information -->
                            <div class="col-md-6">
                                <div class="card h-100 border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-user me-2"></i>
                                            Student & School Details
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong class="text-muted">Student:</strong><br>
                                            @if($transfer->student)
                                                @if($transfer->student->getRouteKey())
                                                <a href="{{ route('school.students.show', $transfer->student->getRouteKey()) }}" class="text-decoration-none">
                                                    <span class="fs-6">{{ $transfer->student->first_name }} {{ $transfer->student->last_name }}</span>
                                                    @if($transfer->student->admission_number)
                                                        <br><small class="text-muted">Admission #: {{ $transfer->student->admission_number }}</small>
                                                    @endif
                                                </a>
                                                @else
                                                    <span class="fs-6">{{ $transfer->student->first_name }} {{ $transfer->student->last_name }}</span>
                                                    @if($transfer->student->admission_number)
                                                        <br><small class="text-muted">Admission #: {{ $transfer->student->admission_number }}</small>
                                                    @endif
                                                @endif
                                            @else
                                                <span class="text-muted fs-6">
                                                    @if($transfer->student_name && $transfer->student_name !== 'N/A')
                                                        {{ $transfer->student_name }}
                                                    @else
                                                        Student record not available
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <strong class="text-muted">From School:</strong><br>
                                            <span class="fs-6">{{ $transfer->previous_school ?? 'N/A' }}</span>
                                        </div>

                                        <div class="mb-3">
                                            <strong class="text-muted">To School:</strong><br>
                                            <span class="fs-6">{{ $transfer->new_school ?? 'N/A' }}</span>
                                        </div>

                                        @if($transfer->processedBy)
                                        <div class="mb-3">
                                            <strong class="text-muted">Processed By:</strong><br>
                                            <span class="fs-6">{{ $transfer->processedBy->name ?? 'N/A' }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row g-4 mt-2">
                            @if($transfer->academic_records)
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">
                                            <i class="bx bx-book me-2"></i>
                                            Academic Records
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="academic-content">
                                            {!! nl2br(e($transfer->academic_records)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($transfer->notes)
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-note me-2"></i>
                                            Additional Notes
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="notes-content">
                                            {!! nl2br(e($transfer->notes)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Documents Section -->
                        @if($transfer->transfer_certificate || $transfer->academic_report)
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">
                                            <i class="bx bx-file me-2"></i>
                                            Transfer Documents
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @if($transfer->transfer_certificate)
                                            <div class="col-md-6 mb-3">
                                                <div class="document-card p-3 border rounded">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bx bx-file text-primary me-3" style="font-size: 2rem;"></i>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">Transfer Certificate</h6>
                                                            <small class="text-muted">{{ basename($transfer->transfer_certificate) }}</small>
                                                        </div>
                                                        <a href="{{ asset('storage/' . $transfer->transfer_certificate) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                            <i class="bx bx-show me-1"></i> View
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif

                                            @if($transfer->academic_report)
                                            <div class="col-md-6 mb-3">
                                                <div class="document-card p-3 border rounded">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bx bx-file text-success me-3" style="font-size: 2rem;"></i>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">Academic Report</h6>
                                                            <small class="text-muted">{{ basename($transfer->academic_report) }}</small>
                                                        </div>
                                                        <a href="{{ asset('storage/' . $transfer->academic_report) }}" target="_blank" class="btn btn-outline-success btn-sm">
                                                            <i class="bx bx-show me-1"></i> View
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Transfer History (if student exists) -->
                        @if($transfer->student && $transfer->student->transfers->count() > 1)
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0">
                                            <i class="bx bx-history me-2"></i>
                                            Transfer History
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Type</th>
                                                        <th>From → To</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($transfer->student->transfers->sortByDesc('transfer_date') as $historyTransfer)
                                                    <tr class="{{ $historyTransfer->id === $transfer->id ? 'table-primary' : '' }}">
                                                        <td>{{ $historyTransfer->transfer_date ? $historyTransfer->transfer_date->format('M d, Y') : 'N/A' }}</td>
                                                        <td>
                                                            <span class="badge
                                                                @if($historyTransfer->transfer_type === 'transfer_out') badge-danger
                                                                @elseif($historyTransfer->transfer_type === 'transfer_in') badge-success
                                                                @else badge-warning
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
                                                        <td>
                                                            @if($historyTransfer->getRouteKey())
                                                            <a href="{{ route('school.student-transfers.show', $historyTransfer->getRouteKey()) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                            @else
                                                            <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
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
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.15s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    .fs-5 {
        font-size: 1.25rem !important;
    }

    .fs-6 {
        font-size: 1rem !important;
    }

    .academic-content, .notes-content {
        line-height: 1.6;
        white-space: pre-wrap;
        font-size: 0.95rem;
    }

    .document-card {
        background: #f8f9fa;
        transition: background-color 0.15s ease-in-out;
    }

    .document-card:hover {
        background: #e9ecef;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        border-top: none;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .table-primary {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.gap-2 {
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Transfer view page loaded');

    // Add any interactive functionality here
    $('.document-card').on('click', function(e) {
        // Only trigger if not clicking on the view button
        if (!$(e.target).closest('.btn').length) {
            $(this).find('.btn').trigger('click');
        }
    });
});
</script>
@endpush

<style media="print">
    .btn, .card-header .d-flex.gap-2 {
        display: none !important;
    }

    .card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
    }

    .card-header {
        background: #f8f9fa !important;
        border-bottom: 1px solid #dee2e6 !important;
        color: #000 !important;
    }
</style>