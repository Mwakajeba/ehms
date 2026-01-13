@extends('layouts.main')

@section('title', 'College Fee Invoices')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-graduation'],
            ['label' => 'Fee Invoices', 'url' => '#', 'icon' => 'bx bx-receipt']
        ]" />

        <h6 class="mb-0 text-uppercase">COLLEGE FEE INVOICES MANAGEMENT</h6>
        <hr />

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ number_format($statistics['total_invoices']) }}</h4>
                                <p class="mb-0">Total Invoices</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-receipt bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ number_format($statistics['paid_invoices']) }}</h4>
                                <p class="mb-0">Paid Invoices</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-check-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ number_format($statistics['pending_invoices']) }}</h4>
                                <p class="mb-0">Pending</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-time bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ $statistics['currency'] }} {{ number_format($statistics['amount_due'], 2) }}</h4>
                                <p class="mb-0">Due Amount</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-error-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-list-ul me-2"></i>Fee Invoices List
                </h5>
                <a href="{{ route('college.fee-invoices.create') }}" class="btn btn-light btn-sm">
                    <i class="bx bx-plus me-1"></i> Generate New Invoice
                </a>
            </div>
            <div class="card-body">
                <!-- Filters Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h6 class="card-title mb-0">
                                    <i class="bx bx-filter me-2"></i>Filters & Search
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="statusFilter" class="form-label fw-bold">
                                            <i class="bx bx-info-circle text-info me-1"></i>Status
                                        </label>
                                        <select id="statusFilter" class="form-select form-select-lg">
                                            <option value="">All Status</option>
                                            <option value="draft">Draft</option>
                                            <option value="issued">Sent</option>
                                            <option value="paid">Paid</option>
                                            <option value="overdue">Overdue</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="programFilter" class="form-label fw-bold">
                                            <i class="bx bx-building text-success me-1"></i>Program
                                        </label>
                                        <select id="programFilter" class="form-select form-select-lg">
                                            <option value="">All Programs</option>
                                            @foreach($programs as $program)
                                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="feeGroupFilter" class="form-label fw-bold">
                                            <i class="bx bx-group text-warning me-1"></i>Fee Group
                                        </label>
                                        <select id="feeGroupFilter" class="form-select form-select-lg">
                                            <option value="">All Fee Groups</option>
                                            @foreach($feeGroups as $feeGroup)
                                                <option value="{{ $feeGroup->id }}">{{ $feeGroup->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold">&nbsp;</label>
                                        <div class="d-grid">
                                            <button id="clearFilters" class="btn btn-outline-secondary btn-lg">
                                                <i class="bx bx-refresh me-1"></i> Clear Filters
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="table-responsive">
                    <table id="feeInvoicesTable" class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th><i class="bx bx-user me-1"></i>Student Name</th>
                                <th><i class="bx bx-building me-1"></i>Program</th>
                                <th><i class="bx bx-calendar me-1"></i>Fee Period</th>
                                <th class="text-end"><i class="bx bx-money me-1"></i>Amount</th>
                                <th class="text-center"><i class="bx bx-calendar-check me-1"></i>Due Date</th>
                                <th class="text-center"><i class="bx bx-info-circle me-1"></i>Status</th>
                                <th class="text-center"><i class="bx bx-cog me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bx bx-trash me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="bx bx-error-circle text-danger" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center mb-0">
                    Are you sure you want to delete this fee invoice?<br>
                    <strong class="text-danger">This action cannot be undone.</strong>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bx bx-trash me-1"></i>Delete Invoice
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .page-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: 100vh;
        padding: 20px 0;
    }

    .breadcrumb {
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .breadcrumb-item + .breadcrumb-item::before {
        content: "›";
        color: #6c757d;
    }

    .card {
        border-radius: 0.75rem;
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border: none;
        padding: 1rem 1.25rem;
    }

    .bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%) !important;
    }

    .bg-success {
        background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;
    }

    .bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    }

    .bg-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    }

    .bg-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%) !important;
    }

    .form-select-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-select-lg:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
    }

    .btn-outline-secondary {
        border: 2px solid #6c757d;
        color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
        transform: translateY(-2px);
    }

    .table {
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .table thead th {
        background: linear-gradient(135deg, #343a40 0%, #212529 100%) !important;
        color: white;
        border: none;
        font-weight: 600;
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: scale(1.01);
    }

    .table-responsive {
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .modal-content {
        border-radius: 1rem;
    }

    .modal-header {
        border-radius: 1rem 1rem 0 0;
    }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        min-height: 48px;
    }

    .select2-container--bootstrap4 .select2-selection:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .stats-card {
        transition: all 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
        border-radius: 0.375rem;
    }

    .text-end {
        text-align: right !important;
    }

    .text-center {
        text-align: center !important;
    }
</style>
@endpush

@push('scripts')
<script>
// Global AJAX setup for CSRF token
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    console.log('Fee invoices index script loaded');

    // Initialize Select2
    $('#statusFilter, #programFilter, #feeGroupFilter').select2({
        theme: 'bootstrap-5',
        placeholder: function() {
            return $(this).data('placeholder') || 'Please select...';
        },
        allowClear: true,
        width: '100%'
    });

    // Initialize DataTable
    var table = $('#feeInvoicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("college.fee-invoices.data") }}',
            type: 'GET',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.program_id = $('#programFilter').val();
                d.fee_group_id = $('#feeGroupFilter').val();
                console.log('DataTable AJAX data:', d);
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX error:', xhr, error, thrown);
                alert('Error loading data: ' + error);
            }
        },
        columns: [
            {
                data: 'DT_RowIndex',
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                data: 'student_name',
                className: 'align-middle'
            },
            {
                data: 'program_name',
                className: 'align-middle'
            },
            {
                data: 'fee_period',
                className: 'align-middle'
            },
            {
                data: 'amount',
                className: 'text-end align-middle fw-bold'
            },
            {
                data: 'due_date',
                className: 'text-center align-middle'
            },
            {
                data: 'status_badge',
                orderable: false,
                className: 'text-center align-middle'
            },
            {
                data: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center align-middle'
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        searching: true,
        language: {
            processing: '<div class="text-center"><i class="bx bx-loader-alt bx-spin bx-lg text-primary"></i><br>Processing...</div>',
            search: '<i class="bx bx-search me-2"></i>Search by student name, invoice number, program, or fee group:',
            searchPlaceholder: 'Type student name, invoice number, program, or fee group...'
        }
    });

    // Filter change events
    $('#statusFilter, #programFilter, #feeGroupFilter').on('change', function() {
        table.ajax.reload();
    });

    // Clear filters
    $('#clearFilters').on('click', function() {
        $('#statusFilter').val('').trigger('change');
        $('#programFilter').val('').trigger('change');
        $('#feeGroupFilter').val('').trigger('change');
        table.ajax.reload();
    });

    // Delete modal handling
    let deleteUrl = '';
    $('#feeInvoicesTable').on('click', '.btn-danger', function(e) {
        e.preventDefault();
        deleteUrl = $(this).closest('form').attr('action');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (deleteUrl) {
            $('<form>', {
                'method': 'POST',
                'action': deleteUrl,
                'html': '<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">'
            }).appendTo('body').submit();
        }
    });
});
</script>
@endpush