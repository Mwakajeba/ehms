@extends('layouts.main')

@section('title', 'Edit Student')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'College Management', 'url' => route('college.index'), 'icon' => 'bx bx-building'],
            ['label' => 'Students', 'url' => route('college.students.index'), 'icon' => 'bx bx-user'],
            ['label' => 'Edit', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT STUDENT</h6>
        <hr />

        <div class="row">
            <!-- Information Sidebar -->
            <div class="col-12 col-lg-4 order-2 order-lg-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i> Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="bx bx-bulb me-1"></i> Tips for Editing Students:</h6>
                            <ul class="mb-0 small">
                                <li>Student number can be modified if needed</li>
                                <li>Email must remain unique across all students</li>
                                <li>Phone number should include country code</li>
                                <li>Emergency contact is important for safety</li>
                                <li>Previous education helps track academic progress</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="bx bx-error me-1"></i> Required Fields:</h6>
                            <ul class="mb-0 small">
                                <li>Student Number</li>
                                <li>Program</li>
                                <li>First Name & Last Name</li>
                                <li>Gender</li>
                                <li>Enrollment Year</li>
                                <li>Status</li>
                                <li>Admission Level</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8 order-1 order-lg-1">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Student: {{ $student->first_name }} {{ $student->last_name }}</h5>
                        </div>
                        <hr />

                        <form action="{{ route('college.students.update', \Vinkla\Hashids\Facades\Hashids::encode($student->id)) }}" method="POST" enctype="multipart/form-data" id="studentForm">
                            @csrf
                            @method('PUT')

                            <!-- Academic Information Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-book me-2 text-primary"></i> Academic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Student Number <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('student_number') is-invalid @enderror"
                                                       id="student_number" name="student_number"
                                                       value="{{ old('student_number', $student->student_number) }}" required>
                                                @error('student_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Auto-generated when names are entered
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Program <span class="text-danger">*</span></label>
                                                <select class="form-control @error('program_id') is-invalid @enderror"
                                                        id="program_id" name="program_id" required>
                                                    <option value="">Select Program</option>
                                                    @foreach($programs as $program)
                                                        <option value="{{ $program->id }}" {{ old('program_id', $student->program_id) == $program->id ? 'selected' : '' }}>
                                                            {{ $program->name }} ({{ $program->department->name ?? 'N/A' }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('program_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Enrollment Year <span class="text-danger">*</span></label>
                                                <select class="form-control @error('enrollment_year') is-invalid @enderror"
                                                        id="enrollment_year" name="enrollment_year" required>
                                                    <option value="">Select Enrollment Year</option>
                                                    @php
                                                        $academicYears = \App\Models\School\AcademicYear::forCompany(Auth::user()->company_id)
                                                            ->orderBy('start_date', 'desc')
                                                            ->get();
                                                    @endphp
                                                    @foreach($academicYears as $year)
                                                        @php
                                                            // Extract the starting year as integer from year_name (e.g., "2024-2025" -> 2024)
                                                            $startYear = intval(explode('-', $year->year_name)[0]);
                                                        @endphp
                                                        <option value="{{ $startYear }}"
                                                                {{ old('enrollment_year', $student->enrollment_year) == $startYear ? 'selected' : '' }}>
                                                            {{ $year->year_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('enrollment_year')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @if(isset($activeAcademicYear))
                                                    <div class="form-text text-muted">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        Active academic year: <strong>{{ $activeAcademicYear->year_name }}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Expected Graduation Year</label>
                                                <input type="number" class="form-control @error('graduation_year') is-invalid @enderror"
                                                       id="graduation_year" name="graduation_year"
                                                       value="{{ old('graduation_year', $student->graduation_year) }}"
                                                       min="2000" max="{{ date('Y') + 10 }}">
                                                @error('graduation_year')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Admission Date</label>
                                                <input type="date" class="form-control @error('admission_date') is-invalid @enderror"
                                                       id="admission_date" name="admission_date"
                                                       value="{{ old('admission_date', $student->admission_date ? $student->admission_date->format('Y-m-d') : date('Y-m-d')) }}">
                                                @error('admission_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                                <select class="form-control @error('status') is-invalid @enderror"
                                                        id="status" name="status" required>
                                                    <option value="">Select Status</option>
                                                    <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                    <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Graduated</option>
                                                    <option value="suspended" {{ old('status', $student->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                                    <option value="transferred" {{ old('status', $student->status) == 'transferred' ? 'selected' : '' }}>Transferred</option>
                                                </select>
                                                @error('status')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Admission Level <span class="text-danger">*</span></label>
                                                <select class="form-control @error('admission_level') is-invalid @enderror"
                                                        id="admission_level" name="admission_level" required>
                                                    <option value="">Select Admission Level</option>
                                                    <option value="1" {{ old('admission_level', $student->admission_level) == '1' ? 'selected' : '' }}>First Year</option>
                                                    <option value="2" {{ old('admission_level', $student->admission_level) == '2' ? 'selected' : '' }}>Second Year</option>
                                                    <option value="3" {{ old('admission_level', $student->admission_level) == '3' ? 'selected' : '' }}>Third Year</option>
                                                    <option value="4" {{ old('admission_level', $student->admission_level) == '4' ? 'selected' : '' }}>Fourth Year</option>
                                                </select>
                                                @error('admission_level')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Select Courses</label>
                                                <select class="form-control select2-multiple @error('courses') is-invalid @enderror"
                                                        id="courses" name="courses[]" multiple>
                                                    @foreach($courses as $course)
                                                        <option value="{{ $course->id }}" {{ in_array($course->id, old('courses', $enrolledCourseIds)) ? 'selected' : '' }}>
                                                            {{ $course->name }} ({{ $course->code }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('courses')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Select multiple courses the student is enrolled in
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information Section -->
                            <div class="card border-info mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-user me-2 text-info"></i> Personal Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                                       id="first_name" name="first_name"
                                                       value="{{ old('first_name', $student->first_name) }}" required>
                                                @error('first_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                                       id="last_name" name="last_name"
                                                       value="{{ old('last_name', $student->last_name) }}" required>
                                                @error('last_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Email</label>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                                       id="email" name="email"
                                                       value="{{ old('email', $student->email) }}">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Phone</label>
                                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                                       id="phone" name="phone"
                                                       value="{{ old('phone', $student->phone) }}">
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Date of Birth</label>
                                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                                       id="date_of_birth" name="date_of_birth"
                                                       value="{{ old('date_of_birth', $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}">
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                                                <select class="form-control @error('gender') is-invalid @enderror"
                                                        id="gender" name="gender" required>
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
                                                <label class="form-label fw-bold">Nationality</label>
                                                <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                                                       id="nationality" name="nationality"
                                                       value="{{ old('nationality', $student->nationality) }}"
                                                       placeholder="e.g., Kenyan, Ugandan, Tanzanian">
                                                @error('nationality')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">ID/Passport Number</label>
                                                <input type="text" class="form-control @error('id_number') is-invalid @enderror"
                                                       id="id_number" name="id_number"
                                                       value="{{ old('id_number', $student->id_number) }}"
                                                       placeholder="National ID or Passport Number">
                                                @error('id_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Address Information Section -->
                            <div class="card border-success mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-map me-2 text-success"></i> Address Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Permanent Address</label>
                                                <textarea class="form-control @error('permanent_address') is-invalid @enderror"
                                                          id="permanent_address" name="permanent_address"
                                                          rows="3">{{ old('permanent_address', $student->permanent_address) }}</textarea>
                                                @error('permanent_address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Current/Mailing Address</label>
                                                <textarea class="form-control @error('current_address') is-invalid @enderror"
                                                          id="current_address" name="current_address"
                                                          rows="3">{{ old('current_address', $student->current_address) }}</textarea>
                                                @error('current_address')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Leave blank if same as permanent address
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact Section -->
                            <div class="card border-warning mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-phone me-2 text-warning"></i> Emergency Contact
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Emergency Contact Name</label>
                                                <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                                       id="emergency_contact_name" name="emergency_contact_name"
                                                       value="{{ old('emergency_contact_name', $student->emergency_contact_name) }}"
                                                       placeholder="Full name of emergency contact">
                                                @error('emergency_contact_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Emergency Contact Phone</label>
                                                <input type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                                                       id="emergency_contact_phone" name="emergency_contact_phone"
                                                       value="{{ old('emergency_contact_phone', $student->emergency_contact_phone) }}"
                                                       placeholder="Emergency contact phone number">
                                                @error('emergency_contact_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Relationship</label>
                                                <select class="form-control @error('emergency_contact_relationship') is-invalid @enderror"
                                                        id="emergency_contact_relationship" name="emergency_contact_relationship">
                                                    <option value="">Select Relationship</option>
                                                    <option value="parent" {{ old('emergency_contact_relationship', $student->emergency_contact_relationship) == 'parent' ? 'selected' : '' }}>Parent</option>
                                                    <option value="guardian" {{ old('emergency_contact_relationship', $student->emergency_contact_relationship) == 'guardian' ? 'selected' : '' }}>Guardian</option>
                                                    <option value="sibling" {{ old('emergency_contact_relationship', $student->emergency_contact_relationship) == 'sibling' ? 'selected' : '' }}>Sibling</option>
                                                    <option value="spouse" {{ old('emergency_contact_relationship', $student->emergency_contact_relationship) == 'spouse' ? 'selected' : '' }}>Spouse</option>
                                                    <option value="relative" {{ old('emergency_contact_relationship', $student->emergency_contact_relationship) == 'relative' ? 'selected' : '' }}>Other Relative</option>
                                                    <option value="friend" {{ old('emergency_contact_relationship', $student->emergency_contact_relationship) == 'friend' ? 'selected' : '' }}>Friend</option>
                                                    <option value="other" {{ old('emergency_contact_relationship', $student->emergency_contact_relationship) == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                                @error('emergency_contact_relationship')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Previous Education Section -->
                            <div class="card border-secondary mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-graduation me-2 text-secondary"></i> Previous Education
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Previous School/College</label>
                                                <input type="text" class="form-control @error('previous_school') is-invalid @enderror"
                                                       id="previous_school" name="previous_school"
                                                       value="{{ old('previous_school', $student->previous_school) }}"
                                                       placeholder="Name of previous educational institution">
                                                @error('previous_school')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Qualification Obtained</label>
                                                <input type="text" class="form-control @error('qualification') is-invalid @enderror"
                                                       id="qualification" name="qualification"
                                                       value="{{ old('qualification', $student->qualification) }}"
                                                       placeholder="e.g., KCSE, Diploma, Degree">
                                                @error('qualification')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Grade/Score</label>
                                                <input type="text" class="form-control @error('grade_score') is-invalid @enderror"
                                                       id="grade_score" name="grade_score"
                                                       value="{{ old('grade_score', $student->grade_score) }}"
                                                       placeholder="e.g., A-, B+, 350 marks">
                                                @error('grade_score')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Year of Completion</label>
                                                <input type="number" class="form-control @error('completion_year') is-invalid @enderror"
                                                       id="completion_year" name="completion_year"
                                                       value="{{ old('completion_year', $student->completion_year) }}"
                                                       min="1950" max="{{ date('Y') }}">
                                                @error('completion_year')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Photo Upload Section -->
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="bx bx-camera me-2 text-primary"></i> Student Photo
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Passport-size Photo</label>
                                                <input type="file" class="form-control @error('student_photo') is-invalid @enderror"
                                                       id="student_photo" name="student_photo" accept="image/*">
                                                @error('student_photo')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <div class="form-text text-muted">
                                                    <i class="bx bx-info-circle me-1"></i>
                                                    Upload a passport-size photo (JPG, PNG, max 2MB)
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div id="photo-preview" class="d-none">
                                                <label class="form-label fw-bold">Preview:</label>
                                                <div class="border rounded p-2 bg-light">
                                                    <img id="photo-preview-img" src="" alt="Photo Preview" class="img-fluid rounded" style="max-height: 150px;">
                                                </div>
                                            </div>
                                            @if($student->student_photo)
                                                <div id="current-photo">
                                                    <label class="form-label fw-bold">Current Photo:</label>
                                                    <div class="border rounded p-2 bg-light">
                                                        <img src="{{ asset('storage/' . $student->student_photo) }}" alt="Current Photo" class="img-fluid rounded" style="max-height: 150px;">
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
                                        <a href="{{ route('college.students.index') }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-arrow-back me-1"></i> Back to Students
                                        </a>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-warning" onclick="resetForm()">
                                                <i class="bx bx-refresh me-1"></i> Reset Form
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bx bx-save me-1"></i> Update Student
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
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Form Field Enhancements */
    .form-label {
        margin-bottom: 0.5rem;
        color: #495057;
        font-weight: 500;
    }

    /* Select2 Custom Styling */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 24px;
        padding-left: 0;
    }

    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 8px;
    }

    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 0.375rem 0.75rem;
    }

    /* Select2 Error State */
    .select2-container.is-invalid .select2-selection--single {
        border-color: #dc3545;
    }

    .select2-container.is-invalid.select2-container--focus .select2-selection--single {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
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

    /* Alert Styling */
    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    /* Photo Preview Styling */
    #photo-preview-img {
        border: 2px solid #dee2e6;
        transition: border-color 0.15s ease-in-out;
    }

    #photo-preview-img:hover {
        border-color: #86b7fe;
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
    .border-success { border-color: #d1edff !important; }
    .border-warning { border-color: #fff3cd !important; }
    .border-secondary { border-color: #f8f9fa !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    console.log('Enhanced college student edit form loaded');

    // Initialize Select2 for all select fields with live search
    $('#program_id').select2({
        placeholder: 'Search and select program...',
        allowClear: true,
        width: '100%'
    });

    $('#enrollment_year').select2({
        placeholder: 'Search and select enrollment year...',
        allowClear: true,
        width: '100%'
    });

    $('#status').select2({
        placeholder: 'Select status...',
        allowClear: true,
        width: '100%'
    });

    $('#admission_level').select2({
        placeholder: 'Select admission level...',
        allowClear: true,
        width: '100%'
    });

    $('#gender').select2({
        placeholder: 'Select gender...',
        allowClear: true,
        width: '100%'
    });

    $('#emergency_contact_relationship').select2({
        placeholder: 'Select relationship...',
        allowClear: true,
        width: '100%'
    });

    // Initialize Select2 for courses (multiple selection)
    $('#courses').select2({
        placeholder: 'Search and select courses...',
        allowClear: true,
        width: '100%',
        closeOnSelect: false
    });

    // Auto-generate student number if empty
    $('#first_name, #last_name').on('input', function() {
        if (!$('#student_number').val()) {
            var firstName = $('#first_name').val().toUpperCase().substring(0, 2);
            var lastName = $('#last_name').val().toUpperCase().substring(0, 2);
            var year = new Date().getFullYear();
            var randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            var studentNumber = firstName + lastName + year + randomNum;
            $('#student_number').val(studentNumber);
        }
    });

    // Auto-capitalize names
    $('#first_name, #last_name').on('input', function() {
        let value = $(this).val();
        if (value.length > 0) {
            $(this).val(value.charAt(0).toUpperCase() + value.slice(1));
        }
    });

    // Copy permanent address to current address if checkbox is checked
    $('#same_address').on('change', function() {
        if ($(this).is(':checked')) {
            $('#current_address').val($('#permanent_address').val());
        } else {
            $('#current_address').val('{{ $student->current_address }}');
        }
    });

    // Update current address when permanent address changes (if same address is checked)
    $('#permanent_address').on('input', function() {
        if ($('#same_address').is(':checked')) {
            $('#current_address').val($(this).val());
        }
    });

    // File upload preview
    $('#student_photo').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileSize = file.size / 1024 / 1024; // MB
            if (fileSize > 2) {
                showToast('File size must be less than 2MB', 'error');
                $(this).val('');
                $('#photo-preview').addClass('d-none');
                $('#current-photo').show();
                return;
            }

            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                showToast('Please select a valid image file (JPG, PNG)', 'error');
                $(this).val('');
                $('#photo-preview').addClass('d-none');
                $('#current-photo').show();
                return;
            }

            // Show preview and hide current photo
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#photo-preview-img').attr('src', e.target.result);
                $('#photo-preview').removeClass('d-none');
                $('#current-photo').hide();
            };
            reader.readAsDataURL(file);

            showToast('Photo selected successfully', 'success');
        } else {
            $('#photo-preview').addClass('d-none');
            $('#current-photo').show();
        }
    });

    // Form validation
    $('#studentForm').on('submit', function(e) {
        let isValid = true;
        const requiredFields = ['student_number', 'program_id', 'first_name', 'last_name', 'gender', 'enrollment_year', 'status', 'admission_level'];

        requiredFields.forEach(field => {
            const element = $('#' + field);
            // For select fields, check if a value is selected (not empty)
            // For input fields, check if they have a value
            if (element.is('select') && (!element.val() || element.val() === '')) {
                element.addClass('is-invalid');
                // Also add error class to Select2 container
                element.next('.select2-container').addClass('is-invalid');
                isValid = false;
            } else if (element.is('input, textarea') && !element.val()) {
                element.addClass('is-invalid');
                isValid = false;
            } else {
                element.removeClass('is-invalid');
                // Remove error class from Select2 container
                element.next('.select2-container').removeClass('is-invalid');
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
});

// Reset form function
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
        $('#studentForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').hide();
        $('#photo-preview').addClass('d-none');
        $('#current-photo').show();
        // Reset all Select2 fields
        $('#program_id').val('{{ $student->program_id }}').trigger('change');
        $('#enrollment_year').val('{{ $student->enrollment_year }}').trigger('change');
        $('#status').val('{{ $student->status }}').trigger('change');
        $('#gender').val('{{ $student->gender }}').trigger('change');
        $('#emergency_contact_relationship').val('{{ $student->emergency_contact_relationship }}').trigger('change');
        $('#admission_level').val('{{ $student->admission_level }}').trigger('change');
        showToast('Form has been reset', 'info');
    }
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