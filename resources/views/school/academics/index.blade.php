@extends('layouts.main')

@section('title', 'Academics Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Academics', 'url' => '#', 'icon' => 'bx bx-graduation']
        ]" />
        <h6 class="mb-0 text-uppercase">ACADEMICS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <!-- Statistics Cards -->
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bx bx-group fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $subjectGroups->count() }}</h4>
                                <small>Subject Groups</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bx bx-book fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $subjects->count() }}</h4>
                                <small>Subjects</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bx bx-file fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $curricula->count() }}</h4>
                                <small>Curricula</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bx bx-check-circle fs-1"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ $curricula->where('is_active', true)->count() }}</h4>
                                <small>Active Curricula</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Quick Actions</h6>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('school.academics.subject-groups.create') }}" class="btn btn-outline-primary w-100">
                                    <i class="bx bx-plus me-1"></i> Add Subject Group
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('school.academics.subjects.create') }}" class="btn btn-outline-success w-100">
                                    <i class="bx bx-plus me-1"></i> Add Subject
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('school.academics.curricula.create') }}" class="btn btn-outline-info w-100">
                                    <i class="bx bx-plus me-1"></i> Add Curriculum
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('school.academics.subject-groups.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="bx bx-list-ul me-1"></i> View All
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Subject Groups</h6>
                    </div>
                    <div class="card-body">
                        @if($subjectGroups->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($subjectGroups->take(5) as $group)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $group->name }}</strong>
                                            <br><small class="text-muted">{{ $group->description ?? 'No description' }}</small>
                                        </div>
                                        <span class="badge bg-{{ $group->is_active ? 'success' : 'secondary' }}">
                                            {{ $group->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('school.academics.subject-groups.index') }}" class="btn btn-sm btn-outline-primary">
                                    View All Subject Groups
                                </a>
                            </div>
                        @else
                            <p class="text-muted mb-0">No subject groups found. <a href="{{ route('school.academics.subject-groups.create') }}">Create one now</a></p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Recent Subjects</h6>
                    </div>
                    <div class="card-body">
                        @if($subjects->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($subjects->take(5) as $subject)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $subject->name }}</strong> ({{ $subject->code }})
                                            <br><small class="text-muted">{{ $subject->subjectGroup->name ?? 'No group' }}</small>
                                        </div>
                                        <span class="badge bg-{{ $subject->is_active ? 'success' : 'secondary' }}">
                                            {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('school.academics.subjects.index') }}" class="btn btn-sm btn-outline-success">
                                    View All Subjects
                                </a>
                            </div>
                        @else
                            <p class="text-muted mb-0">No subjects found. <a href="{{ route('school.academics.subjects.create') }}">Create one now</a></p>
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
        transition: transform 0.2s ease-in-out;
    }

    .card:hover {
        transform: translateY(-2px);
    }

    .fs-1 {
        font-size: 2.5rem !important;
    }

    .btn-outline-primary:hover, .btn-outline-success:hover, .btn-outline-info:hover, .btn-outline-secondary:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Academics dashboard loaded');
    });
</script>
@endpush