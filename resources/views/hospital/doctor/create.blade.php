@extends('layouts.main')

@section('title', 'Record Consultation')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Doctor', 'url' => route('hospital.doctor.index'), 'icon' => 'bx bx-user-md'],
                ['label' => 'Record Consultation', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD CONSULTATION</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-user-md me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Consultation for {{ $visit->patient->full_name }}</h5>
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
                                        @if($visit->triageVitals)
                                            <strong>Priority:</strong> 
                                            <span class="badge bg-{{ $visit->triageVitals->priority == 'critical' ? 'dark' : ($visit->triageVitals->priority == 'high' ? 'danger' : ($visit->triageVitals->priority == 'medium' ? 'warning' : 'success')) }}">
                                                {{ ucfirst($visit->triageVitals->priority) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($visit->triageVitals)
                                <div class="alert alert-secondary mb-4">
                                    <h6 class="mb-2"><i class="bx bx-pulse me-2"></i>Triage Vitals:</h6>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <strong>BP:</strong> {{ $visit->triageVitals->blood_pressure_formatted ?? 'N/A' }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Pulse:</strong> {{ $visit->triageVitals->pulse_rate ? $visit->triageVitals->pulse_rate . ' bpm' : 'N/A' }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>Temp:</strong> {{ $visit->triageVitals->temperature ? $visit->triageVitals->temperature . ' °C' : 'N/A' }}
                                        </div>
                                        <div class="col-md-3">
                                            <strong>SpO2:</strong> {{ $visit->triageVitals->oxygen_saturation ? $visit->triageVitals->oxygen_saturation . ' %' : 'N/A' }}
                                        </div>
                                    </div>
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

                            <form action="{{ route('hospital.doctor.store', $visit->id) }}" method="POST">
                                @csrf

                                <!-- Chief Complaint -->
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-clipboard me-2"></i>Chief Complaint</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="chief_complaint" rows="3" 
                                                  placeholder="Patient's main complaint...">{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                                    </div>
                                </div>

                                <!-- History of Present Illness -->
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bx bx-history me-2"></i>History of Present Illness</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="history_of_present_illness" rows="4" 
                                                  placeholder="Detailed history of the current illness...">{{ old('history_of_present_illness') }}</textarea>
                                    </div>
                                </div>

                                <!-- Physical Examination -->
                                <div class="card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-search me-2"></i>Physical Examination</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="physical_examination" rows="4" 
                                                  placeholder="Findings from physical examination...">{{ old('physical_examination') }}</textarea>
                                    </div>
                                </div>

                                <!-- Diagnosis -->
                                <div class="card mb-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="bx bx-diagnosis me-2"></i>Diagnosis</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="diagnosis" rows="3" 
                                                  placeholder="Diagnosis...">{{ old('diagnosis') }}</textarea>
                                    </div>
                                </div>

                                <!-- Treatment Plan -->
                                <div class="card mb-4">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="bx bx-plan me-2"></i>Treatment Plan</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="treatment_plan" rows="4" 
                                                  placeholder="Treatment plan and recommendations...">{{ old('treatment_plan') }}</textarea>
                                    </div>
                                </div>

                                <!-- Prescription -->
                                <div class="card mb-4">
                                    <div class="card-header bg-dark text-white">
                                        <h6 class="mb-0"><i class="bx bx-pill me-2"></i>Prescription</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="prescription" rows="5" 
                                                  placeholder="Medications and dosages...">{{ old('prescription') }}</textarea>
                                    </div>
                                </div>

                                <!-- Medicines Selection -->
                                <div class="card mb-4">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="bx bx-capsule me-2"></i>Medicines / Dawa</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            Select medicines to prescribe. Stock availability is shown for each medicine.
                                        </div>
                                        <div id="medicines-container">
                                            <div class="medicine-item mb-3 p-3 border rounded">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Medicine</label>
                                                        <select class="form-select medicine-select" name="medicines[0][product_id]" onchange="updateMedicineInfo(this, 0)">
                                                            <option value="">Select Medicine...</option>
                                                            @foreach($medicines as $medicine)
                                                                <option value="{{ $medicine['id'] }}" 
                                                                        data-price="{{ $medicine['unit_price'] }}"
                                                                        data-stock="{{ $medicine['available_stock'] }}"
                                                                        data-unit="{{ $medicine['unit_of_measure'] }}"
                                                                        data-available="{{ $medicine['is_available'] ? '1' : '0' }}">
                                                                    {{ $medicine['name'] }} ({{ $medicine['code'] }})
                                                                    @if($medicine['is_available'])
                                                                        - Stock: {{ $medicine['available_stock'] }} {{ $medicine['unit_of_measure'] }}
                                                                    @else
                                                                        - OUT OF STOCK
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="text-muted medicine-stock-info" id="stock-info-0"></small>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Quantity</label>
                                                        <input type="number" class="form-control medicine-quantity" name="medicines[0][quantity]" value="1" min="1" onchange="calculateTotal()">
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label class="form-label">Price</label>
                                                        <input type="text" class="form-control medicine-price" readonly value="0.00">
                                                    </div>
                                                    <div class="col-md-1">
                                                        <label class="form-label">&nbsp;</label>
                                                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeMedicine(this)">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMedicine()">
                                            <i class="bx bx-plus me-1"></i>Add Medicine
                                        </button>
                                    </div>
                                </div>

                                <!-- Lab Tests Selection -->
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bx bx-test-tube me-2"></i>Lab Tests / Vipimo vya Lab</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($labServices as $lab)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input lab-test-checkbox" 
                                                               type="checkbox" 
                                                               name="lab_tests[{{ $loop->index }}][service_id]" 
                                                               value="{{ $lab['id'] }}" 
                                                               id="lab_{{ $lab['id'] }}"
                                                               data-price="{{ $lab['unit_price'] }}"
                                                               onchange="calculateTotal()">
                                                        <label class="form-check-label" for="lab_{{ $lab['id'] }}">
                                                            <strong>{{ $lab['name'] }}</strong>
                                                            <br>
                                                            <small class="text-muted">TZS {{ number_format($lab['unit_price'], 2) }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Ultrasound Services Selection -->
                                <div class="card mb-4">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="bx bx-scan me-2"></i>Ultrasound Services / Huduma za Ultrasound</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($ultrasoundServices as $ultrasound)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input ultrasound-checkbox" 
                                                               type="checkbox" 
                                                               name="ultrasound_services[{{ $loop->index }}][service_id]" 
                                                               value="{{ $ultrasound['id'] }}" 
                                                               id="ultrasound_{{ $ultrasound['id'] }}"
                                                               data-price="{{ $ultrasound['unit_price'] }}"
                                                               onchange="calculateTotal()">
                                                        <label class="form-check-label" for="ultrasound_{{ $ultrasound['id'] }}">
                                                            <strong>{{ $ultrasound['name'] }}</strong>
                                                            <br>
                                                            <small class="text-muted">TZS {{ number_format($ultrasound['unit_price'], 2) }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Other Services Selection -->
                                <div class="card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Other Services / Huduma Zingine</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($otherServices as $service)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input other-service-checkbox" 
                                                               type="checkbox" 
                                                               name="other_services[{{ $loop->index }}][service_id]" 
                                                               value="{{ $service['id'] }}" 
                                                               id="service_{{ $service['id'] }}"
                                                               data-price="{{ $service['unit_price'] }}"
                                                               onchange="calculateTotal()">
                                                        <label class="form-check-label" for="service_{{ $service['id'] }}">
                                                            <strong>{{ $service['name'] }}</strong>
                                                            <br>
                                                            <small class="text-muted">TZS {{ number_format($service['unit_price'], 2) }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Cost Summary -->
                                <div class="card mb-4 border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Total Cost Summary / Jumla ya Gharama</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Medicines:</strong>
                                                <div class="h5 text-danger" id="medicines-total">TZS 0.00</div>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Lab Tests:</strong>
                                                <div class="h5 text-info" id="lab-total">TZS 0.00</div>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Ultrasound:</strong>
                                                <div class="h5 text-warning" id="ultrasound-total">TZS 0.00</div>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Other Services:</strong>
                                                <div class="h5 text-success" id="services-total">TZS 0.00</div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-12 text-end">
                                                <h4 class="mb-0">
                                                    <strong>Grand Total:</strong>
                                                    <span class="text-primary" id="grand-total">TZS 0.00</span>
                                                </h4>
                                                <small class="text-muted">Bill will be created automatically when consultation is saved</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="bx bx-note me-2"></i>Additional Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        <textarea class="form-control" name="notes" rows="3" 
                                                  placeholder="Any additional notes or observations...">{{ old('notes') }}</textarea>
                                    </div>
                                </div>

                                <!-- Department Routing -->
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-building me-2"></i>Route to Departments (Optional)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            Select additional departments to route this patient to (e.g., Lab, Pharmacy, etc.)
                                        </div>
                                        <div class="row">
                                            @foreach($departments as $department)
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="route_to_departments[]" 
                                                               value="{{ $department->id }}" 
                                                               id="dept_{{ $department->id }}">
                                                        <label class="form-check-label" for="dept_{{ $department->id }}">
                                                            <strong>{{ $department->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $department->type)) }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.doctor.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Save Consultation
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

    <script>
        let medicineIndex = 1;

        function addMedicine() {
            const container = document.getElementById('medicines-container');
            const newItem = document.createElement('div');
            newItem.className = 'medicine-item mb-3 p-3 border rounded';
            newItem.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Medicine</label>
                        <select class="form-select medicine-select" name="medicines[${medicineIndex}][product_id]" onchange="updateMedicineInfo(this, ${medicineIndex})">
                            <option value="">Select Medicine...</option>
                            @foreach($medicines as $medicine)
                                <option value="{{ $medicine['id'] }}" 
                                        data-price="{{ $medicine['unit_price'] }}"
                                        data-stock="{{ $medicine['available_stock'] }}"
                                        data-unit="{{ $medicine['unit_of_measure'] }}"
                                        data-available="{{ $medicine['is_available'] ? '1' : '0' }}">
                                    {{ $medicine['name'] }} ({{ $medicine['code'] }})
                                    @if($medicine['is_available'])
                                        - Stock: {{ $medicine['available_stock'] }} {{ $medicine['unit_of_measure'] }}
                                    @else
                                        - OUT OF STOCK
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted medicine-stock-info" id="stock-info-${medicineIndex}"></small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control medicine-quantity" name="medicines[${medicineIndex}][quantity]" value="1" min="1" onchange="calculateTotal()">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Price</label>
                        <input type="text" class="form-control medicine-price" readonly value="0.00">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeMedicine(this)">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newItem);
            medicineIndex++;
        }

        function removeMedicine(button) {
            button.closest('.medicine-item').remove();
            calculateTotal();
        }

        function updateMedicineInfo(select, index) {
            const option = select.options[select.selectedIndex];
            const price = parseFloat(option.getAttribute('data-price')) || 0;
            const stock = parseFloat(option.getAttribute('data-stock')) || 0;
            const unit = option.getAttribute('data-unit') || '';
            const available = option.getAttribute('data-available') === '1';

            const quantityInput = select.closest('.medicine-item').querySelector('.medicine-quantity');
            const priceInput = select.closest('.medicine-item').querySelector('.medicine-price');
            const stockInfo = document.getElementById(`stock-info-${index}`);

            if (select.value) {
                priceInput.value = price.toFixed(2);
                if (available) {
                    stockInfo.textContent = `Available: ${stock} ${unit}`;
                    stockInfo.className = 'text-success';
                    quantityInput.max = stock;
                } else {
                    stockInfo.textContent = 'OUT OF STOCK';
                    stockInfo.className = 'text-danger';
                    quantityInput.max = 0;
                    quantityInput.value = 0;
                }
            } else {
                priceInput.value = '0.00';
                stockInfo.textContent = '';
            }

            calculateTotal();
        }

        function calculateTotal() {
            let medicinesTotal = 0;
            let labTotal = 0;
            let ultrasoundTotal = 0;
            let servicesTotal = 0;

            // Calculate medicines total
            document.querySelectorAll('.medicine-item').forEach(item => {
                const select = item.querySelector('.medicine-select');
                const quantityInput = item.querySelector('.medicine-quantity');
                if (select.value && quantityInput.value) {
                    const price = parseFloat(select.options[select.selectedIndex].getAttribute('data-price')) || 0;
                    const quantity = parseFloat(quantityInput.value) || 0;
                    medicinesTotal += price * quantity;
                    
                    // Update price display
                    const priceInput = item.querySelector('.medicine-price');
                    priceInput.value = (price * quantity).toFixed(2);
                }
            });

            // Calculate lab tests total
            document.querySelectorAll('.lab-test-checkbox:checked').forEach(checkbox => {
                labTotal += parseFloat(checkbox.getAttribute('data-price')) || 0;
            });

            // Calculate ultrasound total
            document.querySelectorAll('.ultrasound-checkbox:checked').forEach(checkbox => {
                ultrasoundTotal += parseFloat(checkbox.getAttribute('data-price')) || 0;
            });

            // Calculate other services total
            document.querySelectorAll('.other-service-checkbox:checked').forEach(checkbox => {
                servicesTotal += parseFloat(checkbox.getAttribute('data-price')) || 0;
            });

            // Update display
            document.getElementById('medicines-total').textContent = 'TZS ' + medicinesTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            document.getElementById('lab-total').textContent = 'TZS ' + labTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            document.getElementById('ultrasound-total').textContent = 'TZS ' + ultrasoundTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            document.getElementById('services-total').textContent = 'TZS ' + servicesTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            
            const grandTotal = medicinesTotal + labTotal + ultrasoundTotal + servicesTotal;
            document.getElementById('grand-total').textContent = 'TZS ' + grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateTotal();
        });
    </script>
@endsection
