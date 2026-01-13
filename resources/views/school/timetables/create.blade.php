@extends('layouts.main')

@section('title', 'Create New Timetable')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Timetables', 'url' => route('school.timetables.index'), 'icon' => 'bx bx-time-five'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW TIMETABLE</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Create New Timetable</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.timetables.store') }}" method="POST" id="timetableForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Timetable Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., Class One - Form 1A Timetable" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="timetable_type" class="form-label">Timetable Type <span class="text-danger">*</span></label>
                                        <select class="form-control @error('timetable_type') is-invalid @enderror" id="timetable_type" name="timetable_type" required>
                                            <option value="">Select Type</option>
                                            <option value="master" {{ old('timetable_type') == 'master' ? 'selected' : '' }}>Master Timetable</option>
                                            <option value="teacher_on_duty" {{ old('timetable_type') == 'teacher_on_duty' ? 'selected' : '' }}>Teacher on Duty</option>
                                        </select>
                                        @error('timetable_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                        <select class="form-control @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" {{ old('academic_year_id', isset($currentAcademicYear) && $currentAcademicYear->id == $year->id ? $year->id : '') == $year->id ? 'selected' : '' }}>
                                                    {{ $year->year_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Class</label>
                                        <select class="form-control @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
                                            <option value="">Select Class (Optional)</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Optional - Select if this timetable is for a specific class</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stream_id" class="form-label">Stream</label>
                                        <select class="form-control @error('stream_id') is-invalid @enderror" id="stream_id" name="stream_id">
                                            <option value="">Select Stream (Optional)</option>
                                        </select>
                                        @error('stream_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Optional - Select a class first to load streams</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Optional description of the timetable">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('school.timetables.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Timetable
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Information</h6>
                        <hr />
                        <p class="small text-muted">
                            <strong>Timetable Types:</strong>
                        </p>
                        <ul class="small">
                            <li><strong>Master Timetable:</strong> Complete overview of all timetables</li>
                            <li><strong>Teacher on Duty:</strong> Shows schedule for teachers on duty</li>
                        </ul>
                        <p class="small text-muted mt-3">
                            After creating the timetable, you can add periods and entries in the edit page.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for Academic Year with live search
        $('#academic_year_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select academic year...',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0
        });

        // Initialize Select2 for Class with live search
        $('#class_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Search and select class...',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0
        });

        // Initialize Select2 for Stream with live search
        $('#stream_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Select class first to load streams...',
            allowClear: true,
            width: '100%',
            minimumResultsForSearch: 0,
            disabled: true
        });

        // Load streams when class is selected
        $('#class_id').on('change', function() {
            var classId = $(this).val();
            var $streamSelect = $('#stream_id');
            
            if (classId) {
                $streamSelect.prop('disabled', true);
                $streamSelect.html('<option value="">Loading streams...</option>').trigger('change');
                
                $.ajax({
                    url: '{{ route("school.timetables.get-streams") }}',
                    type: 'POST',
                    data: {
                        class_id: classId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $streamSelect.empty();
                        $streamSelect.append('<option value="">Select Stream</option>');
                        if (response && response.length > 0) {
                            response.forEach(function(stream) {
                                $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                            });
                        }
                        $streamSelect.prop('disabled', false).trigger('change');
                    },
                    error: function(xhr) {
                        console.error('Error loading streams:', xhr);
                        $streamSelect.empty();
                        $streamSelect.append('<option value="">Select Stream</option>');
                        $streamSelect.prop('disabled', false).trigger('change');
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Failed to load streams');
                        } else {
                            alert('Failed to load streams');
                        }
                    }
                });
            } else {
                $streamSelect.empty();
                $streamSelect.append('<option value="">Select Stream</option>');
                $streamSelect.prop('disabled', true).trigger('change');
            }
        });

        // Class and Stream are now optional, so no validation needed
    });
</script>
@endpush

