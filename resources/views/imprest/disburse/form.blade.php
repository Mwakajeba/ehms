@extends('layouts.main')

@section('title', 'Disburse Imprest Funds')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Imprest Management', 'url' => route('imprest.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Request Details', 'url' => route('imprest.requests.show', $imprestRequest->id), 'icon' => 'bx bx-show'],
            ['label' => 'Disburse Funds', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0 text-primary">
                <i class="bx bx-money me-2"></i>Disburse Imprest Funds
                @if(isset($imprestSettings) && $imprestSettings->retirement_enabled)
                    <span class="badge bg-info ms-2">With Retirement</span>
                @else
                    <span class="badge bg-success ms-2">Direct Expense</span>
                @endif
            </h5>
            <span class="{{ $imprestRequest->getStatusBadgeClass() }}">{{ $imprestRequest->getStatusLabel() }}</span>
        </div>

        @if(isset($imprestSettings) && $imprestSettings->retirement_enabled)
            {{-- Retirement enabled - uses imprest receivables account --}}
            <div class="alert alert-info">
                <h6 class="fw-bold mb-2"><i class="bx bx-info-circle me-1"></i>Retirement Mode</h6>
                <p class="mb-0">This disbursement will be posted to an imprest receivables account. Expenses will be posted later when the imprest is retired/liquidated.</p>
            </div>
        @else
            {{-- No retirement - direct expense posting --}}
            <div class="alert alert-success">
                <h6 class="fw-bold mb-2"><i class="bx bx-check-circle me-1"></i>Direct Expense Mode</h6>
                <p class="mb-0">This disbursement will directly post expenses to their respective accounts. No retirement/liquidation is required.</p>
            </div>
        @endif

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Disbursement Details</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('imprest.disbursed.disburse', $imprestRequest->id) }}" method="POST" id="disbursementForm">
                            @csrf
                            
                            @if(isset($imprestSettings) && $imprestSettings->retirement_enabled)
                                {{-- Retirement mode - show amount and imprest account fields --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="amount" class="form-label">Amount to Disburse <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">TZS</span>
                                            <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                                   id="amount" name="amount" step="0.01" min="0.01" 
                                                   max="{{ $imprestRequest->amount_requested }}" 
                                                   value="{{ old('amount', $imprestRequest->amount_requested) }}" required>
                                            @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <small class="text-muted">Maximum: TZS {{ number_format($imprestRequest->amount_requested, 2) }}</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="bank_account_id" class="form-label">Bank/Cash Account <span class="text-danger">*</span></label>
                                        <select class="form-select @error('bank_account_id') is-invalid @enderror" 
                                                id="bank_account_id" name="bank_account_id" required>
                                            <option value="">Select Bank/Cash Account</option>
                                            @foreach($bankAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} 
                                                @if($account->chartAccount)
                                                    - {{ $account->chartAccount->account_name }}
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('bank_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="imprest_account_display" class="form-label">Imprest Receivable Account</label>
                                        @if($imprestSettings && $imprestSettings->isRetirementConfigured())
                                            {{-- Auto-select from settings when retirement is properly configured --}}
                                            <input type="hidden" name="imprest_account_id" value="{{ $imprestSettings->receivablesAccount->id }}">
                                            <div class="form-control bg-light" style="background-color: #f8f9fa !important;">
                                                <strong>{{ $imprestSettings->receivablesAccount->account_code }} - {{ $imprestSettings->receivablesAccount->account_name }}</strong>
                                                <br><small class="text-muted">Auto-selected from settings</small>
                                            </div>
                                            <small class="text-success">
                                                <i class="bx bx-check-circle me-1"></i>Account automatically selected from imprest settings
                                            </small>
                                        @else
                                            {{-- Fallback if no account in settings --}}
                                            <div class="alert alert-warning">
                                                <i class="bx bx-warning me-1"></i>
                                                <strong>No imprest receivables account configured.</strong><br>
                                                Please configure the imprest receivables account in settings before disbursing.
                                                <a href="{{ route('imprest.index') }}" class="btn btn-sm btn-warning mt-2">
                                                    <i class="bx bx-cog me-1"></i>Configure Settings
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                            @else
                                {{-- Direct expense mode - show bank account and expense breakdown --}}
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="bank_account_id" class="form-label">Bank/Cash Account <span class="text-danger">*</span></label>
                                        <select class="form-select @error('bank_account_id') is-invalid @enderror" 
                                                id="bank_account_id" name="bank_account_id" required>
                                            <option value="">Select Bank/Cash Account</option>
                                            @foreach($bankAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} 
                                                @if($account->chartAccount)
                                                    - {{ $account->chartAccount->account_name }}
                                                @endif
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('bank_account_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Total Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">TZS</span>
                                            <input type="text" class="form-control" 
                                                   value="{{ number_format($imprestRequest->amount_requested, 2) }}" readonly>
                                        </div>
                                        <small class="text-muted">Full amount will be disbursed as expenses</small>
                                    </div>
                                </div>

                                {{-- Show expense breakdown --}}
                                <div class="mb-3">
                                    <label class="form-label">Expense Accounts to be Debited</label>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Account Code</th>
                                                    <th>Account Name</th>
                                                    <th class="text-end">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($imprestRequest->imprestItems as $item)
                                                <tr>
                                                    <td><code>{{ $item->chartAccount->account_code }}</code></td>
                                                    <td>{{ $item->chartAccount->account_name }}</td>
                                                    <td class="text-end fw-bold">TZS {{ number_format($item->amount, 2) }}</td>
                                                </tr>
                                                @endforeach
                                                <tr class="table-primary">
                                                    <th colspan="2">Total</th>
                                                    <th class="text-end">TZS {{ number_format($imprestRequest->amount_requested, 2) }}</th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted">These accounts will be debited directly when funds are disbursed.</small>
                                </div>
                            @endif

                                <div class="col-md-6 mb-3">
                                    <label for="reference" class="form-label">Reference Number</label>
                                    <input type="text" class="form-control @error('reference') is-invalid @enderror" 
                                           id="reference" name="reference" 
                                           value="{{ old('reference', 'IMP-DISB-' . $imprestRequest->request_number) }}" 
                                           placeholder="Payment reference">
                                    @error('reference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description/Notes</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="3" 
                                          placeholder="Additional notes or description">{{ old('description', "Imprest disbursement for: {$imprestRequest->purpose}") }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('imprest.requests.show', $imprestRequest->id) }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Cancel
                                </a>
                                @if(isset($imprestSettings) && $imprestSettings->retirement_enabled && (!$imprestSettings->isRetirementConfigured()))
                                    {{-- Disable submit if retirement enabled but not properly configured --}}
                                    <button type="button" class="btn btn-primary" disabled>
                                        <i class="bx bx-warning me-1"></i>Configure Settings First
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="bx bx-money me-1"></i>Disburse Funds
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Request Summary Card -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Request Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Request Number</small>
                            <div class="fw-bold">{{ $imprestRequest->request_number }}</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Employee</small>
                            <div class="fw-bold">{{ $imprestRequest->employee->name ?? 'N/A' }}</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Department</small>
                            <div>{{ $imprestRequest->department->name ?? 'N/A' }}</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Purpose</small>
                            <div>{{ $imprestRequest->purpose }}</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Requested Amount</small>
                            <div class="fw-bold text-primary">TZS {{ number_format($imprestRequest->amount_requested, 2) }}</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Date Required</small>
                            <div>{{ $imprestRequest->date_required ? $imprestRequest->date_required->format('M d, Y') : 'N/A' }}</div>
                        </div>
                        <div>
                            <small class="text-muted">Status</small>
                            <div>{{ $imprestRequest->getStatusLabel() }}</div>
                        </div>
                    </div>
                </div>

                <!-- Accounting Impact Card -->
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Accounting Impact</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info alert-sm">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Journal Entry Preview:</strong>
                        </div>
                        @if(isset($imprestSettings) && $imprestSettings->retirement_enabled)
                            <div class="mb-2">
                                <strong>Debit:</strong> 
                                @if($imprestSettings->isRetirementConfigured())
                                    {{ $imprestSettings->receivablesAccount->account_name }}
                                    <br><code class="text-muted">{{ $imprestSettings->receivablesAccount->account_code }}</code>
                                @else
                                    <span class="text-warning">Imprest Receivable Account (Not Configured)</span>
                                @endif
                                <br>
                                <small class="text-muted">Increases asset (money advanced to employee)</small>
                            </div>
                        @else
                            <div class="mb-2">
                                <strong>Debit:</strong> Multiple Expense Accounts<br>
                                <small class="text-muted">Direct posting to expense accounts</small>
                            </div>
                        @endif
                        <div class="mb-3">
                            <strong>Credit:</strong> Bank/Cash Account<br>
                            <small class="text-muted">Decreases cash/bank (money paid out)</small>
                        </div>
                        <div class="alert alert-success alert-sm">
                            <i class="bx bx-check-circle me-1"></i>
                            <small><strong>Note:</strong> This disbursement will be automatically approved since the imprest request has already been approved.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.alert-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Simple form submission with loading state
    $('#disbursementForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Processing...');
        
        // Let the form submit normally (don't prevent default)
        // The CSRF token in the form will be handled automatically
    });
    
    // Calculate and show amount in real-time
    $('#amount').on('input', function() {
        const amount = parseFloat($(this).val()) || 0;
        const maxAmount = parseFloat($(this).attr('max')) || 0;
        
        if (amount > maxAmount) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">Amount cannot exceed TZS ' + maxAmount.toLocaleString() + '</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
});
</script>
@endpush