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
                                                <th>Start Time</th>
                                                <th>End Time</th>
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
                                                    <td>
                                                        <span class="text-warning">
                                                            <i class="bx bx-time"></i> {{ $visitDept->waiting_time_formatted ?? '00:00:00' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-primary">
                                                            <i class="bx bx-time-five"></i> {{ $visitDept->service_time_formatted ?? '00:00:00' }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($visitDept->service_started_at)
                                                            <small>{{ $visitDept->service_started_at->format('d M Y, H:i') }}</small>
                                                        @elseif($visitDept->waiting_started_at)
                                                            <small class="text-muted">{{ $visitDept->waiting_started_at->format('d M Y, H:i') }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($visitDept->service_ended_at)
                                                            <small>{{ $visitDept->service_ended_at->format('d M Y, H:i') }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
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

                <!-- Pre-Paid Bill History -->
                @if($paidInvoices && $paidInvoices->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-receipt me-2"></i>Pre-Paid Bill History
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Items</th>
                                                <th>Total Amount</th>
                                                <th>Paid Amount</th>
                                                <th>Payment Date</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($paidInvoices as $invoice)
                                                @php
                                                    $billType = 'General';
                                                    if (str_contains($invoice->notes ?? '', 'Lab test')) {
                                                        $billType = 'Lab Test';
                                                    } elseif (str_contains($invoice->notes ?? '', 'Pharmacy')) {
                                                        $billType = 'Pharmacy';
                                                    } elseif (str_contains($invoice->notes ?? '', 'Consultation')) {
                                                        $billType = 'Consultation';
                                                    } elseif (str_contains($invoice->notes ?? '', 'Ultrasound')) {
                                                        $billType = 'Ultrasound';
                                                    }
                                                    $paymentDate = $invoice->receipts->first()->date ?? $invoice->receipts->first()->created_at ?? $invoice->updated_at;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <strong>{{ $invoice->invoice_number }}</strong>
                                                    </td>
                                                    <td>{{ $invoice->created_at->format('d M Y, H:i') }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ $billType }}</span>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            @foreach($invoice->items->take(2) as $item)
                                                                {{ $item->item_name }}{{ !$loop->last ? ', ' : '' }}
                                                            @endforeach
                                                            @if($invoice->items->count() > 2)
                                                                <span class="text-muted">+{{ $invoice->items->count() - 2 }} more</span>
                                                            @endif
                                                        </small>
                                                    </td>
                                                    <td>{{ number_format($invoice->total_amount ?? $invoice->total ?? 0, 2) }} {{ $invoice->currency ?? 'TZS' }}</td>
                                                    <td>
                                                        <strong class="text-success">{{ number_format($invoice->paid_amount ?? $invoice->total_amount ?? $invoice->total ?? 0, 2) }} {{ $invoice->currency ?? 'TZS' }}</strong>
                                                    </td>
                                                    <td>{{ $paymentDate->format('d M Y, H:i') }}</td>
                                                    <td>
                                                        <a href="{{ route('sales.invoices.show', $invoice->encoded_id) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           target="_blank"
                                                           title="View Invoice">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr class="table-info">
                                                <th colspan="4" class="text-end">Total Paid:</th>
                                                <th colspan="4">
                                                    <strong>{{ number_format($paidInvoices->sum('paid_amount') ?? $paidInvoices->sum('total_amount') ?? $paidInvoices->sum('total') ?? 0, 2) }} TZS</strong>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

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
                                    <i class="bx bx-user-md me-2"></i>Doctor Consultation
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

                <!-- Diagnosis Explanation -->
                @if($visit->diagnosisExplanation)
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-file me-2"></i>Diagnosis Explanation
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>Diagnosis:</strong>
                                    <p class="mb-0">{{ $visit->diagnosisExplanation->diagnosis ?? 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <strong>Explanation:</strong>
                                    <p class="mb-0">{{ $visit->diagnosisExplanation->explanation ?? 'N/A' }}</p>
                                </div>
                                @if($visit->diagnosisExplanation->notes)
                                    <div class="mb-3">
                                        <strong>Notes:</strong>
                                        <p class="mb-0">{{ $visit->diagnosisExplanation->notes }}</p>
                                    </div>
                                @endif
                                <small class="text-muted">
                                    Created: {{ $visit->diagnosisExplanation->created_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Create Lab Test Bill Card -->
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <div class="card border-primary h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-test-tube font-50 text-primary mb-3"></i>
                                            <h5 class="card-title">Lab Test Bill</h5>
                                            <p class="card-text text-muted">Create a bill for lab tests and send patient to cashier for payment</p>
                                            <a href="{{ route('hospital.doctor.create-lab-bill', $visit->id) }}" class="btn btn-primary">
                                                <i class="bx bx-plus me-1"></i>Create Bill
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pharmacy Card -->
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <div class="card border-success h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-capsule font-50 text-success mb-3"></i>
                                            <h5 class="card-title">Pharmacy</h5>
                                            <p class="card-text text-muted">Create pharmacy bill and send patient to cashier for payment</p>
                                            <a href="{{ route('hospital.doctor.create-pharmacy-bill', $visit->id) }}" class="btn btn-success">
                                                <i class="bx bx-plus me-1"></i>Create Bill
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Vaccination Card -->
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <div class="card border-warning h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-shield font-50 text-warning mb-3"></i>
                                            <h5 class="card-title">Vaccination</h5>
                                            <p class="card-text text-muted">Create vaccination bill and send patient to cashier for payment</p>
                                            <a href="{{ route('hospital.doctor.create-vaccination-bill', $visit->id) }}" class="btn btn-warning">
                                                <i class="bx bx-plus me-1"></i>Create Bill
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Injection Card -->
                                <div class="col-md-3 col-lg-3 mb-3">
                                    <div class="card border-danger h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-injection font-50 text-danger mb-3"></i>
                                            <h5 class="card-title">Injection</h5>
                                            <p class="card-text text-muted">Create injection bill and send patient to cashier for payment</p>
                                            <a href="{{ route('hospital.doctor.create-injection-bill', $visit->id) }}" class="btn btn-danger">
                                                <i class="bx bx-plus me-1"></i>Create Bill
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medication Dispensed -->
                @if($visit->pharmacyDispensations && $visit->pharmacyDispensations->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-capsule me-2"></i>Medication Dispensed
                                    <span class="badge bg-light text-dark ms-2">{{ $visit->pharmacyDispensations->count() }}</span>
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach($visit->pharmacyDispensations as $dispensation)
                                    <div class="card border mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1">
                                                        Dispensation #: <strong>{{ $dispensation->dispensation_number }}</strong>
                                                        <span class="badge bg-{{ $dispensation->status === 'dispensed' ? 'success' : 'warning' }} ms-2">
                                                            {{ ucfirst($dispensation->status) }}
                                                        </span>
                                                    </h6>
                                                    <small class="text-muted">
                                                        Dispensed: {{ $dispensation->dispensed_at ? $dispensation->dispensed_at->format('d M Y, H:i') : 'N/A' }}
                                                        @if($dispensation->dispensedBy)
                                                            by {{ $dispensation->dispensedBy->name }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>

                                            @if($dispensation->items->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Medication</th>
                                                                <th>Quantity Prescribed</th>
                                                                <th>Quantity Dispensed</th>
                                                                <th>Dosage/Description</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($dispensation->items as $item)
                                                                <tr>
                                                                    <td>
                                                                        <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                                                    </td>
                                                                    <td>{{ $item->quantity_prescribed ?? 0 }}</td>
                                                                    <td>
                                                                        <strong class="text-success">{{ $item->quantity_dispensed ?? 0 }}</strong>
                                                                    </td>
                                                                    <td>
                                                                        <small class="text-muted">{{ $item->description ?? 'N/A' }}</small>
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge bg-{{ $item->status === 'dispensed' ? 'success' : 'warning' }}">
                                                                            {{ ucfirst($item->status) }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
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

                <!-- Lab Test Bills and Results -->
                @php
                    $labInvoices = $paidInvoices ? $paidInvoices->filter(function($inv) {
                        return str_contains($inv->notes ?? '', 'Lab test');
                    }) : collect();
                @endphp
                @if($labInvoices->count() > 0 || $visit->labResults->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-test-tube me-2"></i>Lab Test Bills and Results
                                    @if($visit->labResults->count() > 0)
                                        <span class="badge bg-light text-dark ms-2">{{ $visit->labResults->count() }} result(s)</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($labInvoices->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-3"><i class="bx bx-receipt me-1"></i>Lab Test Bills</h6>
                                        @foreach($labInvoices as $invoice)
                                            <div class="card border mb-2">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $invoice->invoice_number }}</strong>
                                                            <small class="text-muted ms-2">{{ $invoice->created_at->format('d M Y, H:i') }}</small>
                                                        </div>
                                                        <div>
                                                            <span class="badge bg-success">{{ number_format($invoice->paid_amount ?? $invoice->total_amount ?? 0, 2) }} TZS</span>
                                                            <a href="{{ route('sales.invoices.show', $invoice->encoded_id) }}" 
                                                               class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($visit->labResults->count() > 0)
                                    <div>
                                        <h6 class="mb-3"><i class="bx bx-test-tube me-1"></i>Lab Test Results</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Test Name</th>
                                                        <th>Service</th>
                                                        <th>Result Value</th>
                                                        <th>Unit</th>
                                                        <th>Reference Range</th>
                                                        <th>Status</th>
                                                        <th>Result Status</th>
                                                        <th>Completed At</th>
                                                        <th>Performed By</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($visit->labResults as $result)
                                                        @php
                                                            $statusColors = [
                                                                'normal' => 'success',
                                                                'abnormal' => 'warning',
                                                                'critical' => 'danger'
                                                            ];
                                                            $statusColor = $statusColors[$result->status] ?? 'secondary';
                                                            $resultStatusColors = [
                                                                'pending' => 'warning',
                                                                'ready' => 'success',
                                                                'printed' => 'info',
                                                                'delivered' => 'primary'
                                                            ];
                                                            $resultStatusColor = $resultStatusColors[$result->result_status] ?? 'secondary';
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $result->test_name }}</strong></td>
                                                            <td>{{ $result->service->name ?? 'N/A' }}</td>
                                                            <td>
                                                                @if($result->result_value)
                                                                    {{ $result->result_value }}
                                                                @else
                                                                    <span class="text-muted">Pending</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $result->unit ?? 'N/A' }}</td>
                                                            <td>{{ $result->reference_range ?? 'N/A' }}</td>
                                                            <td>
                                                                @if($result->status)
                                                                    <span class="badge bg-{{ $statusColor }}">
                                                                        {{ ucfirst($result->status) }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $resultStatusColor }}">
                                                                    {{ ucfirst($result->result_status) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                {{ $result->completed_at ? $result->completed_at->format('d M Y, H:i') : 'N/A' }}
                                                            </td>
                                                            <td>{{ $result->performedBy->name ?? 'N/A' }}</td>
                                                            <td>
                                                                @if($result->result_status === 'ready' || $result->result_status === 'printed')
                                                                    <div class="btn-group btn-group-sm">
                                                                        <a href="{{ route('hospital.reception.visits.print-results', ['visit' => $visit->id, 'type' => 'lab', 'result_id' => $result->id, 'format' => 'a4']) }}" 
                                                                           class="btn btn-outline-primary" target="_blank" title="Print A4">
                                                                            <i class="bx bx-printer"></i> A4
                                                                        </a>
                                                                        <a href="{{ route('hospital.reception.visits.print-results', ['visit' => $visit->id, 'type' => 'lab', 'result_id' => $result->id, 'format' => 'thermal']) }}" 
                                                                           class="btn btn-outline-info" target="_blank" title="Print Thermal Receipt">
                                                                            <i class="bx bx-receipt"></i> Thermal
                                                                        </a>
                                                                    </div>
                                                                @else
                                                                    <span class="text-muted">Not ready</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ultrasound Test Bills and Results -->
                @php
                    $ultrasoundInvoices = $paidInvoices ? $paidInvoices->filter(function($inv) {
                        return str_contains($inv->notes ?? '', 'Ultrasound');
                    }) : collect();
                @endphp
                @if($ultrasoundInvoices->count() > 0 || $visit->ultrasoundResults->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-scan me-2"></i>Ultrasound Test Bills and Results
                                    @if($visit->ultrasoundResults->count() > 0)
                                        <span class="badge bg-light text-dark ms-2">{{ $visit->ultrasoundResults->count() }} result(s)</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($ultrasoundInvoices->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-3"><i class="bx bx-receipt me-1"></i>Ultrasound Test Bills</h6>
                                        @foreach($ultrasoundInvoices as $invoice)
                                            <div class="card border mb-2">
                                                <div class="card-body p-3">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>{{ $invoice->invoice_number }}</strong>
                                                            <small class="text-muted ms-2">{{ $invoice->created_at->format('d M Y, H:i') }}</small>
                                                        </div>
                                                        <div>
                                                            <span class="badge bg-success">{{ number_format($invoice->paid_amount ?? $invoice->total_amount ?? 0, 2) }} TZS</span>
                                                            <a href="{{ route('sales.invoices.show', $invoice->encoded_id) }}" 
                                                               class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                                                                <i class="bx bx-show"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($visit->ultrasoundResults->count() > 0)
                                    <div>
                                        <h6 class="mb-3"><i class="bx bx-scan me-1"></i>Ultrasound Results</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Result #</th>
                                                        <th>Examination Type</th>
                                                        <th>Service</th>
                                                        <th>Findings</th>
                                                        <th>Impression</th>
                                                        <th>Status</th>
                                                        <th>Completed At</th>
                                                        <th>Performed By</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($visit->ultrasoundResults as $result)
                                                        @php
                                                            $images = $result->images ? (is_string($result->images) ? json_decode($result->images, true) : $result->images) : [];
                                                            $statusColors = [
                                                                'pending' => 'warning',
                                                                'ready' => 'success',
                                                                'printed' => 'info',
                                                                'delivered' => 'primary'
                                                            ];
                                                            $color = $statusColors[$result->result_status] ?? 'secondary';
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $result->result_number }}</strong></td>
                                                            <td>{{ $result->examination_type }}</td>
                                                            <td>{{ $result->service->name ?? 'N/A' }}</td>
                                                            <td>
                                                                @if($result->findings)
                                                                    {{ strlen($result->findings) > 50 ? substr($result->findings, 0, 50) . '...' : $result->findings }}
                                                                @else
                                                                    <span class="text-muted">Pending</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($result->impression)
                                                                    {{ strlen($result->impression) > 50 ? substr($result->impression, 0, 50) . '...' : $result->impression }}
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $color }}">
                                                                    {{ ucfirst($result->result_status) }}
                                                                </span>
                                                                @if(count($images) > 0)
                                                                    <span class="badge bg-info ms-1">{{ count($images) }} image(s)</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $result->completed_at ? $result->completed_at->format('d M Y, H:i') : 'N/A' }}
                                                            </td>
                                                            <td>{{ $result->performedBy->name ?? 'N/A' }}</td>
                                                            <td>
                                                                @if($result->result_status === 'ready' || $result->result_status === 'printed')
                                                                    <div class="btn-group btn-group-sm">
                                                                        <a href="{{ route('hospital.ultrasound.show', $result->id) }}" 
                                                                           class="btn btn-outline-secondary" target="_blank" title="View Full Result">
                                                                            <i class="bx bx-show"></i>
                                                                        </a>
                                                                        <a href="{{ route('hospital.reception.visits.print-results', ['visit' => $visit->id, 'type' => 'ultrasound', 'result_id' => $result->id, 'format' => 'a4']) }}" 
                                                                           class="btn btn-outline-primary" target="_blank" title="Print A4">
                                                                            <i class="bx bx-printer"></i> A4
                                                                        </a>
                                                                        <a href="{{ route('hospital.reception.visits.print-results', ['visit' => $visit->id, 'type' => 'ultrasound', 'result_id' => $result->id, 'format' => 'thermal']) }}" 
                                                                           class="btn btn-outline-info" target="_blank" title="Print Thermal Receipt">
                                                                            <i class="bx bx-receipt"></i> Thermal
                                                                        </a>
                                                                    </div>
                                                                @else
                                                                    <span class="text-muted">Not ready</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Dental Bills and Records -->
                @php
                    $dentalInvoices = $paidInvoices ? $paidInvoices->filter(function($inv) {
                        return str_contains($inv->notes ?? '', 'Dental');
                    }) : collect();
                @endphp
                @if($dentalInvoices->count() > 0 || $visit->dentalRecords->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-smile me-2"></i>Dental Bills and Records
                                    @if($visit->dentalRecords->count() > 0)
                                        <span class="badge bg-light text-dark ms-2">{{ $visit->dentalRecords->count() }} record(s)</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($dentalInvoices->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-3"><i class="bx bx-receipt me-1"></i>Dental Bills</h6>
                                        @foreach($dentalInvoices as $invoice)
                                            <div class="card mb-2">
                                                <div class="card-body p-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-3">
                                                            <strong>Invoice #:</strong> {{ $invoice->invoice_number }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Date:</strong> {{ $invoice->created_at->format('d M Y, H:i') }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Total:</strong> {{ number_format($invoice->total_amount, 2) }} TZS
                                                        </div>
                                                        <div class="col-md-3 text-end">
                                                            <a href="{{ route('sales.invoices.show', $invoice->encoded_id) }}" class="btn btn-sm btn-outline-info" target="_blank">
                                                                <i class="bx bx-show"></i> View Invoice
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($visit->dentalRecords->count() > 0)
                                    <div>
                                        <h6 class="mb-3"><i class="bx bx-smile me-1"></i>Dental Records</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Record #</th>
                                                        <th>Service</th>
                                                        <th>Procedure Type</th>
                                                        <th>Status</th>
                                                        <th>Findings</th>
                                                        <th>Treatment Performed</th>
                                                        <th>Completed At</th>
                                                        <th>Performed By</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($visit->dentalRecords as $record)
                                                        @php
                                                            $statusColors = [
                                                                'completed' => 'success',
                                                                'follow_up_required' => 'warning',
                                                                'pending' => 'info'
                                                            ];
                                                            $statusColor = $statusColors[$record->status] ?? 'secondary';
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $record->record_number }}</strong></td>
                                                            <td>{{ $record->service->name ?? 'N/A' }}</td>
                                                            <td>{{ $record->procedure_type ?? 'N/A' }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $statusColor }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($record->findings)
                                                                    {{ strlen($record->findings) > 50 ? substr($record->findings, 0, 50) . '...' : $record->findings }}
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($record->treatment_performed)
                                                                    {{ strlen($record->treatment_performed) > 50 ? substr($record->treatment_performed, 0, 50) . '...' : $record->treatment_performed }}
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $record->completed_at ? $record->completed_at->format('d M Y, H:i') : 'N/A' }}
                                                            </td>
                                                            <td>{{ $record->performedBy->name ?? 'N/A' }}</td>
                                                            <td>
                                                                <a href="{{ route('hospital.dental.show', $record->id) }}" 
                                                                   class="btn btn-outline-info btn-sm" target="_blank" title="View Full Record">
                                                                    <i class="bx bx-show"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Vaccination Bills and Records -->
                @php
                    $vaccinationInvoices = $paidInvoices ? $paidInvoices->filter(function($inv) {
                        return str_contains($inv->notes ?? '', 'Vaccination');
                    }) : collect();
                @endphp
                @if($vaccinationInvoices->count() > 0 || $visit->vaccinationRecords->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-shield me-2"></i>Vaccination Bills and Records
                                    @if($visit->vaccinationRecords->count() > 0)
                                        <span class="badge bg-light text-dark ms-2">{{ $visit->vaccinationRecords->count() }} record(s)</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($vaccinationInvoices->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-3"><i class="bx bx-receipt me-1"></i>Vaccination Bills</h6>
                                        @foreach($vaccinationInvoices as $invoice)
                                            <div class="card mb-2">
                                                <div class="card-body p-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-3">
                                                            <strong>Invoice #:</strong> {{ $invoice->invoice_number }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Date:</strong> {{ $invoice->created_at->format('d M Y, H:i') }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Total:</strong> {{ number_format($invoice->total_amount, 2) }} TZS
                                                        </div>
                                                        <div class="col-md-3 text-end">
                                                            <a href="{{ route('sales.invoices.show', $invoice->encoded_id) }}" class="btn btn-sm btn-outline-warning" target="_blank">
                                                                <i class="bx bx-show"></i> View Invoice
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($visit->vaccinationRecords->count() > 0)
                                    <div>
                                        <h6 class="mb-3"><i class="bx bx-shield me-1"></i>Vaccination Records</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Record #</th>
                                                        <th>Item</th>
                                                        <th>Vaccine Type</th>
                                                        <th>Status</th>
                                                        <th>Vaccine Name</th>
                                                        <th>Dosage</th>
                                                        <th>Completed At</th>
                                                        <th>Performed By</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($visit->vaccinationRecords as $record)
                                                        @php
                                                            $statusColors = [
                                                                'completed' => 'success',
                                                                'follow_up_required' => 'warning',
                                                                'pending' => 'info'
                                                            ];
                                                            $statusColor = $statusColors[$record->status] ?? 'secondary';
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $record->record_number }}</strong></td>
                                                            <td>{{ $record->item->name ?? 'N/A' }}</td>
                                                            <td>{{ $record->vaccine_type ?? 'N/A' }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $statusColor }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $record->vaccine_name ?? 'N/A' }}</td>
                                                            <td>{{ $record->dosage ?? 'N/A' }}</td>
                                                            <td>
                                                                {{ $record->completed_at ? $record->completed_at->format('d M Y, H:i') : 'N/A' }}
                                                            </td>
                                                            <td>{{ $record->performedBy->name ?? 'N/A' }}</td>
                                                            <td>
                                                                <a href="{{ route('hospital.vaccination.show', $record->id) }}" 
                                                                   class="btn btn-outline-warning btn-sm" target="_blank" title="View Full Record">
                                                                    <i class="bx bx-show"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Injection Bills and Records -->
                @php
                    $injectionInvoices = $paidInvoices ? $paidInvoices->filter(function($inv) {
                        return str_contains($inv->notes ?? '', 'Injection');
                    }) : collect();
                @endphp
                @if($injectionInvoices->count() > 0 || $visit->injectionRecords->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="bx bx-injection me-2"></i>Injection Bills and Records
                                    @if($visit->injectionRecords->count() > 0)
                                        <span class="badge bg-light text-dark ms-2">{{ $visit->injectionRecords->count() }} record(s)</span>
                                    @endif
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($injectionInvoices->count() > 0)
                                    <div class="mb-4">
                                        <h6 class="mb-3"><i class="bx bx-receipt me-1"></i>Injection Bills</h6>
                                        @foreach($injectionInvoices as $invoice)
                                            <div class="card mb-2">
                                                <div class="card-body p-3">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-3">
                                                            <strong>Invoice #:</strong> {{ $invoice->invoice_number }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Date:</strong> {{ $invoice->created_at->format('d M Y, H:i') }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <strong>Total:</strong> {{ number_format($invoice->total_amount, 2) }} TZS
                                                        </div>
                                                        <div class="col-md-3 text-end">
                                                            <a href="{{ route('sales.invoices.show', $invoice->encoded_id) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                                                <i class="bx bx-show"></i> View Invoice
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if($visit->injectionRecords->count() > 0)
                                    <div>
                                        <h6 class="mb-3"><i class="bx bx-injection me-1"></i>Injection Records</h6>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Record #</th>
                                                        <th>Item</th>
                                                        <th>Injection Type</th>
                                                        <th>Status</th>
                                                        <th>Medication Name</th>
                                                        <th>Dosage</th>
                                                        <th>Completed At</th>
                                                        <th>Performed By</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($visit->injectionRecords as $record)
                                                        @php
                                                            $statusColors = [
                                                                'completed' => 'success',
                                                                'follow_up_required' => 'warning',
                                                                'pending' => 'info'
                                                            ];
                                                            $statusColor = $statusColors[$record->status] ?? 'secondary';
                                                        @endphp
                                                        <tr>
                                                            <td><strong>{{ $record->record_number }}</strong></td>
                                                            <td>{{ $record->item->name ?? 'N/A' }}</td>
                                                            <td>{{ $record->injection_type ?? 'N/A' }}</td>
                                                            <td>
                                                                <span class="badge bg-{{ $statusColor }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $record->medication_name ?? 'N/A' }}</td>
                                                            <td>{{ $record->dosage ?? 'N/A' }}</td>
                                                            <td>
                                                                {{ $record->completed_at ? $record->completed_at->format('d M Y, H:i') : 'N/A' }}
                                                            </td>
                                                            <td>{{ $record->performedBy->name ?? 'N/A' }}</td>
                                                            <td>
                                                                <a href="{{ route('hospital.injection.show', $record->id) }}" 
                                                                   class="btn btn-outline-danger btn-sm" target="_blank" title="View Full Record">
                                                                    <i class="bx bx-show"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Reception Actions Card -->
                <div class="col-12 mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-cog me-2"></i>Reception Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                {{-- Send to Doctor - Commented out as no longer in use
                                @if($visit->labResults->where('result_status', 'ready')->count() > 0 || $visit->ultrasoundResults->where('result_status', 'ready')->count() > 0)
                                    <form action="{{ route('hospital.reception.visits.send-to-doctor', $visit->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning" onclick="return confirm('Send patient back to Doctor for review?')">
                                            <i class="bx bx-user-md me-1"></i>Send to Doctor
                                        </button>
                                    </form>
                                @endif
                                --}}
                                
                                {{-- Bill Actions - Commented out as no longer in use
                                @php
                                    $finalBill = $visit->bills->where('bill_type', 'final')->first();
                                    $preBill = $visit->bills->where('bill_type', 'pre_bill')->first();
                                @endphp
                                
                                @if($finalBill)
                                    <a href="{{ route('hospital.cashier.bills.show', $finalBill->id) }}" class="btn btn-success">
                                        <i class="bx bx-money me-1"></i>View Final Bill
                                    </a>
                                @else
                                    <a href="{{ route('hospital.reception.visits.create-bill', $visit->id) }}" class="btn btn-success" onclick="return confirm('Create a new final bill for this visit?')">
                                        <i class="bx bx-plus-circle me-1"></i>Create Final Bill
                                    </a>
                                @endif
                                
                                @if($preBill)
                                    <a href="{{ route('hospital.cashier.bills.show', $preBill->id) }}" class="btn btn-info">
                                        <i class="bx bx-receipt me-1"></i>View Pre-Bill
                                    </a>
                                @endif
                                --}}
                                
                                <a href="{{ route('hospital.reception.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i>Back to Reception
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
