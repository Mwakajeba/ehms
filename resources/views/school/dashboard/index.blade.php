@extends('layouts.main')

@section('title', 'School Executive Dashboard')

@push('styles')
<style>
    :root {
        --dashboard-bg: #ffffff;
        --dashboard-text: #212529;
        --dashboard-card-bg: #ffffff;
        --dashboard-border: #dee2e6;
        --dashboard-text-muted: #6c757d;
    }

    body.dark-mode {
        --dashboard-bg: #1a1d29;
        --dashboard-text: #ffffff;
        --dashboard-card-bg: #252836;
        --dashboard-border: #3a3f4b;
        --dashboard-text-muted: #a0a4b8;
        background-color: var(--dashboard-bg) !important;
    }

    body.dark-mode .page-content {
        background-color: var(--dashboard-bg) !important;
        color: var(--dashboard-text);
    }

    .metric-card {
        border-radius: 10px;
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        background-color: var(--dashboard-card-bg);
        border-color: var(--dashboard-border);
    }
    
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0;
        color: inherit;
    }
    
    .metric-label {
        font-size: 0.9rem;
        color: var(--dashboard-text-muted);
        margin-bottom: 0.5rem;
    }
    
    .chart-container {
        min-height: 300px;
    }
    
    .dashboard-header {
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        background-color: transparent;
        border: 1px solid var(--dashboard-border);
    }

    body.dark-mode .dashboard-header {
        border-color: var(--dashboard-border);
    }

    .dashboard-header h2 {
        color: var(--dashboard-text);
    }

    .dashboard-header .text-white-50 {
        color: var(--dashboard-text-muted) !important;
    }
    
    .teacher-performance-bar {
        height: 30px;
        border-radius: 15px;
        background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%);
        display: flex;
        align-items: center;
        padding: 0 15px;
        color: white;
        font-weight: 600;
    }

    body.dark-mode .card {
        background-color: var(--dashboard-card-bg);
        border-color: var(--dashboard-border);
        color: var(--dashboard-text);
    }

    body.dark-mode .text-muted {
        color: var(--dashboard-text-muted) !important;
    }

    body.dark-mode .badge.bg-light {
        background-color: var(--dashboard-card-bg) !important;
        color: var(--dashboard-text) !important;
        border: 1px solid var(--dashboard-border);
    }

    .dark-mode-toggle {
        background: transparent;
        border: 2px solid var(--dashboard-border);
        color: var(--dashboard-text);
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .dark-mode-toggle:hover {
        background-color: var(--dashboard-card-bg);
        border-color: var(--dashboard-text-muted);
    }

    body.dark-mode .dark-mode-toggle {
        border-color: var(--dashboard-border);
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Management', 'url' => route('school.index'), 'icon' => 'bx bx-school'],
            ['label' => 'Executive Dashboard', 'url' => '#', 'icon' => 'bx bx-bar-chart-alt-2']
        ]" />
        
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-1">
                        <i class="bx bx-bar-chart-alt-2 me-2"></i>
                        SCHOOL EXECUTIVE DASHBOARD
                    </h2>
                    @if($activeAcademicYear)
                        <p class="mb-0 text-muted">{{ $activeAcademicYear->year_name }}</p>
                    @endif
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="dark-mode-toggle me-3" id="darkModeToggle" title="Toggle Dark Mode">
                        <i class="bx bx-moon" id="darkModeIcon"></i>
                        <span id="darkModeText">Dark Mode</span>
                    </button>
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bx bx-calendar me-1"></i>
                        {{ now()->format('F d, Y') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Key Statistics Summary -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-top border-0 border-4 border-info metric-card">
                    <div class="card-body text-center">
                        <i class="bx bx-user-check fs-1 text-info mb-3"></i>
                        <h6 class="metric-label text-uppercase">Total Teachers</h6>
                        <h2 class="metric-value text-info">{{ number_format($additionalStats['teachers']) }}</h2>
                        <p class="text-muted mb-0">Active Teachers</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-top border-0 border-4 border-success metric-card">
                    <div class="card-body text-center">
                        <i class="bx bx-book-open fs-1 text-success mb-3"></i>
                        <h6 class="metric-label text-uppercase">Total Courses</h6>
                        <h2 class="metric-value text-success">{{ number_format($additionalStats['subjects']) }}</h2>
                        <p class="text-muted mb-0">Active Subjects</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-top border-0 border-4 border-primary metric-card">
                    <div class="card-body text-center">
                        <i class="bx bx-building fs-1 text-primary mb-3"></i>
                        <h6 class="metric-label text-uppercase">Total Classes</h6>
                        <h2 class="metric-value text-primary">{{ number_format($additionalStats['classes']) }}</h2>
                        <p class="text-muted mb-0">Active Classes</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card border-top border-0 border-4 border-warning metric-card">
                    <div class="card-body text-center">
                        <i class="bx bx-clipboard fs-1 text-warning mb-3"></i>
                        <h6 class="metric-label text-uppercase">Total Exams</h6>
                        <h2 class="metric-value text-warning">{{ number_format($additionalStats['exams']) }}</h2>
                        <p class="text-muted mb-0">Examinations</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Enrollment Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-primary metric-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h6 class="metric-label text-uppercase">Student Enrollment</h6>
                                <h2 class="metric-value text-primary">{{ number_format($enrollmentData['total']) }}</h2>
                                <p class="text-muted mb-0">Total Active Students</p>
                            </div>
                            <div class="col-md-9">
                                <div id="enrollmentChart" class="chart-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Rate Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-top border-0 border-4 border-success metric-card">
                    <div class="card-body">
                        <h6 class="metric-label text-uppercase">Attendance Rate</h6>
                        <h2 class="metric-value text-success">{{ number_format($attendanceData['rate'], 1) }}%</h2>
                        <p class="text-muted mb-3">Overall Attendance Percentage</p>
                        <div id="attendanceRateChart" class="chart-container" style="min-height: 200px;"></div>
                    </div>
                </div>
            </div>
            
            <!-- Fee Payment Status -->
            <div class="col-md-6">
                <div class="card border-top border-0 border-4 border-info metric-card">
                    <div class="card-body">
                        <h6 class="metric-label text-uppercase">Fee Payment Status</h6>
                        <h2 class="metric-value text-info">{{ number_format($feePaymentData['rate'], 1) }}%</h2>
                        <p class="text-muted mb-3">Payment Completion Rate</p>
                        <div id="feePaymentChart" class="chart-container" style="min-height: 200px;"></div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-success">Fully Paid: {{ number_format($feePaymentData['fully_paid']) }}</span>
                                <span class="badge bg-warning">Outstanding: {{ number_format($feePaymentData['outstanding']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Performance Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card border-top border-0 border-4 border-warning metric-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h6 class="metric-label text-uppercase">Academic Performance</h6>
                                <h2 class="metric-value text-warning">{{ number_format($academicPerformanceData['average'], 1) }}%</h2>
                                <p class="text-muted mb-0">Average Performance</p>
                            </div>
                            <div class="col-md-9">
                                <div id="academicPerformanceChart" class="chart-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Student-Teacher Ratio -->
            <div class="col-md-4">
                <div class="card border-top border-0 border-4 border-secondary metric-card h-100">
                    <div class="card-body d-flex flex-column justify-content-center">
                        <h6 class="metric-label text-uppercase">Student-Teacher Ratio</h6>
                        <h2 class="metric-value text-secondary">{{ $studentTeacherRatio['ratio'] }}</h2>
                        <p class="text-muted mb-0">Students: {{ number_format($studentTeacherRatio['students']) }}</p>
                        <p class="text-muted mb-0">Teachers: {{ number_format($studentTeacherRatio['teachers']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teacher Performance Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-top border-0 border-4 border-primary metric-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="metric-label text-uppercase mb-0">Teacher Performance</h6>
                            <h5 class="mb-0 text-primary">{{ number_format($teacherPerformanceData['average'], 1) }}%</h5>
                        </div>
                        <div id="teacherPerformanceChart" class="chart-container" style="min-height: 250px;"></div>
                        <div class="mt-3">
                            @foreach($teacherPerformanceData['teachers'] as $teacher)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">{{ $teacher['name'] }}</span>
                                    <span class="text-primary fw-bold">{{ $teacher['performance'] }}%</span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-primary" role="progressbar" 
                                         style="width: {{ $teacher['performance'] }}%" 
                                         aria-valuenow="{{ $teacher['performance'] }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ $teacher['performance'] }}%
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Trend -->
            <div class="col-md-6">
                <div class="card border-top border-0 border-4 border-success metric-card">
                    <div class="card-body">
                        <h6 class="metric-label text-uppercase">Attendance Trend</h6>
                        <div id="attendanceTrendChart" class="chart-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics Section -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-top border-0 border-4 border-info metric-card">
                    <div class="card-body text-center">
                        <i class="bx bx-book-open fs-1 text-info mb-3"></i>
                        <h6 class="metric-label text-uppercase">Library Statistics</h6>
                        <h3 class="text-info">{{ number_format($additionalStats['library']['total_books']) }}</h3>
                        <p class="text-muted mb-0">Total Books</p>
                        <div class="mt-2">
                            <span class="badge bg-success">Available: {{ number_format($additionalStats['library']['available_books']) }}</span>
                            <span class="badge bg-warning">Borrowed: {{ number_format($additionalStats['library']['borrowed_books']) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-top border-0 border-4 border-warning metric-card">
                    <div class="card-body text-center">
                        <i class="bx bx-task fs-1 text-warning mb-3"></i>
                        <h6 class="metric-label text-uppercase">Assignments</h6>
                        <h3 class="text-warning">{{ number_format($additionalStats['assignments']) }}</h3>
                        <p class="text-muted mb-0">Total Assignments</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-top border-0 border-4 border-primary metric-card">
                    <div class="card-body text-center">
                        <i class="bx bx-building fs-1 text-primary mb-3"></i>
                        <h6 class="metric-label text-uppercase">Classes</h6>
                        <h3 class="text-primary">{{ number_format($additionalStats['classes']) }}</h3>
                        <p class="text-muted mb-0">Active Classes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Summary -->
        <div class="row">
            <div class="col-12">
                <div class="card border-top border-0 border-4 border-success">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bx bx-money me-2 text-success"></i>
                            Fee Collection Summary
                        </h5>
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h6 class="text-muted">Total Amount</h6>
                                <h4 class="text-primary">{{ number_format($feePaymentData['total_amount'], 2) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Paid Amount</h6>
                                <h4 class="text-success">{{ number_format($feePaymentData['paid_amount'], 2) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Outstanding</h6>
                                <h4 class="text-danger">{{ number_format($feePaymentData['outstanding_amount'], 2) }}</h4>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Collection Rate</h6>
                                <h4 class="text-info">{{ number_format(($feePaymentData['paid_amount'] / max($feePaymentData['total_amount'], 1)) * 100, 1) }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/plugins/apexcharts-bundle/js/apexcharts.min.js') }}"></script>
<script>
    // Check if dark mode is enabled and apply it immediately
    const isDarkMode = localStorage.getItem('dashboardDarkMode') === 'true';
    if (isDarkMode) {
        document.body.classList.add('dark-mode');
    }
    const gridColor = isDarkMode ? '#3a3f4b' : '#e7e7e7';
    const textColor = isDarkMode ? '#ffffff' : '#373d3f';
    const chartTheme = isDarkMode ? 'dark' : 'light';
    $(document).ready(function() {
        // Enrollment Chart (Bar Chart)
        var enrollmentOptions = {
            series: [{
                name: 'Students',
                data: @json($enrollmentData['by_class']->pluck('count'))
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    horizontal: false,
                    columnWidth: '60%',
                }
            },
            dataLabels: {
                enabled: true
            },
            colors: ['#0d6efd'],
            theme: {
                mode: chartTheme
            },
            grid: {
                borderColor: gridColor,
            },
            xaxis: {
                categories: @json($enrollmentData['by_class']->pluck('class_name')),
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
        };
        var enrollmentChart = new ApexCharts(document.querySelector("#enrollmentChart"), enrollmentOptions);
        enrollmentChart.render();

        // Attendance Rate Chart (Line Chart)
        var attendanceRateOptions = {
            series: [{
                name: 'Attendance Rate',
                data: @json($attendanceData['monthly_trend']->pluck('rate'))
            }],
            chart: {
                type: 'line',
                height: 200,
                toolbar: { show: false },
                sparkline: { enabled: false }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#198754'],
            theme: {
                mode: chartTheme
            },
            grid: {
                borderColor: gridColor,
            },
            markers: {
                size: 4
            },
            xaxis: {
                categories: @json($attendanceData['monthly_trend']->pluck('month')),
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            }
        };
        var attendanceRateChart = new ApexCharts(document.querySelector("#attendanceRateChart"), attendanceRateOptions);
        attendanceRateChart.render();

        // Fee Payment Chart (Donut Chart)
        var feePaymentOptions = {
            series: [{{ $feePaymentData['fully_paid'] }}, {{ $feePaymentData['outstanding'] }}],
            chart: {
                type: 'donut',
                height: 200
            },
            labels: ['Fully Paid', 'Outstanding'],
            colors: ['#0dcaf0', '#ffc107'],
            theme: {
                mode: chartTheme
            },
            legend: {
                position: 'bottom',
                labels: {
                    colors: textColor
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val.toFixed(1) + "%"
                }
            }
        };
        var feePaymentChart = new ApexCharts(document.querySelector("#feePaymentChart"), feePaymentOptions);
        feePaymentChart.render();

        // Academic Performance Chart (Bar Chart)
        var academicOptions = {
            series: [{
                name: 'Students',
                data: [
                    {{ $academicPerformanceData['grade_counts']['A'] }},
                    {{ $academicPerformanceData['grade_counts']['B'] }},
                    {{ $academicPerformanceData['grade_counts']['C'] }},
                    {{ $academicPerformanceData['grade_counts']['D'] }},
                    {{ $academicPerformanceData['grade_counts']['F'] }}
                ]
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    horizontal: false,
                    columnWidth: '60%',
                }
            },
            dataLabels: {
                enabled: true
            },
            colors: ['#ffc107'],
            theme: {
                mode: chartTheme
            },
            grid: {
                borderColor: gridColor,
            },
            xaxis: {
                categories: ['A', 'B', 'C', 'D', 'F'],
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
        };
        var academicChart = new ApexCharts(document.querySelector("#academicPerformanceChart"), academicOptions);
        academicChart.render();

        // Attendance Trend Chart (Line Chart)
        var attendanceTrendOptions = {
            series: [{
                name: 'Attendance Rate',
                data: @json($attendanceTrendData->pluck('rate'))
            }],
            chart: {
                type: 'line',
                height: 300,
                toolbar: { show: false }
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            colors: ['#198754'],
            theme: {
                mode: chartTheme
            },
            markers: {
                size: 5
            },
            grid: {
                borderColor: gridColor,
            },
            xaxis: {
                categories: @json($attendanceTrendData->pluck('month')),
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textColor
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: 'vertical',
                    shadeIntensity: 0.3,
                    gradientToColors: ['#198754'],
                    inverseColors: false,
                    opacityFrom: 0.7,
                    opacityTo: 0.3,
                }
            }
        };
        var attendanceTrendChart = new ApexCharts(document.querySelector("#attendanceTrendChart"), attendanceTrendOptions);
        attendanceTrendChart.render();

        // Store chart instances for dark mode updates
        window.dashboardCharts = {
            enrollment: enrollmentChart,
            attendance: attendanceChart,
            feePayment: feePaymentChart,
            academic: academicChart,
            attendanceTrend: attendanceTrendChart
        };
    });

    // Dark Mode Toggle Functionality
    (function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const darkModeText = document.getElementById('darkModeText');
        
        // Check localStorage for saved preference
        const isDarkMode = localStorage.getItem('dashboardDarkMode') === 'true';
        
        // Apply initial theme
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            updateDarkModeIcon(true);
        }
        
        // Toggle dark mode
        darkModeToggle.addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('dashboardDarkMode', isDark);
            updateDarkModeIcon(isDark);
            updateChartsForDarkMode(isDark);
        });
        
        function updateDarkModeIcon(isDark) {
            if (isDark) {
                darkModeIcon.classList.remove('bx-moon');
                darkModeIcon.classList.add('bx-sun');
                darkModeText.textContent = 'Light Mode';
            } else {
                darkModeIcon.classList.remove('bx-sun');
                darkModeIcon.classList.add('bx-moon');
                darkModeText.textContent = 'Dark Mode';
            }
        }
        
        function updateChartsForDarkMode(isDark) {
            if (!window.dashboardCharts) return;
            
            const gridColor = isDark ? '#3a3f4b' : '#e7e7e7';
            const textColor = isDark ? '#ffffff' : '#373d3f';
            
            // Update all charts with new colors
            Object.entries(window.dashboardCharts).forEach(([key, chart]) => {
                if (chart && chart.updateOptions) {
                    // Get the original config to preserve categories for Attendance Trend
                    let updateConfig = {
                        theme: {
                            mode: isDark ? 'dark' : 'light'
                        },
                        grid: {
                            borderColor: gridColor
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    colors: textColor // Use string for single color
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: textColor // Use string for single color
                                }
                            }
                        },
                        legend: {
                            labels: {
                                colors: textColor
                            }
                        }
                    };
                    
                    // For Attendance Trend, preserve categories
                    if (key === 'attendanceTrend' && chart.w && chart.w.config && chart.w.config.xaxis) {
                        updateConfig.xaxis.categories = chart.w.config.xaxis.categories;
                    }
                    
                    // Update with redraw - signature: updateOptions(newOptions, redraw, animate, updateSyncedCharts)
                    // redraw=true, animate=false, updateSyncedCharts=true
                    chart.updateOptions(updateConfig, true, false, true);
                }
            });
        }
    })();
</script>
@endpush

