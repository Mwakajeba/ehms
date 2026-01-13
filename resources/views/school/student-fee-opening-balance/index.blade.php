@extends('layouts.main')

@section('title', 'Student Fee Opening Balance')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Student Opening Balance', 'url' => '#', 'icon' => 'bx bx-wallet']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT FEE OPENING BALANCE</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-wallet me-1 font-22 text-primary"></i>
                                <span class="h5 mb-0 text-primary">Student Fee Opening Balance</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-danger me-2" id="exportPdfBtn">
                                    <i class="bx bx-file me-1"></i> Export PDF
                                </button>
                                <button type="button" class="btn btn-success me-2" id="exportExcelBtn">
                                    <i class="bx bx-file me-1"></i> Export Excel
                                </button>
                                <a href="{{ route('school.student-fee-opening-balance.import') }}" class="btn btn-info me-2">
                                    <i class="bx bx-upload me-1"></i> Import Opening Balance
                                </a>
                                <a href="{{ route('school.student-fee-opening-balance.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add Opening Balance
                                </a>
                            </div>
                        </div>
                        <hr />

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <!-- Filter Section -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-filter me-2"></i> Filter Opening Balances
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('school.student-fee-opening-balance.index') }}" id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="academic_year_id" class="form-label fw-bold">Academic Year</label>
                                            <select class="form-select" id="academic_year_id" name="academic_year_id">
                                                <option value="">All Academic Years</option>
                                                @foreach($academicYears as $year)
                                                    @php
                                                        $isSelected = request('academic_year_id') 
                                                            ? (request('academic_year_id') == $year->id) 
                                                            : ($currentAcademicYear && $currentAcademicYear->id == $year->id);
                                                    @endphp
                                                    <option value="{{ $year->id }}" {{ $isSelected ? 'selected' : '' }}>{{ $year->year_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="class_id" class="form-label fw-bold">Class</label>
                                            <select class="form-select" id="class_id" name="class_id">
                                                <option value="">All Classes</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="stream_id" class="form-label fw-bold">Stream</label>
                                            <select class="form-select" id="stream_id" name="stream_id">
                                                <option value="">All Streams</option>
                                                @if(request('stream_id'))
                                                    @php
                                                        $selectedStream = \App\Models\School\Stream::find(request('stream_id'));
                                                    @endphp
                                                    @if($selectedStream)
                                                        <option value="{{ $selectedStream->id }}" selected>{{ $selectedStream->name }}</option>
                                                    @endif
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="status" class="form-label fw-bold">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">All Statuses</option>
                                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                                <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                            </select>
                                        </div>
                                        <div class="col-12 d-flex justify-content-end gap-2">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-search me-1"></i> Filter
                                            </button>
                                            <a href="{{ route('school.student-fee-opening-balance.index') }}" class="btn btn-outline-secondary">
                                                <i class="bx bx-refresh me-1"></i> Clear
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="openingBalanceTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Student Name</th>
                                        <th>Admission No.</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Academic Year</th>
                                        <th>Opening Date</th>
                                        <th>Opening Balance</th>
                                        <th>Paid Amount</th>
                                        <th>Balance Due</th>
                                        <th>Control Number</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
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
    // Store DataTable instance
    window.openingBalanceTable = $('#openingBalanceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.student-fee-opening-balance.data") }}',
            type: 'GET',
            data: function(d) {
                const academicYearId = $('#academic_year_id').val();
                d.academic_year_id = academicYearId || '{{ $currentAcademicYear ? $currentAcademicYear->id : "" }}';
                d.class_id = $('#class_id').val();
                d.stream_id = $('#stream_id').val();
                d.status = $('#status').val();
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables error:', error);
                console.error('Response:', xhr.responseText);
                alert('Error loading data. Please check the console for details.');
                // Reset button state on error
                $('#filterForm button[type="submit"]').prop('disabled', false).html('<i class="bx bx-search me-1"></i> Filter');
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name' },
            { data: 'admission_number', name: 'admission_number' },
            { data: 'class', name: 'class', orderable: false },
            { data: 'stream', name: 'stream', orderable: false },
            { data: 'academic_year', name: 'academic_year' },
            { data: 'opening_date', name: 'opening_date' },
            { data: 'opening_balance', name: 'opening_balance' },
            { data: 'paid_amount', name: 'paid_amount' },
            { data: 'balance_due', name: 'balance_due' },
            { data: 'lipisha_control_number', name: 'lipisha_control_number', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[4, 'desc']],
        responsive: true,
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
        }
    });

    // Function to load streams for a class
    function loadStreamsForClass(classId) {
        const streamSelect = $('#stream_id');

        // Show loading state
        streamSelect.html('<option value="">Loading streams...</option>');
        streamSelect.prop('disabled', true);

        // Make AJAX call to get streams for this class
        $.ajax({
            url: '{{ route("school.api.students.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                // Clear loading option
                streamSelect.empty();
                streamSelect.append('<option value="">All Streams</option>');

                if (response.streams && response.streams.length > 0) {
                    // Add stream options
                    response.streams.forEach(function(stream) {
                        const selected = '{{ request("stream_id") }}' == stream.id ? 'selected' : '';
                        streamSelect.append(`<option value="${stream.id}" ${selected}>${stream.name}</option>`);
                    });
                    streamSelect.prop('disabled', false);
                } else {
                    streamSelect.append('<option value="">No streams available</option>');
                    streamSelect.prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.html('<option value="">Error loading streams</option>');
                streamSelect.prop('disabled', true);
            }
        });
    }

    // Class change handler
    $('#class_id').on('change', function() {
        const classId = $(this).val();
        if (classId) {
            loadStreamsForClass(classId);
        } else {
            $('#stream_id').html('<option value="">All Streams</option>').prop('disabled', false);
        }
    });

    // Load streams on page load if class is selected
    @if(request('class_id'))
        loadStreamsForClass('{{ request("class_id") }}');
    @endif

    // Reload DataTable with new filter parameters
    function reloadDataTable() {
        if (window.openingBalanceTable) {
            window.openingBalanceTable.ajax.reload(function() {
                // Reset filter button state after reload completes
                $('#filterForm button[type="submit"]').prop('disabled', false).html('<i class="bx bx-search me-1"></i> Filter');
            }, false); // false parameter prevents resetting paging
        }
    }

    // Reload table when filter form is submitted
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Filtering...');
        reloadDataTable();
    });

    // Export PDF
    $('#exportPdfBtn').on('click', function() {
        const academicYearId = $('#academic_year_id').val() || '{{ $currentAcademicYear ? $currentAcademicYear->id : "" }}';
        const classId = $('#class_id').val() || '';
        const streamId = $('#stream_id').val() || '';
        const status = $('#status').val() || '';

        let url = '{{ route("school.student-fee-opening-balance.export.pdf") }}?';
        url += 'academic_year_id=' + academicYearId;
        if (classId) url += '&class_id=' + classId;
        if (streamId) url += '&stream_id=' + streamId;
        if (status) url += '&status=' + status;

        window.open(url, '_blank');
    });

    // Export Excel
    $('#exportExcelBtn').on('click', function() {
        const academicYearId = $('#academic_year_id').val() || '{{ $currentAcademicYear ? $currentAcademicYear->id : "" }}';
        const classId = $('#class_id').val() || '';
        const streamId = $('#stream_id').val() || '';
        const status = $('#status').val() || '';

        let url = '{{ route("school.student-fee-opening-balance.export.excel") }}?';
        url += 'academic_year_id=' + academicYearId;
        if (classId) url += '&class_id=' + classId;
        if (streamId) url += '&stream_id=' + streamId;
        if (status) url += '&status=' + status;

        window.location.href = url;
    });
});
</script>
@endpush

