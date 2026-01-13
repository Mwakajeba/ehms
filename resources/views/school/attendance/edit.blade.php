@extends('layouts.main')

@section('title', 'Edit Attendance Session')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Attendance', 'url' => route('school.attendance.index'), 'icon' => 'bx bx-calendar-check'],
            ['label' => 'Edit Session', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT ATTENDANCE SESSION</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Attendance Session</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.attendance.update', $attendanceSession) }}" method="POST" id="attendanceForm">
                            @csrf
                            @method('PUT')

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
                                                       value="{{ old('session_date', $attendanceSession->session_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
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
                                                                {{ (old('academic_year_id', $attendanceSession->academic_year_id) == $year->id) ? 'selected' : '' }}>
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
                                                        <option value="{{ $class->id }}" {{ old('class_id', $attendanceSession->class_id) == $class->id ? 'selected' : '' }}>
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
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                                <select class="form-select @error('status') is-invalid @enderror"
                                                        id="status" name="status" required>
                                                    <option value="draft" {{ old('status', $attendanceSession->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="completed" {{ old('status', $attendanceSession->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Draft: Can still mark attendance | Completed: Session is finalized
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="notes" class="form-label fw-bold">Notes</label>
                                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                                          id="notes" name="notes" rows="3"
                                                          placeholder="Optional notes about this attendance session">{{ old('notes', $attendanceSession->notes) }}</textarea>
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

                            <!-- Session Statistics (Read-only) -->
                            @if($attendanceSession->studentAttendances->count() > 0)
                            <div class="card border-info mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-bar-chart me-2"></i> Current Session Statistics
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="stat-card">
                                                <div class="stat-icon">
                                                    <i class="bx bx-group"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <h4>{{ $attendanceSession->studentAttendances->count() }}</h4>
                                                    <p>Total Students</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-card present">
                                                <div class="stat-icon">
                                                    <i class="bx bx-check-circle"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <h4>{{ $attendanceSession->studentAttendances->where('status', 'present')->count() }}</h4>
                                                    <p>Present</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-card absent">
                                                <div class="stat-icon">
                                                    <i class="bx bx-x-circle"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <h4>{{ $attendanceSession->studentAttendances->where('status', 'absent')->count() }}</h4>
                                                    <p>Absent</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="stat-card late">
                                                <div class="stat-icon">
                                                    <i class="bx bx-time"></i>
                                                </div>
                                                <div class="stat-content">
                                                    <h4>{{ $attendanceSession->studentAttendances->where('status', 'late')->count() }}</h4>
                                                    <p>Late</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="alert alert-warning mt-3">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Note:</strong> Changing class or stream will reset all attendance records. You will need to mark attendance again for the new student list.
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('school.attendance.show', $attendanceSession) }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Session Details
                                </a>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('school.attendance-management.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-list me-1"></i> Back to List
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="bx bx-save me-1"></i> Update Session
                                    </button>
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

    .card.border-info .card-header {
        background: linear-gradient(135deg, #0dcaf0 0%, #17a2b8 100%) !important;
        border-bottom: 2px solid #0dcaf0;
        color: white;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25em 0.5em;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 4px solid #0d6efd;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card.present {
        border-left-color: #198754;
    }

    .stat-card.absent {
        border-left-color: #dc3545;
    }

    .stat-card.late {
        border-left-color: #fd7e14;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.2rem;
    }

    .stat-card.present .stat-icon {
        background: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .stat-card.absent .stat-icon {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .stat-card.late .stat-icon {
        background: rgba(253, 126, 20, 0.1);
        color: #fd7e14;
    }

    .stat-content h4 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
    }

    .stat-content p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 0.9rem;
    }

    .alert-warning {
        background: rgba(255, 193, 7, 0.1);
        border-color: rgba(255, 193, 7, 0.3);
        color: #856404;
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

        .d-flex.gap-2 {
            flex-direction: column;
            width: 100%;
        }

        .d-flex.gap-2 .btn {
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
        $('#class_id, #stream_id, #academic_year_id, #status').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });
    }

    // Function to load streams for a class
    function loadStreamsForClass(classId, selectedStreamId = null) {
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
                        const selected = selectedStreamId && stream.id == selectedStreamId ? 'selected' : '';
                        streamSelect.append(`<option value="${stream.id}" ${selected}>${stream.name}</option>`);
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

    // Load streams if a class is already selected (on page load)
    const selectedClassId = $('#class_id').val();
    const selectedStreamId = '{{ $attendanceSession->stream_id }}';
    if (selectedClassId) {
        loadStreamsForClass(selectedClassId, selectedStreamId);
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
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Updating...');

        // Check if class or stream changed
        const originalClassId = '{{ $attendanceSession->class_id }}';
        const originalStreamId = '{{ $attendanceSession->stream_id }}';

        if (classId != originalClassId || streamId != originalStreamId) {
            if (!confirm('Changing class or stream will reset all attendance records. You will need to mark attendance again. Continue?')) {
                e.preventDefault();
                submitBtn.prop('disabled', false).html(originalText);
                return false;
            }
        }
    });

    // Set max date for session date input
    $('#session_date').attr('max', new Date().toISOString().split('T')[0]);
});
</script>
@endpush