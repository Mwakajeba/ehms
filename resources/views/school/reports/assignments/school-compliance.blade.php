@extends('layouts.main')

@section('title', 'School-Wide Compliance Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'School-Wide Compliance', 'url' => '#', 'icon' => 'bx bx-check-circle']
        ]" />
        <h6 class="mb-0 text-uppercase">SCHOOL-WIDE COMPLIANCE REPORT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-check-circle me-1 font-22 text-success"></i></div>
                            <h5 class="mb-0 text-success">School-Wide Compliance Report</h5>
                        </div>
                        <hr />

                        <!-- Filters -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Filters</h6>
                                <form id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Academic Year</label>
                                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                                <option value="">All Academic Years</option>
                                                @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}" {{ $currentAcademicYear && $currentAcademicYear->id == $year->id ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Class</label>
                                            <select class="form-select" id="class_id" name="class_id">
                                                <option value="">All Classes</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Date From</label>
                                            <input type="date" class="form-control" id="date_from" name="date_from">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Date To</label>
                                            <input type="date" class="form-control" id="date_to" name="date_to">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">&nbsp;</label>
                                            <div>
                                                <button type="button" class="btn btn-primary w-100" id="generateReportBtn">
                                                    <i class="bx bx-search me-1"></i> Generate Report
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Report Results -->
                        <div id="reportResults" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="card-title mb-0">Report Results</h6>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-success" id="exportExcelBtn">
                                                <i class="bx bx-file me-1"></i> Export Excel
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" id="exportPdfBtn">
                                                <i class="bx bx-file me-1"></i> Export PDF
                                            </button>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="complianceTable" class="table table-striped table-bordered">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Metric</th>
                                                    <th>Value</th>
                                                    <th>Target</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Data will be loaded here -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Placeholder -->
                        <div id="reportPlaceholder" class="text-center py-5">
                            <i class="bx bx-check-circle fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">School-Wide Compliance Report</h5>
                            <p class="text-muted">Select filters and click "Generate Report" to view compliance metrics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        let table;

        $('#academic_year_id, #class_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select...',
            allowClear: true
        });

        $('#generateReportBtn').on('click', function() {
            if (table) {
                table.destroy();
            }

            $('#reportPlaceholder').hide();
            $('#reportResults').show();

            table = $('#complianceTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('school.reports.school-compliance') }}",
                    data: function(d) {
                        d.academic_year_id = $('#academic_year_id').val();
                        d.class_id = $('#class_id').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'metric', name: 'metric' },
                    { data: 'value', name: 'value' },
                    { data: 'target', name: 'target' },
                    { 
                        data: 'status', 
                        name: 'status',
                        render: function(data) {
                            if (data === 'success') {
                                return '<span class="badge bg-success"><i class="bx bx-check"></i> Good</span>';
                            } else if (data === 'warning') {
                                return '<span class="badge bg-warning"><i class="bx bx-error"></i> Warning</span>';
                            } else if (data === 'danger') {
                                return '<span class="badge bg-danger"><i class="bx bx-x"></i> Critical</span>';
                            } else {
                                return '<span class="badge bg-info"><i class="bx bx-info-circle"></i> Info</span>';
                            }
                        }
                    }
                ],
                order: [[1, 'asc']],
                pageLength: 25,
                searching: false
            });
        });

        $('#exportExcelBtn').on('click', function() {
            const params = new URLSearchParams({
                export: 'excel',
                academic_year_id: $('#academic_year_id').val() || '',
                class_id: $('#class_id').val() || '',
                date_from: $('#date_from').val() || '',
                date_to: $('#date_to').val() || ''
            });
            window.location.href = "{{ route('school.reports.school-compliance') }}?" + params.toString();
        });

        $('#exportPdfBtn').on('click', function() {
            const params = new URLSearchParams({
                export: 'pdf',
                academic_year_id: $('#academic_year_id').val() || '',
                class_id: $('#class_id').val() || '',
                date_from: $('#date_from').val() || '',
                date_to: $('#date_to').val() || ''
            });
            window.location.href = "{{ route('school.reports.school-compliance') }}?" + params.toString();
        });
    });
</script>
@endpush
