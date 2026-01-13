@extends('layouts.main')

@section('title', 'Timetables')

@section('content')
<div class="page-content" style="margin-top: 70px; margin-left: 235px; margin-right: 20px;">
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="row mb-3">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 me-2">
                        <i class="bx bx-home-alt me-1"></i> Dashboard
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.index') }}" class="btn btn-light btn-sm rounded-pill px-3 mx-2">
                        <i class="bx bx-book-reader me-1"></i> College Management
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <span class="btn btn-primary btn-sm rounded-pill px-3 ms-2">
                        <i class="bx bx-calendar-alt me-1"></i> Timetables
                    </span>
                </nav>
            </div>
        </div>

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="bx bx-calendar-alt me-2"></i>Program Timetables
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="#">College</a></li>
                            <li class="breadcrumb-item active">Timetables</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Total Timetables</p>
                                <h4 class="fs-22 fw-semibold mb-0" id="totalCount">-</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                    <i class="bx bx-calendar-alt text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Published</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-success" id="publishedCount">-</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                    <i class="bx bx-check-circle text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Draft</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-warning" id="draftCount">-</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle rounded fs-3">
                                    <i class="bx bx-edit text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Programs</p>
                                <h4 class="fs-22 fw-semibold mb-0 text-info">{{ $programs->count() }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                    <i class="bx bx-book-open text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-list-ul me-2"></i>All Timetables
                                </h5>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('college.teacher-timetables.index') }}" class="btn btn-outline-info me-2">
                                    <i class="bx bx-user me-1"></i> Teacher Timetables
                                </a>
                                <a href="{{ route('college.venues.index') }}" class="btn btn-outline-secondary me-2">
                                    <i class="bx bx-building me-1"></i> Manage Venues
                                </a>
                                <a href="{{ route('college.timetables.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Create Timetable
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Program</label>
                                <select class="form-select" id="filterProgram">
                                    <option value="">All Programs</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Academic Year</label>
                                <select class="form-select" id="filterAcademicYear">
                                    <option value="">All Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}">{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Semester</label>
                                <select class="form-select" id="filterSemester">
                                    <option value="">All Semesters</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="filterStatus">
                                    <option value="">All Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-secondary w-100" id="resetFilters">
                                    <i class="bx bx-reset me-1"></i> Reset
                                </button>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="timetablesTable" class="table table-hover table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Name</th>
                                        <th>Program</th>
                                        <th>Academic Year</th>
                                        <th>Semester</th>
                                        <th>Year</th>
                                        <th>Slots</th>
                                        <th>Hours/Week</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Modal -->
