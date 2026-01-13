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
@endsection
