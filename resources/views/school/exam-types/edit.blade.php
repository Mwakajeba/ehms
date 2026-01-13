@extends('layouts.main')

@section('title', 'Edit Exam Type')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Types', 'url' => route('school.exam-types.index'), 'icon' => 'bx bx-category'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Edit Exam Type: {{ $examType->name }}</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.exam-types.update', $examType) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Exam Type Name <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               id="name"
                                               name="name"
                                               value="{{ old('name', $examType->name) }}"
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
                                                  placeholder="Optional description of this exam type">{{ old('description', $examType->description) }}</textarea>
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
                                               value="{{ old('weight', $examType->weight) }}"
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
                                                   {{ old('is_active', $examType->is_active) ? 'checked' : '' }}>
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
                                                <i class="bx bx-info-circle me-1"></i> Exam Type Details
                                            </h6>
                                            <hr />
                                            <dl class="row small">
                                                <dt class="col-sm-5">Created:</dt>
                                                <dd class="col-sm-7">{{ $examType->created_at->format('M d, Y H:i') }}</dd>

                                                <dt class="col-sm-5">Last Updated:</dt>
                                                <dd class="col-sm-7">{{ $examType->updated_at->format('M d, Y H:i') }}</dd>

                                                <dt class="col-sm-5">Total Exams:</dt>
                                                <dd class="col-sm-7">{{ $examType->exams()->count() }}</dd>
                                            </dl>
                                        </div>
                                    </div>

                                    @if($examType->exams()->count() > 0)
                                    <div class="card border-warning mt-3">
                                        <div class="card-body">
                                            <h6 class="card-title text-warning">
                                                <i class="bx bx-exclamation-triangle me-1"></i> Usage Warning
                                            </h6>
                                            <hr />
                                            <p class="small mb-0">
                                                This exam type is currently used in {{ $examType->exams()->count() }} exam(s).
                                                Changes to weight may affect existing grade calculations.
                                            </p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <hr />
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('school.exam-types.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to List
                                        </a>
                                        <button type="submit" class="btn btn-warning">
                                            <i class="bx bx-save me-1"></i> Update Exam Type
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
    // Weight validation
    $('#weight').on('input', function() {
        const value = parseFloat($(this).val());
        if (value < 0) $(this).val(0);
        if (value > 100) $(this).val(100);
    });
});
</script>
@endpush