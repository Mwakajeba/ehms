@extends('layouts.main')

@section('title', 'Other Income Collection Report')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'School Reports', 'url' => route('school.reports.index'), 'icon' => 'bx bx-bar-chart'],
            ['label' => 'Other Income Collection', 'url' => '#', 'icon' => 'bx bx-money']
        ]" />
        <h6 class="mb-0 text-uppercase">OTHER INCOME COLLECTION REPORT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div><i class="bx bx-money me-1 font-22 text-success"></i></div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-success btn-modern" onclick="exportToExcel()">
                                    <i class="bx bx-file me-1"></i> Export Excel
                                </button>
                                <button type="button" class="btn btn-danger btn-modern" onclick="exportToPDF()">
                                    <i class="bx bx-file-pdf me-1"></i> Export PDF
                                </button>
                            </div>
                        </div>
                        <hr />

                        <!-- Filters -->
                        <form method="GET" action="{{ route('school.reports.other-income-collection') }}" id="filterForm">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="date_from" class="form-label">Date From</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control"
                                           value="{{ $dateFrom }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="date_to" class="form-label">Date To</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control"
                                           value="{{ $dateTo }}">
                                </div>
                                <div class="col-md-2">
                                    <label for="class_id" class="form-label">Class</label>
                                    <select name="class_id" id="class_id" class="form-select select2-single">
                                        <option value="">All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }} ({{ $class->students_count }} students)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="stream_id" class="form-label">Stream</label>
                                    <select name="stream_id" id="stream_id" class="form-select select2-single">
                                        <option value="">All Streams</option>
                                        @foreach($streams as $stream)
                                            <option value="{{ $stream->id }}" {{ (isset($streamId) && $streamId == $stream->id) ? 'selected' : '' }}>
                                                {{ $stream->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="account_id" class="form-label">Account</label>
                                    <select name="account_id" id="account_id" class="form-select select2-single">
                                        <option value="">All Accounts</option>
                                        @foreach($incomeAccounts as $account)
                                            <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_code }} - {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search me-1"></i> Filter
                                    </button>
                                    <a href="{{ route('school.reports.other-income-collection') }}" class="btn btn-secondary">
                                        <i class="bx bx-reset me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>

                        <!-- Report Data -->
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="otherIncomeTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Stream</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount (TZS)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($otherIncomeData as $income)
                                        <tr>
                                            <td>{{ $income->transaction_date->format('Y-m-d') }}</td>
                                            <td>{{ $income->student ? $income->student->first_name . ' ' . $income->student->last_name : $income->other_party }}</td>
                                            <td>{{ $income->student && $income->student->class ? $income->student->class->name : '-' }}</td>
                                            <td>{{ $income->student && $income->student->stream ? $income->student->stream->name : '-' }}</td>
                                            <td>{{ $income->incomeAccount ? $income->incomeAccount->account_name : '-' }}</td>
                                            <td>{{ $income->description }}</td>
                                            <td class="text-end">{{ number_format($income->amount, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No data found for the selected criteria.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <th colspan="6" class="text-end">Total:</th>
                                        <th class="text-end">{{ number_format($totalAmount, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
    .table th, .table td {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for filter dropdowns
    $('.select2-single').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });
});

function exportToExcel() {
    const url = new URL(window.location);
    url.searchParams.set('export', 'excel');
    window.open(url.toString(), '_blank');
}

function exportToPDF() {
    const url = new URL(window.location);
    url.searchParams.set('export', 'pdf');
    window.open(url.toString(), '_blank');
}
</script>
@endpush