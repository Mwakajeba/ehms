@extends('layouts.main')

@section('title', 'Subject Details - ' . $subject->name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subjects', 'url' => route('school.subjects.index'), 'icon' => 'bx bx-book-open'],
            ['label' => $subject->name, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">SUBJECT DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-show me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Subject Information</h5>
                            </div>
                            <div>
                                <a href="{{ route('school.subjects.edit', $subject->hashid) }}" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.subjects.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Subject Name</label>
                                    <div class="text-value">{{ $subject->name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Subject Code</label>
                                    <div class="text-value">{{ $subject->code }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Short Name</label>
                                    <div class="text-value">{{ $subject->short_name ?: 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Subject Type</label>
                                    <div class="text-value">
                                        <span class="badge bg-{{ $subject->subject_type === 'practical' ? 'warning' : 'info' }}">
                                            {{ ucfirst($subject->subject_type ?? 'theory') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Passing Marks</label>
                                    <div class="text-value">{{ $subject->passing_marks ?? '40.00' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Total Marks</label>
                                    <div class="text-value">{{ $subject->total_marks ?? '100.00' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <div class="text-value">
                                        <span class="badge bg-{{ $subject->is_active ? 'success' : 'secondary' }}">
                                            {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created By</label>
                                    <div class="text-value">{{ $subject->creator ? $subject->creator->name : 'N/A' }}</div>
                                </div>
                            </div>
                        </div>

                        @if($subject->description)
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description</label>
                                    <div class="text-value">{{ $subject->description }}</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created At</label>
                                    <div class="text-value">{{ $subject->created_at->format('M d, Y \a\t h:i A') }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated</label>
                                    <div class="text-value">{{ $subject->updated_at->format('M d, Y \a\t h:i A') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-cog me-1 text-info"></i> Quick Actions
                        </h6>
                        <hr />
                        <div class="d-grid gap-2">
                            <a href="{{ route('school.subjects.edit', $subject->hashid) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit Subject
                            </a>
                            <a href="{{ route('school.subjects.create') }}" class="btn btn-success">
                                <i class="bx bx-plus me-1"></i> Add New Subject
                            </a>
                            <a href="{{ route('school.subjects.index') }}" class="btn btn-secondary">
                                <i class="bx bx-list-ul me-1"></i> View All Subjects
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-stats me-1 text-info"></i> Subject Statistics
                        </h6>
                        <hr />
                        <div class="text-center">
                            <div class="mb-3">
                                <h4 class="text-primary">{{ $subject->id }}</h4>
                                <small class="text-muted">Subject ID</small>
                            </div>
                            <div class="mb-3">
                                <h4 class="text-success">{{ $subject->is_active ? 'Active' : 'Inactive' }}</h4>
                                <small class="text-muted">Current Status</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1 text-info"></i> Subject Information
                        </h6>
                        <hr />
                        <div class="small text-muted">
                            <p><strong>Subject Code:</strong> {{ $subject->code }}</p>
                            <p><strong>Type:</strong> {{ ucfirst($subject->subject_type ?? 'theory') }}</p>
                            <p><strong>Passing Marks:</strong> {{ $subject->passing_marks ?? '40.00' }}</p>
                            <p><strong>Total Marks:</strong> {{ $subject->total_marks ?? '100.00' }}</p>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .text-value {
        padding: 0.5rem 0;
        color: #495057;
        font-size: 1rem;
        line-height: 1.5;
        font-weight: 500;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Subject details view loaded');
    });
</script>
@endpush