@extends('layouts.main')

@section('title', 'Class-Wise Revenue Collection Report')

@push('styles')
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

    .class-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        font-weight: 600;
    }

    .stream-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.75rem;
        border-radius: 6px;
        margin-bottom: 0.5rem;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .period-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .collection-rate {
        font-weight: 600;
        font-size: 1.1rem;
    }

    .rate-excellent {
        color: #28a745;
    }

    .rate-good {
        color: #17a2b8;
    }

    .rate-fair {
        color: #ffc107;
    }

    .rate-poor {
        color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Class-Wise Revenue Collection', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        <h6 class="mb-0 text-uppercase">CLASS-WISE REVENUE COLLECTION REPORT</h6>
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
                            @if(!empty($revenueData) && !empty($revenueData['grand_totals']))
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
                        <form method="GET" action="{{ route('school.reports.class-wise-revenue-collection') }}" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="academic_year_id" class="form-label">Academic Year</label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-select select2">
                                        <option value="">All Academic Years</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ $academicYearId == $year->id ? 'selected' : '' }}>
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
                                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
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
                                            <option value="{{ $stream->id }}" {{ $streamId == $stream->id ? 'selected' : '' }}>
                                                {{ $stream->name }}
                                            </option>
                                        @endforeach
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
                                    <a href="{{ route('school.reports.class-wise-revenue-collection') }}" class="btn btn-outline-secondary btn-modern">
                                        <i class="bx bx-undo me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(!empty($revenueData) && !empty($revenueData['grand_totals']))
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card border-primary">
                            <div class="card-body text-center">
                                <i class="bx bx-receipt stats-icon text-primary"></i>
                                <div class="stat-number text-primary">{{ number_format($revenueData['grand_totals']['total_invoices']) }}</div>
                                <div class="stat-label text-primary">Total Invoices</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-info">
                            <div class="card-body text-center">
                                <i class="bx bx-money stats-icon text-info"></i>
                                <div class="stat-number text-info">{{ number_format($revenueData['grand_totals']['total_billed'], 2) }}</div>
                                <div class="stat-label text-info">Total Billed</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-success">
                            <div class="card-body text-center">
                                <i class="bx bx-check-circle stats-icon text-success"></i>
                                <div class="stat-number text-success">{{ number_format($revenueData['grand_totals']['total_collected'], 2) }}</div>
                                <div class="stat-label text-success">Total Collected</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-warning">
                            <div class="card-body text-center">
                                <i class="bx bx-time stats-icon text-warning"></i>
                                <div class="stat-number text-warning">{{ number_format($revenueData['grand_totals']['total_outstanding'], 2) }}</div>
                                <div class="stat-label text-warning">Total Outstanding</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collection Rate Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h5 class="card-title mb-2">Overall Collection Rate</h5>
                                <div class="collection-rate {{ $revenueData['grand_totals']['collection_rate'] >= 90 ? 'rate-excellent' : ($revenueData['grand_totals']['collection_rate'] >= 70 ? 'rate-good' : ($revenueData['grand_totals']['collection_rate'] >= 50 ? 'rate-fair' : 'rate-poor')) }}">
                                    {{ number_format($revenueData['grand_totals']['collection_rate'], 2) }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Data by Class -->
                <div class="card table-card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="bx bx-bar-chart-alt-2 me-2"></i>Revenue Collection by Class
                        </h5>

                        @if(!empty($revenueData['classes']))
                            @foreach($revenueData['classes'] as $classData)
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
                                                Billed: {{ number_format($classData['total_billed'], 2) }}
                                            </span>
                                            <span class="badge bg-light text-dark me-2">
                                                Collected: {{ number_format($classData['total_collected'], 2) }}
                                            </span>
                                            <span class="badge bg-light text-dark">
                                                Rate: {{ number_format($classData['collection_rate'], 2) }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                @if(!empty($classData['streams']))
                                    @foreach($classData['streams'] as $streamData)
                                    <div class="mb-3">
                                        <div class="stream-header">
                                            <i class="bx bx-list-ul me-2"></i>{{ $streamData['stream_name'] }}
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover mb-3">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Period</th>
                                                        <th class="text-end">Billed</th>
                                                        <th class="text-end">Collected</th>
                                                        <th class="text-end">Outstanding</th>
                                                        <th class="text-center">Collection Rate</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach(['Q1', 'Q2', 'Q3', 'Q4', 'Annual'] as $periodKey)
                                                        @if($streamData['periods'][$periodKey]['billed'] > 0 || $streamData['periods'][$periodKey]['collected'] > 0)
                                                        <tr>
                                                            <td>
                                                                <span class="period-badge bg-primary text-white">{{ $periodKey }}</span>
                                                            </td>
                                                            <td class="text-end">{{ number_format($streamData['periods'][$periodKey]['billed'], 2) }}</td>
                                                            <td class="text-end text-success">{{ number_format($streamData['periods'][$periodKey]['collected'], 2) }}</td>
                                                            <td class="text-end text-danger">{{ number_format($streamData['periods'][$periodKey]['outstanding'], 2) }}</td>
                                                            <td class="text-center">
                                                                <span class="badge {{ $streamData['periods'][$periodKey]['collection_rate'] >= 90 ? 'bg-success' : ($streamData['periods'][$periodKey]['collection_rate'] >= 70 ? 'bg-info' : ($streamData['periods'][$periodKey]['collection_rate'] >= 50 ? 'bg-warning' : 'bg-danger')) }}">
                                                                    {{ number_format($streamData['periods'][$periodKey]['collection_rate'], 2) }}%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endif
                                                    @endforeach
                                                    <tr class="table-secondary fw-bold">
                                                        <td>Stream Total</td>
                                                        <td class="text-end">{{ number_format($streamData['total_billed'], 2) }}</td>
                                                        <td class="text-end text-success">{{ number_format($streamData['total_collected'], 2) }}</td>
                                                        <td class="text-end text-danger">{{ number_format($streamData['total_outstanding'], 2) }}</td>
                                                        <td class="text-center">
                                                            <span class="badge {{ $streamData['collection_rate'] >= 90 ? 'bg-success' : ($streamData['collection_rate'] >= 70 ? 'bg-info' : ($streamData['collection_rate'] >= 50 ? 'bg-warning' : 'bg-danger')) }}">
                                                                {{ number_format($streamData['collection_rate'], 2) }}%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @endforeach
                                @endif

                                <!-- Class Period Summary -->
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-info">
                                            <tr>
                                                <th>Period</th>
                                                <th class="text-end">Billed</th>
                                                <th class="text-end">Collected</th>
                                                <th class="text-end">Outstanding</th>
                                                <th class="text-center">Collection Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(['Q1', 'Q2', 'Q3', 'Q4', 'Annual'] as $periodKey)
                                                @if($classData['periods'][$periodKey]['billed'] > 0 || $classData['periods'][$periodKey]['collected'] > 0)
                                                <tr>
                                                    <td><strong>{{ $periodKey }}</strong></td>
                                                    <td class="text-end">{{ number_format($classData['periods'][$periodKey]['billed'], 2) }}</td>
                                                    <td class="text-end text-success">{{ number_format($classData['periods'][$periodKey]['collected'], 2) }}</td>
                                                    <td class="text-end text-danger">{{ number_format($classData['periods'][$periodKey]['outstanding'], 2) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge {{ $classData['periods'][$periodKey]['collection_rate'] >= 90 ? 'bg-success' : ($classData['periods'][$periodKey]['collection_rate'] >= 70 ? 'bg-info' : ($classData['periods'][$periodKey]['collection_rate'] >= 50 ? 'bg-warning' : 'bg-danger')) }}">
                                                            {{ number_format($classData['periods'][$periodKey]['collection_rate'], 2) }}%
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-info text-center">
                                <i class="bx bx-info-circle me-2"></i>No revenue data found for the selected filters.
                            </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="alert alert-info text-center">
                    <i class="bx bx-info-circle me-2"></i>No revenue data found for the selected filters.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });

    // Export report function (global scope)
    window.exportReport = function(type) {
        try {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("school.reports.class-wise-revenue-collection.export") }}';
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
            var filters = ['academic_year_id', 'class_id', 'stream_id', 'period', 'date_from', 'date_to'];
            
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

