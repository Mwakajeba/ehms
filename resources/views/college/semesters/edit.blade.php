@extends('layouts.main')

@section('title', 'Edit Semester')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Semesters', 'url' => route('college.semesters.index'), 'icon' => 'bx bx-time-five'],
            ['label' => 'Edit: ' . $semester->name, 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT SEMESTER</h6>
        <hr />

        <div class="row">
            <!-- Information Sidebar -->
            <div class="col-12 col-lg-4 order-2 order-lg-2">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2 text-warning"></i> Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info mb-3">
                            <h6 class="alert-heading"><i class="bx bx-bulb me-1"></i> Tips for Editing Semesters:</h6>
                            <ul class="mb-0 small">
                                <li>Changing the number affects display order</li>
                                <li>Name changes will reflect across the system</li>
                                <li>Deactivating a semester hides it from selections</li>
                                <li>Consider impact on existing records before changes</li>
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

                        <div class="alert alert-secondary mb-0">
                            <h6 class="alert-heading"><i class="bx bx-history me-1"></i> Record Information:</h6>
                            <ul class="mb-0 small">
                                <li>Created: {{ $semester->created_at->format('M d, Y h:i A') }}</li>
                                <li>Last Updated: {{ $semester->updated_at->format('M d, Y h:i A') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-cog me-2 text-primary"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('college.semesters.show', $semester->id) }}" class="btn btn-outline-info">
                                <i class="bx bx-show me-1"></i> View Details
                            </a>
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                <i class="bx bx-trash me-1"></i> Delete Semester
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form -->
            <div class="col-12 col-lg-8 order-1 order-lg-1">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-warning"></i></div>
                            <h5 class="mb-0 text-warning">Edit Semester: {{ $semester->name }}</h5>
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

                        <form action="{{ route('college.semesters.update', $semester->id) }}" method="POST" id="semesterForm">
                            @csrf
                            @method('PUT')

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
                                                <input type="number" class="form-control @error('number') is-invalid @enderror"
                                                       id="number" name="number" value="{{ old('number', $semester->number) }}" 
                                                       min="1" max="12" required placeholder="e.g., 1, 2, 3...">
                                                @error('number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Enter the semester number for ordering (1-12)
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Semester Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                       id="name" name="name" value="{{ old('name', $semester->name) }}" 
                                                       required placeholder="e.g., Semester 1, Fall Semester">
                                                @error('name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Current: <strong>{{ $semester->name }}</strong>
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
                                                    <option value="active" {{ old('status', $semester->status) == 'active' ? 'selected' : '' }}>
                                                        Active
                                                    </option>
                                                    <option value="inactive" {{ old('status', $semester->status) == 'inactive' ? 'selected' : '' }}>
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
                                                    @if($semester->status == 'active')
                                                        <span class="badge bg-success fs-6">Active</span>
                                                        <small class="d-block mt-2 text-muted">Semester is available for use</small>
                                                    @else
                                                        <span class="badge bg-secondary fs-6">Inactive</span>
                                                        <small class="d-block mt-2 text-muted">Semester is hidden from selections</small>
                                                    @endif
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
                                                          placeholder="Brief description of this semester (optional). E.g., covers courses from September to December...">{{ old('description', $semester->description) }}</textarea>
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
                                            <a href="{{ route('college.semesters.show', $semester->id) }}" class="btn btn-outline-info">
                                                <i class="bx bx-show me-1"></i> View
                                            </a>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-save me-1"></i> Update Semester
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-trash me-2"></i> Delete Semester</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                </div>
                <p class="text-center">Are you sure you want to delete <strong>{{ $semester->name }}</strong>?</p>
                <p class="text-danger text-center"><i class="bx bx-error me-1"></i> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <form action="{{ route('college.semesters.destroy', $semester->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
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
});

function confirmDelete() {
    $('#deleteModal').modal('show');
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
