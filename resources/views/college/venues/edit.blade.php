@extends('layouts.main')

@section('title', 'Edit Venue - ' . $venue->name)

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
                        <i class="bx bx-edit me-1"></i> Edit
                    </span>
                </nav>
            </div>
        </div>

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="bx bx-edit me-2"></i>Edit Venue
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('college.venues.index') }}">Venues</a></li>
                            <li class="breadcrumb-item active">Edit</li>
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
                        <form action="{{ route('college.venues.update', $venue) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Venue Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" 
                                        value="{{ old('code', $venue->code) }}" required placeholder="e.g., LH-101">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Venue Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                        value="{{ old('name', $venue->name) }}" required placeholder="e.g., Lecture Hall 101">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Building</label>
                                    <input type="text" name="building" class="form-control @error('building') is-invalid @enderror" 
                                        value="{{ old('building', $venue->building) }}" placeholder="e.g., Main Block">
                                    @error('building')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Floor</label>
                                    <input type="text" name="floor" class="form-control @error('floor') is-invalid @enderror" 
                                        value="{{ old('floor', $venue->floor) }}" placeholder="e.g., 1st Floor">
                                    @error('floor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Capacity <span class="text-danger">*</span></label>
                                    <input type="number" name="capacity" class="form-control @error('capacity') is-invalid @enderror" 
                                        value="{{ old('capacity', $venue->capacity) }}" required min="1" placeholder="e.g., 100">
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
                                        <option value="lecture_hall" {{ old('venue_type', $venue->venue_type) == 'lecture_hall' ? 'selected' : '' }}>Lecture Hall</option>
                                        <option value="lab" {{ old('venue_type', $venue->venue_type) == 'lab' ? 'selected' : '' }}>Laboratory</option>
                                        <option value="computer_lab" {{ old('venue_type', $venue->venue_type) == 'computer_lab' ? 'selected' : '' }}>Computer Lab</option>
                                        <option value="seminar_room" {{ old('venue_type', $venue->venue_type) == 'seminar_room' ? 'selected' : '' }}>Seminar Room</option>
                                        <option value="auditorium" {{ old('venue_type', $venue->venue_type) == 'auditorium' ? 'selected' : '' }}>Auditorium</option>
                                        <option value="classroom" {{ old('venue_type', $venue->venue_type) == 'classroom' ? 'selected' : '' }}>Classroom</option>
                                        <option value="workshop" {{ old('venue_type', $venue->venue_type) == 'workshop' ? 'selected' : '' }}>Workshop</option>
                                        <option value="other" {{ old('venue_type', $venue->venue_type) == 'other' ? 'selected' : '' }}>Other</option>
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
                                            id="is_active" {{ old('is_active', $venue->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Facilities / Equipment</label>
                                <div class="row">
                                    @php
                                        $facilitiesList = [
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
                                        $currentFacilities = old('facilities', $venue->facilities ?? []);
                                    @endphp
                                    @foreach($facilitiesList as $key => $label)
                                        <div class="col-md-4 col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="facilities[]" 
                                                    value="{{ $key }}" id="facility_{{ $key }}"
                                                    {{ is_array($currentFacilities) && in_array($key, $currentFacilities) ? 'checked' : '' }}>
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
                                    rows="3" placeholder="Optional description about the venue">{{ old('description', $venue->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('college.venues.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Venue
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Venue Info Card -->
                <div class="card">
                    <div class="card-header border-bottom bg-primary-subtle">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2 text-primary"></i>Venue Info
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted">Created</span>
                                <span>{{ $venue->created_at->format('M d, Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span class="text-muted">Last Updated</span>
                                <span>{{ $venue->updated_at->format('M d, Y') }}</span>
                            </li>
                            @if($venue->createdBy)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Created By</span>
                                    <span>{{ $venue->createdBy->name }}</span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Schedule Usage Card -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-calendar me-2"></i>Schedule Usage
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $slotsCount = $venue->timetableSlots()->count();
                        @endphp
                        @if($slotsCount > 0)
                            <div class="alert alert-info mb-0">
                                <i class="bx bx-info-circle me-1"></i>
                                This venue is used in <strong>{{ $slotsCount }}</strong> timetable slot(s).
                            </div>
                        @else
                            <p class="text-muted mb-0">
                                <i class="bx bx-calendar-x me-1"></i>
                                This venue is not currently scheduled in any timetable.
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-zap me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('college.venues.show', $venue) }}" class="btn btn-outline-info">
                                <i class="bx bx-show me-1"></i> View Venue
                            </a>
                            <a href="{{ route('college.venues.create') }}" class="btn btn-outline-primary">
                                <i class="bx bx-plus me-1"></i> Add New Venue
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
