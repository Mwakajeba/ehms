@extends('layouts.main')

@section('title', 'Student Transfer History - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => $student->first_name . ' ' . $student->last_name, 'url' => '#', 'icon' => 'bx bx-user']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT TRANSFER HISTORY</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <!-- Student Information Card -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="mb-0">
                                    <i class="bx bx-user me-2"></i>
                                    {{ $student->first_name }} {{ $student->last_name }}
                                </h4>
                                <small>Admission #: {{ $student->admission_number }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('school.students.show', $student->getRouteKey()) }}" class="btn btn-light btn-sm">
                                    <i class="bx bx-show me-1"></i> View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="text-center">
                                    @if($student->passport_photo)
                                        <img src="{{ asset('storage/' . $student->passport_photo) }}"
                                             alt="Student Photo"
                                             class="rounded-circle mb-3"
                                             style="width: 80px; height: 80px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                                             style="width: 80px; height: 80px;">
                                            <i class="bx bx-user text-muted" style="font-size: 2rem;"></i>
                                        </div>
                                    @endif
                                    <h6 class="mb-1">{{ $student->first_name }} {{ $student->last_name }}</h6>
                                    <span class="badge bg-primary">{{ $student->status }}</span>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Admission Number</label>
                                            <div class="info-value">{{ $student->admission_number }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Date of Birth</label>
                                            <div class="info-value">{{ $student->date_of_birth ? $student->date_of_birth->format('M d, Y') : 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Gender</label>
                                            <div class="info-value">{{ ucfirst($student->gender) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Class</label>
                                            <div class="info-value">{{ $student->class->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Stream</label>
                                            <div class="info-value">{{ $student->stream->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Academic Year</label>
                                            <div class="info-value">{{ $student->academicYear->year_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transfer History Timeline -->
                <div class="card border-info">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-history me-2"></i> Transfer History Timeline
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($transferHistory->count() > 0)
                            <div class="timeline">
                                @foreach($transferHistory as $index => $historyTransfer)
                                <div class="timeline-item {{ $historyTransfer->id === $transfer->id ? 'current' : '' }}">
                                    <div class="timeline-marker
                                        @if($historyTransfer->transfer_type === 'transfer_out') transfer-out
                                        @elseif($historyTransfer->transfer_type === 'transfer_in') transfer-in
                                        @else re-admission
                                        @endif">
                                        @switch($historyTransfer->transfer_type)
                                            @case('transfer_out')
                                                <i class="bx bx-log-out"></i>
                                                @break
                                            @case('transfer_in')
                                                <i class="bx bx-log-in"></i>
                                                @break
                                            @case('re_admission')
                                                <i class="bx bx-refresh"></i>
                                                @break
                                        @endswitch
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">
                                                    @switch($historyTransfer->transfer_type)
                                                        @case('transfer_out') Transfer Out @break
                                                        @case('transfer_in') Transfer In @break
                                                        @case('re_admission') Re-admission @break
                                                    @endswitch
                                                    @if($historyTransfer->id === $transfer->id)
                                                        <span class="badge bg-primary ms-2">Current</span>
                                                    @endif
                                                </h6>
                                                <div class="row g-2 mb-2">
                                                    <div class="col-md-6">
                                                        <small class="text-muted">
                                                            <i class="bx bx-calendar me-1"></i>
                                                            {{ $historyTransfer->transfer_date ? $historyTransfer->transfer_date->format('M d, Y') : 'N/A' }}
                                                        </small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <small class="text-muted">
                                                            <i class="bx bx-user me-1"></i>
                                                            {{ $historyTransfer->processedBy->name ?? 'N/A' }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="transfer-details">
                                                    @if($historyTransfer->transfer_type === 'transfer_out')
                                                        <p class="mb-1"><strong>From:</strong> {{ $historyTransfer->previous_school ?? 'This School' }}</p>
                                                        <p class="mb-1"><strong>To:</strong> {{ $historyTransfer->new_school ?? 'N/A' }}</p>
                                                    @elseif($historyTransfer->transfer_type === 'transfer_in')
                                                        <p class="mb-1"><strong>From:</strong> {{ $historyTransfer->previous_school ?? 'N/A' }}</p>
                                                        <p class="mb-1"><strong>To:</strong> {{ $historyTransfer->new_school ?? 'This School' }}</p>
                                                    @else
                                                        <p class="mb-1"><strong>From:</strong> {{ $historyTransfer->previous_school ?? 'Break/Absence' }}</p>
                                                        <p class="mb-1"><strong>To:</strong> {{ $historyTransfer->new_school ?? 'This School' }}</p>
                                                    @endif
                                                    @if($historyTransfer->reason)
                                                        <p class="mb-1"><strong>Reason:</strong> {{ ucfirst(str_replace('_', ' ', $historyTransfer->reason)) }}</p>
                                                    @endif
                                                    @if($historyTransfer->transfer_certificate_number)
                                                        <p class="mb-1"><strong>Certificate #:</strong> {{ $historyTransfer->transfer_certificate_number }}</p>
                                                    @endif
                                                </div>
                                                @if($historyTransfer->academic_records)
                                                    <div class="academic-summary mt-2 p-2 bg-light rounded">
                                                        <small><strong>Academic Records:</strong></small>
                                                        <div class="mt-1">{{ Str::limit(strip_tags($historyTransfer->academic_records), 100) }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ms-3">
                                                <span class="badge
                                                    @if($historyTransfer->status === 'pending') bg-warning
                                                    @elseif($historyTransfer->status === 'approved') bg-info
                                                    @elseif($historyTransfer->status === 'completed') bg-success
                                                    @else bg-secondary
                                                    @endif">
                                                    {{ ucfirst($historyTransfer->status) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="timeline-actions mt-2">
                                            <!-- Actions removed -->
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-history text-muted" style="font-size: 4rem;"></i>
                                <h5 class="text-muted mt-3">No Transfer History</h5>
                                <p class="text-muted">This student has no transfer records.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Statistics Card -->
                @if($transferHistory->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card border-success text-center">
                            <div class="card-body">
                                <div class="stat-icon text-success mb-2">
                                    <i class="bx bx-transfer" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="mb-0">{{ $transferHistory->count() }}</h4>
                                <small class="text-muted">Total Transfers</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-info text-center">
                            <div class="card-body">
                                <div class="stat-icon text-info mb-2">
                                    <i class="bx bx-log-in" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="mb-0">{{ $transferHistory->where('transfer_type', 'transfer_in')->count() }}</h4>
                                <small class="text-muted">Transfer Ins</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning text-center">
                            <div class="card-body">
                                <div class="stat-icon text-warning mb-2">
                                    <i class="bx bx-log-out" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="mb-0">{{ $transferHistory->where('transfer_type', 'transfer_out')->count() }}</h4>
                                <small class="text-muted">Transfer Outs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-secondary text-center">
                            <div class="card-body">
                                <div class="stat-icon text-secondary mb-2">
                                    <i class="bx bx-refresh" style="font-size: 2rem;"></i>
                                </div>
                                <h4 class="mb-0">{{ $transferHistory->where('transfer_type', 're_admission')->count() }}</h4>
                                <small class="text-muted">Re-admissions</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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

    .timeline {
        position: relative;
        padding-left: 40px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(to bottom, #e9ecef 0%, #dee2e6 100%);
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-left: 60px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-item.current {
        background-color: rgba(13, 110, 253, 0.05);
        border-radius: 8px;
        padding: 15px;
        margin-left: -15px;
        margin-right: -15px;
    }

    .timeline-marker {
        position: absolute;
        left: -35px;
        top: 0;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        border: 3px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .timeline-marker.transfer-out {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    }

    .timeline-marker.transfer-in {
        background: linear-gradient(135deg, #198754 0%, #146c43 100%);
    }

    .timeline-marker.re-admission {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }

    .timeline-content h6 {
        margin: 0 0 8px 0;
        color: #333;
        font-size: 1rem;
        font-weight: 600;
    }

    .transfer-details p {
        margin: 0.25rem 0;
        font-size: 0.9rem;
        color: #666;
    }

    .academic-summary {
        font-size: 0.85rem;
        line-height: 1.4;
    }

    .timeline-actions {
        margin-top: 10px;
    }

    .stat-icon {
        opacity: 0.8;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.75rem;
        transition: box-shadow 0.15s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .timeline {
            padding-left: 30px;
        }

        .timeline-item {
            padding-left: 40px;
        }

        .timeline-marker {
            left: -25px;
            width: 30px;
            height: 30px;
            font-size: 1rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 0.5rem;
        }

        .timeline-actions .btn {
            width: 100%;
            margin-bottom: 0.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Student transfer history page loaded');

    // Add smooth scrolling for timeline navigation
    $('.timeline-actions a').on('click', function(e) {
        // Allow normal navigation
    });

    // Add animation to timeline items on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Initially hide timeline items and animate them in
    $('.timeline-item').each(function() {
        $(this).css({
            'opacity': '0',
            'transform': 'translateY(20px)',
            'transition': 'opacity 0.6s ease, transform 0.6s ease'
        });
        observer.observe(this);
    });
});
</script>
@endpush