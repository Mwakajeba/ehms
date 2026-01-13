@extends('layouts.main')

@section('title', 'Venue - ' . $venue->name)

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
                        <i class="bx bx-show me-1"></i> View
                    </span>
                </nav>
            </div>
        </div>

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">
                        <i class="bx bx-building me-2"></i>{{ $venue->full_name }}
                    </h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('college.venues.index') }}">Venues</a></li>
                            <li class="breadcrumb-item active">View</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="row mb-3">
            <div class="col-lg-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Capacity</p>
                                <h4 class="fs-22 fw-semibold mb-0">{{ $venue->capacity }}</h4>
                                <small class="text-muted">seats</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-primary-subtle rounded fs-3">
                                    <i class="bx bx-user text-primary"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Type</p>
                                <h4 class="fs-18 fw-semibold mb-0">{{ ucwords(str_replace('_', ' ', $venue->venue_type)) }}</h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-info-subtle rounded fs-3">
                                    <i class="bx bx-category text-info"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Location</p>
                                <h4 class="fs-18 fw-semibold mb-0">{{ $venue->building ?? 'N/A' }}</h4>
                                <small class="text-muted">{{ $venue->floor ?? '' }}</small>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-success-subtle rounded fs-3">
                                    <i class="bx bx-map text-success"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card card-animate">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <p class="text-uppercase fw-medium text-muted mb-0">Status</p>
                                <h4 class="fs-18 fw-semibold mb-0">
                                    @if($venue->is_active)
                                        <span class="badge bg-success fs-6">Active</span>
                                    @else
                                        <span class="badge bg-danger fs-6">Inactive</span>
                                    @endif
                                </h4>
                            </div>
                            <div class="avatar-sm flex-shrink-0">
                                <span class="avatar-title bg-warning-subtle rounded fs-3">
                                    <i class="bx bx-check-shield text-warning"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('college.venues.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to List
                    </a>
                    <a href="{{ route('college.venues.edit', $venue) }}" class="btn btn-primary">
                        <i class="bx bx-edit me-1"></i> Edit Venue
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Venue Details -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-detail me-2"></i>Venue Details
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Code</td>
                                    <td><strong>{{ $venue->code }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Name</td>
                                    <td>{{ $venue->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Building</td>
                                    <td>{{ $venue->building ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Floor</td>
                                    <td>{{ $venue->floor ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Capacity</td>
                                    <td>{{ $venue->capacity }} seats</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Type</td>
                                    <td>
                                        @php
                                            $typeColors = [
                                                'lecture_hall' => 'primary',
                                                'lab' => 'success',
                                                'computer_lab' => 'info',
                                                'seminar_room' => 'warning',
                                                'auditorium' => 'danger',
                                                'classroom' => 'secondary',
                                                'workshop' => 'dark',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $typeColors[$venue->venue_type] ?? 'secondary' }}">
                                            {{ ucwords(str_replace('_', ' ', $venue->venue_type)) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Facilities Card -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-cog me-2"></i>Facilities & Equipment
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $facilitiesLabels = [
                                'projector' => ['icon' => 'bx-slideshow', 'label' => 'Projector'],
                                'whiteboard' => ['icon' => 'bx-chalkboard', 'label' => 'Whiteboard'],
                                'smart_board' => ['icon' => 'bx-tv', 'label' => 'Smart Board'],
                                'air_conditioning' => ['icon' => 'bx-wind', 'label' => 'Air Conditioning'],
                                'sound_system' => ['icon' => 'bx-speaker', 'label' => 'Sound System'],
                                'microphone' => ['icon' => 'bx-microphone', 'label' => 'Microphone'],
                                'computer' => ['icon' => 'bx-desktop', 'label' => 'Computer'],
                                'internet' => ['icon' => 'bx-wifi', 'label' => 'Internet/WiFi'],
                                'video_conferencing' => ['icon' => 'bx-video', 'label' => 'Video Conferencing'],
                                'lab_equipment' => ['icon' => 'bx-test-tube', 'label' => 'Lab Equipment'],
                            ];
                            $venueFacilities = $venue->facilities ?? [];
                        @endphp
                        @if(!empty($venueFacilities))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($venueFacilities as $facility)
                                    @if(isset($facilitiesLabels[$facility]))
                                        <span class="badge bg-success-subtle text-success fs-6">
                                            <i class="bx {{ $facilitiesLabels[$facility]['icon'] }} me-1"></i>
                                            {{ $facilitiesLabels[$facility]['label'] }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $facility }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">
                                <i class="bx bx-x-circle me-1"></i>
                                No facilities listed for this venue.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Scheduled Slots List -->
            <div class="col-lg-8">
                @php
                    // Get ALL slots for this venue (both draft and published)
                    $venueSlots = $venue->timetableSlots()
                        ->with(['course', 'timetable.program', 'instructor'])
                        ->where('is_active', true)
                        ->get();
                @endphp
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-ul me-2"></i>All Scheduled Sessions ({{ $venueSlots->count() }})
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Program</th>
                                        <th>Course</th>
                                        <th>Day</th>
                                        <th>Time</th>
                                        <th>Type</th>
                                        <th>Instructor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($venueSlots->sortBy(['day_of_week', 'start_time']) as $slot)
                                        <tr>
                                            <td>
                                                <small class="text-muted">{{ $slot->timetable->program->code }}</small><br>
                                                <span class="small">Year {{ $slot->timetable->year_of_study }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $slot->course->code }}</strong><br>
                                                <small class="text-muted">{{ Str::limit($slot->course->name, 20) }}</small>
                                            </td>
                                            <td>{{ $slot->day_of_week }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - 
                                                {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                            </td>
                                            <td>
                                                @php
                                                    $colors = [
                                                        'lecture' => 'primary',
                                                        'tutorial' => 'success',
                                                        'practical' => 'warning',
                                                        'lab' => 'info',
                                                        'seminar' => 'secondary',
                                                        'workshop' => 'dark',
                                                        'exam' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $colors[$slot->slot_type] ?? 'secondary' }}">
                                                    {{ ucfirst($slot->slot_type) }}
                                                </span>
                                            </td>
                                            <td>{{ $slot->instructor?->full_name ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">
                                                <i class="bx bx-calendar-x fs-1 d-block mb-2"></i>
                                                No scheduled sessions for this venue.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table td {
    font-size: 12px;
}
</style>
@endpush
