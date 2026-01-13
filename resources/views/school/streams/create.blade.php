@extends('layouts.main')

@section('title', 'Create New Stream')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Streams', 'url' => route('school.streams.index'), 'icon' => 'bx bx-book-open'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE NEW STREAM</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Add New Academic Streams</h5>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="addStreamBtn">
                                <i class="bx bx-plus me-1"></i> Add Line
                            </button>
                        </div>
                        <hr />

                        <form action="{{ route('school.streams.store') }}" method="POST">
                            @csrf

                            <div id="streamsContainer">
                                <div class="stream-row border rounded p-3 mb-3" data-row="0">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <label class="form-label">Stream Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control stream-name @error('streams.0.name') is-invalid @enderror"
                                                   name="streams[0][name]" value="{{ old('streams.0.name') }}"
                                                   placeholder="e.g., Science, Arts, Commerce" required>
                                            <div class="stream-warning text-warning small mt-1" style="display: none;">
                                                <i class="bx bx-error-circle me-1"></i>
                                                This stream name already exists!
                                            </div>
                                            @error('streams.0.name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Description</label>
                                            <input type="text" class="form-control stream-description"
                                                   name="streams[0][description]" value="{{ old('streams.0.description') }}"
                                                   placeholder="Optional description">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-stream-btn" disabled>
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-text mb-3">
                                Enter the names and descriptions of the academic streams. Click "Add Line" to add more streams at once.
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="{{ route('school.streams.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Streams
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Create Streams
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bx bx-info-circle me-1 text-info"></i> Information
                        </h6>
                        <hr />
                        <div class="mb-3">
                            <h6>What are Academic Streams?</h6>
                            <p class="small text-muted">
                                Academic streams represent different educational tracks or specializations that students can choose from,
                                such as Science, Arts, Commerce, or General studies. These streams help organize curriculum and subject offerings.
                            </p>
                        </div>
                        <div class="mb-3">
                            <h6>Examples:</h6>
                            <ul class="small text-muted">
                                <li>Science (Physics, Chemistry, Biology)</li>
                                <li>Arts (History, Geography, Literature)</li>
                                <li>Commerce (Accounting, Business Studies)</li>
                                <li>General (Green, Red, Blue)</li>
                                <li>General (A, B, C)</li>
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

    .stream-row {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6 !important;
        transition: all 0.2s ease;
    }

    .stream-row:hover {
        background-color: #e9ecef;
        border-color: #adb5bd !important;
    }

    .stream-row .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }

    .remove-stream-btn:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }

    #addStreamBtn {
        border: 1px solid #0d6efd;
        color: #0d6efd;
    }

    #addStreamBtn:hover {
        background-color: #0d6efd;
        color: white;
    }

    /* Warning styles for existing stream names */
    .is-warning {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
    }

    .stream-warning {
        font-weight: 500;
        color: #856404 !important;
    }

    .duplicate-warning {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        let rowCount = 1;

        // Add new stream row
        $('#addStreamBtn').on('click', function() {
            addStreamRow();
        });

        // Remove stream row
        $(document).on('click', '.remove-stream-btn', function() {
            $(this).closest('.stream-row').remove();
            updateRemoveButtons();
            updateRowIndices();
        });

        // Auto-capitalize first letter for stream names
        $(document).on('input', '.stream-name', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
            checkStreamName(this);
        });

        // Check for duplicate names within the form
        $(document).on('input', '.stream-name', function() {
            checkForDuplicates();
        });

        function addStreamRow() {
            const rowHtml = `
                <div class="stream-row border rounded p-3 mb-3" data-row="${rowCount}">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label">Stream Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control stream-name"
                                   name="streams[${rowCount}][name]"
                                   placeholder="e.g., Science, Arts, Commerce" required>
                            <div class="stream-warning text-warning small mt-1" style="display: none;">
                                <i class="bx bx-error-circle me-1"></i>
                                This stream name already exists!
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control stream-description"
                                   name="streams[${rowCount}][description]"
                                   placeholder="Optional description">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-stream-btn">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('#streamsContainer').append(rowHtml);
            rowCount++;
            updateRemoveButtons();
        }

        function checkStreamName(inputElement) {
            const $input = $(inputElement);
            const $row = $input.closest('.stream-row');
            const $warning = $row.find('.stream-warning');
            const name = $input.val().trim();

            if (name.length === 0) {
                $warning.hide();
                $input.removeClass('is-warning');
                return;
            }

            // Check against database
            $.ajax({
                url: '{{ route("school.api.streams.check-name") }}',
                method: 'GET',
                data: { name: name },
                success: function(response) {
                    if (response.exists) {
                        $warning.show();
                        $input.addClass('is-warning');
                    } else {
                        $warning.hide();
                        $input.removeClass('is-warning');
                    }
                },
                error: function() {
                    console.error('Error checking stream name');
                }
            });
        }

        function checkForDuplicates() {
            const names = [];
            let hasDuplicates = false;

            $('.stream-name').each(function() {
                const name = $(this).val().trim().toLowerCase();
                if (name.length > 0) {
                    if (names.includes(name)) {
                        hasDuplicates = true;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                        names.push(name);
                    }
                }
            });

            // Update submit button state
            const $submitBtn = $('button[type="submit"]');
            if (hasDuplicates) {
                $submitBtn.prop('disabled', true);
                if (!$('.duplicate-warning').length) {
                    $('#streamsContainer').after(`
                        <div class="duplicate-warning alert alert-danger mt-3">
                            <i class="bx bx-error-circle me-1"></i>
                            Duplicate stream names detected within the form. Please use unique names.
                        </div>
                    `);
                }
            } else {
                $submitBtn.prop('disabled', false);
                $('.duplicate-warning').remove();
            }
        }

        function updateRemoveButtons() {
            const rows = $('.stream-row');
            if (rows.length === 1) {
                rows.find('.remove-stream-btn').prop('disabled', true);
            } else {
                rows.find('.remove-stream-btn').prop('disabled', false);
            }
        }

        function updateRowIndices() {
            $('.stream-row').each(function(index) {
                $(this).attr('data-row', index);
                $(this).find('input[name*="streams["]').each(function() {
                    const name = $(this).attr('name');
                    const newName = name.replace(/streams\[\d+\]/, `streams[${index}]`);
                    $(this).attr('name', newName);
                });
            });
            rowCount = $('.stream-row').length;
        }

        // Initialize remove buttons state
        updateRemoveButtons();

        console.log('Create streams form loaded');
    });
</script>
@endpush