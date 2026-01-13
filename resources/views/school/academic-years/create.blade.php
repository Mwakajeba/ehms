@extends('layouts.main')

@section('title', 'Create Academic Year')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Management', 'url' => route('school.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Academic Years', 'url' => route('school.academic-years.index'), 'icon' => 'bx bx-calendar'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE ACADEMIC YEAR</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Academic Year</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.academic-years.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Academic Year Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           name="year_name" value="{{ old('year_name') }}"
                                           placeholder="e.g., 2024-2025" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Enter a descriptive name for the academic year</div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                           name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                           name="end_date" value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_current" value="1"
                                               id="is_current" {{ old('is_current') ? 'checked' : '' }}>
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
                                    <i class="bx bx-save me-1"></i> Create Academic Year
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Information</h6>
                    </div>
                    <div class="card-body">
                        <h6>Academic Year Setup</h6>
                        <p class="text-muted small mb-3">
                            Academic years define the period during which students are enrolled and tracked.
                            Setting an academic year as "current" will automatically assign new students to it.
                        </p>

                        <h6>Important Notes:</h6>
                        <ul class="text-muted small">
                            <li>Start and end dates should cover the full academic period</li>
                            <li>Only one academic year can be marked as current</li>
                            <li>You cannot delete academic years that have students assigned</li>
                            <li>Use descriptive names like "2024-2025" or "Fall 2024"</li>
                        </ul>
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