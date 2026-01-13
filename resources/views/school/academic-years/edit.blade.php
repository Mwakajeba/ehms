@extends('layouts.main')

@section('title', 'Edit Academic Year')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Management', 'url' => route('school.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Academic Years', 'url' => route('school.academic-years.index'), 'icon' => 'bx bx-calendar'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT ACADEMIC YEAR</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Academic Year: {{ $academicYear->year_name }}</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.academic-years.update', $academicYear) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Academic Year Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           name="year_name" value="{{ old('year_name', $academicYear->year_name) }}"
                                           placeholder="e.g., 2024-2025" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Enter a descriptive name for the academic year</div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                           name="start_date" value="{{ old('start_date', $academicYear->start_date ? $academicYear->start_date->format('Y-m-d') : '') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                           name="end_date" value="{{ old('end_date', $academicYear->end_date ? $academicYear->end_date->format('Y-m-d') : '') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_current" value="1"
                                               id="is_current" {{ old('is_current', $academicYear->is_current) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_current">
                                            Set as Current Academic Year
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        Check this box if this is the current academic year. Only one academic year can be current at a time.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.academic-years.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Academic Years
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Academic Year
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Academic Year Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Created:</strong> {{ $academicYear->created_at->format('M d, Y H:i') }}
                        </div>
                        <div class="mb-3">
                            <strong>Last Updated:</strong> {{ $academicYear->updated_at->format('M d, Y H:i') }}
                        </div>
                        <div class="mb-3">
                            <strong>Students Assigned:</strong> {{ $academicYear->students_count ?? 0 }}
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong>
                            @if($academicYear->is_current)
                                <span class="badge bg-success">Current</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </div>

                        @if(($academicYear->students_count ?? 0) > 0)
                            <div class="alert alert-warning">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> This academic year has students assigned to it.
                                Some changes may be restricted.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Validate date range
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.querySelector('input[name="start_date"]');
        const endDateInput = document.querySelector('input[name="end_date"]');

        function validateDates() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (startDate && endDate && startDate >= endDate) {
                endDateInput.setCustomValidity('End date must be after start date');
                endDateInput.reportValidity();
            } else {
                endDateInput.setCustomValidity('');
            }
        }

        startDateInput.addEventListener('change', validateDates);
        endDateInput.addEventListener('change', validateDates);
    });
</script>
@endpush