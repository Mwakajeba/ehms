@extends('layouts.main')

@section('title', 'Edit Patient')

@push('styles')
<style>
    .form-section {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .form-section-header {
        border-bottom: 2px solid #0d6efd;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <x-breadcrumbs-with-icons :links="[
                ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                ['label' => 'Hospital Management', 'url' => route('hospital.index'), 'icon' => 'bx bx-plus-medical'],
                ['label' => 'Reception', 'url' => route('hospital.reception.index'), 'icon' => 'bx bx-user-plus'],
                ['label' => 'Edit Patient', 'url' => '#', 'icon' => 'bx bx-edit']
            ]" />
            <h6 class="mb-0 text-uppercase">EDIT PATIENT</h6>
            <hr />

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                                <h5 class="mb-0 text-primary">Patient Edit Form</h5>
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

                            <form action="{{ route('hospital.reception.patients.update', $patient->id) }}" method="POST" id="patientForm">
                                @csrf
                                @method('PUT')

                                <!-- Basic Information -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-user me-2"></i> Basic Information
                                        </h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                                       id="first_name" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="last_name" class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                                       id="last_name" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="date_of_birth" class="form-label fw-bold">Date of Birth</label>
                                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') : '') }}"
                                                       onchange="calculateAge()">
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Age will be auto-calculated
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="age" class="form-label fw-bold">Age</label>
                                                <input type="text" class="form-control bg-light" id="age" readonly>
                                                <div class="form-text text-muted">Auto-calculated from date of birth</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="gender" class="form-label fw-bold">Gender</label>
                                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                                    <option value="">Select Gender</option>
                                                    <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                                    <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                                    <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                @error('gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="blood_group" class="form-label fw-bold">Blood Group</label>
                                                <select class="form-select @error('blood_group') is-invalid @enderror" id="blood_group" name="blood_group">
                                                    <option value="">Select Blood Group</option>
                                                    <option value="A+" {{ old('blood_group', $patient->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                                    <option value="A-" {{ old('blood_group', $patient->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                                    <option value="B+" {{ old('blood_group', $patient->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                                    <option value="B-" {{ old('blood_group', $patient->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                                    <option value="AB+" {{ old('blood_group', $patient->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                                    <option value="AB-" {{ old('blood_group', $patient->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                                    <option value="O+" {{ old('blood_group', $patient->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                                    <option value="O-" {{ old('blood_group', $patient->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                                                </select>
                                                @error('blood_group')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Information -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-phone me-2"></i> Contact Information
                                        </h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                       id="phone" name="phone" value="{{ old('phone', $patient->phone) }}" placeholder="255655577803">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label fw-bold">Email</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                       id="email" name="email" value="{{ old('email', $patient->email) }}">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="address" class="form-label fw-bold">Address</label>
                                                <textarea class="form-control @error('address') is-invalid @enderror"
                                                          id="address" name="address" rows="2">{{ old('address', $patient->address) }}</textarea>
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Next of Kin Information -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-user-circle me-2"></i> Next of Kin Information
                                        </h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="next_of_kin_name" class="form-label fw-bold">Next of Kin Name</label>
                                                <input type="text" class="form-control @error('next_of_kin_name') is-invalid @enderror"
                                                       id="next_of_kin_name" name="next_of_kin_name" value="{{ old('next_of_kin_name', $patient->next_of_kin_name) }}">
                                                @error('next_of_kin_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="next_of_kin_phone" class="form-label fw-bold">Next of Kin Phone (Mobile)</label>
                                                <input type="text" class="form-control @error('next_of_kin_phone') is-invalid @enderror"
                                                       id="next_of_kin_phone" name="next_of_kin_phone" value="{{ old('next_of_kin_phone', $patient->next_of_kin_phone) }}">
                                                @error('next_of_kin_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="next_of_kin_relationship" class="form-label fw-bold">Relationship</label>
                                                <input type="text" class="form-control @error('next_of_kin_relationship') is-invalid @enderror"
                                                       id="next_of_kin_relationship" name="next_of_kin_relationship" value="{{ old('next_of_kin_relationship', $patient->next_of_kin_relationship) }}"
                                                       placeholder="e.g., Spouse, Parent, Sibling">
                                                @error('next_of_kin_relationship')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Medical Information -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-heart me-2"></i> Medical Information
                                        </h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="medical_history" class="form-label fw-bold">Medical History</label>
                                                <textarea class="form-control @error('medical_history') is-invalid @enderror"
                                                          id="medical_history" name="medical_history" rows="3">{{ old('medical_history', $patient->medical_history) }}</textarea>
                                                @error('medical_history')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="allergies" class="form-label fw-bold">Allergies</label>
                                                <textarea class="form-control @error('allergies') is-invalid @enderror"
                                                          id="allergies" name="allergies" rows="2" placeholder="List any known allergies">{{ old('allergies', $patient->allergies) }}</textarea>
                                                @error('allergies')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Identification & Insurance -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <h6 class="mb-0">
                                            <i class="bx bx-id-card me-2"></i> Identification & Insurance
                                        </h6>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="id_number" class="form-label fw-bold">ID Number</label>
                                                <input type="text" class="form-control @error('id_number') is-invalid @enderror"
                                                       id="id_number" name="id_number" value="{{ old('id_number', $patient->id_number) }}">
                                                @error('id_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="insurance_type" class="form-label fw-bold">Insurance Type</label>
                                                <select class="form-select @error('insurance_type') is-invalid @enderror" id="insurance_type" name="insurance_type">
                                                    <option value="None" {{ old('insurance_type', $patient->insurance_type ?? 'None') == 'None' ? 'selected' : '' }}>None</option>
                                                    <option value="NHIF" {{ old('insurance_type', $patient->insurance_type) == 'NHIF' ? 'selected' : '' }}>NHIF</option>
                                                    <option value="CHF" {{ old('insurance_type', $patient->insurance_type) == 'CHF' ? 'selected' : '' }}>CHF</option>
                                                    <option value="Jubilee" {{ old('insurance_type', $patient->insurance_type) == 'Jubilee' ? 'selected' : '' }}>Jubilee</option>
                                                    <option value="Strategy" {{ old('insurance_type', $patient->insurance_type) == 'Strategy' ? 'selected' : '' }}>Strategy</option>
                                                </select>
                                                @error('insurance_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="insurance_number" class="form-label fw-bold">Insurance Number</label>
                                                <input type="text" class="form-control @error('insurance_number') is-invalid @enderror"
                                                       id="insurance_number" name="insurance_number" value="{{ old('insurance_number', $patient->insurance_number) }}">
                                                @error('insurance_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Form Actions -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('hospital.reception.patients.show', $patient->id) }}" class="btn btn-secondary">
                                                <i class="bx bx-arrow-back me-1"></i>Cancel
                                            </a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i>Update Patient
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
    function calculateAge() {
        const dobInput = document.getElementById('date_of_birth');
        const ageInput = document.getElementById('age');
        
        if (dobInput.value) {
            const dob = new Date(dobInput.value);
            const today = new Date();
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            
            ageInput.value = age + ' years';
        } else {
            ageInput.value = '';
        }
    }

    // Calculate age on page load if date_of_birth exists
    document.addEventListener('DOMContentLoaded', function() {
        const dobInput = document.getElementById('date_of_birth');
        if (dobInput && dobInput.value) {
            calculateAge();
        }
    });
</script>
@endpush
