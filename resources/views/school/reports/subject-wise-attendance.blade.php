@extends('layouts.main')

@section('title', 'Subject-Wise Attendance Report')

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

    .subject-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }

    .attendance-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-present {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-absent {
        background-color: #f8d7da;
        color: #721c24;
    }

    .badge-late {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-sick {
        background-color: #d1ecf1;
        color: #0c5460;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Subject-Wise Attendance Report', 'url' => '#', 'icon' => 'bx bx-book']
        ]" />

        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h6 class="mb-0 text-uppercase">SUBJECT-WISE ATTENDANCE REPORT</h6>
                <p class="text-muted mb-0">Attendance analysis by subject</p>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <h6 class="section-title">
                    <i class="bx bx-filter-alt me-2"></i>Filters & Options
                </h6>
                <form method="GET" action="{{ route('school.reports.subject-wise-attendance') }}" class="row g-3">
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
                        <label for="subject_id" class="form-label fw-semibold">Subject</label>
                        <select class="form-select select2" id="subject_id" name="subject_id">
                            <option value="">All Subjects</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ ($subjectId == $subject->id) ? 'selected' : '' }}>
                                    {{ $subject->name }} @if($subject->code)({{ $subject->code }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-modern">
                                <i class="bx bx-search me-1"></i>Apply Filters
                            </button>
                            <a href="{{ route('school.reports.subject-wise-attendance') }}" class="btn btn-outline-secondary btn-modern">
                                <i class="bx bx-undo me-1"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Statistics -->
        @if(!empty($attendanceData))
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card border-primary">
                    <div class="card-body text-center">
                        <i class="bx bx-book stats-icon text-primary"></i>
                        <div class="stat-number text-primary">{{ count($attendanceData) }}</div>
                        <div class="stat-label">Total Subjects</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-success">
                    <div class="card-body text-center">
                        <i class="bx bx-user-check stats-icon text-success"></i>
                        <div class="stat-number text-success">
                            {{ number_format(array_sum(array_column($attendanceData, 'total_present')) / max(array_sum(array_column($attendanceData, 'total_records')), 1) * 100, 1) }}%
                        </div>
                        <div class="stat-label">Average Attendance Rate</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-info">
                    <div class="card-body text-center">
                        <i class="bx bx-calendar-check stats-icon text-info"></i>
                        <div class="stat-number text-info">{{ array_sum(array_column($attendanceData, 'total_sessions')) }}</div>
                        <div class="stat-label">Total Sessions</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card border-warning">
                    <div class="card-body text-center">
                        <i class="bx bx-group stats-icon text-warning"></i>
                        <div class="stat-number text-warning">{{ array_sum(array_column($attendanceData, 'total_students')) }}</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Subject-Wise Data -->
        <div class="card table-card">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bx bx-bar-chart me-2"></i>Subject-Wise Attendance Analysis
                </h6>
            </div>
            <div class="card-body">
                @if(empty($attendanceData))
                    <div class="alert alert-info text-center">
                        <i class="bx bx-info-circle me-2"></i>
                        No attendance data found for the selected filters. Please adjust your filters and try again.
                    </div>
                @else
                    @foreach($attendanceData as $subject)
                        <div class="subject-section mb-4">
                            <div class="subject-header">
                                <h5 class="mb-0">
                                    <i class="bx bx-book me-2"></i>
                                    {{ $subject['subject_name'] }}
                                    @if($subject['subject_code'])
                                        <small>({{ $subject['subject_code'] }})</small>
                                    @endif
                                </h5>
                            </div>

                            <!-- Subject Summary -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="stat-number text-primary">{{ $subject['attendance_rate'] }}%</div>
                                            <div class="stat-label">Attendance Rate</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="stat-number text-success">{{ $subject['total_present'] }}</div>
                                            <div class="stat-label">Present</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="stat-number text-danger">{{ $subject['total_absent'] }}</div>
                                            <div class="stat-label">Absent</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center">
                                            <div class="stat-number text-warning">{{ $subject['total_late'] + $subject['total_sick'] }}</div>
                                            <div class="stat-label">Late/Sick</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Class-wise Breakdown -->
                            @if(!empty($subject['classes']))
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Class</th>
                                                <th>Stream</th>
                                                <th>Sessions</th>
                                                <th>Students</th>
                                                <th>Present</th>
                                                <th>Absent</th>
                                                <th>Late</th>
                                                <th>Sick</th>
                                                <th>Attendance Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($subject['classes'] as $classData)
                                                <tr>
                                                    <td>{{ $classData['class_name'] }}</td>
                                                    <td>{{ $classData['stream_name'] }}</td>
                                                    <td>{{ $classData['sessions'] }}</td>
                                                    <td>{{ $classData['students'] }}</td>
                                                    <td>
                                                        <span class="attendance-badge badge-present">{{ $classData['present'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="attendance-badge badge-absent">{{ $classData['absent'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="attendance-badge badge-late">{{ $classData['late'] }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="attendance-badge badge-sick">{{ $classData['sick'] }}</span>
                                                    </td>
                                                    <td>
                                                        <strong class="text-primary">{{ $classData['attendance_rate'] }}%</strong>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    });
</script>
@endpush

