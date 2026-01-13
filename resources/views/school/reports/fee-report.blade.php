@extends('layouts.main')

@section('title', 'Fee Payment Status Report')

@push('styles')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
<style>
    .stats-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: none;
        border-radius: 12px;
    }

    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }

    .filter-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .table-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .btn-modern {
        border-radius: 8px;
        font-weight: 500;
        padding: 0.5rem 1.5rem;
        transition: all 0.2s ease-in-out;
    }

    .btn-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.8;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1.5rem;
    }

    .status-paid {
        background-color: #d4edda;
        color: #155724;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .status-overdue {
        background-color: #f8d7da;
        color: #721c24;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .status-issued {
        background-color: #e2e3e5;
        color: #383d41;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .status-partial-paid {
        background-color: #d1ecf1;
        color: #0c5460;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 500;
    }

    .fee-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: 600;
        border: none;
        padding: 12px 8px;
    }

    .fee-table td {
        padding: 10px 8px;
        vertical-align: middle;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        margin-bottom: 15px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 6px;
        border: 1px solid #ced4da;
        padding: 0.375rem 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Fee Payment Status', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">FEE PAYMENT STATUS REPORT</h6>
                <p class="text-muted mb-0">Comprehensive fee collection and payment status analysis</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-modern" onclick="window.location.reload()">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card border-info mb-4">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bx bx-filter me-2"></i> Filter Fee Payment Status
                </h6>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="academic_year_id" class="form-label fw-bold">Academic Year</label>
                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                <option value="">All Academic Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                        {{ $year->year_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="class_id" class="form-label fw-bold">Class</label>
                            <select class="form-select" id="class_id" name="class_id">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                        {{ $class->name }} ({{ $class->students_count ?? 0 }} students)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="stream_id" class="form-label fw-bold">Stream</label>
                            <select class="form-select" id="stream_id" name="stream_id">
                                <option value="">All Streams</option>
                                @if($streams ?? [])
                                    @foreach($streams as $stream)
                                        <option value="{{ $stream->id }}" {{ ($streamId ?? null) == $stream->id ? 'selected' : '' }}>
                                            {{ $stream->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="quarter" class="form-label fw-bold">Period</label>
                            <select class="form-select" id="quarter" name="quarter">
                                <option value="">All Periods</option>
                                <option value="1" {{ $quarter == '1' ? 'selected' : '' }}>Q1</option>
                                <option value="2" {{ $quarter == '2' ? 'selected' : '' }}>Q2</option>
                                <option value="3" {{ $quarter == '3' ? 'selected' : '' }}>Q3</option>
                                <option value="4" {{ $quarter == '4' ? 'selected' : '' }}>Q4</option>
                                <option value="6" {{ $quarter == '6' ? 'selected' : '' }}>Term 1</option>
                                <option value="7" {{ $quarter == '7' ? 'selected' : '' }}>Term 2</option>
                                <option value="5" {{ $quarter == '5' ? 'selected' : '' }}>Annual</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label fw-bold">Payment Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="issued" {{ $status == 'issued' ? 'selected' : '' }}>Issued</option>
                                <option value="partial_paid" {{ $status == 'partial_paid' ? 'selected' : '' }}>Partial Paid</option>
                                <option value="paid" {{ $status == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="overdue" {{ $status == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary btn-sm" id="filterBtn">
                                    <i class="bx bx-search me-1"></i> Filter
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearBtn">
                                    <i class="bx bx-refresh me-1"></i> Clear
                                </button>
                                <button type="button" class="btn btn-success btn-sm" id="exportPdfBtn">
                                    <i class="bx bx-file me-1"></i> Export PDF
                                </button>
                                <button type="button" class="btn btn-info btn-sm" id="exportExcelBtn">
                                    <i class="bx bx-spreadsheet me-1"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Class-wise Summary -->
        @if($feeData['class_summary']->isNotEmpty())
        <div class="card table-card mb-4">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="bx bx-bar-chart me-2"></i>Class-wise Fee Summary
                </h6>
                <div class="table-responsive">
                    <table class="table table-bordered fee-table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th class="text-center">Total Invoices</th>
                                <th class="text-center">Issued</th>
                                <th class="text-center">Partial Paid</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Overdue</th>
                                <th class="text-center">Total Amount</th>
                                <th class="text-center">Paid Amount</th>
                                <th class="text-center">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeData['class_summary'] as $className => $summary)
                                <tr>
                                    <td class="font-weight-bold">{{ $className }}</td>
                                    <td class="text-center">{{ number_format($summary['total_invoices']) }}</td>
                                    <td class="text-center">
                                        <span class="badge status-issued">{{ number_format($summary['issued_count'] ?? 0) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge status-partial-paid">{{ number_format($summary['partial_paid_count'] ?? 0) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge status-paid">{{ number_format($summary['paid_count']) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge status-overdue">{{ number_format($summary['overdue_count']) }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($summary['total_amount'], 2) }}</td>
                                    <td class="text-center">{{ number_format($summary['total_paid'], 2) }}</td>
                                    <td class="text-center font-weight-bold">{{ number_format($summary['total_outstanding'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Detailed Invoices Table -->
        <div class="card table-card">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="bx bx-detail me-2"></i>Detailed Fee Invoices
                </h6>
                <div class="table-responsive">
                    <table class="table table-hover" id="feeInvoicesTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th><i class="bx bx-receipt me-1"></i>Invoice #</th>
                                <th><i class="bx bx-user me-1"></i>Student</th>
                                <th><i class="bx bx-school me-1"></i>Class</th>
                                <th><i class="bx bx-branch me-1"></i>Stream</th>
                                <th><i class="bx bx-calendar-week me-1"></i>Quarter</th>
                                <th><i class="bx bx-calendar-star me-1"></i>Academic Year</th>
                                <th class="text-center"><i class="bx bx-money me-1"></i>Total Amount</th>
                                <th class="text-center"><i class="bx bx-check-circle me-1"></i>Paid Amount</th>
                                <th class="text-center"><i class="bx bx-trending-down me-1"></i>Outstanding</th>
                                <th class="text-center"><i class="bx bx-calendar me-1"></i>Due Date</th>
                                <th class="text-center"><i class="bx bx-info-circle me-1"></i>Status</th>
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
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        placeholder: 'Select an option'
    });

    // Initialize DataTable with server-side processing
    var table = $('#feeInvoicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.reports.fee-report") }}',
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: function(d) {
                // Add filter parameters to the AJAX request
                d.academic_year_id = $('#academic_year_id').val();
                d.class_id = $('#class_id').val();
                d.stream_id = $('#stream_id').val();
                d.quarter = $('#quarter').val();
                d.status = $('#status').val();
                d.ajax = 1; // Flag to indicate AJAX request
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', xhr, error, thrown);
                alert('Error loading data: ' + error + ' - ' + thrown);
            }
        },
        columns: [
            {
                data: 'invoice_number',
                name: 'invoice_number',
                orderable: true
            },
            {
                data: 'student_name',
                name: 'student_name',
                orderable: true
            },
            {
                data: 'class_name',
                name: 'class_name',
                orderable: true
            },
            {
                data: 'stream_name',
                name: 'stream_name',
                orderable: true
            },
            {
                data: 'quarter',
                name: 'quarter',
                orderable: true
            },
            {
                data: 'academic_year',
                name: 'academic_year',
                orderable: true
            },
            {
                data: 'total_amount',
                name: 'total_amount',
                orderable: true,
                className: 'text-center'
            },
            {
                data: 'paid_amount',
                name: 'paid_amount',
                orderable: true,
                className: 'text-center'
            },
            {
                data: 'outstanding_amount',
                name: 'outstanding_amount',
                orderable: true,
                className: 'text-center'
            },
            {
                data: 'due_date',
                name: 'due_date',
                orderable: true,
                className: 'text-center'
            },
            {
                data: 'status',
                name: 'status',
                orderable: false,
                className: 'text-center'
            }
        ],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "_INPUT_",
            searchPlaceholder: "Search fee invoices...",
            lengthMenu: "_MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "No entries found",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        initComplete: function() {
            // Add search input styling
            $('.dataTables_filter input').addClass('form-control');
            $('.dataTables_length select').addClass('form-select');
            console.log('DataTable initialized successfully');
        }
    });

    // Prevent form submission and use DataTable reload instead
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload(null, false); // false = don't reset paging
        return false;
    });

    // Filter button click handler
    $('#filterBtn').click(function(e) {
        e.preventDefault();
        // Reload DataTable with current filter values
        table.ajax.reload(null, false); // false = don't reset paging
    });

    // Clear button click handler
    $('#clearBtn').click(function(e) {
        e.preventDefault();
        $('#filterForm')[0].reset();
        // Reload DataTable after clearing
        table.ajax.reload(null, false);
    });

    // Export PDF button click handler
    $('#exportPdfBtn').click(function() {
        var formData = new FormData(document.getElementById('filterForm'));
        formData.append('export', 'pdf');

        // Create a temporary form to submit
        var form = $('<form>', {
            'method': 'POST',
            'action': '{{ route("school.reports.fee-report") }}',
            'target': '_blank'
        });

        // Add CSRF token
        form.append($('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': $('meta[name="csrf-token"]').attr('content')
        }));

        // Add form data
        for (var pair of formData.entries()) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': pair[0],
                'value': pair[1]
            }));
        }

        // Submit the form
        $('body').append(form);
        form.submit();
        form.remove();
    });

    // Export Excel button click handler
    $('#exportExcelBtn').click(function() {
        var formData = new FormData(document.getElementById('filterForm'));
        formData.append('export', 'excel');

        // Create a temporary form to submit
        var form = $('<form>', {
            'method': 'POST',
            'action': '{{ route("school.reports.fee-report") }}',
            'target': '_blank'
        });

        // Add CSRF token
        form.append($('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': $('meta[name="csrf-token"]').attr('content')
        }));

        // Add form data
        for (var pair of formData.entries()) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': pair[0],
                'value': pair[1]
            }));
        }

        // Submit the form
        $('body').append(form);
        form.submit();
        form.remove();
    });

    // Class change handler - populate streams
    $('#class_id').change(function() {
        var classId = $(this).val();
        var streamSelect = $('#stream_id');

        if (classId) {
            // Fetch streams for the selected class
            $.ajax({
                url: '{{ route("school.reports.fee-report.streams-by-class") }}',
                type: 'GET',
                data: { class_id: classId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    streamSelect.empty();
                    streamSelect.append('<option value="">All Streams</option>');

                    if (response.streams && response.streams.length > 0) {
                        response.streams.forEach(function(stream) {
                            streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                        });
                    }
                },
                error: function(xhr, error, thrown) {
                    console.error('Error loading streams:', xhr, error, thrown);
                    streamSelect.empty();
                    streamSelect.append('<option value="">All Streams</option>');
                }
            });
        } else {
            // Reset streams dropdown
            streamSelect.empty();
            streamSelect.append('<option value="">All Streams</option>');
        }
    });

    console.log('Fee Report DataTable script loaded');
});
</script>
@endpush