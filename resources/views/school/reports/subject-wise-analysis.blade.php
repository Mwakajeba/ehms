@extends('layouts.main')

@section('title', 'Subject Performance Analysis (By Grades and Gender) Report')

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

    .grade-header {
        background-color: #e9ecef;
        font-weight: 600;
    }

    .subject-header {
        background-color: #f8f9fa;
        font-weight: 700;
        font-size: 1rem;
        vertical-align: middle !important;
        text-align: center;
    }

    .absent-students-card {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 1px solid #f39c12;
        border-radius: 12px;
    }

    .summary-row {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .summary-row td {
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
            ['label' => 'Subject Performance Analysis', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />
        <h6 class="mb-0 text-uppercase">SUBJECT PERFORMANCE ANALYSIS (BY GRADES AND GENDER) REPORT</h6>
        <hr />

        <!-- Filters -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('school.reports.subject-wise-analysis') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                            <select class="form-control select2-single" id="academic_year_id" name="academic_year_id" required>
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($year->id) }}" {{ (isset($selectedAcademicYear) ? $selectedAcademicYear : ($currentAcademicYear ? \Vinkla\Hashids\Facades\Hashids::encode($currentAcademicYear->id) : '')) == \Vinkla\Hashids\Facades\Hashids::encode($year->id) ? 'selected' : '' }}>
                                        {{ $year->year_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="exam_type_id" class="form-label">Exam Type <span class="text-danger">*</span></label>
                            <select class="form-control select2-single" id="exam_type_id" name="exam_type_id" required>
                                <option value="">Select Exam Type</option>
                                @foreach($examTypes as $examType)
                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($examType->id) }}" {{ (isset($selectedExamType) ? $selectedExamType : '') == \Vinkla\Hashids\Facades\Hashids::encode($examType->id) ? 'selected' : '' }}>
                                        {{ $examType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
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
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
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
                            <button type="submit" class="btn btn-primary btn-modern me-2" id="generateReportBtn">
                                <i class="bx bx-search me-1"></i> Generate Report
                            </button>

                            @if(request()->hasAny(['academic_year_id', 'exam_type_id', 'class_id', 'stream_id']))
                                <a href="{{ route('school.reports.subject-wise-analysis') }}" class="btn btn-secondary btn-modern me-2">
                                    <i class="bx bx-reset me-1"></i> Clear Filters
                                </a>

                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-modern dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bx bx-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('school.reports.subject-wise-analysis.export', request()->all()) }}" target="_blank">
                                            <i class="bx bx-file-pdf me-2"></i> Export to PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('school.reports.subject-wise-analysis', array_merge(request()->all(), ['export' => 'excel'])) }}">
                                            <i class="bx bx-file-excel me-2"></i> Export to Excel
                                        </a></li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(request()->has('exam_type_id') && request()->has('academic_year_id'))
            <!-- Subject Performance Analysis Table -->
            @if(!empty($subjectWiseData['subjects']))
            <div class="card table-card">
                <div class="card-header">
                    <h6 class="mb-0">SUBJECT PERFORMANCE ANALYSIS (BY GRADES AND GENDER)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="subjectAnalysisTable">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="subject-header">Subject</th>
                                    <th rowspan="2" class="grade-header">Grade</th>
                                    <th colspan="2" class="text-center grade-header">Count</th>
                                    <th rowspan="2" class="grade-header text-center">Total</th>
                                </tr>
                                <tr>
                                    <th class="text-center grade-header">Female (KE)</th>
                                    <th class="text-center grade-header">Male (ME)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjectWiseData['subjects'] as $subject)
                                    @php
                                        $grades = array_keys($subject['grade_breakdown']);
                                        $firstGrade = true;
                                    @endphp
                                    @foreach($grades as $grade)
                                        <tr>
                                            @if($firstGrade)
                                                <td rowspan="{{ count($grades) + 1 }}" class="subject-header fw-bold text-center">
                                                    {{ $subject['subject_name'] }}
                                                </td>
                                                @php $firstGrade = false; @endphp
                                            @endif
                                            <td class="text-center fw-bold">{{ $grade }}</td>
                                            <td class="text-center">{{ $subject['grade_breakdown'][$grade]['female'] }}</td>
                                            <td class="text-center">{{ $subject['grade_breakdown'][$grade]['male'] }}</td>
                                            <td class="text-center fw-bold">{{ $subject['grade_breakdown'][$grade]['total'] }}</td>
                                        </tr>
                                    @endforeach
                                    <!-- Total row for this subject -->
                                    <tr class="summary-row">
                                        <td class="text-center fw-bold">Total</td>
                                        <td class="text-center fw-bold">{{ $subject['totals']['female'] }}</td>
                                        <td class="text-center fw-bold">{{ $subject['totals']['male'] }}</td>
                                        <td class="text-center fw-bold">{{ $subject['totals']['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Absent Students -->
            @if(!empty($subjectWiseData['absentStudents']))
            <div class="card absent-students-card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">STUDENTS ABSENT FROM EXAMINATIONS</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Stream</th>
                                    <th>ABSENT SUBJECTS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjectWiseData['absentStudents'] as $index => $absentStudent)
                                <tr>
                                    <td>{{ $absentStudent['student']->first_name }} {{ $absentStudent['student']->last_name }}</td>
                                    <td class="text-center">{{ $absentStudent['student']->class->name ?? '-' }}</td>
                                    <td class="text-center">{{ $absentStudent['student']->stream->name ?? '-' }}</td>
                                    <td>
                                        @if(!empty($absentStudent['absent_subjects']))
                                            <span class="badge bg-danger">{{ implode(', ', $absentStudent['absent_subjects']) }}</span>
                                        @else
                                            <span class="badge bg-warning">ABSENT</span>
                                        @endif
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
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-info-circle fs-1 text-info mb-3"></i>
                    <h5>Please select Academic Year and Exam Type to generate the report</h5>
                    <p class="text-muted">Choose an academic year and exam type from the filters above, then click "Generate Report" to view subject performance analysis.</p>
                </div>
            </div>
        @endif
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
    // Initialize Select2
    $('.select2-single').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Select an option';
        }
    });

    // Initialize DataTable
    $('#subjectAnalysisTable').DataTable({
        responsive: true,
        paging: true,
        searching: true,
        ordering: false, // Disable client-side sorting since data is pre-sorted
        info: true,
        pageLength: 50,
        columnDefs: [
            { className: 'text-center', targets: '_all' }
        ],
        language: {
            search: "Search subjects:",
            lengthMenu: "Show _MENU_ entries per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Remove auto-submit functionality - only submit when Generate Report is clicked

    // Add form validation before submission
    $('#filterForm').on('submit', function(e) {
        var academicYear = $('#academic_year_id').val();
        var examType = $('#exam_type_id').val();

        if (!academicYear || !examType) {
            e.preventDefault();
            alert('Please select both Academic Year and Exam Type to generate the report.');
            return false;
        }

        // Show loading state
        $('#generateReportBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Generating...');
    });
});
</script>
@endpush