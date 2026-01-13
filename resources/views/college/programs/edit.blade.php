@extends('layouts.main')

@section('title', 'Edit Program')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Programs', 'url' => route('college.programs.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Edit Program', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT PROGRAM</h6>
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
                            <h6><i class="bx bx-bulb me-1"></i> Tips for Editing:</h6>
                            <ul class="mb-0 small">
                                <li>Changing the code may affect existing records</li>
                                <li>Review enrolled students before major changes</li>
                                <li>Duration changes don't affect current students</li>
                                <li>Deactivating will hide program from enrollment</li>
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
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-cog me-2"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('college.programs.show', $program->id) }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                            <i class="bx bx-show me-1"></i> View Program
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bx bx-trash me-1"></i> Delete Program
                        </button>
                    </div>
                </div>

                <!-- Record Information -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-history me-2"></i> Record Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small text-muted"><i class="bx bx-calendar-plus me-1"></i>Created</span>
                            <span class="small fw-bold">{{ $program->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small text-muted"><i class="bx bx-calendar-check me-1"></i>Updated</span>
                            <span class="small fw-bold">{{ $program->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small text-muted"><i class="bx bx-group me-1"></i>Students</span>
                            <span class="badge bg-primary">{{ $program->students()->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted"><i class="bx bx-check-shield me-1"></i>Status</span>
                            @if($program->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <div class="col-12 col-lg-8 order-1 order-lg-1">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Program: {{ $program->code }}</h5>
                        </div>
                        <hr />

                        <form action="{{ route('college.programs.update', $program->id) }}" method="POST" id="programForm">
                            @csrf
                            @method('PUT')

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
                                                       id="name" name="name" value="{{ old('name', $program->name) }}"
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
                                                       id="code" name="code" value="{{ old('code', $program->code) }}"
                                                       placeholder="e.g., BSC-CS" required>
                                                @error('code')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Unique code for this program
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
                                                        <option value="{{ $department->id }}" {{ old('department_id', $program->department_id) == $department->id ? 'selected' : '' }}>
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
                                                           {{ old('is_active', $program->is_active) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_active">
                                                        <span id="status_label" class="badge {{ $program->is_active ? 'bg-success' : 'bg-danger' }}">
                                                            {{ $program->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
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
                                                    <option value="certificate" {{ old('level', $program->level) == 'certificate' ? 'selected' : '' }}>Certificate</option>
                                                    <option value="diploma" {{ old('level', $program->level) == 'diploma' ? 'selected' : '' }}>Diploma</option>
                                                    <option value="bachelor" {{ old('level', $program->level) == 'bachelor' ? 'selected' : '' }}>Bachelor's Degree</option>
                                                    <option value="master" {{ old('level', $program->level) == 'master' ? 'selected' : '' }}>Master's Degree</option>
                                                    <option value="phd" {{ old('level', $program->level) == 'phd' ? 'selected' : '' }}>PhD</option>
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
                                                    <option value="1" {{ old('duration_years', $program->duration_years) == '1' ? 'selected' : '' }}>1 Year</option>
                                                    <option value="2" {{ old('duration_years', $program->duration_years) == '2' ? 'selected' : '' }}>2 Years</option>
                                                    <option value="3" {{ old('duration_years', $program->duration_years) == '3' ? 'selected' : '' }}>3 Years</option>
                                                    <option value="4" {{ old('duration_years', $program->duration_years) == '4' ? 'selected' : '' }}>4 Years</option>
                                                    <option value="5" {{ old('duration_years', $program->duration_years) == '5' ? 'selected' : '' }}>5 Years</option>
                                                    <option value="6" {{ old('duration_years', $program->duration_years) == '6' ? 'selected' : '' }}>6 Years</option>
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
                                                  placeholder="Brief overview of what the program covers...">{{ old('description', $program->description) }}</textarea>
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
                                                  placeholder="What students will achieve upon completion...">{{ old('objectives', $program->objectives) }}</textarea>
                                        @error('objectives')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Admission Requirements</label>
                                        <textarea class="form-control @error('requirements') is-invalid @enderror"
                                                  id="requirements" name="requirements" rows="3"
                                                  placeholder="Entry requirements for admission...">{{ old('requirements', $program->requirements) }}</textarea>
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
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Program
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bx bx-trash me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bx bx-error-circle text-danger" style="font-size: 64px;"></i>
                <h5 class="mt-3">Are you sure you want to delete this program?</h5>
                <p class="text-muted mb-0">
                    <strong>{{ $program->code }} - {{ $program->name }}</strong>
                </p>
                @if($program->students()->count() > 0)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bx bx-exclamation-triangle me-1"></i>
                        This program has <strong>{{ $program->students()->count() }}</strong> enrolled students.
                        Deleting may cause data issues.
                    </div>
                @endif
                <p class="text-muted small mt-3">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <form action="{{ route('college.programs.destroy', $program->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Delete Program
                    </button>
                </form>
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
        // Status toggle label update
        $('#is_active').on('change', function() {
            if ($(this).is(':checked')) {
                $('#status_label').removeClass('bg-danger').addClass('bg-success').text('Active');
            } else {
                $('#status_label').removeClass('bg-success').addClass('bg-danger').text('Inactive');
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
