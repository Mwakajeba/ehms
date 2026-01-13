@extends('layouts.main')

@section('title', 'Fee Waivers & Discounts Report')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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

    .class-header {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .stream-header {
        background: linear-gradient(135deg, #ffd54f 0%, #ffb74d 100%);
        color: #333;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .discount-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-fixed {
        background-color: #17a2b8;
        color: white;
    }

    .badge-percentage {
        background-color: #28a745;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Fee Waivers & Discounts', 'url' => '#', 'icon' => 'bx bx-discount']
        ]" />
        <h6 class="mb-0 text-uppercase">FEE WAIVERS & DISCOUNTS REPORT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <!-- Filters -->
                <div class="card filter-card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-filter me-2"></i>Filters
                            </h5>
                            @if(!empty($waiversDiscountsData) && !empty($waiversDiscountsData['grand_totals']))
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
                        <form method="GET" action="{{ route('school.reports.fee-waivers-discounts') }}" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="academic_year_id" class="form-label">Academic Year</label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-select select2">
                                        <option value="">All Academic Years</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ Hashids::encode($year->id) }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
                                                {{ $year->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="class_id" class="form-label">Class</label>
                                    <select name="class_id" id="class_id" class="form-select select2">
                                        <option value="">All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ Hashids::encode($class->id) }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="stream_id" class="form-label">Stream</label>
                                    <select name="stream_id" id="stream_id" class="form-select select2">
                                        <option value="">All Streams</option>
                                        @foreach($streams as $stream)
                                            <option value="{{ Hashids::encode($stream->id) }}" {{ $streamId == $stream->id ? 'selected' : '' }}>
                                                {{ $stream->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="discount_type" class="form-label">Discount Type</label>
                                    <select name="discount_type" id="discount_type" class="form-select select2">
                                        <option value="">All Types</option>
                                        <option value="fixed" {{ $discountType == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                        <option value="percentage" {{ $discountType == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="period" class="form-label">Period</label>
                                    <select name="period" id="period" class="form-select select2">
                                        <option value="">All Periods</option>
                                        <option value="Q1" {{ $period == 'Q1' ? 'selected' : '' }}>Q1</option>
                                        <option value="Q2" {{ $period == 'Q2' ? 'selected' : '' }}>Q2</option>
                                        <option value="Q3" {{ $period == 'Q3' ? 'selected' : '' }}>Q3</option>
                                        <option value="Q4" {{ $period == 'Q4' ? 'selected' : '' }}>Q4</option>
                                        <option value="Term 1" {{ $period == 'Term 1' ? 'selected' : '' }}>Term 1</option>
                                        <option value="Term 2" {{ $period == 'Term 2' ? 'selected' : '' }}>Term 2</option>
                                        <option value="Annual" {{ $period == 'Annual' ? 'selected' : '' }}>Annual</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-modern">
                                        <i class="bx bx-search me-1"></i>Apply Filters
                                    </button>
                                    <a href="{{ route('school.reports.fee-waivers-discounts') }}" class="btn btn-outline-secondary btn-modern">
                                        <i class="bx bx-undo me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(!empty($waiversDiscountsData) && !empty($waiversDiscountsData['grand_totals']))
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card border-warning">
                            <div class="card-body text-center">
                                <i class="bx bx-receipt stats-icon text-warning"></i>
                                <div class="stat-number text-warning">{{ number_format($waiversDiscountsData['grand_totals']['total_invoices']) }}</div>
                                <div class="stat-label text-warning">Total Invoices</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-info">
                            <div class="card-body text-center">
                                <i class="bx bx-money stats-icon text-info"></i>
                                <div class="stat-number text-info">{{ number_format($waiversDiscountsData['grand_totals']['total_subtotal'], 2) }}</div>
                                <div class="stat-label text-info">Total Subtotal</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-danger">
                            <div class="card-body text-center">
                                <i class="bx bx-discount stats-icon text-danger"></i>
                                <div class="stat-number text-danger">{{ number_format($waiversDiscountsData['grand_totals']['total_discount_amount'], 2) }}</div>
                                <div class="stat-label text-danger">Total Discounts</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-success">
                            <div class="card-body text-center">
                                <i class="bx bx-check-circle stats-icon text-success"></i>
                                <div class="stat-number text-success">{{ number_format($waiversDiscountsData['grand_totals']['total_after_discount'], 2) }}</div>
                                <div class="stat-label text-success">After Discount</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discount Type Breakdown -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bx bx-money me-2"></i>Fixed Amount Discounts
                                </h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="h4 mb-0">{{ number_format($waiversDiscountsData['grand_totals']['discount_types']['fixed']['count']) }}</div>
                                        <small class="text-muted">Invoices</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="h4 mb-0 text-info">{{ number_format($waiversDiscountsData['grand_totals']['discount_types']['fixed']['amount'], 2) }}</div>
                                        <small class="text-muted">Total Amount</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <h5 class="card-title mb-3">
                                    <i class="bx bx-percent me-2"></i>Percentage Discounts
                                </h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="h4 mb-0">{{ number_format($waiversDiscountsData['grand_totals']['discount_types']['percentage']['count']) }}</div>
                                        <small class="text-muted">Invoices</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="h4 mb-0 text-success">{{ number_format($waiversDiscountsData['grand_totals']['discount_types']['percentage']['amount'], 2) }}</div>
                                        <small class="text-muted">Total Amount</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overall Discount Rate -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-2">Overall Discount Rate</h5>
                                <div class="h2 mb-0 text-warning">{{ number_format($waiversDiscountsData['grand_totals']['discount_percentage'], 2) }}%</div>
                                <small class="text-muted">of total subtotal</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Waivers & Discounts Data by Class -->
                <div class="card table-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bx bx-bar-chart-alt-2 me-2"></i>Fee Waivers & Discounts by Class
                        </h5>

                        @if(!empty($waiversDiscountsData['classes']))
                            @foreach($waiversDiscountsData['classes'] as $classData)
                            <div class="mb-4">
                                <div class="class-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bx bx-group me-2"></i>{{ $classData['class_name'] }}
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark me-2">
                                                Invoices: {{ $classData['total_invoices'] }}
                                            </span>
                                            <span class="badge bg-light text-dark me-2">
                                                Subtotal: {{ number_format($classData['total_subtotal'], 2) }}
                                            </span>
                                            <span class="badge bg-light text-dark me-2">
                                                Discounts: {{ number_format($classData['total_discount_amount'], 2) }}
                                            </span>
                                            <span class="badge bg-light text-dark">
                                                Rate: {{ number_format($classData['discount_percentage'], 2) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($classData['streams']))
                                    @foreach($classData['streams'] as $streamData)
                                    <div class="mb-3">
                                        <div class="stream-header">
                                            <i class="bx bx-list-ul me-2"></i>{{ $streamData['stream_name'] }} - 
                                            Invoices: {{ $streamData['total_invoices'] }} | 
                                            Discounts: {{ number_format($streamData['total_discount_amount'], 2) }} | 
                                            Rate: {{ number_format($streamData['discount_percentage'], 2) }}%
                                        </div>

                                        @if(!empty($streamData['invoices']))
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover mb-3" id="invoicesTable{{ $streamData['stream_id'] ?? $loop->index }}">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Hash ID</th>
                                                        <th>Invoice #</th>
                                                        <th>Student</th>
                                                        <th>Admission #</th>
                                                        <th>Period</th>
                                                        <th>Issue Date</th>
                                                        <th class="text-end">Subtotal</th>
                                                        <th>Discount Type</th>
                                                        <th>Discount Value</th>
                                                        <th class="text-end">Discount Amount</th>
                                                        <th class="text-end">After Discount</th>
                                                        <th class="text-end">Total Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($streamData['invoices'] as $invoice)
                                                    <tr>
                                                        <td>{{ $invoice['hash_id'] }}</td>
                                                        <td>{{ $invoice['invoice_number'] }}</td>
                                                        <td>{{ $invoice['student_name'] }}</td>
                                                        <td>{{ $invoice['admission_number'] }}</td>
                                                        <td>{{ $invoice['period'] }}</td>
                                                        <td>{{ $invoice['issue_date'] }}</td>
                                                        <td class="text-end">{{ number_format($invoice['subtotal'], 2) }}</td>
                                                        <td>
                                                            @if($invoice['discount_type'] != 'N/A')
                                                                <span class="discount-badge badge-{{ $invoice['discount_type'] }}">
                                                                    {{ ucfirst($invoice['discount_type']) }}
                                                                </span>
                                                            @else
                                                                <span class="badge bg-secondary">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($invoice['discount_type'] == 'percentage')
                                                                {{ number_format($invoice['discount_value'], 2) }}%
                                                            @elseif($invoice['discount_type'] == 'fixed')
                                                                {{ number_format($invoice['discount_value'], 2) }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-end text-danger">{{ number_format($invoice['discount_amount'], 2) }}</td>
                                                        <td class="text-end text-success">{{ number_format($invoice['after_discount'], 2) }}</td>
                                                        <td class="text-end">{{ number_format($invoice['total_amount'], 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                    <tr class="table-secondary fw-bold">
                                                        <td colspan="6" class="text-end">Stream Total</td>
                                                        <td class="text-end">{{ number_format($streamData['total_subtotal'], 2) }}</td>
                                                        <td colspan="2"></td>
                                                        <td class="text-end text-danger">{{ number_format($streamData['total_discount_amount'], 2) }}</td>
                                                        <td class="text-end text-success">{{ number_format($streamData['total_after_discount'], 2) }}</td>
                                                        <td></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-info text-center">
                                <i class="bx bx-info-circle me-2"></i>No waivers or discounts found for the selected filters.
                            </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="alert alert-info text-center">
                    <i class="bx bx-info-circle me-2"></i>No waivers or discounts found for the selected filters.
                </div>
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
        @if(!empty($waiversDiscountsData) && !empty($waiversDiscountsData['classes']))
            @foreach($waiversDiscountsData['classes'] as $classIndex => $classData)
                @if(!empty($classData['streams']))
                    @foreach($classData['streams'] as $streamIndex => $streamData)
                        @if(!empty($streamData['invoices']))
                            $('#invoicesTable{{ $streamData['stream_id'] ?? ($classIndex . '_' . $streamIndex) }}').DataTable({
                                responsive: true,
                                order: [[5, 'desc']], // Sort by issue date descending
                                pageLength: 25,
                                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                                language: {
                                    search: "_INPUT_",
                                    searchPlaceholder: "Search invoices..."
                                },
                                columnDefs: [
                                    { orderable: true, targets: '_all' },
                                    { className: 'text-center', targets: [4, 5] },
                                    { className: 'text-end', targets: [6, 9, 10, 11] }
                                ]
                            });
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endif
    });

    // Export report function (global scope)
    window.exportReport = function(type) {
        try {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("school.reports.fee-waivers-discounts.export") }}';
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
            var filters = ['academic_year_id', 'class_id', 'stream_id', 'discount_type', 'period', 'date_from', 'date_to'];
            
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

