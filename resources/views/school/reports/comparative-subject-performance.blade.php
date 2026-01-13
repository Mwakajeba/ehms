@extends('layouts.main')

@section('title', 'Comparative Subject Performance Analysis by Grade and Gender Report')

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

    .comparison-table th {
        background: #17a2b8;
        color: white;
        font-size: 12px;
        padding: 8px 6px;
        text-align: center;
    }

    .comparison-table td {
        padding: 8px 6px;
        font-size: 11px;
        text-align: center;
    }

    .period-header {
        background: #f8f9fa !important;
        font-weight: bold;
        color: #000 !important;
    }

    .difference-positive {
        color: #28a745;
        font-weight: bold;
    }

    .difference-negative {
        color: #dc3545;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Comparative Subject Performance', 'url' => '#', 'icon' => 'bx bx-trending-up']
        ]" />
        <h6 class="mb-0 text-uppercase">COMPARATIVE SUBJECT PERFORMANCE ANALYSIS BY GRADE AND GENDER</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card filter-card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('school.reports.comparative-subject-performance') }}" id="filterForm">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">Period 1</h6>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label for="academic_year_id_1" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-control select2-single" id="academic_year_id_1" name="academic_year_id_1" required>
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($year->id) }}" {{ (isset($selectedAcademicYear1) ? $selectedAcademicYear1 : ($currentAcademicYear ? \Vinkla\Hashids\Facades\Hashids::encode($currentAcademicYear->id) : '')) == \Vinkla\Hashids\Facades\Hashids::encode($year->id) ? 'selected' : '' }}>
                                                        {{ $year->year_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="exam_type_id_1" class="form-label">Exam Type <span class="text-danger">*</span></label>
                                            <select class="form-control select2-single" id="exam_type_id_1" name="exam_type_id_1" required>
                                                <option value="">Select Exam Type</option>
                                                @foreach($examTypes as $examType)
                                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($examType->id) }}" {{ (isset($selectedExamType1) ? $selectedExamType1 : '') == \Vinkla\Hashids\Facades\Hashids::encode($examType->id) ? 'selected' : '' }}>
                                                        {{ $examType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="text-info mb-3">Period 2</h6>
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <label for="academic_year_id_2" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                            <select class="form-control select2-single" id="academic_year_id_2" name="academic_year_id_2" required>
                                                <option value="">Select Academic Year</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($year->id) }}" {{ (isset($selectedAcademicYear2) ? $selectedAcademicYear2 : '') == \Vinkla\Hashids\Facades\Hashids::encode($year->id) ? 'selected' : '' }}>
                                                        {{ $year->year_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="exam_type_id_2" class="form-label">Exam Type <span class="text-danger">*</span></label>
                                            <select class="form-control select2-single" id="exam_type_id_2" name="exam_type_id_2" required>
                                                <option value="">Select Exam Type</option>
                                                @foreach($examTypes as $examType)
                                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($examType->id) }}" {{ (isset($selectedExamType2) ? $selectedExamType2 : '') == \Vinkla\Hashids\Facades\Hashids::encode($examType->id) ? 'selected' : '' }}>
                                                        {{ $examType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label for="class_id" class="form-label">Filter by Class (Optional)</label>
                                    <select class="form-control select2-single" id="class_id" name="class_id">
                                        <option value="">All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($class->id) }}" {{ (isset($selectedClass) ? $selectedClass : '') == \Vinkla\Hashids\Facades\Hashids::encode($class->id) ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="stream_id" class="form-label">Filter by Stream (Optional)</label>
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

                            <div class="row g-3 mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-modern">
                                        <i class="bx bx-search me-1"></i> Generate Comparison
                                    </button>
                                    @if(request()->hasAny(['academic_year_id_1', 'exam_type_id_1', 'academic_year_id_2', 'exam_type_id_2']))
                                        <a href="{{ route('school.reports.comparative-subject-performance', array_merge(request()->all(), ['export' => 'excel'])) }}" class="btn btn-success btn-modern">
                                            <i class="bx bx-download me-1"></i> Export to Excel
                                        </a>
                                        <a href="{{ route('school.reports.comparative-subject-performance.pdf') }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger btn-modern">
                                            <i class="bx bx-file me-1"></i> Export to PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($comparativeData['period1'] || $comparativeData['period2'])
        <div class="row mt-4">
            <!-- Period 1 Summary -->
            @if($comparativeData['period1'])
            <div class="col-md-6">
                <div class="card table-card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Period 1 Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="stats-card p-3 text-center">
                                    <div class="stats-icon text-primary mb-2"><i class="bx bx-book"></i></div>
                                    <h4 class="mb-1">{{ $comparativeData['period1']['summary']['total_subjects'] }}</h4>
                                    <small class="text-muted">Subjects</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-card p-3 text-center">
                                    <div class="stats-icon text-success mb-2"><i class="bx bx-group"></i></div>
                                    <h4 class="mb-1">{{ $comparativeData['period1']['summary']['total_students'] }}</h4>
                                    <small class="text-muted">Students</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="stats-card p-3 text-center">
                                    <div class="stats-icon text-info mb-2"><i class="bx bx-trending-up"></i></div>
                                    <h4 class="mb-1">{{ $comparativeData['period1']['summary']['average_score'] }}</h4>
                                    <small class="text-muted">Average Score</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Period 2 Summary -->
            @if($comparativeData['period2'])
            <div class="col-md-6">
                <div class="card table-card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">Period 2 Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="stats-card p-3 text-center">
                                    <div class="stats-icon text-primary mb-2"><i class="bx bx-book"></i></div>
                                    <h4 class="mb-1">{{ $comparativeData['period2']['summary']['total_subjects'] }}</h4>
                                    <small class="text-muted">Subjects</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stats-card p-3 text-center">
                                    <div class="stats-icon text-success mb-2"><i class="bx bx-group"></i></div>
                                    <h4 class="mb-1">{{ $comparativeData['period2']['summary']['total_students'] }}</h4>
                                    <small class="text-muted">Students</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="stats-card p-3 text-center">
                                    <div class="stats-icon text-info mb-2"><i class="bx bx-trending-up"></i></div>
                                    <h4 class="mb-1">{{ $comparativeData['period2']['summary']['average_score'] }}</h4>
                                    <small class="text-muted">Average Score</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Comparison Table -->
        @if(!empty($comparativeData['comparison']))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card table-card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">Subject Performance Comparison by Grade and Gender</h6>
                    </div>
                    <div class="card-body">
                        @foreach($comparativeData['comparison'] as $comparison)
                        <div class="mb-4">
                            <h5 class="text-primary mb-3">{{ $comparison['subject_name'] }}</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered comparison-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="period-header">GRADE</th>
                                            <th colspan="3" class="period-header">CURRENT PERIOD</th>
                                            <th colspan="3" class="period-header">PREVIOUS PERIOD</th>
                                            <th rowspan="2" class="period-header">Improvement/<br>Decline</th>
                                        </tr>
                                        <tr>
                                            <th>MALE</th>
                                            <th>FEMALE</th>
                                            <th>TOTAL</th>
                                            <th>MALE</th>
                                            <th>FEMALE</th>
                                            <th>TOTAL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $grades = $gradeLetters ?? ['A', 'B', 'C', 'D', 'F'];
                                        @endphp
                                        @foreach($grades as $grade)
                                        <tr>
                                            <td class="font-weight-bold text-center">{{ $grade }}</td>
                                            <td>{{ $comparison['period1'] ? ($comparison['period1']['grade_breakdown'][$grade]['male'] ?? 0) : '-' }}</td>
                                            <td>{{ $comparison['period1'] ? ($comparison['period1']['grade_breakdown'][$grade]['female'] ?? 0) : '-' }}</td>
                                            <td class="font-weight-bold">{{ $comparison['period1'] ? ($comparison['period1']['grade_breakdown'][$grade]['total'] ?? 0) : '-' }}</td>
                                            <td>{{ $comparison['period2'] ? ($comparison['period2']['grade_breakdown'][$grade]['male'] ?? 0) : '-' }}</td>
                                            <td>{{ $comparison['period2'] ? ($comparison['period2']['grade_breakdown'][$grade]['female'] ?? 0) : '-' }}</td>
                                            <td class="font-weight-bold">{{ $comparison['period2'] ? ($comparison['period2']['grade_breakdown'][$grade]['total'] ?? 0) : '-' }}</td>
                                            <td class="{{ ($comparison['period1'] && $comparison['period2']) ? (($comparison['period2']['grade_breakdown'][$grade]['total'] ?? 0) > ($comparison['period1']['grade_breakdown'][$grade]['total'] ?? 0) ? 'difference-positive' : 'difference-negative') : '' }}">
                                                @if($comparison['period1'] && $comparison['period2'])
                                                    @php
                                                        $diff = ($comparison['period2']['grade_breakdown'][$grade]['total'] ?? 0) - ($comparison['period1']['grade_breakdown'][$grade]['total'] ?? 0);
                                                    @endphp
                                                    {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                        <!-- Total Row -->
                                        <tr class="table-info font-weight-bold">
                                            <td class="text-center">TOTAL</td>
                                            @php
                                                $totalCurrentMale = 0;
                                                $totalCurrentFemale = 0;
                                                $totalCurrentTotal = 0;
                                                $totalPreviousMale = 0;
                                                $totalPreviousFemale = 0;
                                                $totalPreviousTotal = 0;

                                                if ($comparison['period1']) {
                                                    foreach ($grades as $grade) {
                                                        $totalCurrentMale += $comparison['period1']['grade_breakdown'][$grade]['male'] ?? 0;
                                                        $totalCurrentFemale += $comparison['period1']['grade_breakdown'][$grade]['female'] ?? 0;
                                                        $totalCurrentTotal += $comparison['period1']['grade_breakdown'][$grade]['total'] ?? 0;
                                                    }
                                                }

                                                if ($comparison['period2']) {
                                                    foreach ($grades as $grade) {
                                                        $totalPreviousMale += $comparison['period2']['grade_breakdown'][$grade]['male'] ?? 0;
                                                        $totalPreviousFemale += $comparison['period2']['grade_breakdown'][$grade]['female'] ?? 0;
                                                        $totalPreviousTotal += $comparison['period2']['grade_breakdown'][$grade]['total'] ?? 0;
                                                    }
                                                }
                                            @endphp
                                            <td>{{ $comparison['period1'] ? $totalCurrentMale : '-' }}</td>
                                            <td>{{ $comparison['period1'] ? $totalCurrentFemale : '-' }}</td>
                                            <td>{{ $comparison['period1'] ? $totalCurrentTotal : '-' }}</td>
                                            <td>{{ $comparison['period2'] ? $totalPreviousMale : '-' }}</td>
                                            <td>{{ $comparison['period2'] ? $totalPreviousFemale : '-' }}</td>
                                            <td>{{ $comparison['period2'] ? $totalPreviousTotal : '-' }}</td>
                                            <td class="{{ ($comparison['period1'] && $comparison['period2']) ? ($totalPreviousTotal > $totalCurrentTotal ? 'difference-positive' : 'difference-negative') : '' }}">
                                                @if($comparison['period1'] && $comparison['period2'])
                                                    @php
                                                        $totalDiff = $totalPreviousTotal - $totalCurrentTotal;
                                                    @endphp
                                                    {{ $totalDiff > 0 ? '+' : '' }}{{ $totalDiff }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Absent Students by Period -->
        <div class="row mt-4">
            <!-- Period 1 Absent Students -->
            @if(!empty($comparativeData['absent_students_period1']))
            <div class="col-md-6">
                <div class="card table-card">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">STUDENTS ABSENT FROM EXAMINATIONS - CURRENT PERIOD</h6>
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
                                    @foreach($comparativeData['absent_students_period1'] as $index => $absentStudent)
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
            </div>
            @endif

            <!-- Period 2 Absent Students -->
            @if(!empty($comparativeData['absent_students_period2']))
            <div class="col-md-6">
                <div class="card table-card">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0">STUDENTS ABSENT FROM EXAMINATIONS - PREVIOUS PERIOD</h6>
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
                                    @foreach($comparativeData['absent_students_period2'] as $index => $absentStudent)
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
            </div>
            @endif
        </div>

        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-single').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: function() {
                return $(this).data('placeholder') || 'Select an option';
            }
        });
    });
</script>
@endpush
@endsection