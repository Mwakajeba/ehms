@extends('layouts.main')

@section('title', 'View Assignment')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Assignments', 'url' => route('school.assignments.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'View Assignment', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">VIEW ASSIGNMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-book-open me-1 font-22 text-primary"></i></div>
                                <div>
                                    <h5 class="mb-0 text-primary">{{ $assignment->title }}</h5>
                                    <small class="text-muted">
                                        Assignment ID: {{ $assignment->assignment_id }}
                                        @if($assignment->academicYear)
                                            | {{ $assignment->academicYear->year_name }}
                                        @endif
                                        @if($assignment->term)
                                            | {{ $assignment->term }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('school.assignments.edit', $assignment->hashid) }}" class="btn btn-warning">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.assignments.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Assignment Information -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">Type</h6>
                                        <p class="mb-0">
                                            @php
                                                $typeBadges = [
                                                    'homework' => 'primary',
                                                    'classwork' => 'info',
                                                    'project' => 'success',
                                                    'revision_task' => 'warning'
                                                ];
                                                $badge = $typeBadges[$assignment->type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ ucwords(str_replace('_', ' ', $assignment->type)) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="card-title text-info">Status</h6>
                                        <p class="mb-0">
                                            @php
                                                $statusBadges = [
                                                    'draft' => 'secondary',
                                                    'published' => 'success',
                                                    'closed' => 'warning',
                                                    'archived' => 'dark'
                                                ];
                                                $badge = $statusBadges[$assignment->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ ucfirst($assignment->status) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="card-title text-success">Subject</h6>
                                        <p class="mb-0">
                                            {{ $assignment->subject ? $assignment->subject->name : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="card-title text-warning">Teacher</h6>
                                        <p class="mb-0">
                                            {{ $assignment->teacher ? ($assignment->teacher->first_name . ' ' . $assignment->teacher->last_name) : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Instructions -->
                        @if($assignment->description)
                        <div class="alert alert-info mb-3">
                            <strong>Description:</strong> {{ $assignment->description }}
                        </div>
                        @endif

                        @if($assignment->instructions)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-list-check me-2"></i>Instructions</h6>
                            </div>
                            <div class="card-body">
                                {!! nl2br(e($assignment->instructions)) !!}
                            </div>
                        </div>
                        @endif

                        <!-- Scheduling Information -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Scheduling</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Date Assigned:</strong><br>
                                        {{ $assignment->date_assigned->format('M d, Y') }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Due Date:</strong><br>
                                        {{ $assignment->due_date->format('M d, Y') }}
                                        @if($assignment->due_time)
                                            at {{ \Carbon\Carbon::parse($assignment->due_time)->format('g:i A') }}
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Estimated Time:</strong><br>
                                        {{ $assignment->estimated_completion_time ? $assignment->estimated_completion_time . ' minutes' : 'N/A' }}
                                    </div>
                                </div>
                                @if($assignment->is_recurring)
                                <div class="mt-2">
                                    <span class="badge bg-info">Recurring Assignment</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Assigned Classes -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-group me-2"></i>Assigned Classes</h6>
                            </div>
                            <div class="card-body">
                                @if($assignment->assignmentClasses->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Class</th>
                                                    <th>Stream</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($assignment->assignmentClasses as $assignmentClass)
                                                <tr>
                                                    <td>{{ $assignmentClass->classe ? $assignmentClass->classe->name : 'N/A' }}</td>
                                                    <td>{{ $assignmentClass->stream ? $assignmentClass->stream->name : 'All Streams' }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted mb-0">No classes assigned.</p>
                                @endif
                            </div>
                        </div>

                        <!-- Submission Settings -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-upload me-2"></i>Submission Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Submission Type:</strong><br>
                                        {{ ucwords(str_replace('_', ' ', $assignment->submission_type)) }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Maximum Attempts:</strong><br>
                                        {{ $assignment->max_attempts }}
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <span class="badge bg-{{ $assignment->resubmission_allowed ? 'success' : 'secondary' }}">
                                            Resubmission {{ $assignment->resubmission_allowed ? 'Allowed' : 'Not Allowed' }}
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="badge bg-{{ $assignment->lock_after_deadline ? 'warning' : 'secondary' }}">
                                            {{ $assignment->lock_after_deadline ? 'Locked After Deadline' : 'Not Locked' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Marking & Assessment -->
                        @if($assignment->total_marks || $assignment->rubric)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-check-square me-2"></i>Marking & Assessment</h6>
                            </div>
                            <div class="card-body">
                                @if($assignment->total_marks)
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>Total Marks:</strong> {{ $assignment->total_marks }}
                                    </div>
                                    @if($assignment->passing_marks)
                                    <div class="col-md-4">
                                        <strong>Passing Marks:</strong> {{ $assignment->passing_marks }}
                                    </div>
                                    @endif
                                    @if($assignment->auto_graded)
                                    <div class="col-md-4">
                                        <span class="badge bg-info">Auto-graded</span>
                                    </div>
                                    @endif
                                </div>
                                @endif
                                @if($assignment->rubric)
                                <div>
                                    <strong>Rubric / Marking Guide:</strong>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        {!! nl2br(e($assignment->rubric)) !!}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Attachments -->
                        @if($assignment->attachments->count() > 0)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-paperclip me-2"></i>Attachments</h6>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    @foreach($assignment->attachments as $attachment)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bx bx-file me-2"></i>
                                            <strong>{{ $attachment->original_name }}</strong>
                                            <small class="text-muted ms-2">({{ $attachment->file_size_human }})</small>
                                        </div>
                                        <a href="{{ $attachment->url }}" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="bx bx-download me-1"></i> Download
                                        </a>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Submissions Summary -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Submissions Summary</h6>
                            </div>
                            <div class="card-body">
                                @php
                                    $totalSubmissions = $assignment->submissions->count();
                                    $submitted = $assignment->submissions->where('status', 'submitted')->count();
                                    $marked = $assignment->submissions->where('status', 'marked')->count();
                                    $late = $assignment->submissions->where('is_late', true)->count();
                                @endphp
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <h4 class="text-primary">{{ $totalSubmissions }}</h4>
                                        <small class="text-muted">Total Submissions</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h4 class="text-success">{{ $submitted }}</h4>
                                        <small class="text-muted">Submitted</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h4 class="text-info">{{ $marked }}</h4>
                                        <small class="text-muted">Marked</small>
                                    </div>
                                    <div class="col-md-3">
                                        <h4 class="text-warning">{{ $late }}</h4>
                                        <small class="text-muted">Late</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Created Info -->
                        <div class="alert alert-light">
                            <small>
                                <strong>Created by:</strong> {{ $assignment->creator ? $assignment->creator->name : 'N/A' }} 
                                on {{ $assignment->created_at->format('M d, Y g:i A') }}
                                @if($assignment->updated_at != $assignment->created_at)
                                    | <strong>Last updated:</strong> {{ $assignment->updated_at->format('M d, Y g:i A') }}
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

