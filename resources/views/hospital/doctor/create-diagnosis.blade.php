@extends('layouts.main')

@section('title', 'Create Diagnosis Explanation')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
            ['label' => 'Doctor', 'url' => route('hospital.doctor.index'), 'icon' => 'bx bx-user-md'],
            ['label' => 'Create Diagnosis', 'url' => '#', 'icon' => 'bx bx-file']
        ]" />
        <h6 class="mb-0 text-uppercase">CREATE DIAGNOSIS EXPLANATION</h6>
        <hr />

        <div class="card">
            <div class="card-header bg-info text-white">
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

                <form method="POST" action="{{ route('hospital.doctor.store-diagnosis', $visit->id) }}">
                    @csrf

                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Note:</strong> Write the diagnosis and explanation for this patient's visit.
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="diagnosis" class="form-label">Diagnosis <span class="text-muted">(Optional)</span></label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" placeholder="Enter diagnosis...">{{ old('diagnosis', $visit->diagnosisExplanation->diagnosis ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="explanation" class="form-label">Explanation <span class="text-muted">(Optional)</span></label>
                            <textarea class="form-control" id="explanation" name="explanation" rows="5" placeholder="Enter explanation of the diagnosis...">{{ old('explanation', $visit->diagnosisExplanation->explanation ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="notes" class="form-label">Notes <span class="text-muted">(Optional)</span></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Enter additional notes...">{{ old('notes', $visit->diagnosisExplanation->notes ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('hospital.doctor.create', $visit->id) }}" class="btn btn-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                        <button type="submit" class="btn btn-info">
                            <i class="bx bx-save me-1"></i>Save Diagnosis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
