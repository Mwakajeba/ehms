@extends('layouts.main')

@section('title', 'Edit Subject Group')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Subjects', 'url' => route('school.subjects.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Subject Groups', 'url' => route('school.subject-groups.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT SUBJECT GROUP</h6>
        <hr />

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Subject Group Information</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('school.subject-groups.update', $subjectGroup->hashid) }}" method="POST">
                            @csrf
                            @method('PUT')

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
                                    <label for="code" class="form-label">Subject Group Code</label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror"
                                           id="code" name="code" value="{{ old('code', $subjectGroup->code) }}"
                                           style="text-transform: uppercase;">
                                    <small class="form-text text-muted">Optional: Unique code to identify this subject group</small>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                    <select class="form-select @error('class_id') is-invalid @enderror"
                                            id="class_id" name="class_id" required>
                                        <option value="">Choose class...</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id', $subjectGroup->class_id) == $class->id ? 'selected' : '' }}>
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
                                    <label for="name" class="form-label">Subject Group Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $subjectGroup->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="subjects" class="form-label">Assign Subjects</label>
                                    <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                        @if($subjects->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 50px;">Assign</th>
                                                            <th>Subject</th>
                                                            <th style="width: 120px;">Sort Order</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($subjects as $index => $subject)
                                                            @php
                                                                $isAssigned = $subjectGroup->subjects->contains('id', $subject->id);
                                                                $currentSortOrder = $isAssigned ? $subjectGroup->subjects->find($subject->id)->pivot->sort_order : '';
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check mb-0">
                                                                        <input class="form-check-input subject-checkbox" type="checkbox"
                                                                               id="subject_{{ $subject->id }}" name="subject_ids[]"
                                                                               value="{{ $subject->id }}"
                                                                               {{ in_array($subject->id, old('subject_ids', $subjectGroup->subjects->pluck('id')->toArray())) ? 'checked' : ($isAssigned ? 'checked' : '') }}>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <label class="form-check-label mb-0" for="subject_{{ $subject->id }}">
                                                                        <strong>{{ $subject->code }}</strong> - {{ $subject->name }}
                                                                        @if($subject->short_name)
                                                                            <small class="text-muted">({{ $subject->short_name }})</small>
                                                                        @endif
                                                                        @if($subject->subjectGroups->where('id', '!=', $subjectGroup->id)->count() > 0)
                                                                            <small class="text-warning">(Also in {{ $subject->subjectGroups->where('id', '!=', $subjectGroup->id)->count() }} other group{{ $subject->subjectGroups->where('id', '!=', $subjectGroup->id)->count() > 1 ? 's' : '' }})</small>
                                                                        @endif
                                                                    </label>
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control form-control-sm sort-order-input"
                                                                           name="sort_orders[{{ $subject->id }}]"
                                                                           value="{{ old('sort_orders.' . $subject->id, $currentSortOrder) }}"
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
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                               {{ old('is_active', $subjectGroup->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Inactive subject groups will not be available for new subject assignments.</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('school.subject-groups.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Subject Group
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Associated Subjects -->
                @if($subjectGroup->subjects->count() > 0)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="card-title mb-0">Associated Subjects ({{ $subjectGroup->subjects->count() }})</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($subjectGroup->subjects as $subject)
                                            <tr>
                                                <td><code>{{ $subject->code }}</code></td>
                                                <td>{{ $subject->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $subject->is_active ? 'success' : 'secondary' }}">
                                                        {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1 text-info"></i> Information
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <h6>Editing Subject Groups:</h6>
                            <p class="small text-muted">
                                Modify subject group details and reassign subjects. Changes will affect all academic records using this group.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Subject Assignment:</h6>
                            <p class="small text-muted">
                                Select subjects to assign to this subject group. Subjects can belong to multiple subject groups, allowing flexibility across different classes and academic programs.
                                Subjects already assigned to other groups will show how many other groups they're in.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Class Association:</h6>
                            <p class="small text-muted">
                                Subject groups are linked to specific classes. Ensure the selected class is appropriate for the assigned subjects.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Impact of Changes:</h6>
                            <ul class="small text-muted">
                                <li>Subject assignments may affect curriculum planning</li>
                                <li>Academic reports and analytics will reflect changes</li>
                                <li>Student records remain unaffected</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@push('styles')
<style>
    .form-label {
        font-weight: 600;
    }

    .card {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .table th {
        font-size: 0.875rem;
        font-weight: 600;
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

        // Handle subject checkbox changes
        $('.subject-checkbox').on('change', function() {
            const subjectId = $(this).val();
            const sortOrderInput = $(`input[name="sort_orders[${subjectId}]"]`);

            // Clear sort order when unchecked
            if (!$(this).is(':checked')) {
                sortOrderInput.val('');
                sortOrderInput.removeClass('is-invalid');
            }
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