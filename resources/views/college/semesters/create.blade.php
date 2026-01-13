@extends('layouts.main')

@section('title', 'Create Semester')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Semesters', 'url' => route('college.semesters.index'), 'icon' => 'bx bx-time-five'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW SEMESTER</h6>
        <hr />

        <div class="row">
            <!-- Information Sidebar -->
            <div class="col-12 col-lg-4 order-2 order-lg-2">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2 text-primary"></i> Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <h6 class="alert-heading"><i class="bx bx-bulb me-1"></i> Tips for Creating Semesters:</h6>
                            <ul class="mb-0 small">
                                <li>Semester number determines the order of display</li>
                                <li>Name should be descriptive (e.g., "Semester 1", "Fall Semester")</li>
                                <li>Use description for additional details</li>
                                <li>Only active semesters appear in selection lists</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning mb-3">
                            <h6 class="alert-heading"><i class="bx bx-error me-1"></i> Required Fields:</h6>
                            <ul class="mb-0 small">
                                <li>Semester Number</li>
                                <li>Semester Name</li>
                                <li>Status</li>
                            </ul>
                        </div>

                        <div class="alert alert-success mb-0">
                            <h6 class="alert-heading"><i class="bx bx-check-circle me-1"></i> Common Semester Names:</h6>
                            <ul class="mb-0 small">
                                <li>Semester 1, Semester 2</li>
                                <li>Fall Semester, Spring Semester</li>
                                <li>First Term, Second Term</li>
                                <li>Quarter 1, Quarter 2, etc.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-bar-chart me-2 text-success"></i> Current Statistics
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Total Semesters:</span>
                            <span class="badge bg-primary" id="totalSemesters">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Active Semesters:</span>
                            <span class="badge bg-success" id="activeSemesters">-</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Inactive Semesters:</span>
                            <span class="badge bg-secondary" id="inactiveSemesters">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <div class="col-12 col-lg-8 order-1 order-lg-1">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Semester</h5>
                        </div>
                        <hr />

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i>
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('college.semesters.store') }}" method="POST" id="semesterForm">
                            @csrf

                            <!-- Basic Information Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-info-circle me-2 text-primary"></i> Basic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Semester Number <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-primary text-white">
                                                        <i class="bx bx-hash"></i>
                                                    </span>
                                                    <input type="number" class="form-control @error('number') is-invalid @enderror"
                                                           id="number" name="number" value="{{ $nextNumber }}" 
                                                           readonly style="background-color: #e9ecef; font-weight: bold; font-size: 1.1rem;">
                                                </div>
                                                @error('number')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-success">
                                                    <i class="bx bx-check-circle me-1"></i>
                                                    Auto-generated: Next available semester number
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Semester Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                       id="name" name="name" value="{{ old('name', 'Semester ' . $nextNumber) }}" 
                                                       required placeholder="e.g., Semester 1, Fall Semester">
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Auto-generated based on number, or enter custom name
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status & Description Section -->
                            <div class="card border-success mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-cog me-2 text-success"></i> Status & Description
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                                <select class="form-select @error('status') is-invalid @enderror" 
                                                        id="status" name="status" required>
                                                    <option value="">Select Status</option>
                                                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                                        Active
                                                    </option>
                                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                                        Inactive
                                                    </option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Active semesters appear in selection dropdowns
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Status Preview</label>
                                                <div class="p-3 bg-light rounded" id="statusPreview">
                                                    <span class="badge bg-success fs-6">Active</span>
                                                    <small class="d-block mt-2 text-muted">Semester will be available for use</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Description</label>
                                                <textarea class="form-control @error('description') is-invalid @enderror"
                                                          id="description" name="description" rows="4"
                                                          placeholder="Brief description of this semester (optional). E.g., covers courses from September to December...">{{ old('description') }}</textarea>
                                                @error('description')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Optional: Add any notes about this semester
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('college.semesters.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Cancel
                                        </a>
                                        <div class="d-flex gap-2">
                                            <button type="reset" class="btn btn-outline-warning">
                                                <i class="bx bx-reset me-1"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i> Create Semester
                                            </button>
                                        </div>
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
    // Name field is pre-filled with auto-generated semester name
    // Allow user to edit name if they want a custom name
    $('#name').on('focus', function() {
        $(this).select(); // Select all text for easy editing
    });

    // Status preview update
    $('#status').on('change', function() {
        const status = $(this).val();
        let html = '';
        if (status === 'active') {
            html = '<span class="badge bg-success fs-6">Active</span>' +
                   '<small class="d-block mt-2 text-muted">Semester will be available for use</small>';
        } else if (status === 'inactive') {
            html = '<span class="badge bg-secondary fs-6">Inactive</span>' +
                   '<small class="d-block mt-2 text-muted">Semester will be hidden from selections</small>';
        } else {
            html = '<span class="badge bg-light text-dark fs-6">Not Selected</span>' +
                   '<small class="d-block mt-2 text-muted">Please select a status</small>';
        }
        $('#statusPreview').html(html);
    });

    // Load statistics
    loadStats();
});

function loadStats() {
    $.ajax({
        url: '{{ route("college.semesters.data") }}',
        type: 'GET',
        data: { length: -1 },
        success: function(response) {
            if (response.data) {
                let total = response.data.length;
                let active = response.data.filter(s => s.status === 'active').length;
                let inactive = total - active;
                $('#totalSemesters').text(total);
                $('#activeSemesters').text(active);
                $('#inactiveSemesters').text(inactive);
            }
        },
        error: function() {
            $('#totalSemesters').text('N/A');
            $('#activeSemesters').text('N/A');
            $('#inactiveSemesters').text('N/A');
        }
    });
}
</script>
@endpush

@push('styles')
<style>
    .card {
        transition: box-shadow 0.2s ease-in-out;
    }
    .card:hover {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    .form-label.fw-bold {
        color: #333;
    }
    .alert h6 {
        font-size: 0.9rem;
    }
    .alert ul {
        padding-left: 1.2rem;
    }
</style>
@endpush
