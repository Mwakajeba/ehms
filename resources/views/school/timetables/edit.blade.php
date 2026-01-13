@extends('layouts.main')

@section('title', 'Edit Timetable')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Timetables', 'url' => route('school.timetables.index'), 'icon' => 'bx bx-time-five'],
            ['label' => 'Edit Timetable', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />
        <h6 class="mb-0 text-uppercase">EDIT TIMETABLE</h6>
        <hr />

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center">
                            <div><i class="bx bx-edit me-1 font-22 text-primary"></i></div>
                            <h5 class="mb-0 text-primary">Edit Timetable: {{ $timetable->name }}</h5>
                        </div>
                        <hr />

                        <form action="{{ route('school.timetables.update', $timetable->hashid) }}" method="POST" id="timetableForm">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Timetable Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $timetable->name) }}" placeholder="e.g., Class One - Form 1A Timetable" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="timetable_type" class="form-label">Timetable Type <span class="text-danger">*</span></label>
                                        <select class="form-control @error('timetable_type') is-invalid @enderror" id="timetable_type" name="timetable_type" required>
                                            <option value="">Select Type</option>
                                            <option value="class" {{ old('timetable_type', $timetable->timetable_type) == 'class' ? 'selected' : '' }}>Class Timetable</option>
                                            <option value="teacher" {{ old('timetable_type', $timetable->timetable_type) == 'teacher' ? 'selected' : '' }}>Teacher Timetable</option>
                                            <option value="teacher_on_duty" {{ old('timetable_type', $timetable->timetable_type) == 'teacher_on_duty' ? 'selected' : '' }}>Teacher on Duty</option>
                                            <option value="room" {{ old('timetable_type', $timetable->timetable_type) == 'room' ? 'selected' : '' }}>Room Timetable</option>
                                            <option value="master" {{ old('timetable_type', $timetable->timetable_type) == 'master' ? 'selected' : '' }}>Master Timetable</option>
                                        </select>
                                        @error('timetable_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                        <select class="form-control @error('academic_year_id') is-invalid @enderror" id="academic_year_id" name="academic_year_id" required>
                                            <option value="">Select Academic Year</option>
                                            @foreach($academicYears as $year)
                                                <option value="{{ $year->id }}" {{ old('academic_year_id', $timetable->academic_year_id) == $year->id ? 'selected' : '' }}>
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
                                        <label for="class_id" class="form-label">Class</label>
                                        <select class="form-control @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
                                            <option value="">Select Class (Optional)</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->id }}" {{ old('class_id', $timetable->class_id) == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('class_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="stream_id" class="form-label">Stream</label>
                                        <select class="form-control @error('stream_id') is-invalid @enderror" id="stream_id" name="stream_id">
                                            <option value="">Select Stream (Optional)</option>
                                            @if($timetable->stream_id)
                                                <option value="{{ $timetable->stream_id }}" selected>{{ $timetable->stream->name ?? 'N/A' }}</option>
                                            @endif
                                        </select>
                                        @error('stream_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Select a class first to load streams</small>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Optional description of the timetable">{{ old('description', $timetable->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('school.timetables.show', $timetable->hashid) }}" class="btn btn-info">
                                        <i class="bx bx-show me-1"></i> View
                                    </a>
                                    <a href="{{ route('school.timetables.index') }}" class="btn btn-secondary">
                                        <i class="bx bx-arrow-back me-1"></i> Back
                                    </a>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Update Timetable
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Period Management Section -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="card-title d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Period Management</h6>
                                <small class="text-muted">Customize periods, times, and breaks for this timetable</small>
                            </div>
                            <div class="btn-group">
                                @if($timetable->periods->isNotEmpty())
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#managePeriodsModal">
                                        <i class="bx bx-edit me-1"></i> Update Periods
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#managePeriodsModal">
                                    <i class="bx bx-cog me-1"></i> {{ $timetable->periods->isNotEmpty() ? 'Manage Periods' : 'Add Periods' }}
                                </button>
                            </div>
                        </div>
                        <hr />

                        @if($timetable->periods->isEmpty())
                            <div class="alert alert-info text-center">
                                <i class="bx bx-info-circle me-2"></i>
                                No periods configured. Click "Manage Periods" to set up your school day schedule.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Period #</th>
                                            <th>Day</th>
                                            <th>Start Time</th>
                                            <th>End Time</th>
                                            <th>Duration</th>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($timetable->periods->sortBy(['day_of_week', 'period_number']) as $period)
                                            <tr>
                                                <td>{{ $period->period_number }}</td>
                                                <td>{{ $period->day_of_week }}</td>
                                                <td>{{ \Carbon\Carbon::parse($period->start_time)->format('g:i A') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($period->end_time)->format('g:i A') }}</td>
                                                <td>{{ $period->duration_minutes }} min</td>
                                                <td>
                                                    @if($period->is_break)
                                                        <span class="badge bg-warning">Break</span>
                                                    @else
                                                        <span class="badge bg-primary">{{ ucfirst($period->period_type) }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $period->period_name ?? '-' }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-period-btn" 
                                                            data-period-id="{{ $period->id }}"
                                                            data-day="{{ $period->day_of_week }}"
                                                            title="Edit Period">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Timetable Entries Section -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="card-title d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">Timetable Entries</h6>
                                <small class="text-muted">Manage periods and subjects for this timetable</small>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('school.timetables.bulk-entries', $timetable->hashid) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-grid-alt me-1"></i> Bulk Entry
                                </a>
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                                    <i class="bx bx-plus me-1"></i> Add Entry
                                </button>
                            </div>
                        </div>
                        <hr />

                        @if($timetable->entries->isEmpty())
                            <div class="alert alert-info text-center">
                                <i class="bx bx-info-circle me-2"></i>
                                No timetable entries found. Click "Add Entry" to start building your timetable.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Day</th>
                                            <th>Period</th>
                                            <th>Subject</th>
                                            <th>Teacher</th>
                                            <th>Room</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($timetable->entries->sortBy(['day_of_week', 'period_number']) as $entry)
                                            <tr>
                                                <td>{{ $entry->day_of_week }}</td>
                                                <td>
                                                    @if($entry->period && $entry->period->period_name)
                                                        {{ $entry->period->period_name }}
                                                        <br><small class="text-muted">(Period {{ $entry->period_number }})</small>
                                                    @else
                                                        Period {{ $entry->period_number }}
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $entry->subject->name ?? 'N/A' }}
                                                    @if($entry->subject->code)
                                                        <br><small class="text-muted">({{ $entry->subject->code }})</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($entry->teacher)
                                                        {{ $entry->teacher->first_name ?? '' }} {{ $entry->teacher->last_name ?? '' }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($entry->room)
                                                        {{ $entry->room->room_name }}
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-warning edit-entry" data-entry-id="{{ $entry->id }}">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger delete-entry" data-entry-id="{{ $entry->id }}">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title">Timetable Information</h6>
                        <hr />
                        <div class="mb-3">
                            <strong>Status:</strong>
                            @php
                                $statusBadges = [
                                    'draft' => 'secondary',
                                    'reviewed' => 'info',
                                    'approved' => 'success',
                                    'published' => 'primary'
                                ];
                                $badge = $statusBadges[$timetable->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $badge }} ms-2">{{ ucfirst($timetable->status) }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Created:</strong><br>
                            <small class="text-muted">{{ $timetable->created_at->format('M d, Y g:i A') }}</small>
                        </div>
                        @if($timetable->creator)
                        <div class="mb-3">
                            <strong>Created By:</strong><br>
                            <small class="text-muted">{{ $timetable->creator->name }}</small>
                        </div>
                        @endif
                        @if($timetable->updated_at)
                        <div class="mb-3">
                            <strong>Last Updated:</strong><br>
                            <small class="text-muted">{{ $timetable->updated_at->format('M d, Y g:i A') }}</small>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="card-title">Quick Actions</h6>
                        <hr />
                        <div class="d-grid gap-2">
                            <a href="{{ route('school.timetables.show', $timetable->hashid) }}" class="btn btn-info btn-sm">
                                <i class="bx bx-show me-1"></i> View Timetable
                            </a>
                            <a href="{{ route('school.timetables.duplicate', $timetable->hashid) }}" class="btn btn-secondary btn-sm">
                                <i class="bx bx-copy me-1"></i> Duplicate Timetable
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manage Periods Modal -->
<div class="modal fade" id="managePeriodsModal" tabindex="-1" aria-labelledby="managePeriodsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="managePeriodsModalLabel">
                    <i class="bx bx-cog me-2"></i>Manage Periods
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="periodsForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="timetable_id" value="{{ $timetable->id }}">
                    
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Instructions:</strong> Configure periods for all days of the week. Use the tabs below to switch between days. You can set custom start/end times, mark periods as breaks (Morning Break, Lunch, etc.), and set period names. Click "Copy Monday to All Days" to quickly copy Monday's periods to other days.
                    </div>

                    <!-- Day Tabs -->
                    <ul class="nav nav-tabs mb-3" id="periodDayTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="monday-tab" data-bs-toggle="tab" data-bs-target="#monday" type="button" role="tab" data-day="Monday">Monday</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tuesday-tab" data-bs-toggle="tab" data-bs-target="#tuesday" type="button" role="tab" data-day="Tuesday">Tuesday</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="wednesday-tab" data-bs-toggle="tab" data-bs-target="#wednesday" type="button" role="tab" data-day="Wednesday">Wednesday</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="thursday-tab" data-bs-toggle="tab" data-bs-target="#thursday" type="button" role="tab" data-day="Thursday">Thursday</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="friday-tab" data-bs-toggle="tab" data-bs-target="#friday" type="button" role="tab" data-day="Friday">Friday</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="saturday-tab" data-bs-toggle="tab" data-bs-target="#saturday" type="button" role="tab" data-day="Saturday">Saturday</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sunday-tab" data-bs-toggle="tab" data-bs-target="#sunday" type="button" role="tab" data-day="Sunday">Sunday</button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="periodDayTabContent">
                        <div class="tab-pane fade show active" id="monday" role="tabpanel" data-day="Monday">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Monday Periods</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" data-day="Monday" onclick="showCopyDayModal('Monday')">
                                        <i class="bx bx-copy me-1"></i> Copy to Other Days
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-day="Monday" onclick="addPeriodForDay('Monday')">
                                        <i class="bx bx-plus me-1"></i> Add Period
                                    </button>
                    </div>
                            </div>
                            <div class="periods-container" data-day="Monday"></div>
                        </div>
                        <div class="tab-pane fade" id="tuesday" role="tabpanel" data-day="Tuesday">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Tuesday Periods</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" data-day="Tuesday" onclick="showCopyDayModal('Tuesday')">
                                        <i class="bx bx-copy me-1"></i> Copy to Other Days
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-day="Tuesday" onclick="addPeriodForDay('Tuesday')">
                            <i class="bx bx-plus me-1"></i> Add Period
                                    </button>
                                </div>
                            </div>
                            <div class="periods-container" data-day="Tuesday"></div>
                        </div>
                        <div class="tab-pane fade" id="wednesday" role="tabpanel" data-day="Wednesday">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Wednesday Periods</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" data-day="Wednesday" onclick="showCopyDayModal('Wednesday')">
                                        <i class="bx bx-copy me-1"></i> Copy to Other Days
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-day="Wednesday" onclick="addPeriodForDay('Wednesday')">
                                        <i class="bx bx-plus me-1"></i> Add Period
                                    </button>
                                </div>
                            </div>
                            <div class="periods-container" data-day="Wednesday"></div>
                        </div>
                        <div class="tab-pane fade" id="thursday" role="tabpanel" data-day="Thursday">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Thursday Periods</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" data-day="Thursday" onclick="showCopyDayModal('Thursday')">
                                        <i class="bx bx-copy me-1"></i> Copy to Other Days
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-day="Thursday" onclick="addPeriodForDay('Thursday')">
                                        <i class="bx bx-plus me-1"></i> Add Period
                                    </button>
                                </div>
                            </div>
                            <div class="periods-container" data-day="Thursday"></div>
                        </div>
                        <div class="tab-pane fade" id="friday" role="tabpanel" data-day="Friday">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Friday Periods</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" data-day="Friday" onclick="showCopyDayModal('Friday')">
                                        <i class="bx bx-copy me-1"></i> Copy to Other Days
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-day="Friday" onclick="addPeriodForDay('Friday')">
                                        <i class="bx bx-plus me-1"></i> Add Period
                                    </button>
                                </div>
                            </div>
                            <div class="periods-container" data-day="Friday"></div>
                        </div>
                        <div class="tab-pane fade" id="saturday" role="tabpanel" data-day="Saturday">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Saturday Periods</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" data-day="Saturday" onclick="showCopyDayModal('Saturday')">
                                        <i class="bx bx-copy me-1"></i> Copy to Other Days
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-day="Saturday" onclick="addPeriodForDay('Saturday')">
                                        <i class="bx bx-plus me-1"></i> Add Period
                                    </button>
                                </div>
                            </div>
                            <div class="periods-container" data-day="Saturday"></div>
                        </div>
                        <div class="tab-pane fade" id="sunday" role="tabpanel" data-day="Sunday">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Sunday Periods</h6>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-info" data-day="Sunday" onclick="showCopyDayModal('Sunday')">
                                        <i class="bx bx-copy me-1"></i> Copy to Other Days
                                    </button>
                                    <button type="button" class="btn btn-sm btn-success" data-day="Sunday" onclick="addPeriodForDay('Sunday')">
                                        <i class="bx bx-plus me-1"></i> Add Period
                                    </button>
                                </div>
                            </div>
                            <div class="periods-container" data-day="Sunday"></div>
                        </div>
                    </div>

                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-sm btn-info" id="copyMondayToAllPeriodsBtn">
                            <i class="bx bx-copy me-1"></i> Copy Monday to All Days
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Save & Update Periods
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Entry Modal -->
<div class="modal fade" id="addEntryModal" tabindex="-1" aria-labelledby="addEntryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEntryModalLabel">Add Timetable Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEntryForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="timetable_id" value="{{ $timetable->id }}">
                    <input type="hidden" id="entry_id" name="entry_id" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entry_day_of_week" class="form-label">Day of Week <span class="text-danger">*</span></label>
                                <select class="form-control" id="entry_day_of_week" name="day_of_week" required>
                                    <option value="">Select Day</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                    <option value="Sunday">Sunday</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entry_period_number" class="form-label">Period Number <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="entry_period_number" name="period_number" min="1" max="12" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entry_subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                <select class="form-control" id="entry_subject_id" name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" 
                                                data-requirement-type="{{ $subject->requirement_type ?? 'compulsory' }}">
                                            {{ $subject->name }} @if($subject->code)({{ $subject->code }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entry_teacher_id" class="form-label">Teacher</label>
                                <select class="form-control" id="entry_teacher_id" name="teacher_id">
                                    <option value="">Select Teacher (Optional)</option>
                                    @foreach($teachers as $teacher)
                                        <option value="{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entry_room_id" class="form-label">Room</label>
                                <select class="form-control" id="entry_room_id" name="room_id">
                                    <option value="">Select Room (Optional)</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">{{ $room->room_name }} @if($room->room_code)({{ $room->room_code }})@endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="entry_subject_type" class="form-label">Subject Type</label>
                                <input type="hidden" id="entry_subject_type" name="subject_type" value="compulsory">
                                <div class="form-control bg-light" id="entry_subject_type_display" style="pointer-events: none; min-height: 38px; display: flex; align-items: center;">
                                    <span class="badge bg-success" id="entry_subject_type_badge">Compulsory</span>
                                    <small class="text-muted ms-2">Auto from subject</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="entry_is_double_period" name="is_double_period" value="1">
                                    <label class="form-check-label" for="entry_is_double_period">
                                        Double Period
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="entry_is_practical" name="is_practical" value="1">
                                    <label class="form-check-label" for="entry_is_practical">
                                        Practical Subject
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="entry_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="entry_notes" name="notes" rows="2" placeholder="Optional notes about this entry"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="entrySubmitBtn">
                        <i class="bx bx-save me-1"></i> <span id="entrySubmitText">Add Entry</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Load streams when class is selected
        $('#class_id').on('change', function() {
            var classId = $(this).val();
            var $streamSelect = $('#stream_id');
            var currentStreamId = '{{ $timetable->stream_id }}';
            
            if (classId) {
                $.ajax({
                    url: '{{ route("school.timetables.get-streams") }}',
                    type: 'POST',
                    data: {
                        class_id: classId,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $streamSelect.prop('disabled', true).html('<option value="">Loading streams...</option>');
                    },
                    success: function(response) {
                        $streamSelect.empty();
                        $streamSelect.append('<option value="">Select Stream (Optional)</option>');
                        if (response && response.length > 0) {
                            response.forEach(function(stream) {
                                var selected = (currentStreamId && stream.id == currentStreamId) ? 'selected' : '';
                                $streamSelect.append('<option value="' + stream.id + '" ' + selected + '>' + stream.name + '</option>');
                            });
                        }
                        $streamSelect.prop('disabled', false);
                    },
                    error: function(xhr) {
                        console.error('Error loading streams:', xhr);
                        $streamSelect.empty();
                        $streamSelect.append('<option value="">Select Stream (Optional)</option>');
                        $streamSelect.prop('disabled', false);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: 'Failed to load streams',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        } else {
                            alert('Failed to load streams');
                        }
                    }
                });
            } else {
                $streamSelect.empty();
                $streamSelect.append('<option value="">Select Stream (Optional)</option>');
            }
        });

        // Trigger change on page load if class is already selected
        @if($timetable->class_id)
            $('#class_id').trigger('change');
        @endif


        // Auto-update requirement type when subject is selected in add entry modal
        $('#entry_subject_id').on('change', function() {
            const subjectId = $(this).val();
            const $typeInput = $('#entry_subject_type');
            const $typeBadge = $('#entry_subject_type_badge');
            const $typeDisplay = $('#entry_subject_type_display');
            
            if (subjectId) {
                // Get requirement type from selected option
                const selectedOption = $(this).find('option:selected');
                const requirementType = selectedOption.data('requirement-type') || 'compulsory';
                
                // Update hidden input
                $typeInput.val(requirementType);
                
                // Update badge
                $typeBadge.removeClass('bg-success bg-warning')
                         .addClass(requirementType === 'optional' ? 'bg-warning' : 'bg-success')
                         .text(requirementType.charAt(0).toUpperCase() + requirementType.slice(1));
            } else {
                // Reset to default
                $typeInput.val('compulsory');
                $typeBadge.removeClass('bg-warning').addClass('bg-success').text('Compulsory');
            }
        });

        // Period Management
        let periodCounters = {
            Monday: 0,
            Tuesday: 0,
            Wednesday: 0,
            Thursday: 0,
            Friday: 0,
            Saturday: 0,
            Sunday: 0
        };
        let existingPeriods = @json($periodsByDay);
        const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Load periods for all days when modal is shown
        $('#managePeriodsModal').on('shown.bs.modal', function() {
            daysOfWeek.forEach(function(day) {
                loadPeriodsForDay(day);
            });
        });

        function loadPeriodsForDay(day) {
            const container = $(`.periods-container[data-day="${day}"]`);
            container.empty();
            periodCounters[day] = 0;

            // Get periods for the selected day
            const periods = existingPeriods[day] || [];
            
            if (!periods || periods.length === 0) {
                // Add default periods if none exist
                addDefaultPeriods(day);
            } else {
                periods.forEach(function(period, index) {
                    addPeriodRow(day, period);
                });
            }
        }
        
        // Global function for add period button
        window.addPeriodForDay = function(day) {
            addPeriodRow(day);
        };

        function addDefaultPeriods(day) {
            const defaultStartTime = '08:00';
            const defaultDuration = 40;
            const defaultPeriods = 8;

            for (let i = 1; i <= defaultPeriods; i++) {
                const startTime = calculateTime(defaultStartTime, (i - 1) * defaultDuration);
                const endTime = calculateTime(startTime, defaultDuration);
                
                addPeriodRow(day, {
                    period_number: i,
                    start_time: startTime,
                    end_time: endTime,
                    duration_minutes: defaultDuration,
                    period_type: 'regular',
                    period_name: '',
                    is_break: false,
                });
            }
        }

        function calculateTime(startTime, addMinutes) {
            const [hours, minutes] = startTime.split(':').map(Number);
            const totalMinutes = hours * 60 + minutes + addMinutes;
            const newHours = Math.floor(totalMinutes / 60);
            const newMins = totalMinutes % 60;
            return String(newHours).padStart(2, '0') + ':' + String(newMins).padStart(2, '0');
        }

        function addPeriodRow(day, periodData = {}) {
            periodCounters[day]++;
            const periodCounter = periodCounters[day];
            const periodId = periodData.id || 'new_' + day + '_' + periodCounter;
            const isBreak = periodData.is_break || false;
            
            // Format time values to HH:MM format (remove seconds if present)
            let startTime = periodData.start_time || null;
            let endTime = periodData.end_time || null;
            
            // Debug log
            console.log('Adding period row:', periodData);
            
            // Handle time formatting
            if (startTime) {
                // Remove seconds if present (HH:MM:SS -> HH:MM)
                if (startTime.length > 5) {
                    startTime = startTime.substring(0, 5);
                }
                // Validate format (should be HH:MM)
                if (!/^\d{2}:\d{2}$/.test(startTime)) {
                    startTime = '08:00';
                }
            } else {
                startTime = '08:00';
            }
            
            if (endTime) {
                if (endTime.length > 5) {
                    endTime = endTime.substring(0, 5);
                }
                if (!/^\d{2}:\d{2}$/.test(endTime)) {
                    endTime = '08:40';
                }
            } else {
                endTime = '08:40';
            }
            
            // Ensure values are not empty or invalid
            if (!startTime || startTime === '--:--' || startTime === 'null' || startTime === 'undefined') {
                startTime = '08:00';
            }
            if (!endTime || endTime === '--:--' || endTime === 'null' || endTime === 'undefined') {
                endTime = '08:40';
            }
            
            const row = `
                <div class="period-row mb-3 p-3 border rounded" data-period-id="${periodId}">
                    <div class="row align-items-end">
                        <div class="col-md-1">
                            <label class="form-label small">Period #</label>
                            <input type="number" class="form-control form-control-sm period-number" 
                                   name="periods[${day}][${periodCounter}][period_number]" 
                                   value="${periodData.period_number || periodCounter}" min="1" required>
                            ${periodData.id ? '<input type="hidden" name="periods[' + day + '][' + periodCounter + '][id]" value="' + periodData.id + '">' : ''}
                            <input type="hidden" name="periods[${day}][${periodCounter}][day_of_week]" value="${day}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Start Time</label>
                            <input type="time" class="form-control form-control-sm period-start-time" 
                                   name="periods[${day}][${periodCounter}][start_time]" 
                                   value="${startTime}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">End Time</label>
                            <input type="time" class="form-control form-control-sm period-end-time" 
                                   name="periods[${day}][${periodCounter}][end_time]" 
                                   value="${endTime}" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small">Duration</label>
                            <input type="number" class="form-control form-control-sm period-duration" 
                                   name="periods[${day}][${periodCounter}][duration_minutes]" 
                                   value="${periodData.duration_minutes || 40}" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Type</label>
                            <select class="form-control form-control-sm period-type" 
                                    name="periods[${day}][${periodCounter}][period_type]">
                                <option value="regular" ${periodData.period_type === 'regular' ? 'selected' : ''}>Regular</option>
                                <option value="break" ${periodData.period_type === 'break' ? 'selected' : ''}>Break</option>
                                <option value="assembly" ${periodData.period_type === 'assembly' ? 'selected' : ''}>Assembly</option>
                                <option value="games" ${periodData.period_type === 'games' ? 'selected' : ''}>Games</option>
                                <option value="lunch" ${periodData.period_type === 'lunch' ? 'selected' : ''}>Lunch</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Period Name</label>
                            <input type="text" class="form-control form-control-sm" 
                                   name="periods[${day}][${periodCounter}][period_name]" 
                                   value="${periodData.period_name || ''}" 
                                   placeholder="e.g., Morning Break">
                        </div>
                        <div class="col-md-1">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" 
                                       name="periods[${day}][${periodCounter}][is_break]" 
                                       value="1" ${isBreak ? 'checked' : ''}>
                                <label class="form-check-label small">Break</label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-sm btn-danger remove-period" title="Remove Period">
                                <i class="bx bx-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            const container = $(`.periods-container[data-day="${day}"]`);
            container.append(row);
            
            // Auto-calculate end time when start time or duration changes
            const $row = container.find('.period-row').last();
            $row.find('.period-start-time, .period-duration').on('change', function() {
                calculateEndTime($row);
            });
        }

        function calculateEndTime($row) {
            const startTime = $row.find('.period-start-time').val();
            const duration = parseInt($row.find('.period-duration').val()) || 40;
            
            if (startTime) {
                const [hours, minutes] = startTime.split(':').map(Number);
                const totalMinutes = hours * 60 + minutes + duration;
                const endHours = Math.floor(totalMinutes / 60);
                const endMins = totalMinutes % 60;
                const endTime = String(endHours).padStart(2, '0') + ':' + String(endMins).padStart(2, '0');
                $row.find('.period-end-time').val(endTime);
            }
        }

        // Copy Monday periods to all days
        $('#copyMondayToAllPeriodsBtn').on('click', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Copy Monday to All Days?',
                    text: 'This will copy all Monday periods to Tuesday, Wednesday, Thursday, Friday, Saturday, and Sunday. Existing periods on those days will be overwritten.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Copy to All Days',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        copyMondayPeriodsToAllDays();
                    }
                });
            } else {
                if (confirm('Copy Monday periods to all other days?')) {
                    copyMondayPeriodsToAllDays();
                }
            }
        });
        
        function copyMondayPeriodsToAllDays() {
            copyDayPeriodsToOtherDays('Monday', ['Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
        }
        
        // Global function to show copy day modal
        window.showCopyDayModal = function(fromDay) {
            const otherDays = daysOfWeek.filter(day => day !== fromDay);
            
            if (typeof Swal !== 'undefined') {
                let checkboxesHtml = '';
                otherDays.forEach(function(day) {
                    checkboxesHtml += `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="copyTo${day}" value="${day}" checked>
                            <label class="form-check-label" for="copyTo${day}">${day}</label>
                        </div>
                    `;
                });
                
                Swal.fire({
                    title: `Copy ${fromDay} to Other Days`,
                    html: `
                        <p class="mb-3">Select which days to copy ${fromDay}'s periods to:</p>
                        ${checkboxesHtml}
                        <div class="alert alert-warning mt-3">
                            <small><i class="bx bx-info-circle me-1"></i>Existing periods on selected days will be overwritten.</small>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Copy Periods',
                    cancelButtonText: 'Cancel',
                    width: '500px',
                    preConfirm: () => {
                        const selectedDays = [];
                        otherDays.forEach(function(day) {
                            if (document.getElementById('copyTo' + day)?.checked) {
                                selectedDays.push(day);
                            }
                        });
                        return { fromDay, selectedDays };
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        copyDayPeriodsToOtherDays(result.value.fromDay, result.value.selectedDays);
                    }
                });
            } else {
                const selectedDays = [];
                otherDays.forEach(function(day) {
                    if (confirm(`Copy ${fromDay} periods to ${day}?`)) {
                        selectedDays.push(day);
                    }
                });
                if (selectedDays.length > 0) {
                    copyDayPeriodsToOtherDays(fromDay, selectedDays);
                }
            }
        };
        
        function copyDayPeriodsToOtherDays(fromDay, toDays) {
            const sourcePeriods = [];
            $(`.periods-container[data-day="${fromDay}"] .period-row`).each(function() {
                const $row = $(this);
                const periodData = {
                    period_number: $row.find('.period-number').val(),
                    start_time: $row.find('.period-start-time').val(),
                    end_time: $row.find('.period-end-time').val(),
                    duration_minutes: $row.find('.period-duration').val(),
                    period_type: $row.find('.period-type').val(),
                    period_name: $row.find('input[name*="period_name"]').val(),
                    is_break: $row.find('input[name*="is_break"]').is(':checked'),
                    id: $row.data('period-id') || null
                };
                sourcePeriods.push(periodData);
            });
            
            if (sourcePeriods.length === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Periods',
                        text: `No periods found for ${fromDay}. Please add periods first.`
                    });
                } else {
                    alert(`No periods found for ${fromDay}. Please add periods first.`);
                }
                return;
            }
            
            let totalCopied = 0;
            toDays.forEach(function(toDay) {
                const container = $(`.periods-container[data-day="${toDay}"]`);
                container.empty();
                periodCounters[toDay] = 0;
                
                sourcePeriods.forEach(function(periodData) {
                    addPeriodRow(toDay, periodData);
                    totalCopied++;
                });
            });
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: `Copied ${sourcePeriods.length} periods from ${fromDay} to ${toDays.join(', ')}.`,
                    timer: 2000
                });
            } else {
                alert(`Copied ${sourcePeriods.length} periods from ${fromDay} to ${toDays.join(', ')}.`);
            }
        }

        $(document).on('click', '.remove-period', function() {
            const $row = $(this).closest('.period-row');
            const container = $row.closest('.periods-container');
            const day = container.data('day');
            
            if (container.find('.period-row').length > 1) {
                $row.remove();
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cannot Remove Period',
                        text: `You must have at least one period for ${day}`,
                        confirmButtonText: 'OK'
                    });
                } else {
                    alert(`You must have at least one period for ${day}`);
                }
            }
        });

        // Handle edit period button click from table
        $(document).on('click', '.edit-period-btn', function() {
            const periodId = $(this).data('period-id');
            const day = $(this).data('day');
            
            // Open the modal
            $('#managePeriodsModal').modal('show');
            
            // Switch to the correct tab
            setTimeout(function() {
                const dayTab = $(`#${day.toLowerCase()}-tab`);
                if (dayTab.length) {
                    dayTab.tab('show');
                }
            
                // Scroll to the specific period if needed
            setTimeout(function() {
                const periodRow = $(`.period-row[data-period-id="${periodId}"]`);
                if (periodRow.length) {
                        const container = periodRow.closest('.tab-pane');
                        if (container.length) {
                    $('html, body').animate({
                        scrollTop: periodRow.offset().top - 100
                    }, 500);
                    periodRow.css('background-color', '#fff3cd').delay(2000).queue(function() {
                        $(this).css('background-color', '').dequeue();
                    });
                }
                    }
                }, 300);
            }, 300);
        });

        // Handle period form submission
        $('#periodsForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
            
            $.ajax({
                url: '{{ route("school.timetables.periods.store") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#managePeriodsModal').modal('hide');
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Periods saved successfully',
                                showConfirmButton: false,
                                timer: 3000
                            }).then(() => {
                                // Reload page to show updated periods
                                window.location.reload();
                            });
                        } else {
                            alert('Periods saved successfully');
                            window.location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to save periods',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(response.message || 'Failed to save periods');
                        }
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    var errorMessage = 'Failed to save periods';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errorMessage);
                    }
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Handle Add Entry Form Submission
        $('#addEntryForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = $(this).serialize();
            const entryId = $('#entry_id').val();
            const submitBtn = $('#entrySubmitBtn');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
            
            let url, method;
            if (entryId) {
                // Update existing entry
                url = '{{ route("school.timetables.entries.update", ":id") }}'.replace(':id', entryId);
                method = 'PUT';
            } else {
                // Create new entry
                url = '{{ route("school.timetables.entries.store") }}';
                method = 'POST';
            }
            
            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#addEntryModal').modal('hide');
                        $('#addEntryForm')[0].reset();
                        $('#entry_id').val('');
                        $('#addEntryModalLabel').text('Add Timetable Entry');
                        $('#entrySubmitText').text('Add Entry');
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Entry saved successfully',
                                showConfirmButton: false,
                                timer: 3000
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            alert(response.message || 'Entry saved successfully');
                            window.location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to save entry',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(response.message || 'Failed to save entry');
                        }
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to save entry';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('\n');
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errorMessage);
                    }
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Handle Edit Entry Button Click
        $(document).on('click', '.edit-entry', function() {
            const entryId = $(this).data('entry-id');
            
            // Fetch entry data
            $.ajax({
                url: '{{ route("school.timetables.edit", $timetable->hashid) }}',
                type: 'GET',
                data: { entry_id: entryId },
                success: function(response) {
                    // We'll need to get entry data from the page or make a separate AJAX call
                    // For now, let's fetch it from the table row
                    const $row = $(`.edit-entry[data-entry-id="${entryId}"]`).closest('tr');
                    
                    // Get entry data from the page (we need to add data attributes)
                    // Or make an AJAX call to get entry details
                    loadEntryForEdit(entryId);
                },
                error: function() {
                    // Fallback: load from table data
                    loadEntryForEdit(entryId);
                }
            });
        });

        // Function to load entry data for editing
        function loadEntryForEdit(entryId) {
            // Make AJAX call to get entry details
            $.ajax({
                url: '{{ route("school.timetables.entries.edit", ":id") }}'.replace(':id', entryId),
                type: 'GET',
                success: function(response) {
                    if (response.entry) {
                        const entry = response.entry;
                        $('#entry_id').val(entry.id);
                        $('#entry_day_of_week').val(entry.day_of_week);
                        $('#entry_period_number').val(entry.period_number);
                        $('#entry_subject_id').val(entry.subject_id).trigger('change');
                        $('#entry_teacher_id').val(entry.teacher_id || '');
                        $('#entry_room_id').val(entry.room_id || '');
                        $('#entry_subject_type').val(entry.subject_type || 'compulsory');
                        $('#entry_is_double_period').prop('checked', entry.is_double_period == 1);
                        $('#entry_is_practical').prop('checked', entry.is_practical == 1);
                        $('#entry_notes').val(entry.notes || '');
                        
                        // Update badge
                        const requirementType = entry.subject_type || 'compulsory';
                        $('#entry_subject_type_badge')
                            .removeClass('bg-success bg-warning')
                            .addClass(requirementType === 'optional' ? 'bg-warning' : 'bg-success')
                            .text(requirementType.charAt(0).toUpperCase() + requirementType.slice(1));
                        
                        $('#addEntryModalLabel').text('Edit Timetable Entry');
                        $('#entrySubmitText').text('Update Entry');
                        $('#addEntryModal').modal('show');
                    }
                },
                error: function() {
                    // If no edit endpoint, get data from table
                    const $row = $(`.edit-entry[data-entry-id="${entryId}"]`).closest('tr');
                    // This is a fallback - we'll need data attributes on the row
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Could not load entry data',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        }

        // Handle Delete Entry Button Click
        $(document).on('click', '.delete-entry', function() {
            const entryId = $(this).data('entry-id');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Delete Entry?',
                    text: 'Are you sure you want to delete this timetable entry? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteEntry(entryId);
                    }
                });
            } else {
                if (confirm('Are you sure you want to delete this entry?')) {
                    deleteEntry(entryId);
                }
            }
        });

        // Function to delete entry
        function deleteEntry(entryId) {
            $.ajax({
                url: '{{ route("school.timetables.entries.destroy", ":id") }}'.replace(':id', entryId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message || 'Entry deleted successfully',
                                showConfirmButton: false,
                                timer: 3000
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            alert(response.message || 'Entry deleted successfully');
                            window.location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to delete entry',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            alert(response.message || 'Failed to delete entry');
                        }
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to delete entry';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert(errorMessage);
                    }
                }
            });
        }

        // Reset modal when closed
        $('#addEntryModal').on('hidden.bs.modal', function() {
            $('#addEntryForm')[0].reset();
            $('#entry_id').val('');
            $('#addEntryModalLabel').text('Add Timetable Entry');
            $('#entrySubmitText').text('Add Entry');
            $('#entry_subject_type_badge').removeClass('bg-warning').addClass('bg-success').text('Compulsory');
            $('#entry_subject_type').val('compulsory');
        });
    });
</script>
@endpush

