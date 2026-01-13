@extends('layouts.main')

@section('title', 'Edit Exam Class Assignment')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Class Assignments', 'url' => route('school.exam-class-assignments.index'), 'icon' => 'bx bx-target-lock'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Edit Assignment: {{ $assignment->examType->name }} - {{ $assignment->classe->name }}</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.exam-class-assignments.update', $assignment) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="exam_type_id" class="form-label">Exam Type <span class="text-danger">*</span></label>
                                                <select class="form-select @error('exam_type_id') is-invalid @enderror"
                                                        id="exam_type_id"
                                                        name="exam_type_id"
                                                        required>
                                                    <option value="">Select Exam Type</option>
                                                    @foreach($examTypes as $examType)
                                                        <option value="{{ $examType->id }}" {{ old('exam_type_id', $assignment->exam_type_id) == $examType->id ? 'selected' : '' }}>
                                                            {{ $examType->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('exam_type_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                                <select class="form-select @error('academic_year_id') is-invalid @enderror"
                                                        id="academic_year_id"
                                                        name="academic_year_id"
                                                        required>
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
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                                <select class="form-select @error('class_id') is-invalid @enderror"
                                                        id="class_id"
                                                        name="class_id"
                                                        required>
                                                    <option value="">Select Class</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ old('class_id', $assignment->class_id) == $class->id ? 'selected' : '' }}>
                                                            {{ $class->class_name }}
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
                                                <label for="stream_id" class="form-label">Stream</label>
                                                <select class="form-select @error('stream_id') is-invalid @enderror"
                                                        id="stream_id"
                                                        name="stream_id">
                                                    <option value="">Select Stream (Optional)</option>
                                                    @if($assignment->stream)
                                                        <option value="{{ $assignment->stream->id }}" selected>
                                                            {{ $assignment->stream->stream_name }}
                                                        </option>
                                                    @endif
                                                    <!-- Additional streams will be loaded dynamically -->
                                                </select>
                                                @error('stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                        <select class="form-select @error('subject_id') is-invalid @enderror"
                                                id="subject_id"
                                                name="subject_id"
                                                required>
                                            <option value="">Select Subject</option>
                                            @if($assignment->subject)
                                                <option value="{{ $assignment->subject->id }}" selected>
                                                    {{ $assignment->subject->subject_name }}
                                                </option>
                                            @endif
                                            <!-- Additional subjects will be loaded dynamically -->
                                        </select>
                                        @error('subject_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="due_date" class="form-label">Due Date</label>
                                                <input type="date"
                                                       class="form-control @error('due_date') is-invalid @enderror"
                                                       id="due_date"
                                                       name="due_date"
                                                       value="{{ old('due_date', $assignment->due_date ? $assignment->due_date->format('Y-m-d') : '') }}">
                                                <div class="form-text">Optional due date for assignment completion</div>
                                                @error('due_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                                <select class="form-select @error('status') is-invalid @enderror"
                                                        id="status"
                                                        name="status"
                                                        required>
                                                    <option value="pending" {{ old('status', $assignment->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="in_progress" {{ old('status', $assignment->status) == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                                    <option value="completed" {{ old('status', $assignment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="cancelled" {{ old('status', $assignment->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="notes" class="form-label">Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                                  id="notes"
                                                  name="notes"
                                                  rows="3"
                                                  placeholder="Optional notes about this assignment">{{ old('notes', $assignment->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="card-title text-info">
                                                <i class="bx bx-info-circle me-1"></i> Assignment Details
                                            </h6>
                                            <hr />
                                            <dl class="row small">
                                                <dt class="col-sm-5">Created:</dt>
                                                <dd class="col-sm-7">{{ $assignment->created_at->format('M d, Y H:i') }}</dd>

                                                <dt class="col-sm-5">Last Updated:</dt>
                                                <dd class="col-sm-7">{{ $assignment->updated_at->format('M d, Y H:i') }}</dd>

                                                <dt class="col-sm-5">Exam:</dt>
                                                <dd class="col-sm-7">{{ $assignment->exam->name }}</dd>

                                                <dt class="col-sm-5">Class:</dt>
                                                <dd class="col-sm-7">{{ $assignment->class->class_name }}</dd>

                                                @if($assignment->stream)
                                                <dt class="col-sm-5">Stream:</dt>
                                                <dd class="col-sm-7">{{ $assignment->stream->stream_name }}</dd>
                                                @endif

                                                <dt class="col-sm-5">Subject:</dt>
                                                <dd class="col-sm-7">{{ $assignment->subject->subject_name }}</dd>
                                            </dl>
                                        </div>
                                    </div>

                                    <div class="card border-warning mt-3">
                                        <div class="card-body">
                                            <h6 class="card-title text-warning">
                                                <i class="bx bx-exclamation-triangle me-1"></i> Important Notes
                                            </h6>
                                            <hr />
                                            <ul class="list-unstyled small">
                                                <li class="mb-2">
                                                    Changing exam, class, or subject may create duplicate assignments.
                                                </li>
                                                <li class="mb-2">
                                                    Each class-subject combination can only be assigned to an exam once.
                                                </li>
                                                <li class="mb-0">
                                                    Status updates help track assignment progress.
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
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bx bx-save me-1"></i> Update Assignment
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

@push('scripts')
<script>
$(document).ready(function() {
    // Load streams when class is selected
    $('#class_id').on('change', function() {
        const classId = $(this).val();
        const academicYearId = $('#academic_year_id').val();

        if (classId && academicYearId) {
            loadStreams(classId);
            loadSubjects(classId);
        } else {
            $('#stream_id').html('<option value="">Select Stream (Optional)</option>');
            $('#subject_id').html('<option value="">Select Subject</option>');
        }
    });

    // Load subjects when class is selected
    $('#academic_year_id').on('change', function() {
        const classId = $('#class_id').val();
        const academicYearId = $(this).val();

        if (classId && academicYearId) {
            loadStreams(classId);
            loadSubjects(classId);
        }
    });

    function loadStreams(classId) {
        $.ajax({
            url: '{{ route("school.exam-class-assignments.api.streams", ":classId") }}'.replace(':classId', classId),
            type: 'GET',
            success: function(response) {
                let options = '<option value="">Select Stream (Optional)</option>';
                const currentStreamId = '{{ $assignment->stream_id }}';

                response.forEach(function(stream) {
                    const selected = currentStreamId == stream.id ? 'selected' : '';
                    options += `<option value="${stream.id}" ${selected}>${stream.stream_name}</option>`;
                });
                $('#stream_id').html(options);
            },
            error: function() {
                $('#stream_id').html('<option value="">Error loading streams</option>');
            }
        });
    }

    function loadSubjects(classId) {
        $.ajax({
            url: '{{ route("school.exam-class-assignments.api.subjects", ":classId") }}'.replace(':classId', classId),
            type: 'GET',
            success: function(response) {
                let options = '<option value="">Select Subject</option>';
                const currentSubjectId = '{{ $assignment->subject_id }}';

                response.forEach(function(subject) {
                    const selected = currentSubjectId == subject.id ? 'selected' : '';
                    options += `<option value="${subject.id}" ${selected}>${subject.subject_name}</option>`;
                });
                $('#subject_id').html(options);
            },
            error: function() {
                $('#subject_id').html('<option value="">Error loading subjects</option>');
            }
        });
    }

    // Set minimum date for due date
    const today = new Date().toISOString().split('T')[0];
    $('#due_date').attr('min', today);

    // Load initial data
    const initialClassId = $('#class_id').val();
    const initialAcademicYearId = $('#academic_year_id').val();

    if (initialClassId && initialAcademicYearId) {
        loadStreams(initialClassId);
        loadSubjects(initialClassId);
    }
});
</script>
@endpush