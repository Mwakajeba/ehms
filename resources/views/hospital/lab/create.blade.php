@extends('layouts.main')

@section('title', 'Record Lab Test Results')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Lab', 'url' => route('hospital.lab.index'), 'icon' => 'bx bx-test-tube'],
                ['label' => 'Record Results', 'url' => '#', 'icon' => 'bx bx-edit']
            ]" />
            <h6 class="mb-0 text-uppercase">RECORD LAB TEST RESULTS</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bx bx-user me-2"></i>Patient: {{ $visit->patient->full_name }} (MRN: {{ $visit->patient->mrn }})</h5>
                        </div>
                        <div class="card-body">
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($labInvoice && $labInvoiceItems->count() > 0)
                                <div class="alert alert-info mb-4">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Lab Tests from Doctor:</strong> The following tests were ordered by the doctor. Please add results for each test.
                                </div>

                                <form action="{{ route('hospital.lab.store', $visit->id) }}" method="POST" id="lab-results-form">
                                    @csrf

                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="20%">Test Name</th>
                                                    <th width="15%">Result Value</th>
                                                    <th width="10%">Unit</th>
                                                    <th width="15%">Reference Range</th>
                                                    <th width="10%">Status</th>
                                                    <th width="15%">Result Status</th>
                                                    <th width="15%">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($labInvoiceItems as $index => $item)
                                                    @php
                                                        $serviceId = $item->inventory_item_id;
                                                        $service = $item->inventoryItem ?? null;
                                                        $existingResult = $existingResults->get($serviceId);
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <input type="hidden" name="results[{{ $index }}][service_id]" value="{{ $serviceId }}">
                                                            <strong>{{ $item->item_name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $service->code ?? $item->item_code ?? 'N/A' }}</small>
                                                        </td>
                                                        <td>
                                                            <input type="text" 
                                                                   class="form-control form-control-sm" 
                                                                   name="results[{{ $index }}][result_value]" 
                                                                   value="{{ $existingResult->result_value ?? old("results.{$index}.result_value") }}"
                                                                   placeholder="e.g., 120, Negative">
                                                        </td>
                                                        <td>
                                                            <input type="text" 
                                                                   class="form-control form-control-sm" 
                                                                   name="results[{{ $index }}][unit]" 
                                                                   value="{{ $existingResult->unit ?? old("results.{$index}.unit") }}"
                                                                   placeholder="mg/dL">
                                                        </td>
                                                        <td>
                                                            <input type="text" 
                                                                   class="form-control form-control-sm" 
                                                                   name="results[{{ $index }}][reference_range]" 
                                                                   value="{{ $existingResult->reference_range ?? old("results.{$index}.reference_range") }}"
                                                                   placeholder="70-100">
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm" name="results[{{ $index }}][status]">
                                                                <option value="">Select</option>
                                                                <option value="normal" {{ ($existingResult->status ?? old("results.{$index}.status")) == 'normal' ? 'selected' : '' }}>Normal</option>
                                                                <option value="abnormal" {{ ($existingResult->status ?? old("results.{$index}.status")) == 'abnormal' ? 'selected' : '' }}>Abnormal</option>
                                                                <option value="critical" {{ ($existingResult->status ?? old("results.{$index}.status")) == 'critical' ? 'selected' : '' }}>Critical</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-select form-select-sm" name="results[{{ $index }}][result_status]" required>
                                                                <option value="pending" {{ ($existingResult->result_status ?? old("results.{$index}.result_status", 'pending')) == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="ready" {{ ($existingResult->result_status ?? old("results.{$index}.result_status")) == 'ready' ? 'selected' : '' }}>Ready</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" name="results[{{ $index }}][test_name]" value="{{ $item->item_name }}">
                                                            <textarea class="form-control form-control-sm" 
                                                                      name="results[{{ $index }}][notes]" 
                                                                      rows="2" 
                                                                      placeholder="Notes...">{{ $existingResult->notes ?? old("results.{$index}.notes") }}</textarea>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="alert alert-warning mt-3">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Note:</strong> Mark tests as "Ready" when results are complete. Once all tests are marked as "Ready", the patient will be automatically sent back to the doctor.
                                    </div>

                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="{{ route('hospital.lab.index') }}" class="btn btn-secondary">
                                            <i class="bx bx-arrow-back me-1"></i>Cancel
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-save me-1"></i>Save Results
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-warning">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>No Lab Tests Found:</strong> No lab tests have been ordered by the doctor for this visit. Please contact the doctor to create a lab test bill first.
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('hospital.lab.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i>Back to Lab Dashboard
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
