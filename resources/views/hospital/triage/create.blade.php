@extends('layouts.main')

@section('title', 'Record Triage Vitals')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Triage', 'url' => route('hospital.triage.index'), 'icon' => 'bx bx-pulse'],
                ['label' => 'Record Vitals', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD TRIAGE VITALS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-pulse me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Record Vitals for {{ $visit->patient->full_name }}</h5>
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
                                        <strong>Chief Complaint:</strong> {{ $visit->chief_complaint ?? 'N/A' }}
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

                            <form action="{{ route('hospital.triage.store', $visit->id) }}" method="POST" id="triageForm">
                                @csrf

                                <!-- Vital Signs -->
                                <div class="card mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-pulse me-2"></i>Vital Signs</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="temperature" class="form-label fw-bold">Temperature (°C)</label>
                                                    <input type="number" step="0.01" class="form-control" id="temperature" name="temperature" 
                                                           value="{{ old('temperature') }}" min="30" max="45">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="blood_pressure_systolic" class="form-label fw-bold">BP Systolic</label>
                                                    <input type="number" class="form-control" id="blood_pressure_systolic" name="blood_pressure_systolic" 
                                                           value="{{ old('blood_pressure_systolic') }}" min="50" max="250">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="blood_pressure_diastolic" class="form-label fw-bold">BP Diastolic</label>
                                                    <input type="number" class="form-control" id="blood_pressure_diastolic" name="blood_pressure_diastolic" 
                                                           value="{{ old('blood_pressure_diastolic') }}" min="30" max="150">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="pulse_rate" class="form-label fw-bold">Pulse Rate (bpm)</label>
                                                    <input type="number" class="form-control" id="pulse_rate" name="pulse_rate" 
                                                           value="{{ old('pulse_rate') }}" min="30" max="200">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="respiratory_rate" class="form-label fw-bold">Respiratory Rate (/min)</label>
                                                    <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate" 
                                                           value="{{ old('respiratory_rate') }}" min="10" max="50">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="oxygen_saturation" class="form-label fw-bold">Oxygen Saturation (%)</label>
                                                    <input type="number" step="0.01" class="form-control" id="oxygen_saturation" name="oxygen_saturation" 
                                                           value="{{ old('oxygen_saturation') }}" min="0" max="100">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="weight" class="form-label fw-bold">Weight (kg)</label>
                                                    <input type="number" step="0.01" class="form-control" id="weight" name="weight" 
                                                           value="{{ old('weight') }}" min="0" max="300" onchange="calculateBMI()">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="height" class="form-label fw-bold">Height (cm)</label>
                                                    <input type="number" step="0.01" class="form-control" id="height" name="height" 
                                                           value="{{ old('height') }}" min="0" max="250" onchange="calculateBMI()">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">BMI</label>
                                                    <input type="text" class="form-control" id="bmi_display" readonly>
                                                    <small class="text-muted">Auto-calculated</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Assessment -->
                                <div class="card mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bx bx-clipboard me-2"></i>Assessment</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="priority" class="form-label fw-bold">Priority <span class="text-danger">*</span></label>
                                                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                                        <option value="">Select Priority</option>
                                                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                                        <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                                        <option value="critical" {{ old('priority') == 'critical' ? 'selected' : '' }}>Critical</option>
                                                    </select>
                                                    @error('priority')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label for="chief_complaint" class="form-label fw-bold">Chief Complaint</label>
                                                    <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="2">{{ old('chief_complaint', $visit->chief_complaint) }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label for="triage_notes" class="form-label fw-bold">Triage Notes</label>
                                                    <textarea class="form-control" id="triage_notes" name="triage_notes" rows="3" 
                                                              placeholder="Additional notes, observations, or recommendations...">{{ old('triage_notes') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Department Routing -->
                                <div class="card mb-4">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-building me-2"></i>Route to Departments (Optional)</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="bx bx-info-circle me-2"></i>
                                            Select additional departments to route this patient to. Patient will be added to their waiting lists.
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
                                            <a href="{{ route('hospital.triage.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Save Vitals & Route
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
function calculateBMI() {
    const weight = parseFloat(document.getElementById('weight').value) || 0;
    const height = parseFloat(document.getElementById('height').value) || 0;
    
    if (weight > 0 && height > 0) {
        const heightInMeters = height / 100;
        const bmi = weight / (heightInMeters * heightInMeters);
        document.getElementById('bmi_display').value = bmi.toFixed(2);
    } else {
        document.getElementById('bmi_display').value = '';
    }
}
</script>
@endpush
