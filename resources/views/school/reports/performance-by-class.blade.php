@extends('layouts.main')

@section('title', 'Performance by Class Report')

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

    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .summary-row {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .summary-row td {
        border-top: 2px solid #dee2e6;
    }

    .absent-students-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Performance by Class Report', 'url' => '#', 'icon' => 'bx bx-trending-up']
        ]" />
        <h6 class="mb-0 text-uppercase">PERFORMANCE BY CLASS REPORT</h6>
        <hr />

        <!-- Filters -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('school.reports.performance-by-class') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="academic_year_id" class="form-label">Academic Year</label>
                            <select class="form-control select2-single" id="academic_year_id" name="academic_year_id" required>
                                <option value="">All Academic Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($year->id) }}" {{ (isset($selectedAcademicYear) ? $selectedAcademicYear : ($currentAcademicYear ? \Vinkla\Hashids\Facades\Hashids::encode($currentAcademicYear->id) : '')) == \Vinkla\Hashids\Facades\Hashids::encode($year->id) ? 'selected' : '' }}>
                                        {{ $year->year_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="exam_type_id" class="form-label">Exam Type</label>
                            <select class="form-control select2-single" id="exam_type_id" name="exam_type_id" required>
                                <option value="">Select Exam Type</option>
                                @foreach($examTypes as $examType)
                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($examType->id) }}" {{ (isset($selectedExamType) ? $selectedExamType : '') == \Vinkla\Hashids\Facades\Hashids::encode($examType->id) ? 'selected' : '' }}>
                                        {{ $examType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="class_id" class="form-label">Class</label>
                            <select class="form-control select2-single" id="class_id" name="class_id">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($class->id) }}" {{ (isset($selectedClass) ? $selectedClass : '') == \Vinkla\Hashids\Facades\Hashids::encode($class->id) ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="stream_id" class="form-label">Stream</label>
                            <select class="form-control select2-single" id="stream_id" name="stream_id">
                                <option value="">All Streams</option>
                                @foreach($streams as $stream)
                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($stream->id) }}" {{ (isset($selectedStream) ? $selectedStream : '') == \Vinkla\Hashids\Facades\Hashids::encode($stream->id) ? 'selected' : '' }}>
                                        {{ $stream->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-modern me-2">
                                <i class="bx bx-search me-1"></i> Generate Report
                            </button>

                            @if(request()->hasAny(['academic_year_id', 'exam_type_id', 'class_id', 'stream_id']))
                                <a href="{{ route('school.reports.performance-by-class') }}" class="btn btn-secondary btn-modern me-2">
                                    <i class="bx bx-reset me-1"></i> Clear Filters
                                </a>

                                @if(isset($selectedExamType) && isset($selectedAcademicYear) && isset($performanceData) && !empty($performanceData))
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-modern dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bx bx-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('school.reports.performance-by-class', [
                                            'academic_year_id' => $selectedAcademicYear ?? request('academic_year_id'),
                                            'exam_type_id' => $selectedExamType ?? request('exam_type_id'),
                                            'class_id' => $selectedClass ?? request('class_id'),
                                            'stream_id' => $selectedStream ?? request('stream_id'),
                                            'export' => 'pdf'
                                        ]) }}">
                                            <i class="bx bx-file-pdf me-2"></i> Export to PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('school.reports.performance-by-class', [
                                            'academic_year_id' => $selectedAcademicYear ?? request('academic_year_id'),
                                            'exam_type_id' => $selectedExamType ?? request('exam_type_id'),
                                            'class_id' => $selectedClass ?? request('class_id'),
                                            'stream_id' => $selectedStream ?? request('stream_id'),
                                            'export' => 'excel'
                                        ]) }}">
                                            <i class="bx bx-file-excel me-2"></i> Export to Excel
                                        </a></li>
                                    </ul>
                                </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>                <!-- Report Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-trending-up me-1 font-22 text-dark"></i></div>
                            <h5 class="mb-0 text-dark">Performance by Class Report</h5>
                        </div>
                        <hr />

        @if(request()->has('exam_type_id'))
            <!-- Summary Statistics -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-group"></i>
                            </div>
                            <h4 class="mb-1">{{ $performanceData['grandTotal']['total_students'] }}</h4>
                            <p class="text-muted mb-0 small">Total Students</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <h4 class="mb-1">{{ $performanceData['grandTotal']['passed'] }}</h4>
                            <p class="text-muted mb-0 small">Passed</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-x-circle"></i>
                            </div>
                            <h4 class="mb-1">{{ $performanceData['grandTotal']['failed'] }}</h4>
                            <p class="text-muted mb-0 small">Failed</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-time"></i>
                            </div>
                            <h4 class="mb-1">{{ $performanceData['grandTotal']['not_attempted'] }}</h4>
                            <p class="text-muted mb-0 small">Not Attempted</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-percentage"></i>
                            </div>
                            <h4 class="mb-1">{{ $performanceData['grandTotal']['pass_rate'] ?? 0 }}%</h4>
                            <p class="text-muted mb-0 small">Pass Rate</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-school"></i>
                            </div>
                            <h4 class="mb-1">{{ $performanceData['grandTotal']['classes_count'] ?? 0 }}</h4>
                            <p class="text-muted mb-0 small">Classes</p>
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($performanceData['performance']))
                <!-- Performance Table -->
                <div class="card table-card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">PERFORMANCE BY CLASS AND STREAM</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="performanceByClassTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">Class Level</th>
                                        <th class="text-center">Stream</th>
                                        <th class="text-center">Passed</th>
                                        <th class="text-center">Failed</th>
                                        <th class="text-center">Not Attempted</th>
                                        <th class="text-center">Total Students</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($performanceData['performance'] as $className => $streams)
                                        @foreach($streams as $streamData)
                                        <tr>
                                            <td class="text-center">{{ $streamData['class']->name }}</td>
                                            <td class="text-center">{{ $streamData['stream']->name }}</td>
                                            <td class="text-center">{{ $streamData['passed'] }}</td>
                                            <td class="text-center">{{ $streamData['failed'] }}</td>
                                            <td class="text-center">{{ $streamData['not_attempted'] }}</td>
                                            <td class="text-center">{{ $streamData['total_students'] }}</td>
                                        </tr>
                                        @endforeach

                                        <!-- Subtotal for this class -->
                                        <tr class="summary-row">
                                            <td colspan="2" class="text-right font-weight-bold">SUBTOTAL - {{ $className }}</td>
                                            <td class="text-center font-weight-bold">{{ $performanceData['subtotals'][$className]['passed'] }}</td>
                                            <td class="text-center font-weight-bold">{{ $performanceData['subtotals'][$className]['failed'] }}</td>
                                            <td class="text-center font-weight-bold">{{ $performanceData['subtotals'][$className]['not_attempted'] }}</td>
                                            <td class="text-center font-weight-bold">{{ $performanceData['subtotals'][$className]['total_students'] }}</td>
                                        </tr>
                                    @endforeach

                                    <!-- Grand Total -->
                                    <tr class="summary-row" style="font-weight: bolder; font-size: 1.1em;">
                                        <td colspan="2" class="text-right font-weight-bolder">GRAND TOTAL</td>
                                        <td class="text-center font-weight-bolder">{{ $performanceData['grandTotal']['passed'] }}</td>
                                        <td class="text-center font-weight-bolder">{{ $performanceData['grandTotal']['failed'] }}</td>
                                        <td class="text-center font-weight-bolder">{{ $performanceData['grandTotal']['not_attempted'] }}</td>
                                        <td class="text-center font-weight-bolder">{{ $performanceData['grandTotal']['total_students'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                        <!-- Absent Students -->
                        @if(!empty($performanceData['absentStudents']))
                            <div class="card absent-students-card">
                                <div class="card-header">
                                    <h6 class="mb-0">STUDENTS ABSENT FROM EXAMINATIONS</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th>STUDENT NAME</th>
                                                    <th class="text-center">CLASS</th>
                                                    <th class="text-center">STREAM</th>
                                                    <th>ABSENT SUBJECTS</th>
                                                    <th class="text-center">STATUS</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($performanceData['absentStudents'] as $index => $absentStudent)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td>{{ $absentStudent['student']->first_name }} {{ $absentStudent['student']->last_name }}</td>
                                                        <td class="text-center">{{ $absentStudent['student']->class->name ?? '-' }}</td>
                                                        <td class="text-center">{{ $absentStudent['student']->stream->name ?? '-' }}</td>
                                                        <td>
                                                            @if(!empty($absentStudent['absent_subjects']))
                                                                <span class="badge bg-danger">{{ implode(', ', $absentStudent['absent_subjects']) }}</span>
                                                            @else
                                                                <span class="text-muted">Not registered for some subjects</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-warning">ABSENT</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Please select exam type to generate the performance by class report.
                        </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for all filter dropdowns
    $('.select2-single').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    @if(!empty($performanceData['performance']))
    // Initialize DataTable
    $('#performanceByClassTable').DataTable({
        "pageLength": 25,
        "ordering": true,
        "searching": true,
        "paging": true,
        "responsive": true,
        "order": [[0, 'asc']],
        "columnDefs": [
            { "orderable": false, "targets": [] }
        ],
        "language": {
            "search": "Search classes:",
            "lengthMenu": "Show _MENU_ classes per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ classes",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
    @endif

    // Auto-submit form when filters change
    $('#academic_year_id, #exam_type_id, #class_id, #stream_id').on('change', function() {
        if ($('#academic_year_id').val() && $('#exam_type_id').val()) {
            $('#filterForm').submit();
        }
    });
});
</script>
@endsection