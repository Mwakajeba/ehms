@extends('layouts.main')

@section('title', 'Create Academic Year')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Academic Years', 'url' => route('college.academic-years.index'), 'icon' => 'bx bx-calendar'],
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

                        <form action="{{ route('college.academic-years.store') }}" method="POST" id="academicYearForm">
                            @csrf

                            <!-- Basic Information Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-info-circle me-2 text-primary"></i> Basic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Academic Year Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('year_name') is-invalid @enderror"
                                                       id="year_name" name="year_name" value="{{ old('year_name', request('year_name')) }}"
                                                       placeholder="e.g., 2024-2025" required>
                                                @error('year_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Enter a descriptive name for the academic year
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                                    <option value="">Select Status</option>
                                                    <option value="upcoming" {{ old('status', request('status', 'upcoming')) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                                    <option value="active" {{ old('status', request('status')) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="completed" {{ old('status', request('status')) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="cancelled" {{ old('status', request('status')) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                                       id="start_date" name="start_date" value="{{ old('start_date', request('start_date')) }}" required>
                                                @error('start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                                       id="end_date" name="end_date" value="{{ old('end_date', request('end_date')) }}" required>
                                                @error('end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="3"
                                                  placeholder="Optional description for this academic year">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Provide additional details about the academic year
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Settings Section -->
                            <div class="card border-secondary mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-cog me-2 text-secondary"></i> Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="set_as_current" value="1"
                                                   id="set_as_current" {{ old('set_as_current') ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold" for="set_as_current">
                                                Set as Current Academic Year
                                            </label>
                                        </div>
                                        <div class="form-text text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Check this box if this is the current academic year. Only one academic year can be current at a time.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('college.academic-years.index') }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to Academic Years
                                        </a>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                                                <i class="bx bx-refresh me-1"></i> Reset Form
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i> Create Academic Year
                                            </button>
                                        </div>
                                    </div>
                                </div>
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

                        <h6>Status Options:</h6>
                        <ul class="text-muted small mb-3">
                            <li><strong>Upcoming:</strong> Academic year is planned but not yet started</li>
                            <li><strong>Active:</strong> Academic year is currently in progress</li>
                            <li><strong>Completed:</strong> Academic year has ended successfully</li>
                            <li><strong>Cancelled:</strong> Academic year was cancelled</li>
                        </ul>

                        <h6>Important Notes:</h6>
                        <ul class="text-muted small">
                            <li>Start and end dates should cover the full academic period</li>
                            <li>Only one academic year can be marked as current</li>
                            <li>You cannot delete academic years that have associated records</li>
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
$(document).ready(function() {
    console.log('Enhanced college academic year create form loaded');

    // Validate date range
    function validateDates() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());

        if (startDate && endDate && startDate >= endDate) {
            $('#end_date')[0].setCustomValidity('End date must be after start date');
            $('#end_date')[0].reportValidity();
        } else {
            $('#end_date')[0].setCustomValidity('');
        }
    }

    $('#start_date, #end_date').on('change', validateDates);

    // Pre-fill dates if provided via URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('start_date')) {
        $('#start_date').val(urlParams.get('start_date'));
    }
    if (urlParams.has('end_date')) {
        $('#end_date').val(urlParams.get('end_date'));
    }

    // Auto-capitalize first letter of year name
    $('#year_name').on('input', function() {
        let value = $(this).val();
        if (value.length > 0) {
            $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
        }
    });

    // Form validation
    $('#academicYearForm').on('submit', function(e) {
        let isValid = true;
        const requiredFields = ['year_name', 'status', 'start_date', 'end_date'];

        requiredFields.forEach(field => {
            const element = $('#' + field);
            if (!element.val()) {
                element.addClass('is-invalid');
                isValid = false;
            } else {
                element.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields', 'error');
            // Scroll to first error
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });
});

// Reset form function
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
        $('#academicYearForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
        showToast('Form has been reset', 'info');
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    const toastColors = {
        success: '#198754',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#0dcaf0'
    };

    // Create toast element
    const toast = $(`
        <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
             style="background-color: ${toastColors[type]}; position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `);

    // Add to body and show
    $('body').append(toast);
    const bsToast = new bootstrap.Toast(toast[0]);
    bsToast.show();

    // Remove after shown
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>
@endpush