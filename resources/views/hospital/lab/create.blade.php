@extends('layouts.main')

@section('title', 'Record Lab Test')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Lab', 'url' => route('hospital.lab.index'), 'icon' => 'bx bx-test-tube'],
                ['label' => 'Record Test', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD LAB TEST</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-test-tube me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Lab Test for {{ $visit->patient->full_name }}</h5>
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
                                        <strong>Existing Tests:</strong> {{ $visit->labResults->count() }}
                                    </div>
                                </div>
                            </div>

                            @if($visit->labResults->count() > 0)
                                <div class="alert alert-secondary mb-4">
                                    <h6 class="mb-2"><i class="bx bx-list-ul me-2"></i>Previous Tests:</h6>
                                    <ul class="mb-0">
                                        @foreach($visit->labResults as $result)
                                            <li>
                                                {{ $result->test_name }} - 
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

                            <form action="{{ route('hospital.lab.store', $visit->id) }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="service_id" class="form-label fw-bold">Select Test Service</label>
                                            <select class="form-select @error('service_id') is-invalid @enderror" 
                                                    id="service_id" name="service_id" onchange="selectTest(this)">
                                                <option value="">Select a test service...</option>
                                                @foreach($labTests as $test)
                                                    <option value="{{ $test->id }}" 
                                                            data-name="{{ $test->name }}"
                                                            {{ old('service_id') == $test->id ? 'selected' : '' }}>
                                                        {{ $test->name }} - {{ number_format($test->unit_price, 2) }} TZS
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
                                            <label for="test_name" class="form-label fw-bold">Test Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('test_name') is-invalid @enderror" 
                                                   id="test_name" name="test_name" value="{{ old('test_name') }}" required>
                                            @error('test_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Or enter test name manually</div>
                                        </div>
                                    </div>

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
                                            <div class="form-text">Select "Ready" if test is completed and results are available</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="result_value" class="form-label fw-bold">Result Value</label>
                                            <input type="text" class="form-control" id="result_value" name="result_value" 
                                                   value="{{ old('result_value') }}" placeholder="e.g., 120, Negative, Positive">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="unit" class="form-label fw-bold">Unit</label>
                                            <input type="text" class="form-control" id="unit" name="unit" 
                                                   value="{{ old('unit') }}" placeholder="e.g., mg/dL, %, cells/μL">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="status" class="form-label fw-bold">Result Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="">Select Status</option>
                                                <option value="normal" {{ old('status') == 'normal' ? 'selected' : '' }}>Normal</option>
                                                <option value="abnormal" {{ old('status') == 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                                <option value="critical" {{ old('status') == 'critical' ? 'selected' : '' }}>Critical</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="reference_range" class="form-label fw-bold">Reference Range</label>
                                            <input type="text" class="form-control" id="reference_range" name="reference_range" 
                                                   value="{{ old('reference_range') }}" placeholder="e.g., 70-100 mg/dL, < 5.0">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label fw-bold">Notes</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                                      placeholder="Additional notes or observations...">{{ old('notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.lab.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Save Test Result
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
function selectTest(select) {
    if (select.value) {
        document.getElementById('test_name').value = select.value;
    }
}
</script>
@endpush
