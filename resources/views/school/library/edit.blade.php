@extends('layouts.main')

@section('title', 'Edit Library Material')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Digital Library', 'url' => route('school.library.index'), 'icon' => 'bx bx-library'],
            ['label' => 'Edit Material', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT LIBRARY MATERIAL</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-purple"></i></div>
                            <h5 class="mb-0 text-purple">Edit Material</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.library.update', $material->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information -->
                            <h6 class="text-purple mb-3"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $material->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            <option value="pdf_book" {{ old('type', $material->type) == 'pdf_book' ? 'selected' : '' }}>PDF Book</option>
                                            <option value="notes" {{ old('type', $material->type) == 'notes' ? 'selected' : '' }}>Notes</option>
                                            <option value="past_paper" {{ old('type', $material->type) == 'past_paper' ? 'selected' : '' }}>Past Paper</option>
                                            <option value="assignment" {{ old('type', $material->type) == 'assignment' ? 'selected' : '' }}>Assignment</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $material->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- File Upload -->
                            <h6 class="text-purple mb-3 mt-4"><i class="bx bx-file me-2"></i>File</h6>
                            <div class="mb-3">
                                <label class="form-label">Current File</label>
                                <div class="alert alert-info">
                                    <i class="bx bx-file me-1"></i>
                                    <a href="{{ $material->url }}" target="_blank">{{ $material->original_name }}</a>
                                    <small class="d-block text-muted mt-1">{{ $material->formatted_file_size }}</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label">Replace File (Optional)</label>
                                <input type="file" class="form-control @error('file') is-invalid @enderror" id="file" name="file" accept=".pdf,.doc,.docx">
                                <small class="form-text text-muted">Leave empty to keep current file. Accepted formats: PDF, DOC, DOCX (Max: 10MB)</small>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Academic Setup -->
                            <h6 class="text-purple mb-3 mt-4"><i class="bx bx-book me-2"></i>Academic Setup</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="academic_year_id" class="form-label">Academic Year</label>
                                        <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id">
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" {{ old('academic_year_id', $material->academic_year_id) == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('academic_year_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="class_id" class="form-label">Class</label>
                                        <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
                                            <option value="">All Classes</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id', $material->class_id) == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="subject_id" class="form-label">Subject</label>
                                        <select class="form-select @error('subject_id') is-invalid @enderror" id="subject_id" name="subject_id">
                                            <option value="">All Subjects</option>
                                            @foreach($subjects as $subject)
                                                <option value="{{ $subject->id }}" {{ old('subject_id', $material->subject_id) == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('subject_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <h6 class="text-purple mb-3 mt-4"><i class="bx bx-cog me-2"></i>Status</h6>
                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="draft" {{ old('status', $material->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $material->status) == 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="archived" {{ old('status', $material->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ route('school.library.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-purple">
                                    <i class="bx bx-save me-1"></i> Update Material
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
    .text-purple {
        color: #6f42c1 !important;
    }

    .btn-purple {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }

    .btn-purple:hover {
        background-color: #5a32a3;
        border-color: #5a32a3;
        color: white;
    }
</style>
@endpush

