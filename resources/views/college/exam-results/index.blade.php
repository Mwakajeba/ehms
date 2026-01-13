@extends('layouts.main')

@section('content')
<style>
    .results-container {
        margin-left: 250px;
        margin-right: 20px;
        padding: 20px;
        max-width: calc(100vw - 280px);
    }
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
    }
    .filter-card .form-label {
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 0.3rem;
    }
    .filter-card .form-control,
    .filter-card .form-select {
        font-size: 0.875rem;
    }
    .results-table th {
        font-size: 0.8rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .results-table td {
        font-size: 0.85rem;
        vertical-align: middle;
    }
    
    /* Breadcrumb Styles - Modern with Icons */
    .breadcrumb-modern-icons {
        display: flex;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        background: transparent;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
    }
    .breadcrumb-item {
        display: flex;
        align-items: center;
        color: #6c757d;
        transition: all 0.2s ease;
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
        transition: all 0.2s ease;
    }
    .breadcrumb-link {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #6c757d;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.5);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .breadcrumb-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(13, 110, 253, 0.1), transparent);
        transition: left 0.5s ease;
    }
    .breadcrumb-link:hover {
        color: #0d6efd;
        background: rgba(13, 110, 253, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        border-color: rgba(13, 110, 253, 0.2);
    }
    .breadcrumb-link:hover .breadcrumb-icon {
        opacity: 1;
        transform: scale(1.1);
    }
    .breadcrumb-link:hover::before {
        left: 100%;
    }
    .breadcrumb-item-current {
        color: #495057;
        font-weight: 600;
    }
    .breadcrumb-item-current .breadcrumb-content {
        padding: 0.5rem 0.75rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    .breadcrumb-item-current .breadcrumb-icon {
        opacity: 1;
        color: #0d6efd;
    }
    .breadcrumb-separator {
        display: flex;
        align-items: center;
        color: #adb5bd;
        margin-left: 0.5rem;
        font-size: 0.75rem;
        opacity: 0.6;
    }
    .breadcrumb-separator i {
        font-size: 0.875rem;
    }
    .breadcrumb-text {
        font-weight: inherit;
        white-space: nowrap;
    }
</style>

<div class="results-container">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4" style="margin-top: 100px;">
        <ol class="breadcrumb-modern-icons">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="breadcrumb-link">
                    <div class="breadcrumb-content">
                        <i class="breadcrumb-icon bx bx-home-alt"></i>
                        <span class="breadcrumb-text">Dashboard</span>
                    </div>
                </a>
                <span class="breadcrumb-separator">
                    <i class="bx bx-chevron-right"></i>
                </span>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('college.index') }}" class="breadcrumb-link">
                    <div class="breadcrumb-content">
                        <i class="breadcrumb-icon bx bx-building"></i>
                        <span class="breadcrumb-text">College</span>
                    </div>
                </a>
                <span class="breadcrumb-separator">
                    <i class="bx bx-chevron-right"></i>
                </span>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('college.exams-management.dashboard') }}" class="breadcrumb-link">
                    <div class="breadcrumb-content">
                        <i class="breadcrumb-icon bx bx-book-reader"></i>
                        <span class="breadcrumb-text">Exams & Academics</span>
                    </div>
                </a>
                <span class="breadcrumb-separator">
                    <i class="bx bx-chevron-right"></i>
                </span>
            </li>
            <li class="breadcrumb-item breadcrumb-item-current" aria-current="page">
                <div class="breadcrumb-content">
                    <i class="breadcrumb-icon bx bx-file"></i>
                    <span class="breadcrumb-text">Student Exam Results</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="bx bx-file text-primary me-2"></i>Student Exam Results</h4>
            <p class="text-muted mb-0 small">View and manage all student exam results</p>
        </div>
        <a href="{{ route('college.exams-management.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3 px-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded p-2">
                            <i class="bx bx-file text-primary" style="font-size: 1.25rem;"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">Total Results</small>
                            <h5 class="mb-0 fw-bold">{{ number_format($stats['total_results']) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3 px-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 rounded p-2">
                            <i class="bx bx-check-circle text-success" style="font-size: 1.25rem;"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">Passed</small>
                            <h5 class="mb-0 fw-bold text-success">{{ number_format($stats['passed']) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3 px-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-danger bg-opacity-10 rounded p-2">
                            <i class="bx bx-x-circle text-danger" style="font-size: 1.25rem;"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">Failed</small>
                            <h5 class="mb-0 fw-bold text-danger">{{ number_format($stats['failed']) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm stat-card h-100">
                <div class="card-body py-3 px-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded p-2">
                            <i class="bx bx-time text-warning" style="font-size: 1.25rem;"></i>
                        </div>
                        <div class="ms-3">
                            <small class="text-muted">Pending</small>
                            <h5 class="mb-0 fw-bold text-warning">{{ number_format($stats['pending']) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-3 filter-card">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('college.exam-results.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Search Student</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Name or email..." value="{{ request('search') }}">
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
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select form-select-sm">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>{{ $course->course_code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="course_status" class="form-select form-select-sm">
                            <option value="">All</option>
                            <option value="passed" {{ request('course_status') == 'passed' ? 'selected' : '' }}>Passed</option>
                            <option value="failed" {{ request('course_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="bx bx-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('college.exam-results.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-reset me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 results-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Student</th>
                            <th>Program</th>
                            <th>Course</th>
                            <th>Academic Year</th>
                            <th>Semester</th>
                            <th class="text-center">CA</th>
                            <th class="text-center">Exam</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Grade</th>
                            <th class="text-center">GPA</th>
                            <th class="text-center">Status</th>
                            <th>Result Status</th>
                            <th class="text-center pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        <tr>
                            <td class="ps-3">{{ $loop->iteration + ($results->currentPage() - 1) * $results->perPage() }}</td>
                            <td>
                                <div class="fw-semibold">{{ $result->student->user->name }}</div>
                                <small class="text-muted">{{ $result->student->registration_number }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $result->course->course_code }}</div>
                                <small class="text-muted">{{ Str::limit($result->course->course_name, 20) }}</small>
                            </td>
                            <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info">{{ number_format($result->ca_total, 0) }}</span></td>
                            <td class="text-center"><span class="badge bg-warning bg-opacity-10 text-warning">{{ number_format($result->exam_total, 0) }}</span></td>
                            <td class="text-center"><strong>{{ number_format($result->total_marks, 0) }}</strong></td>
                            <td class="text-center"><span class="badge bg-dark">{{ $result->grade }}</span></td>
                            <td class="text-center"><strong>{{ number_format($result->gpa_points, 2) }}</strong></td>
                            <td class="text-center">
                                @if($result->course_status == 'passed')
                                    <span class="badge bg-success">Pass</span>
                                @else
                                    <span class="badge bg-danger">Fail</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('college.exam-results.show', $result->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bx bx-show"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="bx bx-info-circle text-muted" style="font-size: 2.5rem;"></i>
                                <p class="text-muted mt-2 mb-0">No results found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($results->hasPages())
            <div class="p-3 border-top">
                {{ $results->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

