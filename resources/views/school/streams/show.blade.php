@extends('layouts.main')

@section('title', 'View Stream Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Streams', 'url' => route('school.streams.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'View', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">STREAM DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-show me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Stream Information</h5>
                            </div>
                            <div>
                                <a href="{{ route('school.streams.edit', $stream) }}" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.streams.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Stream Name</label>
                                    <p class="form-control-plaintext">{{ $stream->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Stream ID</label>
                                    <p class="form-control-plaintext">#{{ $stream->id }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created At</label>
                                    <p class="form-control-plaintext">{{ $stream->created_at->format('M d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated</label>
                                    <p class="form-control-plaintext">{{ $stream->updated_at->format('M d, Y \a\t h:i A') }}</p>
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
                            <i class="bx bx-info-circle me-1 text-info"></i> Quick Actions
                        </h6>
                        <hr />
                        <div class="d-grid gap-2">
                            <a href="{{ route('school.streams.edit', $stream) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit Stream
                            </a>
                            <a href="{{ route('school.streams.create') }}" class="btn btn-success">
                                <i class="bx bx-plus me-1"></i> Add New Stream
                            </a>
                            <a href="{{ route('school.streams.index') }}" class="btn btn-secondary">
                                <i class="bx bx-list-ul me-1"></i> View All Streams
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-stats me-1 text-info"></i> Stream Statistics
                        </h6>
                        <hr />
                        <div class="text-center">
                            <div class="mb-3">
                                <h4 class="text-primary">{{ $stream->sections()->count() }}</h4>
                                <small class="text-muted">Sections</small>
                            </div>
                            <div class="mb-3">
                                <h4 class="text-success">{{ $stream->enrollments()->count() }}</h4>
                                <small class="text-muted">Students Enrolled</small>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-control-plaintext {
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
        margin-bottom: 0;
        line-height: 1.5;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #f8f9fa;
        padding-left: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Stream details view loaded');
    });
</script>
@endpush