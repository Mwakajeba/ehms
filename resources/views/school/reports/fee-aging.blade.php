@extends('layouts.main')

@section('title', 'Fee Aging Report')

@push('styles')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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

    .fee-group-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .aging-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-current {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-0-30 {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-31-60 {
        background-color: #ffeaa7;
        color: #d63031;
    }

    .badge-61-90 {
        background-color: #fab1a0;
        color: #2d3436;
    }

    .badge-91-plus {
        background-color: #f8d7da;
        color: #721c24;
    }

    .aging-amount {
        font-weight: 600;
        font-size: 1.1rem;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Fee Aging Report', 'url' => '#', 'icon' => 'bx bx-time']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">FEE AGING REPORT</h6>
                <p class="text-muted mb-0">Fee payment aging analysis</p>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="bx bx-filter-alt me-2"></i>Filters & Options
                </h6>
                <form method="GET" action="{{ route('school.reports.fee-aging') }}" class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label for="academic_year_id" class="form-label fw-semibold">Academic Year</label>
                        <select class="form-select select2" id="academic_year_id" name="academic_year_id">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ ($academicYearId == $year->id) ? 'selected' : '' }}>
                                    {{ $year->year_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="class_id" class="form-label fw-semibold">Class</label>
                        <select class="form-select select2" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ ($classId == $class->id) ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="stream_id" class="form-label fw-semibold">Stream</label>
                        <select class="form-select select2" id="stream_id" name="stream_id">
                            <option value="">All Streams</option>
                            @foreach($streams as $stream)
                                <option value="{{ $stream->id }}" {{ ($streamId == $stream->id) ? 'selected' : '' }}>
                                    {{ $stream->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="fee_group_id" class="form-label fw-semibold">Fee Group</label>
                        <select class="form-select select2" id="fee_group_id" name="fee_group_id">
                            <option value="">All Fee Groups</option>
                            @foreach($feeGroups as $feeGroup)
                                <option value="{{ $feeGroup->id }}" {{ ($feeGroupId == $feeGroup->id) ? 'selected' : '' }}>
                                    {{ $feeGroup->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="period" class="form-label fw-semibold">Period</label>
                        <select class="form-select select2" id="period" name="period">
                            <option value="">All Periods</option>
                            <option value="Q1" {{ (isset($period) && $period == 'Q1') ? 'selected' : '' }}>Q1</option>
                            <option value="Q2" {{ (isset($period) && $period == 'Q2') ? 'selected' : '' }}>Q2</option>
                            <option value="Q3" {{ (isset($period) && $period == 'Q3') ? 'selected' : '' }}>Q3</option>
                            <option value="Q4" {{ (isset($period) && $period == 'Q4') ? 'selected' : '' }}>Q4</option>
                            <option value="Term 1" {{ (isset($period) && $period == 'Term 1') ? 'selected' : '' }}>Term 1</option>
                            <option value="Term 2" {{ (isset($period) && $period == 'Term 2') ? 'selected' : '' }}>Term 2</option>
                            <option value="Annual" {{ (isset($period) && $period == 'Annual') ? 'selected' : '' }}>Annual</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="as_of_date" class="form-label fw-semibold">As of Date</label>
                        <input type="date" class="form-control" id="as_of_date" name="as_of_date" value="{{ $asOfDate }}">
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary btn-modern">
                                    <i class="bx bx-search me-1"></i>Apply Filters
                                </button>
                                <a href="{{ route('school.reports.fee-aging') }}" class="btn btn-outline-secondary btn-modern">
                                    <i class="bx bx-undo me-1"></i>Reset
                                </a>
                            </div>
                            @if(!empty($agingData) && !empty($agingData['fee_groups']))
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-modern dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-download me-1"></i>Export Report
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportReport('pdf'); return false;">
                                            <i class="bx bx-file me-2"></i>Export as PDF
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="exportReport('excel'); return false;">
                                            <i class="bx bx-spreadsheet me-2"></i>Export as Excel
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Statistics -->
        @if(!empty($agingData) && !empty($agingData['fee_groups']))
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card border-primary">
                    <div class="card-body text-center">
                        <i class="bx bx-money stats-icon text-primary"></i>
                        <div class="stat-number text-primary">
                            {{ number_format($agingData['grand_totals']['total_outstanding'], 2) }}
                        </div>
                        <div class="stat-label">Total Outstanding</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-warning">
                    <div class="card-body text-center">
                        <i class="bx bx-user stats-icon text-warning"></i>
                        <div class="stat-number text-warning">{{ $agingData['grand_totals']['student_count'] }}</div>
                        <div class="stat-label">Students with Outstanding</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-info">
                    <div class="card-body text-center">
                        <i class="bx bx-file stats-icon text-info"></i>
                        <div class="stat-number text-info">{{ $agingData['grand_totals']['invoice_count'] }}</div>
                        <div class="stat-label">Outstanding Invoices</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-danger">
                    <div class="card-body text-center">
                        <i class="bx bx-time-five stats-icon text-danger"></i>
                        <div class="stat-number text-danger">
                            {{ number_format($agingData['grand_totals']['91+'], 2) }}
                        </div>
                        <div class="stat-label">91+ Days Overdue</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Fee Group-Wise Data -->
        <div class="card table-card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bx bx-bar-chart me-2"></i>Fee Aging Analysis by Fee Group
                </h6>
            </div>
            <div class="card-body">
                @if(empty($agingData) || empty($agingData['fee_groups']))
                    <div class="alert alert-info text-center">
                        <i class="bx bx-info-circle me-2"></i>
                        No outstanding fees found for the selected filters. Please adjust your filters and try again.
                    </div>
                @else
                    <!-- Grand Totals Summary -->
                    @if(isset($agingData['grand_totals']))
                    <div class="card border-primary mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">Grand Totals Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Current</th>
                                            <th>0-30 Days</th>
                                            <th>31-60 Days</th>
                                            <th>61-90 Days</th>
                                            <th>91+ Days</th>
                                            <th class="fw-bold">Total Outstanding</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="aging-amount text-success">
                                                {{ number_format($agingData['grand_totals']['current'], 2) }}
                                            </td>
                                            <td class="aging-amount text-warning">
                                                {{ number_format($agingData['grand_totals']['0-30'], 2) }}
                                            </td>
                                            <td class="aging-amount" style="color: #d63031;">
                                                {{ number_format($agingData['grand_totals']['31-60'], 2) }}
                                            </td>
                                            <td class="aging-amount" style="color: #2d3436;">
                                                {{ number_format($agingData['grand_totals']['61-90'], 2) }}
                                            </td>
                                            <td class="aging-amount text-danger">
                                                {{ number_format($agingData['grand_totals']['91+'], 2) }}
                                            </td>
                                            <td class="aging-amount text-primary fw-bold">
                                                {{ number_format($agingData['grand_totals']['total_outstanding'], 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @foreach($agingData['fee_groups'] as $index => $feeGroup)
                        <div class="fee-group-section mb-4">
                            <div class="fee-group-header">
                                <h5 class="mb-0">
                                    <i class="bx bx-group me-2"></i>
                                    {{ $feeGroup['fee_group_name'] }}
                                    @if($feeGroup['fee_group_code'])
                                        <small>({{ $feeGroup['fee_group_code'] }})</small>
                                    @endif
                                </h5>
                            </div>

                            <!-- Fee Group Summary -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Current</th>
                                                    <th>0-30 Days</th>
                                                    <th>31-60 Days</th>
                                                    <th>61-90 Days</th>
                                                    <th>91+ Days</th>
                                                    <th class="fw-bold">Total Outstanding</th>
                                                    <th>Students</th>
                                                    <th>Invoices</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="aging-amount text-success">
                                                        {{ number_format($feeGroup['current'], 2) }}
                                                    </td>
                                                    <td class="aging-amount text-warning">
                                                        {{ number_format($feeGroup['0-30'], 2) }}
                                                    </td>
                                                    <td class="aging-amount" style="color: #d63031;">
                                                        {{ number_format($feeGroup['31-60'], 2) }}
                                                    </td>
                                                    <td class="aging-amount" style="color: #2d3436;">
                                                        {{ number_format($feeGroup['61-90'], 2) }}
                                                    </td>
                                                    <td class="aging-amount text-danger">
                                                        {{ number_format($feeGroup['91+'], 2) }}
                                                    </td>
                                                    <td class="aging-amount text-primary fw-bold">
                                                        {{ number_format($feeGroup['total_outstanding'], 2) }}
                                                    </td>
                                                    <td>{{ $feeGroup['student_count'] }}</td>
                                                    <td>{{ $feeGroup['invoice_count'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Invoice Listing -->
                            @if(!empty($feeGroup['invoices']))
                                <div class="card border-0 bg-light">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Detailed Invoice Listing</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered table-hover" id="invoicesTable{{ $feeGroup['fee_group_id'] ?? $index }}">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Invoice #</th>
                                                        <th>Student</th>
                                                        <th>Class</th>
                                                        <th>Stream</th>
                                                        <th>Issue Date</th>
                                                        <th>Due Date</th>
                                                        <th>Days Overdue</th>
                                                        <th>Total Amount</th>
                                                        <th>Paid</th>
                                                        <th>Outstanding</th>
                                                        <th>Aging</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($feeGroup['invoices'] as $invoice)
                                                        <tr>
                                                            <td>{{ $invoice['invoice_number'] }}</td>
                                                            <td>{{ $invoice['student_name'] }}</td>
                                                            <td>{{ $invoice['class_name'] }}</td>
                                                            <td>{{ $invoice['stream_name'] }}</td>
                                                            <td>{{ $invoice['issue_date'] }}</td>
                                                            <td>{{ $invoice['due_date'] }}</td>
                                                            <td>
                                                                @if($invoice['days_overdue'] > 0)
                                                                    <span class="text-danger fw-bold">{{ $invoice['days_overdue'] }}</span>
                                                                @else
                                                                    <span class="text-success">0</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ number_format($invoice['total_amount'], 2) }}</td>
                                                            <td>{{ number_format($invoice['paid_amount'], 2) }}</td>
                                                            <td class="fw-bold">{{ number_format($invoice['outstanding_amount'], 2) }}</td>
                                                            <td>
                                                                @if($invoice['aging_bucket'] == 'current')
                                                                    <span class="aging-badge badge-current">Current</span>
                                                                @elseif($invoice['aging_bucket'] == '0-30')
                                                                    <span class="aging-badge badge-0-30">0-30 Days</span>
                                                                @elseif($invoice['aging_bucket'] == '31-60')
                                                                    <span class="aging-badge badge-31-60">31-60 Days</span>
                                                                @elseif($invoice['aging_bucket'] == '61-90')
                                                                    <span class="aging-badge badge-61-90">61-90 Days</span>
                                                                @else
                                                                    <span class="aging-badge badge-91-plus">91+ Days</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <hr>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Initialize DataTables for each invoice table
        @if(!empty($agingData) && !empty($agingData['fee_groups']))
            @foreach($agingData['fee_groups'] as $index => $feeGroup)
                @if(!empty($feeGroup['invoices']))
                    $('#invoicesTable{{ $feeGroup['fee_group_id'] ?? $index }}').DataTable({
                        responsive: true,
                        order: [[7, 'asc']], // Sort by due date ascending (oldest first)
                        pageLength: 25,
                        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                        language: {
                            search: "_INPUT_",
                            searchPlaceholder: "Search invoices..."
                        },
                        columnDefs: [
                            { orderable: true, targets: '_all' },
                            { className: 'text-center', targets: [8, 9, 10, 11, 12] },
                            { className: 'text-end', targets: [9, 10, 11] }
                        ]
                    });
                @endif
            @endforeach
        @endif
    });

    // Export report function (global scope)
    window.exportReport = function(type) {
        try {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("school.reports.fee-aging.export") }}';
            form.target = '_blank';
            form.style.display = 'none';

            // Add CSRF token
            var csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            // Add export type
            var exportInput = document.createElement('input');
            exportInput.type = 'hidden';
            exportInput.name = 'export';
            exportInput.value = type;
            form.appendChild(exportInput);

            // Add filter values from URL parameters or form inputs
            var urlParams = new URLSearchParams(window.location.search);
            var filters = ['academic_year_id', 'class_id', 'stream_id', 'fee_group_id', 'as_of_date', 'period'];
            
            filters.forEach(function(filter) {
                var value = null;
                // Try to get from form input first
                var input = document.getElementById(filter);
                if (input && input.value) {
                    value = input.value;
                } else {
                    // Fallback to URL parameter
                    value = urlParams.get(filter);
                }
                
                if (value) {
                    var filterInput = document.createElement('input');
                    filterInput.type = 'hidden';
                    filterInput.name = filter;
                    filterInput.value = value;
                    form.appendChild(filterInput);
                }
            });

            document.body.appendChild(form);
            form.submit();
            
            // Remove form after a short delay
            setTimeout(function() {
                if (form.parentNode) {
                    document.body.removeChild(form);
                }
            }, 1000);
        } catch (error) {
            console.error('Export error:', error);
            alert('An error occurred while exporting the report. Please try again.');
        }
    }
</script>
@endpush

