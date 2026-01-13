@extends('layouts.main')

@section('title', 'Create Dispensation')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Pharmacy', 'url' => route('hospital.pharmacy.index'), 'icon' => 'bx bx-capsule'],
                ['label' => 'Create Dispensation', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">CREATE DISPENSATION</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-capsule me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Dispensation for {{ $visit->patient->full_name }}</h5>
                            </div>
                            <hr />

                            <!-- Patient Info -->
                            <div class="alert alert-info mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Patient:</strong> {{ $visit->patient->full_name }}<br>
                                        <strong>MRN:</strong> {{ $visit->patient->mrn }}<br>
                                        <strong>Age:</strong> {{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Visit #:</strong> {{ $visit->visit_number }}<br>
                                        <strong>Visit Type:</strong> {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}<br>
                                        <strong>Existing Dispensations:</strong> {{ $visit->pharmacyDispensations->count() }}
                                    </div>
                                </div>
                            </div>

                            @if($prescription)
                                <div class="alert alert-success mb-4">
                                    <h6 class="mb-2"><i class="bx bx-pill me-2"></i>Prescription from Doctor:</h6>
                                    <p class="mb-0">{{ $prescription }}</p>
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('hospital.pharmacy.store', $visit->id) }}" method="POST" id="dispensationForm">
                                @csrf

                                <!-- Medications -->
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-capsule me-2"></i>Medications</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="itemsContainer">
                                            <div class="item-row mb-3 p-3 border rounded">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Medication <span class="text-danger">*</span></label>
                                                        <select class="form-select medication-select" name="items[0][product_id]" required onchange="loadStock(this, 0)">
                                                            <option value="">Select Medication</option>
                                                            @foreach($medications as $med)
                                                                <option value="{{ $med['id'] }}" 
                                                                        data-price="{{ $med['unit_price'] }}"
                                                                        data-name="{{ $med['name'] }}"
                                                                        data-stock="{{ $med['stock'] }}"
                                                                        data-track-stock="{{ $med['track_stock'] ? 1 : 0 }}">
                                                                    {{ $med['name'] }} ({{ $med['code'] }}) - {{ number_format($med['unit_price'], 2) }} TZS
                                                                    @if($med['track_stock'] && $locationId)
                                                                        [Stock: {{ $med['stock'] }} {{ $med['unit_of_measure'] }}]
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fw-bold">Prescribed Qty <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" name="items[0][quantity_prescribed]" 
                                                               min="1" value="1" required onchange="updateDispensed(0)">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label fw-bold">Dispensed Qty <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control dispensed-qty" name="items[0][quantity_dispensed]" 
                                                               min="0" value="0" required>
                                                        <small class="text-muted stock-info" id="stock_0"></small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-bold">Dosage Instructions</label>
                                                        <input type="text" class="form-control" name="items[0][dosage_instructions]" 
                                                               placeholder="e.g., 1 tablet twice daily">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="button" class="btn btn-danger w-100" onclick="removeItem(0)" style="display:none;">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                                            <i class="bx bx-plus me-1"></i>Add Medication
                                        </button>
                                    </div>
                                </div>

                                <!-- Additional Instructions -->
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bx bx-note me-2"></i>Additional Instructions</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="instructions" rows="3" 
                                                  placeholder="Additional instructions for the patient...">{{ old('instructions') }}</textarea>
                                    </div>
                                </div>

                                <!-- Bill Creation -->
                                <div class="card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-money me-2"></i>Billing</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="create_bill" name="create_bill" value="1" checked>
                                            <label class="form-check-label" for="create_bill">
                                                <strong>Create Bill for Dispensed Medications</strong>
                                            </label>
                                        </div>
                                        <div class="form-text">If checked, a bill will be created for the dispensed medications. Patient will need to pay before receiving medications.</div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.pharmacy.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Create Dispensation
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
<script>
let itemCount = 1;

function addItem() {
    const container = document.getElementById('itemsContainer');
    const newItem = document.createElement('div');
    newItem.className = 'item-row mb-3 p-3 border rounded';
    newItem.id = `item_${itemCount}`;
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <label class="form-label fw-bold">Medication <span class="text-danger">*</span></label>
                <select class="form-select medication-select" name="items[${itemCount}][product_id]" required onchange="loadStock(this, ${itemCount})">
                    <option value="">Select Medication</option>
                    @foreach($medications as $med)
                        <option value="{{ $med['id'] }}" 
                                data-price="{{ $med['unit_price'] }}"
                                data-name="{{ $med['name'] }}"
                                data-stock="{{ $med['stock'] }}"
                                data-track-stock="{{ $med['track_stock'] ? 1 : 0 }}">
                            {{ $med['name'] }} ({{ $med['code'] }}) - {{ number_format($med['unit_price'], 2) }} TZS
                            @if($med['track_stock'] && $locationId)
                                [Stock: {{ $med['stock'] }} {{ $med['unit_of_measure'] }}]
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Prescribed Qty <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="items[${itemCount}][quantity_prescribed]" 
                       min="1" value="1" required onchange="updateDispensed(${itemCount})">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Dispensed Qty <span class="text-danger">*</span></label>
                <input type="number" class="form-control dispensed-qty" name="items[${itemCount}][quantity_dispensed]" 
                       min="0" value="0" required>
                <small class="text-muted stock-info" id="stock_${itemCount}"></small>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Dosage Instructions</label>
                <input type="text" class="form-control" name="items[${itemCount}][dosage_instructions]" 
                       placeholder="e.g., 1 tablet twice daily">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-danger w-100" onclick="removeItem(${itemCount})">
                    <i class="bx bx-trash"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newItem);
    itemCount++;
}

