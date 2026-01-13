@extends('layouts.main')

@section('title', 'Create Attendance Session')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Attendance', 'url' => route('school.attendance.index'), 'icon' => 'bx bx-calendar-check'],
            ['label' => 'Create Session', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE ATTENDANCE SESSION</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Create New Attendance Session</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.attendance.store') }}" method="POST" id="attendanceForm">
                            @csrf

                            <!-- Session Information Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-calendar me-2"></i> Session Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="session_date" class="form-label fw-bold">Session Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('session_date') is-invalid @enderror"
                                                       id="session_date" name="session_date"
                                                       value="{{ old('session_date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                                                @error('session_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Select the date for this attendance session
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="academic_year_id" class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                                                <select class="form-select @error('academic_year_id') is-invalid @enderror"
                                                        id="academic_year_id" name="academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}"
                                                                {{ (old('academic_year_id', $defaultAcademicYear) == $year->id) ? 'selected' : '' }}>
                                                            {{ $year->year_name }}
                                                            @if($year->is_current)
                                                                <span class="badge bg-success ms-1">Current</span>
                                                            @endif
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
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                                <select class="form-select @error('class_id') is-invalid @enderror"
                                                        id="class_id" name="class_id" required>
                                                    <option value="">Select Class</option>
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
                                                <label for="stream_id" class="form-label fw-bold">Stream <span class="text-danger">*</span></label>
                                                <select class="form-select @error('stream_id') is-invalid @enderror"
                                                        id="stream_id" name="stream_id" required disabled>
                                                    <option value="">Select Class First</option>
                                                </select>
                                                @error('stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="notes" class="form-label fw-bold">Notes</label>
                                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                                          id="notes" name="notes" rows="3"
                                                          placeholder="Optional notes about this attendance session">{{ old('notes') }}</textarea>
                                                @error('notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Add any additional notes or remarks about this session
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('school.attendance-management.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Attendance Sessions
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="bx bx-save me-1"></i> Create Attendance Session
                                </button>
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

    .form-label {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
        transition: all 0.15s ease-in-out;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .btn-outline-secondary {
        transition: all 0.15s ease-in-out;
    }

    .btn-outline-secondary:hover {
        transform: translateY(-1px);
    }

    .card.border-primary .card-header {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
        border-bottom: 2px solid #0d6efd;
        color: white;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25em 0.5em;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.justify-content-between .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 elements
    function initializeSelect2() {
        $('#class_id, #stream_id, #academic_year_id').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });
    }

    // Function to load streams for a class
    function loadStreamsForClass(classId) {
        const streamSelect = $('#stream_id');

        // Show loading state
        streamSelect.html('<option value="">Loading streams...</option>');
        streamSelect.prop('disabled', true);

        // Make AJAX call to get streams for this class
        $.ajax({
            url: '{{ route("school.api.attendance.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Clear loading option
                streamSelect.empty();
                streamSelect.append('<option value="">Select Stream</option>');

                if (response.streams && response.streams.length > 0) {
                    // Add stream options
                    response.streams.forEach(function(stream) {
                        streamSelect.append(`<option value="${stream.id}">${stream.name}</option>`);
                    });
                    streamSelect.prop('disabled', false);
                } else {
                    streamSelect.append('<option value="">No streams available</option>');
                    streamSelect.prop('disabled', true);
                }

                // Re-initialize Select2 after populating options
                streamSelect.select2({
                    placeholder: 'Select Stream',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    minimumInputLength: 0
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.empty();
                streamSelect.append('<option value="">Error loading streams</option>');
                streamSelect.prop('disabled', true);
            }
        });
    }

    // Class change handler
    $('#class_id').on('change', function() {
        const classId = $(this).val();
        if (classId) {
            loadStreamsForClass(classId);
        } else {
            // Reset stream select when no class is selected
            $('#stream_id').empty().append('<option value="">Select Class First</option>').prop('disabled', true);
            $('#stream_id').select2({
                placeholder: 'Select Class First',
                allowClear: true,
                width: '100%',
                theme: 'bootstrap-5',
                minimumInputLength: 0
            });
        }
    });

    // Initialize Select2 on page load
    initializeSelect2();

    // Load streams if a class is already selected (on page reload)
    const selectedClassId = $('#class_id').val();
    if (selectedClassId) {
        loadStreamsForClass(selectedClassId);
    }

    // Form validation and submission
    $('#attendanceForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        // Basic client-side validation
        const sessionDate = $('#session_date').val();
        const classId = $('#class_id').val();
        const streamId = $('#stream_id').val();
        const academicYearId = $('#academic_year_id').val();

        if (!sessionDate || !classId || !streamId || !academicYearId) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }

        // Check if session date is not in the future
        const today = new Date().toISOString().split('T')[0];
        if (sessionDate > today) {
            e.preventDefault();
            alert('Session date cannot be in the future.');
            return false;
        }

        // Show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Creating...');
    });

    // Set max date for session date input
    $('#session_date').attr('max', new Date().toISOString().split('T')[0]);
});
</script>
@endpush