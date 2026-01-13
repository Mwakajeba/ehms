@extends('layouts.main')

@section('content')
<style>
    .schedule-container {
        margin-left: 250px;
        margin-right: 20px;
        padding: 20px;
        max-width: calc(100vw - 280px);
    }
    .stat-card {
        transition: all 0.3s ease;
        border-radius: 12px;
    }
    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.5rem;
    }
    .filter-card .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 0.3rem;
        color: #555;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        font-size: 0.85rem;
        border-radius: 8px;
    }
    .schedule-table th {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: #f8f9fa;
        border: none;
    }
    .schedule-table td {
        font-size: 0.875rem;
        vertical-align: middle;
        border-color: #eee;
    }
    .schedule-table tr:hover {
        background: #f8f9fa;
    }
    .exam-date-badge {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        padding: 8px 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        color: white;
        min-width: 60px;
    }
    .exam-date-badge .day {
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1;
    }
    .exam-date-badge .month {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .time-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: #e3f2fd;
        color: #1976d2;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .venue-info {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #666;
        font-size: 0.85rem;
    }
    .venue-info i {
        color: #999;
    }
    
    /* Breadcrumb Styles */
    .breadcrumb-modern-icons {
        display: flex;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .breadcrumb-item {
        display: flex;
        align-items: center;
    }
    .breadcrumb-item:not(:last-child) {
        margin-right: 0.5rem;
    }
    .breadcrumb-content {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .breadcrumb-icon {
        font-size: 1rem;
        opacity: 0.7;
    }
    .breadcrumb-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #6c757d;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.5);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .breadcrumb-link:hover {
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
    }
    .breadcrumb-item-current .breadcrumb-content {
        padding: 0.5rem 0.75rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        color: #495057;
        font-weight: 600;
    }
    .breadcrumb-item-current .breadcrumb-icon {
        opacity: 1;
        color: #0d6efd;
    }
    .breadcrumb-separator {
        color: #adb5bd;
        margin-left: 0.5rem;
        font-size: 0.75rem;
    }
    .action-dropdown .dropdown-menu {
        min-width: 160px;
        border: none;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        border-radius: 10px;
    }
    .action-dropdown .dropdown-item {
        padding: 8px 16px;
        font-size: 0.85rem;
    }
    .action-dropdown .dropdown-item:hover {
        background: #f0f7ff;
    }
    .action-dropdown .dropdown-item i {
        width: 20px;
    }
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }
    .empty-state i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }

    /* Delete Confirmation Modal */
    .delete-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .delete-modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .delete-modal {
        background: white;
        border-radius: 24px;
        width: 100%;
        max-width: 420px;
        margin: 20px;
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
        transform: scale(0.8) translateY(-20px);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .delete-modal-overlay.active .delete-modal {
        transform: scale(1) translateY(0);
    }

    .delete-modal-header {
        background: linear-gradient(135deg, #ef4444 0%, #f87171 50%, #fca5a5 100%);
        padding: 30px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .delete-modal-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -30%;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .delete-modal-icon {
        width: 80px;
        height: 80px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        position: relative;
        z-index: 1;
    }

    .delete-modal-icon i {
        font-size: 40px;
        color: white;
        animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(-10deg); }
        75% { transform: rotate(10deg); }
    }

    .delete-modal-header h3 {
        color: white;
        font-size: 22px;
        font-weight: 700;
        margin: 0;
        position: relative;
        z-index: 1;
    }

    .delete-modal-body {
        padding: 30px;
        text-align: center;
    }

    .delete-modal-body p {
        color: #64748b;
        font-size: 15px;
        line-height: 1.7;
        margin: 0 0 10px;
    }

    .delete-modal-body .exam-name {
        color: #1e293b;
        font-weight: 700;
        font-size: 16px;
        padding: 12px 20px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 10px;
        margin: 16px 0;
        border: 1px solid #fca5a5;
    }

    .delete-modal-body .warning-text {
        color: #dc2626;
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-top: 16px;
    }

    .delete-modal-body .warning-text i {
        font-size: 18px;
    }

    .delete-modal-footer {
        padding: 0 30px 30px;
        display: flex;
        gap: 12px;
    }

    .modal-btn {
        flex: 1;
        padding: 16px 24px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .modal-btn.cancel {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        color: #475569;
        border: 2px solid #cbd5e1;
    }

    .modal-btn.cancel:hover {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        transform: translateY(-2px);
    }

    .modal-btn.confirm-delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    }

    .modal-btn.confirm-delete:hover {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
    }

    .modal-btn i {
        font-size: 18px;
    }

    @media (max-width: 480px) {
        .delete-modal-footer {
            flex-direction: column;
        }
    }
</style>

<div class="schedule-container">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4" style="margin-top: 100px;">
        <ol class="breadcrumb-modern-icons">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="breadcrumb-link">
                    <div class="breadcrumb-content">
                        <i class="breadcrumb-icon bx bx-home-alt"></i>
                        <span>Dashboard</span>
                    </div>
                </a>
                <span class="breadcrumb-separator"><i class="bx bx-chevron-right"></i></span>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('college.index') }}" class="breadcrumb-link">
                    <div class="breadcrumb-content">
                        <i class="breadcrumb-icon bx bx-building"></i>
                        <span>College</span>
                    </div>
                </a>
                <span class="breadcrumb-separator"><i class="bx bx-chevron-right"></i></span>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('college.exams-management.dashboard') }}" class="breadcrumb-link">
                    <div class="breadcrumb-content">
                        <i class="breadcrumb-icon bx bx-book-reader"></i>
                        <span>Exams & Academics</span>
                    </div>
                </a>
                <span class="breadcrumb-separator"><i class="bx bx-chevron-right"></i></span>
            </li>
            <li class="breadcrumb-item breadcrumb-item-current" aria-current="page">
                <div class="breadcrumb-content">
                    <i class="breadcrumb-icon bx bx-calendar"></i>
                    <span>Exam Schedules</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">
                <i class="bx bx-calendar text-success me-2"></i>Exam Schedules
            </h4>
            <p class="text-muted mb-0 small">Manage examination timetables and schedules</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('college.exam-schedules.master-timetable') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-table me-1"></i> Master Timetable
            </a>
            <a href="{{ route('college.exam-schedules.calendar') }}" class="btn btn-outline-info btn-sm">
                <i class="bx bx-calendar-alt me-1"></i> Calendar View
            </a>
            <a href="{{ route('college.exam-schedules.create') }}" class="btn btn-success btn-sm">
                <i class="bx bx-plus me-1"></i> Add Schedule
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3 col-xl">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10">
                            <i class="bx bx-calendar text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">Total Schedules</small>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['total']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-xl">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10">
                            <i class="bx bx-time-five text-info"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">Upcoming</small>
                            <h4 class="mb-0 fw-bold text-info">{{ number_format($stats['upcoming']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-xl">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10">
                            <i class="bx bx-calendar-event text-warning"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">Today</small>
                            <h4 class="mb-0 fw-bold text-warning">{{ number_format($stats['today']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-xl">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10">
                            <i class="bx bx-check-circle text-success"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">Completed</small>
                            <h4 class="mb-0 fw-bold text-success">{{ number_format($stats['completed']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3 col-xl">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-secondary bg-opacity-10">
                            <i class="bx bx-calendar-week text-secondary"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">This Week</small>
                            <h4 class="mb-0 fw-bold">{{ number_format($stats['this_week']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4 filter-card">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('college.exam-schedules.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Exam name, course..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select form-select-sm">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" class="form-select form-select-sm">
                            <option value="">All Semesters</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>{{ $semester->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select form-select-sm">
                            <option value="">All Programs</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            @foreach(\App\Models\College\ExamSchedule::STATUSES as $key => $value)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                <i class="bx bx-search"></i> Filter
                            </button>
                            <a href="{{ route('college.exam-schedules.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-reset"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($schedules->count() > 0)
            <div class="table-responsive">
                <table class="table schedule-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 bg-primary text-white">Date</th>
                            <th class="bg-primary text-white">Exam Details</th>
                            <th class="bg-primary text-white">Course</th>
                            <th class="bg-primary text-white">Time</th>
                            <th class="bg-primary text-white">Venue</th>
                            <th class="text-center bg-primary text-white">Status</th>
                            <th class="text-center pe-3 bg-primary text-white">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        <tr>
                            <td class="ps-3">
                                <div class="exam-date-badge">
                                    <span class="day">{{ $schedule->exam_date->format('d') }}</span>
                                    <span class="month">{{ $schedule->exam_date->format('M') }}</span>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-semibold">{{ $schedule->exam_name }}</div>
                                    <small class="text-muted">
                                        <span class="badge bg-light text-dark me-1">{{ $schedule->exam_type_name }}</span>
                                        {{ $schedule->program->name ?? 'N/A' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="fw-semibold">{{ $schedule->course->code ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ Str::limit($schedule->course->name ?? '', 30) }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="time-badge">
                                    <i class="bx bx-time"></i>
                                    {{ $schedule->formatted_time }}
                                </div>
                                <small class="d-block text-muted mt-1">{{ $schedule->duration_formatted }}</small>
                            </td>
                            <td>
                                <div class="venue-info">
                                    <i class="bx bx-map"></i>
                                    <span>{{ $schedule->full_venue }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $schedule->status_color }}">
                                    {{ $schedule->status_name }}
                                </span>
                                @if($schedule->is_published)
                                    <span class="badge bg-success-subtle text-success ms-1" title="Published">
                                        <i class="bx bx-check"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('college.exam-schedules.show', $schedule) }}" 
                                       class="btn btn-sm btn-outline-info" title="View Details">
                                        <i class="bx bx-show"></i>
                                    </a>
                                    @if($schedule->canEdit())
                                    <a href="{{ route('college.exam-schedules.edit', $schedule) }}" 
                                       class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </a>
                                    @endif
                                    <div class="dropdown action-dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @if(!$schedule->is_published && $schedule->status == 'draft')
                                            <li>
                                                <form action="{{ route('college.exam-schedules.publish', $schedule) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bx bx-upload text-success"></i> Publish
                                                    </button>
                                                </form>
                                            </li>
                                            @elseif($schedule->is_published)
                                            <li>
                                                <form action="{{ route('college.exam-schedules.unpublish', $schedule) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="bx bx-hide text-warning"></i> Unpublish
                                                    </button>
                                                </form>
                                            </li>
                                            @endif
                                            <li>
                                                <a class="dropdown-item" href="{{ route('college.exam-schedules.print', $schedule) }}" target="_blank">
                                                    <i class="bx bx-printer text-secondary"></i> Print
                                                </a>
                                            </li>
                                            @if($schedule->canDelete())
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button" class="dropdown-item text-danger" onclick="openDeleteModal('{{ $schedule->id }}', '{{ addslashes($schedule->course->name ?? 'Exam Schedule') }}')">
                                                    <i class="bx bx-trash"></i> Delete
                                                </button>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($schedules->hasPages())
            <div class="card-footer bg-white border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $schedules->firstItem() }} to {{ $schedules->lastItem() }} of {{ $schedules->total() }} schedules
                    </small>
                    {{ $schedules->links() }}
                </div>
            </div>
            @endif

            @else
            <div class="empty-state">
                <i class="bx bx-calendar-x"></i>
                <h5 class="text-muted">No Exam Schedules Found</h5>
                <p class="text-muted mb-4">Get started by creating your first exam schedule.</p>
                <a href="{{ route('college.exam-schedules.create') }}" class="btn btn-success">
                    <i class="bx bx-plus me-1"></i> Create Exam Schedule
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof toastr !== 'undefined') {
            toastr.success('{{ session('success') }}');
        }
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof toastr !== 'undefined') {
            toastr.error('{{ session('error') }}');
        }
    });
</script>
@endif

<!-- Delete Confirmation Modal -->
<div class="delete-modal-overlay" id="deleteModal">
    <div class="delete-modal">
        <div class="delete-modal-header">
            <div class="delete-modal-icon">
                <i class='bx bx-trash-alt'></i>
            </div>
            <h3>Delete Exam Schedule?</h3>
        </div>
        <div class="delete-modal-body">
            <p>You are about to delete this exam schedule:</p>
            <div class="exam-name" id="deleteExamName"></div>
            <p>This action cannot be undone. All associated data will be permanently removed.</p>
            <div class="warning-text">
                <i class='bx bx-error-circle'></i>
                This is a permanent action!
            </div>
        </div>
        <div class="delete-modal-footer">
            <button type="button" class="modal-btn cancel" onclick="closeDeleteModal()">
                <i class='bx bx-x'></i>
                Cancel
            </button>
            <form id="deleteForm" method="POST" style="flex: 1; margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="modal-btn confirm-delete" style="width: 100%;">
                    <i class='bx bx-trash'></i>
                    Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(scheduleId, examName) {
        document.getElementById('deleteExamName').textContent = examName;
        document.getElementById('deleteForm').action = '{{ url("college/exam-schedules") }}/' + scheduleId;
        document.getElementById('deleteModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });
</script>
@endsection
