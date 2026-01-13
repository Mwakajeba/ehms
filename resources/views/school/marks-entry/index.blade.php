@extends('layouts.main')

@section('title', 'Marks Entry')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Marks Entry', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-edit-alt font-20 me-2"></i>
                            <h5 class="mb-0">Student Marks Entry</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Current Academic Year Info -->
                        <div class="alert alert-info border-0 bg-light-info d-flex align-items-center mb-4">
                            <i class="bx bx-calendar font-18 text-info me-2"></i>
                            <div>
                                <strong>Current Academic Year:</strong> {{ $currentAcademicYear->year_name }}
                                <small class="d-block text-info">Marks will be entered for the current academic year automatically</small>
                            </div>
                        </div>

                        <form id="marksEntryForm" class="needs-validation" novalidate>
                            @csrf
                            <input type="hidden" id="academic_year_id" name="academic_year_id" value="{{ $currentAcademicYear->id }}">

                            <div class="row g-3">
                                <!-- Exam Type Selection -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('exam_type_id') is-invalid @enderror"
                                                id="exam_type_id"
                                                name="exam_type_id"
                                                required>
                                            <option value="">Choose exam type...</option>
                                            @foreach($examTypes as $exam)
                                            <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="exam_type_id">
                                            <i class="bx bx-test-tube me-1"></i>Exam Type <span class="text-danger">*</span>
                                        </label>
                                        @error('exam_type_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Class Selection -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('class_id') is-invalid @enderror"
                                                id="class_id"
                                                name="class_id"
                                                required disabled>
                                            <option value="">Select exam type first...</option>
                                        </select>
                                        <label for="class_id">
                                            <i class="bx bx-group me-1"></i>Class <span class="text-danger">*</span>
                                        </label>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Stream Selection (Optional) -->
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select @error('stream_id') is-invalid @enderror"
                                                id="stream_id"
                                                name="stream_id"
                                                disabled>
                                            <option value="">No stream selected (optional)</option>
                                        </select>
                                        <label for="stream_id">
                                            <i class="bx bx-branch me-1"></i>Stream (Optional)
                                        </label>
                                        @error('stream_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-12">
                                    <div class="d-flex gap-2 justify-content-end">
                                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                            <i class="bx bx-refresh me-1"></i>Reset
                                        </button>
                                        <button type="button"
                                                class="btn btn-primary"
                                                id="proceedBtn"
                                                disabled>
                                            <i class="bx bx-right-arrow-alt me-1"></i>Proceed to Enter Marks
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

@section('scripts')
<script>
$(document).ready(function() {
    let classesData = [];

    // When exam type changes, fetch available classes
    $('#exam_type_id').change(function() {
        const examTypeId = $(this).val();

        if (examTypeId) {
            fetchClasses(examTypeId);
        } else {
            resetForm();
        }
    });

    // When class changes, update streams
    $('#class_id').change(function() {
        const classId = $(this).val();
        updateStreams(classId);
        updateProceedButton();
    });

    // When stream changes, update proceed button
    $('#stream_id').change(function() {
        updateProceedButton();
    });

    function fetchClasses(examTypeId) {
        // Show loading state
        $('#class_id').prop('disabled', true).html('<option value="">Loading classes...</option>');
        $('#stream_id').prop('disabled', true).html('<option value="">No stream selected (optional)</option>');

        const academicYearId = $('#academic_year_id').val();

        $.ajax({
            url: '{{ route("school.marks-entry.get-students") }}',
            method: 'POST',
            data: {
                academic_year_id: academicYearId,
                exam_type_id: examTypeId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                classesData = response.classes;
                populateClasses();
                $('#class_id').prop('disabled', false);

                if (classesData.length === 0) {
                    toastr.warning('No classes found for the selected exam type');
                }
            },
            error: function(xhr) {
                toastr.error('Error fetching classes. Please try again.');
                resetForm();
            }
        });
    }

    function populateClasses() {
        const $classSelect = $('#class_id');
        $classSelect.empty().append('<option value="">Choose class...</option>');

        classesData.forEach(function(cls) {
            $classSelect.append(`<option value="${cls.id}" data-hash="${cls.hash}">${cls.name}</option>`);
        });
    }

    function updateStreams(classId) {
        const $streamSelect = $('#stream_id');
        $streamSelect.empty().append('<option value="">No stream selected (optional)</option>');

        const selectedClass = classesData.find(cls => cls.id == classId);
        if (selectedClass && selectedClass.streams && selectedClass.streams.length > 0) {
            selectedClass.streams.forEach(function(stream) {
                $streamSelect.append(`<option value="${stream.id}" data-hash="${stream.hash}">${stream.name}</option>`);
            });
            $streamSelect.prop('disabled', false);
        } else {
            $streamSelect.prop('disabled', true);
        }
    }

    function updateProceedButton() {
        const classId = $('#class_id').val();
        const examTypeId = $('#exam_type_id').val();

        $('#proceedBtn').prop('disabled', !(classId && examTypeId));
    }

    function resetForm() {
        $('#class_id').empty().append('<option value="">Select exam type first...</option>').prop('disabled', true);
        $('#stream_id').empty().append('<option value="">No stream selected (optional)</option>').prop('disabled', true);
        $('#proceedBtn').prop('disabled', true);
        classesData = [];
    }

    // Reset form function for the reset button
    window.resetForm = function() {
        $('#exam_type_id').val('').trigger('change');
        resetForm();
        toastr.info('Form has been reset');
    };

    // Proceed to marks entry
    $('#proceedBtn').click(function() {
        const academicYearId = $('#academic_year_id').val();
        const examTypeId = $('#exam_type_id').val();
        const classId = $('#class_id').val();
        const streamId = $('#stream_id').val();

        const selectedClass = classesData.find(cls => cls.id == classId);
        if (!selectedClass) {
            toastr.error('Please select a valid class');
            return;
        }

        const academicYearHash = '{{ \Vinkla\Hashids\Facades\Hashids::encode($currentAcademicYear->id) }}';
        const examTypeHash = $('#exam_type_id option:selected').text().replace(/\s+/g, '-').toLowerCase();

        const url = `{{ url('school/marks-entry/enter-marks') }}/${examTypeHash}/${selectedClass.hash}/${academicYearHash}`;

        // Show loading
        $(this).prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Loading...');

        window.location.href = url;
    });

    // Form validation
    $('#marksEntryForm').on('submit', function(e) {
        e.preventDefault();
        if (this.checkValidity()) {
            $('#proceedBtn').click();
        } else {
            this.classList.add('was-validated');
        }
    });
});
</script>
@endsection