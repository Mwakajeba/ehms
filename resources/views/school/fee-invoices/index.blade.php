@extends('layouts.main')

@section('title', 'School Fee Invoices')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Management', 'url' => route('school.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Fee Invoices', 'url' => '#', 'icon' => 'bx bx-receipt']
        ]" />

        <h6 class="mb-0 text-uppercase">SCHOOL FEE INVOICES MANAGEMENT</h6>
        <hr />

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ number_format($totalInvoices) }}</h4>
                                <p class="mb-0">Total Invoices</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-receipt bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ number_format($issuedInvoices) }}</h4>
                                <p class="mb-0">Issued Invoices</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-file bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ number_format($paidInvoices) }}</h4>
                                <p class="mb-0">Paid Invoices</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-check-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h4 class="mb-1">{{ number_format($overdueInvoices) }}</h4>
                                <p class="mb-0">Overdue Invoices</p>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bx bx-error-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-list-ul me-2"></i>Fee Invoices List
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('school.fee-invoices.create') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-plus me-1"></i> Generate Bulk Invoices
                    </a>
                    <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#bulkInvoicePdfModal">
                        <i class="bx bx-file me-1"></i> Bulk Invoice PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="exportFeeInvoicesToExcel()">
                        <i class="bx bx-download me-1"></i> Export Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="exportFeeInvoicesToPDF()">
                        <i class="bx bx-file me-1"></i> Export PDF
                    </button>
                </div>
            </div>
            <div class="card-body">
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

                <!-- Filters Section -->
                <div class="card border-info mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-filter me-2"></i>Filters & Search
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="filterForm">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label for="class_id" class="form-label fw-bold small">Class</label>
                                    <select class="form-select form-select-sm" id="class_id" name="class_id">
                                        <option value="">All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="stream_id" class="form-label fw-bold small">Stream</label>
                                    <select class="form-select form-select-sm" id="stream_id" name="stream_id">
                                        <option value="">All Streams</option>
                                        @foreach($streams as $stream)
                                            <option value="{{ \Vinkla\Hashids\Facades\Hashids::encode($stream->id) }}">{{ $stream->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="fee_group_id" class="form-label fw-bold small">Fee Group</label>
                                    <select class="form-select form-select-sm" id="fee_group_id" name="fee_group_id">
                                        <option value="">All Fee Groups</option>
                                        @foreach($feeGroups as $feeGroup)
                                            <option value="{{ $feeGroup->id }}">{{ $feeGroup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="academic_year_id" class="form-label fw-bold small">Academic Year</label>
                                    <select class="form-select form-select-sm" id="academic_year_id" name="academic_year_id">
                                        <option value="">All Academic Years</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" {{ $currentAcademicYear && $currentAcademicYear->id == $year->id ? 'selected' : '' }}>
                                                {{ $year->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="period" class="form-label fw-bold small">Period</label>
                                    <select class="form-select form-select-sm" id="period" name="period">
                                        <option value="">All Periods</option>
                                        <option value="1">Quarter 1</option>
                                        <option value="2">Quarter 2</option>
                                        <option value="3">Quarter 3</option>
                                        <option value="4">Quarter 4</option>
                                        <option value="6">Term 1</option>
                                        <option value="7">Term 2</option>
                                        <option value="5">Full Year</option>
                                    </select>
                                </div>
                                <div class="col-md-12 d-flex gap-2 mt-2">
                                    <button type="button" class="btn btn-primary btn-sm" id="filterBtn">
                                        <i class="bx bx-search me-1"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clearFiltersBtn">
                                        <i class="bx bx-refresh me-1"></i> Clear Filters
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- DataTable -->
                <div class="table-responsive">
                            <table id="feeInvoicesTable" class="table table-striped table-hover">
                                <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Stream</th>
                                <th>Academic Year</th>
                                <th>Fee Group</th>
                                <th>Period</th>
                                <th>Invoice Numbers</th>
                                <th>Control Number</th>
                                <th>Total Amount Invoiced</th>
                                <th>Opening Balance</th>
                                <th>Opening Balance Control No.</th>
                                <th>Total Amount to be Paid</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Invoice PDF Modal -->
<div class="modal fade" id="bulkInvoicePdfModal" tabindex="-1" aria-labelledby="bulkInvoicePdfModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkInvoicePdfModalLabel">
                    <i class="bx bx-file me-2"></i>Generate Bulk Invoice PDF
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkInvoicePdfForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="pdf_class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                        <select class="form-select" id="pdf_class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Quarters <span class="text-danger">*</span></label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="outstanding_quarters[]" value="1" id="quarter1">
                            <label class="form-check-label" for="quarter1">Q1</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="outstanding_quarters[]" value="2" id="quarter2">
                            <label class="form-check-label" for="quarter2">Q2</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="outstanding_quarters[]" value="3" id="quarter3">
                            <label class="form-check-label" for="quarter3">Q3</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="outstanding_quarters[]" value="4" id="quarter4">
                            <label class="form-check-label" for="quarter4">Q4</label>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-1"></i>
                        This will generate a PDF containing all invoices for students in the selected class and quarters.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="generatePdfBtn">
                        <i class="bx bx-file me-1"></i> Generate PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Store all streams initially (before Select2 initialization)
    const allStreams = [];
    $('#stream_id option').each(function() {
        if ($(this).val()) {
            allStreams.push({
                id: $(this).val(),
                name: $(this).text()
            });
        }
    });

    // Initialize Select2 for dropdowns
    $('#class_id, #fee_group_id, #academic_year_id, #stream_id').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Load streams when class is selected
    $('#class_id').on('change', function() {
        const classId = $(this).val();
        const $streamSelect = $('#stream_id');
        const currentStreamValue = $streamSelect.val(); // Save current selection
        
        if (classId) {
            // Fetch streams for the selected class
            $.ajax({
                url: '{{ route("school.fee-invoices.get-streams") }}',
                type: 'GET',
                data: {
                    class_id: classId
                },
                success: function(data) {
                    $streamSelect.empty();
                    $streamSelect.append('<option value="">All Streams</option>');
                    if (data.streams && data.streams.length > 0) {
                        data.streams.forEach(function(stream) {
                            $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                        });
                    } else {
                        // If no streams for this class, show all streams
                        allStreams.forEach(function(stream) {
                            $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                        });
                    }
                    // Restore previous selection if it still exists
                    if (currentStreamValue && $streamSelect.find('option[value="' + currentStreamValue + '"]').length) {
                        $streamSelect.val(currentStreamValue);
                    }
                    $streamSelect.trigger('change');
                },
                error: function(xhr, status, error) {
                    console.error('Error loading streams:', error);
                    // On error, restore all streams
                    $streamSelect.empty();
                    $streamSelect.append('<option value="">All Streams</option>');
                    allStreams.forEach(function(stream) {
                        $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
                    });
                    if (currentStreamValue) {
                        $streamSelect.val(currentStreamValue);
                    }
                    $streamSelect.trigger('change');
                }
            });
        } else {
            // If no class selected, show all streams
            $streamSelect.empty();
            $streamSelect.append('<option value="">All Streams</option>');
            allStreams.forEach(function(stream) {
                $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
            });
            if (currentStreamValue) {
                $streamSelect.val(currentStreamValue);
            }
            $streamSelect.trigger('change');
        }
    });

    // Bulk Invoice PDF Form Handler
    $('#bulkInvoicePdfForm').on('submit', function(e) {
        e.preventDefault();
        
        const classId = $('#pdf_class_id').val();
        const quarters = $('input[name="outstanding_quarters[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        if (!classId) {
            alert('Please select a class');
            return;
        }

        if (quarters.length === 0) {
            alert('Please select at least one quarter');
            return;
        }

        // Disable button and show loading
        const btn = $('#generatePdfBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Generating...');

        // Create form data
        const formData = {
            class_id: classId,
            outstanding_quarters: quarters,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // Submit via AJAX
        $.ajax({
            url: '{{ route("school.fee-invoices.generate-bulk-outstanding") }}',
            method: 'POST',
            data: formData,
            xhrFields: {
                responseType: 'blob'
            },
            success: function(blob, status, xhr) {
                // Create download link
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'bulk_invoices_outstanding_students.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                // Close modal and reset form
                $('#bulkInvoicePdfModal').modal('hide');
                $('#bulkInvoicePdfForm')[0].reset();
                btn.prop('disabled', false).html('<i class="bx bx-file me-1"></i> Generate PDF');
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Failed to generate PDF. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // If not JSON, use default message
                    }
                }
                
                alert(errorMessage);
                btn.prop('disabled', false).html('<i class="bx bx-file me-1"></i> Generate PDF');
            }
        });
    });

    // Initialize DataTable
    window.feeInvoicesTable = $('#feeInvoicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.fee-invoices.data") }}',
            type: 'GET',
            data: function(d) {
                d.class_id = $('#class_id').val();
                d.fee_group_id = $('#fee_group_id').val();
                d.academic_year_id = $('#academic_year_id').val();
                d.stream_id = $('#stream_id').val();
                d.period = $('#period').val();
            },
            error: function(xhr, status, error) {
                console.error('DataTables error:', error);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'student_name', name: 'student_name' },
            { data: 'class_name', name: 'class_name' },
            { data: 'stream_name', name: 'stream_name', orderable: false },
            { data: 'academic_year', name: 'academic_year' },
            { data: 'fee_group', name: 'fee_group' },
            { data: 'period', name: 'period', orderable: false },
            { data: 'invoices', name: 'invoices', orderable: false },
            { data: 'control_number', name: 'control_number', orderable: false },
            { data: 'total_amount_invoiced', name: 'total_amount_invoiced', className: 'text-end' },
            { data: 'opening_balance', name: 'opening_balance', orderable: false, className: 'text-end' },
            { data: 'opening_balance_control_number', name: 'opening_balance_control_number', orderable: false, searchable: false },
            { data: 'total_amount_to_be_paid', name: 'total_amount_to_be_paid', className: 'text-end' },
            { data: 'status', name: 'status', orderable: false, className: 'text-center' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        order: [[1, 'asc']],
        responsive: true,
        language: {
            processing: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
        },
        initComplete: function() {
            // Style the DataTables elements
            $('.dataTables_filter input').addClass('form-control form-control-sm');
        }
    });

    // Reload DataTable with new filter parameters
    function reloadDataTable() {
        if (window.feeInvoicesTable) {
            window.feeInvoicesTable.ajax.reload(function() {
                // Reset filter button state after reload completes
                $('#filterBtn').prop('disabled', false).html('<i class="bx bx-search me-1"></i> Filter');
            }, false); // false parameter prevents resetting paging
        }
    }

    // Filter button click handler
    $('#filterBtn').on('click', function() {
        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Filtering...');
        reloadDataTable();
    });

    // Clear filters button click handler
    $('#clearFiltersBtn').on('click', function() {
        $('#class_id').val('').trigger('change');
        $('#fee_group_id').val('').trigger('change');
        $('#academic_year_id').val('').trigger('change');
        $('#period').val('').trigger('change');
        // Restore all streams
        const $streamSelect = $('#stream_id');
        $streamSelect.empty();
        $streamSelect.append('<option value="">All Streams</option>');
        if (typeof allStreams !== 'undefined' && allStreams) {
        allStreams.forEach(function(stream) {
            $streamSelect.append('<option value="' + stream.id + '">' + stream.name + '</option>');
        });
        }
        $streamSelect.trigger('change');
        reloadDataTable();
    });

    // Export functions
    window.exportFeeInvoicesToExcel = function() {
        const classId = $('#class_id').val();
        const streamId = $('#stream_id').val();
        const feeGroupId = $('#fee_group_id').val();
        const academicYearId = $('#academic_year_id').val();

        // Generate a simple hash ID for security
        const hashId = btoa(Date.now().toString()).replace(/=/g, '');

        let url = '{{ route("school.fee-invoices.export.excel", ":hashId") }}'.replace(':hashId', hashId);
        const params = new URLSearchParams();

        if (classId) params.append('class_id', classId);
        if (streamId) params.append('stream_id', streamId);
        if (feeGroupId) params.append('fee_group_id', feeGroupId);
        if (academicYearId) params.append('academic_year_id', academicYearId);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        window.open(url, '_blank');
    };

    window.exportFeeInvoicesToPDF = function() {
        const classId = $('#class_id').val();
        const streamId = $('#stream_id').val();
        const feeGroupId = $('#fee_group_id').val();
        const academicYearId = $('#academic_year_id').val();

        // Generate a simple hash ID for security
        const hashId = btoa(Date.now().toString()).replace(/=/g, '');

        let url = '{{ route("school.fee-invoices.export.pdf", ":hashId") }}'.replace(':hashId', hashId);
        const params = new URLSearchParams();

        if (classId) params.append('class_id', classId);
        if (streamId) params.append('stream_id', streamId);
        if (feeGroupId) params.append('fee_group_id', feeGroupId);
        if (academicYearId) params.append('academic_year_id', academicYearId);

        if (params.toString()) {
            url += '?' + params.toString();
        }

        window.open(url, '_blank');
    };
});
</script>
@endpush
@endsection

