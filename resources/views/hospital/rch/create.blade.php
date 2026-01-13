@extends('layouts.main')

@section('title', 'Record RCH Service')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'RCH', 'url' => route('hospital.rch.index'), 'icon' => 'bx bx-heart'],
                ['label' => 'Record Service', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD RCH SERVICE</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-heart me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">RCH Service for {{ $visit->patient->full_name }}</h5>
                            </div>
                            <hr />

                            <!-- Patient Info -->
                            <div class="alert alert-info mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Patient:</strong> {{ $visit->patient->full_name }}<br>
                                        <strong>MRN:</strong> {{ $visit->patient->mrn }}<br>
                                        <strong>Age:</strong> {{ $visit->patient->age ? $visit->patient->age . ' years' : 'N/A' }}<br>
                                        <strong>Gender:</strong> {{ ucfirst($visit->patient->gender ?? 'N/A') }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Visit #:</strong> {{ $visit->visit_number }}<br>
                                        <strong>Visit Type:</strong> {{ ucfirst(str_replace('_', ' ', $visit->visit_type)) }}<br>
                                        <strong>Existing Records:</strong> {{ $visit->rchRecords->count() }}
                                    </div>
                                </div>
                            </div>

                            @if($visit->rchRecords->count() > 0)
                                <div class="alert alert-secondary mb-4">
                                    <h6 class="mb-2"><i class="bx bx-list-ul me-2"></i>Previous Services:</h6>
                                    <ul class="mb-0">
                                        @foreach($visit->rchRecords as $record)
                                            <li>
                                                {{ ucfirst(str_replace('_', ' ', $record->service_type)) }} - 
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

                            <form action="{{ route('hospital.rch.store', $visit->id) }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="service_id" class="form-label fw-bold">Select RCH Service</label>
                                            <select class="form-select @error('service_id') is-invalid @enderror" 
                                                    id="service_id" name="service_id">
                                                <option value="">Select a service...</option>
                                                @foreach($rchServices as $service)
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
                                            <label for="service_type" class="form-label fw-bold">Service Type <span class="text-danger">*</span></label>
                                            <select class="form-select @error('service_type') is-invalid @enderror" 
                                                   id="service_type" name="service_type" required>
                                                <option value="">Select service type...</option>
                                                <option value="antenatal_care" {{ old('service_type') == 'antenatal_care' ? 'selected' : '' }}>Antenatal Care</option>
                                                <option value="postnatal_care" {{ old('service_type') == 'postnatal_care' ? 'selected' : '' }}>Postnatal Care</option>
                                                <option value="child_health" {{ old('service_type') == 'child_health' ? 'selected' : '' }}>Child Health</option>
                                                <option value="family_planning" {{ old('service_type') == 'family_planning' ? 'selected' : '' }}>Family Planning</option>
                                                <option value="immunization" {{ old('service_type') == 'immunization' ? 'selected' : '' }}>Immunization</option>
                                                <option value="growth_monitoring" {{ old('service_type') == 'growth_monitoring' ? 'selected' : '' }}>Growth Monitoring</option>
                                                <option value="health_education" {{ old('service_type') == 'health_education' ? 'selected' : '' }}>Health Education</option>
                                                <option value="counseling" {{ old('service_type') == 'counseling' ? 'selected' : '' }}>Counseling</option>
                                                <option value="other" {{ old('service_type') == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('service_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="service_description" class="form-label fw-bold">Service Description</label>
                                            <textarea class="form-control @error('service_description') is-invalid @enderror" 
                                                      id="service_description" name="service_description" rows="3"
                                                      placeholder="Describe the service provided...">{{ old('service_description') }}</textarea>
                                            @error('service_description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Vitals Section -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="bx bx-pulse me-2"></i>Vital Signs & Measurements</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="vitals_weight" class="form-label">Weight (kg)</label>
                                                    <input type="number" step="0.1" class="form-control" 
                                                           id="vitals_weight" name="vitals[weight]" 
                                                           value="{{ old('vitals.weight') }}" placeholder="0.0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="vitals_height" class="form-label">Height (cm)</label>
                                                    <input type="number" step="0.1" class="form-control" 
                                                           id="vitals_height" name="vitals[height]" 
                                                           value="{{ old('vitals.height') }}" placeholder="0.0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="vitals_blood_pressure" class="form-label">Blood Pressure</label>
                                                    <input type="text" class="form-control" 
                                                           id="vitals_blood_pressure" name="vitals[blood_pressure]" 
                                                           value="{{ old('vitals.blood_pressure') }}" placeholder="e.g., 120/80">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="vitals_temperature" class="form-label">Temperature (°C)</label>
                                                    <input type="number" step="0.1" class="form-control" 
                                                           id="vitals_temperature" name="vitals[temperature]" 
                                                           value="{{ old('vitals.temperature') }}" placeholder="37.0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="vitals_pulse" class="form-label">Pulse (bpm)</label>
                                                    <input type="number" class="form-control" 
                                                           id="vitals_pulse" name="vitals[pulse]" 
                                                           value="{{ old('vitals.pulse') }}" placeholder="72">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="vitals_respiratory_rate" class="form-label">Respiratory Rate (/min)</label>
                                                    <input type="number" class="form-control" 
                                                           id="vitals_respiratory_rate" name="vitals[respiratory_rate]" 
                                                           value="{{ old('vitals.respiratory_rate') }}" placeholder="16">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="findings" class="form-label fw-bold">Clinical Findings</label>
                                            <textarea class="form-control @error('findings') is-invalid @enderror" 
                                                      id="findings" name="findings" rows="4"
                                                      placeholder="Record clinical findings and observations...">{{ old('findings') }}</textarea>
                                            @error('findings')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="recommendations" class="form-label fw-bold">Recommendations</label>
                                            <textarea class="form-control @error('recommendations') is-invalid @enderror" 
                                                      id="recommendations" name="recommendations" rows="4"
                                                      placeholder="Provide recommendations...">{{ old('recommendations') }}</textarea>
                                            @error('recommendations')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="counseling_notes" class="form-label fw-bold">Counseling Notes</label>
                                            <textarea class="form-control @error('counseling_notes') is-invalid @enderror" 
                                                      id="counseling_notes" name="counseling_notes" rows="3"
                                                      placeholder="Record counseling provided...">{{ old('counseling_notes') }}</textarea>
                                            @error('counseling_notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="health_education_topics" class="form-label fw-bold">Health Education Topics</label>
                                            <textarea class="form-control @error('health_education_topics') is-invalid @enderror" 
                                                      id="health_education_topics" name="health_education_topics" rows="3"
                                                      placeholder="Topics covered in health education...">{{ old('health_education_topics') }}</textarea>
                                            @error('health_education_topics')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                            <select class="form-select @error('status') is-invalid @enderror" 
                                                   id="status" name="status" required>
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
                                        <div class="mb-3">
                                            <label for="next_appointment_date" class="form-label fw-bold">Next Appointment Date</label>
                                            <input type="date" class="form-control @error('next_appointment_date') is-invalid @enderror" 
                                                   id="next_appointment_date" name="next_appointment_date" 
                                                   value="{{ old('next_appointment_date') }}" 
                                                   min="{{ date('Y-m-d') }}">
                                            @error('next_appointment_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Required if status is "Follow-up Required"</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label fw-bold">Additional Notes</label>
                                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                      id="notes" name="notes" rows="3"
                                                      placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                                            @error('notes')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.rch.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Save RCH Record
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
    // Auto-fill next appointment date if follow-up required is selected
    document.getElementById('status').addEventListener('change', function() {
        if (this.value === 'follow_up_required') {
            const nextAppointmentInput = document.getElementById('next_appointment_date');
            if (!nextAppointmentInput.value) {
                // Set default to 1 week from today
                const nextWeek = new Date();
                nextWeek.setDate(nextWeek.getDate() + 7);
                nextAppointmentInput.value = nextWeek.toISOString().split('T')[0];
            }
        }
    });
</script>
@endpush
