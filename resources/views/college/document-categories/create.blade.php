@extends('layouts.main')

@section('title', 'Create Document Category')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Document Categories', 'url' => route('college.document-categories.index'), 'icon' => 'bx bx-folder'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

        <h6 class="mb-0 text-uppercase">CREATE NEW DOCUMENT CATEGORY</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Document Category</h5>
                        </div>
                        <hr />

                        <form action="{{ route('college.document-categories.store') }}" method="POST" id="documentCategoryForm">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Category Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name"
                                               value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Enter a descriptive name for the document category
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Category Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('code') is-invalid @enderror"
                                               id="code" name="code"
                                               value="{{ old('code') }}" required
                                               style="text-transform: uppercase;">
                                        @error('code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text text-muted">
                                            <i class="bx bx-info-circle me-1"></i>
                                            Enter a unique code (will be converted to uppercase)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('college.document-categories.index') }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to Categories
                                        </a>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                                                <i class="bx bx-refresh me-1"></i> Reset Form
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i> Create Category
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
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
                            <h6><i class="bx bx-bulb me-1"></i> Tips for Creating Categories:</h6>
                            <ul class="mb-0 small">
                                <li>Choose descriptive names that clearly identify the document type</li>
                                <li>Use short, memorable codes (e.g., CERT, DIPL, TRAN)</li>
                                <li>Codes are automatically converted to uppercase</li>
                                <li>Each category must be unique within your company</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="bx bx-error me-1"></i> Requirements:</h6>
                            <ul class="mb-0 small">
                                <li>Category Name is required</li>
                                <li>Category Code is required and must be unique</li>
                                <li>Both fields are mandatory</li>
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
    console.log('Document category creation form loaded');

    // Auto-convert code to uppercase
    $('#code').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Auto-generate code from name if empty
    $('#name').on('input', function() {
        if (!$('#code').val()) {
            var name = $(this).val();
            var code = name.substring(0, 4).toUpperCase().replace(/[^A-Z]/g, '');
            if (code.length >= 2) {
                $('#code').val(code);
            }
        }
    });

    // Form validation
    $('#documentCategoryForm').on('submit', function(e) {
        let isValid = true;
        const requiredFields = ['name', 'code'];

        requiredFields.forEach(field => {
            const element = $('#' + field);
            if (!element.val().trim()) {
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
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        $('#documentCategoryForm')[0].reset();
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