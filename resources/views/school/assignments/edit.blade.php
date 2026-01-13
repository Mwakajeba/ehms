@extends('layouts.main')

@section('title', 'Edit Assignment')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Assignments', 'url' => route('school.assignments.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'Edit Assignment', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT ASSIGNMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Assignment: {{ $assignment->title }}</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.assignments.update', $assignment->hashid) }}" method="POST" id="assignmentForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information -->
                            <h6 class="text-primary mb-3"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $assignment->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            <option value="homework" {{ old('type', $assignment->type) == 'homework' ? 'selected' : '' }}>Homework</option>
                                            <option value="classwork" {{ old('type', $assignment->type) == 'classwork' ? 'selected' : '' }}>Classwork</option>
                                            <option value="project" {{ old('type', $assignment->type) == 'project' ? 'selected' : '' }}>Project</option>
                                            <option value="revision_task" {{ old('type', $assignment->type) == 'revision_task' ? 'selected' : '' }}>Revision Task</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $assignment->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="instructions" class="form-label">Instructions</label>
                                <textarea class="form-control @error('instructions') is-invalid @enderror" id="instructions" name="instructions" rows="4">{{ old('instructions', $assignment->instructions) }}</textarea>
                                @error('instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Academic Setup -->
                            <h6 class="text-primary mb-3 mt-4"><i class="bx bx-book me-2"></i>Academic Setup</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                        <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" {{ old('academic_year_id', $assignment->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                    {{ $year->year_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="term" class="form-label">Term</label>
                                        <select class="form-select @error('term') is-invalid @enderror" id="term" name="term">
                                            <option value="">Select Term</option>
                                            <option value="Term I" {{ old('term', $assignment->term) == 'Term I' ? 'selected' : '' }}>Term I</option>
                                            <option value="Term II" {{ old('term', $assignment->term) == 'Term II' ? 'selected' : '' }}>Term II</option>
                                            <option value="Term III" {{ old('term', $assignment->term) == 'Term III' ? 'selected' : '' }}>Term III</option>
                                        </select>
                                        @error('term')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select class="form-select @error('subject_id') is-invalid @enderror" id="subject_id" name="subject_id" required>
                                            <option value="">Select Subject</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                                    {{ $subject->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="teacher_id" class="form-label">Teacher <span class="text-danger">*</span></label>
                                        <select class="form-select @error('teacher_id') is-invalid @enderror" id="teacher_id" name="teacher_id" required>
                                            <option value="">Select Teacher</option>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" {{ old('teacher_id', $assignment->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('teacher_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Class & Stream Assignment -->
                            <h6 class="text-primary mb-3 mt-4"><i class="bx bx-group me-2"></i>Assign to Classes</h6>
                            <div id="classesContainer">
                                @foreach($assignment->assignmentClasses as $index => $assignmentClass)
                                <div class="class-row mb-3 border p-3 rounded">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <label class="form-label">Class <span class="text-danger">*</span></label>
                                            <select class="form-select class-select" name="classes[{{ $index }}][class_id]" required>
                                                <option value="">Select Class</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ $assignmentClass->class_id == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Stream</label>
                                            <select class="form-select stream-select" name="classes[{{ $index }}][stream_id]" data-class-id="{{ $assignmentClass->class_id }}" data-selected-stream="{{ $assignmentClass->stream_id }}">
                                                <option value="">Select Stream</option>
                                                @if($assignmentClass->stream_id && $assignmentClass->stream)
                                                    <option value="{{ $assignmentClass->stream_id }}" selected>{{ $assignmentClass->stream->name }}</option>
                                                @endif
                                            </select>
                                            <small class="text-muted">Select class first to load streams</small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger w-100 remove-class-row" {{ $assignment->assignmentClasses->count() <= 1 ? 'style="display:none;"' : '' }}>
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn btn-sm btn-secondary mb-3" id="addClassRow">
                                <i class="bx bx-plus me-1"></i> Add Another Class
                            </button>

                            <!-- Scheduling -->
                            <h6 class="text-primary mb-3 mt-4"><i class="bx bx-calendar me-2"></i>Scheduling</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="date_assigned" class="form-label">Date Assigned <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('date_assigned') is-invalid @enderror" id="date_assigned" name="date_assigned" value="{{ old('date_assigned', $assignment->date_assigned->format('Y-m-d')) }}" required>
                                        @error('date_assigned')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', $assignment->due_date->format('Y-m-d')) }}" required>
                                        @error('due_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="due_time" class="form-label">Due Time</label>
                                        <input type="time" class="form-control @error('due_time') is-invalid @enderror" id="due_time" name="due_time" value="{{ old('due_time', $assignment->due_time ? \Carbon\Carbon::parse($assignment->due_time)->format('H:i') : '') }}">
                                        @error('due_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="estimated_completion_time" class="form-label">Estimated Completion Time (minutes)</label>
                                        <input type="number" class="form-control @error('estimated_completion_time') is-invalid @enderror" id="estimated_completion_time" name="estimated_completion_time" value="{{ old('estimated_completion_time', $assignment->estimated_completion_time) }}" min="1">
                                        @error('estimated_completion_time')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring', $assignment->is_recurring) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_recurring">
                                                Recurring Assignment
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submission Settings -->
                            <h6 class="text-primary mb-3 mt-4"><i class="bx bx-upload me-2"></i>Submission Settings</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="submission_type" class="form-label">Submission Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('submission_type') is-invalid @enderror" id="submission_type" name="submission_type" required>
                                            <option value="">Select Type</option>
                                            <option value="written" {{ old('submission_type', $assignment->submission_type) == 'written' ? 'selected' : '' }}>Written (Exercise Book)</option>
                                            <option value="online_upload" {{ old('submission_type', $assignment->submission_type) == 'online_upload' ? 'selected' : '' }}>Online Upload</option>
                                            <option value="photo_upload" {{ old('submission_type', $assignment->submission_type) == 'photo_upload' ? 'selected' : '' }}>Photo Upload</option>
                                        </select>
                                        @error('submission_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="max_attempts" class="form-label">Maximum Attempts</label>
                                        <input type="number" class="form-control @error('max_attempts') is-invalid @enderror" id="max_attempts" name="max_attempts" value="{{ old('max_attempts', $assignment->max_attempts) }}" min="1">
                                        @error('max_attempts')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="resubmission_allowed" name="resubmission_allowed" value="1" {{ old('resubmission_allowed', $assignment->resubmission_allowed) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="resubmission_allowed">
                                                Allow Resubmission
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="lock_after_deadline" name="lock_after_deadline" value="1" {{ old('lock_after_deadline', $assignment->lock_after_deadline) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="lock_after_deadline">
                                                Lock Submission After Deadline
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Marking & Assessment -->
                            <h6 class="text-primary mb-3 mt-4"><i class="bx bx-check-square me-2"></i>Marking & Assessment</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="total_marks" class="form-label">Total Marks</label>
                                        <input type="number" class="form-control @error('total_marks') is-invalid @enderror" id="total_marks" name="total_marks" value="{{ old('total_marks', $assignment->total_marks) }}" step="0.01" min="0">
                                        @error('total_marks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="passing_marks" class="form-label">Passing Marks</label>
                                        <input type="number" class="form-control @error('passing_marks') is-invalid @enderror" id="passing_marks" name="passing_marks" value="{{ old('passing_marks', $assignment->passing_marks) }}" step="0.01" min="0">
                                        @error('passing_marks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="rubric" class="form-label">Rubric / Marking Guide</label>
                                <textarea class="form-control @error('rubric') is-invalid @enderror" id="rubric" name="rubric" rows="4">{{ old('rubric', $assignment->rubric) }}</textarea>
                                @error('rubric')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="auto_graded" name="auto_graded" value="1" {{ old('auto_graded', $assignment->auto_graded) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_graded">
                                        Auto-graded Assignment
                                    </label>
                                </div>
                            </div>

                            <!-- Existing Attachments -->
                            @if($assignment->attachments->count() > 0)
                            <h6 class="text-primary mb-3 mt-4"><i class="bx bx-paperclip me-2"></i>Existing Attachments</h6>
                            <div class="mb-3">
                                @foreach($assignment->attachments as $attachment)
                                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
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
                            @endif

                            <!-- New Attachments -->
                            <h6 class="text-primary mb-3 mt-4"><i class="bx bx-paperclip me-2"></i>Add New Attachments</h6>
                            <div class="mb-3">
                                <label for="attachments" class="form-label">Upload Files</label>
                                <input type="file" class="form-control @error('attachments') is-invalid @enderror" id="attachments" name="attachments[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <small class="text-muted">Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG (Max 10MB per file)</small>
                                @error('attachments')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                    <option value="draft" {{ old('status', $assignment->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $assignment->status) == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="closed" {{ old('status', $assignment->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                    <option value="archived" {{ old('status', $assignment->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('school.assignments.show', $assignment->hashid) }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Assignment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Assignment Information</h6>
                        <hr />
                        <p class="small">
                            <strong>Assignment ID:</strong><br>
                            {{ $assignment->assignment_id }}
                        </p>
                        <p class="small">
                            <strong>Created:</strong><br>
                            {{ $assignment->created_at->format('M d, Y g:i A') }}
                        </p>
                        <p class="small">
                            <strong>Created By:</strong><br>
                            {{ $assignment->creator ? $assignment->creator->name : 'N/A' }}
                        </p>
                        @if($assignment->updated_at != $assignment->created_at)
                        <p class="small">
                            <strong>Last Updated:</strong><br>
                            {{ $assignment->updated_at->format('M d, Y g:i A') }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let classRowCount = {{ $assignment->assignmentClasses->count() }};

        // Add class row
        $('#addClassRow').on('click', function() {
            const newRow = `
                <div class="class-row mb-3 border p-3 rounded">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select class-select" name="classes[${classRowCount}][class_id]" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Stream</label>
                            <select class="form-select stream-select" name="classes[${classRowCount}][stream_id]">
                                <option value="">Select Stream</option>
                            </select>
                            <small class="text-muted">Select class first to load streams</small>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger w-100 remove-class-row">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('#classesContainer').append(newRow);
            classRowCount++;
            updateRemoveButtons();
        });

        // Remove class row
        $(document).on('click', '.remove-class-row', function() {
            $(this).closest('.class-row').remove();
            updateRemoveButtons();
        });

        // Update remove buttons visibility
        function updateRemoveButtons() {
            const rows = $('.class-row').length;
            $('.remove-class-row').toggle(rows > 1);
        }

        // Load streams for existing rows on page load
        $('.stream-select').each(function() {
            const classId = $(this).data('class-id');
            const selectedStreamId = $(this).data('selected-stream');
            const streamSelect = $(this);
            
            if (classId) {
                loadStreamsForClass(classId, streamSelect, selectedStreamId);
            }
        });

        // Load streams for existing rows on page load
        $('.stream-select').each(function() {
            const classId = $(this).data('class-id');
            const selectedStreamId = $(this).data('selected-stream');
            const streamSelect = $(this);
            
            if (classId) {
                loadStreamsForClass(classId, streamSelect, selectedStreamId);
            }
        });

        // Load streams when class is selected
        $(document).on('change', '.class-select', function() {
            const classId = $(this).val();
            const streamSelect = $(this).closest('.class-row').find('.stream-select');
            streamSelect.data('class-id', classId);
            streamSelect.removeData('selected-stream');
            
            if (classId) {
                loadStreamsForClass(classId, streamSelect);
            } else {
                streamSelect.empty();
                streamSelect.append('<option value="">Select Stream</option>');
                streamSelect.prop('disabled', true);
            }
        });

        function loadStreamsForClass(classId, streamSelect, selectedStreamId = null) {
            streamSelect.prop('disabled', true);
            streamSelect.html('<option value="">Loading streams...</option>');
            
            $.ajax({
                url: '{{ route("school.timetables.get-streams") }}',
                type: 'POST',
                data: {
                    class_id: classId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    streamSelect.empty();
                    streamSelect.append('<option value="">Select Stream (Optional)</option>');
                    if (response && response.length > 0) {
                        response.forEach(function(stream) {
                            const selected = (selectedStreamId && stream.id == selectedStreamId) ? 'selected' : '';
                            streamSelect.append('<option value="' + stream.id + '" ' + selected + '>' + stream.name + '</option>');
                        });
                    }
                    streamSelect.prop('disabled', false);
                },
                error: function(xhr) {
                    console.error('Error loading streams:', xhr);
                    streamSelect.empty();
                    streamSelect.append('<option value="">Select Stream (Optional)</option>');
                    streamSelect.prop('disabled', false);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Failed to load streams');
                    }
                }
            });
        }

        // Set minimum due date to date assigned
        $('#date_assigned').on('change', function() {
            const assignedDate = $(this).val();
            if (assignedDate) {
                $('#due_date').attr('min', assignedDate);
            }
        });

        // Validate form
        $('#assignmentForm').on('submit', function(e) {
            const classRows = $('.class-row');
            let hasValidClass = false;

            classRows.each(function() {
                const classId = $(this).find('.class-select').val();
                if (classId) {
                    hasValidClass = true;
                    return false;
                }
            });

            if (!hasValidClass) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Required Fields',
                        text: 'Please select at least one class.'
                    });
                } else {
                    alert('Please select at least one class.');
                }
                return false;
            }
        });

        updateRemoveButtons();
    });
</script>
@endpush

