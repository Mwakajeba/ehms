@extends('layouts.main')

@section('title', 'Create Grade Scale')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Grade Scales', 'url' => route('school.grade-scales.index'), 'icon' => 'bx bx-star'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE GRADE SCALE</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-star me-1 font-22 text-danger"></i></div>
                            <h5 class="mb-0 text-danger">Create New Grade Scale</h5>
                        </div>
                        <hr />

                        <form id="grade-scale-form" action="{{ route('school.grade-scales.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-white">
                                            <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i>Basic Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Grade Scale Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                                <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}" {{ (isset($currentAcademicYear) && $currentAcademicYear && $currentAcademicYear->id == $year->id) || old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                                            {{ $year->year_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('academic_year_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if(isset($currentAcademicYear) && $currentAcademicYear)
                                                    <div class="form-text text-success">
                                                        <i class="bx bx-info-circle me-1"></i>Current academic year is automatically selected.
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="mb-3">
                                                <label for="max_marks" class="form-label">Maximum Marks <span class="text-danger">*</span></label>
                                                <select class="form-select @error('max_marks') is-invalid @enderror" id="max_marks" name="max_marks" required>
                                                    <option value="">Select Maximum Marks</option>
                                                    <option value="50" {{ old('max_marks') == '50' ? 'selected' : '' }}>50 Marks</option>
                                                    <option value="100" {{ old('max_marks') == '100' ? 'selected' : '' }}>100 Marks</option>
                                                </select>
                                                @error('max_marks')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="passed_average_point" class="form-label">Passed Average Point <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control @error('passed_average_point') is-invalid @enderror" id="passed_average_point" name="passed_average_point" value="{{ old('passed_average_point') }}" required>
                                                @error('passed_average_point')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    <i class="bx bx-info-circle me-1"></i>Minimum average score required to pass (e.g., 50.00)
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="is_active">
                                                        Active
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Grades Configuration -->
                                <div class="col-md-6">
                                    <div class="card border-info">
                                        <div class="card-header bg-info text-white">
                                            <h6 class="mb-0"><i class="bx bx-list-ul me-1"></i>Grade Ranges</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="grades-container">
                                                <!-- Grades will be added here dynamically -->
                                            </div>

                                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-grade-btn">
                                                <i class="bx bx-plus me-1"></i>Add Grade
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('school.grade-scales.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i>Back to Grade Scales
                                        </a>
                                        <button type="submit" class="btn btn-danger">
                                            <i class="bx bx-save me-1"></i>Create Grade Scale
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

@push('styles')
<style>
    .grade-row {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        position: relative;
    }

    .grade-row .btn-remove {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
    }

    .form-check-input:checked {
        background-color: #dc3545;
        border-color: #dc3545;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let gradeIndex = 0;
    let nextSortOrder = 1;

    // Add initial grade row
    addGradeRow();

    // Add grade button click
    $('#add-grade-btn').click(function() {
        addGradeRow();
    });

    // Remove grade row
    $(document).on('click', '.btn-remove', function() {
        $(this).closest('.grade-row').remove();
        updateGradeOrder();
    });

    function addGradeRow(gradeData = null) {
        const sortOrder = gradeData ? gradeData.sort_order : nextSortOrder;
        nextSortOrder++;
        const gradeHtml = `
            <div class="grade-row" data-index="${gradeIndex}">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                    <i class="bx bx-trash"></i>
                </button>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Grade Letter <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="grades[${gradeIndex}][grade_letter]" value="${gradeData ? gradeData.grade_letter : ''}" required maxlength="5">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Grade Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="grades[${gradeIndex}][grade_name]" value="${gradeData ? gradeData.grade_name : ''}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Min Marks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="grades[${gradeIndex}][min_marks]" value="${gradeData ? gradeData.min_marks : ''}" required min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Max Marks <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="grades[${gradeIndex}][max_marks]" value="${gradeData ? gradeData.max_marks : ''}" required min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Grade Point</label>
                            <input type="number" class="form-control" name="grades[${gradeIndex}][grade_point]" value="${gradeData ? gradeData.grade_point : ''}" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="grades[${gradeIndex}][sort_order]" value="${sortOrder}" min="1">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea class="form-control" name="grades[${gradeIndex}][remarks]" rows="2">${gradeData ? gradeData.remarks : ''}</textarea>
                </div>
            </div>
        `;

        $('#grades-container').append(gradeHtml);
        gradeIndex++;
    }

    function updateGradeOrder() {
        // This function can be used if we want to auto-reorder after removal
        // For now, we'll keep the existing sort orders
    }

    // Form validation
    $('#grade-scale-form').submit(function(e) {
        // Validate that at least one grade is added
        if ($('.grade-row').length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please add at least one grade range.'
            });
            return false;
        }

        // Get the selected maximum marks for the grade scale
        const gradeScaleMaxMarks = parseInt($('#max_marks').val());

        // Validate that grade max_marks don't exceed grade scale max_marks
        let gradeValidationError = false;
        $('.grade-row').each(function(index) {
            const gradeMaxMarks = parseFloat($(this).find('input[name*="[max_marks]"]').val());
            if (gradeMaxMarks > gradeScaleMaxMarks) {
                $(this).find('input[name*="[max_marks]"]').addClass('is-invalid');
                gradeValidationError = true;
            } else {
                $(this).find('input[name*="[max_marks]"]').removeClass('is-invalid');
            }
        });

        if (gradeValidationError) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Grade max marks cannot exceed the grade scale maximum marks (' + gradeScaleMaxMarks + ').'
            });
            return false;
        }

        // Validate grade ranges don't overlap
        let isValid = true;
        const grades = [];

        $('.grade-row').each(function() {
            const minMarks = parseFloat($(this).find('input[name*="[min_marks]"]').val());
            const maxMarks = parseFloat($(this).find('input[name*="[max_marks]"]').val());

            if (minMarks >= maxMarks) {
                isValid = false;
                $(this).find('input[name*="[max_marks]"]').addClass('is-invalid');
            } else {
                $(this).find('input[name*="[max_marks]"]').removeClass('is-invalid');
            }

            grades.push({ min: minMarks, max: maxMarks });
        });

        // Check for overlapping ranges
        for (let i = 0; i < grades.length; i++) {
            for (let j = i + 1; j < grades.length; j++) {
                if (!(grades[i].max <= grades[j].min || grades[i].min >= grades[j].max)) {
                    isValid = false;
                    break;
                }
            }
            if (!isValid) break;
        }

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Grade ranges cannot overlap and minimum marks must be less than maximum marks.'
            });
            return false;
        }
    });
});
</script>
@endpush