@extends('layouts.main')

@section('title', 'Assign Subject Teacher')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subject Teachers', 'url' => route('school.subject-teachers.index'), 'icon' => 'bx bx-chalkboard'],
            ['label' => 'Assign', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">ASSIGN SUBJECT TEACHER</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-chalkboard me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Assign Teacher to Subject</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.subject-teachers.store') }}" method="POST" id="assignment-form">
                            @csrf

                            <!-- Class and Stream Selection (First Row) -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                        <select class="form-control class-select @error('class_id') is-invalid @enderror" 
                                                id="class_id" 
                                                name="class_id" 
                                                required>
                                            <option value="">Select a class...</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stream_id" class="form-label">Stream <span class="text-danger">*</span></label>
                                        <select class="form-control stream-select @error('stream_id') is-invalid @enderror" 
                                                id="stream_id" 
                                                name="stream_id" 
                                                required>
                                            <option value="">Select a class first...</option>
                                        </select>
                                        @error('stream_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Teacher and Subject Assignments Container -->
                            <div id="assignment-lines">
                                <div class="assignment-line mb-4 p-3 border rounded" data-line="0">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-primary">
                                            <i class="bx bx-user-plus me-1"></i> Assignment #1
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-line-btn" style="display: none;">
                                            <i class="bx bx-trash me-1"></i> Remove
                                        </button>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Teacher <span class="text-danger">*</span></label>
                                                <select class="form-control employee-select @error('assignments.0.employee_id') is-invalid @enderror"
                                                        name="assignments[0][employee_id]" required>
                                                    <option value="">Select a teacher...</option>
                                                    @foreach($employees as $employee)
                                                        <option value="{{ $employee->id }}">
                                                            {{ $employee->first_name }} {{ $employee->last_name }}@if($employee->employee_number) ({{ $employee->employee_number }})@endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('assignments.0.employee_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                                <select class="form-control subject-select @error('assignments.0.subject_id') is-invalid @enderror"
                                                        name="assignments[0][subject_id]" required>
                                                    <option value="">Select a class first...</option>
                                                </select>
                                                @error('assignments.0.subject_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Line Button -->
                            <div class="mb-4">
                                <button type="button" class="btn btn-outline-primary" id="add-line-btn">
                                    <i class="bx bx-plus me-1"></i> Add Another Assignment
                                </button>
                            </div>

                            @error('assignments')
                                <div class="alert alert-danger">
                                    <i class="bx bx-error-circle me-1"></i>
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.subject-teachers.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Subject Teachers
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Assign Teachers
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
                            <i class="bx bx-info-circle me-1 text-info"></i> Information
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <h6>What is a Subject Teacher?</h6>
                            <p class="small text-muted">
                                A subject teacher is responsible for teaching a specific subject to students in a particular class and stream.
                                They deliver curriculum content and assess student performance in their subject area.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Assignment Rules:</h6>
                            <ul class="small text-muted mb-2">
                                <li>Select a class and stream for all assignments</li>
                                <li>Add multiple teacher/subject combinations</li>
                                <li>One teacher can teach multiple subjects</li>
                                <li>One subject can have multiple teachers (different periods)</li>
                                <li>Assignments automatically use the current active academic year</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Responsibilities:</h6>
                            <ul class="small text-muted">
                                <li>Subject-specific teaching</li>
                                <li>Curriculum delivery</li>
                                <li>Student assessment and grading</li>
                                <li>Lesson planning and preparation</li>
                            </ul>
                        </div>
                        <div class="alert alert-light small">
                            <i class="bx bx-bulb me-1 text-warning"></i>
                            <strong>Tip:</strong> You can add multiple teacher/subject assignments for the same class and stream at once.
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

    .font-22 {
        font-size: 1.375rem !important;
    }

    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

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

    .assignment-line {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6 !important;
        transition: all 0.3s ease;
    }

    .assignment-line:hover {
        border-color: #0d6efd !important;
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.1);
    }

    .assignment-line .card-title {
        color: #0d6efd !important;
        margin-bottom: 0;
    }

    #add-line-btn {
        border-style: dashed;
        transition: all 0.3s ease;
    }

    #add-line-btn:hover {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }

    .remove-line-btn {
        transition: all 0.3s ease;
    }

    .remove-line-btn:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let lineCounter = 1;
        let selectedClassId = null;
        let subjectsData = [];

        // Initialize select2 for the first row
        initializeSelect2();

        // Handle class change to load streams and subjects
        $('#class_id').on('change', function() {
            selectedClassId = $(this).val();
            
            if (selectedClassId) {
                // Load streams
                loadStreams(selectedClassId);
                
                // Load subjects
                loadSubjects(selectedClassId);
            } else {
                // Reset streams and subjects
                $('#stream_id').html('<option value="">Select a class first...</option>');
                if (typeof $.fn.select2 !== 'undefined') {
                    $('#stream_id').trigger('change');
                }
                
                $('.subject-select').html('<option value="">Select a class first...</option>');
                $('.subject-select').each(function() {
                    if (typeof $.fn.select2 !== 'undefined') {
                        $(this).trigger('change');
                    }
                });
            }
        });

        // Add line button click handler
        $('#add-line-btn').on('click', function() {
            lineCounter++;
            addAssignmentLine(lineCounter);
            updateRemoveButtons();
            
            // If subjects are already loaded, populate the new subject select
            if (subjectsData.length > 0) {
                const $newSubjectSelect = $('.assignment-line[data-line="' + (lineCounter - 1) + '"]').find('.subject-select');
                populateSubjectSelect($newSubjectSelect);
            }
        });

        // Remove line button click handler
        $(document).on('click', '.remove-line-btn', function() {
            $(this).closest('.assignment-line').remove();
            updateLineNumbers();
            updateRemoveButtons();
        });

        function loadStreams(classId) {
            $.ajax({
                url: '{{ route("school.subject-teachers.streams", ":classId") }}'.replace(':classId', classId),
                type: 'GET',
                success: function(response) {
                    var options = '<option value="">Select a stream...</option>';
                    if (response.streams && response.streams.length > 0) {
                        response.streams.forEach(function(stream) {
                            options += '<option value="' + stream.id + '">' + stream.name + '</option>';
                        });
                    }
                    $('#stream_id').html(options);
                    if (typeof $.fn.select2 !== 'undefined') {
                        $('#stream_id').select2({
                            theme: 'bootstrap-5',
                            width: '100%'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading streams:', error);
                    $('#stream_id').html('<option value="">Error loading streams</option>');
                }
            });
        }

        function loadSubjects(classId) {
            $.ajax({
                url: '{{ route("school.subject-teachers.subjects", ":classId") }}'.replace(':classId', classId),
                type: 'GET',
                success: function(response) {
                    subjectsData = response.subjects || [];
                    
                    // Populate all existing subject selects
                    $('.subject-select').each(function() {
                        populateSubjectSelect($(this));
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error loading subjects:', error);
                    subjectsData = [];
                    $('.subject-select').html('<option value="">Error loading subjects</option>');
                }
            });
        }

        function populateSubjectSelect($select) {
            var options = '<option value="">Select a subject...</option>';
            if (subjectsData.length > 0) {
                subjectsData.forEach(function(subject) {
                    var subjectName = subject.name;
                    if (subject.code) {
                        subjectName += ' (' + subject.code + ')';
                    }
                    options += '<option value="' + subject.id + '">' + subjectName + '</option>';
                });
            } else {
                options = '<option value="">No subjects available for this class</option>';
            }
            $select.html(options);
            if (typeof $.fn.select2 !== 'undefined') {
                $select.select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }
        }

        function addAssignmentLine(lineNumber) {
            const lineHtml = `
                <div class="assignment-line mb-4 p-3 border rounded" data-line="${lineNumber - 1}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-primary">
                            <i class="bx bx-user-plus me-1"></i> Assignment #${lineNumber}
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-line-btn">
                            <i class="bx bx-trash me-1"></i> Remove
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Teacher <span class="text-danger">*</span></label>
                                <select class="form-control employee-select" name="assignments[${lineNumber - 1}][employee_id]" required>
                                    <option value="">Select a teacher...</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">
                                            {{ $employee->first_name }} {{ $employee->last_name }}@if($employee->employee_number) ({{ $employee->employee_number }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-control subject-select" name="assignments[${lineNumber - 1}][subject_id]" required>
                                    <option value="">Select a class first...</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#assignment-lines').append(lineHtml);
            initializeSelect2();
        }

        function updateLineNumbers() {
            $('.assignment-line').each(function(index) {
                const newNumber = index + 1;
                $(this).attr('data-line', index);
                $(this).find('h6').html(`<i class="bx bx-user-plus me-1"></i> Assignment #${newNumber}`);

                // Update form field names
                $(this).find('.employee-select').attr('name', `assignments[${index}][employee_id]`);
                $(this).find('.subject-select').attr('name', `assignments[${index}][subject_id]`);
            });
            lineCounter = $('.assignment-line').length;
        }

        function updateRemoveButtons() {
            const lineCount = $('.assignment-line').length;
            if (lineCount > 1) {
                $('.remove-line-btn').show();
            } else {
                $('.remove-line-btn').hide();
            }
        }

        function initializeSelect2() {
            // Only initialize Select2 if it's available
            if (typeof $.fn.select2 !== 'undefined') {
                $('.employee-select, .class-select, .stream-select, .subject-select').each(function() {
                    if (!$(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: function() {
                                return $(this).find('option:first').text();
                            },
                            allowClear: true
                        });
                    }
                });
            }
        }

        // Form submission handler
        $('#assignment-form').on('submit', function(e) {
            // Validate class and stream are selected
            const classId = $('#class_id').val();
            const streamId = $('#stream_id').val();

            if (!classId || !streamId) {
                e.preventDefault();
                alert('Please select both Class and Stream.');
                return false;
            }

            // Validate that at least one assignment has all required fields
            let hasValidAssignment = false;
            $('.assignment-line').each(function() {
                const employeeId = $(this).find('.employee-select').val();
                const subjectId = $(this).find('.subject-select').val();
                
                if (employeeId && subjectId) {
                    hasValidAssignment = true;
                    return false; // break loop
                }
            });

            if (!hasValidAssignment) {
                e.preventDefault();
                alert('Please fill in all required fields (Teacher and Subject) for at least one assignment.');
                return false;
            }
        });

        console.log('Assign subject teacher form loaded with dynamic lines');
    });
</script>
@endpush
