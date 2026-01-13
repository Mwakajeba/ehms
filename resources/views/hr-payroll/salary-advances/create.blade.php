@extends('layouts.main')

@section('title', 'Create Salary Advance')

@push('styles')
<style>
    .alert-sm {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }
    
    .border-start {
        border-left-width: 3px !important;
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: box-shadow 0.3s ease-in-out;
    }
    
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .step-number {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #0d6efd;
        color: white;
        font-size: 13px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .calculation-box {
        background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 15px;
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'HR & Payroll', 'url' => route('hr-payroll.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Salary Advances', 'url' => route('hr.salary-advances.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />

            <div class="row">
                <div class="col-lg-8">
                    <!-- Page Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0 text-uppercase">CREATE SALARY ADVANCE</h4>
                        </div>
                    </div>

                    <!-- Form -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bx bx-plus me-2"></i>New Salary Advance Request
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('hr.salary-advances.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <!-- Employee Selection -->
                                    <div class="col-md-6 mb-3">
                                        <label for="employee_id" class="form-label">Employee <span
                                                class="text-danger">*</span></label>
                                        <select name="employee_id" id="employee_id"
                                            class="form-select select2-single @error('employee_id') is-invalid @enderror" required>
                                            <option value="">Select Employee</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->full_name }} ({{ $employee->employee_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('employee_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Bank Account -->
                                    <div class="col-md-6 mb-3">
                                        <label for="bank_account_id" class="form-label">Bank Account <span
                                                class="text-danger">*</span></label>
                                        <select name="bank_account_id" id="bank_account_id"
                                            class="form-select select2-single @error('bank_account_id') is-invalid @enderror"
                                            required>
                                            <option value="">Select Bank Account</option>
                                            @foreach($bankAccounts as $bankAccount)
                                                <option value="{{ $bankAccount->id }}" {{ old('bank_account_id') == $bankAccount->id ? 'selected' : '' }}>
                                                    {{ $bankAccount->name }} ({{ $bankAccount->account_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bank_account_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Date -->
                                    <div class="col-md-6 mb-3">
                                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                        <input type="date" name="date" id="date"
                                            class="form-control @error('date') is-invalid @enderror"
                                            value="{{ old('date', date('Y-m-d')) }}" required>
                                        @error('date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Branch -->
                                    <div class="col-md-6 mb-3">
                                        <label for="branch_id" class="form-label">Branch</label>
                                        <select name="branch_id" id="branch_id"
                                            class="form-select select2-single @error('branch_id') is-invalid @enderror">
                                            <option value="">Select Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Amount -->
                                    <div class="col-md-6 mb-3">
                                        <label for="amount" class="form-label">Amount (TZS) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="amount" id="amount"
                                            class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}"
                                           required>
                                        @error('amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Monthly Deduction -->
                                    <div class="col-md-6 mb-3">
                                        <label for="monthly_deduction" class="form-label">Monthly Deduction (TZS) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" name="monthly_deduction" id="monthly_deduction"
                                            class="form-control @error('monthly_deduction') is-invalid @enderror"
                                            value="{{ old('monthly_deduction') }}"  required>
                                        @error('monthly_deduction')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Reason -->
                                    <div class="col-12 mb-3">
                                        <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                                        <textarea name="reason" id="reason"
                                            class="form-control @error('reason') is-invalid @enderror" rows="4"
                                            placeholder="Please provide a detailed reason for the salary advance..."
                                            required>{{ old('reason') }}</textarea>
                                        @error('reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row">
                                    <div class="col-12">
                                        <hr>
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('hr.salary-advances.index') }}" class="btn btn-secondary">Cancel</a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Create Salary Advance
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Guidelines Card -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bx bx-info-circle text-info me-2"></i>Salary Advance Guidelines
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="text-primary mb-3">
                                    <i class="bx bx-help-circle me-1"></i>What is a Salary Advance?
                                </h6>
                                <p class="small text-muted">
                                    A salary advance is an early payment of future salary to help employees meet urgent financial needs. The amount is deducted from subsequent salary payments.
                                </p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-primary mb-3">
                                    <i class="bx bx-list-ol me-1"></i>Process Steps
                                </h6>
                                <div class="small">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="step-number me-3 flex-shrink-0">1</div>
                                        <div>
                                            <strong>Select Employee:</strong> Choose the employee requesting the advance
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="step-number me-3 flex-shrink-0">2</div>
                                        <div>
                                            <strong>Choose Bank Account:</strong> Select the bank account for payment
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="step-number me-3 flex-shrink-0">3</div>
                                        <div>
                                            <strong>Set Amount:</strong> Enter the advance amount requested
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="step-number me-3 flex-shrink-0">4</div>
                                        <div>
                                            <strong>Monthly Deduction:</strong> Set how much to deduct monthly
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <div class="step-number me-3 flex-shrink-0">5</div>
                                        <div>
                                            <strong>Document Reason:</strong> Provide detailed justification
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-success mb-2">
                                    <i class="bx bx-calculator me-1"></i>Repayment Calculation
                                </h6>
                                <div class="calculation-box">
                                    <div class="small">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Advance Amount:</span>
                                            <strong>TZS X</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Monthly Deduction:</span>
                                            <strong>TZS Y</strong>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Repayment Period:</strong></span>
                                            <strong class="text-primary">X ÷ Y = Z months</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Policy Guidelines Card -->
                    <div class="card">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h6 class="mb-0 text-warning">
                                <i class="bx bx-shield me-2"></i>Policy Guidelines
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    Maximum advance: 50% of monthly salary
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    Minimum employment: 6 months
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    One active advance per employee
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check text-success me-2"></i>
                                    Valid reason required
                                </li>
                                <li class="mb-0">
                                    <i class="bx bx-check text-success me-2"></i>
                                    Supervisor approval needed
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Common Reasons Card -->
                    <div class="card">
                        <div class="card-header bg-info bg-opacity-10">
                            <h6 class="mb-0 text-info">
                                <i class="bx bx-list-check me-2"></i>Common Valid Reasons
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="small">
                                <div class="border-start border-3 border-primary ps-3 mb-3">
                                    <strong>Medical Emergency</strong><br>
                                    <span class="text-muted">Urgent medical expenses for employee or family</span>
                                </div>
                                <div class="border-start border-3 border-info ps-3 mb-3">
                                    <strong>Education Fees</strong><br>
                                    <span class="text-muted">School fees for children or professional development</span>
                                </div>
                                <div class="border-start border-3 border-success ps-3 mb-3">
                                    <strong>Home Emergency</strong><br>
                                    <span class="text-muted">Urgent home repairs or rent payment</span>
                                </div>
                                <div class="border-start border-3 border-warning ps-3">
                                    <strong>Family Event</strong><br>
                                    <span class="text-muted">Wedding, funeral, or other family obligations</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes Card -->
                    <div class="card border-danger">
                        <div class="card-header bg-danger">
                            <h6 class="mb-0 text-white">
                                <i class="bx bx-error-circle me-2"></i>Important Notes
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning alert-sm mb-3">
                                <small>
                                    <i class="bx bx-info-circle me-1"></i>
                                    <strong>Automatic Deduction:</strong> Monthly deductions will be automatically applied to payroll until the advance is fully repaid.
                                </small>
                            </div>
                            <div class="alert alert-info alert-sm mb-0">
                                <small>
                                    <i class="bx bx-shield me-1"></i>
                                    <strong>Employment Status:</strong> Advance balance becomes immediately due if employment is terminated.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

