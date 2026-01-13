@extends('layouts.main')

@section('title', 'Subject Teacher Assignment Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subject Teachers', 'url' => route('school.subject-teachers.index'), 'icon' => 'bx bx-chalkboard'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">SUBJECT TEACHER ASSIGNMENT DETAILS</h6>
        <hr />

        <div class="row">
            <!-- Main Content Area -->
            <div class="col-12 col-lg-8">
                <!-- Assignment Overview Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-chalkboard me-2 font-22 text-primary"></i>
                                <h5 class="mb-0 text-primary d-inline">Assignment Overview</h5>
                            </div>
                            <div>
                                @if($subjectTeacher->is_active)
                                    <span class="badge bg-success">Active Assignment</span>
                                @else
                                    <span class="badge bg-secondary">Inactive Assignment</span>
                                @endif
                            </div>
                        </div>
                        <hr />

                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-card p-3 mb-3 bg-light rounded">
                                    <h6 class="text-primary mb-3"><i class="bx bx-user me-1"></i> Teacher Information</h6>
                                    <p class="mb-2"><strong>{{ $subjectTeacher->employee->first_name }} {{ $subjectTeacher->employee->last_name }}</strong></p>
                                    <p class="mb-1 small text-muted">Employee ID: {{ $subjectTeacher->employee->employee_id }}</p>
                                    <p class="mb-1 small text-muted">Department: {{ $subjectTeacher->employee->department->name ?? 'N/A' }}</p>
                                    <p class="mb-0 small text-muted">Position: {{ $subjectTeacher->employee->position->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card p-3 mb-3 bg-light rounded">
                                    <h6 class="text-primary mb-3"><i class="bx bx-book me-1"></i> Subject Information</h6>
                                    <p class="mb-2"><strong>{{ $subjectTeacher->subject->name }}</strong></p>
                                    <p class="mb-1 small text-muted">Code: {{ $subjectTeacher->subject->code }}</p>
                                    <p class="mb-1 small text-muted">Type: {{ ucfirst($subjectTeacher->subject->subject_type ?? 'N/A') }}</p>
                                    <p class="mb-0 small text-muted">Marks: {{ $subjectTeacher->subject->passing_marks ?? 'N/A' }}/{{ $subjectTeacher->subject->total_marks ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-card p-3 mb-3 bg-light rounded">
                                    <h6 class="text-info mb-3"><i class="bx bx-school me-1"></i> Class</h6>
                                    <p class="mb-2"><strong>{{ $subjectTeacher->classe->name }}</strong></p>
                                    <p class="mb-1 small text-muted">Code: {{ $subjectTeacher->classe->code }}</p>
                                    <p class="mb-0 small text-muted">Level: {{ $subjectTeacher->classe->level ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card p-3 mb-3 bg-light rounded">
                                    <h6 class="text-warning mb-3"><i class="bx bx-branch me-1"></i> Stream</h6>
                                    @if($subjectTeacher->stream)
                                        <p class="mb-2"><strong>{{ $subjectTeacher->stream->name }}</strong></p>
                                        <p class="mb-1 small text-muted">Code: {{ $subjectTeacher->stream->code ?? 'N/A' }}</p>
                                        <p class="mb-0 small text-muted">
                                            @if($subjectTeacher->stream->is_active ?? true)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </p>
                                    @else
                                        <p class="mb-0 small text-muted"><em>No stream assigned</em></p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-card p-3 mb-3 bg-light rounded">
                                    <h6 class="text-success mb-3"><i class="bx bx-calendar me-1"></i> Academic Year</h6>
                                    <p class="mb-2"><strong>{{ $subjectTeacher->academicYear->year_name }}</strong></p>
                                    <p class="mb-1 small text-muted">{{ $subjectTeacher->academicYear->start_date->format('M d, Y') }} - {{ $subjectTeacher->academicYear->end_date->format('M d, Y') }}</p>
                                    <p class="mb-0 small text-muted">
                                        @if($subjectTeacher->academicYear->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assignment Timeline Card -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-time me-1 text-secondary"></i> Assignment Timeline
                        </h6>
                        <hr />
                        <div class="row">
                            <div class="col-md-6">
                                <div class="timeline-item">
                                    <h6 class="text-muted small mb-1">Assigned Date</h6>
                                    <p class="mb-2">{{ $subjectTeacher->created_at->format('M d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="timeline-item">
                                    <h6 class="text-muted small mb-1">Last Updated</h6>
                                    <p class="mb-2">{{ $subjectTeacher->updated_at->format('M d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="timeline-item">
                                    <h6 class="text-muted small mb-1">Assignment ID</h6>
                                    <p class="mb-0"><code>#{{ $subjectTeacher->id }}</code></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-12 col-lg-4">
                <!-- Action Buttons Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-cog me-1 text-primary"></i> Actions
                        </h6>
                        <hr />
                        <div class="d-grid gap-2">
                            <a href="{{ route('school.subject-teachers.edit', $subjectTeacher->hashid) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit Assignment
                            </a>
                            @if($subjectTeacher->is_active)
                                <button type="button" class="btn btn-danger toggle-status-btn" 
                                        data-url="{{ route('school.subject-teachers.toggle-status', $subjectTeacher->hashid) }}"
                                        data-action="deactivate"
                                        data-name="{{ $subjectTeacher->employee->full_name }} ({{ $subjectTeacher->subject->name }} - {{ $subjectTeacher->classe->name }}{{ $subjectTeacher->stream ? ' - ' . $subjectTeacher->stream->name : '' }})">
                                    <i class="bx bx-pause me-1"></i> Deactivate
                                </button>
                            @else
                                <button type="button" class="btn btn-success toggle-status-btn" 
                                        data-url="{{ route('school.subject-teachers.toggle-status', $subjectTeacher->hashid) }}"
                                        data-action="activate"
                                        data-name="{{ $subjectTeacher->employee->full_name }} ({{ $subjectTeacher->subject->name }} - {{ $subjectTeacher->classe->name }}{{ $subjectTeacher->stream ? ' - ' . $subjectTeacher->stream->name : '' }})">
                                    <i class="bx bx-play me-1"></i> Activate
                                </button>
                            @endif
                            <a href="{{ route('school.subject-teachers.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Subject Teacher Role Card -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1 text-info"></i> Subject Teacher Role
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <h6 class="text-muted small mb-2">Key Responsibilities:</h6>
                            <ul class="small text-muted ps-3 mb-3">
                                <li>Deliver subject-specific curriculum</li>
                                <li>Conduct classes and practical sessions</li>
                                <li>Assess and grade student performance</li>
                                <li>Prepare lesson plans and materials</li>
                                <li>Track student progress and provide feedback</li>
                                <li>Maintain subject-related records</li>
                            </ul>
                        </div>
                        <div class="alert alert-info small py-2">
                            <i class="bx bx-bulb me-1"></i>
                            <strong>Focus:</strong> Specialized knowledge delivery in assigned subject area.
                        </div>
                    </div>
                </div>

                <!-- Related Information Card -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-group me-1 text-primary"></i> Related Assignments
                        </h6>
                        <hr />
                        <p class="small text-muted mb-3">This teacher may have additional assignments. Check related records for complete information.</p>

                        <div class="d-grid gap-2">
                            <a href="{{ route('school.class-teachers.index', ['employee_id' => $subjectTeacher->employee_id, 'academic_year_id' => $subjectTeacher->academic_year_id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-search me-1"></i> Class Teacher Assignments
                            </a>
                            <a href="{{ route('school.subject-teachers.index', ['employee_id' => $subjectTeacher->employee_id, 'academic_year_id' => $subjectTeacher->academic_year_id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-search me-1"></i> Other Subject Assignments
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
        color: #495057;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .info-card {
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-color: #dee2e6;
    }

    .info-card h6 {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
    }

    .info-card h6 i {
        margin-right: 0.5rem;
    }

    .info-card p {
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }

    .info-card strong {
        color: #212529;
    }

    .timeline-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }

    .timeline-item h6 {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .timeline-item p {
        margin-bottom: 0;
        font-weight: 500;
        color: #495057;
    }

    .timeline-item code {
        background: #e9ecef;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.85rem;
        color: #495057;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }

    .text-muted {
        color: #6c757d !important;
    }

    .btn {
        border-radius: 0.375rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .d-grid .btn {
        margin-bottom: 0.5rem;
    }

    .d-grid .btn:last-child {
        margin-bottom: 0;
    }

    ul li {
        margin-bottom: 0.25rem;
        line-height: 1.4;
    }

    .card {
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-radius: 0.5rem;
    }

    .card-body {
        padding: 1.5rem;
    }

    .card-title {
        border-bottom: 2px solid #f8f9fa;
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }

    hr {
        margin: 1.5rem 0;
        border-color: #e9ecef;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Subject Teacher Show page loaded');

        // SweetAlert for toggle status confirmation
        $(document).on('click', '.toggle-status-btn', function(e) {
            e.preventDefault();
            var button = $(this);
            var url = button.data('url');
            var assignmentName = button.data('name');
            var action = button.data('action');

            var title, confirmText, confirmButtonColor, icon;

            if (action === 'deactivate') {
                title = 'Deactivate Subject Teacher Assignment?';
                confirmText = 'Yes, deactivate it!';
                confirmButtonColor = '#d33';
                icon = 'warning';
            } else {
                title = 'Activate Subject Teacher Assignment?';
                confirmText = 'Yes, activate it!';
                confirmButtonColor = '#28a745';
                icon = 'question';
            }

            Swal.fire({
                title: title,
                html: 'Are you sure you want to <strong>' + action + '</strong> the assignment for "<strong>' + assignmentName + '</strong>"?<br><br>' +
                      '<small class="text-muted">This will change the teacher\'s assignment status.</small><br><br>' +
                      '<strong class="text-danger">This action will ' + action + ' the assignment immediately.</strong>',
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Create a form and submit it
                    var form = $('<form>', {
                        'method': 'POST',
                        'action': url
                    });

                    // Add CSRF token
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': '{{ csrf_token() }}'
                    }));

                    // Add method spoofing for PATCH
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_method',
                        'value': 'PATCH'
                    }));

                    // Append form to body and submit
                    $('body').append(form);
                    form.submit();
                }
            });
        });
    });
</script>
@endpush