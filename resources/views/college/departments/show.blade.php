@extends('layouts.main')

@section('title', 'Department Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Departments', 'url' => route('college.departments.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">DEPARTMENT DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <!-- Department Information Section -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-detail me-1 font-22 text-primary"></i>
                                <h5 class="mb-0 text-primary d-inline">{{ $department->name }}</h5>
                                <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }} ms-2">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div>
                                <a href="{{ route('college.departments.edit', $department->id) }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('college.departments.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-list-ul me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Basic Information -->
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
                                            <label class="form-label fw-bold">Department Name:</label>
                                            <p class="mb-0">{{ $department->name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Department Code:</label>
                                            <p class="mb-0">
                                                <span class="badge bg-info">{{ $department->code }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Head of Department:</label>
                                            <p class="mb-0">
                                                @if($department->headOfDepartment)
                                                    {{ $department->headOfDepartment->name }}
                                                    <small class="text-muted">({{ $department->headOfDepartment->email }})</small>
                                                @else
                                                    <em class="text-muted">Not assigned</em>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Status:</label>
                                            <p class="mb-0">
                                                <span class="badge bg-{{ $department->is_active ? 'success' : 'secondary' }}">
                                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if($department->description)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Description:</label>
                                    <p class="mb-0">{{ $department->description }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="card border-info">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bx bx-time me-2 text-info"></i> Timestamps
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Created:</label>
                                            <p class="mb-0">{{ $department->created_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Last Updated:</label>
                                            <p class="mb-0">{{ $department->updated_at->format('M d, Y H:i') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Programs Section -->
                @if($department->programs->count() > 0)
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-book me-1 font-22 text-success"></i>
                                <h6 class="mb-0 text-success">Programs ({{ $department->programs->count() }})</h6>
                            </div>
                            <a href="{{ route('college.programs.create') }}" class="btn btn-success btn-sm">
                                <i class="bx bx-plus me-1"></i> Add Program
                            </a>
                        </div>
                        <hr />

                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Code</th>
                                        <th>Duration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($department->programs as $program)
                                    <tr>
                                        <td>{{ $program->name }}</td>
                                        <td><span class="badge bg-info">{{ $program->code }}</span></td>
                                        <td>{{ $program->duration_years }} years</td>
                                        <td>
                                            <span class="badge bg-{{ $program->is_active ? 'success' : 'secondary' }}">
                                                {{ $program->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('college.programs.show', $program->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{ route('college.programs.edit', $program->id) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="card mt-3">
                    <div class="card-body text-center">
                        <i class="bx bx-book font-48 text-muted mb-3"></i>
                        <h6 class="text-muted">No Programs Yet</h6>
                        <p class="text-muted mb-3">This department doesn't have any programs assigned yet.</p>
                        <a href="{{ route('college.programs.create') }}" class="btn btn-success">
                            <i class="bx bx-plus me-1"></i> Add First Program
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-12 col-lg-4">
                <!-- Statistics Sidebar -->
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-stats me-1 text-info"></i> Statistics
                        </h6>
                        <hr />

                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stats-card">
                                    <h4 class="text-primary">{{ $department->programs->count() }}</h4>
                                    <small class="text-muted">Programs</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-card">
                                    <h4 class="text-success">{{ $department->programs->where('is_active', true)->count() }}</h4>
                                    <small class="text-muted">Active Programs</small>
                                </div>
                            </div>
                        </div>

                        <hr />

                        <div class="mb-3">
                            <strong>Department Status:</strong>
                            <div class="mt-2">
                                @if($department->is_active)
                                    <span class="badge bg-success w-100">Active</span>
                                @else
                                    <span class="badge bg-secondary w-100">Inactive</span>
                                @endif
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Info:</strong> Department details are managed through the College Management module.
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-cog me-1 text-warning"></i> Quick Actions
                        </h6>
                        <hr />

                        <div class="d-grid gap-2">
                            <a href="{{ route('college.departments.edit', $department->id) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bx bx-edit me-1"></i> Edit Department
                            </a>
                            <a href="{{ route('college.programs.create') }}" class="btn btn-outline-success btn-sm">
                                <i class="bx bx-plus me-1"></i> Add Program
                            </a>
                            <a href="{{ route('college.departments.create') }}" class="btn btn-outline-info btn-sm">
                                <i class="bx bx-plus me-1"></i> Add New Department
                            </a>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete()">
                                <i class="bx bx-trash me-1"></i> Delete Department
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the department <strong>{{ $department->name }}</strong>?</p>
                <div class="alert alert-warning">
                    <i class="bx bx-warning me-1"></i>
                    This action cannot be undone. All associated programs and data will be affected.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('college.departments.destroy', $department->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Department</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stats-card {
        padding: 15px;
        border-radius: 8px;
        background: #f8f9fa;
        margin-bottom: 10px;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
    }
</style>
@endpush

@push('scripts')
<script>
    function confirmDelete() {
        $('#deleteModal').modal('show');
    }

    $(document).ready(function() {
        console.log('Department details page loaded');
    });
</script>
@endpush