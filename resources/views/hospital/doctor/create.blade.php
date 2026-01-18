@extends('layouts.main')

@section('title', 'Doctor Consultation')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Doctor', 'url' => route('hospital.doctor.index'), 'icon' => 'bx bx-user-md'],
                ['label' => 'Consultation', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">DOCTOR CONSULTATION</h6>
            <hr />

            <div class="row">
                <!-- Patient Info Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bx bx-user me-2"></i>Patient Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Patient Name:</strong> {{ $visit->patient->full_name }}</p>
                                    <p><strong>MRN:</strong> {{ $visit->patient->mrn }}</p>
                                    <p><strong>Age:</strong> {{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}</p>
                                    <p><strong>Gender:</strong> {{ ucfirst($visit->patient->gender ?? 'N/A') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Visit #:</strong> {{ $visit->visit_number }}</p>
                                    <p><strong>Visit Type:</strong> {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}</p>
                                    <p><strong>Visit Date:</strong> {{ $visit->visit_date->format('d M Y, H:i') }}</p>
                                    @if($visit->triageVitals)
                                        <p><strong>Priority:</strong> 
                                            <span class="badge bg-{{ $visit->triageVitals->priority == 'critical' ? 'dark' : ($visit->triageVitals->priority == 'high' ? 'danger' : ($visit->triageVitals->priority == 'medium' ? 'warning' : 'success')) }}">
                                                {{ ucfirst($visit->triageVitals->priority) }}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($paidInvoices && $paidInvoices->count() > 0)
                <!-- Pre-Paid Bill History Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-receipt me-2"></i>Pre-Paid Bill History</h5>
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

                @if($visit->triageVitals)
                <!-- Triage Vitals Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-pulse me-2"></i>Triage Vitals</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Blood Pressure:</strong> {{ $visit->triageVitals->blood_pressure_formatted ?? 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Pulse Rate:</strong> {{ $visit->triageVitals->pulse_rate ? $visit->triageVitals->pulse_rate . ' bpm' : 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Temperature:</strong> {{ $visit->triageVitals->temperature ? $visit->triageVitals->temperature . ' °C' : 'N/A' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>SpO2:</strong> {{ $visit->triageVitals->oxygen_saturation ? $visit->triageVitals->oxygen_saturation . ' %' : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($visit->labResults && $visit->labResults->count() > 0)
                <!-- Lab Test Results Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="bx bx-test-tube me-2"></i>Lab Test Results</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Test Name</th>
                                            <th>Result Value</th>
                                            <th>Unit</th>
                                            <th>Reference Range</th>
                                            <th>Status</th>
                                            <th>Result Status</th>
                                            <th>Performed By</th>
                                            <th>Completed At</th>
                                            <th>Notes</th>
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
                                                <td>
                                                    {{ $result->result_value ?? 'N/A' }}
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
                                                <td>{{ $result->performedBy->name ?? 'N/A' }}</td>
                                                <td>
                                                    {{ $result->completed_at ? $result->completed_at->format('d M Y, H:i') : 'N/A' }}
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $result->notes ?? '-' }}</small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($visit->diagnosisExplanation)
                <!-- Diagnosis Explanation Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bx bx-file me-2"></i>Diagnosis Explanation</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <p><strong>Diagnosis:</strong></p>
                                    <p class="text-muted">{{ $visit->diagnosisExplanation->diagnosis ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <p><strong>Explanation:</strong></p>
                                    <p class="text-muted">{{ $visit->diagnosisExplanation->explanation ?? 'N/A' }}</p>
                                </div>
                                @if($visit->diagnosisExplanation->notes)
                                <div class="col-md-12 mb-3">
                                    <p><strong>Notes:</strong></p>
                                    <p class="text-muted">{{ $visit->diagnosisExplanation->notes }}</p>
                                </div>
                                @endif
                                <div class="col-md-12">
                                    <a href="{{ route('hospital.doctor.create-diagnosis', $visit->id) }}" class="btn btn-info">
                                        <i class="bx bx-edit me-1"></i>Edit Diagnosis
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($visit->pharmacyDispensations && $visit->pharmacyDispensations->where('status', 'dispensed')->count() > 0)
                <!-- Medication Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-capsule me-2"></i>Medications Dispensed</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Medication</th>
                                            <th>Prescribed Qty</th>
                                            <th>Dispensed Qty</th>
                                            <th>Dosage Instructions</th>
                                            <th>Status</th>
                                            <th>Dispensed By</th>
                                            <th>Dispensed At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($visit->pharmacyDispensations->where('status', 'dispensed') as $dispensation)
                                            @foreach($dispensation->items as $item)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $item->product->name ?? 'N/A' }}</strong><br>
                                                        <small class="text-muted">{{ $item->product->code ?? '' }}</small>
                                                    </td>
                                                    <td>{{ $item->quantity_prescribed }} {{ $item->product->unit_of_measure ?? '' }}</td>
                                                    <td>
                                                        <span class="fw-bold">{{ $item->quantity_dispensed }}</span>
                                                        {{ $item->product->unit_of_measure ?? '' }}
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $item->dosage_instructions ?? 'No instructions' }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $item->status == 'dispensed' ? 'success' : ($item->status == 'partial' ? 'warning' : 'secondary') }}">
                                                            {{ ucfirst($item->status) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $dispensation->dispensedBy->name ?? 'N/A' }}</td>
                                                    <td>
                                                        {{ $dispensation->dispensed_at ? $dispensation->dispensed_at->format('d M Y, H:i') : 'N/A' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Pre-Billing Services Card -->
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bx bx-money me-2"></i>Pre-Billing Services (Optional)</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="bx bx-info-circle me-2"></i>
                                Select services to create a pre-bill. Patient will need to clear the bill at Cashier before proceeding to departments.
                            </div>
                            
                            <form action="{{ route('hospital.doctor.store-pre-bill', $visit->id) }}" method="POST">
                                @csrf

                                <!-- Services Selection -->
                                <div class="mb-3" id="pre-billing-services">
                                    @if(isset($preBillingServices) && $preBillingServices->count() > 0)
                                        @foreach($preBillingServices as $service)
                                            <div class="row mb-2 align-items-center pre-billing-service-item">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input class="form-check-input service-checkbox" 
                                                               type="checkbox" 
                                                               name="services[{{ $loop->index }}][service_id]" 
                                                               value="{{ $service->id }}" 
                                                               id="service_{{ $service->id }}"
                                                               data-price="{{ $service->unit_price }}"
                                                               onchange="calculatePreBillTotal()">
                                                        <label class="form-check-label" for="service_{{ $service->id }}">
                                                            <strong>{{ $service->name }}</strong>
                                                            @if($service->code)
                                                                <br><small class="text-muted">{{ $service->code }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number" 
                                                           class="form-control form-control-sm quantity-input" 
                                                           name="services[{{ $loop->index }}][quantity]" 
                                                           value="1" 
                                                           min="1" 
                                                           data-price="{{ $service->unit_price }}"
                                                           onchange="calculatePreBillTotal()"
                                                           style="display: none;">
                                                </div>
                                                <div class="col-md-2 text-end">
                                                    <span class="fw-bold service-price">TZS {{ number_format($service->unit_price, 2) }}</span>
                                                </div>
                                                <div class="col-md-2 text-end">
                                                    <span class="fw-bold text-primary service-total">TZS 0.00</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="bx bx-info-circle me-2"></i>
                                            No pre-billing services available. Please add services in the inventory management (item_type = 'service').
                                        </div>
                                    @endif
                                </div>

                                <!-- Total Summary -->
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Total Cost</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12 text-end">
                                                <h4 class="mb-0">
                                                    <strong>Total:</strong>
                                                    <span class="text-primary" id="pre-bill-total">TZS 0.00</span>
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bx bx-save me-1"></i>Create Pre-Bill & Send to Cashier
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Create Lab Test Bill Card -->
                                <div class="col-md-6 mb-3">
                                    <div class="card border-primary h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-test-tube font-50 text-primary mb-3"></i>
                                            <h5 class="card-title">Create Lab Test Bill</h5>
                                            <p class="card-text text-muted">Create a bill for lab tests and send patient to cashier for payment</p>
                                            <a href="{{ url('/hospital/doctor/visits/' . $visit->id . '/create-lab-bill') }}" class="btn btn-primary">
                                                <i class="bx bx-plus me-1"></i>Create Lab Test Bill
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Diagnosis Explanation Card -->
                                <div class="col-md-3 mb-3">
                                    <div class="card border-info h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-file font-50 text-info mb-3"></i>
                                            <h5 class="card-title">Diagnosis</h5>
                                            <p class="card-text text-muted">Write diagnosis explanation</p>
                                            <a href="{{ route('hospital.doctor.create-diagnosis', $visit->id) }}" class="btn btn-info">
                                                <i class="bx bx-plus me-1"></i>{{ $visit->diagnosisExplanation ? 'Edit' : 'Create' }}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pharmacy Card -->
                                <div class="col-md-3 mb-3">
                                    <div class="card border-success h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-capsule font-50 text-success mb-3"></i>
                                            <h5 class="card-title">Pharmacy</h5>
                                            <p class="card-text text-muted">Create pharmacy bill</p>
                                            <a href="{{ route('hospital.doctor.create-pharmacy-bill', $visit->id) }}" class="btn btn-success">
                                                <i class="bx bx-plus me-1"></i>Create Bill
                                            </a>
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

    <script>
        function calculatePreBillTotal() {
            let total = 0;

            document.querySelectorAll('.service-checkbox:checked').forEach(checkbox => {
                const row = checkbox.closest('.pre-billing-service-item');
                const quantityInput = row.querySelector('.quantity-input');
                const price = parseFloat(checkbox.getAttribute('data-price')) || 0;
                const quantity = quantityInput ? parseFloat(quantityInput.value) || 1 : 1;
                const serviceTotal = price * quantity;
                
                total += serviceTotal;
                
                // Update service total
                const totalSpan = row.querySelector('.service-total');
                if (totalSpan) {
                    totalSpan.textContent = 'TZS ' + serviceTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }
            });

            document.getElementById('pre-bill-total').textContent = 'TZS ' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Show quantity input when checkbox is checked
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.service-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const row = this.closest('.pre-billing-service-item');
                    const quantityInput = row.querySelector('.quantity-input');
                    if (quantityInput) {
                        quantityInput.style.display = this.checked ? 'block' : 'none';
                        if (!this.checked) {
                            quantityInput.value = 1;
                        }
                        calculatePreBillTotal();
                    }
                });
            });
        });
    </script>
@endsection
