@extends('layouts.main')

@section('title', 'Student Prepaid Accounts')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Student Prepaid Accounts', 'url' => '#', 'icon' => 'bx bx-credit-card']
        ]" />
        <h6 class="mb-0 text-uppercase">STUDENT PREPAID ACCOUNTS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-credit-card me-1 font-22 text-primary"></i>
                                <span class="h5 mb-0 text-primary">Student Prepaid Accounts</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-warning me-2" id="syncGlTransactionsBtn" title="Sync GL Transactions - Fix missing GL transactions for prepaid account applications">
                                    <i class="bx bx-sync me-1"></i> Sync GL Transactions
                                </button>
                                <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#createAccountsModal">
                                    <i class="bx bx-user-plus me-1"></i> Create Accounts
                                </button>
                                <a href="{{ route('school.prepaid-accounts.import') }}" class="btn btn-info me-2">
                                    <i class="bx bx-upload me-1"></i> Import
                                </a>
                                <a href="{{ route('school.prepaid-accounts.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-1"></i> Add Credit
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

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h4 class="text-primary mb-1">{{ number_format($totalAccounts) }}</h4>
                                        <small class="text-muted">Total Accounts</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h4 class="text-success mb-1">{{ config('app.currency', 'TZS') }} {{ number_format($totalCredit, 2) }}</h4>
                                        <small class="text-muted">Total Credit Balance</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Section -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-filter me-2"></i> Filter Accounts
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="filter_class_id" class="form-label fw-bold">Class</label>
                                        <select class="form-select select2" id="filter_class_id" name="class_id">
                                            <option value="">All Classes</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="filter_search" class="form-label fw-bold">Search</label>
                                        <input type="text" class="form-control" id="filter_search" name="search" placeholder="Student name or admission number">
                                    </div>
                                    <div class="col-md-4 d-flex align-items-end">
                                        <button type="button" class="btn btn-primary w-100" id="applyFilters">
                                            <i class="bx bx-search me-1"></i> Apply Filters
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DataTable -->
                        <div class="table-responsive">
                            <table id="prepaidAccountsTable" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Admission Number</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Credit Balance</th>
                                        <th>Total Deposited</th>
                                        <th>Total Used</th>
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
    </div>
</div>

<!-- Create Accounts Modal -->
<div class="modal fade" id="createAccountsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Prepaid Accounts for Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createAccountsForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-1"></i>
                        This will create prepaid accounts with zero balance for all active students in the selected class for the current academic year.
                    </div>
                    <div class="mb-3">
                        <label for="create_class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="create_class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">All active students in this class will get prepaid accounts</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-user-plus me-1"></i> Create Accounts
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Credit Modal -->
<div class="modal fade" id="addCreditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Credit to Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCreditForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="modal_account_id" name="account_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student</label>
                        <input type="text" class="form-control" id="modal_student_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="modal_amount" class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" class="form-control" id="modal_amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal_bank_account_id" class="form-label fw-bold">Received In (Bank Account) <span class="text-danger">*</span></label>
                        <select class="form-select select2" id="modal_bank_account_id" name="bank_account_id" required>
                            <option value="">Select Bank Account</option>
                            @php
                                $user = Auth::user();
                                $modalBankAccounts = \App\Models\BankAccount::with('chartAccount')
                                    ->where(function($query) use ($user) {
                                        $query->whereHas('chartAccount.accountClassGroup', function ($subQuery) use ($user) {
                                            $subQuery->where('company_id', $user->company_id);
                                        })
                                        ->orWhere('company_id', $user->company_id);
                                    })
                                    ->orderBy('name')
                                    ->get();
                            @endphp
                            @foreach($modalBankAccounts as $bankAccount)
                                <option value="{{ $bankAccount->id }}">
                                    {{ $bankAccount->name }} 
                                    @if($bankAccount->chartAccount)
                                        - {{ $bankAccount->chartAccount->account_name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_reference" class="form-label fw-bold">Reference</label>
                        <input type="text" class="form-control" id="modal_reference" name="reference" placeholder="Payment reference number">
                    </div>
                    <div class="mb-3">
                        <label for="modal_notes" class="form-label fw-bold">Notes</label>
                        <textarea class="form-control" id="modal_notes" name="notes" rows="3" placeholder="Additional notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Credit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for class filter with search
    $('#filter_class_id').select2({
        placeholder: 'All Classes',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0
    });

    // Initialize Select2 for create accounts modal
    $('#create_class_id').select2({
        placeholder: 'Select Class',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0,
        dropdownParent: $('#createAccountsModal')
    });

    // Handle create accounts form submission
    $('#createAccountsForm').on('submit', function(e) {
        e.preventDefault();
        const classId = $('#create_class_id').val();
        
        if (!classId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select a class',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            } else {
                alert('Please select a class');
            }
            return;
        }

        // Show loading state
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Creating Accounts...',
                text: 'Please wait while we create prepaid accounts for all students.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }

        $.ajax({
            url: '{{ route("school.prepaid-accounts.bulk-create") }}',
            method: 'POST',
            data: {
                class_id: classId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#createAccountsModal').modal('hide');
                $('#create_class_id').val('').trigger('change');
                table.draw();
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Success!',
                        html: `Successfully created <strong>${response.created_count}</strong> prepaid account(s).<br><br>${response.message || ''}`,
                        icon: 'success',
                        confirmButtonColor: '#3085d6',
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    alert('Successfully created ' + response.created_count + ' prepaid account(s).');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Failed to create accounts';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error!',
                        text: error,
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    alert('Error: ' + error);
                }
            }
        });
    });

    // Handle sync GL transactions button
    $('#syncGlTransactionsBtn').on('click', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Sync GL Transactions?',
                text: 'This will check and create missing GL transactions for prepaid account applications. This may take a few moments.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, sync now',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Syncing...',
                        text: 'Please wait while we sync GL transactions.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '{{ route("school.prepaid-accounts.sync-gl-transactions") }}',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    html: `GL transactions synced successfully.<br><br>Found: <strong>${response.missing_count}</strong> missing transactions<br>Fixed: <strong>${response.fixed_count}</strong> transactions`,
                                    icon: 'success',
                                    confirmButtonColor: '#3085d6',
                                    timer: 5000,
                                    timerProgressBar: true
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: response.message || 'Failed to sync GL transactions',
                                    icon: 'error',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function(xhr) {
                            const error = xhr.responseJSON?.message || 'Failed to sync GL transactions';
                            Swal.fire({
                                title: 'Error!',
                                text: error,
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
        } else {
            if (confirm('Sync GL transactions? This will check and create missing GL transactions.')) {
                $.ajax({
                    url: '{{ route("school.prepaid-accounts.sync-gl-transactions") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert(response.message || 'GL transactions synced successfully');
                    },
                    error: function(xhr) {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Failed to sync GL transactions'));
                    }
                });
            }
        }
    });

    var table = $('#prepaidAccountsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("school.prepaid-accounts.data") }}',
            data: function(d) {
                d.class_id = $('#filter_class_id').val();
                d.search = $('#filter_search').val();
            }
        },
        columns: [
            { data: 'admission_number', name: 'admission_number' },
            { data: 'student_name', name: 'student_name' },
            { data: 'class_name', name: 'class_name' },
            { data: 'stream_name', name: 'stream_name' },
            { 
                data: 'credit_balance_formatted', 
                name: 'credit_balance',
                render: function(data) {
                    return '{{ config("app.currency", "TZS") }} ' + data;
                }
            },
            { 
                data: 'total_deposited_formatted', 
                name: 'total_deposited',
                render: function(data) {
                    return '{{ config("app.currency", "TZS") }} ' + data;
                }
            },
            { 
                data: 'total_used_formatted', 
                name: 'total_used',
                render: function(data) {
                    return '{{ config("app.currency", "TZS") }} ' + data;
                }
            },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        language: {
            processing: '<i class="bx bx-loader-alt bx-spin"></i> Loading...'
        }
    });

    $('#applyFilters').on('click', function() {
        table.draw();
    });

    $('#filter_class_id, #filter_search').on('change keyup', function() {
        table.draw();
    });

    // Initialize Select2 for bank account in modal
    $('#modal_bank_account_id').select2({
        placeholder: 'Select Bank Account',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0,
        dropdownParent: $('#addCreditModal')
    });

    // Add Credit Modal
    window.showAddCreditModal = function(accountHashId, studentName) {
        $('#modal_account_id').val(accountHashId);
        $('#modal_student_name').val(studentName);
        $('#modal_amount').val('');
        $('#modal_bank_account_id').val('').trigger('change');
        $('#modal_reference').val('');
        $('#modal_notes').val('');
        $('#addCreditModal').modal('show');
    };

    // Handle add credit form submission
    $('#addCreditForm').on('submit', function(e) {
        e.preventDefault();
        const accountHashId = $('#modal_account_id').val();
        const formData = $(this).serialize();
        
        $.ajax({
            url: '{{ url("school/prepaid-accounts") }}/' + accountHashId + '/add-credit',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#addCreditModal').modal('hide');
                    $('#addCreditForm')[0].reset();
                    table.draw();
                    
                    let message = response.message || 'Credit added successfully!';
                    let htmlMessage = message;
                    
                    // If credit was auto-applied to invoices, show detailed information
                    if (response.auto_applied && response.auto_applied_amount > 0) {
                        htmlMessage = message;
                        if (response.auto_applied_invoices && response.auto_applied_invoices.length > 0) {
                            let invoiceList = '<ul style="text-align: left; margin-top: 10px;">';
                            response.auto_applied_invoices.forEach(function(invoice) {
                                invoiceList += `<li>Invoice ${invoice.invoice_number} (Period ${invoice.period}): TZS ${parseFloat(invoice.amount_applied).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</li>`;
                            });
                            invoiceList += '</ul>';
                            htmlMessage = message + invoiceList;
                        }
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Success!',
                            html: htmlMessage,
                            icon: 'success',
                            confirmButtonColor: '#3085d6',
                            timer: response.auto_applied ? 5000 : 2000,
                            timerProgressBar: true
                        });
                    } else {
                        alert(message);
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Failed to add credit';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error!',
                        text: error,
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    alert('Error: ' + error);
                }
            }
        });
    });

    // Delete Account
    window.deleteAccount = function(accountHashId, studentName) {
        // Use SweetAlert if available, otherwise fallback to browser confirm
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Delete Prepaid Account?',
                html: `Are you sure you want to delete the prepaid account for <strong>${studentName}</strong>?<br><br>This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the account.',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '{{ url("school/prepaid-accounts") }}/' + accountHashId + '/delete',
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            table.draw();
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Prepaid account has been deleted successfully.',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                timer: 2000,
                                timerProgressBar: true
                            });
                        },
                        error: function(xhr) {
                            const error = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Failed to delete account';
                            Swal.fire({
                                title: 'Error!',
                                text: error,
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
        } else {
            // Fallback to browser confirm
            if (confirm('Are you sure you want to delete the prepaid account for ' + studentName + '? This action cannot be undone.')) {
                $.ajax({
                    url: '{{ url("school/prepaid-accounts") }}/' + accountHashId,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        table.draw();
                        alert('Account deleted successfully!');
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Failed to delete account';
                        alert('Error: ' + error);
                    }
                });
            }
        }
    };
});
</script>
@endpush

