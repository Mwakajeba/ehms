@extends('layouts.main')

@section('title', 'Document Category Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Document Categories', 'url' => route('college.document-categories.index'), 'icon' => 'bx bx-folder'],
            ['label' => $documentCategory->name, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <h6 class="mb-0 text-uppercase">DOCUMENT CATEGORY DETAILS</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <!-- Category Information -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-folder me-2 text-primary"></i>{{ $documentCategory->name }}
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('college.document-categories.edit', $documentCategory->id) }}" class="btn btn-warning btn-sm">
                                <i class="bx bx-edit me-1"></i>Edit
                            </a>
                            <form action="{{ route('college.document-categories.destroy', $documentCategory->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this document category? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bx bx-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Category Name</label>
                                    <p class="form-control-plaintext">{{ $documentCategory->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Category Code</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-primary">{{ $documentCategory->code }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created By</label>
                                    <p class="form-control-plaintext">{{ $documentCategory->creator->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Created At</label>
                                    <p class="form-control-plaintext">{{ $documentCategory->created_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Company</label>
                                    <p class="form-control-plaintext">{{ $documentCategory->company->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            @if($documentCategory->branch)
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Branch</label>
                                    <p class="form-control-plaintext">{{ $documentCategory->branch->name }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        @if($documentCategory->updated_at != $documentCategory->created_at)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Last Updated</label>
                                    <p class="form-control-plaintext">{{ $documentCategory->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('college.document-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to Categories
                            </a>
                            <div class="d-flex gap-2">
                                <a href="{{ route('college.document-categories.edit', $documentCategory->id) }}" class="btn btn-warning">
                                    <i class="bx bx-edit me-1"></i> Edit Category
                                </a>
                                <a href="{{ route('college.document-categories.create') }}" class="btn btn-success">
                                    <i class="bx bx-plus me-1"></i> Add New Category
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Sidebar -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i> Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="bx bx-bulb me-1"></i> About Document Categories:</h6>
                            <ul class="mb-0 small">
                                <li>Document categories help organize different types of documents</li>
                                <li>Each category has a unique name and code within your company</li>
                                <li>Categories are used to classify and manage documents efficiently</li>
                                <li>You can edit or delete categories as needed</li>
                            </ul>
                        </div>

                        <div class="alert alert-light">
                            <h6><i class="bx bx-stats me-1"></i> Category Statistics:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Full Name:</strong> {{ $documentCategory->full_name }}</li>
                                <li><strong>Status:</strong> <span class="badge bg-success">Active</span></li>
                                <li><strong>Company Scope:</strong> {{ $documentCategory->company->name ?? 'N/A' }}</li>
                                @if($documentCategory->branch)
                                    <li><strong>Branch Scope:</strong> {{ $documentCategory->branch->name }}</li>
                                @else
                                    <li><strong>Branch Scope:</strong> All Branches</li>
                                @endif
                            </ul>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-text {
        font-size: 0.875rem;
    }

    h6 {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
    }

    /* Enhanced Card Styling */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.75rem;
        transition: box-shadow 0.15s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        font-weight: 600;
        background-color: #f8f9fa !important;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Form Field Enhancements */
    .form-label {
        margin-bottom: 0.5rem;
        color: #495057;
        font-weight: 500;
    }

    .form-control-plaintext {
        padding-top: 0.375rem;
        padding-bottom: 0.375rem;
        margin-bottom: 0;
        line-height: 1.5;
        background-color: transparent;
        border: solid transparent;
        border-width: 1px 0;
    }

    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    /* Alert Styling */
    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    /* Badge Styling */
    .badge {
        font-size: 0.875rem;
        padding: 0.5em 0.75em;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.gap-2 {
            justify-content: center;
        }
    }

    /* Icon Styling */
    .bx {
        font-size: 1.1em;
    }

    /* Required Field Indicator */
    .text-danger {
        font-weight: bold;
    }

    /* Form Text Enhancement */
    .form-text {
        font-style: italic;
        margin-top: 0.25rem;
    }

    /* Card Border Colors - Subtle */
    .border-primary { border-color: #e3f2fd !important; }
    .border-info { border-color: #e0f2fe !important; }
    .border-success { border-color: #d1edff !important; }
    .border-warning { border-color: #fff3cd !important; }
    .border-secondary { border-color: #f8f9fa !important; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    console.log('Document category details page loaded');
});
</script>
@endpush