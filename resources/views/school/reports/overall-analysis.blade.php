@extends('layouts.main')

@section('title', 'Overall Analysis Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Overall Analysis Report', 'url' => '#', 'icon' => 'bx bx-trending-up']
        ]" />
        <h6 class="mb-0 text-uppercase">OVERALL ANALYSIS REPORT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <!-- Filters Card -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-filter-alt me-2"></i>Filters</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('school.reports.overall-analysis') }}" class="mb-0" id="filterForm">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="academic_year_id">Academic Year</label>
                                    <select name="academic_year_id" id="academic_year_id" class="form-control select2-single" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($year->id) }}" {{ (isset($selectedAcademicYear) ? $selectedAcademicYear : ($currentAcademicYear ? \Vinkla\Hashids\Facades\Hashids::encode($currentAcademicYear->id) : '')) == \Vinkla\Hashids\Facades\Hashids::encode($year->id) ? 'selected' : '' }}>
                                                {{ $year->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="exam_type_id">Exam Type</label>
                                    <select name="exam_type_id" id="exam_type_id" class="form-control select2-single" required>
                                        <option value="">Select Exam Type</option>
                                        @foreach($examTypes as $examType)
                                            <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($examType->id) }}" {{ (isset($selectedExamType) ? $selectedExamType : '') == \Vinkla\Hashids\Facades\Hashids::encode($examType->id) ? 'selected' : '' }}>
                                                {{ $examType->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="class_id">Class</label>
                                    <select name="class_id" id="class_id" class="form-control select2-single" data-placeholder="All Classes">
                                        <option value="" {{ (empty($selectedClass) || $selectedClass == '') ? 'selected' : '' }}>All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($class->id) }}" {{ (isset($selectedClass) && $selectedClass != '' && $selectedClass == \Vinkla\Hashids\Facades\Hashids::encode($class->id)) ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-block flex-fill">
                                            <i class="fas fa-search"></i> Generate Report
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label>&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-secondary btn-block flex-fill" onclick="clearFilters()">
                                            <i class="fas fa-eraser"></i> Clear Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if(request()->has('exam_type_id') && request()->has('academic_year_id') && !empty($analysisData['analysis']))
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('school.reports.overall-analysis', [
                                'academic_year_id' => $selectedAcademicYear ?? request('academic_year_id'),
                                'exam_type_id' => $selectedExamType ?? request('exam_type_id'),
                                'class_id' => $selectedClass ?? request('class_id'),
                                'stream_id' => $selectedStream ?? request('stream_id'),
                                'export' => 'pdf'
                            ]) }}" class="btn btn-success">
                                <i class="bx bx-file-pdf me-1"></i> Export to PDF
                            </a>
                            <a href="{{ route('school.reports.overall-analysis', [
                                'academic_year_id' => $selectedAcademicYear ?? request('academic_year_id'),
                                'exam_type_id' => $selectedExamType ?? request('exam_type_id'),
                                'class_id' => $selectedClass ?? request('class_id'),
                                'stream_id' => $selectedStream ?? request('stream_id'),
                                'export' => 'excel'
                            ]) }}" class="btn btn-success">
                                <i class="bx bx-file-excel me-1"></i> Export to Excel
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Report Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-trending-up me-1 font-22 text-dark"></i></div>
                            <h5 class="mb-0 text-dark">Overall Analysis Report</h5>
                        </div>
                        <hr />

                    @if(!empty($analysisData['analysis']))
                        @php
                            $gradeLetters = $analysisData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'F'];
                        @endphp
                        <!-- Summary Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card text-center border-primary">
                                    <div class="card-body">
                                        <div class="text-primary mb-2">
                                            <i class="bx bx-group bx-lg"></i>
                                        </div>
                                        <h4 class="mb-1">{{ $analysisData['grandTotal']['students'] }}</h4>
                                        <p class="text-muted mb-0 small">Total Students</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center border-success">
                                    <div class="card-body">
                                        <div class="text-success mb-2">
                                            <i class="bx bx-trending-up bx-lg"></i>
                                        </div>
                                        <h4 class="mb-1">{{ number_format($analysisData['grandTotal']['total_mean'], 2) }}</h4>
                                        <p class="text-muted mb-0 small">Overall Mean</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center border-info">
                                    <div class="card-body">
                                        <div class="text-info mb-2">
                                            <i class="bx bx-award bx-lg"></i>
                                        </div>
                                        <h4 class="mb-1" style="color: black;">{{ str_replace('>', '', $analysisData['grandTotal']['grade']) }}</h4>
                                        <p class="text-muted mb-0 small">Overall Grade</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center border-warning">
                                    <div class="card-body">
                                        <div class="text-warning mb-2">
                                            <i class="bx bx-target bx-lg"></i>
                                        </div>
                                        <h4 class="mb-1">{{ count($analysisData['analysis']) }}</h4>
                                        <p class="text-muted mb-0 small">Classes/Streams</p>
                                    </div>
                                </div>
                            </div>
                        </div>



                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="overallAnalysisTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">Class</th>
                                        <th class="text-center">Stream</th>
                                        <th class="text-center">Students</th>
                                        @foreach($gradeLetters as $grade)
                                        <th class="text-center">{{ $grade }}</th>
                                        @endforeach
                                        <th class="text-center">Class Mean</th>
                                        <th class="text-center">Grade</th>
                                        <th class="text-center">Class Teacher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $currentClassId = null;
                                    @endphp
                                    @foreach($analysisData['analysis'] as $item)
                                        @if($currentClassId !== null && $currentClassId != $item['class']->id)
                                            @php
                                                $classSubtotal = $analysisData['classSubtotals'][$currentClassId] ?? null;
                                            @endphp
                                            @if($classSubtotal)
                                            <tr class="table-info">
                                                <td class="text-right font-weight-bold" colspan="2">SUBTOTAL - {{ $classSubtotal['class']->name }}</td>
                                                <td class="text-center font-weight-bold">{{ $classSubtotal['students'] }}</td>
                                                @foreach($gradeLetters as $grade)
                                                <td class="text-center font-weight-bold">{{ $classSubtotal['grade_counts'][$grade] ?? 0 }}</td>
                                                @endforeach
                                                <td class="text-center font-weight-bold">{{ number_format($classSubtotal['total_mean'], 2) }}</td>
                                                <td class="text-center font-weight-bold">
                                                    <span class="text-dark font-weight-bold" style="color: black;">
                                                        {{ str_replace('>', '', $classSubtotal['grade']) }}
                                                    </span>
                                                </td>
                                                <td></td>
                                            </tr>
                                            @endif
                                        @endif
                                    <tr>
                                        <td class="text-center">{{ $item['class']->name }}</td>
                                        <td class="text-center">{{ $item['stream']->name }}</td>
                                        <td class="text-center">{{ $item['students'] }}</td>
                                        @foreach($gradeLetters as $grade)
                                        <td class="text-center">{{ $item['grade_counts'][$grade] ?? 0 }}</td>
                                        @endforeach
                                        <td class="text-center">{{ number_format($item['class_mean'], 2) }}</td>
                                        <td class="text-center">
                                            <span class="text-dark font-weight-bold" style="color: black;">
                                                {{ str_replace('>', '', $item['grade']) }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ $item['class_teacher'] }}</td>
                                    </tr>
                                    @php
                                        $currentClassId = $item['class']->id;
                                    @endphp
                                    @endforeach
                                    
                                    @if($currentClassId !== null)
                                        @php
                                            $classSubtotal = $analysisData['classSubtotals'][$currentClassId] ?? null;
                                        @endphp
                                        @if($classSubtotal)
                                        <tr class="table-info">
                                            <td class="text-right font-weight-bold" colspan="2">SUBTOTAL - {{ $classSubtotal['class']->name }}</td>
                                            <td class="text-center font-weight-bold">{{ $classSubtotal['students'] }}</td>
                                            @foreach($gradeLetters as $grade)
                                            <td class="text-center font-weight-bold">{{ $classSubtotal['grade_counts'][$grade] ?? 0 }}</td>
                                            @endforeach
                                            <td class="text-center font-weight-bold">{{ number_format($classSubtotal['total_mean'], 2) }}</td>
                                            <td class="text-center font-weight-bold">
                                                <span class="text-dark font-weight-bold" style="color: black;">
                                                    {{ str_replace('>', '', $classSubtotal['grade']) }}
                                                </span>
                                            </td>
                                            <td></td>
                                        </tr>
                                        @endif
                                    @endif

                                    <!-- Subtotals -->
                                    @foreach($analysisData['subtotals'] as $categoryName => $subtotal)
                                    <tr class="table-warning">
                                        <td colspan="2" class="text-right font-weight-bold">SUBTOTAL - {{ $categoryName }}</td>
                                        <td class="text-center font-weight-bold">{{ $subtotal['students'] }}</td>
                                        @foreach($gradeLetters as $grade)
                                        <td class="text-center font-weight-bold">{{ $subtotal['grade_counts'][$grade] ?? 0 }}</td>
                                        @endforeach
                                        <td class="text-center font-weight-bold">{{ number_format($subtotal['total_mean'], 2) }}</td>
                                        <td class="text-center font-weight-bold">
                                            <span class="badge badge-{{ \App\Http\Controllers\School\SchoolReportsController::getGradeBadgeClassStatic($subtotal['grade']) }}">
                                                {{ $subtotal['grade'] }}
                                            </span>
                                        </td>
                                        <td></td>
                                    </tr>
                                    @endforeach

                                    <!-- Grand Total -->
                                    <tr class="table-primary" style="font-weight: bolder; font-size: 1.1em;">
                                        <td colspan="2" class="text-right font-weight-bolder">GRAND TOTAL</td>
                                        <td class="text-center font-weight-bolder">{{ $analysisData['grandTotal']['students'] }}</td>
                                        @foreach($gradeLetters as $grade)
                                        <td class="text-center font-weight-bolder">{{ $analysisData['grandTotal']['grade_counts'][$grade] ?? 0 }}</td>
                                        @endforeach
                                        <td class="text-center font-weight-bolder">{{ number_format($analysisData['grandTotal']['total_mean'], 2) }}</td>
                                        <td class="text-center font-weight-bolder">
                                            <span class="text-dark font-weight-bolder" style="color: black;">
                                                {{ str_replace('>', '', $analysisData['grandTotal']['grade']) }}
                                            </span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Please select academic year and exam type to generate the overall analysis report.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for all filter dropdowns
    $('.select2-single').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true
    });
    
    // Special configuration for class_id to ensure "All Classes" is visible
    $('#class_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: false,
        placeholder: {
            id: '',
            text: 'All Classes'
        }
    });

    @if(!empty($analysisData['analysis']))
    // Initialize DataTable
    @php
        $gradeLetters = $analysisData['gradeLetters'] ?? ['A', 'B', 'C', 'D', 'F'];
        $gradeColumnsCount = count($gradeLetters);
        $classTeacherColumnIndex = 3 + $gradeColumnsCount + 2; // Class + Stream + Students + Grade columns + Class Mean + Grade
    @endphp
    $('#overallAnalysisTable').DataTable({
        "pageLength": 25,
        "ordering": true,
        "searching": true,
        "paging": true,
        "responsive": true,
        "order": [[0, 'asc']],
        "columnDefs": [
            { "orderable": false, "targets": [{{ $classTeacherColumnIndex }}] } // Class Teacher column
        ]
    });
    @endif
});

// Clear filters function
function clearFilters() {
    // Reset all select2 elements
    $('#academic_year_id').val('').trigger('change');
    $('#exam_type_id').val('').trigger('change');
    $('#class_id').val('').trigger('change');

    // Submit the form to clear the report
    $('#filterForm').attr('action', '{{ route("school.reports.overall-analysis") }}').submit();
}
</script>
@endsection