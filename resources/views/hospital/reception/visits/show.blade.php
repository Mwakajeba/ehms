@extends('layouts.main')

@section('title', 'Visit Details')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reception', 'url' => route('hospital.reception.index'), 'icon' => 'bx bx-user-plus'],
                ['label' => 'Visit Details', 'url' => '#', 'icon' => 'bx bx-show']
            ]" />
            <h6 class="mb-0 text-uppercase">VISIT DETAILS</h6>
            <hr />

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bx bx-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <!-- Visit Information -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-info-circle me-2"></i>Visit Information
                            </h5>
                            <div>
                                <a href="{{ route('hospital.reception.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Reception
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Visit Number:</th>
                                            <td><strong class="text-primary">{{ $visit->visit_number }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Patient:</th>
                                            <td>
                                                <a href="{{ route('hospital.reception.patients.show', $visit->patient->id) }}">
                                                    {{ $visit->patient->full_name }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>MRN:</th>
                                            <td>{{ $visit->patient->mrn }}</td>
                                        </tr>
                                        <tr>
                                            <th>Visit Type:</th>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'in_progress' => 'primary',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $color = $statusColors[$visit->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst(str_replace('_', ' ', $visit->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Visit Date:</th>
                                            <td>{{ $visit->visit_date->format('d M Y, H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Chief Complaint:</th>
                                            <td>{{ $visit->chief_complaint ?? 'N/A' }}</td>
                                        </tr>
                                        @if($visit->completed_at)
                                            <tr>
                                                <th>Completed At:</th>
                                                <td>{{ $visit->completed_at->format('d M Y, H:i') }}</td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <th>Created By:</th>
                                            <td>{{ $visit->creator->name ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Department Routing -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-building me-2"></i>Department Routing
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($visit->visitDepartments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Sequence</th>
                                                <th>Department</th>
                                                <th>Type</th>
                                                <th>Status</th>
                                                <th>Waiting Time</th>
                                                <th>Service Time</th>
                                                <th>Served By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($visit->visitDepartments->sortBy('sequence') as $visitDept)
                                                @php
                                                    $statusColors = [
                                                        'waiting' => 'warning',
                                                        'in_service' => 'primary',
                                                        'completed' => 'success',
                                                        'skipped' => 'secondary'
                                                    ];
                                                    $color = $statusColors[$visitDept->status] ?? 'secondary';
                                                @endphp
                                                <tr>
                                                    <td>{{ $visitDept->sequence }}</td>
                                                    <td>{{ $visitDept->department->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            {{ ucfirst(str_replace('_', ' ', $visitDept->department->type ?? 'N/A')) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $color }}">
                                                            {{ ucfirst(str_replace('_', ' ', $visitDept->status)) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $visitDept->waiting_time_formatted ?? '00:00:00' }}</td>
                                                    <td>{{ $visitDept->service_time_formatted ?? '00:00:00' }}</td>
                                                    <td>{{ $visitDept->servedBy->name ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="bx bx-info-circle text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mt-2">No departments assigned to this visit.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Bills -->
                @if($visit->bills->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-money me-2"></i>Bills
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($visit->bills as $bill)
                                    <div class="card border mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1">
                                                        Bill #: <strong>{{ $bill->bill_number }}</strong>
                                                        <span class="badge bg-info ms-2">{{ ucfirst(str_replace('_', ' ', $bill->bill_type)) }}</span>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Created: {{ $bill->created_at->format('d M Y, H:i') }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <div>
                                                        @php
                                                            $paymentStatusColors = [
                                                                'pending' => 'warning',
                                                                'partial' => 'info',
                                                                'paid' => 'success',
                                                                'cancelled' => 'danger'
                                                            ];
                                                            $paymentColor = $paymentStatusColors[$bill->payment_status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $paymentColor }}">
                                                            {{ ucfirst($bill->payment_status) }}
                                                        </span>
                                                    </div>
                                                    <div class="mt-1">
                                                        @php
                                                            $clearanceColors = [
                                                                'pending' => 'warning',
                                                                'cleared' => 'success',
                                                                'cancelled' => 'danger'
                                                            ];
                                                            $clearanceColor = $clearanceColors[$bill->clearance_status] ?? 'secondary';
                                                        @endphp
                                                        <span class="badge bg-{{ $clearanceColor }}">
                                                            {{ ucfirst($bill->clearance_status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($bill->items->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Quantity</th>
                                                                <th>Unit Price</th>
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($bill->items as $item)
                                                                <tr>
                                                                    <td>{{ $item->item_name }}</td>
                                                                    <td>{{ $item->quantity }}</td>
                                                                    <td>{{ number_format($item->unit_price, 2) }} TZS</td>
                                                                    <td>{{ number_format($item->total, 2) }} TZS</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <th colspan="3">Subtotal:</th>
                                                                <th>{{ number_format($bill->subtotal, 2) }} TZS</th>
                                                            </tr>
                                                            <tr>
                                                                <th colspan="3">Discount:</th>
                                                                <th>{{ number_format($bill->discount, 2) }} TZS</th>
                                                            </tr>
                                                            <tr>
                                                                <th colspan="3">Tax:</th>
                                                                <th>{{ number_format($bill->tax, 2) }} TZS</th>
                                                            </tr>
                                                            <tr class="table-primary">
                                                                <th colspan="3">Total:</th>
                                                                <th>{{ number_format($bill->total, 2) }} TZS</th>
                                                            </tr>
                                                            <tr>
                                                                <th colspan="3">Paid:</th>
                                                                <th>{{ number_format($bill->paid, 2) }} TZS</th>
                                                            </tr>
                                                            <tr>
                                                                <th colspan="3">Balance:</th>
                                                                <th>{{ number_format($bill->balance, 2) }} TZS</th>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Triage Vitals -->
                @if($visit->triageVitals)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-pulse me-2"></i>Triage Vitals
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="50%">Temperature:</th>
                                        <td>{{ $visit->triageVitals->temperature ? $visit->triageVitals->temperature . ' °C' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Blood Pressure:</th>
                                        <td>{{ $visit->triageVitals->blood_pressure_formatted ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pulse Rate:</th>
                                        <td>{{ $visit->triageVitals->pulse_rate ? $visit->triageVitals->pulse_rate . ' bpm' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Respiratory Rate:</th>
                                        <td>{{ $visit->triageVitals->respiratory_rate ? $visit->triageVitals->respiratory_rate . ' /min' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Oxygen Saturation:</th>
                                        <td>{{ $visit->triageVitals->oxygen_saturation ? $visit->triageVitals->oxygen_saturation . ' %' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Weight:</th>
                                        <td>{{ $visit->triageVitals->weight ? $visit->triageVitals->weight . ' kg' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Height:</th>
                                        <td>{{ $visit->triageVitals->height ? $visit->triageVitals->height . ' cm' : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>BMI:</th>
                                        <td>{{ $visit->triageVitals->bmi ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Priority:</th>
                                        <td>
                                            @php
                                                $priorityColors = [
                                                    'low' => 'success',
                                                    'medium' => 'warning',
                                                    'high' => 'danger',
                                                    'critical' => 'dark'
                                                ];
                                                $priorityColor = $priorityColors[$visit->triageVitals->priority] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $priorityColor }}">
                                                {{ ucfirst($visit->triageVitals->priority) }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                @if($visit->triageVitals->triage_notes)
                                    <div class="mt-3">
                                        <strong>Notes:</strong>
                                        <p class="mb-0">{{ $visit->triageVitals->triage_notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Consultation -->
                @if($visit->consultation)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-user-md me-2"></i>Consultation
                                </h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Doctor:</th>
                                        <td>{{ $visit->consultation->doctor->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Date:</th>
                                        <td>{{ $visit->consultation->created_at->format('d M Y, H:i') }}</td>
                                    </tr>
                                </table>
                                @if($visit->consultation->chief_complaint)
                                    <div class="mb-3">
                                        <strong>Chief Complaint:</strong>
                                        <p class="mb-0">{{ $visit->consultation->chief_complaint }}</p>
                                    </div>
                                @endif
                                @if($visit->consultation->diagnosis)
                                    <div class="mb-3">
                                        <strong>Diagnosis:</strong>
                                        <p class="mb-0">{{ $visit->consultation->diagnosis }}</p>
                                    </div>
                                @endif
                                @if($visit->consultation->treatment_plan)
                                    <div class="mb-3">
                                        <strong>Treatment Plan:</strong>
                                        <p class="mb-0">{{ $visit->consultation->treatment_plan }}</p>
                                    </div>
                                @endif
                                @if($visit->consultation->prescription)
                                    <div class="mb-3">
                                        <strong>Prescription:</strong>
                                        <p class="mb-0">{{ $visit->consultation->prescription }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
