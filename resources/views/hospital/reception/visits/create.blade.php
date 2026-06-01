@extends('layouts.main')

@section('title', 'Create Visit')

@push('styles')
<style>
    .form-section {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-section-header {
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
    .service-item {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 0.5rem;
    }
    .department-checkbox {
        margin-bottom: 0.5rem;
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reception', 'url' => route('hospital.reception.index'), 'icon' => 'bx bx-user-plus'],
                ['label' => 'Create Visit', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">CREATE NEW VISIT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Create Visit for {{ $patient->full_name }}</h5>
                            </div>
                            <hr />

                            <!-- Patient Info Card -->
                            <div class="alert alert-info mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Patient:</strong> {{ $patient->full_name }}<br>
                                        <strong>MRN:</strong> {{ $patient->mrn }}<br>
                                        <strong>Age:</strong> {{ $patient->age ? $patient->age . ' years' : 'N/A' }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Phone:</strong> {{ $patient->phone ?? 'N/A' }}<br>
                                        <strong>Insurance:</strong> {{ $patient->insurance_type_name }}<br>
                                        <a href="{{ route('hospital.reception.patients.show', $patient->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="bx bx-show me-1"></i>View Patient Details
                                        </a>
                                    </div>
                                </div>
                            </div>

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('hospital.reception.visits.store', $patient->id) }}" method="POST" id="visitForm">
                                @csrf

                                <!-- Visit Information -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-info-circle me-2"></i>Visit Information
                                        </h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="visit_date" class="form-label fw-bold">Visiting Date <span class="text-danger">*</span></label>
                                                <input type="datetime-local" class="form-control @error('visit_date') is-invalid @enderror"
                                                       id="visit_date" name="visit_date" required
                                                       value="{{ old('visit_date', now()->format('Y-m-d\TH:i')) }}">
                                                <div class="form-text">Choose date and time for this visit (supports backdating).</div>
                                                @error('visit_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="visit_type" class="form-label fw-bold">Visit Type <span class="text-danger">*</span></label>
                                                <select class="form-select @error('visit_type') is-invalid @enderror" id="visit_type" name="visit_type" required>
                                                    <option value="">Select Visit Type</option>
                                                    <option value="new" {{ old('visit_type') == 'new' ? 'selected' : '' }}>New</option>
                                                    <option value="follow_up" {{ old('visit_type') == 'follow_up' ? 'selected' : '' }}>Follow Up</option>
                                                    <option value="emergency" {{ old('visit_type') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                                </select>
                                                @error('visit_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="chief_complaint" class="form-label fw-bold">Chief Complaint</label>
                                                <textarea class="form-control @error('chief_complaint') is-invalid @enderror"
                                                          id="chief_complaint" name="chief_complaint" rows="2" 
                                                          placeholder="Brief description of patient's main complaint">{{ old('chief_complaint') }}</textarea>
                                                @error('chief_complaint')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Department Routing -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-building me-2"></i>Department Routing
                                        </h6>
                                    </div>
                                    <div class="alert alert-warning">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Note:</strong> Patient must start at Triage unless going directly to Pharmacy. Select departments in order of visit.
                                    </div>
                                    <div class="row">
                                        @foreach($departments as $department)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check department-checkbox">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="departments[]" 
                                                           value="{{ $department->id }}" 
                                                           id="dept_{{ $department->id }}"
                                                           @if($department->type == 'triage') checked @endif
                                                           @if(old('departments') && in_array($department->id, old('departments'))) checked @endif>
                                                    <label class="form-check-label" for="dept_{{ $department->id }}">
                                                        <strong>{{ $department->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $department->type)) }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('departments')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Pre-Billing Services -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-money me-2"></i>Pre-Billing Services (Optional)
                                        </h6>
                                    </div>
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        Select services to create a pre-bill. Patient will need to clear the bill at Cashier before proceeding to departments.
                                        You can edit the <strong>unit price</strong> if the patient pays less than the amount configured on the service item.
                                    </div>
                                    <div class="row text-muted small fw-bold mb-1 d-none d-md-flex">
                                        <div class="col-md-5">Service</div>
                                        <div class="col-md-2">Qty</div>
                                        <div class="col-md-2">Unit price (TZS)</div>
                                        <div class="col-md-3">Line total</div>
                                    </div>
                                    <div id="servicesContainer">
                                        <div class="service-item" data-index="0">
                                            <div class="row align-items-center g-2">
                                                <div class="col-md-5">
                                                    <select class="form-select service-select" name="services[0][service_id]" onchange="updateServicePrice(this)">
                                                        <option value="">Select Service</option>
                                                        @foreach($services as $service)
                                                            <option value="{{ $service->id }}"
                                                                    data-price="{{ $service->unit_price }}"
                                                                    data-name="{{ $service->name }}"
                                                                    {{ old('services.0.service_id') == $service->id ? 'selected' : '' }}>
                                                                {{ $service->name }} ({{ number_format($service->unit_price, 2) }} TZS)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number" class="form-control quantity-input"
                                                           name="services[0][quantity]"
                                                           value="{{ old('services.0.quantity', 1) }}" min="1"
                                                           oninput="calculateServiceTotalFromRow(this.closest('.service-item'))">
                                                </div>
                                                <div class="col-md-2">
                                                    <input type="number" class="form-control unit-price-input"
                                                           name="services[0][unit_price]"
                                                           value="{{ old('services.0.unit_price', '') }}"
                                                           min="0" step="0.01" placeholder="0.00"
                                                           oninput="calculateServiceTotalFromRow(this.closest('.service-item'))">
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control total-display"
                                                               readonly value="0.00">
                                                        <button type="button" class="btn btn-danger btn-remove-service" onclick="removeServiceRow(this)" style="display:none;">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addService()">
                                        <i class="bx bx-plus me-1"></i>Add Service
                                    </button>
                                    <div class="mt-3">
                                        <strong>Total: <span id="totalAmount">0.00</span> TZS</strong>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.reception.patients.show', $patient->id) }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Create Visit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@php
    $serviceOptionsForJs = $services->map(function ($service) {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'unit_price' => (float) $service->unit_price,
        ];
    })->values();
@endphp
<script>
    let serviceCount = 1;

    const serviceOptionsHtml = @json($serviceOptionsForJs);

    function buildServiceSelectOptions(selectedId = '') {
        let html = '<option value="">Select Service</option>';
        serviceOptionsHtml.forEach(s => {
            const selected = String(selectedId) === String(s.id) ? ' selected' : '';
            html += `<option value="${s.id}" data-price="${s.unit_price}" data-name="${s.name}"${selected}>${s.name} (${s.unit_price.toFixed(2)} TZS)</option>`;
        });
        return html;
    }

    function addService() {
        const container = document.getElementById('servicesContainer');
        const newService = document.createElement('div');
        newService.className = 'service-item';
        newService.dataset.index = String(serviceCount);
        newService.innerHTML = `
            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <select class="form-select service-select" name="services[${serviceCount}][service_id]" onchange="updateServicePrice(this)">
                        ${buildServiceSelectOptions()}
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control quantity-input"
                           name="services[${serviceCount}][quantity]"
                           value="1" min="1"
                           oninput="calculateServiceTotalFromRow(this.closest('.service-item'))">
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control unit-price-input"
                           name="services[${serviceCount}][unit_price]"
                           value="" min="0" step="0.01" placeholder="0.00"
                           oninput="calculateServiceTotalFromRow(this.closest('.service-item'))">
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <input type="text" class="form-control total-display" readonly value="0.00">
                        <button type="button" class="btn btn-danger btn-remove-service" onclick="removeServiceRow(this)">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(newService);
        serviceCount++;
        updateRemoveButtons();
    }

    function removeServiceRow(btn) {
        const row = btn.closest('.service-item');
        if (row) {
            row.remove();
            calculateTotal();
            updateRemoveButtons();
        }
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('#servicesContainer .service-item');
        rows.forEach((row, i) => {
            const btn = row.querySelector('.btn-remove-service');
            if (btn) {
                btn.style.display = rows.length > 1 ? '' : 'none';
            }
        });
    }

    function updateServicePrice(select) {
        const row = select.closest('.service-item');
        const option = select.options[select.selectedIndex];
        const unitPriceInput = row.querySelector('.unit-price-input');
        if (option && option.value) {
            const catalogPrice = parseFloat(option.getAttribute('data-price')) || 0;
            unitPriceInput.value = catalogPrice.toFixed(2);
        } else {
            unitPriceInput.value = '';
        }
        calculateServiceTotalFromRow(row);
    }

    function calculateServiceTotalFromRow(row) {
        if (!row) return;
        const select = row.querySelector('.service-select');
        const quantityInput = row.querySelector('.quantity-input');
        const unitPriceInput = row.querySelector('.unit-price-input');
        const totalDisplay = row.querySelector('.total-display');

        if (!select.value) {
            totalDisplay.value = '0.00';
            calculateTotal();
            return;
        }

        const quantity = parseFloat(quantityInput.value) || 1;
        let unitPrice = parseFloat(unitPriceInput.value);
        if (isNaN(unitPrice) || unitPriceInput.value === '') {
            const option = select.options[select.selectedIndex];
            unitPrice = parseFloat(option.getAttribute('data-price')) || 0;
            unitPriceInput.value = unitPrice.toFixed(2);
        }

        const total = Math.max(0, unitPrice) * quantity;
        totalDisplay.value = total.toFixed(2);
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.total-display').forEach(display => {
            total += parseFloat(display.value) || 0;
        });
        document.getElementById('totalAmount').textContent = total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#servicesContainer .service-item').forEach(row => {
            const select = row.querySelector('.service-select');
            const unitPriceInput = row.querySelector('.unit-price-input');
            if (select.value && !unitPriceInput.value) {
                updateServicePrice(select);
            } else {
                calculateServiceTotalFromRow(row);
            }
        });
        updateRemoveButtons();
    });

    document.getElementById('visitForm').addEventListener('submit', function(e) {
        const departments = document.querySelectorAll('input[name="departments[]"]:checked');
        if (departments.length === 0) {
            e.preventDefault();
            alert('Please select at least one department.');
            return false;
        }
    });
</script>
@endpush
