@extends('layouts.main')

@section('title', 'Assign Class Teacher')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Class Teachers', 'url' => route('school.class-teachers.index'), 'icon' => 'bx bx-user-check'],
            ['label' => 'Assign', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">ASSIGN CLASS TEACHER</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-user-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Assign Teacher to Class</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.class-teachers.store') }}" method="POST" id="assignment-form">
                            @csrf

                            <!-- Assignment Lines Container -->
                            <div id="assignment-lines">
                                <div class="assignment-line mb-4 p-3 border rounded" data-line="1">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 text-primary">
                                            <i class="bx bx-user-plus me-1"></i> Assignment #1
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-line-btn" style="display: none;">
                                            <i class="bx bx-trash me-1"></i> Remove
                                        </button>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
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
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Class <span class="text-danger">*</span></label>
                                                <select class="form-control class-select @error('assignments.0.class_id') is-invalid @enderror"
                                                        name="assignments[0][class_id]" required>
                                                    <option value="">Select a class...</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}">
                                                            {{ $class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('assignments.0.class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Stream <span class="text-danger">*</span></label>
                                                <select class="form-control stream-select @error('assignments.0.stream_id') is-invalid @enderror"
                                                        name="assignments[0][stream_id]" required>
                                                    <option value="">Select a stream...</option>
                                                    @foreach($streams as $stream)
                                                        <option value="{{ $stream->id }}">
                                                            {{ $stream->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('assignments.0.stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">Active</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="assignments[0][is_active]" value="1" checked>
                                                    <label class="form-check-label">Yes</label>
                                                </div>
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
                                <a href="{{ route('school.class-teachers.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Class Teachers
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
                            <h6>What is a Class Teacher?</h6>
                            <p class="small text-muted">
                                A class teacher is responsible for the overall management and supervision of a specific class.
                                They coordinate with subject teachers and ensure smooth academic progress.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Assignment Rules:</h6>
                            <ul class="small text-muted mb-2">
                                <li>One teacher can be assigned to multiple classes</li>
                                <li>One class can have only one class teacher per academic year</li>
                                <li>Assignments are automatically set to the current active academic year</li>
                                <li>Duplicate assignments for the same teacher-class-year combination are prevented</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Batch Assignment:</h6>
                            <p class="small text-muted">
                                You can assign multiple teachers to different classes at once using the "Add Another Assignment" button.
                                Each assignment will be created for the current active academic year.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Responsibilities:</h6>
                            <ul class="small text-muted">
                                <li>Class management and discipline</li>
                                <li>Parent-teacher communication</li>
                                <li>Academic progress monitoring</li>
                                <li>Coordinating with subject teachers</li>
                            </ul>
                        </div>
                        <div class="alert alert-light small">
                            <i class="bx bx-bulb me-1 text-warning"></i>
                            <strong>Tip:</strong> Ensure the selected teacher has appropriate qualifications and experience for class management.
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

    /* Assignment line styling */
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

    /* Add line button styling */
    #add-line-btn {
        border-style: dashed;
        transition: all 0.3s ease;
    }

    #add-line-btn:hover {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }

    /* Remove button styling */
    .remove-line-btn {
        transition: all 0.3s ease;
    }

    .remove-line-btn:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .assignment-line .row > div {
            margin-bottom: 1rem;
        }

        .assignment-line .d-flex.justify-content-between {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let lineCounter = 1;

        // Initialize select2 for the first line
        initializeSelect2();

        // Add line button click handler
        $('#add-line-btn').on('click', function() {
            lineCounter++;
            addAssignmentLine(lineCounter);
            updateRemoveButtons();
        });

        // Remove line button click handler
        $(document).on('click', '.remove-line-btn', function() {
            $(this).closest('.assignment-line').remove();
            updateLineNumbers();
            updateRemoveButtons();
        });

        function addAssignmentLine(lineNumber) {
            const lineHtml = `
                <div class="assignment-line mb-4 p-3 border rounded" data-line="${lineNumber}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-primary">
                            <i class="bx bx-user-plus me-1"></i> Assignment #${lineNumber}
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-line-btn">
                            <i class="bx bx-trash me-1"></i> Remove
                        </button>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
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
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Class <span class="text-danger">*</span></label>
                                <select class="form-control class-select" name="assignments[${lineNumber - 1}][class_id]" required>
                                    <option value="">Select a class...</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Stream <span class="text-danger">*</span></label>
                                <select class="form-control stream-select" name="assignments[${lineNumber - 1}][stream_id]" required>
                                    <option value="">Select a stream...</option>
                                    @foreach($streams as $stream)
                                        <option value="{{ $stream->id }}">
                                            {{ $stream->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Active</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="assignments[${lineNumber - 1}][is_active]" value="1" checked>
                                    <label class="form-check-label">Yes</label>
                                </div>
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
                $(this).attr('data-line', newNumber);
                $(this).find('h6').html(`<i class="bx bx-user-plus me-1"></i> Assignment #${newNumber}`);

                // Update form field names
                $(this).find('.employee-select').attr('name', `assignments[${index}][employee_id]`);
                $(this).find('.class-select').attr('name', `assignments[${index}][class_id]`);
                $(this).find('.stream-select').attr('name', `assignments[${index}][stream_id]`);
                $(this).find('.form-check-input').attr('name', `assignments[${index}][is_active]`);
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
                $('.employee-select, .class-select, .stream-select').each(function() {
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

            // Add event listener for class selection change
            $(document).on('change', '.class-select', function() {
                const classId = $(this).val();
                const $streamSelect = $(this).closest('.assignment-line').find('.stream-select');
                const $line = $(this).closest('.assignment-line');

                if (classId) {
                    // Fetch streams for the selected class
                    $.ajax({
                        url: '{{ route("school.class-teachers.streams", ":id") }}'.replace(':id', classId),
                        type: 'GET',
                        success: function(data) {
                            $streamSelect.empty();
                            $streamSelect.append('<option value="">Select a stream...</option>');
                            if (data.streams && data.streams.length > 0) {
                                data.streams.forEach(function(stream) {
                                    $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                                });
                            }
                            // Trigger change to update select2
                            if (typeof $.fn.select2 !== 'undefined') {
                                $streamSelect.trigger('change');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error loading streams:', error);
                            $streamSelect.empty();
                            $streamSelect.append('<option value="">Select a stream...</option>');
                            if (typeof $.fn.select2 !== 'undefined') {
                                $streamSelect.trigger('change');
                            }
                        }
                    });
                } else {
                    // No class selected, clear streams
                    $streamSelect.empty();
                    $streamSelect.append('<option value="">Select a stream...</option>');
                    if (typeof $.fn.select2 !== 'undefined') {
                        $streamSelect.trigger('change');
                    }
                }
            });
        }

        // Form submission handler
        $('#assignment-form').on('submit', function(e) {
            // Validate that at least one assignment has all required fields
            let hasValidAssignment = false;
            $('.assignment-line').each(function() {
                const employeeId = $(this).find('.employee-select').val();
                const classId = $(this).find('.class-select').val();
                const streamId = $(this).find('.stream-select').val();
                
                if (employeeId && classId && streamId) {
                    hasValidAssignment = true;
                    return false; // break loop
                }
            });

            if (!hasValidAssignment) {
                e.preventDefault();
                alert('Please fill in all required fields (Teacher, Class, and Stream) for at least one assignment.');
                return false;
            }
        });

        console.log('Assign class teacher form loaded with dynamic lines');
    });
</script>
@endpush