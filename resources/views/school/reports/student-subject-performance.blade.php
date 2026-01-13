@extends('layouts.main')

@section('title', 'Student Subject Performance and Progress Analysis')

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
        color: #000 !important;
    }

    .table thead.table-dark th {
        background-color: #f8f9fa !important;
        color: #000 !important;
        border-color: #dee2e6 !important;
    }

    .table td {
        vertical-align: middle;
        font-size: 0.875rem;
    }

    .student-header {
        background-color: #e9ecef;
        font-weight: 600;
        font-size: 1rem;
    }

    .subject-header {
        background-color: #f8f9fa;
        font-weight: 700;
        font-size: 0.9rem;
        vertical-align: middle !important;
        text-align: center;
    }

    .grade-badge {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
        color: #000 !important;
    }

    [class*="grade-"] {
        color: #000 !important;
    }

    .grade-A { background-color: #d4edda; }
    .grade-B { background-color: #cce5ff; }
    .grade-C { background-color: #fff3cd; }
    .grade-D { background-color: #f8d7da; }
    .grade-E { background-color: #f5c6cb; }

    .improvement-up {
        color: #28a745;
        font-weight: 600;
    }

    .improvement-down {
        color: #dc3545;
        font-weight: 600;
    }

    .improvement-same {
        color: #6c757d;
        font-weight: 600;
    }

    .summary-row {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .summary-row td {
        border-top: 2px solid #dee2e6;
    }

    .period-indicator {
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .period-current {
        background-color: #e3f2fd;
        color: #1976d2;
    }

    .subject-name-cell {
        text-align: left !important;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Student Subject Performance', 'url' => '#', 'icon' => 'bx bx-user']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT SUBJECT PERFORMANCE AND PROGRESS ANALYSIS</h6>
        <hr />

        <!-- Filters -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('school.reports.student-subject-performance') }}" id="reportForm">
                        <div class="row g-3">
                            <!-- Current Period -->
                            <div class="col-md-3">
                                <label for="academic_year_id_1" class="form-label">Current Period Academic Year</label>
                                <select name="academic_year_id_1" id="academic_year_id_1" class="form-select select2" value="{{ $selectedAcademicYear1 ?? '' }}">
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($year->id) }}" {{ (isset($selectedAcademicYear1) ? $selectedAcademicYear1 : '') == \Vinkla\Hashids\Facades\Hashids::encode($year->id) ? 'selected' : '' }}>
                                            {{ $year->year_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="exam_type_id_1" class="form-label">Current Period Exam Type</label>
                                <select name="exam_type_id_1" id="exam_type_id_1" class="form-select select2" value="{{ $selectedExamType1 ?? '' }}">
                                    <option value="">Select Exam Type</option>
                                    @foreach($examTypes as $examType)
                                        <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($examType->id) }}" {{ (isset($selectedExamType1) ? $selectedExamType1 : '') == \Vinkla\Hashids\Facades\Hashids::encode($examType->id) ? 'selected' : '' }}>
                                            {{ $examType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Previous Period -->
                            <div class="col-md-3">
                                <label for="academic_year_id_2" class="form-label">Previous Period Academic Year</label>
                                <select name="academic_year_id_2" id="academic_year_id_2" class="form-select select2" value="{{ $selectedAcademicYear2 ?? '' }}">
                                    <option value="">Select Academic Year</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($year->id) }}" {{ (isset($selectedAcademicYear2) ? $selectedAcademicYear2 : '') == \Vinkla\Hashids\Facades\Hashids::encode($year->id) ? 'selected' : '' }}>
                                            {{ $year->year_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="exam_type_id_2" class="form-label">Previous Period Exam Type</label>
                                <select name="exam_type_id_2" id="exam_type_id_2" class="form-select select2" value="{{ $selectedExamType2 ?? '' }}">
                                    <option value="">Select Exam Type</option>
                                    @foreach($examTypes as $examType)
                                        <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($examType->id) }}" {{ (isset($selectedExamType2) ? $selectedExamType2 : '') == \Vinkla\Hashids\Facades\Hashids::encode($examType->id) ? 'selected' : '' }}>
                                            {{ $examType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Class and Stream -->
                            <div class="col-md-3">
                                <label for="class_id" class="form-label">Class</label>
                                <select name="class_id" id="class_id" class="form-select select2" value="{{ $selectedClass ?? '' }}">
                                    <option value="">Select Class</option>
                                    @foreach($classes as $class)
                                        <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($class->id) }}" {{ (isset($selectedClass) ? $selectedClass : '') == \Vinkla\Hashids\Facades\Hashids::encode($class->id) ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="stream_id" class="form-label">Stream</label>
                                <select name="stream_id" id="stream_id" class="form-select select2" value="{{ $selectedStream ?? '' }}">
                                    <option value="">All Streams</option>
                                    @foreach($streams as $stream)
                                        <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($stream->id) }}" {{ (isset($selectedStream) ? $selectedStream : '') == \Vinkla\Hashids\Facades\Hashids::encode($stream->id) ? 'selected' : '' }}>
                                            {{ $stream->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-modern">
                                    <i class="bx bx-search me-1"></i> Generate Analysis
                                </button>
                                @if(request()->hasAny(['academic_year_id_1', 'exam_type_id_1', 'academic_year_id_2', 'exam_type_id_2']))
                                    <a href="{{ route('school.reports.student-subject-performance.pdf') }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger btn-modern">
                                        <i class="bx bx-file me-1"></i> Export to PDF
                                    </a>
                                    <a href="{{ route('school.reports.student-subject-performance.excel') }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-modern">
                                        <i class="bx bx-download me-1"></i> Export to Excel
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results -->
        @if(!empty($studentPerformanceData['students']))
            <div class="card table-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="performanceTable" class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th rowspan="2" class="text-center align-middle">STUDENT NAME</th>
                                    <th rowspan="2" class="subject-name-cell align-middle">SUBJECT</th>
                                    <th colspan="3" class="text-center">CURRENT PERFORMANCE</th>
                                    <th colspan="3" class="text-center">PREVIOUS PERFORMANCE</th>
                                    <th rowspan="2" class="text-center align-middle">Improvement/<br>Decline</th>
                                </tr>
                                <tr>
                                    <th class="text-center">Grade</th>
                                                <th class="text-center">Marks Scored (%)</th>
                                                <th class="text-center">Class Rank</th>
                                                <th class="text-center">Grade</th>
                                                <th class="text-center">Marks Scored (%)</th>
                                                <th class="text-center">Class Rank</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $currentStudent = null;
                                                $rowspan = 0;
                                            @endphp

                                            @foreach($studentPerformanceData['students'] as $studentIndex => $studentData)
                                                @php
                                                    $studentSubjects = array_filter($studentData['subjects'], function($subject) {
                                                        return $subject['current_period'] || $subject['previous_period'];
                                                    });
                                                    $subjectCount = count($studentSubjects);
                                                    $rowspan = max($rowspan, $subjectCount);
                                                @endphp

                                                @foreach($studentData['subjects'] as $subjectIndex => $subject)
                                                    @if($subject['current_period'] || $subject['previous_period'])
                                                    <tr>
                                                        @if($subjectIndex == 0)
                                                            <td rowspan="{{ $subjectCount }}" class="font-weight-bold align-middle">
                                                                {{ $studentData['student']->first_name }} {{ $studentData['student']->last_name }}
                                                                <br><small class="text-muted">{{ $studentData['student']->class->name ?? '-' }} {{ $studentData['student']->stream->name ?? '' }}</small>
                                                            </td>
                                                        @endif
                                                        <td class="font-weight-bold subject-name-cell">{{ $subject['subject_name'] }}</td>

                                                        <!-- Current Period -->
                                                        <td class="text-center">
                                                            @if($subject['current_period'])
                                                                @if($subject['current_period']['status'] === 'absent')
                                                                    <span class="badge bg-danger">ABS</span>
                                                                @elseif($subject['current_period']['status'] === 'exempted')
                                                                    <span class="badge bg-warning">EXEMPT</span>
                                                                @elseif($subject['current_period']['status'] === 'present')
                                                                    <span class="grade-badge grade-{{ strtolower($subject['current_period']['grade']) }}">{{ $subject['current_period']['grade'] }}</span>
                                                                @else
                                                                    <span class="badge bg-light">{{ $subject['current_period']['grade'] }}</span>
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($subject['current_period'])
                                                                @if($subject['current_period']['status'] === 'absent' || $subject['current_period']['status'] === 'exempted')
                                                                    -
                                                                @else
                                                                    {{ $subject['current_period']['marks_percentage'] }}%
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($subject['current_period'])
                                                                @if($subject['current_period']['status'] === 'absent' || $subject['current_period']['status'] === 'exempted')
                                                                    -
                                                                @else
                                                                    {{ $subject['current_period']['class_rank'] }}
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>

                                                        <!-- Previous Period -->
                                                        <td class="text-center">
                                                            @if($subject['previous_period'])
                                                                @if($subject['previous_period']['status'] === 'absent')
                                                                    <span class="badge bg-danger">ABS</span>
                                                                @elseif($subject['previous_period']['status'] === 'exempted')
                                                                    <span class="badge bg-warning">EXEMPT</span>
                                                                @elseif($subject['previous_period']['status'] === 'present')
                                                                    <span class="grade-badge grade-{{ strtolower($subject['previous_period']['grade']) }}">{{ $subject['previous_period']['grade'] }}</span>
                                                                @else
                                                                    <span class="badge bg-light">{{ $subject['previous_period']['grade'] }}</span>
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($subject['previous_period'])
                                                                @if($subject['previous_period']['status'] === 'absent' || $subject['previous_period']['status'] === 'exempted')
                                                                    -
                                                                @else
                                                                    {{ $subject['previous_period']['marks_percentage'] }}%
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            @if($subject['previous_period'])
                                                                @if($subject['previous_period']['status'] === 'absent' || $subject['previous_period']['status'] === 'exempted')
                                                                    -
                                                                @else
                                                                    {{ $subject['previous_period']['class_rank'] }}
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>

                                                        <!-- Improvement/Decline -->
                                                        <td class="text-center">
                                                            @if($subject['improvement'] !== null)
                                                                @if($subject['improvement'] > 0)
                                                                    <span class="text-success font-weight-bold">+{{ $subject['improvement'] }}%</span>
                                                                @elseif($subject['improvement'] < 0)
                                                                    <span class="text-danger font-weight-bold">{{ $subject['improvement'] }}%</span>
                                                                @else
                                                                    <span class="text-warning">0%</span>
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        @if(request()->hasAny(['academic_year_id_1', 'exam_type_id_1', 'academic_year_id_2', 'exam_type_id_2']))
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        No student performance data found for the selected criteria.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
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
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Select an option';
        }
    });

    // Set selected values for Select2
    @if(isset($selectedAcademicYear1) && $selectedAcademicYear1)
        $('#academic_year_id_1').val('{{ $selectedAcademicYear1 }}').trigger('change');
    @endif
    @if(isset($selectedExamType1) && $selectedExamType1)
        $('#exam_type_id_1').val('{{ $selectedExamType1 }}').trigger('change');
    @endif
    @if(isset($selectedAcademicYear2) && $selectedAcademicYear2)
        $('#academic_year_id_2').val('{{ $selectedAcademicYear2 }}').trigger('change');
    @endif
    @if(isset($selectedExamType2) && $selectedExamType2)
        $('#exam_type_id_2').val('{{ $selectedExamType2 }}').trigger('change');
    @endif
    @if(isset($selectedClass) && $selectedClass)
        $('#class_id').val('{{ $selectedClass }}').trigger('change');
    @endif
    @if(isset($selectedStream) && $selectedStream)
        $('#stream_id').val('{{ $selectedStream }}').trigger('change');
    @endif

    // Initialize DataTable if table exists
    if ($('#performanceTable').length) {
        $('#performanceTable').DataTable({
            responsive: true,
            paging: true,
            searching: true,
            ordering: false, // Disable client-side sorting since data is pre-sorted
            info: true,
            pageLength: 50,
            columnDefs: [
                { className: 'text-center', targets: '_all' },
                { className: 'subject-name-cell', targets: 1 } // Subject column (index 1)
            ],
            language: {
                search: "Search students:",
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
    }

    // Form validation
    $('#reportForm').on('submit', function(e) {
        var currentAcademicYear = $('#academic_year_id_1').val();
        var currentExamType = $('#exam_type_id_1').val();

        if (!currentAcademicYear || !currentExamType) {
            e.preventDefault();
            alert('Please select both Current Period Academic Year and Exam Type to generate the report.');
            return false;
        }

        // Show loading state
        $('#reportForm button[type="submit"]').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Generating...');
    });
});
</script>
@endpush