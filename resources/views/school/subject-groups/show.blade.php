@extends('layouts.main')

@section('title', 'View Subject Group: ' . $subjectGroup->name)

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Subjects', 'url' => route('school.subjects.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subject Groups', 'url' => route('school.subject-groups.index'), 'icon' => 'bx bx-group'],
            ['label' => $subjectGroup->name, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <!-- Header with Actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold text-dark">
                    <i class="bx bx-group me-2 text-primary"></i>{{ $subjectGroup->name }}
                </h4>
                <p class="text-muted mb-0">Subject Group Details & Information</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('school.subject-groups.edit', $subjectGroup->hashid) }}" class="btn btn-outline-primary">
                    <i class="bx bx-edit me-1"></i> Edit
                </a>
                <a href="{{ route('school.subject-groups.index') }}" class="btn btn-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Status Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 mb-4">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 mb-4">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Subject Group Information -->
            <div class="col-lg-8">
                <div class="card border">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-info-circle me-2 text-primary"></i>Subject Group Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Code:</label>
                                <p class="mb-0">{{ $subjectGroup->code ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Name:</label>
                                <p class="mb-0">{{ $subjectGroup->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Class:</label>
                                <p class="mb-0">
                                    @if($subjectGroup->classe)
                                        <span class="badge bg-info">{{ $subjectGroup->classe->name }} (Level {{ $subjectGroup->classe->level }})</span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="mb-0">
                                    @if($subjectGroup->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Created:</label>
                                <p class="mb-0">{{ $subjectGroup->created_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Last Updated:</label>
                                <p class="mb-0">{{ $subjectGroup->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>

                        @if($subjectGroup->description)
                            <div class="mt-3">
                                <label class="form-label fw-bold">Description:</label>
                                <p class="mb-0">{{ $subjectGroup->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Subjects in this Group -->
                <div class="card border mt-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-book me-2 text-primary"></i>Subjects in this Group ({{ $subjectGroup->subjects->count() }})
                        </h6>
                        <a href="{{ route('school.subject-groups.edit', $subjectGroup->hashid) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-edit me-1"></i> Manage Subjects
                        </a>
                    </div>
                    <div class="card-body">
                        @if($subjectGroup->subjects->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Short Name</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjectGroup->subjects as $index => $subject)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $subject->code }}</strong></td>
                                                <td>{{ $subject->name }}</td>
                                                <td>{{ $subject->short_name ?? 'N/A' }}</td>
                                                <td>
                                                    @if($subject->subject_type === 'theory')
                                                        <span class="badge bg-primary">Theory</span>
                                                    @elseif($subject->subject_type === 'practical')
                                                        <span class="badge bg-warning">Practical</span>
                                                    @else
                                                        <span class="badge bg-secondary">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($subject->is_active)
                                                        <span class="badge bg-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bx bx-book text-muted" style="font-size: 3rem;"></i>
                                <h6 class="text-muted mt-2">No subjects assigned</h6>
                                <p class="text-muted">This subject group doesn't have any subjects assigned yet.</p>
                                <a href="{{ route('school.subject-groups.edit', $subjectGroup->hashid) }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-plus me-1"></i> Add Subjects
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Statistics Card -->
                <div class="card border">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-bar-chart me-2 text-primary"></i>Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="mb-1 text-primary">{{ $subjectGroup->subjects->count() }}</h4>
                                    <small class="text-muted">Subjects</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="border rounded p-3">
                                    <h4 class="mb-1 text-success">{{ $subjectGroup->subjects->where('is_active', true)->count() }}</h4>
                                    <small class="text-muted">Active</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h4 class="mb-1 text-info">{{ $subjectGroup->subjects->where('subject_type', 'theory')->count() }}</h4>
                                    <small class="text-muted">Theory</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-3">
                                    <h4 class="mb-1 text-warning">{{ $subjectGroup->subjects->where('subject_type', 'practical')->count() }}</h4>
                                    <small class="text-muted">Practical</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card border mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-cog me-2 text-primary"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('school.subject-groups.edit', $subjectGroup->hashid) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-edit me-1"></i> Edit Group
                            </a>
                            <a href="{{ route('school.subjects.create') }}" class="btn btn-outline-success btn-sm">
                                <i class="bx bx-plus me-1"></i> Add New Subject
                            </a>
                            <a href="{{ route('school.subject-groups.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-list-ul me-1"></i> View All Groups
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Related Information -->
                <div class="card border mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0 fw-bold">
                            <i class="bx bx-link me-2 text-primary"></i>Related Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Class:</label>
                            <p class="mb-1">
                                @if($subjectGroup->classe)
                                    {{ $subjectGroup->classe->name }} (Level {{ $subjectGroup->classe->level }})
                                @else
                                    <span class="text-muted">Not assigned to any class</span>
                                @endif
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Subjects:</label>
                            <p class="mb-1">{{ $subjectGroup->subjects->count() }} subjects</p>
                        </div>
                        <div class="mb-0">
                            <label class="form-label fw-bold">Created By:</label>
                            <p class="mb-0">
                                @if($subjectGroup->creator)
                                    {{ $subjectGroup->creator->name }}
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </p>
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
    .page-wrapper {
        background-color: #f8f9fa;
        min-height: 100vh;
    }

    .card {
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-radius: 8px;
    }

    .card-header {
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        border-bottom: 2px solid #dee2e6;
        padding: 0.75rem;
    }

    .table td {
        padding: 0.75rem;
        vertical-align: middle;
    }

    .badge {
        font-weight: 500;
        font-size: 0.75rem;
    }

    .alert {
        border-radius: 8px;
        border-left: 4px solid;
    }

    .alert-success {
        border-left-color: #198754;
    }

    .alert-danger {
        border-left-color: #dc3545;
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 0.375rem 0.75rem;
    }

    .border {
        border-color: #dee2e6 !important;
    }
</style>
@endpush