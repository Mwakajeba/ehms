@extends('layouts.main')

@section('title', 'Create Program')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Programs', 'url' => route('college.programs.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Create Program', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW PROGRAM</h6>
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
                            <h6><i class="bx bx-bulb me-1"></i> Tips for Creating Programs:</h6>
                            <ul class="mb-0 small">
                                <li>Program code is auto-generated from name</li>
                                <li>Code must be unique across all programs</li>
                                <li>Choose the appropriate academic level</li>
                                <li>Duration should match the curriculum</li>
                                <li>Description helps students understand the program</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="bx bx-error me-1"></i> Required Fields:</h6>
                            <ul class="mb-0 small">
                                <li>Program Name</li>
                                <li>Program Code</li>
                                <li>Department</li>
                                <li>Duration (Years)</li>
                                <li>Academic Level</li>
                            </ul>
                        </div>

                        <div class="alert alert-secondary">
                            <h6><i class="bx bx-layer me-1"></i> Academic Levels:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Certificate:</strong> 1 year or less</li>
                                <li><strong>Diploma:</strong> 2-3 years</li>
                                <li><strong>Bachelor's:</strong> 3-4 years</li>
                                <li><strong>Master's:</strong> 1-2 years</li>
                                <li><strong>PhD:</strong> 3-5 years</li>
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
                            $totalPrograms = \App\Models\College\Program::count();
                            $activePrograms = \App\Models\College\Program::where('is_active', 1)->count();
                            $inactivePrograms = $totalPrograms - $activePrograms;
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small"><i class="bx bx-graduation text-primary me-2"></i>Total Programs</span>
                            <span class="badge bg-primary">{{ $totalPrograms }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small"><i class="bx bx-check-circle text-success me-2"></i>Active Programs</span>
                            <span class="badge bg-success">{{ $activePrograms }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small"><i class="bx bx-x-circle text-danger me-2"></i>Inactive Programs</span>
                            <span class="badge bg-danger">{{ $inactivePrograms }}</span>
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
                            <h5 class="mb-0 text-primary">Add New Program</h5>
                        </div>
                        <hr />

                        <form action="{{ route('college.programs.store') }}" method="POST" id="programForm">
                            @csrf

                            <!-- Basic Information Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-id-card me-2 text-primary"></i> Basic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Program Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                       id="name" name="name" value="{{ old('name') }}"
                                                       placeholder="e.g., Bachelor of Science in Computer Science" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Full official name of the program
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Program Code <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('code') is-invalid @enderror"
                                                       id="code" name="code" value="{{ old('code') }}"
                                                       placeholder="e.g., BSC-CS" required>
                                                @error('code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Auto-generated from program name
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Department <span class="text-danger">*</span></label>
                                                <select class="form-select @error('department_id') is-invalid @enderror"
                                                        id="department_id" name="department_id" required>
                                                    <option value="">Select Department</option>
                                                    @foreach($departments as $department)
                                                        <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                            {{ $department->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('department_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Status</label>
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox"
                                                           id="is_active" name="is_active" value="1"
                                                           {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_active">
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
                                                <label class="form-label fw-bold">Academic Level <span class="text-danger">*</span></label>
                                                <select class="form-select @error('level') is-invalid @enderror"
                                                        id="level" name="level" required>
                                                    <option value="">Select Level</option>
                                                    <option value="certificate" {{ old('level') == 'certificate' ? 'selected' : '' }}>Certificate</option>
                                                    <option value="diploma" {{ old('level') == 'diploma' ? 'selected' : '' }}>Diploma</option>
                                                    <option value="bachelor" {{ old('level') == 'bachelor' ? 'selected' : '' }}>Bachelor's Degree</option>
                                                    <option value="master" {{ old('level') == 'master' ? 'selected' : '' }}>Master's Degree</option>
                                                    <option value="phd" {{ old('level') == 'phd' ? 'selected' : '' }}>PhD</option>
                                                </select>
                                                @error('level')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Duration (Years) <span class="text-danger">*</span></label>
                                                <select class="form-select @error('duration_years') is-invalid @enderror"
                                                        id="duration_years" name="duration_years" required>
                                                    <option value="">Select Duration</option>
                                                    <option value="1" {{ old('duration_years') == '1' ? 'selected' : '' }}>1 Year</option>
                                                    <option value="2" {{ old('duration_years') == '2' ? 'selected' : '' }}>2 Years</option>
                                                    <option value="3" {{ old('duration_years') == '3' ? 'selected' : '' }}>3 Years</option>
                                                    <option value="4" {{ old('duration_years') == '4' ? 'selected' : '' }}>4 Years</option>
                                                    <option value="5" {{ old('duration_years') == '5' ? 'selected' : '' }}>5 Years</option>
                                                    <option value="6" {{ old('duration_years') == '6' ? 'selected' : '' }}>6 Years</option>
                                                </select>
                                                @error('duration_years')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description & Objectives Section -->
                            <div class="card border-success mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-file me-2 text-success"></i> Description & Objectives
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Program Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="3"
                                                  placeholder="Brief overview of what the program covers...">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Provide a clear description to help students understand the program
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Program Objectives</label>
                                        <textarea class="form-control @error('objectives') is-invalid @enderror"
                                                  id="objectives" name="objectives" rows="3"
                                                  placeholder="What students will achieve upon completion...">{{ old('objectives') }}</textarea>
                                        @error('objectives')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Admission Requirements</label>
                                        <textarea class="form-control @error('requirements') is-invalid @enderror"
                                                  id="requirements" name="requirements" rows="3"
                                                  placeholder="Entry requirements for admission...">{{ old('requirements') }}</textarea>
                                        @error('requirements')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('college.programs.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="reset" class="btn btn-outline-warning">
                                    <i class="bx bx-reset me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Program
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-generate program code based on name
        $('#name').on('input', function() {
            var name = $(this).val();
            if (name && !$('#code').data('manual')) {
                // Generate code from first letters of words
                var code = name.split(' ')
                    .filter(word => word.length > 2)
                    .map(word => word.charAt(0).toUpperCase())
                    .join('')
                    .substring(0, 6);
                $('#code').val(code);
            }
        });

        // Mark code as manually edited
        $('#code').on('input', function() {
            if ($(this).val()) {
                $(this).data('manual', true);
            } else {
                $(this).data('manual', false);
            }
        });

        // Status toggle label update
        $('#is_active').on('change', function() {
            if ($(this).is(':checked')) {
                $('#status_label').removeClass('bg-danger').addClass('bg-success').text('Active');
            } else {
                $('#status_label').removeClass('bg-success').addClass('bg-danger').text('Inactive');
            }
        });

        // Auto-suggest duration based on level
        $('#level').on('change', function() {
            var level = $(this).val();
            var duration = '';
            
            switch(level) {
                case 'certificate':
                    duration = '1';
                    break;
                case 'diploma':
                    duration = '2';
                    break;
                case 'bachelor':
                    duration = '4';
                    break;
                case 'master':
                    duration = '2';
                    break;
                case 'phd':
                    duration = '4';
                    break;
            }
            
            if (duration && !$('#duration_years').val()) {
                $('#duration_years').val(duration);
            }
        });

        // Form validation
        $('#programForm').on('submit', function(e) {
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
