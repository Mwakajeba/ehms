@extends('layouts.main')

@section('title', 'Create Exam')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exams', 'url' => route('school.exams.index'), 'icon' => 'bx bx-file'],
            ['label' => 'Create Exam', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <i class="bx bx-plus me-2 font-22 text-success"></i>
                            <span class="h5 mb-0 text-success">Create New Exam</span>
                        </div>
                        <hr />

                        <form id="examForm" action="{{ route('school.exams.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Basic Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="exam_name" class="form-label required">Exam Name</label>
                                                <input type="text" class="form-control @error('exam_name') is-invalid @enderror"
                                                       id="exam_name" name="exam_name" value="{{ old('exam_name') }}"
                                                       placeholder="e.g., Mid-term Mathematics Exam" required>
                                                @error('exam_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="exam_type_id" class="form-label required">Exam Type</label>
                                                <select class="form-select @error('exam_type_id') is-invalid @enderror"
                                                        id="exam_type_id" name="exam_type_id" required>
                                                    <option value="">Select Exam Type</option>
                                                    @foreach($examTypes as $type)
                                                    <option value="{{ $type->id }}" {{ old('exam_type_id') == $type->id ? 'selected' : '' }}>
                                                        {{ $type->name }} (Weight: {{ $type->weight }}%)
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('exam_type_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="academic_year_id" class="form-label required">Academic Year</label>
                                                <select class="form-select @error('academic_year_id') is-invalid @enderror"
                                                        id="academic_year_id" name="academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                        {{ $year->year_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('academic_year_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror"
                                                          id="description" name="description" rows="3"
                                                          placeholder="Optional description of the exam">{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subject & Class Information -->
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bx bx-book me-1"></i> Subject & Class</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="subject_id" class="form-label required">Subject</label>
                                                <select class="form-select @error('subject_id') is-invalid @enderror"
                                                        id="subject_id" name="subject_id" required>
                                                    <option value="">Select Subject</option>
                                                    @foreach($subjects as $subject)
                                                    <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
                                                        {{ $subject->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('subject_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="class_id" class="form-label required">Class</label>
                                                <select class="form-select @error('class_id') is-invalid @enderror"
                                                        id="class_id" name="class_id" required>
                                                    <option value="">Select Class</option>
                                                    @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                        {{ $class->class_name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="stream_id" class="form-label">Stream (Optional)</label>
                                                <select class="form-select @error('stream_id') is-invalid @enderror"
                                                        id="stream_id" name="stream_id">
                                                    <option value="">Select Stream (Optional)</option>
                                                    @foreach($streams as $stream)
                                                    <option value="{{ $stream->id }}" {{ old('stream_id') == $stream->id ? 'selected' : '' }}>
                                                        {{ $stream->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <!-- Schedule Information -->
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bx bx-calendar me-1"></i> Schedule</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="exam_date" class="form-label required">Exam Date</label>
                                                <input type="date" class="form-control @error('exam_date') is-invalid @enderror"
                                                       id="exam_date" name="exam_date" value="{{ old('exam_date') }}" required>
                                                @error('exam_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="start_time" class="form-label">Start Time</label>
                                                        <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                                               id="start_time" name="start_time" value="{{ old('start_time') }}">
                                                        @error('start_time')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="end_time" class="form-label">End Time</label>
                                                        <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                                               id="end_time" name="end_time" value="{{ old('end_time') }}">
                                                        @error('end_time')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="duration_minutes" class="form-label">Duration (Minutes)</label>
                                                <input type="number" class="form-control @error('duration_minutes') is-invalid @enderror"
                                                       id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes') }}"
                                                       min="1" placeholder="e.g., 120">
                                                @error('duration_minutes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Marks & Settings -->
                                <div class="col-md-6">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bx bx-target me-1"></i> Marks & Settings</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="max_marks" class="form-label required">Maximum Marks</label>
                                                        <input type="number" class="form-control @error('max_marks') is-invalid @enderror"
                                                               id="max_marks" name="max_marks" value="{{ old('max_marks') }}"
                                                               min="1" step="0.01" placeholder="100" required>
                                                        @error('max_marks')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="pass_marks" class="form-label required">Pass Marks</label>
                                                        <input type="number" class="form-control @error('pass_marks') is-invalid @enderror"
                                                               id="pass_marks" name="pass_marks" value="{{ old('pass_marks') }}"
                                                               min="0" step="0.01" placeholder="40" required>
                                                        @error('pass_marks')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="weight" class="form-label">Weight (%)</label>
                                                <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                                       id="weight" name="weight" value="{{ old('weight') }}"
                                                       min="0" max="100" step="0.01" placeholder="e.g., 25">
                                                @error('weight')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">Weight of this exam in final grade calculation</div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="status" class="form-label required">Status</label>
                                                <select class="form-select @error('status') is-invalid @enderror"
                                                        id="status" name="status" required>
                                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                    <option value="scheduled" {{ old('status', 'scheduled') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Instructions -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card border">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bx bx-list-ul me-1"></i> Instructions</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="instructions" class="form-label">Exam Instructions</label>
                                                <textarea class="form-control @error('instructions') is-invalid @enderror"
                                                          id="instructions" name="instructions" rows="4"
                                                          placeholder="Enter exam instructions, rules, and guidelines">{{ old('instructions') }}</textarea>
                                                @error('instructions')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('school.exams.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to Exams
                                        </a>
                                        <div>
                                            <button type="button" class="btn btn-outline-primary me-2" onclick="clearForm()">
                                                <i class="bx bx-refresh me-1"></i> Clear
                                            </button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bx bx-save me-1"></i> Create Exam
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
    .required::after {
        content: " *";
        color: #dc3545;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .btn {
        border-radius: 0.375rem;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate duration from start and end time
    $('#start_time, #end_time').on('change', function() {
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();

        if (startTime && endTime) {
            const start = new Date(`2000-01-01T${startTime}`);
            const end = new Date(`2000-01-01T${endTime}`);

            if (end > start) {
                const duration = (end - start) / (1000 * 60); // minutes
                $('#duration_minutes').val(duration);
            }
        }
    });

    // Validate pass marks doesn't exceed max marks
    $('#max_marks, #pass_marks').on('input', function() {
        const maxMarks = parseFloat($('#max_marks').val()) || 0;
        const passMarks = parseFloat($('#pass_marks').val()) || 0;

        if (passMarks > maxMarks) {
            $('#pass_marks').addClass('is-invalid');
            $('#pass_marks').next('.invalid-feedback').remove();
            $('#pass_marks').after('<div class="invalid-feedback">Pass marks cannot exceed maximum marks</div>');
        } else {
            $('#pass_marks').removeClass('is-invalid');
            $('#pass_marks').next('.invalid-feedback').remove();
        }
    });

    // Form validation before submit
    $('#examForm').on('submit', function(e) {
        const maxMarks = parseFloat($('#max_marks').val()) || 0;
        const passMarks = parseFloat($('#pass_marks').val()) || 0;

        if (passMarks > maxMarks) {
            e.preventDefault();
            toastr.error('Pass marks cannot exceed maximum marks');
            $('#pass_marks').focus();
            return false;
        }
    });
});

function clearForm() {
    $('#examForm')[0].reset();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
}
</script>
@endpush