@extends('layouts.main')

@section('title', 'Bulk Send Fee Invoices')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('college.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Fee Invoices', 'url' => route('college.fee-invoices.index'), 'icon' => 'bx bx-receipt'],
            ['label' => 'Bulk Send', 'url' => '#', 'icon' => 'bx bx-send']
        ]" />
        <h6 class="mb-0 text-uppercase">BULK SEND FEE INVOICES</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-send me-2"></i>Bulk Send Draft Invoices
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Bulk Send Feature:</strong> Select a program, fee group, and period to preview and send all matching draft invoices in bulk.
                            Only draft invoices will be affected, and this action cannot be undone.
                        </div>

                        <form id="bulkSendForm" class="row g-3">
                            @csrf

                            <!-- Program Selection -->
                            <div class="col-md-4">
                                <label for="program_id" class="form-label">
                                    <i class="bx bx-building-house me-1"></i>Program <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="program_id" name="program_id" required>
                                    <option value="">Select Program</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Fee Group Selection -->
                            <div class="col-md-4">
                                <label for="fee_group_id" class="form-label">
                                    <i class="bx bx-group me-1"></i>Fee Group <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="fee_group_id" name="fee_group_id" required>
                                    <option value="">Select Fee Group</option>
                                    @foreach($feeGroups as $feeGroup)
                                        <option value="{{ $feeGroup->id }}">{{ $feeGroup->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Fee Period Selection -->
                            <div class="col-md-4">
                                <label for="period" class="form-label">
                                    <i class="bx bx-calendar me-1"></i>Fee Period <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="period" name="period" required>
                                    <option value="">Select Period</option>
                                    @foreach($feePeriodOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Preview Button -->
                            <div class="col-12 text-center">
                                <button type="button" id="previewBtn" class="btn btn-primary btn-lg" disabled>
                                    <i class="bx bx-show me-2"></i>Preview Draft Invoices
                                </button>
                            </div>
                        </form>

                        <!-- Preview Section -->
                        <div id="previewSection" class="mt-4" style="display: none;">
                            <hr>
                            <div id="previewContent"></div>
                        </div>
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
    // Enable/disable preview button based on form completion
    $('#bulkSendForm select').on('change', function() {
        const programId = $('#program_id').val();
        const feeGroupId = $('#fee_group_id').val();
        const period = $('#period').val();

        $('#previewBtn').prop('disabled', !(programId && feeGroupId && period));
    });

    // Preview button click handler
    $('#previewBtn').on('click', function() {
        const formData = new FormData(document.getElementById('bulkSendForm'));

        // Show loading state
        $('#previewBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-2"></i>Loading Preview...');
        $('#previewSection').hide();

        // Make AJAX request
        $.ajax({
            url: '{{ route("college.fee-invoices.bulk-send-preview") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#previewContent').html(response.html);
                $('#previewSection').show();

                // Scroll to preview section
                $('#previewSection')[0].scrollIntoView({ behavior: 'smooth' });
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred while loading the preview.';
                if (xhr.responseJSON && xhr.responseJSON.html) {
                    errorMessage = xhr.responseJSON.html;
                }

                $('#previewContent').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>Preview Error:</strong> ${errorMessage}
                    </div>
                `);
                $('#previewSection').show();
            },
            complete: function() {
                $('#previewBtn').prop('disabled', false).html('<i class="bx bx-show me-2"></i>Preview Draft Invoices');
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.card {
    border-radius: 0.75rem;
}

.card-header {
    border-radius: 0.75rem 0.75rem 0 0 !important;
}

.form-select {
    border-radius: 0.375rem;
}

.btn {
    border-radius: 0.375rem;
}

.alert {
    border-radius: 0.5rem;
}

@media (max-width: 768px) {
    .col-md-4 {
        margin-bottom: 1rem;
    }
}
</style>
@endpush