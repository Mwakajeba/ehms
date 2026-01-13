@extends('layouts.main')

@section('title', 'Record Dental Procedure')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Dental', 'url' => route('hospital.dental.index'), 'icon' => 'bx bx-smile'],
                ['label' => 'Record Procedure', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD DENTAL PROCEDURE</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-smile me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Dental Procedure for {{ $visit->patient->full_name }}</h5>
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
                                        <strong>Existing Records:</strong> {{ $visit->dentalRecords->count() }}
                                    </div>
                                </div>
                            </div>

                            @if($visit->dentalRecords->count() > 0)
                                <div class="alert alert-secondary mb-4">
                                    <h6 class="mb-2"><i class="bx bx-list-ul me-2"></i>Previous Procedures:</h6>
                                    <ul class="mb-0">
                                        @foreach($visit->dentalRecords as $record)
                                            <li>
                                                {{ $record->procedure_type }} - 
                                                @if($record->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($record->status === 'follow_up_required')
                                                    <span class="badge bg-warning">Follow-up Required</span>
                                                @else
                                                    <span class="badge bg-info">Pending</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
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

                            <form action="{{ route('hospital.dental.store', $visit->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="service_id" class="form-label fw-bold">Select Dental Service</label>
                                            <select class="form-select @error('service_id') is-invalid @enderror" 
                                                    id="service_id" name="service_id">
                                                <option value="">Select a service...</option>
                                                @foreach($dentalServices as $service)
                                                    <option value="{{ $service->id }}" 
                                                            {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                                        {{ $service->name }} - {{ number_format($service->unit_price, 2) }} TZS
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('service_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="procedure_type" class="form-label fw-bold">Procedure Type <span class="text-danger">*</span></label>
                                            <select class="form-select @error('procedure_type') is-invalid @enderror" 
                                                   id="procedure_type" name="procedure_type" required>
                                                <option value="">Select Procedure Type</option>
                                                <option value="Cleaning" {{ old('procedure_type') == 'Cleaning' ? 'selected' : '' }}>Cleaning</option>
                                                <option value="Filling" {{ old('procedure_type') == 'Filling' ? 'selected' : '' }}>Filling</option>
                                                <option value="Extraction" {{ old('procedure_type') == 'Extraction' ? 'selected' : '' }}>Extraction</option>
                                                <option value="Root Canal" {{ old('procedure_type') == 'Root Canal' ? 'selected' : '' }}>Root Canal</option>
                                                <option value="Crown" {{ old('procedure_type') == 'Crown' ? 'selected' : '' }}>Crown</option>
                                                <option value="Bridge" {{ old('procedure_type') == 'Bridge' ? 'selected' : '' }}>Bridge</option>
                                                <option value="Dentures" {{ old('procedure_type') == 'Dentures' ? 'selected' : '' }}>Dentures</option>
                                                <option value="Orthodontic Treatment" {{ old('procedure_type') == 'Orthodontic Treatment' ? 'selected' : '' }}>Orthodontic Treatment</option>
                                                <option value="Other" {{ old('procedure_type') == 'Other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('procedure_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="procedure_description" class="form-label fw-bold">Procedure Description</label>
                                            <textarea class="form-control" id="procedure_description" name="procedure_description" rows="3" 
                                                      placeholder="Detailed description of the procedure...">{{ old('procedure_description') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="findings" class="form-label fw-bold">Examination Findings</label>
                                            <textarea class="form-control" id="findings" name="findings" rows="4" 
                                                      placeholder="Dental examination findings...">{{ old('findings') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="treatment_plan" class="form-label fw-bold">Treatment Plan</label>
                                            <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="3" 
                                                      placeholder="Planned treatment approach...">{{ old('treatment_plan') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="treatment_performed" class="form-label fw-bold">Treatment Performed</label>
                                            <textarea class="form-control" id="treatment_performed" name="treatment_performed" rows="4" 
                                                      placeholder="Details of treatment performed...">{{ old('treatment_performed') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                            <select class="form-select @error('status') is-invalid @enderror" 
                                                    id="status" name="status" required onchange="toggleNextAppointment()">
                                                <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="follow_up_required" {{ old('status') == 'follow_up_required' ? 'selected' : '' }}>Follow-up Required</option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3" id="nextAppointmentDiv" style="display: none;">
                                            <label for="next_appointment_date" class="form-label fw-bold">Next Appointment Date</label>
                                            <input type="date" class="form-control @error('next_appointment_date') is-invalid @enderror" 
                                                   id="next_appointment_date" name="next_appointment_date" 
                                                   value="{{ old('next_appointment_date') }}" 
                                                   min="{{ date('Y-m-d') }}">
                                            @error('next_appointment_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="images" class="form-label fw-bold">Upload Images</label>
                                            <input type="file" class="form-control @error('images.*') is-invalid @enderror" 
                                                   id="images" name="images[]" 
                                                   accept="image/*" multiple>
                                            @error('images.*')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">You can upload multiple images (Max 5MB per image)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label fw-bold">Additional Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                      placeholder="Any additional notes or observations...">{{ old('notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.dental.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Save Record
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
function toggleNextAppointment() {
    const status = document.getElementById('status').value;
    const nextAppointmentDiv = document.getElementById('nextAppointmentDiv');
    const nextAppointmentInput = document.getElementById('next_appointment_date');
    
    if (status === 'follow_up_required') {
        nextAppointmentDiv.style.display = 'block';
        nextAppointmentInput.setAttribute('required', 'required');
    } else {
        nextAppointmentDiv.style.display = 'none';
        nextAppointmentInput.removeAttribute('required');
        nextAppointmentInput.value = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleNextAppointment();
});
</script>
@endpush
