@extends('layouts.main')

@section('title', 'Add Other Income')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Other Income Collection', 'url' => route('school.other-income.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Add New Income', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">ADD OTHER INCOME</h6>
        <hr />

        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-center">
                    <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                    <h5 class="mb-0 text-primary">Add New Income Record</h5>
                </div>
                <hr />

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">Please fix the following errors:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <!-- Main Form Section -->
                    <div class="col-lg-8">
                        <form id="income-form" action="{{ route('school.other-income.store') }}" method="POST">
                            @csrf

                            <!-- Income Lines Container -->
                            <div id="income-lines-container">
                                <!-- Row 1: Transaction Date, Income Type, Other Party -->
                                <div class="income-line mb-3 p-3 border rounded">
                                    <div class="row g-3 mb-3">
                                        <!-- Transaction Date -->
                                        <div class="col-md-4">
                                            <label class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                            <input type="date" name="income_lines[0][transaction_date]" class="form-control transaction-date" value="{{ date('Y-m-d') }}" required>
                                        </div>

                                <!-- Income Type -->
                                <div class="col-md-4">
                                    <label class="form-label">Income Type <span class="text-danger">*</span></label>
                                    <select name="income_lines[0][income_type]" class="form-select income-type select2-single" required>
                                        <option value="">Select Type</option>
                                        <option value="student">Student</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>                                        <!-- Other Party (shown when other type is selected) -->
                                        <div class="col-md-4 other-party-field" style="display: none;">
                                            <label class="form-label">Other Party <span class="text-danger">*</span></label>
                                            <input type="text" name="income_lines[0][other_party]" class="form-control other-party-input" placeholder="Enter party name">
                                        </div>

                                        <!-- Student Selection (shown when student type is selected) -->
                                        <div class="col-md-4 student-field" style="display: none;">
                                            <label class="form-label">Student <span class="text-danger">*</span></label>
                                            <select name="income_lines[0][student_id]" class="form-select student-select select2-single" data-placeholder="Select Student">
                                                <option value="">Select Student</option>
                                                @foreach($students as $student)
                                                    <option value="{{ $student->id }}">
                                                        {{ $student->name }}
                                                        @if($student->class || $student->stream)
                                                            (
                                                            @if($student->class){{ $student->class->name }}@endif
                                                            @if($student->class && $student->stream) - @endif
                                                            @if($student->stream){{ $student->stream->name }}@endif
                                                            )
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Row 2: Received In, Description -->
                                    <div class="row g-3 mb-3">
                                        <!-- Received In -->
                                        <div class="col-md-6">
                                            <label class="form-label">Received In <span class="text-danger">*</span></label>
                                            <select name="income_lines[0][received_in]" class="form-select select2-single" required>
                                                <option value="">Select Bank Account</option>
                                                @if(count($bankAccounts) > 0)
                                                    @foreach($bankAccounts as $bankAccount)
                                                        <option value="{{ $bankAccount->id }}">{{ $bankAccount->name }} ({{ $bankAccount->account_number }})</option>
                                                    @endforeach
                                                @else
                                                    <option value="" disabled>No bank accounts available</option>
                                                @endif
                                            </select>
                                        </div>

                                        <!-- Transaction Description -->
                                        <div class="col-md-6">
                                            <label class="form-label">Description <span class="text-danger">*</span></label>
                                            <input type="text" name="income_lines[0][description]" class="form-control" placeholder="Enter description" required>
                                        </div>
                                    </div>

                                    <!-- Row 3: Income Account, Amount -->
                                    <div class="row g-3">
                                        <!-- Income Account -->
                                        <div class="col-md-5">
                                            <label class="form-label">Income Account <span class="text-danger">*</span></label>
                                            <select name="income_lines[0][income_account_id]" class="form-select select2-single" required data-placeholder="Select Account">
                                                <option value="">Select Account</option>
                                                @foreach($incomeAccounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Amount -->
                                        <div class="col-md-5">
                                            <label class="form-label">Amount ({{ config('app.currency', 'TZS') }}) <span class="text-danger">*</span></label>
                                            <input type="number" name="income_lines[0][amount]" class="form-control amount-input" step="0.01" min="0.01" placeholder="0.00" required>
                                        </div>

                                        <!-- Remove Line Button (hidden for first line) -->
                                        <div class="col-md-2 d-flex align-items-end" id="remove-line-0" style="display: none;">
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-line-btn w-100">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add Line Button -->
                            <div class="mb-4">
                                <button type="button" id="add-line-btn" class="btn btn-outline-primary">
                                    <i class="bx bx-plus me-1"></i> Add Another Line
                                </button>
                            </div>

                            <!-- Total Amount Display -->
                            <div class="row mb-4">
                                <div class="col-md-6 offset-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="mb-1">Total Amount</h5>
                                            <h3 class="text-primary mb-0" id="total-amount">{{ config('app.currency', 'TZS') }} 0.00</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('school.other-income.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Income Records
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Information Panel -->
                    <div class="col-lg-4">
                        <div class="sticky-top" style="top: 20px;">
                            <!-- Information Card -->
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="text-info"><i class="bx bx-calendar me-1"></i> Transaction Date</h6>
                                        <p class="small text-muted">Select the date when the income was received.</p>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="text-info"><i class="bx bx-user me-1"></i> Income Type</h6>
                                        <p class="small text-muted">Choose 'Student' for student-related income or 'Other' for external parties.</p>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="text-info"><i class="bx bx-money me-1"></i> Received In</h6>
                                        <p class="small text-muted">Select the bank account where the income was received.</p>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="text-info"><i class="bx bx-detail me-1"></i> Description</h6>
                                        <p class="small text-muted">Provide a clear description of the income source.</p>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="text-info"><i class="bx bx-wallet me-1"></i> Income Account</h6>
                                        <p class="small text-muted">Select an income account from the chart of accounts (account codes 4000-4999 range).</p>
                                    </div>

                                    <div class="mb-3">
                                        <h6 class="text-info"><i class="bx bx-calculator me-1"></i> Amount</h6>
                                        <p class="small text-muted">Enter the exact amount received in {{ config('app.currency', 'TZS') }}.</p>
                                    </div>

                                    <hr>

                                    <div class="mb-3">
                                        <h6 class="text-warning"><i class="bx bx-plus-circle me-1"></i> Adding Lines</h6>
                                        <p class="small text-muted">Use 'Add Another Line' to record multiple income entries with different accounts and amounts.</p>
                                    </div>

                                    <div class="alert alert-light border">
                                        <small class="text-muted">
                                            <i class="bx bx-check-circle text-success me-1"></i>
                                            All records will be saved as pending and require approval before journal entries are created.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Stats Card -->
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bx bx-bar-chart me-1"></i> Quick Stats</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-primary mb-0" id="line-count">1</h4>
                                                <small class="text-muted">Lines</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success mb-0" id="total-lines-amount">{{ config('app.currency', 'TZS') }} 0.00</h4>
                                            <small class="text-muted">Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    .income-line {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }

    .income-line:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn-outline-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,123,255,0.3);
    }

    .card.bg-light {
        border: 2px solid #007bff;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    .remove-line-btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
        transition: all 0.15s ease-in-out;
    }

    .remove-line-btn:hover {
        background-color: #dc3545;
        border-color: #dc3545;
        color: white;
        transform: scale(1.05);
    }

    .card.border-info .card-header {
        background: linear-gradient(135deg, #17a2b8, #138496);
        border-bottom: 1px solid #17a2b8;
    }

    .sticky-top {
        z-index: 100;
    }

    @media (max-width: 992px) {
        .row {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }

        .col-lg-8, .col-lg-4 {
            margin-bottom: 2rem;
        }

        .sticky-top {
            position: static !important;
        }
    }

    @media (max-width: 768px) {
        .row.g-3 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }

        .col-md-5, .col-md-6, .col-lg-3 {
            margin-bottom: 1rem;
        }

        .d-flex.justify-content-end.gap-2 {
            flex-direction: column;
        }

        .d-flex.justify-content-end.gap-2 .btn {
            margin-bottom: 0.5rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let lineCount = 1;

    console.log('Page loaded, income type dropdown should work now');

    // Handle income type change
    $(document).on('change', '.income-type', function() {
        const $line = $(this).closest('.income-line');
        const incomeType = $(this).val();

        console.log('Income type changed to:', incomeType);

        if (incomeType === 'student') {
            $line.find('.student-field').show();
            $line.find('.other-party-field').hide();
            $line.find('.student-select').attr('required', true);
            $line.find('.other-party-input').attr('required', false).val('');
        } else if (incomeType === 'other') {
            $line.find('.student-field').hide();
            $line.find('.other-party-field').show();
            $line.find('.student-select').attr('required', false).val('');
            $line.find('.other-party-input').attr('required', true);
        } else {
            $line.find('.student-field, .other-party-field').hide();
            $line.find('.student-select, .other-party-input').attr('required', false).val('');
        }
    });

    // Add new income line
    $('#add-line-btn').on('click', function() {
        const lineHtml = `
            <div class="income-line mb-3 p-3 border rounded">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label">Income Account <span class="text-danger">*</span></label>
                        <select name="income_lines[${lineCount}][income_account_id]" class="form-select select2-single" required data-placeholder="Select Account">
                            <option value="">Select Account</option>
                            @foreach($incomeAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Amount ({{ config('app.currency', 'TZS') }}) <span class="text-danger">*</span></label>
                        <input type="number" name="income_lines[${lineCount}][amount]" class="form-control amount-input" step="0.01" min="0.01" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-line-btn w-100">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        $('#income-lines-container').append(lineHtml);
        lineCount++;
        updateTotalAmount();
        updateLineCount();

        // Initialize Select2 for newly added select inputs
        if ($.fn.select2) {
            $('#income-lines-container .select2-single').last().select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: function() {
                    return $(this).data('placeholder') || 'Select an option';
                },
                allowClear: true
            });
        }

        console.log('New line added, total lines:', lineCount);
    });

    // Remove income line
    $(document).on('click', '.remove-line-btn', function() {
        $(this).closest('.income-line').remove();
        updateTotalAmount();
        updateLineCount();
    });

    // Update total amount when amount inputs change
    $(document).on('input', '.amount-input', function() {
        updateTotalAmount();
    });

    // Function to calculate and update total amount
    function updateTotalAmount() {
        let total = 0;
        $('.amount-input').each(function() {
            const value = parseFloat($(this).val()) || 0;
            total += value;
        });
        $('#total-amount').text('{{ config('app.currency', 'TZS') }} ' + total.toFixed(2));
        $('#total-lines-amount').text('{{ config('app.currency', 'TZS') }} ' + total.toFixed(2));
    }

    // Function to update line count
    function updateLineCount() {
        const count = $('.income-line').length;
        $('#line-count').text(count);
    }

    // Form validation before submit
    $('#income-form').on('submit', function(e) {
        const $firstLine = $('.income-line').first();
        const incomeType = $firstLine.find('.income-type').val();
        const amount = parseFloat($firstLine.find('.amount-input').val());

        // Validate first line (which has all fields)
        if (!incomeType) {
            alert('Please select income type');
            e.preventDefault();
            return false;
        }

        if (incomeType === 'student' && !$firstLine.find('.student-select').val()) {
            alert('Please select a student');
            e.preventDefault();
            return false;
        }

        if (incomeType === 'other' && !$firstLine.find('.other-party-input').val().trim()) {
            alert('Please enter other party name');
            e.preventDefault();
            return false;
        }

        if (!$firstLine.find('select[name*="received_in"]').val()) {
            alert('Please select received in method');
            e.preventDefault();
            return false;
        }

        if (!$firstLine.find('input[name*="description"]').val().trim()) {
            alert('Please enter description');
            e.preventDefault();
            return false;
        }

        if (!amount || amount <= 0) {
            alert('Please enter a valid amount for the first line');
            e.preventDefault();
            return false;
        }

        // Validate additional lines (only Income Account and Amount required)
        $('.income-line:not(:first)').each(function(index) {
            const $line = $(this);
            const lineAmount = parseFloat($line.find('.amount-input').val());

            if (!$line.find('select[name*="income_account_id"]').val()) {
                alert(`Please select income account for additional line ${index + 2}`);
                e.preventDefault();
                return false;
            }

            if (!lineAmount || lineAmount <= 0) {
                alert(`Please enter a valid amount for additional line ${index + 2}`);
                e.preventDefault();
                return false;
            }
        });

        // Show loading state
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
    });

    // Initialize Select2 for better dropdowns
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
});
</script>
@endpush