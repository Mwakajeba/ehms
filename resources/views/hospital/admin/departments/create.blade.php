@extends('layouts.main')

@section('title', 'Create Hospital Department')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Departments', 'url' => route('hospital.admin.departments.index'), 'icon' => 'bx bx-buildings'],
                ['label' => 'Create', 'url' => '#', 'icon' => 'bx bx-plus']
            ]" />
            <h6 class="mb-0 text-uppercase">CREATE HOSPITAL DEPARTMENT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-plus me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Add New Hospital Department</h5>
                            </div>
                            <hr />

                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('hospital.admin.departments.store') }}" method="POST">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label fw-bold">Department Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                   id="name" name="name" value="{{ old('name') }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="code" class="form-label fw-bold">Code <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                                   id="code" name="code" value="{{ old('code') }}" 
                                                   placeholder="e.g., RECEPTION" required>
                                            @error('code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Unique code for the department (will be converted to uppercase)</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="type" class="form-label fw-bold">Department Type <span class="text-danger">*</span></label>
                                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                                <option value="">Select Type</option>
                                                <option value="reception" {{ old('type') == 'reception' ? 'selected' : '' }}>Reception</option>
                                                <option value="cashier" {{ old('type') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                                                <option value="triage" {{ old('type') == 'triage' ? 'selected' : '' }}>Triage</option>
                                                <option value="doctor" {{ old('type') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                                <option value="lab" {{ old('type') == 'lab' ? 'selected' : '' }}>Lab</option>
                                                <option value="ultrasound" {{ old('type') == 'ultrasound' ? 'selected' : '' }}>Ultrasound</option>
                                                <option value="dental" {{ old('type') == 'dental' ? 'selected' : '' }}>Dental</option>
                                                <option value="pharmacy" {{ old('type') == 'pharmacy' ? 'selected' : '' }}>Pharmacy</option>
                                                <option value="rch" {{ old('type') == 'rch' ? 'selected' : '' }}>RCH</option>
                                                <option value="family_planning" {{ old('type') == 'family_planning' ? 'selected' : '' }}>Family Planning</option>
                                                <option value="vaccine" {{ old('type') == 'vaccine' ? 'selected' : '' }}>Vaccine</option>
                                                <option value="injection" {{ old('type') == 'injection' ? 'selected' : '' }}>Injection</option>
                                                <option value="observation" {{ old('type') == 'observation' ? 'selected' : '' }}>Observation</option>
                                            </select>
                                            @error('type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="is_active" class="form-label fw-bold">Status</label>
                                            <div class="form-check form-switch mt-2">
                                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                                       value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Active
                                                </label>
                                            </div>
                                            <div class="form-text">Inactive departments will not appear in visit routing</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-bold">Description</label>
                                            <textarea class="form-control @error('description') is-invalid @enderror"
                                                      id="description" name="description" rows="4" 
                                                      placeholder="Enter department description...">{{ old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.admin.departments.index') }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Create Department
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
