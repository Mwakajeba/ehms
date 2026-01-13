@extends('layouts.main')

@section('title', 'Edit Student')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Students', 'url' => route('school.students.index'), 'icon' => 'bx bx-id-card'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT STUDENT</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Student Record</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.students.update', $student) }}" method="POST" enctype="multipart/form-data" id="studentForm">
                            @csrf
                            @method('PUT')

                            <!-- Academic Information Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-book me-2"></i> Academic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="academic_year_id" class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                                                <select class="form-select @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                                    <option value="">Select Academic Year</option>
                                                    @foreach($academicYears as $year)
                                                        <option value="{{ $year->id }}" {{ old('academic_year_id', $student->academic_year_id) == $year->id ? 'selected' : '' }}>
                                                            {{ $year->year_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('academic_year_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="admission_number" class="form-label fw-bold">Admission Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('admission_number') is-invalid @enderror"
                                                       id="admission_number" name="admission_number" value="{{ old('admission_number', $student->admission_number) }}"
                                                       placeholder="ADM001" required>
                                                @error('admission_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="class_id" class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                                                <select class="form-select @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
                                                    <option value="">Select Class</option>
                                                    @foreach($classes as $class)
                                                        <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                                            {{ $class->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('class_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="stream_id" class="form-label fw-bold">Stream <span class="text-danger">*</span></label>
                                                <select class="form-select @error('stream_id') is-invalid @enderror" id="stream_id" name="stream_id" required>
                                                    <option value="">Select a class first</option>
                                                </select>
                                                @error('stream_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="discount_type" class="form-label fw-bold">Discount Type</label>
                                                <select class="form-select @error('discount_type') is-invalid @enderror" id="discount_type" name="discount_type">
                                                    <option value="">No Discount</option>
                                                    <option value="fixed" {{ old('discount_type', $student->discount_type) == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                    <option value="percentage" {{ old('discount_type', $student->discount_type) == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                </select>
                                                @error('discount_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Select the type of discount to apply to this student
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="discount_value" class="form-label fw-bold">Discount Value</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control @error('discount_value') is-invalid @enderror"
                                                           id="discount_value" name="discount_value" value="{{ old('discount_value', $student->discount_value) }}"
                                                           min="0" step="0.01" placeholder="0.00">
                                                    <span class="input-group-text" id="discount-unit">Amount</span>
                                                </div>
                                                @error('discount_value')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Enter the discount amount or percentage
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information Section -->
                            <div class="card border-info mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-user me-2"></i> Personal Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                                       id="first_name" name="first_name" value="{{ old('first_name', $student->first_name) }}" required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="last_name" class="form-label fw-bold">Last Name</label>
                                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                                       id="last_name" name="last_name" value="{{ old('last_name', $student->last_name) }}">
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
                                                       id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $student->date_of_birth?->format('Y-m-d')) }}">
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="gender" class="form-label fw-bold">Gender</label>
                                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                                    <option value="">Select Gender</option>
                                                    <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                                    <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                                    <option value="other" {{ old('gender', $student->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                @error('gender')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="admission_date" class="form-label fw-bold">Admission Date</label>
                                                <input type="date" class="form-control @error('admission_date') is-invalid @enderror"
                                                       id="admission_date" name="admission_date" value="{{ old('admission_date', $student->admission_date?->format('Y-m-d')) }}">
                                                @error('admission_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="address" class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                                <textarea class="form-control @error('address') is-invalid @enderror"
                                                          id="address" name="address" rows="2" required>{{ old('address', $student->address) }}</textarea>
                                                @error('address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($errors->has('name'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bx bx-error-circle me-1"></i>
                                    <strong>Error:</strong> {{ $errors->first('name') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <!-- Boarding & Transport Section -->
                            <div class="card border-warning mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-bus me-2"></i> Boarding & Transport
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="boarding_type" class="form-label fw-bold">Boarding Type</label>
                                                <select class="form-select @error('boarding_type') is-invalid @enderror" id="boarding_type" name="boarding_type">
                                                    <option value="">Select Type</option>
                                                    <option value="day" {{ old('boarding_type', $student->boarding_type) == 'day' ? 'selected' : '' }}>Day Scholar</option>
                                                    <option value="boarding" {{ old('boarding_type', $student->boarding_type) == 'boarding' ? 'selected' : '' }}>Boarding</option>
                                                </select>
                                                @error('boarding_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Transport Section (only show if Day Scholar) -->
                                    <div id="transportSection" style="{{ ($student->boarding_type ?? 'day') === 'day' ? '' : 'display: none;' }}">
                                        <hr class="my-3">
                                        <h6 class="text-muted mb-3">
                                            <i class="bx bx-bus me-1"></i> Transport Details
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="has_transport" class="form-label fw-bold">Has Transport?</label>
                                                    <select class="form-select @error('has_transport') is-invalid @enderror" id="has_transport" name="has_transport">
                                                        <option value="">Select Option</option>
                                                        <option value="yes" {{ old('has_transport', $student->has_transport) == 'yes' ? 'selected' : '' }}>Yes</option>
                                                        <option value="no" {{ old('has_transport', $student->has_transport) == 'no' ? 'selected' : '' }}>No</option>
                                                    </select>
                                                    @error('has_transport')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6" id="busStopSection" style="{{ ($student->has_transport ?? 'no') === 'yes' ? '' : 'display: none;' }}">
                                                <div class="mb-3">
                                                    <label for="bus_stop_id" class="form-label fw-bold">Bus Stop</label>
                                                    <select class="form-select @error('bus_stop_id') is-invalid @enderror" id="bus_stop_id" name="bus_stop_id">
                                                        <option value="">Select Bus Stop</option>
                                                    </select>
                                                    @error('bus_stop_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Current Parents/Guardians Section -->
                            <div class="card border-success mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-user-check me-2"></i> Current Parents/Guardians
                                    </h6>
                                </div>
                                <div class="card-body">
                                    @if($student->guardians->isNotEmpty())
                                        <div class="current-guardians">
                                            @foreach($student->guardians as $guardian)
                                                <div class="guardian-edit-card mb-3">
                                                    <div class="guardian-header">
                                                        <div class="guardian-avatar">
                                                            <i class="bx bx-user"></i>
                                                        </div>
                                                        <div class="guardian-basic">
                                                            <h5>{{ $guardian->name }}</h5>
                                                            <p class="relationship">{{ ucfirst($student->guardians->find($guardian->id)->pivot->relationship) }}</p>
                                                        </div>
                                                        <div class="guardian-actions">
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeGuardian({{ $guardian->id }}, '{{ $guardian->name }}')">
                                                                <i class="bx bx-trash me-1"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="guardian-details">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Name</label>
                                                                    <input type="text" class="form-control guardian-name" data-guardian-id="{{ $guardian->id }}"
                                                                           value="{{ $guardian->name }}" placeholder="Guardian name">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Relationship</label>
                                                                    <select class="form-select guardian-relationship" data-guardian-id="{{ $guardian->id }}">
                                                                        <option value="father" {{ $student->guardians->find($guardian->id)->pivot->relationship == 'father' ? 'selected' : '' }}>Father</option>
                                                                        <option value="mother" {{ $student->guardians->find($guardian->id)->pivot->relationship == 'mother' ? 'selected' : '' }}>Mother</option>
                                                                        <option value="guardian" {{ $student->guardians->find($guardian->id)->pivot->relationship == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Phone</label>
                                                                    <input type="tel" class="form-control guardian-phone" data-guardian-id="{{ $guardian->id }}"
                                                                           value="{{ $guardian->phone }}" placeholder="Phone number">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Email</label>
                                                                    <input type="email" class="form-control guardian-email" data-guardian-id="{{ $guardian->id }}"
                                                                           value="{{ $guardian->email }}" placeholder="Email address">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Address</label>
                                                                    <textarea class="form-control guardian-address" data-guardian-id="{{ $guardian->id }}"
                                                                              rows="2" placeholder="Address">{{ $guardian->address }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Alternative Phone</label>
                                                                    <input type="tel" class="form-control guardian-alt-phone" data-guardian-id="{{ $guardian->id }}"
                                                                           value="{{ $guardian->alt_phone }}" placeholder="Alternative phone">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="guardian-save-actions">
                                                            <button type="button" class="btn btn-sm btn-success" onclick="updateGuardian({{ $guardian->id }})">
                                                                <i class="bx bx-save me-1"></i> Update Guardian
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <hr>
                                        <div class="add-new-guardian">
                                            <button type="button" class="btn btn-outline-primary" onclick="showAddGuardianForm()">
                                                <i class="bx bx-plus me-1"></i> Add New Guardian
                                            </button>
                                        </div>
                                        <div id="addGuardianForm" style="display: none;" class="mt-3">
                                            <div class="card border-primary">
                                                <div class="card-header">
                                                    <h6 class="mb-0">Add New Guardian</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="new_guardian_name" placeholder="Guardian name">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Relationship <span class="text-danger">*</span></label>
                                                                <select class="form-select" id="new_guardian_relationship">
                                                                    <option value="">Select Relationship</option>
                                                                    <option value="father">Father</option>
                                                                    <option value="mother">Mother</option>
                                                                    <option value="guardian">Guardian</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Phone <span class="text-danger">*</span></label>
                                                                <input type="tel" class="form-control" id="new_guardian_phone" placeholder="Phone number">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Email</label>
                                                                <input type="email" class="form-control" id="new_guardian_email" placeholder="Email address">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" id="new_guardian_address" rows="2" placeholder="Address"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Alternative Phone</label>
                                                                <input type="tel" class="form-control" id="new_guardian_alt_phone" placeholder="Alternative phone">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-success" onclick="addNewGuardian()">
                                                            <i class="bx bx-plus me-1"></i> Add Guardian
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary" onclick="hideAddGuardianForm()">
                                                            <i class="bx bx-x me-1"></i> Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="no-guardians text-center py-4">
                                            <div class="no-guardians-icon mb-3">
                                                <i class="bx bx-user-x" style="font-size: 3rem; color: #ccc;"></i>
                                            </div>
                                            <h5 class="text-muted">No Guardians Added</h5>
                                            <p class="text-muted">Add guardians to manage parent/guardian information for this student.</p>
                                            <button type="button" class="btn btn-primary" onclick="showAddGuardianForm()">
                                                <i class="bx bx-plus me-1"></i> Add First Guardian
                                            </button>
                                        </div>
                                        <div id="addGuardianForm" style="display: none;" class="mt-3">
                                            <div class="card border-primary">
                                                <div class="card-header">
                                                    <h6 class="mb-0">Add New Guardian</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" id="new_guardian_name" placeholder="Guardian name">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Relationship <span class="text-danger">*</span></label>
                                                                <select class="form-select" id="new_guardian_relationship">
                                                                    <option value="">Select Relationship</option>
                                                                    <option value="father">Father</option>
                                                                    <option value="mother">Mother</option>
                                                                    <option value="guardian">Guardian</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Phone <span class="text-danger">*</span></label>
                                                                <input type="tel" class="form-control" id="new_guardian_phone" placeholder="Phone number">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Email</label>
                                                                <input type="email" class="form-control" id="new_guardian_email" placeholder="Email address">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" id="new_guardian_address" rows="2" placeholder="Address"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label fw-bold">Alternative Phone</label>
                                                                <input type="tel" class="form-control" id="new_guardian_alt_phone" placeholder="Alternative phone">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-success" onclick="addNewGuardian()">
                                                            <i class="bx bx-plus me-1"></i> Add Guardian
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary" onclick="hideAddGuardianForm()">
                                                            <i class="bx bx-x me-1"></i> Cancel
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Photo Upload Section -->
                            <div class="card border-secondary mb-4">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="bx bx-camera me-2"></i> Photo Upload
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="passport_photo" class="form-label fw-bold">Passport-size Photo</label>
                                                <input type="file" class="form-control @error('passport_photo') is-invalid @enderror"
                                                       id="passport_photo" name="passport_photo" accept="image/*">
                                                @error('passport_photo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Upload a new passport-size photo (JPG, PNG, max 2MB). Leave empty to keep current photo.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            @if($student->passport_photo)
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold">Current Photo</label>
                                                    <div>
                                                        <img src="{{ asset('storage/' . $student->passport_photo) }}" alt="Current Photo" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="card border-secondary">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <a href="{{ route('school.students.index') }}" class="btn btn-outline-secondary btn-lg">
                                            <i class="bx bx-arrow-back me-2"></i> Back to Students
                                        </a>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                                                <i class="bx bx-refresh me-1"></i> Reset Changes
                                            </button>
                                            <button type="submit" class="btn btn-success btn-lg">
                                                <i class="bx bx-save me-2"></i> Update Student
                                            </button>
                                        </div>
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

@push('styles')
<style>
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .form-text {
        font-size: 0.875rem;
    }

    h6 {
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
    }

    /* Enhanced Card Styling */
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.75rem;
        transition: box-shadow 0.15s ease-in-out;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .card-header {
        border-radius: 0.75rem 0.75rem 0 0 !important;
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        font-weight: 600;
        background-color: #f8f9fa !important;
        color: #495057 !important;
    }

    /* Section-specific header backgrounds */
    .border-primary .card-header { background-color: #f8f9fa !important; }
    .border-info .card-header { background-color: #f8f9fa !important; }
    .border-warning .card-header { background-color: #f8f9fa !important; }
    .border-success .card-header { background-color: #f8f9fa !important; }
    .border-secondary .card-header { background-color: #f8f9fa !important; }

    .card-body {
        padding: 1.5rem;
    }

    /* Form Field Enhancements */
    .form-label {
        margin-bottom: 0.5rem;
        color: #495057;
        font-weight: 500;
    }

    .form-control, .form-select {
        border-radius: 0.5rem;
        border: 1px solid #ced4da;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    /* Input Group Styling */
    .input-group-text {
        border-radius: 0 0.5rem 0.5rem 0;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
    }

    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.15s ease-in-out;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }

    /* Alert Styling */
    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    /* Transport Section Animation */
    #transportSection {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }

        .d-flex.gap-2 {
            justify-content: center;
        }
    }

    /* Icon Styling */
    .bx {
        font-size: 1.1em;
    }

    /* Required Field Indicator */
    .text-danger {
        font-weight: bold;
    }

    /* Form Text Enhancement */
    .form-text {
        font-style: italic;
        margin-top: 0.25rem;
    }

    /* Card Border Colors - Subtle */
    .border-primary { border-color: #e3f2fd !important; }
    .border-info { border-color: #e0f2fe !important; }
    .border-warning { border-color: #fff3cd !important; }
    .border-success { border-color: #d1edff !important; }
    .border-secondary { border-color: #f8f9fa !important; }

    /* Image thumbnail styling */
    .img-thumbnail {
        border-radius: 0.5rem;
        border: 2px solid #dee2e6;
        transition: border-color 0.15s ease-in-out;
    }

    .img-thumbnail:hover {
        border-color: #86b7fe;
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Enhanced student edit form loaded');

        // Initialize form validation
        initializeFormValidation();

        // Auto-capitalize names
        $('#first_name, #last_name, #guardian_name').on('input', function() {
            let value = $(this).val();
            if (value.length > 0) {
                $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
            }
        });

        // Handle boarding type change
        $('#boarding_type').on('change', function() {
            const boardingType = $(this).val();
            console.log('Boarding type changed to:', boardingType);
            toggleTransportSection(boardingType);
        });

        // Handle transport option change
        $('#has_transport').on('change', function() {
            const hasTransport = $(this).val();
            console.log('Has transport changed to:', hasTransport);
            toggleBusStopSection(hasTransport);
        });

        // Handle discount type change
        $('#discount_type').on('change', function() {
            const discountType = $(this).val();
            console.log('Discount type changed to:', discountType);
            updateDiscountUnit(discountType);
        });

        // Initialize state based on current values
        const initialBoardingType = $('#boarding_type').val();
        const initialTransport = $('#has_transport').val();
        const initialClassId = $('#class_id').val();
        const initialDiscountType = $('#discount_type').val();
        console.log('Initial boarding type:', initialBoardingType);
        console.log('Initial transport:', initialTransport);
        console.log('Initial class ID:', initialClassId);
        console.log('Initial discount type:', initialDiscountType);

        if (initialBoardingType) {
            toggleTransportSection(initialBoardingType);
        }
        if (initialTransport) {
            toggleBusStopSection(initialTransport);
        }
        if (initialClassId) {
            // Load streams for the initially selected class
            loadStreamsForClass(initialClassId);
        }
        if (initialDiscountType) {
            updateDiscountUnit(initialDiscountType);
        }

        // Load bus stops if bus stop section is visible
        if ($('#busStopSection').is(':visible')) {
            loadBusStops();
        }

        // Handle class change to load streams dynamically
        $('#class_id').on('change', function() {
            const classId = $(this).val();
            const streamSelect = $('#stream_id');

            if (classId) {
                // Enable stream select and load streams for this class
                streamSelect.prop('disabled', false);

                // Clear current selection
                streamSelect.val('').trigger('change');

                // Load streams for the selected class
                loadStreamsForClass(classId);
            } else {
                // Disable stream select when no class is selected
                streamSelect.prop('disabled', true);
                streamSelect.val('').trigger('change');
            }
        });

        // File upload preview
        $('#passport_photo').on('change', function() {
            const file = this.files[0];
            if (file) {
                const fileSize = file.size / 1024 / 1024; // MB
                if (fileSize > 2) {
                    showToast('File size must be less than 2MB', 'error');
                    $(this).val('');
                    return;
                }

                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!validTypes.includes(file.type)) {
                    showToast('Please select a valid image file (JPG, PNG)', 'error');
                    $(this).val('');
                    return;
                }

                showToast('Photo selected successfully', 'success');
            }
        });
    });

    // Initialize Select2 elements
    function initializeSelect2Elements() {
        // Initialize Select2 for class selection with search
        $('#class_id').select2({
            placeholder: 'Search and select a class...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });

        // Initialize Select2 for stream selection (will be populated when class is selected)
        $('#stream_id').select2({
            placeholder: 'Select a stream...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });

        // Initialize Select2 for academic year selection
        $('#academic_year_id').select2({
            placeholder: 'Select academic year...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });

        // Initialize Select2 for bus stop selection (will be populated when transport section is shown)
        $('#bus_stop_id').select2({
            placeholder: 'Select a bus stop...',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });

        // Initialize other Select2 elements
        $('#boarding_type, #has_transport, #gender, #discount_type, #guardian_relationship').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%',
            theme: 'bootstrap-5',
            minimumInputLength: 0
        });
    }

    // Function to toggle transport section
    function toggleTransportSection(boardingType) {
        if (boardingType === 'day') {
            $('#transportSection').slideDown(300);
        } else {
            $('#transportSection').slideUp(300, function() {
                $('#has_transport').val('').trigger('change');
                $('#busStopSection').slideUp(300);
                $('#bus_stop_id').val('').trigger('change');
            });
        }
    }

    // Function to toggle bus stop section
    function toggleBusStopSection(hasTransport) {
        if (hasTransport === 'yes') {
            $('#busStopSection').slideDown(300, function() {
                // Load bus stops when section becomes visible
                loadBusStops();
            });
        } else {
            $('#busStopSection').slideUp(300, function() {
                $('#bus_stop_id').val('').trigger('change');
            });
        }
    }

    // Function to load bus stops via AJAX
    function loadBusStops() {
        const busStopSelect = $('#bus_stop_id');
        const currentValue = '{{ $student->bus_stop_id ?? '' }}'; // Get current bus stop ID from PHP

        // Clear any existing options and show loading
        busStopSelect.empty();
        busStopSelect.append('<option value="">Loading bus stops...</option>');
        busStopSelect.prop('disabled', true);

        // Make AJAX call to get bus stops
        $.ajax({
            url: '{{ route("school.api.students.bus-stops") }}',
            method: 'GET',
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Bus stops loaded:', response);

                // Clear loading option
                busStopSelect.empty();

                if (response.bus_stops && response.bus_stops.length > 0) {
                    // Add default option
                    busStopSelect.append('<option value="">Select Bus Stop</option>');

                    // Add bus stop options
                    response.bus_stops.forEach(function(busStop) {
                        const selected = (busStop.id == currentValue) ? ' selected' : '';
                        const optionText = busStop.stop_name + ' (' + busStop.stop_code + ') - ' + busStop.fare;
                        busStopSelect.append(`<option value="${busStop.id}"${selected}>${optionText}</option>`);
                    });

                    busStopSelect.prop('disabled', false);
                    showToast(`Loaded ${response.bus_stops.length} bus stop(s)`, 'success');
                } else {
                    busStopSelect.append('<option value="">No bus stops available</option>');
                    busStopSelect.prop('disabled', true);
                    showToast('No active bus stops found', 'warning');
                }

                // Re-initialize Select2 after populating options
                busStopSelect.select2({
                    placeholder: 'Select a bus stop...',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    minimumInputLength: 0
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading bus stops:', error);
                busStopSelect.empty();
                busStopSelect.append('<option value="">Error loading bus stops</option>');
                busStopSelect.prop('disabled', true);
                showToast('Error loading bus stops. Please try again.', 'error');
            }
        });
    }

    // Function to load streams for a class
    function loadStreamsForClass(classId) {
        const streamSelect = $('#stream_id');
        // Get current stream ID - it's already encoded in the view if needed, but we'll use the raw ID
        // The AJAX returns encoded IDs, so we need to encode the student's stream_id for comparison
        const currentStreamId = '{{ $student->stream_id ?? '' }}';
        @if($student->stream_id)
        const currentStreamIdEncoded = '{{ \Vinkla\Hashids\Facades\Hashids::encode($student->stream_id) }}';
        @else
        const currentStreamIdEncoded = '';
        @endif

        // Show loading state
        streamSelect.html('<option value="">Loading streams...</option>');
        streamSelect.prop('disabled', true);

        // Make AJAX call to get streams for this class
        $.ajax({
            url: '{{ route("school.api.students.streams-by-class") }}',
            method: 'GET',
            data: { class_id: classId },
            xhrFields: {
                withCredentials: true
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                let options = '<option value="">Select Stream</option>';

                if (response.streams && response.streams.length > 0) {
                    response.streams.forEach(function(stream) {
                        // Compare encoded IDs since AJAX returns encoded stream IDs
                        const selected = (stream.id == currentStreamIdEncoded) ? ' selected' : '';
                        options += `<option value="${stream.id}"${selected}>${stream.name}</option>`;
                    });
                    streamSelect.prop('disabled', false);
                    // Only show toast if streams were loaded (not on initial page load)
                    if (classId !== '{{ $student->class_id ?? '' }}') {
                    showToast(`Loaded ${response.streams.length} stream(s) for this class`, 'success');
                    }
                } else {
                    options = '<option value="">No streams available for this class</option>';
                    streamSelect.prop('disabled', true);
                    if (classId !== '{{ $student->class_id ?? '' }}') {
                    showToast('No streams found for this class', 'warning');
                    }
                }

                streamSelect.html(options);

                // Re-initialize Select2 after populating options
                streamSelect.select2({
                    placeholder: 'Select a stream...',
                    allowClear: true,
                    width: '100%',
                    theme: 'bootstrap-5',
                    minimumInputLength: 0
                });
            },
            error: function(xhr, status, error) {
                console.error('Error loading streams:', error);
                streamSelect.html('<option value="">Error loading streams</option>');
                streamSelect.prop('disabled', true);
                showToast('Error loading streams. Please try again.', 'error');
            }
        });
    }    // Form validation initialization
    function initializeFormValidation() {
        $('#studentForm').on('submit', function(e) {
            let isValid = true;
            const requiredFields = ['academic_year_id', 'class_id', 'stream_id', 'first_name', 'address'];

            requiredFields.forEach(field => {
                const element = $('#' + field);
                if (!element.val()) {
                    element.addClass('is-invalid');
                    isValid = false;
                } else {
                    element.removeClass('is-invalid');
                }
            });

            if (!isValid) {
                e.preventDefault();
                showToast('Please fill in all required fields', 'error');
                // Scroll to first error
                $('html, body').animate({
                    scrollTop: $('.is-invalid').first().offset().top - 100
                }, 500);
            }
        });
    }

    // Reset form function
    function resetForm() {
        if (confirm('Are you sure you want to reset all changes? This will reload the original student data.')) {
            // Reload the page to reset all changes
            window.location.reload();
        }
    }

    // Function to update discount unit based on type
    function updateDiscountUnit(discountType) {
        const discountUnit = $('#discount-unit');
        const discountValue = $('#discount_value');

        if (discountType === 'percentage') {
            discountUnit.text('%');
            discountValue.attr('max', '100');
            discountValue.attr('placeholder', '0.00');
        } else if (discountType === 'fixed') {
            discountUnit.text('Amount');
            discountValue.removeAttr('max');
            discountValue.attr('placeholder', '0.00');
        } else {
            discountUnit.text('Amount');
            discountValue.removeAttr('max');
            discountValue.attr('placeholder', '0.00');
        }
    }

    // Guardian management functions
    function updateGuardian(guardianId) {
        const name = $(`.guardian-name[data-guardian-id="${guardianId}"]`).val();
        const relationship = $(`.guardian-relationship[data-guardian-id="${guardianId}"]`).val();
        const phone = $(`.guardian-phone[data-guardian-id="${guardianId}"]`).val();
        const email = $(`.guardian-email[data-guardian-id="${guardianId}"]`).val();
        const address = $(`.guardian-address[data-guardian-id="${guardianId}"]`).val();
        const altPhone = $(`.guardian-alt-phone[data-guardian-id="${guardianId}"]`).val();

        if (!name || !relationship || !phone || !address) {
            showToast('Please fill in all required fields for the guardian', 'error');
            return;
        }

        // Make AJAX call to update guardian
        $.ajax({
            url: `/school/students/{{ $student->id }}/guardians/${guardianId}`,
            method: 'PUT',
            data: {
                name: name,
                relationship: relationship,
                phone: phone,
                email: email,
                address: address,
                alt_phone: altPhone,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('Guardian updated successfully', 'success');
            },
            error: function(xhr, status, error) {
                console.error('Error updating guardian:', error);
                showToast('Error updating guardian. Please try again.', 'error');
            }
        });
    }

    function removeGuardian(guardianId, guardianName) {
        if (!confirm(`Are you sure you want to remove ${guardianName} as a guardian?`)) {
            return;
        }

        // Make AJAX call to remove guardian
        $.ajax({
            url: `/school/students/{{ $student->id }}/guardians/${guardianId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('Guardian removed successfully', 'success');
                // Reload the page to refresh the guardian list
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr, status, error) {
                console.error('Error removing guardian:', error);
                showToast('Error removing guardian. Please try again.', 'error');
            }
        });
    }

    function showAddGuardianForm() {
        $('#addGuardianForm').slideDown(300);
        $('#addGuardianForm')[0].scrollIntoView({ behavior: 'smooth' });
    }

    function hideAddGuardianForm() {
        $('#addGuardianForm').slideUp(300);
        // Clear form
        $('#new_guardian_name, #new_guardian_relationship, #new_guardian_phone, #new_guardian_email, #new_guardian_address, #new_guardian_alt_phone').val('');
    }

    function addNewGuardian() {
        const name = $('#new_guardian_name').val();
        const relationship = $('#new_guardian_relationship').val();
        const phone = $('#new_guardian_phone').val();
        const email = $('#new_guardian_email').val();
        const address = $('#new_guardian_address').val();
        const altPhone = $('#new_guardian_alt_phone').val();

        if (!name || !relationship || !phone || !address) {
            showToast('Please fill in all required fields', 'error');
            return;
        }

        // Make AJAX call to add new guardian
        $.ajax({
            url: `/school/students/{{ $student->id }}/guardians`,
            method: 'POST',
            data: {
                name: name,
                relationship: relationship,
                phone: phone,
                email: email,
                address: address,
                alt_phone: altPhone,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showToast('Guardian added successfully', 'success');
                hideAddGuardianForm();
                // Reload the page to refresh the guardian list
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr, status, error) {
                console.error('Error adding guardian:', error);
                showToast('Error adding guardian. Please try again.', 'error');
            }
        });
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        const toastColors = {
            success: '#198754',
            error: '#dc3545',
            warning: '#ffc107',
            info: '#0dcaf0'
        };

        // Create toast element
        const toast = $(`
            <div class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true"
                 style="background-color: ${toastColors[type]}; position: fixed; top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        // Add to body and show
        $('body').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        // Remove after shown
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
</script>
@endpush