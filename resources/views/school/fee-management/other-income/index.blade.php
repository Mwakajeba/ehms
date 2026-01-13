@extends('layouts.main')

@section('title', 'Other Income Collection')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Other Income Collection', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        <h6 class="mb-0 text-uppercase">OTHER INCOME COLLECTION</h6>
        <hr />

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card border-primary shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-primary fw-semibold">Total Income</p>
                                <h4 class="mb-0 text-primary">{{ config('app.currency', 'TZS') }} {{ number_format($totalIncome, 2) }}</h4>
                                <small class="text-muted">All time</small>
                            </div>
                            <div class="avatar lg bg-primary bg-opacity-10 rounded-3">
                                <i class="bx bx-money font-24 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card border-info shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-info fw-semibold">Today Income</p>
                                <h4 class="mb-0 text-info">{{ config('app.currency', 'TZS') }} {{ number_format($todayIncome, 2) }}</h4>
                                <small class="text-muted">{{ today()->format('M d, Y') }}</small>
                            </div>
                            <div class="avatar lg bg-info bg-opacity-10 rounded-3">
                                <i class="bx bx-calendar-star font-24 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 mb-3">
                <div class="card border-info shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-info fw-semibold">This Month</p>
                                <h4 class="mb-0 text-info">{{ config('app.currency', 'TZS') }} {{ number_format($thisMonthIncome, 2) }}</h4>
                                <small class="text-muted">{{ now()->format('M Y') }}</small>
                            </div>
                            <div class="avatar lg bg-info bg-opacity-10 rounded-3">
                                <i class="bx bx-calendar font-24 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="card-title d-flex align-items-center">
                        <div><i class="bx bx-money me-1 font-22 text-primary"></i></div>
                        <h5 class="mb-0 text-primary">Other Income Records</h5>
                    </div>
                    <a href="{{ route('school.other-income.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Add New Income
                    </a>
                </div>
                <hr />

                <!-- Filters and Export Section -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card border-light shadow-sm">
                            <div class="card-body">
                                <form id="filterForm" method="GET">
                                    <div class="row g-3">
                                        <!-- Date Range -->
                                        <div class="col-md-3">
                                            <label class="form-label">From Date</label>
                                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">To Date</label>
                                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                        </div>

                                        <!-- Income Type -->
                                        <div class="col-md-2">
                                            <label class="form-label">Type</label>
                                            <select name="income_type" class="form-select select2-single">
                                                <option value="">All Types</option>
                                                <option value="student" {{ request('income_type') === 'student' ? 'selected' : '' }}>Student</option>
                                                <option value="other" {{ request('income_type') === 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>

                                        <!-- Class Filter -->
                                        <div class="col-md-2">
                                            <label class="form-label">Class</label>
                                            <select name="class_id" class="form-select select2-single">
                                                <option value="">All Classes</option>
                                                @foreach($classes as $class)
                                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                        {{ $class->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Income Account -->
                                        <div class="col-md-2">
                                            <label class="form-label">Income Account</label>
                                            <select name="income_account_id" class="form-select select2-single">
                                                <option value="">All Accounts</option>
                                                @foreach($incomeAccounts as $account)
                                                    <option value="{{ $account->id }}" {{ request('income_account_id') == $account->id ? 'selected' : '' }}>
                                                        {{ $account->account_code }} - {{ $account->account_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                                                <i class="bx bx-refresh me-1"></i> Clear Filters
                                            </button>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-success btn-sm me-2" onclick="exportToExcel()">
                                                <i class="bx bx-file me-1"></i> Export Excel
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="exportToPdf()">
                                                <i class="bx bx-file-pdf me-1"></i> Export PDF
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="other-income-table" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Student/Party</th>
                                <th>Class/Stream</th>
                                <th>Description</th>
                                <th>Received In</th>
                                <th>Income Account</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5 class="mb-2">Processing...</h5>
                <p class="text-muted mb-0">Please wait while we process your request.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .fs-1 {
        font-size: 3rem !important;
    }

    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable with server-side processing
        window.otherIncomeTable = $('#other-income-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("school.other-income.data") }}',
                data: function(d) {
                    // Add filter parameters
                    d.date_from = $('[name="date_from"]').val();
                    d.date_to = $('[name="date_to"]').val();
                    d.income_type = $('[name="income_type"]').val();
                    d.class_id = $('[name="class_id"]').val();
                    d.income_account_id = $('[name="income_account_id"]').val();
                }
            },
            columns: [
                {
                    data: null,
                    name: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'transaction_date', 
                    name: 'transaction_date',
                    render: function(data, type, row) {
                        if (!data) return 'N/A';
                        const date = new Date(data);
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = date.getFullYear();
                        return `${day}-${month}-${year}`;
                    }
                },
                { data: 'income_type', name: 'income_type' },
                { data: 'student_name', name: 'student_name' },
                { data: 'student_class_stream', name: 'student_class_stream' },
                { data: 'description', name: 'description' },
                { data: 'received_in_display', name: 'received_in' },
                { data: 'account_name', name: 'account_name' },
                { data: 'formatted_amount', name: 'amount' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            pageLength: 25,
            responsive: true,
            order: [[1, 'desc']]
        });

        // Apply filters when any filter changes
        $('#filterForm input, #filterForm select').change(function() {
            window.otherIncomeTable.ajax.reload();
        });

        // Handle delete button clicks with SweetAlert
        $(document).on('click', '.delete-income-btn', function(e) {
            e.preventDefault();
            
            const incomeId = $(this).data('id');
            const deleteUrl = $(this).data('url');
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to delete this income record?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the income record.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Create and submit form
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = deleteUrl;
                    form.style.display = 'none';

                    // Add CSRF token
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    // Add method spoofing for DELETE
                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';
                    form.appendChild(methodField);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });

    // Clear all filters
    function clearFilters() {
        $('#filterForm')[0].reset();
        $('.class-filter').hide();
        window.otherIncomeTable.ajax.reload();
    }

    // Export to PDF
    function exportToPdf() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        const url = '{{ route("school.other-income.export-list-pdf") }}?' + params.toString();
        window.open(url, '_blank');
    }

    // Export to Excel
    function exportToExcel() {
        const formData = new FormData(document.getElementById('filterForm'));
        const params = new URLSearchParams(formData);
        const url = '{{ route("school.other-income.export-list-excel") }}?' + params.toString();
        window.open(url, '_blank');
    }

    // Initialize Select2 for filter dropdowns
    if ($.fn.select2) {
        $('.select2-single').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: function() {
                return $(this).data('placeholder') || 'Select an option';
            },
            allowClear: true
        });
    }
</script>
@endpush