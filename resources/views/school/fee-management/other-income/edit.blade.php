@extends('layouts.main')

@section('title', 'Edit Other Income')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Fee Management', 'url' => route('school.fee-management.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Other Income Collection', 'url' => route('school.other-income.index'), 'icon' => 'bx bx-money'],
            ['label' => 'Edit Income', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT OTHER INCOME</h6>
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-2"></i>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <!-- Main Form Card -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <div class="card-title d-flex align-items-center mb-0">
                            <i class="bx bx-edit me-2"></i>
                            <h5 class="mb-0">Edit Income Record</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('school.other-income.update', $otherIncome) }}" method="POST" id="incomeForm">
                            @csrf
                            @method('PUT')

                            <!-- Transaction Details Section -->
                            <div class="mb-4">
                                <h6 class="section-title">
                                    <i class="bx bx-calendar me-1"></i> Transaction Details
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="date" name="transaction_date" class="form-control"
                                                   id="transaction_date" value="{{ $otherIncome->transaction_date->format('Y-m-d') }}" required>
                                            <label for="transaction_date">
                                                <i class="bx bx-calendar me-1"></i> Transaction Date <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="income_type" class="form-label">
                                            <i class="bx bx-category me-1"></i> Income Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="income_type" class="form-select income-type select2-single" id="income_type" required>
                                            <option value="">Select Type</option>
                                            <option value="student" {{ $otherIncome->income_type === 'student' ? 'selected' : '' }}>Student</option>
                                            <option value="other" {{ $otherIncome->income_type === 'other' ? 'selected' : '' }}>Other Party</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Party Information Section -->
                            <div class="mb-4">
                                <h6 class="section-title">
                                    <i class="bx bx-user me-1"></i> Party Information
                                </h6>
                                <div class="row g-3">
                                    <!-- Student Selection -->
                                    <div class="col-12 student-field" style="{{ $otherIncome->income_type === 'student' ? '' : 'display: none;' }}">
                                        <label for="student_id" class="form-label">
                                            <i class="bx bx-user me-1"></i> Student <span class="text-danger">*</span>
                                        </label>
                                        <select name="student_id" class="form-select student-select select2-single" id="student_id" {{ $otherIncome->income_type === 'student' ? 'required' : '' }}>
                                            <option value="">Select Student</option>
                                            @foreach($students as $student)
                                                <option value="{{ $student->id }}" {{ $otherIncome->student_id == $student->id ? 'selected' : '' }}>
                                                    {{ $student->name }} ({{ $student->student_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Other Party -->
                                    <div class="col-12 other-party-field" style="{{ $otherIncome->income_type === 'other' ? '' : 'display: none;' }}">
                                        <div class="form-floating">
                                            <input type="text" name="other_party" class="form-control other-party-input"
                                                   id="other_party" value="{{ $otherIncome->other_party }}"
                                                   placeholder="Enter party name" {{ $otherIncome->income_type === 'other' ? 'required' : '' }}>
                                            <label for="other_party">
                                                <i class="bx bx-building me-1"></i> Other Party <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Details Section -->
                            <div class="mb-4">
                                <h6 class="section-title">
                                    <i class="bx bx-money me-1"></i> Financial Details
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="received_in" class="form-label">
                                            <i class="bx bx-wallet me-1"></i> Received In <span class="text-danger">*</span>
                                        </label>
                                        <select name="received_in" class="form-select select2-single" id="received_in" required>
                                            <option value="">Select Bank Account</option>
                                            @foreach($bankAccounts as $bankAccount)
                                                <option value="{{ $bankAccount->id }}" {{ $otherIncome->received_in == $bankAccount->id ? 'selected' : '' }}>
                                                    {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="income_account_id" class="form-label">
                                            <i class="bx bx-chart me-1"></i> Income Account <span class="text-danger">*</span>
                                        </label>
                                        <select name="income_account_id" class="form-select select2-single" id="income_account_id" required>
                                            <option value="">Select Account</option>
                                            @foreach($incomeAccounts as $account)
                                                <option value="{{ $account->id }}" {{ $otherIncome->income_account_id == $account->id ? 'selected' : '' }}>
                                                    {{ $account->account_code }} - {{ $account->account_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="number" name="amount" class="form-control" id="amount"
                                                   value="{{ $otherIncome->amount }}" step="0.01" min="0.01"
                                                   placeholder="0.00" required>
                                            <label for="amount">
                                                <i class="bx bx-money me-1"></i> Amount ({{ config('app.currency', 'TZS') }}) <span class="text-danger">*</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Description Section -->
                            <div class="mb-4">
                                <h6 class="section-title">
                                    <i class="bx bx-detail me-1"></i> Transaction Description
                                </h6>
                                <div class="form-floating">
                                    <textarea name="description" class="form-control" id="description"
                                              placeholder="Enter transaction description" style="height: 80px;" required>{{ $otherIncome->description }}</textarea>
                                    <label for="description">
                                        <i class="bx bx-detail me-1"></i> Description <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div class="text-muted small">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Fields marked with <span class="text-danger">*</span> are required
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('school.other-income.show', $otherIncome) }}" class="btn btn-outline-info">
                                        <i class="bx bx-show me-1"></i> View
                                    </a>
                                    <a href="{{ route('school.other-income.index') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-warning" id="submitBtn">
                                        <i class="bx bx-save me-1"></i> Update Record
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Information Sidebar -->
            <div class="col-lg-4">
                <!-- Current Information Card -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-info-circle me-1"></i> Current Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Record ID</small>
                            <strong>#{{ $otherIncome->id }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Created</small>
                            <strong>{{ $otherIncome->created_at->format('M d, Y H:i') }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Created By</small>
                            <strong>{{ $otherIncome->creator->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Company</small>
                            <strong>{{ $otherIncome->company->name ?? 'N/A' }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Branch</small>
                            <strong>{{ $otherIncome->branch->name ?? 'N/A' }}</strong>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-cog me-1"></i> Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('school.other-income.show', $otherIncome) }}" class="btn btn-outline-info btn-sm">
                                <i class="bx bx-show me-1"></i> View Details
                            </a>
                            <a href="{{ route('school.other-income.export-pdf', $otherIncome) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="bx bx-download me-1"></i> Export PDF
                            </a>
                            <a href="{{ route('school.other-income.create') }}" class="btn btn-outline-success btn-sm">
                                <i class="bx bx-plus me-1"></i> Add New
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize form validation
    $('#incomeForm').validate({
        rules: {
            transaction_date: {
                required: true
            },
            income_type: {
                required: true
            },
            student_id: {
                required: function(element) {
                    return $('#income_type').val() === 'student';
                }
            },
            other_party: {
                required: function(element) {
                    return $('#income_type').val() === 'other';
                }
            },
            description: {
                required: true,
                minlength: 5
            },
            received_in: {
                required: true
            },
            income_account_id: {
                required: true
            },
            amount: {
                required: true,
                min: 0.01
            }
        },
        messages: {
            transaction_date: {
                required: "Please select a transaction date"
            },
            income_type: {
                required: "Please select an income type"
            },
            student_id: {
                required: "Please select a student"
            },
            other_party: {
                required: "Please enter the other party name"
            },
            description: {
                required: "Please enter a description",
                minlength: "Description must be at least 5 characters"
            },
            received_in: {
                required: "Please select a bank account"
            },
            income_account_id: {
                required: "Please select an income account"
            },
            amount: {
                required: "Please enter an amount",
                min: "Amount must be greater than 0"
            }
        },
        errorElement: 'div',
        errorClass: 'invalid-feedback',
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        },
        errorPlacement: function(error, element) {
            error.insertAfter(element);
        }
    });

    // Handle income type change
    $('.income-type').change(function() {
        var incomeType = $(this).val();

        if (incomeType === 'student') {
            $('.student-field').show();
            $('.other-party-field').hide();
            $('.student-select').prop('required', true);
            $('.other-party-input').prop('required', false);
        } else if (incomeType === 'other') {
            $('.student-field').hide();
            $('.other-party-field').show();
            $('.student-select').prop('required', false);
            $('.other-party-input').prop('required', true);
        } else {
            $('.student-field').hide();
            $('.other-party-field').hide();
            $('.student-select').prop('required', false);
            $('.other-party-input').prop('required', false);
        }
    });

    // Format amount input
    $('#amount').on('input', function() {
        var value = $(this).val();
        if (value) {
            $(this).val(parseFloat(value).toFixed(2));
        }
    });

    // Submit button loading state
    $('#incomeForm').on('submit', function(e) {
        if ($(this).valid()) {
            $('#submitBtn').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Updating...');
        }
    });

    // Initialize Select2 for better dropdowns if available
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

@endsection

@push('styles')
<style>
.section-title {
    color: #495057;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 8px;
    margin-bottom: 20px;
}

.form-floating > .form-control:focus,
.form-floating > .form-select:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.card-header.bg-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
}

.btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.3);
}

.alert {
    border-radius: 8px;
    border: none;
}

.card {
    border-radius: 12px;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border: none;
}

.card-body {
    padding: 2rem;
}

.form-floating > label {
    color: #6c757d;
    font-weight: 500;
}

.text-danger {
    color: #dc3545 !important;
}

.btn-outline-info:hover,
.btn-outline-primary:hover,
.btn-outline-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem;
    }

    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }

    .d-flex.gap-2 {
        justify-content: center;
    }
}
</style>
@endpush

@push('styles')
<style>
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
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    .badge {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    @media (max-width: 768px) {
        .row.g-3 {
            --bs-gutter-x: 1rem;
            --bs-gutter-y: 1rem;
        }

        .col-md-6, .col-lg-4 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Handle income type change
    $('.income-type').on('change', function() {
        const incomeType = $(this).val();

        if (incomeType === 'student') {
            $('.student-field').show();
            $('.other-party-field').hide();
            $('.student-select').attr('required', true);
            $('.other-party-input').attr('required', false).val('');
        } else if (incomeType === 'other') {
            $('.student-field').hide();
            $('.other-party-field').show();
            $('.student-select').attr('required', false).val('');
            $('.other-party-input').attr('required', true);
        } else {
            $('.student-field, .other-party-field').hide();
            $('.student-select, .other-party-input').attr('required', false).val('');
        }
    });

    // Form validation before submit
    $('form').on('submit', function(e) {
        const incomeType = $('.income-type').val();
        const amount = parseFloat($('input[name="amount"]').val());

        if (!incomeType) {
            alert('Please select income type');
            e.preventDefault();
            return false;
        }

        if (incomeType === 'student' && !$('select[name="student_id"]').val()) {
            alert('Please select a student');
            e.preventDefault();
            return false;
        }

        if (incomeType === 'other' && !$('input[name="other_party"]').val().trim()) {
            alert('Please enter other party name');
            e.preventDefault();
            return false;
        }

        if (!amount || amount <= 0) {
            alert('Please enter a valid amount');
            e.preventDefault();
            return false;
        }

        // Show loading state
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Updating...');
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