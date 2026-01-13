@extends('layouts.main')

@section('title', 'Salary Advance Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'HR & Payroll', 'url' => route('hr-payroll.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Salary Advances', 'url' => route('hr.salary-advances.index'), 'icon' => 'bx bx-credit-card'],
            ['label' => $salaryAdvance->reference, 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0 text-uppercase">SALARY ADVANCE DETAILS</h4>
                    <p class="text-muted mb-0">{{ $salaryAdvance->reference }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('hr.salary-advances.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i>Back to List
                    </a>
                </div>
            </div>


            <div class="row">
                <!-- Basic Information -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Reference</label>
                                    <p class="mb-0">{{ $salaryAdvance->reference }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Date</label>
                                    <p class="mb-0">{{ $salaryAdvance->formatted_date }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Employee</label>
                                    <p class="mb-0">{{ $salaryAdvance->employee->full_name ?? 'N/A' }}</p>
                                    @if($salaryAdvance->employee)
                                        <small class="text-muted">{{ $salaryAdvance->employee->employee_number }}</small>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Bank Account</label>
                                    <p class="mb-0">{{ $salaryAdvance->bankAccount->name ?? 'N/A' }}</p>
                                    @if($salaryAdvance->bankAccount)
                                        <small class="text-muted">{{ $salaryAdvance->bankAccount->account_number }}</small>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Amount</label>
                                    <p class="mb-0 fs-5 fw-bold text-primary">TZS
                                        {{ number_format($salaryAdvance->amount, 2) }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Monthly Deduction</label>
                                    <p class="mb-0 fs-5 fw-bold text-info">TZS
                                        {{ number_format($salaryAdvance->monthly_deduction, 2) }}
                                    </p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Reason</label>
                                    <p class="mb-0">{{ $salaryAdvance->reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar Information -->
                <div class="col-md-4">
                    <!-- Created By -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Created By</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="bx bx-user-circle text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">{{ $salaryAdvance->user->name ?? 'N/A' }}</h6>
                                    <small class="text-muted">{{ $salaryAdvance->created_at->format('M d, Y H:i') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Information -->
                    @if($salaryAdvance->branch)
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Branch</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bx bx-building text-info" style="font-size: 2rem;"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">{{ $salaryAdvance->branch->name }}</h6>
                                        <small class="text-muted">{{ $salaryAdvance->branch->address ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        <!-- Deduction History -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bx bx-history me-2"></i>Deduction History</h6>
                    </div>
                    <div class="card-body">
                        @if($deductionHistory && $deductionHistory->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="deductionHistoryTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Payroll Period</th>
                                            <th>Payroll Reference</th>
                                            <th class="text-end">Amount Deducted</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($deductionHistory as $index => $payrollEmployee)
                                            @php
                                                $payroll = $payrollEmployee->payroll;
                                                $monthName = \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F');
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $monthName }} {{ $payroll->year }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ $payroll->reference ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <strong class="text-danger">TZS {{ number_format($payrollEmployee->salary_advance, 2) }}</strong>
                                                </td>
                                                <td>
                                                    @if($payroll->status === 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($payroll->status === 'paid')
                                                        <span class="badge bg-primary">Paid</span>
                                                    @elseif($payroll->status === 'processing')
                                                        <span class="badge bg-warning">Processing</span>
                                                    @elseif($payroll->status === 'draft')
                                                        <span class="badge bg-secondary">Draft</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ ucfirst($payroll->status) }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('hr.payrolls.show', $payroll->hash_id) }}" 
                                                       class="btn btn-sm btn-primary" title="View Payroll">
                                                        <i class="bx bx-show"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <td colspan="3" class="text-end"><strong>Total Deducted:</strong></td>
                                            <td class="text-end">
                                                <strong>TZS {{ number_format($deductionHistory->sum('salary_advance'), 2) }}</strong>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info mb-0">
                                <i class="bx bx-info-circle me-2"></i>No deduction history found for this salary advance.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
@if($deductionHistory && $deductionHistory->count() > 0)
<script>
    $(document).ready(function() {
        var table = $('#deductionHistoryTable').DataTable({
            order: [[1, 'desc']],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="bx bx-spreadsheet"></i> Excel',
                    className: 'btn btn-success btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
                        format: {
                            body: function(data, row, column, node) {
                                // Remove HTML tags and badges for export
                                if (typeof data === 'string') {
                                    var $data = $('<div>').html(data);
                                    return $data.text().trim() || data;
                                }
                                return data;
                            }
                        }
                    },
                    title: 'Salary Advance Deduction History - {{ $salaryAdvance->reference }}',
                    filename: 'salary_advance_deduction_history_{{ $salaryAdvance->reference }}'
                },
                {
                    extend: 'pdf',
                    text: '<i class="bx bx-file"></i> PDF',
                    className: 'btn btn-danger btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
                        format: {
                            body: function(data, row, column, node) {
                                if (typeof data === 'string') {
                                    var $data = $('<div>').html(data);
                                    return $data.text().trim() || data;
                                }
                                return data;
                            }
                        }
                    },
                    title: 'Salary Advance Deduction History - {{ $salaryAdvance->reference }}',
                    filename: 'salary_advance_deduction_history_{{ $salaryAdvance->reference }}',
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="bx bx-printer"></i> Print',
                    className: 'btn btn-info btn-sm',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4],
                        format: {
                            body: function(data, row, column, node) {
                                if (typeof data === 'string') {
                                    var $data = $('<div>').html(data);
                                    return $data.text().trim() || data;
                                }
                                return data;
                            }
                        }
                    }
                }
            ],
            language: {
                processing: "Loading deduction history...",
                emptyTable: "No deduction history found",
                zeroRecords: "No matching records found"
            }
        });

        // Place buttons in the card header after DataTable is initialized
        setTimeout(function() {
            var cardHeader = $('#deductionHistoryTable').closest('.card').find('.card-header');
            var buttonsContainer = table.buttons().container();
            if (buttonsContainer.length && cardHeader.length) {
                buttonsContainer
                    .addClass('btn-group')
                    .css('margin-left', 'auto')
                    .appendTo(cardHeader);
            }
        }, 100);
    });
</script>
@endif
@endpush

@endsection
