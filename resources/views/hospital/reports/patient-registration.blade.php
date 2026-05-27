@extends('layouts.main')

@section('title', 'Patient Registration Report')

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .page-wrapper { padding: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reports', 'url' => route('hospital.reports.index'), 'icon' => 'bx bx-file'],
                ['label' => 'Patient Registration', 'url' => '#', 'icon' => 'bx bx-user-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">PATIENT REGISTRATION REPORT</h6>
            <hr />

            <div class="card mb-4 no-print">
                <div class="card-body">
                    <form method="GET" action="{{ route('hospital.reports.patient-registration') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date"
                                   value="{{ $startDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date"
                                   value="{{ $endDate->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-filter me-1"></i>Generate Report
                            </button>
                            <a href="{{ route('hospital.reports.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-none d-print-block mb-3">
                <h4 class="mb-1">Patient Registration Report</h4>
                <p class="mb-0 text-muted">
                    Period: {{ $startDate->format('d M Y') }} — {{ $endDate->format('d M Y') }}
                </p>
                <p class="mb-0 text-muted small">Generated: {{ now()->format('d M Y, H:i') }}</p>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Total Registered</h6>
                            <h3 class="mb-0">{{ number_format($summary['total']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0">{{ number_format($summary['active']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Male</h6>
                            <h3 class="mb-0">{{ number_format($summary['male']) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-warning">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-1">Female</h6>
                            <h3 class="mb-0">{{ number_format($summary['female']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            @if($byInsurance->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="bx bx-shield-quarter me-2"></i>By Insurance Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Insurance</th>
                                        <th class="text-end">Patients</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($byInsurance as $insurance => $count)
                                        <tr>
                                            <td>{{ $insurance }}</td>
                                            <td class="text-end">{{ number_format($count) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-list-ul me-2"></i>Registered Patients
                        <span class="badge bg-primary ms-2">{{ number_format($patients->count()) }}</span>
                    </h5>
                    <div class="no-print">
                        <a class="btn btn-sm btn-outline-success me-1"
                           href="{{ route('hospital.reports.patient-registration.export.excel', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}">
                            <i class="bx bx-spreadsheet me-1"></i>Excel
                        </a>
                        <a class="btn btn-sm btn-outline-danger me-1"
                           href="{{ route('hospital.reports.patient-registration.export.pdf', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}">
                            <i class="bx bxs-file-pdf me-1"></i>PDF
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bx bx-printer me-1"></i>Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small no-print mb-3">
                        Showing patients registered from <strong>{{ $startDate->format('d M Y') }}</strong>
                        to <strong>{{ $endDate->format('d M Y') }}</strong>.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>MRN</th>
                                    <th>Full Name</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Phone</th>
                                    <th>Insurance</th>
                                    <th>Admitted Date</th>
                                    <th>Registered At</th>
                                    <th>Registered By</th>
                                    <th>Status</th>
                                    <th class="no-print">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($patients as $index => $patient)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td><strong>{{ $patient->mrn }}</strong></td>
                                        <td>{{ $patient->full_name }}</td>
                                        <td>{{ $patient->gender ? ucfirst($patient->gender) : '—' }}</td>
                                        <td>{{ $patient->age ?? '—' }}</td>
                                        <td>{{ $patient->phone ?? '—' }}</td>
                                        <td>
                                            @if($patient->insurance_type_name !== 'None')
                                                <span class="badge bg-info">{{ $patient->insurance_type_name }}</span>
                                            @else
                                                <span class="text-muted">None</span>
                                            @endif
                                        </td>
                                        <td>{{ $patient->admitted_date ? $patient->admitted_date->format('d M Y') : '—' }}</td>
                                        <td>{{ $patient->created_at->format('d M Y, H:i') }}</td>
                                        <td>{{ $patient->creator?->name ?? '—' }}</td>
                                        <td>
                                            @if($patient->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="no-print">
                                            <a href="{{ route('hospital.reception.patients.show', $patient->id) }}"
                                               class="btn btn-sm btn-outline-info" title="View patient">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted py-4">
                                            No patients registered in this date range.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
