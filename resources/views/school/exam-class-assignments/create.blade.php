@extends('layouts.main')

@section('title', 'Assign Class to Exam Type')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Class Assignments', 'url' => route('school.exam-class-assignments.index'), 'icon' => 'bx bx-target-lock'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-info"></i></div>
                            <h5 class="mb-0 text-info">Assign Class to Exam Type</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.exam-class-assignments.store') }}" method="POST" id="assignmentForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="exam_type_id" class="form-label">Exam Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('exam_type_id') is-invalid @enderror"
                                                id="exam_type_id"
                                                name="exam_type_id"
                                                required>
                                            <option value="">Select Exam Type</option>
                                            @foreach($examTypes as $examType)
                                                <option value="{{ $examType->id }}" {{ old('exam_type_id') == $examType->id ? 'selected' : '' }}>
                                                    {{ $examType->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('exam_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Classes Section -->
                                    <div class="mb-3">
                                        <label class="form-label">Classes <span class="text-danger">*</span></label>
                                        <div id="classesContainer">
                                            <div class="class-row mb-2">
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <select class="form-select class-select @error('classes.0') is-invalid @enderror"
                                                                name="classes[0]"
                                                                required>
                                                            <option value="">Select Class</option>
                                                            @foreach($classes as $class)
                                                                <option value="{{ $class->id }}" {{ (old('classes.0') == $class->id) ? 'selected' : '' }}>
                                                                    {{ $class->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button" class="btn btn-danger btn-sm remove-class" style="display: none;">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="addClassBtn">
                                            <i class="bx bx-plus me-1"></i> Add Another Class
                                        </button>
                                        @error('classes.0')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="card-title text-info">
                                                <i class="bx bx-info-circle me-1"></i> Assignment Information
                                            </h6>
                                            <hr />
                                            <ul class="list-unstyled small">
                                                <li class="mb-2">
                                                    <strong>Exam Type:</strong> Select the exam type for this assignment.
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Classes:</strong> Choose one or more classes to assign to all exams of the selected exam type.
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Automatic Assignment:</strong> The system will automatically assign all subjects for each selected class to all exams under the selected exam type.
                                                </li>
                                                <li class="mb-0">
                                                    <strong>Current Academic Year:</strong> Assignments are created for the current academic year.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="card border-warning mt-3">
                                        <div class="card-body">
                                            <h6 class="card-title text-warning">
                                                <i class="bx bx-error me-1"></i> Important Notes
                                            </h6>
                                            <hr />
                                            <ul class="list-unstyled small">
                                                <li class="mb-2">
                                                    Each class will be assigned all its subjects for the selected exam.
                                                </li>
                                                <li class="mb-2">
                                                    Duplicate assignments for the same exam-class-subject combination will be prevented.
                                                </li>
                                                <li class="mb-0">
                                                    You can track assignment progress and view statistics after creation.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <hr />
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('school.exam-class-assignments.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to List
                                        </a>
                                        <button type="submit" class="btn btn-info" id="submitBtn">
                                            <i class="bx bx-save me-1"></i> Create Assignment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px !important;
        padding-left: 12px !important;
        padding-right: 20px !important;
        color: #495057 !important;
        font-size: 0.875rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d !important;
        font-size: 0.875rem !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
        right: 8px !important;
    }
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }
    .select2-dropdown {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        min-width: 100% !important;
    }
    .select2-container--default .select2-results__option {
        padding: 8px 12px !important;
        font-size: 0.875rem !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        padding: 8px 12px !important;
        font-size: 0.875rem !important;
        margin: 8px !important;
        width: calc(100% - 16px) !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
        border-color: #80bdff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff !important;
        color: white !important;
    }
    .select2-container {
        min-width: 100% !important;
        width: 100% !important;
    }
    .select2-container .select2-selection--single {
        min-height: 38px !important;
    }
</style>
@endpush

@push('scripts')
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    let classIndex = 1;

    // Initialize Select2 for existing selects
    initializeSelect2();

    // Add new class row
    $('#addClassBtn').on('click', function() {
        const classRow = `
            <div class="class-row mb-2">
                <div class="row">
                    <div class="col-md-10">
                        <select class="form-select class-select" name="classes[${classIndex}]" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-class">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        $('#classesContainer').append(classRow);

        // Initialize Select2 for the new select
        $('#classesContainer .class-row:last-child .class-select').select2({
            placeholder: 'Search and select a class...',
            allowClear: true,
            width: '100%'
        });

        classIndex++;

        // Show remove button for first class if more than one class
        if ($('.class-row').length > 1) {
            $('.remove-class').show();
        }
    });

    // Remove class row
    $(document).on('click', '.remove-class', function() {
        $(this).closest('.class-row').remove();

        // Hide remove button for first class if only one class remains
        if ($('.class-row').length === 1) {
            $('.remove-class').hide();
        }

        // Re-index the remaining class selects
        $('.class-row').each(function(index) {
            $(this).find('.class-select').attr('name', `classes[${index}]`);
        });
        classIndex = $('.class-row').length;
    });

    // Function to initialize Select2
    function initializeSelect2() {
        // Initialize exam type select
        $('#exam_type_id').select2({
            placeholder: 'Search and select an exam type...',
            allowClear: true,
            width: '100%'
        });

        // Initialize existing class selects
        $('.class-select').each(function() {
            $(this).select2({
                placeholder: 'Search and select a class...',
                allowClear: true,
                width: '100%'
            });
        });
    }

    // Form validation and duplicate checking
    $('#assignmentForm').on('submit', function(e) {
        e.preventDefault(); // Prevent immediate submission

        const examTypeId = $('#exam_type_id').val();
        const classSelects = $('.class-select');
        let hasValidClasses = false;
        const selectedClasses = [];

        if (!examTypeId) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select an exam type.',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        classSelects.each(function() {
            const classId = $(this).val();
            if (classId) {
                hasValidClasses = true;
                if (selectedClasses.includes(classId)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Duplicate classes are not allowed.',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
                selectedClasses.push(classId);
            }
        });

        if (!hasValidClasses) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select at least one class.',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }

        // Show loading state
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Checking for duplicates...');

        // Prepare form data
        const formData = new FormData(this);

        // Check for duplicates first
        $.ajax({
            url: '{{ route("school.exam-class-assignments.check-duplicates") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);

                if (response.success && !response.has_duplicates) {
                    // No duplicates found, proceed with form submission
                    Swal.fire({
                        icon: 'success',
                        title: 'No Duplicates Found',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Proceed with Creation',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Submit the form
                            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Creating assignments...');
                            $('#assignmentForm')[0].submit();
                        }
                    });
                } else if (!response.success && response.has_duplicates) {
                    // Duplicates found, show detailed information
                    let duplicateDetails = '<div class="text-start">';
                    duplicateDetails += `<p><strong>Exam Type:</strong> ${response.details.exam_type}</p>`;
                    duplicateDetails += `<p><strong>Academic Year:</strong> ${response.details.academic_year}</p>`;
                    duplicateDetails += `<p><strong>Total assignments that would be created:</strong> ${response.details.total_assignments_would_create}</p>`;
                    duplicateDetails += `<p><strong>Duplicate assignments found:</strong> ${response.details.total_duplicates_found}</p>`;
                    duplicateDetails += '<hr><p><strong>Duplicate Details:</strong></p><ul>';

                    response.details.duplicates.forEach(function(duplicate) {
                        duplicateDetails += `<li><strong>${duplicate.class_name}:</strong>`;
                        duplicateDetails += '<ul>';
                        duplicate.subjects.forEach(function(subject) {
                            duplicateDetails += `<li>${subject.subject_name}</li>`;
                        });
                        duplicateDetails += '</ul></li>';
                    });
                    duplicateDetails += '</ul></div>';

                    Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Assignments Found!',
                        html: duplicateDetails,
                        showCancelButton: true,
                        confirmButtonColor: '#ffc107',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Proceed Anyway (Duplicates will be skipped)',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Submit the form (duplicates will be handled by backend)
                            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Creating assignments...');
                            $('#assignmentForm')[0].submit();
                        }
                    });
                } else {
                    // Error response
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'An error occurred while checking for duplicates.',
                        confirmButtonColor: '#dc3545'
                    });
                }
            },
            error: function(xhr, status, error) {
                submitBtn.prop('disabled', false).html(originalText);

                let errorMessage = 'An error occurred while checking for duplicates.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonColor: '#dc3545'
                });
            }
        });
    });
});
</script>
@endpush