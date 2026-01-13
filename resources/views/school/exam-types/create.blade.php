@extends('layouts.main')

@section('title', 'Create Exam Type')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Types', 'url' => route('school.exam-types.index'), 'icon' => 'bx bx-category'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Create New Exam Type</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.exam-types.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Exam Type Name <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               id="name"
                                               name="name"
                                               value="{{ old('name') }}"
                                               placeholder="e.g., Mid-term Exam, Final Exam, Quiz"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description"
                                                  name="description"
                                                  rows="3"
                                                  placeholder="Optional description of this exam type">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="weight" class="form-label">Weight (%) <span class="text-danger">*</span></label>
                                        <input type="number"
                                               class="form-control @error('weight') is-invalid @enderror"
                                               id="weight"
                                               name="weight"
                                               value="{{ old('weight', 0) }}"
                                               min="0"
                                               max="100"
                                               step="0.01"
                                               placeholder="0.00"
                                               required>
                                        <div class="form-text">Weight determines the importance of this exam type in final grading (0-100%)</div>
                                        @error('weight')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_active"
                                                   name="is_active"
                                                   value="1"
                                                   {{ old('is_active', true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Active
                                            </label>
                                        </div>
                                        <div class="form-text">Inactive exam types cannot be used when creating new exams</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="card-title text-info">
                                                <i class="bx bx-info-circle me-1"></i> Information
                                            </h6>
                                            <hr />
                                            <ul class="list-unstyled small">
                                                <li class="mb-2">
                                                    <strong>Name:</strong> Choose a clear, descriptive name for the exam type.
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Weight:</strong> Determines how much this exam contributes to the final grade.
                                                </li>
                                                <li class="mb-2">
                                                    <strong>Status:</strong> Only active exam types can be selected when creating exams.
                                                </li>
                                                <li class="mb-0">
                                                    <strong>Examples:</strong> Mid-term Exam (30%), Final Exam (50%), Quiz (10%)
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
                                        <a href="{{ route('school.exam-types.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to List
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i> Create Exam Type
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
    // Auto-generate description based on name if empty
    $('#name').on('input', function() {
        const name = $(this).val();
        const description = $('#description').val();

        if (!description && name) {
            // You can add logic here to suggest descriptions based on common exam types
            const suggestions = {
                'mid-term': 'Mid-term examination conducted halfway through the academic term',
                'final': 'Final examination at the end of the academic term',
                'quiz': 'Short assessment to test understanding of specific topics',
                'assignment': 'Coursework assignment for evaluation',
                'project': 'Major project work assessment',
                'practical': 'Practical examination or lab assessment'
            };

            const lowerName = name.toLowerCase();
            for (const [key, value] of Object.entries(suggestions)) {
                if (lowerName.includes(key)) {
                    $('#description').val(value);
                    break;
                }
            }
        }
    });

    // Weight validation
    $('#weight').on('input', function() {
        const value = parseFloat($(this).val());
        if (value < 0) $(this).val(0);
        if (value > 100) $(this).val(100);
    });
});
</script>
@endpush