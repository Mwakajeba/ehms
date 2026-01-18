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
                                        @if($pharmacyInvoice)
                                            <strong>Invoice #:</strong> {{ $pharmacyInvoice->invoice_number }}<br>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(empty($medications) || $medications->count() === 0)
                                <div class="alert alert-warning">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>No medications found.</strong> The doctor must create a pharmacy bill first before dispensing medications.
                                </div>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('hospital.pharmacy.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i>Back
                                    </a>
                                </div>
                            @else
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

                                    <!-- Medications from Doctor's Invoice -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0"><i class="bx bx-capsule me-2"></i>Medications Prescribed by Doctor</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info mb-3">
                                                <i class="bx bx-info-circle me-2"></i>
                                                <strong>Note:</strong> These medications are from the doctor's pharmacy bill. You can only set the dispensed quantity for each medication.
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Medication</th>
                                                            <th>Description/Dosage</th>
                                                            <th>Prescribed Qty</th>
                                                            <th>Stock Available</th>
                                                            <th>Dispensed Qty <span class="text-danger">*</span></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($medications as $index => $med)
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $med['id'] }}">
                                                                    <input type="hidden" name="items[{{ $index }}][sales_invoice_item_id]" value="{{ $med['sales_invoice_item_id'] ?? '' }}">
                                                                    <strong>{{ $med['name'] }}</strong><br>
                                                                    <small class="text-muted">{{ $med['code'] }}</small>
                                                                </td>
                                                                <td>
                                                                    <small class="text-muted">{{ $med['description'] ?? 'No dosage instructions' }}</small>
                                                                </td>
                                                                <td>
                                                                    <input type="hidden" name="items[{{ $index }}][quantity_prescribed]" value="{{ $med['quantity'] }}">
                                                                    <span class="fw-bold">{{ $med['quantity'] }} {{ $med['unit_of_measure'] }}</span>
                                                                </td>
                                                                <td>
                                                                    @if($med['track_stock'])
                                                                        <span class="badge bg-{{ $med['stock'] > 0 ? 'success' : 'danger' }}">
                                                                            {{ $med['stock'] }} {{ $med['unit_of_measure'] }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">N/A</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <input type="number" class="form-control" 
                                                                           name="items[{{ $index }}][quantity_dispensed]" 
                                                                           value="{{ $med['quantity'] }}" 
                                                                           min="0" 
                                                                           max="{{ $med['quantity'] }}" 
                                                                           required
                                                                           data-stock="{{ $med['stock'] }}"
                                                                           data-track-stock="{{ $med['track_stock'] ? 1 : 0 }}"
                                                                           onchange="checkStock(this)">
                                                                    <small class="text-muted stock-warning" style="display:none; color:red;"></small>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function checkStock(input) {
    const trackStock = input.getAttribute('data-track-stock') == 1;
    const availableStock = parseFloat(input.getAttribute('data-stock')) || 0;
    const requestedQty = parseFloat(input.value) || 0;
    const warning = input.closest('td').querySelector('.stock-warning');
    
    if (trackStock && requestedQty > availableStock) {
        warning.textContent = `Warning: Only ${availableStock} available in stock!`;
        warning.style.display = 'block';
    } else {
        warning.style.display = 'none';
    }
}

// Disable bill creation (bill already exists from doctor)
document.getElementById('dispensationForm')?.addEventListener('submit', function(e) {
    // Bill already exists from doctor's invoice, so no need to create another
});
</script>
@endpush
