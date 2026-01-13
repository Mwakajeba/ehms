@extends('layouts.main')

@section('title', 'Add Venue')

@section('content')
<div class="page-content" style="margin-top: 70px; margin-left: 235px; margin-right: 20px;">
    <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
        <div class="row mb-3">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="d-flex align-items-center">
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 me-2">
                        <i class="bx bx-home-alt me-1"></i> Dashboard
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.index') }}" class="btn btn-light btn-sm rounded-pill px-3 mx-2">
                        <i class="bx bx-book-reader me-1"></i> College Management
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.venues.index') }}" class="btn btn-light btn-sm rounded-pill px-3 mx-2">
                        <i class="bx bx-building me-1"></i> Venues
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <span class="btn btn-primary btn-sm rounded-pill px-3 ms-2">
                        <i class="bx bx-plus me-1"></i> Create
                    </span>
                </nav>
            </div>
        </div>

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="bx bx-building-house me-2"></i>Add New Venue
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('college.venues.index') }}">Venues</a></li>
                            <li class="breadcrumb-item active">Add</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-detail me-2"></i>Venue Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('college.venues.store') }}" method="POST">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Venue Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                        value="{{ old('code') }}" required placeholder="e.g., LH-101">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Unique identifier for the venue</small>
                                </div>

                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Venue Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                        value="{{ old('name') }}" required placeholder="e.g., Lecture Hall 101">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Building</label>
                                    <input type="text" name="building" class="form-control @error('building') is-invalid @enderror" 
                                        value="{{ old('building') }}" placeholder="e.g., Main Block">
                                    @error('building')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Floor</label>
                                    <input type="text" name="floor" class="form-control @error('floor') is-invalid @enderror" 
                                        value="{{ old('floor') }}" placeholder="e.g., 1st Floor">
                                    @error('floor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Capacity <span class="text-danger">*</span></label>
                                    <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                                        value="{{ old('capacity') }}" required min="1" placeholder="e.g., 100">
                                    @error('capacity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Venue Type <span class="text-danger">*</span></label>
                                    <select name="venue_type" class="form-select @error('venue_type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="lecture_hall" {{ old('venue_type') == 'lecture_hall' ? 'selected' : '' }}>Lecture Hall</option>
                                        <option value="lab" {{ old('venue_type') == 'lab' ? 'selected' : '' }}>Laboratory</option>
                                        <option value="computer_lab" {{ old('venue_type') == 'computer_lab' ? 'selected' : '' }}>Computer Lab</option>
                                        <option value="seminar_room" {{ old('venue_type') == 'seminar_room' ? 'selected' : '' }}>Seminar Room</option>
                                        <option value="auditorium" {{ old('venue_type') == 'auditorium' ? 'selected' : '' }}>Auditorium</option>
                                        <option value="classroom" {{ old('venue_type') == 'classroom' ? 'selected' : '' }}>Classroom</option>
                                        <option value="workshop" {{ old('venue_type') == 'workshop' ? 'selected' : '' }}>Workshop</option>
                                        <option value="other" {{ old('venue_type') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('venue_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <div class="form-check form-switch mt-2">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                            id="is_active" {{ old('is_active', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Facilities / Equipment</label>
                                <div class="row">
                                    @php
                                        $facilities = [
                                            'projector' => 'Projector',
                                            'whiteboard' => 'Whiteboard',
                                            'smart_board' => 'Smart Board',
                                            'air_conditioning' => 'Air Conditioning',
                                            'sound_system' => 'Sound System',
                                            'microphone' => 'Microphone',
                                            'computer' => 'Computer',
                                            'internet' => 'Internet/WiFi',
                                            'video_conferencing' => 'Video Conferencing',
                                            'lab_equipment' => 'Lab Equipment',
                                        ];
                                        $oldFacilities = old('facilities', []);
                                    @endphp
                                    @foreach($facilities as $key => $label)
                                        <div class="col-md-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="facilities[]" 
                                                    value="{{ $key }}" id="facility_{{ $key }}"
                                                    {{ in_array($key, $oldFacilities) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="facility_{{ $key }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                    rows="3" placeholder="Optional description about the venue">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('college.venues.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save Venue
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Help Card -->
                <div class="card">
                    <div class="card-header border-bottom bg-info-subtle">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-help-circle me-2 text-info"></i>Help
                        </h5>
                    </div>
                    <div class="card-body">
                        <h6 class="fw-semibold">Venue Types</h6>
                        <ul class="small text-muted ps-3">
                            <li><strong>Lecture Hall:</strong> Large rooms for lectures</li>
                            <li><strong>Laboratory:</strong> Science/practical labs</li>
                            <li><strong>Computer Lab:</strong> Rooms with computers</li>
                            <li><strong>Seminar Room:</strong> Small discussion rooms</li>
                            <li><strong>Auditorium:</strong> Large assembly halls</li>
                            <li><strong>Classroom:</strong> Regular classrooms</li>
                            <li><strong>Workshop:</strong> Hands-on practical spaces</li>
                        </ul>

                        <h6 class="fw-semibold mt-3">Tips</h6>
                        <ul class="small text-muted ps-3">
                            <li>Use consistent naming for codes (e.g., LH-101, LAB-A)</li>
                            <li>Select all available facilities for better scheduling</li>
                            <li>Set accurate capacity for proper class allocation</li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-stats me-2"></i>Quick Stats
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Venues are used in timetable scheduling. Adding accurate information helps in:
                        </p>
                        <ul class="small ps-3">
                            <li>Avoiding double-booking</li>
                            <li>Matching class sizes to room capacity</li>
                            <li>Finding suitable venues for specific needs</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
