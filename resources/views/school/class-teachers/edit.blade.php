@extends('layouts.main')

@section('title', 'Edit Class Teacher Assignment')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Class Teachers', 'url' => route('school.class-teachers.index'), 'icon' => 'bx bx-user-check'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT CLASS TEACHER ASSIGNMENT</h6>
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

                        <form action="{{ route('school.class-teachers.update', $classTeacher) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Hidden field for academic year (auto-populated with current active year) -->
                            <input type="hidden" name="academic_year_id" value="{{ $classTeacher->academic_year_id }}">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="employee_id" class="form-label">Teacher <span class="text-danger">*</span></label>
                                        <select class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                            <option value="">Select a teacher...</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ (old('employee_id') ?? $classTeacher->employee_id) == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_id }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('employee_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                        <select class="form-control @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                            <option value="">Select a class...</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ (old('class_id') ?? $classTeacher->class_id) == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }} ({{ $class->code }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="stream_id" class="form-label">Stream <span class="text-danger">*</span></label>
                                        <select class="form-control @error('stream_id') is-invalid @enderror" id="stream_id" name="stream_id" required>
                                            <option value="">Select a stream...</option>
                                            @foreach($streams as $stream)
                                                <option value="{{ $stream->id }}" {{ (old('stream_id') ?? $classTeacher->stream_id) == $stream->id ? 'selected' : '' }}>
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

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ (old('is_active') ?? $classTeacher->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Assignment
                                    </label>
                                </div>
                            </div>

                            @error('assignment')
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
                            <span class="text-primary">{{ $classTeacher->employee->first_name }} {{ $classTeacher->employee->last_name }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Class:</strong><br>
                            <span class="text-primary">{{ $classTeacher->classe->name }} ({{ $classTeacher->classe->code }})</span>
                        </div>
                        <div class="mb-3">
                            <strong>Academic Year:</strong><br>
                            <span class="text-primary">{{ $classTeacher->academicYear->name }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong><br>
                            @if($classTeacher->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <strong>Assigned Date:</strong><br>
                            <span class="text-muted">{{ $classTeacher->created_at->format('M d, Y') }}</span>
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
                                <li>One teacher can be assigned to multiple classes</li>
                                <li>One class can have only one class teacher per academic year</li>
                                <li>Assignments are automatically set to the current active academic year</li>
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
        $('#employee_id, #class_id, #stream_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: function() {
                return $(this).data('placeholder');
            }
        });

        // Add event listener for class selection change
        $('#class_id').on('change', function() {
            const classId = $(this).val();
            const $streamSelect = $('#stream_id');

            if (classId) {
                // Fetch streams for the selected class
                $.ajax({
                    url: '/school/class-teachers/streams/' + classId,
                    type: 'GET',
                    success: function(data) {
                        $streamSelect.empty();
                        $streamSelect.append('<option value="">Select a stream...</option>');
                        data.streams.forEach(function(stream) {
                            const selected = '{{ old("stream_id") ?? $classTeacher->stream_id }}' == stream.id ? 'selected' : '';
                            $streamSelect.append('<option value="' + stream.id + '" ' + selected + '>' + stream.name + '</option>');
                        });
                        // Trigger change to update select2
                        $streamSelect.trigger('change');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading streams:', error);
                        $streamSelect.empty();
                        $streamSelect.append('<option value="">Select a stream...</option>');
                    }
                });
            } else {
                // No class selected, clear streams
                $streamSelect.empty();
                $streamSelect.append('<option value="">Select a stream...</option>');
                $streamSelect.trigger('change');
            }
        });

        console.log('Edit class teacher form loaded');
    });
</script>
@endpush