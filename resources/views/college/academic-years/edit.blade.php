@extends('layouts.main')

@section('title', 'Edit Academic Year')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Academic Years', 'url' => route('college.academic-years.index'), 'icon' => 'bx bx-calendar'],
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

                        <form action="{{ route('college.academic-years.update', $academicYear) }}" method="POST" id="academicYearForm">
                            @csrf
                            @method('PUT')

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
                                                       id="year_name" name="year_name" value="{{ old('year_name', $academicYear->year_name) }}"
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
                                                    <option value="upcoming" {{ old('status', $academicYear->status) == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                                                    <option value="active" {{ old('status', $academicYear->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="completed" {{ old('status', $academicYear->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="cancelled" {{ old('status', $academicYear->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                                                       id="start_date" name="start_date" value="{{ old('start_date', $academicYear->start_date ? $academicYear->start_date->format('Y-m-d') : '') }}" required>
                                                @error('start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                                       id="end_date" name="end_date" value="{{ old('end_date', $academicYear->end_date ? $academicYear->end_date->format('Y-m-d') : '') }}" required>
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
                                                  placeholder="Optional description for this academic year">{{ old('description', $academicYear->description) }}</textarea>
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
                                                   id="set_as_current" {{ old('set_as_current', $academicYear->is_current) ? 'checked' : '' }}>
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
                                                <i class="bx bx-save me-1"></i> Update Academic Year
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
                            <strong>Students:</strong> {{ $academicYear->students()->count() }}
                        </div>
                        <div class="mb-3">
                            <strong>Enrollments:</strong> {{ $academicYear->enrollments()->count() }}
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong> {!! $academicYear->getStatusBadge() !!}
                        </div>
                        <div class="mb-3">
                            <strong>Current:</strong> {!! $academicYear->getCurrentBadge() !!}
                        </div>

                        @if(!$academicYear->canBeEdited())
                            <div class="alert alert-warning">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> This academic year cannot be edited because it is completed or cancelled.
                            </div>
                        @endif

                        @if(!$academicYear->canBeDeleted())
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-1"></i>
                                <strong>Note:</strong> This academic year cannot be deleted because it has associated records or is active.
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
$(document).ready(function() {
    console.log('Enhanced college academic year edit form loaded');

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