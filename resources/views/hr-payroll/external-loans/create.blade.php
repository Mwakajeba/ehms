@extends('layouts.main')

@section('title', 'Create External Loan')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.select2-single').select2({
            width: '100%',
            placeholder: 'Select an option',
            allowClear: true
        });
    });
</script>
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'HR & Payroll', 'url' => route('hr-payroll.index'), 'icon' => 'bx bx-user'],
            ['label' => 'External Loans', 'url' => route('hr.external-loans.index'), 'icon' => 'bx bx-credit-card-alt'],
            ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
        ]" />
        <h6 class="mb-0 text-uppercase">Create External Loan</h6>
        <hr />

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-plus me-2"></i>New External Loan</h6>
                    </div>
                    <div class="card-body">
                        @include('hr-payroll.external-loans._form', [
                            'action' => route('hr.external-loans.store'),
                            'method' => 'POST',
                            'loan' => null,
                            'employees' => $employees,
                        ])
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="bx bx-info-circle text-info me-2"></i>Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <h6 class="text-primary mb-2"><i class="bx bx-help-circle me-1"></i>What is an External Loan?</h6>
                            <p class="small text-muted">An external loan is a financial obligation taken by an employee from a third-party institution, repaid through monthly payroll deductions.</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-success mb-2"><i class="bx bx-list-ol me-1"></i>Process Steps</h6>
                            <ol class="small ps-3 mb-0">
                                <li>Select the employee</li>
                                <li>Enter institution name</li>
                                <li>Specify total loan and monthly deduction</li>
                                <li>Set start and end dates</li>
                                <li>Mark loan as active if applicable</li>
                            </ol>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-warning mb-2"><i class="bx bx-error-circle me-1"></i>Important Notes</h6>
                            <ul class="small text-muted ps-3 mb-0">
                                <li>Monthly deduction will be automatically applied to payroll</li>
                                <li>Ensure institution name matches official records</li>
                                <li>End date is optional, but recommended for tracking</li>
                                <li>Only one active loan per institution per employee is allowed</li>
                                <li>Loan status can be updated later</li>
                            </ul>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-info mb-2"><i class="bx bx-calculator me-1"></i>Repayment Calculation</h6>
                            <div class="border-start border-3 border-primary ps-3">
                                <span class="small">Repayment period = Total Loan ÷ Monthly Deduction</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


