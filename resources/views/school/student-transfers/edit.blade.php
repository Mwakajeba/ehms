@extends('layouts.main')

@section('title', 'Edit Student Transfer - ' . ($transfer->transfer_number ?? 'Unknown'))

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Edit Transfer', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT STUDENT TRANSFER</h6>
        <hr />

        @if(!$transfer || !$transfer->exists)
            <div class="alert alert-danger">
                <h5>Error: Transfer record not found</h5>
                <p>The transfer record could not be loaded. Please go back and try again.</p>
                <a href="{{ route('school.student-transfers.index') }}" class="btn btn-primary">Back to Transfers</a>
            </div>
        @else
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Edit Transfer #{{ $transfer->transfer_number }}</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.student-transfers.update', request()->route('encodedId')) }}" method="POST" enctype="multipart/form-data" id="transferForm">
                            @csrf
                            @method('PUT')

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
                                                <label class="form-label fw-bold">Transfer Type</label>
                                                <div class="transfer-type-display p-3 bg-light rounded">
                                                    @switch($transfer->transfer_type)
                                                        @case('transfer_out')
                                                            <i class="bx bx-log-out text-danger me-2" style="font-size: 1.2em;"></i>
                                                            <strong class="text-danger">Transfer Out</strong>
                                                            <br><small class="text-muted">Student leaving this school</small>
                                                            @break
                                                        @case('transfer_in')
                                                            <i class="bx bx-log-in text-success me-2" style="font-size: 1.2em;"></i>
                                                            <strong class="text-success">Transfer In</strong>
                                                            <br><small class="text-muted">Student joining this school</small>
                                                            @break
                                                        @case('re_admission')
                                                            <i class="bx bx-refresh text-warning me-2" style="font-size: 1.2em;"></i>
                                                            <strong class="text-warning">Re-admission</strong>
                                                            <br><small class="text-muted">Student returning after absence</small>
                                                            @break
                                                    @endswitch
                                                </div>
                                                <input type="hidden" name="transfer_type" value="{{ $transfer->transfer_type }}">
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Transfer type cannot be changed after creation. Create a new transfer record if needed.
                                                </div>
                                                <input type="hidden" name="transfer_type" value="{{ $transfer->transfer_type }}">
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
                                        <div class="col-md-6" id="studentSelectSection">
                                            <div class="mb-3">
                                                <label for="student_id" class="form-label fw-bold">Select Student <span class="text-danger">*</span></label>
                                                <select class="form-select @error('student_id') is-invalid @enderror" id="student_id" name="student_id">
                                                    <option value="">Select Student</option>
                                                    @foreach($activeStudents as $student)
                                                        <option value="{{ $student->id }}" {{ old('student_id', $transfer->student_id) == $student->id ? 'selected' : '' }}>
                                                            {{ $student->admission_number }} - {{ $student->first_name }} {{ $student->last_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('student_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6" id="newStudentSection" style="display: none;">
                                            <div class="mb-3">
                                                <label for="student_name" class="form-label fw-bold">Student Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('student_name') is-invalid @enderror"
                                                       id="student_name" name="student_name" value="{{ old('student_name', $transfer->student_name) }}"
                                                       placeholder="Enter student full name">
                                                @error('student_name')
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
                                                       id="transfer_date" name="transfer_date" value="{{ old('transfer_date', $transfer->transfer_date ? $transfer->transfer_date->format('Y-m-d') : '') }}" required>
                                                @error('transfer_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="transfer_certificate_number" class="form-label fw-bold">Transfer Certificate Number</label>
                                                <input type="text" class="form-control @error('transfer_certificate_number') is-invalid @enderror"
                                                       id="transfer_certificate_number" name="transfer_certificate_number" value="{{ old('transfer_certificate_number', $transfer->transfer_certificate_number) }}"
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
                                                       id="previous_school" name="previous_school" value="{{ old('previous_school', $transfer->previous_school) }}"
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
                                                       id="new_school" name="new_school" value="{{ old('new_school', $transfer->new_school) }}"
                                                       placeholder="New school name">
                                                @error('new_school')
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
                                                    <option value="parent_relocation" {{ old('reason', $transfer->reason) == 'parent_relocation' ? 'selected' : '' }}>Parent Relocation</option>
                                                    <option value="better_facilities" {{ old('reason', $transfer->reason) == 'better_facilities' ? 'selected' : '' }}>Better Facilities</option>
                                                    <option value="academic_performance" {{ old('reason', $transfer->reason) == 'academic_performance' ? 'selected' : '' }}>Academic Performance</option>
                                                    <option value="financial_reasons" {{ old('reason', $transfer->reason) == 'financial_reasons' ? 'selected' : '' }}>Financial Reasons</option>
                                                    <option value="personal_reasons" {{ old('reason', $transfer->reason) == 'personal_reasons' ? 'selected' : '' }}>Personal Reasons</option>
                                                    <option value="other" {{ old('reason', $transfer->reason) == 'other' ? 'selected' : '' }}>Other</option>
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
                                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $transfer->academic_year_id) == $year->id ? 'selected' : '' }}>
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
                                                          id="notes" name="notes" rows="3" placeholder="Any additional information about the transfer...">{{ old('notes', $transfer->notes) }}</textarea>
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
                                                          placeholder="Enter student's academic performance, grades, achievements, etc.">{{ old('academic_records', $transfer->academic_records) }}</textarea>
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
                                    <!-- Current Documents Display -->
                                    @if($transfer->transfer_certificate || $transfer->academic_report)
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <h6 class="text-muted mb-3">Current Documents:</h6>
                                            <div class="row">
                                                @if($transfer->transfer_certificate)
                                                <div class="col-md-6">
                                                    <div class="document-preview p-3 border rounded">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-file text-primary me-2"></i>
                                                            <div class="flex-grow-1">
                                                                <strong>Transfer Certificate</strong>
                                                                <br><small class="text-muted">{{ basename($transfer->transfer_certificate) }}</small>
                                                            </div>
                                                            <a href="{{ asset('storage/' . $transfer->transfer_certificate) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                @if($transfer->academic_report)
                                                <div class="col-md-6">
                                                    <div class="document-preview p-3 border rounded">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bx bx-file text-success me-2"></i>
                                                            <div class="flex-grow-1">
                                                                <strong>Academic Report</strong>
                                                                <br><small class="text-muted">{{ basename($transfer->academic_report) }}</small>
                                                            </div>
                                                            <a href="{{ asset('storage/' . $transfer->academic_report) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="form-text text-muted mt-2">
                                                <i class="bx bx-info-circle me-1"></i>
                                                Upload new files to replace existing ones, or leave blank to keep current files.
                                            </div>
                                        </div>
                                    </div>
                                    @endif

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
                                                    Upload new transfer certificate (PDF, JPG, PNG, max 5MB)
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
                                                    Upload new academic report or transcript (PDF, JPG, PNG, max 5MB)
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
                                                <i class="bx bx-refresh me-1"></i> Reset Changes
                                            </button>
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="bx bx-save me-2"></i> Update Transfer
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
        @endif
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

    /* Document Preview Styling */
    .document-preview {
        background-color: #f8f9fa;
        border-color: #dee2e6 !important;
    }

    .document-preview:hover {
        background-color: #e9ecef;
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
    console.log('Student transfer edit form loaded');

    // Initialize Select2 for all select elements
    initializeSelect2Elements();

    // Initialize form validation
    initializeFormValidation();

    // Initialize form based on current transfer type
    const initialTransferType = '{{ $transfer->transfer_type }}';
    updateFormForTransferType(initialTransferType);

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
    $('#reason, #academic_year_id').select2({
        placeholder: 'Select',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0
    });
}

// Function to update form based on transfer type
function updateFormForTransferType(transferType) {
    const studentSelectSection = $('#studentSelectSection');
    const newStudentSection = $('#newStudentSection');
    const fromSchoolSection = $('#fromSchoolSection');
    const toSchoolSection = $('#toSchoolSection');

    // Reset all sections
    studentSelectSection.show();
    newStudentSection.hide();
    fromSchoolSection.show();
    toSchoolSection.show();

    // Update labels and requirements based on transfer type
    switch(transferType) {
        case 'transfer_out':
            // Student leaving this school - select existing student
            $('#student_id').attr('required', true);
            $('#student_name').attr('required', false);
            $('#previous_school').attr('placeholder', 'This school name');
            $('#new_school').attr('placeholder', 'New school name');
            $('#previous_school').closest('.mb-3').find('label').html('From School <span class="text-danger">*</span>');
            $('#new_school').closest('.mb-3').find('label').html('To School <span class="text-danger">*</span>');
            break;

        case 'transfer_in':
            // Student joining this school - new student info
            studentSelectSection.hide();
            newStudentSection.show();
            $('#student_id').attr('required', false);
            $('#student_name').attr('required', true);
            $('#previous_school').attr('placeholder', 'Previous school name');
            $('#new_school').attr('placeholder', 'This school name');
            $('#previous_school').closest('.mb-3').find('label').html('From School <span class="text-danger">*</span>');
            $('#new_school').closest('.mb-3').find('label').html('To School <span class="text-danger">*</span>');
            break;

        case 're_admission':
            // Student returning - select existing student
            $('#student_id').attr('required', true);
            $('#student_name').attr('required', false);
            $('#previous_school').attr('placeholder', 'Previous school/break period');
            $('#new_school').attr('placeholder', 'This school name');
            $('#previous_school').closest('.mb-3').find('label').html('From (Previous Status)');
            $('#new_school').closest('.mb-3').find('label').html('To School <span class="text-danger">*</span>');
            break;
    }

    // Update section visibility
    if (transferType === 'transfer_in') {
        fromSchoolSection.show();
        toSchoolSection.show();
    }
}

// Form validation initialization
function initializeFormValidation() {
    $('#transferForm').on('submit', function(e) {
        let isValid = true;
        const transferType = '{{ $transfer->transfer_type }}';

        // Validate student selection based on transfer type
        if (transferType === 'transfer_in') {
            if (!$('#student_name').val()) {
                $('#student_name').addClass('is-invalid');
                isValid = false;
            } else {
                $('#student_name').removeClass('is-invalid');
            }
        } else {
            if (!$('#student_id').val()) {
                $('#student_id').addClass('is-invalid');
                isValid = false;
            } else {
                $('#student_id').removeClass('is-invalid');
            }
        }

        // Validate required fields
        const requiredFields = ['transfer_date'];
        if (transferType !== 're_admission') {
            requiredFields.push('previous_school', 'new_school');
        } else {
            requiredFields.push('new_school');
        }

        requiredFields.forEach(field => {
            const element = $('#' + field);
            if (!element.val()) {
                element.addClass('is-invalid');
                isValid = false;
            } else {
                element.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields', 'error');
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });
}

// Reset form function
function resetForm() {
    if (confirm('Are you sure you want to reset all changes? This will reload the original data.')) {
        window.location.reload();
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