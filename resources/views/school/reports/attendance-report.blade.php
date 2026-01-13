@extends('layouts.main')

@section('title', 'Attendance Reports and Analysis')

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

    .chart-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s ease-in-out;
    }

    .chart-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    }

    .chart-container {
        position: relative;
        height: 350px;
        min-height: 350px;
        margin-bottom: 20px;
        width: 100%;
    }
    
    .chart-container canvas {
        max-height: 100% !important;
        max-width: 100% !important;
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

    .card-header-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        border: none;
        padding: 1.25rem;
    }

    .card-header-modern h6 {
        margin: 0;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Attendance', 'url' => route('school.attendance.index'), 'icon' => 'bx bx-calendar-check'],
            ['label' => 'Attendance Reports and Analysis', 'url' => '#', 'icon' => 'bx bx-bar-chart-alt-2']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">ATTENDANCE REPORTS AND ANALYSIS</h6>
                <p class="text-muted mb-0">Comprehensive attendance analytics and reporting dashboard</p>
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
                <form id="attendanceFilterForm" class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label for="class_id" class="form-label fw-semibold">Class</label>
                        <select class="form-select select2" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="stream_id" class="form-label fw-semibold">Stream</label>
                        <select class="form-select select2" id="stream_id" name="stream_id">
                            <option value="">All Streams</option>
                            @foreach($streams as $stream)
                                <option value="{{ $stream->id }}">{{ $stream->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="academic_year_id" class="form-label fw-semibold">Academic Year</label>
                        <select class="form-select select2" id="academic_year_id" name="academic_year_id">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ (isset($currentAcademicYear) && $currentAcademicYear && $currentAcademicYear->id == $year->id) ? 'selected' : '' }}>{{ $year->year_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label class="form-label fw-semibold">Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="{{ date('Y-m-01') }}">
                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="{{ date('Y-m-t') }}">
                        </div>
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
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('summary', 'pdf')">
                                        <i class="bx bx-file me-2"></i>PDF Report</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('summary', 'excel')">
                                        <i class="bx bx-spreadsheet me-2"></i>Excel Report</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportReport('summary', 'csv')">
                                        <i class="bx bx-data me-2"></i>CSV Report</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <div class="stats-icon text-primary mb-3">
                            <i class="bx bx-calendar-alt"></i>
                        </div>
                        <div class="stat-number text-primary">{{ number_format($summaryStats['total_sessions']) }}</div>
                        <div class="stat-label">Total Sessions</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <div class="stats-icon text-success mb-3">
                            <i class="bx bx-trending-up"></i>
                        </div>
                        <div class="stat-number text-success">{{ $summaryStats['overall_attendance_rate'] }}%</div>
                        <div class="stat-label">Attendance Rate</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <div class="stats-icon text-info mb-3">
                            <i class="bx bx-check-circle"></i>
                        </div>
                        <div class="stat-number text-info">{{ number_format($summaryStats['total_present']) }}</div>
                        <div class="stat-label">Total Present</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <div class="stats-icon text-warning mb-3">
                            <i class="bx bx-group"></i>
                        </div>
                        <div class="stat-number text-warning">{{ number_format($summaryStats['total_students']) }}</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics Row -->
        <div class="row mb-4">
            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <div class="stats-icon text-danger mb-3">
                            <i class="bx bx-x-circle"></i>
                        </div>
                        <div class="stat-number text-danger">{{ number_format($summaryStats['total_absent']) }}</div>
                        <div class="stat-label">Total Absent</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <div class="stats-icon text-secondary mb-3">
                            <i class="bx bx-time"></i>
                        </div>
                        <div class="stat-number text-secondary">{{ number_format($summaryStats['total_late']) }}</div>
                        <div class="stat-label">Late Arrivals</div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <div class="stats-icon text-dark mb-3">
                            <i class="bx bx-medkit"></i>
                        </div>
                        <div class="stat-number text-dark">{{ number_format($summaryStats['total_sick']) }}</div>
                        <div class="stat-label">Sick Leave</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="card table-card">
            <div class="card-header card-header-modern">
                <h6><i class="bx bx-table me-2"></i>Detailed Attendance Sessions</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="attendanceTable" width="100%" cellspacing="0">
                        <thead class="table-light">
                            <tr>
                                <th><i class="bx bx-calendar me-1"></i>Date</th>
                                <th><i class="bx bx-school me-1"></i>Class</th>
                                <th><i class="bx bx-branch me-1"></i>Stream</th>
                                <th><i class="bx bx-calendar-star me-1"></i>Academic Year</th>
                                <th><i class="bx bx-group me-1"></i>Total Students</th>
                                <th><i class="bx bx-check-circle me-1"></i>Present</th>
                                <th><i class="bx bx-x-circle me-1"></i>Absent</th>
                                <th><i class="bx bx-time me-1"></i>Late</th>
                                <th><i class="bx bx-medkit me-1"></i>Sick</th>
                                <th><i class="bx bx-percentage me-1"></i>Attendance Rate</th>
                                <th><i class="bx bx-cog me-1"></i>Actions</th>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

$(document).ready(function() {
    console.log('Document ready - initializing attendance report');
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Initialize DataTable
    const attendanceTable = $('#attendanceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.reports.attendance.summary-data") }}',
            data: function(d) {
                d.class_id = $('#class_id').val();
                d.stream_id = $('#stream_id').val();
                d.academic_year_id = $('#academic_year_id').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            }
        },
        columns: [
            { data: 'session_date_formatted', name: 'session_date' },
            { data: 'class_name', name: 'class.name' },
            { data: 'stream_name', name: 'stream.name' },
            { data: 'academic_year_name', name: 'academicYear.year_name' },
            { data: 'total_students', name: 'total_students' },
            { data: 'present', name: 'present' },
            { data: 'absent', name: 'absent' },
            { data: 'late', name: 'late' },
            { data: 'sick', name: 'sick' },
            { data: 'attendance_rate', name: 'attendance_rate' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-info btn-modern" onclick="viewSessionDetails(${row.id})">
                            <i class="bx bx-show me-1"></i>View
                        </button>
                    `;
                }
            }
        ],
        responsive: true,
        pageLength: 25,
        order: [[0, 'desc']],
        language: {
            processing: '<div class="text-center"><i class="bx bx-loader-alt bx-spin bx-lg"></i> Loading...</div>'
        }
    });

    // Apply filters
    $('#applyFilters').on('click', function() {
        attendanceTable.ajax.reload();
        loadSummaryStats();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#attendanceFilterForm')[0].reset();
        $('.select2').trigger('change');
        attendanceTable.ajax.reload();
        loadSummaryStats();
    });
});

function refreshData() {
    // Reload table
    $('#attendanceTable').DataTable().ajax.reload();

    // Reload summary stats
    loadSummaryStats();

    // Show success message
    showToast('Data refreshed successfully', 'success');
}

function loadSummaryStats() {
    // Reload the page to get updated summary stats
    // This is a simple approach - in production, you might want to make an AJAX call
    location.reload();
}


function viewSessionDetails(sessionId) {
    // Show loading modal
    const modalHtml = `
        <div class="modal fade" id="sessionDetailsModal" tabindex="-1" aria-labelledby="sessionDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="sessionDetailsModalLabel">
                            <i class="bx bx-show me-2"></i>Session Details
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <i class="bx bx-loader-alt bx-spin bx-lg text-primary"></i>
                            <p class="mt-2">Loading session details...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Remove existing modal if present
    $('#sessionDetailsModal').remove();
    $('body').append(modalHtml);

    const modal = new bootstrap.Modal(document.getElementById('sessionDetailsModal'));
    modal.show();

    // Fetch session students data
    $.get('{{ route("school.reports.attendance.session.students", ":sessionId") }}'.replace(':sessionId', sessionId))
        .done(function(data) {
            if (data.success) {
                renderSessionDetailsModal(data);
            } else {
                showToast('Error loading session details', 'error');
            }
        })
        .fail(function(xhr) {
            console.error('Error fetching session details:', xhr);
            showToast('Failed to load session details', 'error');
        });
}

function renderSessionDetailsModal(data) {
    const { session, students, summary } = data;

    const statusColors = {
        'present': 'success',
        'absent': 'danger',
        'late': 'warning',
        'sick': 'info'
    };

    const statusIcons = {
        'present': 'bx-check-circle',
        'absent': 'bx-x-circle',
        'late': 'bx-time',
        'sick': 'bx-plus-medical'
    };

    const modalBody = `
        <!-- Session Info -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-primary">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="bx bx-calendar me-2"></i>
                            Session Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Date:</strong> ${session.session_date}
                            </div>
                            <div class="col-md-3">
                                <strong>Class:</strong> ${session.class_name}
                            </div>
                            <div class="col-md-3">
                                <strong>Stream:</strong> ${session.stream_name}
                            </div>
                            <div class="col-md-3">
                                <strong>Academic Year:</strong> ${session.academic_year}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-bar-chart me-2"></i>
                            Attendance Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-2">
                                <div class="p-2 bg-light rounded">
                                    <h4 class="text-primary mb-1">${summary.total_students}</h4>
                                    <small class="text-muted">Total Students</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-2 bg-success bg-opacity-10 rounded">
                                    <h4 class="text-success mb-1">${summary.present}</h4>
                                    <small class="text-muted">Present</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-2 bg-danger bg-opacity-10 rounded">
                                    <h4 class="text-danger mb-1">${summary.absent}</h4>
                                    <small class="text-muted">Absent</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-2 bg-warning bg-opacity-10 rounded">
                                    <h4 class="text-warning mb-1">${summary.late}</h4>
                                    <small class="text-muted">Late</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-2 bg-info bg-opacity-10 rounded">
                                    <h4 class="text-info mb-1">${summary.sick}</h4>
                                    <small class="text-muted">Sick</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="p-2 bg-primary bg-opacity-10 rounded">
                                    <h4 class="text-primary mb-1">${summary.attendance_rate}%</h4>
                                    <small class="text-muted">Attendance Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students List -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="bx bx-group me-2"></i>
                            Student Attendance Details
                        </h6>
                        <span class="badge bg-primary">${students.length} Students</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Admission No.</th>
                                        <th>Student Name</th>
                                        <th>Status</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${students.map(student => `
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">${student.admission_number}</span>
                                            </td>
                                            <td>
                                                <strong>${student.full_name}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-${statusColors[student.status] || 'secondary'} ${(student.status === 'present' || student.status === 'absent') ? 'text-white' : ''}">
                                                    <i class="bx ${statusIcons[student.status] || 'bx-circle'} me-1"></i>
                                                    ${student.formatted_status}
                                                </span>
                                            </td>
                                            <td>
                                                ${student.time_in || '<span class="text-muted">-</span>'}
                                            </td>
                                            <td>
                                                ${student.time_out || '<span class="text-muted">-</span>'}
                                            </td>
                                            <td>
                                                ${student.notes || '<span class="text-muted">-</span>'}
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#sessionDetailsModal .modal-body').html(modalBody);
}

function exportReport(type, format) {
    const params = new URLSearchParams({
        type: type,
        format: format,
        class_id: $('#class_id').val(),
        stream_id: $('#stream_id').val(),
        academic_year_id: $('#academic_year_id').val(),
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val()
    });

    window.open('{{ route("school.reports.attendance.export") }}?' + params.toString(), '_blank');
}

function showToast(message, type = 'info') {
    // Simple toast implementation - you can replace with a proper toast library
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    // Create toast container if it doesn't exist
    if (!$('#toastContainer').length) {
        $('body').append('<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>');
    }

    const $toast = $(toastHtml);
    $('#toastContainer').append($toast);

    const toast = new bootstrap.Toast($toast[0]);
    toast.show();

    // Remove toast after it's hidden
    $toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>
@endpush