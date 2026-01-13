@extends('layouts.main')

@section('title', 'Record New Student Transfer')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Record Transfer', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">RECORD NEW STUDENT TRANSFER</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Record New Student Transfer</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.student-transfers.store') }}" method="POST" enctype="multipart/form-data" id="transferForm">
                            @csrf

                            <!-- Transfer Type Selection -->
                            <div class="card border-primary mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-transfer me-2"></i> Transfer Type
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Select Transfer Type <span class="text-danger">*</span></label>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="transfer_type" id="transfer_out" value="transfer_out" {{ old('transfer_type', 'transfer_out') == 'transfer_out' ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="transfer_out">
                                                                <i class="bx bx-log-out text-danger me-1"></i>
                                                                <strong>Transfer Out</strong>
                                                                <br><small class="text-muted">Student leaving this school</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="transfer_type" id="re_admission" value="re_admission" {{ old('transfer_type') == 're_admission' ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="re_admission">
                                                                <i class="bx bx-refresh text-warning me-1"></i>
                                                                <strong>Re-admission</strong>
                                                                <br><small class="text-muted">Student returning after absence</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                @error('transfer_type')
                                                    <div class="text-danger mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Student Selection Section -->
                            <div class="card border-info mb-4" id="studentSection">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-user me-2"></i> Student Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12" id="studentSelectSection">
                                            <div class="mb-3">
                                                <label for="student_id" class="form-label fw-bold">Select Student <span class="text-danger">*</span></label>
                                                <select class="form-select @error('student_id') is-invalid @enderror" id="student_id" name="student_id">
                                                    <option value="">Select Student</option>
                                                    @foreach($availableStudents as $student)
                                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                            {{ $student->admission_number }} - {{ $student->first_name }} {{ $student->last_name }}
                                                            @if($student->status === 'transferred_out')
                                                                (Transferred Out)
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('student_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Transfer Details Section -->
                            <div class="card border-warning mb-4" id="transferDetailsSection">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-file me-2"></i> Transfer Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="transfer_date" class="form-label fw-bold">Transfer Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('transfer_date') is-invalid @enderror"
                                                       id="transfer_date" name="transfer_date" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
                                                @error('transfer_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="transfer_certificate_number" class="form-label fw-bold">Transfer Certificate Number</label>
                                                <input type="text" class="form-control @error('transfer_certificate_number') is-invalid @enderror"
                                                       id="transfer_certificate_number" name="transfer_certificate_number" value="{{ old('transfer_certificate_number') }}"
                                                       placeholder="TC/2024/001">
                                                @error('transfer_certificate_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6" id="fromSchoolSection">
                                            <div class="mb-3">
                                                <label for="previous_school" class="form-label fw-bold">From School <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('previous_school') is-invalid @enderror"
                                                       id="previous_school" name="previous_school" value="{{ old('previous_school') }}"
                                                       placeholder="Previous school name">
                                                @error('previous_school')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="toSchoolSection">
                                            <div class="mb-3">
                                                <label for="new_school" class="form-label fw-bold">To School <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('new_school') is-invalid @enderror"
                                                       id="new_school" name="new_school" value="{{ old('new_school') }}"
                                                       placeholder="New school name">
                                                @error('new_school')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Class/Stream Assignment for Re-admission -->
                                    <div class="row" id="classAssignmentSection" style="display: none;">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="new_class_id" class="form-label fw-bold">Assign to Class <span class="text-danger">*</span></label>
                                                <select class="form-select @error('new_class_id') is-invalid @enderror" id="new_class_id" name="new_class_id">
                                                    <option value="">Select Class</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ old('new_class_id') == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('new_class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="new_stream_id" class="form-label fw-bold">Assign to Stream <span class="text-danger">*</span></label>
                                                <select class="form-select @error('new_stream_id') is-invalid @enderror" id="new_stream_id" name="new_stream_id">
                                                    <option value="">Select Stream</option>
                                                    @foreach($streams as $stream)
                                                        <option value="{{ $stream->id }}" {{ old('new_stream_id') == $stream->id ? 'selected' : '' }}>
                                                            {{ $stream->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('new_stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="reason" class="form-label fw-bold">Reason for Transfer</label>
                                                <select class="form-select @error('reason') is-invalid @enderror" id="reason" name="reason">
                                                    <option value="">Select Reason</option>
                                                    <option value="parent_relocation" {{ old('reason') == 'parent_relocation' ? 'selected' : '' }}>Parent Relocation</option>
                                                    <option value="better_facilities" {{ old('reason') == 'better_facilities' ? 'selected' : '' }}>Better Facilities</option>
                                                    <option value="academic_performance" {{ old('reason') == 'academic_performance' ? 'selected' : '' }}>Academic Performance</option>
                                                    <option value="financial_reasons" {{ old('reason') == 'financial_reasons' ? 'selected' : '' }}>Financial Reasons</option>
                                                    <option value="personal_reasons" {{ old('reason') == 'personal_reasons' ? 'selected' : '' }}>Personal Reasons</option>
                                                    <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                @error('reason')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="academic_year_id" class="form-label fw-bold">Academic Year</label>
                                                <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id">
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $currentAcademicYear ? $currentAcademicYear->id : '') == $year->id ? 'selected' : '' }}>
                                                            {{ $year->year_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('academic_year_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="notes" class="form-label fw-bold">Additional Notes</label>
                                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                                          id="notes" name="notes" rows="3" placeholder="Any additional information about the transfer...">{{ old('notes') }}</textarea>
                                                @error('notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Records Section -->
                            <div class="card border-success mb-4" id="academicRecordsSection">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-book me-2"></i> Academic Records
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="academic_records" class="form-label fw-bold">Academic Performance Summary</label>
                                                <textarea class="form-control @error('academic_records') is-invalid @enderror"
                                                          id="academic_records" name="academic_records" rows="4"
                                                          placeholder="Enter student's academic performance, grades, achievements, etc.">{{ old('academic_records') }}</textarea>
                                                @error('academic_records')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Include grades, achievements, special notes, or any other relevant academic information.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Document Upload Section -->
                            <div class="card border-secondary mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-file me-2"></i> Transfer Documents
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="transfer_certificate" class="form-label fw-bold">Transfer Certificate</label>
                                                <input type="file" class="form-control @error('transfer_certificate') is-invalid @enderror"
                                                       id="transfer_certificate" name="transfer_certificate" accept=".pdf,.jpg,.jpeg,.png">
                                                @error('transfer_certificate')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Upload transfer certificate (PDF, JPG, PNG, max 5MB)
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="academic_report" class="form-label fw-bold">Academic Report</label>
                                                <input type="file" class="form-control @error('academic_report') is-invalid @enderror"
                                                       id="academic_report" name="academic_report" accept=".pdf,.jpg,.jpeg,.png">
                                                @error('academic_report')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Upload academic report or transcript (PDF, JPG, PNG, max 5MB)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                                                <i class="bx bx-refresh me-1"></i> Reset Form
                                            </button>
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="bx bx-save me-2"></i> Record Transfer
                                            </button>
                                        </div>
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
<style>
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-text {
        font-size: 0.875rem;
    }

    h6 {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
    }

    /* Enhanced Card Styling */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.75rem;
        transition: box-shadow 0.15s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        font-weight: 600;
        background-color: #f8f9fa !important;
        color: #495057 !important;
    }

    /* Section-specific header backgrounds */
    .border-primary .card-header { background-color: #f8f9fa !important; }
    .border-info .card-header { background-color: #f8f9fa !important; }
    .border-warning .card-header { background-color: #f8f9fa !important; }
    .border-success .card-header { background-color: #f8f9fa !important; }

    .card-body {
        padding: 1.5rem;
    }

    /* Form Field Enhancements */
    .form-label {
        margin-bottom: 0.5rem;
        color: #495057;
        font-weight: 500;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Radio button styling */
    .form-check {
        padding: 1rem;
        border-radius: 0.5rem;
        border: 2px solid transparent;
        transition: all 0.15s ease-in-out;
        cursor: pointer;
    }

    .form-check:hover {
        border-color: #dee2e6;
        background-color: #f8f9fa;
    }

    .form-check-input:checked ~ .form-check-label {
        font-weight: 600;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    /* Alert Styling */
    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.gap-2 {
            justify-content: center;
        }

        .form-check {
            margin-bottom: 0.5rem;
        }
    }

    /* Icon Styling */
    .bx {
        font-size: 1.1em;
    }

    /* Required Field Indicator */
    .text-danger {
        font-weight: bold;
    }

    /* Form Text Enhancement */
    .form-text {
        font-style: italic;
        margin-top: 0.25rem;
    }

    /* Card Border Colors - Subtle */
    .border-primary { border-color: #e3f2fd !important; }
    .border-info { border-color: #e0f2fe !important; }
    .border-warning { border-color: #fff3cd !important; }
    .border-success { border-color: #d1edff !important; }
    .border-secondary { border-color: #f8f9fa !important; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Student transfer form loaded');

    // Initialize Select2 for all select elements
    initializeSelect2Elements();

    // Initialize form validation
    initializeFormValidation();

    // Handle transfer type change
    $('input[name="transfer_type"]').on('change', function() {
        const transferType = $(this).val();
        console.log('Transfer type changed to:', transferType);
        updateFormForTransferType(transferType);
    });

    // Initialize form based on current transfer type
    const initialTransferType = $('input[name="transfer_type"]:checked').val() || 'transfer_out';
    updateFormForTransferType(initialTransferType);

    // Initialize stream select state (disabled by default)
    $('#new_stream_id').html('<option value="">Select Class First</option>');
    $('#new_stream_id').prop('disabled', true);

    // File upload validation
    $('#transfer_certificate, #academic_report').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // MB
            if (fileSize > 5) {
                showToast('File size must be less than 5MB', 'error');
                $(this).val('');
                return;
            }

            const validTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                showToast('Please select a valid file (PDF, JPG, PNG)', 'error');
                $(this).val('');
                return;
            }

            showToast('File selected successfully', 'success');
        }
    });
});

    // Initialize Select2 elements
    function initializeSelect2Elements() {
        // Initialize Select2 for student selection
        $('#student_id').select2({
            placeholder: 'Search and select a student...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });

        // Initialize other Select2 elements
        $('#reason, #academic_year_id, #new_class_id, #new_stream_id').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });

        // Add event listener for class selection change
        $('#new_class_id').on('change', function() {
            const selectedClassId = $(this).val();
            if (selectedClassId) {
                loadStreamsForClass(selectedClassId);
            } else {
                // Reset stream select when no class is selected
                $('#new_stream_id').html('<option value="">Select Class First</option>');
                $('#new_stream_id').prop('disabled', true);
                $('#new_stream_id').select2({
                    placeholder: 'Select Class First',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    minimumInputLength: 0
                });
            }
        });
    }

    // Function to load streams for a class
    function loadStreamsForClass(classId) {
        const streamSelect = $('#new_stream_id');

        // Show loading state
        streamSelect.html('<option value="">Loading streams...</option>');
        streamSelect.prop('disabled', true);

        // Make AJAX call to get streams for this class
        $.ajax({
            url: '{{ route("school.api.students.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                let options = '<option value="">Select Stream</option>';

                if (response.streams && response.streams.length > 0) {
                    response.streams.forEach(function(stream) {
                        options += `<option value="${stream.id}">${stream.name}</option>`;
                    });
                    streamSelect.prop('disabled', false);
                    showToast(`Loaded ${response.streams.length} stream(s) for this class`, 'success');
                } else {
                    options = '<option value="">No streams available for this class</option>';
                    streamSelect.prop('disabled', true);
                    showToast('No streams found for this class', 'warning');
                }

                streamSelect.html(options);
                
                // Re-initialize Select2 after populating options
                streamSelect.select2({
                    placeholder: 'Select a stream...',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    minimumInputLength: 0
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.html('<option value="">Error loading streams</option>');
                streamSelect.prop('disabled', true);
                showToast('Error loading streams. Please try again.', 'error');
            }
        });
    }// Function to update form based on transfer type
function updateFormForTransferType(transferType) {
    const studentSelectSection = $('#studentSelectSection');
    const newStudentSection = $('#newStudentSection');
    const fromSchoolSection = $('#fromSchoolSection');
    const toSchoolSection = $('#toSchoolSection');
    const classAssignmentSection = $('#classAssignmentSection');

    // Reset all sections
    studentSelectSection.show();
    newStudentSection.hide();
    fromSchoolSection.show();
    toSchoolSection.show();
    classAssignmentSection.hide();

    // Update labels and requirements based on transfer type
    switch(transferType) {
        case 'transfer_out':
            // Student leaving this school - select existing student
            $('#student_id').attr('required', true);
            $('#student_name').attr('required', false);
            $('#previous_school').attr('placeholder', 'This school name').val('{{ config("app.name") }}');
            $('#new_school').attr('placeholder', 'New school name').val('');
            $('#previous_school').closest('.mb-3').find('label').html('From School <span class="text-danger">*</span>');
            $('#new_school').closest('.mb-3').find('label').html('To School <span class="text-danger">*</span>');
            $('#new_class_id, #new_stream_id').attr('required', false);
            // Load active students
            loadStudentsForTransferType('transfer_out');
            break;

        case 're_admission':
            // Student returning - select transferred out student
            $('#student_id').attr('required', true);
            $('#student_name').attr('required', false);
            $('#previous_school').attr('placeholder', 'Previous school/break period').val('Extended Break/Absence');
            $('#new_school').attr('placeholder', 'This school name').val('{{ config("app.name") }}');
            $('#previous_school').closest('.mb-3').find('label').html('From (Previous Status)');
            $('#new_school').closest('.mb-3').find('label').html('To School <span class="text-danger">*</span>');
            $('#new_class_id, #new_stream_id').attr('required', true);
            classAssignmentSection.show();
            // Load transferred out students
            loadStudentsForTransferType('re_admission');
            // Initialize stream select for re-admission
            $('#new_stream_id').html('<option value="">Select Class First</option>');
            $('#new_stream_id').prop('disabled', true);
            break;
    }
}

// Function to load students based on transfer type
function loadStudentsForTransferType(transferType) {
    $.ajax({
        url: '{{ route("school.student-transfers.get-students") }}',
        type: 'GET',
        data: { transfer_type: transferType },
        success: function(response) {
            const studentSelect = $('#student_id');
            studentSelect.empty();
            studentSelect.append('<option value="">Select Student</option>');

            response.students.forEach(function(student) {
                const statusText = student.status === 'transferred_out' ? ' (Transferred Out)' : '';
                const option = `<option value="${student.id}">${student.admission_number} - ${student.first_name} ${student.last_name}${statusText}</option>`;
                studentSelect.append(option);
            });

            // Reinitialize Select2
            studentSelect.trigger('change');
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr, status, error);
            let errorMessage = 'Error loading students';
            
            if (xhr.status === 404) {
                errorMessage = 'Students endpoint not found';
            } else if (xhr.status === 403) {
                errorMessage = 'Access denied to students data';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error while loading students';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            showToast(errorMessage, 'error');
        }
    });
}

// Form validation initialization
function initializeFormValidation() {
    $('#transferForm').on('submit', function(e) {
        console.log('Form submission started');
        let isValid = true;
        const transferType = $('input[name="transfer_type"]:checked').val();
        console.log('Transfer type:', transferType);

        // Validate transfer type
        if (!transferType) {
            showToast('Please select a transfer type', 'error');
            console.log('Transfer type validation failed');
            isValid = false;
        }

        // Validate student selection (always required for transfer_out and re_admission)
        const studentId = $('#student_id').val();
        console.log('Student ID:', studentId);
        if (!studentId) {
            $('#student_id').addClass('is-invalid');
            console.log('Student ID validation failed');
            isValid = false;
        } else {
            $('#student_id').removeClass('is-invalid');
        }

        // Validate required fields based on transfer type
        const requiredFields = ['transfer_date'];
        if (transferType === 'transfer_out') {
            requiredFields.push('new_school');
        } else if (transferType === 're_admission') {
            requiredFields.push('previous_school', 'new_school', 'new_class_id', 'new_stream_id');
        }

        console.log('Required fields for', transferType, ':', requiredFields);

        requiredFields.forEach(field => {
            const element = $('#' + field);
            const value = element.val();
            console.log('Field', field, 'value:', value);
            if (!value) {
                element.addClass('is-invalid');
                console.log('Field', field, 'validation failed');
                isValid = false;
            } else {
                element.removeClass('is-invalid');
            }
        });

        console.log('Form validation result:', isValid);

        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields', 'error');
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        } else {
            console.log('Form validation passed, submitting...');
        }
    });
}

// Reset form function
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        $('#transferForm')[0].reset();

        // Reset all Select2 elements
        $('#student_id, #reason, #academic_year_id, #new_class_id, #new_stream_id').val('').trigger('change');

        // Reset stream select to disabled state
        $('#new_stream_id').html('<option value="">Select Class First</option>');
        $('#new_stream_id').prop('disabled', true);

        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();

        // Reset to default transfer type
        $('#transfer_out').prop('checked', true);
        updateFormForTransferType('transfer_out');

        showToast('Form has been reset', 'info');
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastColors = {
        success: '#198754',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#0dcaf0'
    };

    // Create toast element
    const toast = $(`
        <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
             style="background-color: ${toastColors[type]}; position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);

    // Add to body and show
    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();

    // Remove after shown
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>
@endpush