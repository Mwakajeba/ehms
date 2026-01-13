@extends('layouts.main')

@section('title', 'Class Teacher Assignment Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Class Teachers', 'url' => route('school.class-teachers.index'), 'icon' => 'bx bx-user-check'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">CLASS TEACHER ASSIGNMENT DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-user-check me-1 font-22 text-primary"></i>
                                <h5 class="mb-0 text-primary d-inline">Assignment Information</h5>
                            </div>
                            <div>
                                @if($classTeacher->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="bx bx-user me-1"></i> Teacher Details</h6>
                                    <div class="ps-3">
                                        <p class="mb-1"><strong>Name:</strong> {{ $classTeacher->employee->first_name }} {{ $classTeacher->employee->last_name }}</p>
                                        <p class="mb-1"><strong>Employee ID:</strong> {{ $classTeacher->employee->employee_id }}</p>
                                        <p class="mb-1"><strong>Department:</strong> {{ $classTeacher->employee->department->name ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Position:</strong> {{ $classTeacher->employee->position->name ?? 'N/A' }}</p>
                                        <p class="mb-0"><strong>Email:</strong> {{ $classTeacher->employee->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="bx bx-school me-1"></i> Class Details</h6>
                                    <div class="ps-3">
                                        <p class="mb-1"><strong>Class Name:</strong> {{ $classTeacher->classe->name }}</p>
                                        <p class="mb-1"><strong>Class Code:</strong> {{ $classTeacher->classe->code }}</p>
                                        <p class="mb-1"><strong>Stream:</strong> {{ $classTeacher->stream ? $classTeacher->stream->name : 'N/A' }}</p>
                                        <p class="mb-1"><strong>Level:</strong> {{ $classTeacher->classe->level ?? 'N/A' }}</p>
                                        <p class="mb-1"><strong>Capacity:</strong> {{ $classTeacher->classe->capacity ?? 'N/A' }}</p>
                                        <p class="mb-0"><strong>Description:</strong> {{ $classTeacher->classe->description ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="bx bx-calendar me-1"></i> Academic Year</h6>
                                    <div class="ps-3">
                                        <p class="mb-1"><strong>Year:</strong> {{ $classTeacher->academicYear->name }}</p>
                                        <p class="mb-1"><strong>Start Date:</strong> {{ $classTeacher->academicYear->start_date->format('M d, Y') }}</p>
                                        <p class="mb-1"><strong>End Date:</strong> {{ $classTeacher->academicYear->end_date->format('M d, Y') }}</p>
                                        <p class="mb-0"><strong>Status:</strong>
                                            @if($classTeacher->academicYear->status == 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2"><i class="bx bx-time me-1"></i> Assignment Timeline</h6>
                                    <div class="ps-3">
                                        <p class="mb-1"><strong>Assigned Date:</strong> {{ $classTeacher->created_at->format('M d, Y \a\t h:i A') }}</p>
                                        <p class="mb-1"><strong>Last Updated:</strong> {{ $classTeacher->updated_at->format('M d, Y \a\t h:i A') }}</p>
                                        <p class="mb-0"><strong>Assignment ID:</strong> #{{ $classTeacher->id }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('school.class-teachers.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to Class Teachers
                            </a>
                            <div>
                                <a href="{{ route('school.class-teachers.edit', $classTeacher) }}" class="btn btn-warning me-2">
                                    <i class="bx bx-edit me-1"></i> Edit Assignment
                                </a>
                                @if($classTeacher->is_active)
                                    <a href="{{ route('school.class-teachers.toggle-status', $classTeacher) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to deactivate this assignment?')">
                                        <i class="bx bx-pause me-1"></i> Deactivate
                                    </a>
                                @else
                                    <a href="{{ route('school.class-teachers.toggle-status', $classTeacher) }}" class="btn btn-success" onclick="return confirm('Are you sure you want to activate this assignment?')">
                                        <i class="bx bx-play me-1"></i> Activate
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1 text-info"></i> Class Teacher Role
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <h6>Responsibilities:</h6>
                            <ul class="small text-muted">
                                <li>Class management and discipline</li>
                                <li>Parent-teacher communication</li>
                                <li>Academic progress monitoring</li>
                                <li>Coordinating with subject teachers</li>
                                <li>Student attendance tracking</li>
                                <li>Class event organization</li>
                            </ul>
                        </div>
                        <div class="alert alert-light small">
                            <i class="bx bx-bulb me-1 text-warning"></i>
                            <strong>Note:</strong> The class teacher serves as the primary point of contact for all matters related to this specific class.
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-group me-1 text-primary"></i> Related Information
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <p class="small text-muted mb-2">This teacher may also be assigned to teach specific subjects in this class. Check the subject teachers section for more details.</p>
                            <a href="{{ route('school.subject-teachers.index', ['class_id' => $classTeacher->class_id, 'academic_year_id' => $classTeacher->academic_year_id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-search me-1"></i> View Subject Assignments
                            </a>
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

    .font-22 {
        font-size: 1.375rem !important;
    }

    .badge {
        font-size: 0.75rem;
    }

    .alert-light {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.375rem;
    }

    ul li {
        margin-bottom: 0.25rem;
    }
</style>
@endpush