@extends('layouts.main')

@section('title', 'Gender Distribution Report (with Totals)')

@push('styles')
<link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .stats-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 1px solid #dee2e6;
        border-radius: 12px;
        background: #fff;
    }

    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stats-icon {
        font-size: 1.8rem;
        opacity: 0.8;
        color: #6c757d;
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
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: #495057;
    }

    .stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.8;
        color: #6c757d;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1.5rem;
    }

    .gender-male {
        color: #007bff;
        font-weight: 600;
    }

    .gender-female {
        color: #e83e8c;
        font-weight: 600;
    }

    .total-row {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .grand-total-row {
        background-color: #e9ecef;
        font-weight: 700;
        border-top: 2px solid #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Gender Distribution Report', 'url' => '#', 'icon' => 'bx bx-group']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">GENDER DISTRIBUTION REPORT (WITH TOTALS)</h6>
                <p class="text-muted mb-0">Student gender distribution by class and stream with comprehensive totals</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-modern" onclick="refreshData()">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="bx bx-filter-alt me-2"></i>Filters & Options
                </h6>
                <form id="genderFilterForm" class="row g-3">
                    <div class="col-md-6 col-lg-4">
                        <label for="class_id" class="form-label fw-semibold">Class</label>
                        <select class="form-select select2" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label for="stream_id" class="form-label fw-semibold">Stream</label>
                        <select class="form-select select2" id="stream_id" name="stream_id">
                            <option value="">All Streams</option>
                            @foreach($streams as $stream)
                                <option value="{{ $stream->id }}" {{ request('stream_id') == $stream->id ? 'selected' : '' }}>{{ $stream->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <label for="academic_year_id" class="form-label fw-semibold">Academic Year</label>
                        <select class="form-select select2" id="academic_year_id" name="academic_year_id">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (request('academic_year_id') ?: ($year->is_current ? $year->id : '')) == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-primary btn-modern me-2" id="applyFilters">
                                    <i class="bx bx-search me-1"></i>Apply Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-modern" id="resetFilters">
                                    <i class="bx bx-undo me-1"></i>Reset
                                </button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success btn-modern dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bx bx-download me-1"></i>Export Report
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('pdf')">
                                        <i class="bx bx-file me-2"></i>PDF Report</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('excel')">
                                        <i class="bx bx-spreadsheet me-2"></i>Excel Report</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mb-2">
                            <i class="bx bx-male"></i>
                        </div>
                        <div class="stat-number">{{ $genderData['grandTotal']['male'] }}</div>
                        <div class="stat-label">Total Male Students</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mb-2">
                            <i class="bx bx-female"></i>
                        </div>
                        <div class="stat-number">{{ $genderData['grandTotal']['female'] }}</div>
                        <div class="stat-label">Total Female Students</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mb-2">
                            <i class="bx bx-group"></i>
                        </div>
                        <div class="stat-number">{{ $genderData['grandTotal']['total'] }}</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mb-2">
                            <i class="bx bx-pie-chart-alt"></i>
                        </div>
                        <div class="stat-number">
                            @if($genderData['grandTotal']['total'] > 0)
                                {{ round(($genderData['grandTotal']['male'] / $genderData['grandTotal']['total']) * 100, 1) }}% / {{ round(($genderData['grandTotal']['female'] / $genderData['grandTotal']['total']) * 100, 1) }}%
                            @else
                                0% / 0%
                            @endif
                        </div>
                        <div class="stat-label">Male/Female Ratio</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gender Distribution Table -->
        <div class="card table-card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="bx bx-table me-2"></i>Gender Distribution by Class and Stream
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Class Level</th>
                                <th>Stream</th>
                                <th class="text-center gender-male">Male Students</th>
                                <th class="text-center gender-female">Female Students</th>
                                <th class="text-center">Total Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($genderData['groupedData'] as $className => $streams)
                                @foreach($streams as $streamName => $data)
                                    <tr>
                                        <td>{{ $className }}</td>
                                        <td>{{ $streamName }}</td>
                                        <td class="text-center gender-male">{{ $data['male'] }}</td>
                                        <td class="text-center gender-female">{{ $data['female'] }}</td>
                                        <td class="text-center"><strong>{{ $data['total'] }}</strong></td>
                                    </tr>
                                @endforeach
                                <!-- Class Total Row -->
                                <tr class="total-row">
                                    <td><strong>{{ $className }}</strong></td>
                                    <td><strong>Total</strong></td>
                                    <td class="text-center gender-male"><strong>{{ $genderData['classTotals'][$className]['male'] }}</strong></td>
                                    <td class="text-center gender-female"><strong>{{ $genderData['classTotals'][$className]['female'] }}</strong></td>
                                    <td class="text-center"><strong>{{ $genderData['classTotals'][$className]['total'] }}</strong></td>
                                </tr>
                            @endforeach
                            <!-- Grand Total Row -->
                            <tr class="grand-total-row">
                                <td colspan="2"><strong>Grand Total</strong></td>
                                <td class="text-center gender-male"><strong>{{ $genderData['grandTotal']['male'] }}</strong></td>
                                <td class="text-center gender-female"><strong>{{ $genderData['grandTotal']['female'] }}</strong></td>
                                <td class="text-center"><strong>{{ $genderData['grandTotal']['total'] }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Select an option';
        }
    });

    // Apply filters
    $('#applyFilters').on('click', function() {
        applyFilters();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#genderFilterForm')[0].reset();
        $('.select2').val(null).trigger('change');
        applyFilters();
    });

    // Auto-submit on filter change - handle both regular select and Select2
    let filterTimeout;
    $('#genderFilterForm select').on('change', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            applyFilters();
        }, 500); // 500ms delay to prevent too many requests
    });

    // Also handle Select2 specific events
    $('.select2').on('select2:select select2:unselect', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });
});

function applyFilters() {
    const formData = new FormData(document.getElementById('genderFilterForm'));
    const params = new URLSearchParams();

    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }

    // Reload page with filters
    window.location.href = '{{ route("school.reports.gender-distribution") }}?' + params.toString();
}

function refreshData() {
    window.location.reload();
}

function exportReport(format) {
    const params = new URLSearchParams(window.location.search);
    params.append('export', format);

    const url = '{{ route("school.reports.gender-distribution") }}?' + params.toString();

    // For now, just open in new tab (you can implement actual export later)
    window.open(url, '_blank');
}
</script>
@endpush