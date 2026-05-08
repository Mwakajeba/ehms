@extends('layouts.main')

@section('title', 'Audiology Results')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Audiology', 'url' => route('hospital.audiology.index'), 'icon' => 'bx bx-volume-full'],
                ['label' => 'Enter Results', 'url' => '#', 'icon' => 'bx bx-edit']
            ]" />
            <h6 class="mb-0 text-uppercase">AUDIOLOGY RESULTS ENTRY</h6>
            <hr />

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bx bx-user me-2"></i>Patient Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Patient:</strong> {{ $visit->patient->full_name ?? 'N/A' }}</p>
                            <p><strong>MRN:</strong> {{ $visit->patient->mrn ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Visit #:</strong> {{ $visit->visit_number }}</p>
                            <p><strong>Visit Date:</strong> {{ $visit->visit_date ? $visit->visit_date->format('d M Y, H:i') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-edit me-2"></i>Enter Audiology Results</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hospital.audiology.store', $visit->id) }}" method="POST">
                        @csrf

                        @php
                            $invoiceItems = $audiologyInvoiceItems ?? collect();
                            $rows = max($invoiceItems->count(), 1);
                        @endphp

                        @for($i = 0; $i < $rows; $i++)
                            @php
                                $item = $invoiceItems[$i] ?? null;
                                $serviceId = $item->inventoryItem->id ?? null;
                                $existing = $serviceId && isset($existingResults[$serviceId]) ? $existingResults[$serviceId] : null;
                            @endphp

                            <div class="border rounded p-3 mb-3">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Service (optional)</label>
                                        <input type="hidden" name="results[{{ $i }}][service_id]" value="{{ $serviceId }}">
                                        <input type="text" class="form-control" value="{{ $item->item_name ?? 'Manual Entry' }}" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Test Type <span class="text-danger">*</span></label>
                                        <input type="text"
                                               name="results[{{ $i }}][test_type]"
                                               class="form-control"
                                               value="{{ old("results.$i.test_type", $existing->test_type ?? ($item->item_name ?? '')) }}"
                                               placeholder="e.g., Pure Tone Audiometry (PTA)"
                                               required>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Result Status <span class="text-danger">*</span></label>
                                        @php
                                            $defaultStatus = $existing->result_status ?? 'pending';
                                        @endphp
                                        <select name="results[{{ $i }}][result_status]" class="form-select" required>
                                            <option value="pending" {{ old("results.$i.result_status", $defaultStatus) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="ready" {{ old("results.$i.result_status", $defaultStatus) == 'ready' ? 'selected' : '' }}>Ready</option>
                                        </select>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Findings</label>
                                        <textarea name="results[{{ $i }}][findings]" class="form-control" rows="2"
                                                  placeholder="Key findings...">{{ old("results.$i.findings", $existing->findings ?? '') }}</textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Impression</label>
                                        <textarea name="results[{{ $i }}][impression]" class="form-control" rows="2"
                                                  placeholder="Interpretation / impression...">{{ old("results.$i.impression", $existing->impression ?? '') }}</textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Recommendation</label>
                                        <textarea name="results[{{ $i }}][recommendation]" class="form-control" rows="2"
                                                  placeholder="Recommendations (e.g., hearing aid trial, ENT referral)...">{{ old("results.$i.recommendation", $existing->recommendation ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endfor

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('hospital.audiology.index') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-info">
                                <i class="bx bx-save me-1"></i>Save Results
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

