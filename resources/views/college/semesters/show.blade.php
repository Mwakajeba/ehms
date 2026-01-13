@extends('layouts.main')

@section('title', 'View Semester')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Semesters', 'url' => route('college.semesters.index'), 'icon' => 'bx bx-time-five'],
            ['label' => $semester->name, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">SEMESTER DETAILS</h6>
        <hr />

        <div class="row">
            <!-- Actions Sidebar -->
            <div class="col-12 col-lg-4 order-2 order-lg-2">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-cog me-2 text-primary"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('college.semesters.edit', $semester->id) }}" class="btn btn-warning">
                                <i class="bx bx-edit me-1"></i> Edit Semester
                            </a>
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete()">
                                <i class="bx bx-trash me-1"></i> Delete Semester
                            </button>
                            <hr class="my-2">
                            <a href="{{ route('college.semesters.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                            <a href="{{ route('college.semesters.create') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i> Create New Semester
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Record Information -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-history me-2 text-info"></i> Record Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="text-muted"><i class="bx bx-calendar me-1"></i> Created:</span>
                            <span class="fw-bold">{{ $semester->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="text-muted"><i class="bx bx-time me-1"></i> Time:</span>
                            <span>{{ $semester->created_at->format('h:i A') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <span class="text-muted"><i class="bx bx-refresh me-1"></i> Last Updated:</span>
                            <span class="fw-bold">{{ $semester->updated_at->format('M d, Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="bx bx-time me-1"></i> Time:</span>
                            <span>{{ $semester->updated_at->format('h:i A') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-check-circle me-2 text-success"></i> Status
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        @if($semester->status == 'active')
                            <div class="mb-3">
                                <i class="bx bx-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                            <span class="badge bg-success fs-5 px-4 py-2">Active</span>
                            <p class="text-muted mt-2 mb-0">This semester is available for use</p>
                        @else
                            <div class="mb-3">
                                <i class="bx bx-pause-circle text-secondary" style="font-size: 4rem;"></i>
                            </div>
                            <span class="badge bg-secondary fs-5 px-4 py-2">Inactive</span>
                            <p class="text-muted mt-2 mb-0">This semester is hidden from selections</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-12 col-lg-8 order-1 order-lg-1">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div><i class="bx bx-time-five me-1 font-22 text-info"></i></div>
                                <h5 class="mb-0 text-info">{{ $semester->name }}</h5>
                            </div>
                            @if($semester->status == 'active')
                                <span class="badge bg-success fs-6">Active</span>
                            @else
                                <span class="badge bg-secondary fs-6">Inactive</span>
                            @endif
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

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
                                        <div class="mb-4">
                                            <label class="form-label text-muted small mb-1">Semester Number</label>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 50px; height: 50px; font-size: 1.5rem; font-weight: bold;">
                                                    {{ $semester->number }}
                                                </div>
                                                <div>
                                                    <h4 class="mb-0">Semester {{ $semester->number }}</h4>
                                                    <small class="text-muted">Display Order</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="form-label text-muted small mb-1">Semester Name</label>
                                            <div class="p-3 bg-light rounded">
                                                <h4 class="mb-0"><i class="bx bx-bookmark me-2 text-primary"></i>{{ $semester->name }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div class="card border-success mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bx bx-detail me-2 text-success"></i> Description
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($semester->description)
                                    <div class="p-3 bg-light rounded">
                                        <p class="mb-0">{{ $semester->description }}</p>
                                    </div>
                                @else
                                    <div class="text-center py-4">
                                        <i class="bx bx-message-detail text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2 mb-0">No description provided for this semester.</p>
                                        <a href="{{ route('college.semesters.edit', $semester->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bx bx-plus me-1"></i> Add Description
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Status Details Section -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="bx bx-check-shield me-2 text-info"></i> Status Details
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded text-center">
                                            <h6 class="text-muted mb-2">Current Status</h6>
                                            @if($semester->status == 'active')
                                                <span class="badge bg-success fs-6 px-3 py-2">
                                                    <i class="bx bx-check me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary fs-6 px-3 py-2">
                                                    <i class="bx bx-pause me-1"></i> Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded text-center">
                                            <h6 class="text-muted mb-2">Visibility</h6>
                                            @if($semester->status == 'active')
                                                <span class="text-success">
                                                    <i class="bx bx-show fs-4"></i>
                                                    <p class="mb-0 mt-1">Visible in selections</p>
                                                </span>
                                            @else
                                                <span class="text-secondary">
                                                    <i class="bx bx-hide fs-4"></i>
                                                    <p class="mb-0 mt-1">Hidden from selections</p>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card border-secondary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <a href="{{ route('college.semesters.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to List
                                    </a>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('college.semesters.edit', $semester->id) }}" class="btn btn-warning">
                                            <i class="bx bx-edit me-1"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
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
</style>
@endpush
