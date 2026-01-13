@extends('layouts.main')

@section('title', 'Create Subject Group')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Subjects', 'url' => route('school.subjects.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subject Groups', 'url' => route('school.subject-groups.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE SUBJECT GROUP</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Subject Group</h5>
                            <div></div>
                        </div>
                        <hr />

                        <form action="{{ route('school.subject-groups.store') }}" method="POST">
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
                                    <label class="form-label">Subject Group Code</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code') }}"
                                           placeholder="e.g., SCI, LANG, ARTS" style="text-transform: uppercase;">
                                    <small class="form-text text-muted">Optional: Unique code to identify this subject group</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Subject Group Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required
                                           placeholder="e.g., Science Subjects, Languages, Arts & Humanities">
                                    <small class="form-text text-muted">Full descriptive name of the subject group</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                    <select class="form-select @error('class_id') is-invalid @enderror"
                                            id="class_id" name="class_id" required>
                                        <option value="">Choose class...</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Select the class this subject group belongs to</small>
                                    @error('class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Assign Subjects</label>
                                    <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                        @if($subjects->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 50px;">
                                                                <div class="form-check mb-0">
                                                                    <input class="form-check-input" type="checkbox" id="select-all-subjects">
                                                                    <label class="form-check-label mb-0" for="select-all-subjects">
                                                                        Assign All
                                                                    </label>
                                                                </div>
                                                            </th>
                                                            <th>Subject</th>
                                                            <th style="width: 120px;">Sort Order</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($subjects as $index => $subject)
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check mb-0">
                                                                        <input class="form-check-input subject-checkbox" type="checkbox"
                                                                               id="subject_{{ $subject->id }}" name="subject_ids[]"
                                                                               value="{{ $subject->id }}"
                                                                               {{ in_array($subject->id, old('subject_ids', [])) ? 'checked' : '' }}>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <label class="form-check-label mb-0" for="subject_{{ $subject->id }}">
                                                                        <strong>{{ $subject->code }}</strong> - {{ $subject->name }}
                                                                        @if($subject->short_name)
                                                                            <small class="text-muted">({{ $subject->short_name }})</small>
                                                                        @endif
                                                                        @if($subject->subjectGroups->count() > 0)
                                                                            <small class="text-warning">(Assigned to {{ $subject->subjectGroups->count() }} group{{ $subject->subjectGroups->count() > 1 ? 's' : '' }})</small>
                                                                        @endif
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control form-control-sm sort-order-input"
                                                                           name="sort_orders[{{ $subject->id }}]"
                                                                           value="{{ old('sort_orders.' . $subject->id) }}"
                                                                           min="1" placeholder="1">
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">No subjects available. <a href="{{ route('school.subjects.create') }}" target="_blank">Create subjects first</a>.</p>
                                        @endif
                                    </div>
                                    <small class="form-text text-muted">Select subjects to assign to this subject group and set their sort order. Lower numbers appear first. Subjects can belong to multiple subject groups for different classes.</small>
                                    @error('subject_ids')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active Subject Group
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Inactive subject groups will not be available for new assignments. Keep checked for active groups.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.subject-groups.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Subject Groups
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Subject Group
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
                            <h6>What are Subject Groups?</h6>
                            <p class="small text-muted">
                                Subject groups help organize and categorize subjects for better academic management and reporting.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Class Selection:</h6>
                            <p class="small text-muted">
                                Choose the specific class for which this subject group is being created. Subject groups are class-specific, but subjects can belong to multiple subject groups across different classes.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Subject Assignment:</h6>
                            <p class="small text-muted">
                                Select subjects to assign to this subject group. Subjects can be assigned to multiple subject groups, allowing them to be used across different classes and academic programs.
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

        // Auto-uppercase for subject group code
        $('#code').on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        // Handle select all checkbox
        $('#select-all-subjects').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.subject-checkbox').each(function() {
                $(this).prop('checked', isChecked);
                const subjectId = $(this).val();
                const sortOrderInput = $(`input[name="sort_orders[${subjectId}]"]`);
                
                // Clear sort order when unchecked via select all
                if (!isChecked) {
                    sortOrderInput.val('');
                    sortOrderInput.removeClass('is-invalid');
                }
            });
        });

        // Handle subject checkbox changes
        $('.subject-checkbox').on('change', function() {
            const subjectId = $(this).val();
            const sortOrderInput = $(`input[name="sort_orders[${subjectId}]"]`);

            // Clear sort order when unchecked
            if (!$(this).is(':checked')) {
                sortOrderInput.val('');
                sortOrderInput.removeClass('is-invalid');
            }

            // Update select all checkbox state
            const totalCheckboxes = $('.subject-checkbox').length;
            const checkedCheckboxes = $('.subject-checkbox:checked').length;
            $('#select-all-subjects').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
        });

        // Validate sort orders before form submission
        $('form').on('submit', function(e) {
            const checkedBoxes = $('.subject-checkbox:checked');
            let hasErrors = false;

            checkedBoxes.each(function() {
                const subjectId = $(this).val();
                const sortOrderInput = $(`input[name="sort_orders[${subjectId}]"]`);
                const sortOrder = sortOrderInput.val().trim();

                if (!sortOrder || isNaN(sortOrder) || parseInt(sortOrder) < 1) {
                    sortOrderInput.addClass('is-invalid');
                    hasErrors = true;
                } else {
                    sortOrderInput.removeClass('is-invalid');
                }
            });

            if (hasErrors) {
                e.preventDefault();
                alert('Please provide valid sort orders (numbers greater than 0) for all selected subjects.');
                return false;
            }
        });
    });
</script>
@endpush