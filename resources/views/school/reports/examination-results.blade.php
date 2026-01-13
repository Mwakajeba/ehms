@extends('layouts.main')

@section('title', 'Examination Results Report')

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

    .position-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 4px;
        min-width: 2rem;
        text-align: center;
    }

    .position-1 { background-color: #ffd700; color: #000; }
    .position-2 { background-color: #c0c0c0; color: #000; }
    .position-3 { background-color: #cd7f32; color: #fff; }
    .position-other { background-color: #e9ecef; color: #495057; }

    .grade-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 4px;
        min-width: 2rem;
        text-align: center;
    }

    .grade-A { background-color: #28a745; color: #fff; }
    .grade-B { background-color: #007bff; color: #fff; }
    .grade-C { background-color: #ffc107; color: #000; }
    .grade-D { background-color: #fd7e14; color: #fff; }
    .grade-E { background-color: #dc3545; color: #fff; }

    .subject-header {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        white-space: nowrap;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.5rem;
        text-align: center;
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
            ['label' => 'Examination Results Report', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />
        <h6 class="mb-0 text-uppercase">EXAMINATION RESULTS REPORT</h6>
        <hr />

        <!-- Filters -->
        <div class="card filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('school.reports.examination-results') }}" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="academic_year_id" class="form-label">Academic Year</label>
                            <select class="form-select select2" id="academic_year_id" name="academic_year_id">
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
                            <select class="form-select select2" id="exam_type_id" name="exam_type_id" required>
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
                            <select class="form-select select2" id="class_id" name="class_id">
                                <option value="">All Classes</option>
                                @foreach($classes as $class)
                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($class->id) }}" {{ (isset($selectedClass) && $selectedClass != '' && $selectedClass == \Vinkla\Hashids\Facades\Hashids::encode($class->id)) ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="stream_id" class="form-label">Stream</label>
                            <select class="form-select select2" id="stream_id" name="stream_id">
                                <option value="">All Streams</option>
                                @foreach($streams as $stream)
                                    <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($stream->id) }}" {{ (isset($selectedStream) && $selectedStream != '' && $selectedStream == \Vinkla\Hashids\Facades\Hashids::encode($stream->id)) ? 'selected' : '' }}>
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
                                <a href="{{ route('school.reports.examination-results') }}" class="btn btn-secondary btn-modern me-2">
                                    <i class="bx bx-reset me-1"></i> Clear Filters
                                </a>

                                @if(isset($selectedExamType) && isset($selectedAcademicYear))
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-modern dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bx bx-download me-1"></i> Export
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('school.reports.examination-results', [
                                            'academic_year_id' => $selectedAcademicYear ?? request('academic_year_id'),
                                            'exam_type_id' => $selectedExamType ?? request('exam_type_id'),
                                            'class_id' => $selectedClass ?? request('class_id'),
                                            'stream_id' => $selectedStream ?? request('stream_id'),
                                            'export' => 'pdf'
                                        ]) }}">
                                            <i class="bx bx-file-pdf me-2"></i> Export to PDF
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('school.reports.examination-results', [
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
        </div>

        @if(request()->has('exam_type_id'))
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-group"></i>
                            </div>
                            <h4 class="mb-1">{{ count($examData['results']) }}</h4>
                            <p class="text-muted mb-0 small">Total Students</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-trending-up"></i>
                            </div>
                            <h4 class="mb-1">{{ $examData['classAverage'] }}</h4>
                            <p class="text-muted mb-0 small">Class Average</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-award"></i>
                            </div>
                            <h4 class="mb-1">{{ $examData['classGrade'] }}</h4>
                            <p class="text-muted mb-0 small">Class Grade</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card stats-card text-center">
                        <div class="card-body">
                            <div class="stats-icon mb-2">
                                <i class="bx bx-target"></i>
                            </div>
                            <h4 class="mb-1">{{ $examData['classTotal'] }}</h4>
                            <p class="text-muted mb-0 small">Total Marks</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overall Performance Summary -->
            @if(!empty($examData['results']))
            <div class="card table-card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">OVERALL PERFORMANCE LEVELS SUMMARY</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>GENDER</th>
                                    @php
                                        $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'F'];
                                    @endphp
                                    @foreach($gradeLetters as $grade)
                                    <th class="text-center">{{ $grade }}</th>
                                    @endforeach
                                    <th class="text-center">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $girlsGrades = [];
                                    $boysGrades = [];
                                    foreach ($gradeLetters as $letter) {
                                        $girlsGrades[$letter] = 0;
                                        $boysGrades[$letter] = 0;
                                    }
                                    $totalGirls = 0;
                                    $totalBoys = 0;

                                    foreach($examData['results'] as $result) {
                                        $grade = $result['grade'] ?? 'N/A';
                                        $gender = strtolower($result['student']->gender ?? '');

                                        // Handle different gender representations
                                        $isFemale = in_array($gender, ['f', 'female', 'woman', 'girl']);
                                        $isMale = in_array($gender, ['m', 'male', 'man', 'boy']);

                                        if ($isFemale && isset($girlsGrades[$grade])) {
                                            $girlsGrades[$grade]++;
                                            $totalGirls++;
                                        } elseif ($isMale && isset($boysGrades[$grade])) {
                                            $boysGrades[$grade]++;
                                            $totalBoys++;
                                        }
                                    }

                                    $totalGrades = [];
                                    foreach ($gradeLetters as $letter) {
                                        $totalGrades[$letter] = ($girlsGrades[$letter] ?? 0) + ($boysGrades[$letter] ?? 0);
                                    }
                                    $grandTotal = $totalGirls + $totalBoys;
                                @endphp
                                <tr>
                                    <td><strong>GIRLS</strong></td>
                                    @foreach($gradeLetters as $grade)
                                    <td class="text-center">{{ $girlsGrades[$grade] ?? 0 }}</td>
                                    @endforeach
                                    <td class="text-center"><strong>{{ $totalGirls }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>BOYS</strong></td>
                                    @foreach($gradeLetters as $grade)
                                    <td class="text-center">{{ $boysGrades[$grade] ?? 0 }}</td>
                                    @endforeach
                                    <td class="text-center"><strong>{{ $totalBoys }}</strong></td>
                                </tr>
                                <tr class="table-primary">
                                    <td><strong>TOTAL</strong></td>
                                    @foreach($gradeLetters as $grade)
                                    <td class="text-center"><strong>{{ $totalGrades[$grade] ?? 0 }}</strong></td>
                                    @endforeach
                                    <td class="text-center"><strong>{{ $grandTotal }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Results Table -->
            <div class="card table-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="resultsTable">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>NAME</th>
                                    <th class="text-center">STR</th>
                                    <th class="text-center">SEX</th>
                                    @foreach($examData['subjects'] as $subject)
                                        <th class="text-center subject-header">{{ $subject->short_name ?? substr($subject->name, 0, 6) }}</th>
                                    @endforeach
                                    <th class="text-center">TOTAL</th>
                                    <th class="text-center">AVR</th>
                                    <th class="text-center">GRD</th>
                                    <th class="text-center">POS</th>
                                    <th>REMARK</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($examData['results'] as $index => $result)
                                    <tr>
                                        <td class="text-center">
                                            <span class="serial-number">{{ $index + 1 }}</span>
                                        </td>
                                        <td>{{ $result['student']->first_name }} {{ $result['student']->last_name }}</td>
                                        <td class="text-center">{{ $result['student']->stream ? $result['student']->stream->name : '-' }}</td>
                                        <td class="text-center">{{ ucfirst(substr($result['student']->gender, 0, 1)) }}</td>
                                        @foreach($examData['subjects'] as $subject)
                                            @php
                                                $mark = $result['marks'][$subject->id] ?? '-';
                                                $markDisplay = $mark;
                                                $markClass = '';
                                                
                                                if ($mark === 'ABS') {
                                                    $markDisplay = '<span class="text-danger fw-bold">ABS</span>';
                                                    $markClass = 'table-danger';
                                                } elseif ($mark === 'EXEMPT') {
                                                    $markDisplay = '<span class="text-warning fw-bold">EXEMPT</span>';
                                                    $markClass = 'table-warning';
                                                }
                                            @endphp
                                            <td class="text-center {{ $markClass }}" {!! $markClass ? 'title="' . ($mark === 'ABS' ? 'Absent' : 'Exempted') . '"' : '' !!}>
                                                {!! $markDisplay !!}
                                            </td>
                                        @endforeach
                                        <td class="text-center fw-bold">{{ $result['total'] }}</td>
                                        <td class="text-center">{{ $result['average'] }}</td>
                                        <td class="text-center">
                                            <span class="grade-badge grade-{{ $result['grade'] }}">
                                                {{ $result['grade'] }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $result['position'] }}</td>
                                        <td>{{ $result['remark'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="summary-row">
                                    <td colspan="4" class="text-end fw-bold">TOTAL</td>
                                    @foreach($examData['subjects'] as $subject)
                                        <td class="text-center fw-bold">{{ $examData['subjectTotals'][$subject->id] ?? 0 }}</td>
                                    @endforeach
                                    <td class="text-center fw-bold">{{ $examData['classTotal'] }}</td>
                                    <td colspan="4"></td>
                                </tr>
                                <tr class="summary-row">
                                    <td colspan="4" class="text-end fw-bold">AVERAGE</td>
                                    @foreach($examData['subjects'] as $subject)
                                        <td class="text-center fw-bold">{{ $examData['subjectAverages'][$subject->id] ?? '-' }}</td>
                                    @endforeach
                                    <td class="text-center fw-bold">{{ $examData['classAverage'] }}</td>
                                    <td colspan="4"></td>
                                </tr>
                                <tr class="summary-row">
                                    <td colspan="4" class="text-end fw-bold">GRADE</td>
                                    @foreach($examData['subjects'] as $subject)
                                        <td class="text-center fw-bold">
                                            <span class="grade-badge grade-{{ $examData['subjectGrades'][$subject->id] }}">
                                                {{ $examData['subjectGrades'][$subject->id] }}
                                            </span>
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-bold">
                                        <span class="grade-badge grade-{{ $examData['classGrade'] }}">
                                            {{ $examData['classGrade'] }}
                                        </span>
                                    </td>
                                    <td colspan="4"></td>
                                </tr>
                                <tr class="summary-row">
                                    <td colspan="4" class="text-end fw-bold">POSITION</td>
                                    @foreach($examData['subjects'] as $subject)
                                        <td class="text-center fw-bold">{{ $examData['subjectPositions'][$subject->id] }}</td>
                                    @endforeach
                                    <td colspan="4"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Subject Performance Analysis -->
            @if(isset($examData['subjectPerformance']['by_stream']) && $examData['subjectPerformance']['by_stream'])
                @foreach($examData['subjectPerformance']['streams'] as $streamId => $streamData)
                    <div class="card table-card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">SUBJECTS PERFORMANCE ANALYSIS - {{ $streamData['stream']->name ?? 'Unknown Stream' }} - TERMINAL EXAMINATION</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>SUBJECT NAME</th>
                                            <th>TEACHER'S NAME</th>
                                            @php
                                                $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'F'];
                                            @endphp
                                            @foreach($gradeLetters as $grade)
                                                <th class="text-center">{{ $grade }}</th>
                                            @endforeach
                                            <th class="text-center">TOTAL</th>
                                            <th class="text-center">GPA</th>
                                            <th class="text-center">GRADE</th>
                                            <th>COMPETENCY LEVEL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($streamData['subjects'] as $subjectId => $performance)
                                            <tr>
                                                <td>{{ $performance['subject']->name }}</td>
                                                <td>
                                                    {{ $performance['teacher'] }}
                                                    @if(isset($performance['teacher_stream']) && $performance['teacher_stream'])
                                                        <br><small class="text-muted">({{ $performance['teacher_stream'] }})</small>
                                                    @endif
                                                </td>
                                                @php
                                                    $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'F'];
                                                @endphp
                                                @foreach($gradeLetters as $grade)
                                                    <td class="text-center">{{ $performance['gradeCounts'][$grade] ?? 0 }}</td>
                                                @endforeach
                                                <td class="text-center">{{ $performance['total'] }}</td>
                                                <td class="text-center">{{ number_format($performance['gpa'], 4) }}</td>
                                                <td class="text-center">
                                                    @if(isset($performance['subjectGrade']))
                                                        <span class="grade-badge grade-{{ $performance['subjectGrade'] }}">
                                                            {{ $performance['subjectGrade'] }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $performance['competencyLevel'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="card table-card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0">CLASS SUBJECTS PERFORMANCE ANALYSIS - TERMINAL EXAMINATION</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>SUBJECT NAME</th>
                                        <th>TEACHER'S NAME</th>
                                        @php
                                            $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'F'];
                                        @endphp
                                        @foreach($gradeLetters as $grade)
                                            <th class="text-center">{{ $grade }}</th>
                                        @endforeach
                                        <th class="text-center">TOTAL</th>
                                        <th class="text-center">GPA</th>
                                        <th class="text-center">GRADE</th>
                                        <th>COMPETENCY LEVEL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examData['subjects'] as $subject)
                                        @php
                                            $performance = $examData['subjectPerformance'][$subject->id] ?? null;
                                        @endphp
                                        <tr>
                                            <td>{{ $subject->name }}</td>
                                            <td>-</td>
                                            @php
                                                $gradeLetters = $examData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'F'];
                                            @endphp
                                            @foreach($gradeLetters as $grade)
                                                <td class="text-center">{{ $performance ? ($performance['gradeCounts'][$grade] ?? 0) : '-' }}</td>
                                            @endforeach
                                            <td class="text-center">{{ $performance ? $performance['total'] : '-' }}</td>
                                            <td class="text-center">{{ $performance ? number_format($performance['gpa'], 4) : '-' }}</td>
                                            <td class="text-center">
                                                @if($performance && isset($performance['subjectGrade']))
                                                    <span class="grade-badge grade-{{ $performance['subjectGrade'] }}">
                                                        {{ $performance['subjectGrade'] }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $performance ? $performance['competencyLevel'] : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Absent Students -->
            @if(!empty($examData['absentStudents']))
                <div class="card table-card mt-4">
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
                                    @foreach($examData['absentStudents'] as $index => $absentStudent)
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
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-info-circle fs-1 text-info mb-3"></i>
                    <h5>Please select an Exam Type to generate the report</h5>
                    <p class="text-muted">Choose an academic year and exam type from the filters above to view examination results.</p>
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

    // Initialize DataTable
    $('#resultsTable').DataTable({
        responsive: true,
        paging: true,
        searching: true,
        ordering: false, // Disable client-side sorting since data is pre-sorted
        info: true,
        pageLength: 25,
        columnDefs: [
            { className: 'text-center', targets: '_all' }
        ],
        language: {
            search: "Search students:",
            lengthMenu: "Show _MENU_ students per page",
            info: "Showing _START_ to _END_ of _TOTAL_ students",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Form will only submit when "Generate Report" button is clicked
});
</script>
@endpush