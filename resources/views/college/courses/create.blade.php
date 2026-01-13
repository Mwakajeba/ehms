@extends('layouts.main')

@section('title', 'Create Course')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Courses', 'url' => route('college.courses.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Create Course', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW COURSE</h6>
        <hr />

        <div class="row">
            <!-- Information Sidebar -->
            <div class="col-12 col-lg-4 order-2 order-lg-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i> Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="bx bx-bulb me-1"></i> Tips for Creating Courses:</h6>
                            <ul class="mb-0 small">
                                <li>Course code must be unique</li>
                                <li>Select the program first</li>
                                <li>Credit hours range from 1-6</li>
                                <li>Core courses are mandatory</li>
                                <li>Elective courses are optional</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="bx bx-error me-1"></i> Required Fields:</h6>
                            <ul class="mb-0 small">
                                <li>Program</li>
                                <li>Course Code</li>
                                <li>Course Name</li>
                                <li>Level</li>
                                <li>Semester</li>
                                <li>Credit Hours</li>
                                <li>Course Type</li>
                            </ul>
                        </div>

                        <div class="alert alert-secondary">
                            <h6><i class="bx bx-bookmark me-1"></i> Course Types:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Core:</strong> Mandatory for all students in program</li>
                                <li><strong>Elective:</strong> Optional student selection</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Statistics -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-stats me-2"></i> Quick Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalCourses = \App\Models\College\Course::count();
                            $activeCourses = \App\Models\College\Course::where('status', 'active')->count();
                            $coreCourses = \App\Models\College\Course::where('core_elective', 'Core')->count();
                            $electiveCourses = \App\Models\College\Course::where('core_elective', 'Elective')->count();
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small"><i class="bx bx-book text-primary me-2"></i>Total Courses</span>
                            <span class="badge bg-primary">{{ $totalCourses }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small"><i class="bx bx-check-circle text-success me-2"></i>Active Courses</span>
                            <span class="badge bg-success">{{ $activeCourses }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small"><i class="bx bx-star text-warning me-2"></i>Core Courses</span>
                            <span class="badge bg-warning text-dark">{{ $coreCourses }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small"><i class="bx bx-bookmark text-info me-2"></i>Elective Courses</span>
                            <span class="badge bg-info">{{ $electiveCourses }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <div class="col-12 col-lg-8 order-1 order-lg-1">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Course</h5>
                        </div>
                        <hr />

                        <form action="{{ route('college.courses.store') }}" method="POST" id="courseForm">
                            @csrf

                            <!-- Program & Basic Information Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-id-card me-2 text-primary"></i> Basic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Program <span class="text-danger">*</span></label>
                                                <select class="form-select @error('program_id') is-invalid @enderror"
                                                        id="program_id" name="program_id" required>
                                                    <option value="">Select Program</option>
                                                    @foreach($programs as $program)
                                                        <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                                            {{ $program->name }} ({{ $program->code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('program_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Course Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                                       id="code" name="code" value="{{ old('code') }}"
                                                       placeholder="e.g., CS101" required>
                                                @error('code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Unique identifier for the course
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Course Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                       id="name" name="name" value="{{ old('name') }}"
                                                       placeholder="e.g., Introduction to Programming" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Status</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox"
                                                           id="status_toggle" name="status" value="active"
                                                           {{ old('status', 'active') == 'active' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="status_toggle">
                                                        <span id="status_label" class="badge bg-success">Active</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Details Section -->
                            <div class="card border-info mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-book me-2 text-info"></i> Academic Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Level <span class="text-danger">*</span></label>
                                                <select class="form-select @error('level') is-invalid @enderror"
                                                        id="level" name="level" required>
                                                    <option value="">Select Level</option>
                                                    <option value="Certificate" {{ old('level') == 'Certificate' ? 'selected' : '' }}>Certificate</option>
                                                    <option value="Diploma" {{ old('level') == 'Diploma' ? 'selected' : '' }}>Diploma</option>
                                                    <option value="Degree" {{ old('level') == 'Degree' ? 'selected' : '' }}>Degree</option>
                                                    <option value="Masters" {{ old('level') == 'Masters' ? 'selected' : '' }}>Masters</option>
                                                    <option value="PhD" {{ old('level') == 'PhD' ? 'selected' : '' }}>PhD</option>
                                                </select>
                                                @error('level')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Semester <span class="text-danger">*</span></label>
                                                <select class="form-select @error('semester') is-invalid @enderror"
                                                        id="semester" name="semester" required>
                                                    <option value="">Select Semester</option>
                                                    @foreach($semesters as $semester)
                                                        <option value="{{ $semester->id }}" {{ old('semester') == $semester->id ? 'selected' : '' }}>
                                                            {{ $semester->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('semester')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Credit Hours <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control @error('credit_hours') is-invalid @enderror"
                                                       id="credit_hours" name="credit_hours" value="{{ old('credit_hours', 3) }}"
                                                       min="1" max="6" required>
                                                @error('credit_hours')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Range: 1-6 credit hours
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Course Type <span class="text-danger">*</span></label>
                                                <select class="form-select @error('core_elective') is-invalid @enderror"
                                                        id="core_elective" name="core_elective" required>
                                                    <option value="">Select Type</option>
                                                    <option value="Core" {{ old('core_elective') == 'Core' ? 'selected' : '' }}>Core (Mandatory)</option>
                                                    <option value="Elective" {{ old('core_elective') == 'Elective' ? 'selected' : '' }}>Elective (Optional)</option>
                                                </select>
                                                @error('core_elective')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="card border-success mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-file me-2 text-success"></i> Description
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Course Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="4"
                                                  placeholder="Brief overview of the course content and objectives...">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Describe what students will learn in this course
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('college.courses.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="reset" class="btn btn-outline-warning">
                                    <i class="bx bx-reset me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Course
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
    .form-label {
        font-weight: 600;
        color: #495057;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .card.border-primary {
        border-color: #0d6efd !important;
    }

    .card.border-info {
        border-color: #0dcaf0 !important;
    }

    .card.border-success {
        border-color: #198754 !important;
    }

    /* Select2 Custom Styling */
    .select2-container--default .select2-selection--single {
        height: 45px !important;
        border-radius: 10px !important;
        border: 1px solid #ced4da;
        padding: 6px 12px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
        padding-left: 0;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 43px !important;
        right: 8px;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    .select2-dropdown {
        border-radius: 10px !important;
        border: 1px solid #ced4da;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .select2-container--default .select2-results__option {
        padding: 10px 12px;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
        border-radius: 6px;
        margin: 2px 4px;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border-radius: 8px;
        padding: 8px 12px;
    }

    .form-control {
        height: 45px;
        border-radius: 10px !important;
    }

    .form-select {
        height: 45px;
        border-radius: 10px !important;
    }

    textarea.form-control {
        height: auto;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize Select2 on dropdowns
        $('#program_id').select2({
            placeholder: 'Select Program',
            allowClear: true,
            width: '100%'
        });

        $('#semester').select2({
            placeholder: 'Select Semester',
            allowClear: true,
            width: '100%'
        });

        $('#level').select2({
            placeholder: 'Select Level',
            allowClear: true,
            width: '100%'
        });

        $('#core_elective').select2({
            placeholder: 'Select Type',
            allowClear: true,
            width: '100%'
        });

        // Status toggle label update
        $('#status_toggle').on('change', function() {
            if ($(this).is(':checked')) {
                $('#status_label').removeClass('bg-danger').addClass('bg-success').text('Active');
            } else {
                $('#status_label').removeClass('bg-success').addClass('bg-danger').text('Inactive');
            }
        });

        // Form validation
        $('#courseForm').on('submit', function(e) {
            var isValid = true;

            // Check required fields
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }
        });

        // Remove validation error on input
        $('input, select, textarea').on('input change', function() {
            if ($(this).val()) {
                $(this).removeClass('is-invalid');
            }
        });
    });
</script>
@endpush