<div class="modal fade" id="duplicateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-copy me-2"></i>Duplicate Timetable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="duplicateForm">
                <div class="modal-body">
                    <input type="hidden" id="duplicateTimetableId">
                    <div class="mb-3">
                        <label class="form-label">New Academic Year <span class="text-danger">*</span></label>
                        <select class="form-select" id="duplicateAcademicYear" required>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Semester <span class="text-danger">*</span></label>
                        <select class="form-select" id="duplicateSemester" required>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}">{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Name (Optional)</label>
                        <input type="text" class="form-control" id="duplicateName" placeholder="Leave blank to auto-generate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-copy me-1"></i> Duplicate
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bx bx-trash me-2"></i>Delete Timetable</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this timetable? This action cannot be undone.</p>
                <input type="hidden" id="deleteTimetableId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bx bx-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<style>
    /* Tooltip Styles */
    .timetable-tooltip {
        position: absolute;
        z-index: 9999;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        min-width: 320px;
        max-width: 400px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        display: none;
        animation: tooltipFadeIn 0.3s ease;
        pointer-events: none;
    }
    @keyframes tooltipFadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .timetable-tooltip::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 30px;
        border-width: 0 10px 10px 10px;
        border-style: solid;
        border-color: transparent transparent #1e3c72 transparent;
    }
    .tooltip-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    .tooltip-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .tooltip-subtitle {
        font-size: 12px;
        opacity: 0.8;
    }
    .tooltip-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .tooltip-status.draft { background: #ffc107; color: #000; }
    .tooltip-status.published { background: #28a745; }
    .tooltip-status.archived { background: #6c757d; }
    .tooltip-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .tooltip-item {
        display: flex;
        flex-direction: column;
    }
    .tooltip-item.full-width {
        grid-column: 1 / -1;
    }
    .tooltip-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.7;
        margin-bottom: 3px;
    }
    .tooltip-value {
        font-size: 14px;
        font-weight: 600;
    }
    .tooltip-value i {
        margin-right: 6px;
        opacity: 0.8;
    }
    .tooltip-stats {
        display: flex;
        gap: 15px;
        margin-top: 15px;
        padding-top: 12px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }
    .tooltip-stat {
        flex: 1;
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        padding: 10px;
        border-radius: 8px;
    }
    .tooltip-stat-value {
        font-size: 20px;
        font-weight: 700;
    }
    .tooltip-stat-label {
        font-size: 10px;
        text-transform: uppercase;
        opacity: 0.7;
    }
    .tooltip-footer {
        margin-top: 15px;
        padding-top: 12px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 11px;
        opacity: 0.7;
        display: flex;
        justify-content: space-between;
    }
    
    /* Make table rows interactive */
    #timetablesTable tbody tr {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    #timetablesTable tbody tr:hover {
        background: linear-gradient(135deg, rgba(30, 60, 114, 0.05) 0%, rgba(42, 82, 152, 0.1) 100%) !important;
        transform: scale(1.005);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#timetablesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("college.timetables.data") }}',
            data: function(d) {
                d.program_id = $('#filterProgram').val();
                d.academic_year_id = $('#filterAcademicYear').val();
                d.semester_id = $('#filterSemester').val();
                d.status = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'name', name: 'name' },
            { data: 'program_name', name: 'program.name' },
            { data: 'academic_year_name', name: 'academicYear.name' },
            { data: 'semester_name', name: 'semester.name' },
            { data: 'year_of_study', name: 'year_of_study' },
            { data: 'slots_count', name: 'slots_count', searchable: false },
            { data: 'total_hours', name: 'total_hours', searchable: false, orderable: false },
            { data: 'status_badge', name: 'status', orderable: true },
            { data: 'created_by_name', name: 'createdBy.name' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        createdRow: function(row, data, dataIndex) {
            // Store tooltip data on each row
            $(row).attr('data-tooltip-info', JSON.stringify(data));
        },
        drawCallback: function() {
            updateStats();
            initTooltips();
        }
    });

    // Filter change handlers
    $('#filterProgram, #filterAcademicYear, #filterSemester, #filterStatus').on('change', function() {
        table.draw();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filterProgram, #filterAcademicYear, #filterSemester, #filterStatus').val('');
        table.draw();
    });

    // Publish timetable
    $(document).on('click', '.publish-btn', function() {
        var id = $(this).data('id');
        if (confirm('Are you sure you want to publish this timetable?')) {
            $.ajax({
                url: '/college/timetables/' + id + '/publish',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        table.draw();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'An error occurred');
                }
            });
        }
    });

    // Duplicate timetable
    $(document).on('click', '.duplicate-btn', function() {
        $('#duplicateTimetableId').val($(this).data('id'));
        $('#duplicateModal').modal('show');
    });

    $('#duplicateForm').on('submit', function(e) {
        e.preventDefault();
        var id = $('#duplicateTimetableId').val();
        $.ajax({
            url: '/college/timetables/' + id + '/duplicate',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                academic_year_id: $('#duplicateAcademicYear').val(),
                semester_id: $('#duplicateSemester').val(),
                name: $('#duplicateName').val()
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#duplicateModal').modal('hide');
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        table.draw();
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'An error occurred');
            }
        });
    });

    // Delete timetable
    $(document).on('click', '.delete-btn', function() {
        $('#deleteTimetableId').val($(this).data('id'));
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        var id = $('#deleteTimetableId').val();
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Deleting...');
        
        $.ajax({
            url: '{{ url("college/timetables") }}/' + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#deleteModal').modal('hide');
                    table.draw();
                } else {
                    toastr.error(response.message || 'Failed to delete timetable');
                }
                $btn.prop('disabled', false).html('<i class="bx bx-trash me-1"></i> Delete');
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'An error occurred while deleting');
                $btn.prop('disabled', false).html('<i class="bx bx-trash me-1"></i> Delete');
            }
        });
    });

    // Update statistics
    function updateStats() {
        // This would ideally be fetched from the server
        var info = table.page.info();
        $('#totalCount').text(info.recordsTotal);
    }
    
    // Tooltip functionality
    var tooltipTimeout;
    var $tooltip = null;
    
    function initTooltips() {
        // Create tooltip element if not exists
        if (!$tooltip) {
            $tooltip = $('<div class="timetable-tooltip"></div>');
            $('body').append($tooltip);
        }
        
        // Hover events for desktop
        $('#timetablesTable tbody tr').off('mouseenter mouseleave').on('mouseenter', function(e) {
            var $row = $(this);
            clearTimeout(tooltipTimeout);
            tooltipTimeout = setTimeout(function() {
                showTooltip($row, e);
            }, 300); // Small delay to prevent flicker
        }).on('mouseleave', function() {
            clearTimeout(tooltipTimeout);
            hideTooltip();
        });
        
        // Touch events for mobile
        $('#timetablesTable tbody tr').off('touchstart touchend').on('touchstart', function(e) {
            var $row = $(this);
            tooltipTimeout = setTimeout(function() {
                showTooltip($row, e.originalEvent.touches[0]);
            }, 500); // Long press
        }).on('touchend', function() {
            clearTimeout(tooltipTimeout);
            setTimeout(hideTooltip, 2000); // Hide after 2 seconds on touch
        });
    }
    
    function showTooltip($row, e) {
        var dataStr = $row.attr('data-tooltip-info');
        if (!dataStr) return;
        
        try {
            var data = JSON.parse(dataStr);
            
            var statusClass = (data.status || 'draft').toLowerCase();
            var statusText = statusClass.charAt(0).toUpperCase() + statusClass.slice(1);
            
            var html = `
                <div class="tooltip-header">
                    <div>
                        <div class="tooltip-title">${data.name || 'Timetable'}</div>
                        <div class="tooltip-subtitle">${data.program_name || 'Program'}</div>
                    </div>
                    <span class="tooltip-status ${statusClass}">${statusText}</span>
                </div>
                <div class="tooltip-grid">
                    <div class="tooltip-item">
                        <span class="tooltip-label">Academic Year</span>
                        <span class="tooltip-value"><i class="bx bx-calendar"></i>${data.academic_year_name || 'N/A'}</span>
                    </div>
                    <div class="tooltip-item">
                        <span class="tooltip-label">Semester</span>
                        <span class="tooltip-value"><i class="bx bx-book-open"></i>${data.semester_name || 'N/A'}</span>
                    </div>
                    <div class="tooltip-item">
                        <span class="tooltip-label">Year of Study</span>
                        <span class="tooltip-value"><i class="bx bx-graduation"></i>Year ${data.year_of_study || '1'}</span>
                    </div>
                    <div class="tooltip-item">
                        <span class="tooltip-label">Created By</span>
                        <span class="tooltip-value"><i class="bx bx-user"></i>${data.created_by_name || 'N/A'}</span>
                    </div>
                </div>
                <div class="tooltip-stats">
                    <div class="tooltip-stat">
                        <div class="tooltip-stat-value">${data.slots_count || 0}</div>
                        <div class="tooltip-stat-label">Time Slots</div>
                    </div>
                    <div class="tooltip-stat">
                        <div class="tooltip-stat-value">${data.total_hours || '0 hrs'}</div>
                        <div class="tooltip-stat-label">Per Week</div>
                    </div>
                </div>
                <div class="tooltip-footer">
                    <span><i class="bx bx-info-circle me-1"></i>Hover for details</span>
                    <span><i class="bx bx-pointer me-1"></i>Click to view</span>
                </div>
            `;
            
            $tooltip.html(html);
            
            // Position tooltip
            var rowOffset = $row.offset();
            var tooltipWidth = $tooltip.outerWidth();
            var windowWidth = $(window).width();
            
            var left = e.pageX - 30;
            var top = rowOffset.top + $row.outerHeight() + 10;
            
            // Adjust if tooltip would go off-screen
            if (left + tooltipWidth > windowWidth - 20) {
                left = windowWidth - tooltipWidth - 20;
            }
            if (left < 20) {
                left = 20;
            }
            
            $tooltip.css({
                left: left + 'px',
                top: top + 'px'
            }).fadeIn(200);
            
        } catch (err) {
            console.error('Error parsing tooltip data:', err);
        }
    }
    
    function hideTooltip() {
        if ($tooltip) {
            $tooltip.fadeOut(150);
        }
    }
    
    // Hide tooltip when clicking elsewhere
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#timetablesTable tbody tr').length) {
            hideTooltip();
        }
    });
});
</script>
@endpush
