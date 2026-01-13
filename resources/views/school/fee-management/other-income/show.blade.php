@extends('layouts.main')

@section('title', 'View Other Income')

@section('content')

<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Other Income Collection', 'url' => route('school.other-income.index'), 'icon' => 'bx bx-money'],
            ['label' => 'View Income', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">VIEW OTHER INCOME</h6>
        <hr />

        <div class="row">
            <!-- Main Details Card -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-receipt me-1 font-22 text-info"></i>
                                <span class="text-info">Income Details</span>
                            </div>
                            <div>
                                <a href="{{ route('school.other-income.edit', $otherIncome) }}" class="btn btn-warning btn-sm me-2">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.other-income.export-pdf', $otherIncome) }}" class="btn btn-primary btn-sm" target="_blank">
                                    <i class="bx bx-download me-1"></i> PDF
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="row g-3">
                            <!-- Transaction Date -->
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="form-label fw-bold">Transaction Date</label>
                                    <p class="mb-0">{{ $otherIncome->transaction_date->format('M d, Y') }}</p>
                                </div>
                            </div>

                            <!-- Income Type -->
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="form-label fw-bold">Income Type</label>
                                    <p class="mb-0">
                                        @if($otherIncome->income_type === 'student')
                                            <span class="badge bg-info">Student</span>
                                        @else
                                            <span class="badge bg-secondary">Other</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Student/Party -->
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="form-label fw-bold">
                                        {{ $otherIncome->income_type === 'student' ? 'Student' : 'Other Party' }}
                                    </label>
                                    <p class="mb-0">
                                        @if($otherIncome->income_type === 'student')
                                            {{ $otherIncome->student->name ?? 'N/A' }}
                                            @if($otherIncome->student)
                                                <br><small class="text-muted">Student ID: {{ $otherIncome->student->student_number }}</small>
                                                @if($otherIncome->student->class)
                                                    <br><small class="text-muted">Class: {{ $otherIncome->student->class->name }}
                                                    @if($otherIncome->student->stream)
                                                        - {{ $otherIncome->student->stream->name }}
                                                    @endif
                                                    </small>
                                                @endif
                                            @endif
                                        @else
                                            {{ $otherIncome->other_party ?? 'N/A' }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="form-label fw-bold">Description</label>
                                    <p class="mb-0">{{ $otherIncome->description }}</p>
                                </div>
                            </div>

                            <!-- Received In -->
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="form-label fw-bold">Received In</label>
                                    <p class="mb-0">{{ $otherIncome->received_in_display }}</p>
                                </div>
                            </div>

                            <!-- Income Account -->
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="form-label fw-bold">Income Account</label>
                                    <p class="mb-0">{{ $otherIncome->incomeAccount->account_name ?? 'N/A' }}</p>
                                </div>
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6">
                                <div class="detail-item">
                                    <label class="form-label fw-bold">Amount</label>
                                    <p class="mb-0 text-success fw-bold fs-5">{{ config('app.currency', 'TZS') }} {{ number_format($otherIncome->amount, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Card -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1"></i> Additional Information
                        </h6>
                        <hr />

                        <div class="mb-3">
                            <label class="form-label fw-bold">Created By</label>
                            <p class="mb-1">{{ $otherIncome->creator->name ?? 'N/A' }}</p>
                            <small class="text-muted">{{ $otherIncome->created_at->format('M d, Y H:i') }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Company</label>
                            <p class="mb-0">{{ $otherIncome->company->name ?? 'N/A' }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Branch</label>
                            <p class="mb-0">{{ $otherIncome->branch->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons Card -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-cog me-1"></i> Actions
                        </h6>
                        <hr />

                        <div class="d-grid gap-2">
                            <a href="{{ route('school.other-income.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>

                            <a href="{{ route('school.other-income.edit', $otherIncome) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit Record
                            </a>

                            <a href="{{ route('school.other-income.export-pdf', $otherIncome) }}" class="btn btn-primary" target="_blank">
                                <i class="bx bx-download me-1"></i> Export PDF
                            </a>

                            @if(auth()->user()->can('delete-other-income'))
                                <button type="button" class="btn btn-danger w-100 delete-income-btn"
                                        data-id="{{ $otherIncome->id }}"
                                        data-url="{{ route('school.other-income.destroy', $otherIncome) }}">
                                    <i class="bx bx-trash me-1"></i> Delete Record
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .detail-item {
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        border: 1px solid #e9ecef;
        margin-bottom: 1rem;
    }

    .detail-item label {
        color: #495057;
        margin-bottom: 0.5rem;
        display: block;
    }

    .detail-item p {
        color: #212529;
        font-size: 0.95rem;
    }

    .card {
        border-radius: 0.75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border: none;
    }

    .card-title {
        color: #495057;
        font-weight: 600;
    }

    .badge {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }

    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .text-success {
        color: #198754 !important;
    }

    .text-muted {
        color: #6c757d !important;
    }

    @media (max-width: 768px) {
        .col-lg-8, .col-lg-4 {
            margin-bottom: 1.5rem;
        }

        .d-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle delete button clicks with SweetAlert
    $('.delete-income-btn').on('click', function(e) {
        e.preventDefault();

        const incomeId = $(this).data('id');
        const deleteUrl = $(this).data('url');

        Swal.fire({
            title: 'Are you sure?',
            text: 'You want to delete this income record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait while we delete the income record.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = deleteUrl;
                form.style.display = 'none';

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add method spoofing for DELETE
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush