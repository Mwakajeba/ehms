@extends('layouts.main')

@section('title', 'Edit Prepaid Account Transactions')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Student Prepaid Accounts', 'url' => route('school.prepaid-accounts.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Edit Transactions', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT PREPAID ACCOUNT TRANSACTIONS</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Prepaid Account Transactions</h5>
                        </div>
                        <hr />

                        <!-- Account Information -->
                        <div class="card border-info mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-info-circle me-2"></i> Account Information
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Student:</strong><br>
                                        {{ $account->student->admission_number ?? 'N/A' }} - {{ $account->student->first_name }} {{ $account->student->last_name }}
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Class:</strong><br>
                                        {{ $account->student->class->name ?? 'N/A' }}
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Current Balance:</strong><br>
                                        <span class="text-success fw-bold">{{ config('app.currency', 'TZS') }} {{ number_format($account->credit_balance, 2) }}</span>
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Total Deposited:</strong><br>
                                        {{ config('app.currency', 'TZS') }} {{ number_format($account->total_deposited, 2) }}
                                    </div>
                                    <div class="col-md-2">
                                        <strong>Total Used:</strong><br>
                                        {{ config('app.currency', 'TZS') }} {{ number_format($account->total_used, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> {{ $errors->first('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('school.prepaid-accounts.update', $account->hashid) }}" method="POST" id="editTransactionsForm">
                            @csrf
                            @method('PUT')

                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bx bx-list-ul me-2"></i> Deposit Transactions
                                    </h6>
                                    <button type="button" class="btn btn-light btn-sm" id="addTransactionRow">
                                        <i class="bx bx-plus me-1"></i> Add New Transaction
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="transactionsTable">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="15%">Date</th>
                                                    <th width="15%">Amount <span class="text-danger">*</span></th>
                                                    <th width="25%">Received In (Bank Account) <span class="text-danger">*</span></th>
                                                    <th width="15%">Reference</th>
                                                    <th width="20%">Notes</th>
                                                    <th width="5%">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="transactionsBody">
                                                @foreach($depositTransactions as $index => $item)
                                                    <tr data-transaction-id="{{ $item['transaction']->id }}">
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $item['transaction']->created_at->format('Y-m-d H:i') }}</td>
                                                        <td>
                                                            <input type="hidden" name="transactions[{{ $index }}][id]" value="{{ $item['transaction']->id }}">
                                                            <input type="number" step="0.01" min="0" class="form-control form-control-sm transaction-amount" 
                                                                   name="transactions[{{ $index }}][amount]" 
                                                                   value="{{ old('transactions.'.$index.'.amount', $item['transaction']->amount) }}" required>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm select2-transaction" 
                                                                    name="transactions[{{ $index }}][bank_account_id]" required>
                                                                <option value="">Select Bank Account</option>
                                                                @foreach($bankAccounts as $bankAccount)
                                                                    <option value="{{ $bankAccount->id }}" 
                                                                            {{ old('transactions.'.$index.'.bank_account_id', $item['bank_account_id']) == $bankAccount->id ? 'selected' : '' }}>
                                                                        {{ $bankAccount->name }} 
                                                                        @if($bankAccount->chartAccount)
                                                                            - {{ $bankAccount->chartAccount->account_name }}
                                                                        @endif
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" 
                                                                   name="transactions[{{ $index }}][reference]" 
                                                                   value="{{ old('transactions.'.$index.'.reference', $item['transaction']->reference) }}">
                                                        </td>
                                                        <td>
                                                            <textarea class="form-control form-control-sm" rows="1" 
                                                                      name="transactions[{{ $index }}][notes]">{{ old('transactions.'.$index.'.notes', $item['transaction']->notes) }}</textarea>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="btn btn-sm btn-danger delete-row" title="Delete">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                            <input type="hidden" name="transactions[{{ $index }}][delete]" value="0" class="delete-flag">
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('school.prepaid-accounts.show', $account->hashid) }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Details
                                    </a>
                                    <a href="{{ route('school.prepaid-accounts.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-x me-1"></i> Cancel
                                    </a>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save All Changes
                                </button>
                            </div>
                        </form>
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
    let transactionIndex = {{ $depositTransactions->count() }};
    const bankAccounts = @json($bankAccounts->map(function($ba) {
        return [
            'id' => $ba->id,
            'name' => $ba->name,
            'chart_account' => $ba->chartAccount ? ['account_name' => $ba->chartAccount->account_name] : null
        ];
    }));

    // Initialize Select2 for existing bank account dropdowns
    $('.select2-transaction').select2({
        placeholder: 'Select Bank Account',
        allowClear: true,
        width: '100%',
        theme: 'bootstrap-5',
        minimumInputLength: 0
    });

    // Add new transaction row
    $('#addTransactionRow').on('click', function() {
        const row = `
            <tr data-transaction-id="">
                <td>${transactionIndex + 1}</td>
                <td><small class="text-muted">New</small></td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm transaction-amount" 
                           name="transactions[${transactionIndex}][amount]" required>
                </td>
                <td>
                    <select class="form-select form-select-sm select2-transaction" 
                            name="transactions[${transactionIndex}][bank_account_id]" required>
                        <option value="">Select Bank Account</option>
                        ${bankAccounts.map(ba => 
                            `<option value="${ba.id}">${ba.name}${ba.chart_account ? ' - ' + ba.chart_account.account_name : ''}</option>`
                        ).join('')}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" 
                           name="transactions[${transactionIndex}][reference]">
                </td>
                <td>
                    <textarea class="form-control form-control-sm" rows="1" 
                              name="transactions[${transactionIndex}][notes]"></textarea>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger delete-row" title="Delete">
                        <i class="bx bx-trash"></i>
                    </button>
                    <input type="hidden" name="transactions[${transactionIndex}][delete]" value="0" class="delete-flag">
                </td>
            </tr>
        `;
        $('#transactionsBody').append(row);
        
        // Initialize Select2 for new row
        $('#transactionsBody tr:last .select2-transaction').select2({
            placeholder: 'Select Bank Account',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });
        
        transactionIndex++;
        updateRowNumbers();
    });

    // Delete row
    $(document).on('click', '.delete-row', function() {
        const row = $(this).closest('tr');
        const transactionId = row.data('transaction-id');
        
        if (transactionId) {
            // Mark for deletion
            row.find('.delete-flag').val('1');
            row.addClass('table-danger');
            row.find('input, select, textarea').prop('disabled', true);
            $(this).html('<i class="bx bx-undo"></i>').removeClass('btn-danger').addClass('btn-warning').attr('title', 'Restore');
        } else {
            // Remove new row
            row.remove();
            updateRowNumbers();
        }
    });

    // Restore deleted row
    $(document).on('click', '.btn-warning.delete-row', function() {
        const row = $(this).closest('tr');
        row.find('.delete-flag').val('0');
        row.removeClass('table-danger');
        row.find('input, select, textarea').prop('disabled', false);
        $(this).html('<i class="bx bx-trash"></i>').removeClass('btn-warning').addClass('btn-danger').attr('title', 'Delete');
    });

    // Update row numbers
    function updateRowNumbers() {
        $('#transactionsBody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    // Form submission confirmation
    $('#editTransactionsForm').on('submit', function(e) {
        const deletedCount = $('.delete-flag[value="1"]').length;
        if (deletedCount > 0) {
            if (!confirm(`You are about to delete ${deletedCount} transaction(s). Are you sure you want to continue?`)) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script>
@endpush
