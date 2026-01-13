@extends('layouts.main')

@section('title', 'Record Ultrasound Examination')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Ultrasound', 'url' => route('hospital.ultrasound.index'), 'icon' => 'bx bx-scan'],
                ['label' => 'Record Examination', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD ULTRASOUND EXAMINATION</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-scan me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Ultrasound Examination for {{ $visit->patient->full_name }}</h5>
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
                                        <strong>Existing Examinations:</strong> {{ $visit->ultrasoundResults->count() }}
                                    </div>
                                </div>
                            </div>

                            @if($visit->ultrasoundResults->count() > 0)
                                <div class="alert alert-secondary mb-4">
                                    <h6 class="mb-2"><i class="bx bx-list-ul me-2"></i>Previous Examinations:</h6>
                                    <ul class="mb-0">
                                        @foreach($visit->ultrasoundResults as $result)
                                            <li>
                                                {{ $result->examination_type }} - 
                                                @if($result->result_status === 'ready')
                                                    <span class="badge bg-success">Ready</span>
                                                @else
                                                    <span class="badge bg-warning">Pending</span>
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

                            <form action="{{ route('hospital.ultrasound.store', $visit->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="service_id" class="form-label fw-bold">Select Ultrasound Service</label>
                                            <select class="form-select @error('service_id') is-invalid @enderror" 
                                                    id="service_id" name="service_id">
                                                <option value="">Select a service...</option>
                                                @foreach($ultrasoundServices as $service)
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
                                            <label for="examination_type" class="form-label fw-bold">Examination Type <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('examination_type') is-invalid @enderror" 
                                                   id="examination_type" name="examination_type" 
                                                   value="{{ old('examination_type') }}" 
                                                   placeholder="e.g., Abdominal Ultrasound, Pelvic Scan, etc." required>
                                            @error('examination_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="result_status" class="form-label fw-bold">Result Status <span class="text-danger">*</span></label>
                                            <select class="form-select @error('result_status') is-invalid @enderror" 
                                                    id="result_status" name="result_status" required>
                                                <option value="pending" {{ old('result_status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="ready" {{ old('result_status') == 'ready' ? 'selected' : '' }}>Ready</option>
                                            </select>
                                            @error('result_status')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Select "Ready" if examination is completed and results are available</div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
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
                                            <label for="findings" class="form-label fw-bold">Findings</label>
                                            <textarea class="form-control" id="findings" name="findings" rows="4" 
                                                      placeholder="Detailed findings from the ultrasound examination...">{{ old('findings') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="impression" class="form-label fw-bold">Impression</label>
                                            <textarea class="form-control" id="impression" name="impression" rows="3" 
                                                      placeholder="Clinical impression based on findings...">{{ old('impression') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="recommendation" class="form-label fw-bold">Recommendation</label>
                                            <textarea class="form-control" id="recommendation" name="recommendation" rows="3" 
                                                      placeholder="Recommendations or follow-up instructions...">{{ old('recommendation') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.ultrasound.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Save Examination
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
