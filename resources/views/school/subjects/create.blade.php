@extends('layouts.main')

@section('title', 'Create New Subject')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subjects', 'url' => route('school.subjects.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW SUBJECT</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Academic Subject</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.subjects.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., Mathematics, Physics, English" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="code" class="form-label">Subject Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="e.g., MATH101, PHYS201" required>
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="short_name" class="form-label">Short Name</label>
                                        <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name') }}" placeholder="e.g., Math, Phys">
                                        @error('short_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="subject_type" class="form-label">Subject Type</label>
                                        <select class="form-control @error('subject_type') is-invalid @enderror" id="subject_type" name="subject_type">
                                            <option value="theory" {{ old('subject_type', 'theory') == 'theory' ? 'selected' : '' }}>Theory</option>
                                            <option value="practical" {{ old('subject_type') == 'practical' ? 'selected' : '' }}>Practical</option>
                                        </select>
                                        @error('subject_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="requirement_type" class="form-label">Requirement Type <span class="text-danger">*</span></label>
                                        <select class="form-control @error('requirement_type') is-invalid @enderror" id="requirement_type" name="requirement_type" required>
                                            <option value="compulsory" {{ old('requirement_type', 'compulsory') == 'compulsory' ? 'selected' : '' }}>Compulsory</option>
                                            <option value="optional" {{ old('requirement_type') == 'optional' ? 'selected' : '' }}>Optional</option>
                                        </select>
                                        <small class="text-muted">Used in timetable entries</small>
                                        @error('requirement_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="passing_marks" class="form-label">Passing Marks</label>
                                        <input type="number" step="0.01" class="form-control @error('passing_marks') is-invalid @enderror" id="passing_marks" name="passing_marks" value="{{ old('passing_marks', 40.00) }}" min="0">
                                        @error('passing_marks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="total_marks" class="form-label">Total Marks</label>
                                        <input type="number" step="0.01" class="form-control @error('total_marks') is-invalid @enderror" id="total_marks" name="total_marks" value="{{ old('total_marks', 100.00) }}" min="0">
                                        @error('total_marks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Optional description of the subject">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Subject
                                    </label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.subjects.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Subjects
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Subject
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
                            <i class="bx bx-info-circle me-1 text-info"></i> Information
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <h6>What is a Subject?</h6>
                            <p class="small text-muted">
                                A subject represents a specific course or area of study within the academic curriculum.
                                Each subject has its own code, credit hours, and assessment criteria.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Subject Types:</h6>
                            <ul class="small text-muted mb-2">
                                <li><strong>Theory:</strong> Classroom-based subjects</li>
                                <li><strong>Practical:</strong> Lab or hands-on subjects</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Examples:</h6>
                            <ul class="small text-muted">
                                <li>Mathematics (MATH101)</li>
                                <li>Physics (PHYS201)</li>
                                <li>Chemistry Lab (CHEM301-P)</li>
                                <li>English Literature (ENG401)</li>
                            </ul>
                        </div>
                        <div class="alert alert-light small">
                            <i class="bx bx-bulb me-1 text-warning"></i>
                            <strong>Tip:</strong> Use descriptive subject codes that are easy to identify and sort.
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
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-capitalize first letter for subject name
        $('#name').on('input', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
        });

        // Auto-uppercase for subject code
        $('#code').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Auto-generate short name from subject name if empty
        $('#name').on('blur', function() {
            const name = $(this).val();
            const shortNameField = $('#short_name');

            if (!shortNameField.val() && name) {
                // Take first word or first 4 characters
                const shortName = name.split(' ')[0].substring(0, 4);
                shortNameField.val(shortName.charAt(0).toUpperCase() + shortName.slice(1).toLowerCase());
            }
        });

        console.log('Create subject form loaded');
    });
</script>
@endpush