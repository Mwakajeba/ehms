@extends('layouts.main')

@section('title', 'Edit Subject Teacher Assignment')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subject Teachers', 'url' => route('school.subject-teachers.index'), 'icon' => 'bx bx-chalkboard'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT SUBJECT TEACHER ASSIGNMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Update Teacher Assignment</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.subject-teachers.update', $subjectTeacher->hashid) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Hidden field for academic year (assignments are tied to specific academic years) -->
                            <input type="hidden" name="academic_year_id" value="{{ $subjectTeacher->academic_year_id }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="employee_id" class="form-label">Teacher <span class="text-danger">*</span></label>
                                        <select class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                            <option value="">Select a teacher...</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ (old('employee_id') ?? $subjectTeacher->employee_id) == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('employee_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                        <select class="form-control @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                            <option value="">Select a class...</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ (old('class_id') ?? $subjectTeacher->class_id) == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }} ({{ $class->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stream_id" class="form-label">Stream <span class="text-danger">*</span></label>
                                        <select class="form-control @error('stream_id') is-invalid @enderror" id="stream_id" name="stream_id" required>
                                            <option value="">Select a class first...</option>
                                            @foreach($streams as $stream)
                                                <option value="{{ $stream->id }}" {{ (old('stream_id') ?? $subjectTeacher->stream_id) == $stream->id ? 'selected' : '' }}>
                                                    {{ $stream->name }} ({{ $stream->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('stream_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select class="form-control @error('subject_id') is-invalid @enderror" id="subject_id" name="subject_id" required>
                                            <option value="">Select a class first...</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ (old('subject_id') ?? $subjectTeacher->subject_id) == $subject->id ? 'selected' : '' }}>
                                                    {{ $subject->name }} ({{ $subject->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ (old('is_active') ?? $subjectTeacher->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Assignment
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.subject-teachers.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Subject Teachers
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
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1 text-info"></i> Current Assignment
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <strong>Teacher:</strong><br>
                            <span class="text-primary">{{ $subjectTeacher->employee->first_name }} {{ $subjectTeacher->employee->last_name }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Class:</strong><br>
                            <span class="text-primary">{{ $subjectTeacher->classe->name }} ({{ $subjectTeacher->classe->code }})</span>
                        </div>
                        <div class="mb-3">
                            <strong>Stream:</strong><br>
                            <span class="text-primary">{{ $subjectTeacher->stream ? $subjectTeacher->stream->name : 'N/A' }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Subject:</strong><br>
                            <span class="text-primary">{{ $subjectTeacher->subject->name }} ({{ $subjectTeacher->subject->code }})</span>
                        </div>
                        <div class="mb-3">
                            <strong>Academic Year:</strong><br>
                            <span class="text-primary">{{ $subjectTeacher->academicYear->name }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong><br>
                            @if($subjectTeacher->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <strong>Assigned Date:</strong><br>
                            <span class="text-muted">{{ $subjectTeacher->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-bulb me-1 text-warning"></i> Important Notes
                        </h6>
                        <hr />
                        <div class="alert alert-warning small">
                            <strong>Assignment Rules:</strong>
                            <ul class="mb-0 mt-2">
                                <li>One teacher can teach multiple subjects and classes</li>
                                <li>One subject per class and stream can have multiple teachers</li>
                                <li>Assignments are specific to academic years</li>
                                <li>Streams are automatically filtered based on the selected class</li>
                                <li>Subjects are filtered based on class curricula when available</li>
                                <li>Changes affect the current assignment immediately</li>
                            </ul>
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

    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .form-text {
        font-size: 0.875rem;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    /* Custom checkbox styling */
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .form-check-label {
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }

    /* Input focus styling */
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    /* Alert styling */
    .alert-light {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        color: #6c757d;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }

    .badge {
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize select2 for better dropdown experience
        $('#employee_id, #subject_id, #class_id, #stream_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: function() {
                return $(this).data('placeholder');
            }
        });

        // Check for SweetAlert messages
        @if(session('sweetalert'))
            var alertData = @json(session('sweetalert'));
            Swal.fire({
                icon: alertData.type,
                title: alertData.title,
                text: alertData.message,
                confirmButtonColor: alertData.type === 'error' ? '#dc3545' : '#28a745'
            });
        @endif

        // Handle class change to load streams and subjects dynamically
        $('#class_id').on('change', function() {
            var classId = $(this).val();
            var streamSelect = $('#stream_id');
            var subjectSelect = $('#subject_id');

            if (classId) {
                // Show loading state for streams
                streamSelect.html('<option value="">Loading streams...</option>');

                // Show loading state for subjects
                subjectSelect.html('<option value="">Loading subjects...</option>');

                // Make AJAX request to get streams for the selected class
                $.ajax({
                    url: '{{ route("school.subject-teachers.streams", ":classId") }}'.replace(':classId', classId),
                    type: 'GET',
                    success: function(response) {
                        var options = '<option value="">Select a stream...</option>';

                        if (response.streams && response.streams.length > 0) {
                            response.streams.forEach(function(stream) {
                                options += '<option value="' + stream.id + '">' + stream.name + '</option>';
                            });
                        } else {
                            options = '<option value="">No streams available for this class</option>';
                        }

                        streamSelect.html(options);
                        // Reinitialize select2
                        streamSelect.select2({
                            theme: 'bootstrap-5',
                            width: '100%'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading streams:', error);
                        streamSelect.html('<option value="">Error loading streams</option>');
                    }
                });

                // Make AJAX request to get subjects for the selected class
                $.ajax({
                    url: '{{ route("school.subject-teachers.subjects", ":classId") }}'.replace(':classId', classId),
                    type: 'GET',
                    success: function(response) {
                        var options = '<option value="">Select a subject...</option>';

                        if (response.subjects && response.subjects.length > 0) {
                            response.subjects.forEach(function(subject) {
                                options += '<option value="' + subject.id + '">' + subject.name + ' (' + subject.code + ')</option>';
                            });
                        } else {
                            options = '<option value="">No subjects available for this class</option>';
                        }

                        subjectSelect.html(options);
                        // Reinitialize select2
                        subjectSelect.select2({
                            theme: 'bootstrap-5',
                            width: '100%'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading subjects:', error);
                        subjectSelect.html('<option value="">Error loading subjects</option>');
                    }
                });
            } else {
                // Reset stream select when no class is selected
                streamSelect.html('<option value="">Select a class first...</option>');
                streamSelect.select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });

                // Reset subject select when no class is selected
                subjectSelect.html('<option value="">Select a class first...</option>');
                subjectSelect.select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        });

        // Check for duplicates before form submission
        $('form').on('submit', function(e) {
            e.preventDefault(); // Prevent immediate submission

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();

            // Show loading state
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Checking...');

            // Collect form data
            var formData = {
                employee_id: $('#employee_id').val(),
                subject_id: $('#subject_id').val(),
                class_id: $('#class_id').val(),
                stream_id: $('#stream_id').val(),
                academic_year_id: $('#academic_year_id').val() || '{{ $subjectTeacher->academic_year_id }}',
                exclude_id: '{{ $subjectTeacher->id }}',
                _token: '{{ csrf_token() }}'
            };

            // Check for duplicates
            $.ajax({
                url: '{{ route("school.subject-teachers.check-duplicate") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.duplicate_teacher) {
                        // Same teacher already assigned
                        submitBtn.prop('disabled', false).html(originalText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Duplicate Assignment',
                            text: 'This teacher is already assigned to this subject, class, and stream combination.'
                        });
                        return;
                    }

                    if (response.duplicate_assignment) {
                        // Different teacher already assigned
                        submitBtn.prop('disabled', false).html(originalText);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Assignment Already Exists',
                            html: 'Another teacher (<strong>' + response.existing_teacher + '</strong>) is already assigned to this subject, class, and stream combination.<br><br>Do you want to proceed with this change?',
                            showCancelButton: true,
                            confirmButtonColor: '#ffc107',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, proceed',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // User confirmed, submit the form
                                submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Updating...');
                                form.off('submit').submit(); // Remove this handler and submit
                            }
                        });
                        return;
                    }

                    // No duplicates found, proceed with submission
                    submitBtn.html('<i class="bx bx-loader-alt bx-spin me-1"></i> Updating...');
                    form.off('submit').submit(); // Remove this handler and submit
                },
                error: function(xhr, status, error) {
                    console.error('Error checking duplicates:', error);
                    submitBtn.prop('disabled', false).html(originalText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to check for duplicates. Please try again.'
                    });
                }
            });
        });

        console.log('Edit subject teacher form loaded');
    });
</script>
@endpush