@extends('layouts.main')

@section('title', 'Student Transfers Management')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Student Transfers', 'url' => '#', 'icon' => 'bx bx-transfer']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT TRANSFERS MANAGEMENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-transfer me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Student Transfer Records</h5>
                            </div>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Filter Section -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-filter me-2"></i> Filter Transfers
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('school.student-transfers.index') }}" id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="transfer_type" class="form-label fw-bold">Transfer Type</label>
                                            <select class="form-select" id="transfer_type" name="transfer_type">
                                                <option value="">All Types</option>
                                                <option value="transfer_out" {{ request('transfer_type') == 'transfer_out' ? 'selected' : '' }}>Transfer Out</option>
                                                <option value="transfer_in" {{ request('transfer_type') == 'transfer_in' ? 'selected' : '' }}>Transfer In</option>
                                                <option value="re_admission" {{ request('transfer_type') == 're_admission' ? 'selected' : '' }}>Re-admission</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="status" class="form-label fw-bold">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">All Statuses</option>
                                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date_from" class="form-label fw-bold">From Date</label>
                                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="date_to" class="form-label fw-bold">To Date</label>
                                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary" id="filterBtn">
                                                    <i class="bx bx-search me-1"></i> Filter
                                                </button>
                                                <a href="{{ route('school.student-transfers.index') }}" class="btn btn-outline-secondary">
                                                    <i class="bx bx-refresh me-1"></i> Clear
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Results Summary -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1">Transfer Records</h6>
                                <small class="text-muted">
                                    Manage student transfers, admissions, and re-admissions
                                </small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('school.student-transfers.create') }}" class="btn btn-success btn-sm">
                                    <i class="bx bx-plus me-1"></i> Create New Transfer
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="exportToExcel()">
                                    <i class="bx bx-download me-1"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportToPdf()">
                                    <i class="bx bx-file me-1"></i> Export PDF
                                </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="transfersTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Transfer ID</th>
                                        <th>Student Name</th>
                                        <th>Transfer Type</th>
                                        <th>From School</th>
                                        <th>To School</th>
                                        <th>Transfer Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bx bx-trash me-2"></i> Confirm Deletion
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="bx bx-error-circle text-danger" style="font-size: 4rem;"></i>
                </div>
                <h6 class="text-center mb-3">Are you sure you want to delete this transfer record?</h6>
                <p class="text-center text-muted mb-0" id="transferInfoText">
                    <strong>Transfer: <span id="transferInfo"></span></strong>
                </p>
                <div class="alert alert-warning mt-3" role="alert">
                    <i class="bx bx-info-circle me-1"></i>
                    <strong>Warning:</strong> This action cannot be undone. The transfer record will be permanently deleted.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i> Cancel
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bx bx-trash me-1"></i> Yes, Delete Transfer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Transfer In Modal -->
<div class="modal fade" id="transferInModal" tabindex="-1" aria-labelledby="transferInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="transferInModalLabel">
                    <i class="bx bx-log-in me-2"></i> Transfer Student In
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="transferInForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="transfer_in_date" class="form-label fw-bold">Transfer In Date *</label>
                            <input type="date" class="form-control" id="transfer_in_date" name="transfer_in_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admission_number" class="form-label fw-bold">New Admission Number *</label>
                            <input type="text" class="form-control" id="admission_number" name="admission_number" required>
                        </div>
                        <div class="col-md-6">
                            <label for="class_id" class="form-label fw-bold">Class *</label>
                            <select class="form-select" id="class_id" name="class_id" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="stream_id" class="form-label fw-bold">Stream</label>
                            <select class="form-select" id="stream_id" name="stream_id">
                                <option value="">Select Stream</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="transfer_notes" class="form-label fw-bold">Transfer Notes</label>
                            <textarea class="form-control" id="transfer_notes" name="transfer_notes" rows="3" placeholder="Additional notes about the transfer..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-log-in me-1"></i> Transfer Student In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.5em 0.75em;
    }

    /* Filter Section Styles */
    .card.border-info .card-header {
        background: linear-gradient(135deg, #0dcaf0 0%, #0d6efd 100%) !important;
        border-bottom: 2px solid #0dcaf0;
    }

    .form-label {
        color: #495057;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-select, .form-control {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-select:focus, .form-control:focus {
        border-color: #0dcaf0;
        box-shadow: 0 0 0 0.25rem rgba(13, 202, 240, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
        transition: all 0.15s ease-in-out;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .btn-outline-secondary {
        transition: all 0.15s ease-in-out;
    }

    .btn-outline-secondary:hover {
        transform: translateY(-1px);
    }

    /* Status badges */
    .badge-transfer-out {
        background-color: #dc3545;
        color: white;
    }

    .badge-transfer-in {
        background-color: #198754;
        color: white;
    }

    .badge-re-admission {
        background-color: #ffc107;
        color: #000;
    }

    .badge-pending {
        background-color: #6c757d;
        color: white;
    }

    .badge-approved {
        background-color: #0dcaf0;
        color: #000;
    }

    .badge-completed {
        background-color: #198754;
        color: white;
    }

    .badge-cancelled {
        background-color: #dc3545;
        color: white;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .d-flex.gap-2 {
            flex-direction: column;
            gap: 0.5rem !important;
        }

        .d-flex.gap-2 .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    window.transfersTable = $('#transfersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.student-transfers.data") }}',
            type: 'GET',
            data: function(d) {
                d.transfer_type = $('#transfer_type').val();
                d.status = $('#status').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            },
            error: function(xhr, status, error) {
                console.error('DataTables error:', error);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'transfer_number', name: 'transfer_number' },
            { data: 'student_name', name: 'student_name' },
            { data: 'transfer_type_badge', name: 'transfer_type', orderable: false },
            { data: 'from_school', name: 'from_school' },
            { data: 'to_school', name: 'to_school' },
            { data: 'transfer_date', name: 'transfer_date' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[6, 'desc']],
        responsive: true,
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
        },
        initComplete: function() {
            // Style the DataTables elements
            $('.dataTables_filter input').addClass('form-control form-control-sm');
        }
    });

    // Reload DataTable with new filter parameters
    function reloadDataTable() {
        if (window.transfersTable) {
            window.transfersTable.ajax.reload(function() {
                // Reset filter button state after reload completes
                $('#filterBtn').prop('disabled', false).html('<i class="bx bx-search me-1"></i> Filter');
            }, false);
        }
    }

    // Filter button click handler
    $('#filterBtn').on('click', function() {
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Filtering...');
        reloadDataTable();
    });

    // Delete confirmation modal function
    window.confirmDelete = function(transferInfo, deleteUrl) {
        $('#transferInfo').text(transferInfo);
        $('#transferInfoText').show();
        $('#deleteForm').attr('action', deleteUrl);
        $('#deleteModal').modal('show');
    };

    // Transfer In modal function
    window.showTransferInModal = function(transferRouteKey) {
        $('#transferInForm').attr('action', '{{ route("school.student-transfers.transfer-in", ":id") }}'.replace(':id', transferRouteKey));
        $('#transferInModal').modal('show');
    };

    // Load streams for selected class
    function loadStreamsForClass(classId) {
        const streamSelect = $('#transferInModal #stream_id');

        if (!classId) {
            streamSelect.empty().append('<option value="">Select Stream</option>');
            return;
        }

        streamSelect.prop('disabled', true);

        $.ajax({
            url: '{{ route("school.api.students.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            success: function(response) {
                streamSelect.empty();
                streamSelect.append('<option value="">Select Stream</option>');

                if (response.streams && response.streams.length > 0) {
                    response.streams.forEach(function(stream) {
                        streamSelect.append(`<option value="${stream.id}">${stream.name}</option>`);
                    });
                }
                streamSelect.prop('disabled', false);
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.empty().append('<option value="">Error loading streams</option>');
            }
        });
    }

    // Class change handler in transfer in modal
    $('#transferInModal #class_id').on('change', function() {
        loadStreamsForClass($(this).val());
    });

    // Toast notification function
    function showToast(message, type = 'info') {
        const toastColors = {
            success: '#198754',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#0dcaf0'
        };

        const toast = $(`
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                 style="background-color: ${toastColors[type]}; position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
});

// Export functions - defined globally so buttons can access them
function exportToExcel() {
    const transferType = $('#transfer_type').val();
    const status = $('#status').val();
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();

    let url = '{{ route("school.student-transfers.export.excel") }}';
    const params = new URLSearchParams();

    if (transferType) params.append('transfer_type', transferType);
    if (status) params.append('status', status);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);

    if (params.toString()) {
        url += '?' + params.toString();
    }

    // Create a temporary link and trigger download
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function exportToPdf() {
    const transferType = $('#transfer_type').val();
    const status = $('#status').val();
    const dateFrom = $('#date_from').val();
    const dateTo = $('#date_to').val();

    let url = '{{ route("school.student-transfers.export.pdf") }}';
    const params = new URLSearchParams();

    if (transferType) params.append('transfer_type', transferType);
    if (status) params.append('status', status);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);

    if (params.toString()) {
        url += '?' + params.toString();
    }

    // Create a temporary link and trigger download
    const link = document.createElement('a');
    link.href = url;
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush