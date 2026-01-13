@extends('layouts.main')

@section('title', 'Monthly Attendance Trend Analysis')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
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

    .chart-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .chart-container {
        position: relative;
        height: 400px;
        min-height: 400px;
        margin-bottom: 20px;
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

    .trend-up {
        color: #28a745;
    }

    .trend-down {
        color: #dc3545;
    }

    .trend-stable {
        color: #ffc107;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Monthly Attendance Trend', 'url' => '#', 'icon' => 'bx bx-line-chart']
        ]" />
        <h6 class="mb-0 text-uppercase">MONTHLY ATTENDANCE TREND ANALYSIS</h6>
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
                            @if(!empty($trendData) && !empty($trendData['monthly_data']) && $trendData['monthly_data']->count() > 0)
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
                        <form method="GET" action="{{ route('school.reports.monthly-attendance-trend') }}" id="filterForm">
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
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-md-6 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary btn-modern">
                                        <i class="bx bx-search me-1"></i>Apply Filters
                                    </button>
                                    <a href="{{ route('school.reports.monthly-attendance-trend') }}" class="btn btn-outline-secondary btn-modern">
                                        <i class="bx bx-undo me-1"></i>Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(!empty($trendData) && !empty($trendData['monthly_data']) && $trendData['monthly_data']->count() > 0)
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card border-primary">
                            <div class="card-body text-center">
                                <i class="bx bx-calendar stats-icon text-primary"></i>
                                <div class="stat-number text-primary">{{ $trendData['grand_totals']['total_sessions'] }}</div>
                                <div class="stat-label text-primary">Total Sessions</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-info">
                            <div class="card-body text-center">
                                <i class="bx bx-group stats-icon text-info"></i>
                                <div class="stat-number text-info">{{ $trendData['grand_totals']['total_students'] }}</div>
                                <div class="stat-label text-info">Total Students</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-success">
                            <div class="card-body text-center">
                                <i class="bx bx-check-circle stats-icon text-success"></i>
                                <div class="stat-number text-success">{{ number_format($trendData['grand_totals']['overall_attendance_rate'], 1) }}%</div>
                                <div class="stat-label text-success">Overall Attendance Rate</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card border-warning">
                            <div class="card-body text-center">
                                <i class="bx bx-user-x stats-icon text-warning"></i>
                                <div class="stat-number text-warning">{{ $trendData['grand_totals']['total_absent'] }}</div>
                                <div class="stat-label text-warning">Total Absent</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="card chart-card mb-4">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bx bx-line-chart me-2"></i>Monthly Attendance Trend Chart
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="attendanceTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Monthly Data Table -->
                <div class="card table-card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">
                            <i class="bx bx-table me-2"></i>Monthly Attendance Statistics
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Month</th>
                                        <th class="text-center">Sessions</th>
                                        <th class="text-center">Students</th>
                                        <th class="text-center">Present</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Late</th>
                                        <th class="text-center">Sick</th>
                                        <th class="text-center">Total Records</th>
                                        <th class="text-center">Attendance Rate (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trendData['monthly_data'] as $month)
                                        @php
                                            $prevMonth = $trendData['monthly_data']->where('month', '<', $month['month'])->last();
                                            $trend = $prevMonth ? ($month['attendance_rate'] - $prevMonth['attendance_rate']) : 0;
                                        @endphp
                                        <tr>
                                            <td><strong>{{ $month['month_name'] }}</strong></td>
                                            <td class="text-center">{{ number_format($month['total_sessions']) }}</td>
                                            <td class="text-center">{{ number_format($month['unique_students']) }}</td>
                                            <td class="text-center text-success">{{ number_format($month['total_present']) }}</td>
                                            <td class="text-center text-danger">{{ number_format($month['total_absent']) }}</td>
                                            <td class="text-center text-warning">{{ number_format($month['total_late']) }}</td>
                                            <td class="text-center text-info">{{ number_format($month['total_sick']) }}</td>
                                            <td class="text-center">{{ number_format($month['total_records']) }}</td>
                                            <td class="text-center">
                                                <strong>{{ number_format($month['attendance_rate'], 2) }}%</strong>
                                                @if($trend > 0)
                                                    <i class="bx bx-trending-up text-success" title="Increased by {{ number_format($trend, 2) }}%"></i>
                                                @elseif($trend < 0)
                                                    <i class="bx bx-trending-down text-danger" title="Decreased by {{ number_format(abs($trend), 2) }}%"></i>
                                                @else
                                                    <i class="bx bx-minus text-warning" title="No change"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary fw-bold">
                                    <tr>
                                        <td><strong>Grand Total</strong></td>
                                        <td class="text-center">{{ number_format($trendData['grand_totals']['total_sessions']) }}</td>
                                        <td class="text-center">{{ number_format($trendData['grand_totals']['total_students']) }}</td>
                                        <td class="text-center text-success">{{ number_format($trendData['grand_totals']['total_present']) }}</td>
                                        <td class="text-center text-danger">{{ number_format($trendData['grand_totals']['total_absent']) }}</td>
                                        <td class="text-center text-warning">{{ number_format($trendData['grand_totals']['total_late']) }}</td>
                                        <td class="text-center text-info">{{ number_format($trendData['grand_totals']['total_sick']) }}</td>
                                        <td class="text-center">{{ number_format($trendData['grand_totals']['total_records']) }}</td>
                                        <td class="text-center"><strong>{{ number_format($trendData['grand_totals']['overall_attendance_rate'], 2) }}%</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @else
                <div class="alert alert-info text-center">
                    <i class="bx bx-info-circle me-2"></i>
                    No attendance data found for the selected filters. Please adjust your filters and try again.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });

    @if(!empty($trendData) && !empty($trendData['monthly_data']) && $trendData['monthly_data']->count() > 0)
    // Chart data
    const monthlyData = @json($trendData['monthly_data']);
    const months = monthlyData.map(item => item.month_name);
    const attendanceRates = monthlyData.map(item => item.attendance_rate);
    const presentCounts = monthlyData.map(item => item.total_present);
    const absentCounts = monthlyData.map(item => item.total_absent);

    // Create chart
    const ctx = document.getElementById('attendanceTrendChart').getContext('2d');
    const attendanceTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Attendance Rate (%)',
                    data: attendanceRates,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    yAxisID: 'y',
                    fill: true
                },
                {
                    label: 'Present',
                    data: presentCounts,
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    tension: 0.4,
                    yAxisID: 'y1',
                    type: 'bar'
                },
                {
                    label: 'Absent',
                    data: absentCounts,
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    tension: 0.4,
                    yAxisID: 'y1',
                    type: 'bar'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Monthly Attendance Trend Analysis'
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.yAxisID === 'y') {
                                label += context.parsed.y.toFixed(2) + '%';
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Attendance Rate (%)'
                    },
                    min: 0,
                    max: 100
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Number of Students'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    @endif

    // Export report function
    window.exportReport = function(type) {
        try {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("school.reports.monthly-attendance-trend") }}';
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

            // Add filter values from form inputs
            var filters = ['academic_year_id', 'class_id', 'stream_id', 'start_date', 'end_date'];
            
            filters.forEach(function(filter) {
                var input = document.getElementById(filter);
                if (input && input.value) {
                    var filterInput = document.createElement('input');
                    filterInput.type = 'hidden';
                    filterInput.name = filter;
                    filterInput.value = input.value;
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

