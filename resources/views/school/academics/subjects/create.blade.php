@extends('layouts.main')

@section('title', 'Create Subject')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Academics', 'url' => route('school.academics.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Subjects', 'url' => route('school.academics.subjects.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE SUBJECT</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Subject</h5>
                            <div></div>
                        </div>
                        <hr />

                        <form action="{{ route('school.academics.subjects.store') }}" method="POST">
                            @csrf

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code') }}" required
                                           placeholder="e.g., MATH101, ENG202, SCI301" style="text-transform: uppercase;">
                                    <small class="form-text text-muted">Unique code to identify this subject</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required
                                           placeholder="e.g., Mathematics, English Literature, Physics">
                                    <small class="form-text text-muted">Full descriptive name of the subject</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject Short-Name</label>
                                    <input type="text" class="form-control @error('short_name') is-invalid @enderror"
                                           id="short_name" name="short_name" value="{{ old('short_name') }}"
                                           placeholder="e.g., Math, Eng, Sci">
                                    <small class="form-text text-muted">Short abbreviated name for the subject</small>
                                    @error('short_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject Type</label>
                                    <select class="form-select @error('subject_type') is-invalid @enderror"
                                            id="subject_type" name="subject_type">
                                        <option value="">Choose subject type...</option>
                                        <option value="theory" {{ old('subject_type') == 'theory' ? 'selected' : '' }}>Theory</option>
                                        <option value="practical" {{ old('subject_type') == 'practical' ? 'selected' : '' }}>Practical</option>
                                    </select>
                                    <small class="form-text text-muted">Select whether this is a theory or practical subject</small>
                                    @error('subject_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                           id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                                           min="0" placeholder="e.g., 1, 2, 3">
                                    <small class="form-text text-muted">Order in which subjects appear in lists (lower numbers first)</small>
                                    @error('sort_order')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description" name="description" rows="3"
                                              placeholder="Describe the subject content, objectives, and what students will learn...">{{ old('description') }}</textarea>
                                    <small class="form-text text-muted">Optional: Provide details about the subject's content and objectives</small>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Credit Hours</label>
                                    <input type="number" class="form-control @error('credit_hours') is-invalid @enderror"
                                           id="credit_hours" name="credit_hours" value="{{ old('credit_hours') }}"
                                           min="0" step="0.5" placeholder="e.g., 3, 4, 2.5">
                                    <small class="form-text text-muted">Number of credit hours (can be decimal)</small>
                                    @error('credit_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Passing Marks (%)</label>
                                    <input type="number" class="form-control @error('passing_marks') is-invalid @enderror"
                                           id="passing_marks" name="passing_marks" value="{{ old('passing_marks', 40) }}"
                                           min="0" max="100" placeholder="e.g., 40, 50">
                                    <small class="form-text text-muted">Minimum marks required to pass (usually 40-50%)</small>
                                    @error('passing_marks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Total Marks</label>
                                    <input type="number" class="form-control @error('total_marks') is-invalid @enderror"
                                           id="total_marks" name="total_marks" value="{{ old('total_marks', 100) }}"
                                           min="0" placeholder="e.g., 100, 200">
                                    <small class="form-text text-muted">Maximum marks possible for this subject</small>
                                    @error('total_marks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Subject
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Inactive subjects will not be available for curriculum assignments. Keep checked for active subjects.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.academics.subjects.index') }}" class="btn btn-secondary">
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
                            <h6>What are Subjects?</h6>
                            <p class="small text-muted">
                                Subjects represent individual courses or academic disciplines that students study.
                                Each subject can have credit hours, passing marks, and detailed descriptions.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Subject Code Format:</h6>
                            <ul class="small text-muted">
                                <li>Use uppercase letters and numbers</li>
                                <li>Examples: MATH101, ENG202, SCI301</li>
                                <li>Keep it unique and memorable</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6>Credit Hours:</h6>
                            <p class="small text-muted">
                                Credit hours represent the weight or importance of a subject. Common values are 2, 3, 4, or 5 hours.
                            </p>
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
    .form-label {
        font-weight: 600;
    }

    .card {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-text {
        font-size: 0.875rem;
        color: #6c757d;
    }

    .form-control:focus, .form-select:focus {
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.2rem rgba(13, 202, 240, 0.25);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-capitalize first letter of name
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

        // Auto-fill total marks if not set and passing marks is set
        $('#passing_marks').on('change', function() {
            let passingMarks = parseFloat($(this).val());
            let totalMarks = parseFloat($('#total_marks').val());

            if (passingMarks > 0 && (!totalMarks || totalMarks === 0)) {
                // If passing marks is set but total marks is not, assume total is 100
                $('#total_marks').val(100);
            }
        });
    });
</script>
@endpush