function removeItem(index) {
    const item = document.getElementById(`item_${index}`);
    if (item) {
        item.remove();
    } else {
        // First item
        const firstItem = document.querySelector('.item-row');
        if (firstItem) {
            firstItem.querySelector('.medication-select').value = '';
            firstItem.querySelector('input[name*="[quantity_prescribed]"]').value = 1;
            firstItem.querySelector('input[name*="[quantity_dispensed]"]').value = 0;
            firstItem.querySelector('input[name*="[dosage_instructions]"]').value = '';
            document.getElementById('stock_0').textContent = '';
        }
    }
}

function loadStock(select, index) {
    const option = select.options[select.selectedIndex];
    if (!option || !option.value) {
        document.getElementById(`stock_${index}`).textContent = '';
        return;
    }

    const productId = option.value;
    const trackStock = option.getAttribute('data-track-stock') == 1;
    const stock = parseFloat(option.getAttribute('data-stock')) || 0;

    const stockInfo = document.getElementById(`stock_${index}`);
    if (trackStock) {
        stockInfo.textContent = `Available: ${stock}`;
        stockInfo.className = stock > 0 ? 'text-success' : 'text-danger';
    } else {
        stockInfo.textContent = 'Stock tracking disabled';
        stockInfo.className = 'text-muted';
    }

    // Auto-set dispensed quantity to prescribed quantity
    updateDispensed(index);
}

function updateDispensed(index) {
    const itemRow = document.getElementById(`item_${index}`) || document.querySelector('.item-row');
    const prescribedInput = itemRow.querySelector('input[name*="[quantity_prescribed]"]');
    const dispensedInput = itemRow.querySelector('input[name*="[quantity_dispensed]"]');
    
    if (prescribedInput && dispensedInput && dispensedInput.value == 0) {
        dispensedInput.value = prescribedInput.value;
    }
}

// Show remove button for first item if there are multiple items
document.getElementById('dispensationForm').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.item-row');
    if (items.length === 0) {
        e.preventDefault();
        alert('Please add at least one medication.');
        return false;
    }
    
    let hasValidItem = false;
    items.forEach(item => {
        const select = item.querySelector('.medication-select');
        const dispensed = item.querySelector('.dispensed-qty');
        if (select.value && parseInt(dispensed.value) > 0) {
            hasValidItem = true;
        }
    });
    
    if (!hasValidItem) {
        e.preventDefault();
        alert('Please select at least one medication and set dispensed quantity.');
        return false;
    }
});
</script>
@endpush
