@extends('layouts.main')

@section('title', 'Teacher Timetable - ' . $employee->full_name)

@push('styles')
<style>
    .teacher-banner {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(30, 60, 114, 0.3);
        position: relative;
        overflow: hidden;
    }
    .teacher-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .teacher-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 700;
        border: 3px solid rgba(255,255,255,0.3);
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin: 0 auto 10px;
    }
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: #333;
    }
    .stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .timetable-grid {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    .timetable-grid th {
        background: #1e3c72;
        color: white;
        padding: 10px 6px;
        font-weight: 600;
        text-align: center;
        font-size: 11px;
        white-space: nowrap;
    }
    .timetable-grid td {
        padding: 6px;
        vertical-align: middle;
        border: 1px solid #e9ecef;
        min-width: 80px;
        height: 80px;
    }
    .time-cell {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        font-weight: 600;
        text-align: center;
        vertical-align: middle !important;
        font-size: 12px;
        width: 100px;
        min-width: 100px;
        color: white;
    }
    
    .slot-item {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-left: 3px solid #2196F3;
        padding: 8px 10px;
        border-radius: 8px;
        margin-bottom: 5px;
        font-size: 11px;
        transition: all 0.3s;
    }
    .slot-item:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .slot-item.lecture { border-color: #2196F3; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); }
    .slot-item.tutorial { border-color: #4CAF50; background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); }
    .slot-item.practical { border-color: #FF9800; background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); }
    .slot-item.lab { border-color: #00BCD4; background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); }
    .slot-item.seminar { border-color: #9E9E9E; background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%); }
    .slot-item.workshop { border-color: #607D8B; background: linear-gradient(135deg, #eceff1 0%, #cfd8dc 100%); }
    .slot-item.exam { border-color: #F44336; background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); }
    
    .slot-course {
        font-weight: 700;
        font-size: 12px;
        color: #333;
    }
    .slot-program {
        font-size: 10px;
        color: #666;
        background: rgba(0,0,0,0.05);
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-block;
        margin-top: 3px;
    }
    .slot-time {
        font-size: 10px;
        color: #555;
    }
    .slot-venue {
        font-size: 10px;
        color: #777;
    }
    .slot-type-badge {
        font-size: 8px;
        padding: 2px 6px;
        border-radius: 4px;
        background: #333;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-view-slot {
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 6px;
        padding: 3px 8px;
        font-size: 12px;
        color: #1e3c72;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .btn-view-slot:hover {
        background: #1e3c72;
        color: white;
        transform: scale(1.1);
    }
    
    .info-item {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 8px;
        border-left: 3px solid #1e3c72;
    }
    .info-item small {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-item p {
        font-size: 13px;
    }
    
    .course-summary-table th {
        background: #1e3c72;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="page-content" style="margin-top: 70px; margin-left: 235px; margin-right: 20px;">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row mb-3">
            <div class="col-12">
                <nav aria-label="breadcrumb" class="d-flex align-items-center flex-wrap gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bx bx-home-alt me-1"></i> Dashboard
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.index') }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bx bx-book-reader me-1"></i> College
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.teacher-timetables.index') }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bx bx-user me-1"></i> Teacher Timetables
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <span class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                        <i class="bx bx-calendar me-1"></i> {{ $employee->first_name }}
                    </span>
                </nav>
            </div>
        </div>

        <!-- Teacher Banner -->
        <div class="teacher-banner">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center">
                        <div class="teacher-avatar me-4">
                            {{ strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)) }}
                        </div>
                        <div>
                            <h2 class="mb-1">{{ $employee->full_name }}</h2>
                            <p class="mb-0 opacity-75">
                                @if($employee->employee_number)
                                    <i class="bx bx-id-card me-1"></i> {{ $employee->employee_number }}
                                    <span class="mx-2">•</span>
                                @endif
                                @if($employee->department)
                                    <i class="bx bx-building me-1"></i> {{ $employee->department->name }}
                                    <span class="mx-2">•</span>
                                @endif
                                @if($employee->designation)
                                    <i class="bx bx-briefcase me-1"></i> {{ $employee->designation }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <div class="d-flex gap-2 justify-content-lg-end">
                        <a href="{{ route('college.teacher-timetables.print', ['employee' => $employee, 'academic_year_id' => $currentAcademicYear?->id]) }}" 
                           class="btn btn-light btn-sm" target="_blank">
                            <i class="bx bx-printer me-1"></i> Print
                        </a>
                        <a href="{{ route('college.teacher-timetables.export-pdf', ['employee' => $employee, 'academic_year_id' => $currentAcademicYear?->id]) }}" 
                           class="btn btn-danger btn-sm">
                            <i class="bx bx-file-blank me-1"></i> Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body py-2">
                        <form method="GET" class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 text-nowrap"><i class="bx bx-calendar me-1"></i> Academic Year:</label>
                            <select name="academic_year_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Years</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ ($currentAcademicYear?->id ?? '') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="bx bx-calendar-event"></i>
                    </div>
                    <div class="stat-value">{{ $totalSlots }}</div>
                    <div class="stat-label">Total Sessions</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="bx bx-time-five"></i>
                    </div>
                    <div class="stat-value">{{ number_format($totalHours, 1) }}</div>
                    <div class="stat-label">Hours/Week</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="bx bx-book"></i>
                    </div>
                    <div class="stat-value">{{ $uniqueCourses }}</div>
                    <div class="stat-label">Courses</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="bx bx-graduation"></i>
                    </div>
                    <div class="stat-value">{{ $uniquePrograms }}</div>
                    <div class="stat-label">Programs</div>
                </div>
            </div>
        </div>

        <!-- Timetable Grid -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-calendar me-2"></i>Weekly Schedule
                            @if($currentAcademicYear)
                                <span class="badge bg-primary ms-2">{{ $currentAcademicYear->name }}</span>
                            @endif
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @php
                            // Define time periods for columns
                            $timePeriods = [];
                            for ($hour = 7; $hour <= 20; $hour++) {
                                $timePeriods[] = [
                                    'start' => sprintf('%02d:00', $hour),
                                    'end' => sprintf('%02d:00', $hour + 1),
                                    'label' => sprintf('%d:00-%d:00', $hour, $hour + 1)
                                ];
                            }
                            
                            // Day labels
                            $dayLabels = [
                                'Monday' => 'Jumatatu',
                                'Tuesday' => 'Jumanne',
                                'Wednesday' => 'Jumatano',
                                'Thursday' => 'Alhamisi',
                                'Friday' => 'Ijumaa',
                                'Saturday' => 'Jumamosi',
                            ];
                            
                            // Function to calculate colspan
                            function getTeacherSlotColspan($slot, $timePeriods) {
                                $sStartH = (int)substr($slot->start_time, 0, 2);
                                $sStartM = (int)substr($slot->start_time, 3, 2);
                                $sEndH = (int)substr($slot->end_time, 0, 2);
                                $sEndM = (int)substr($slot->end_time, 3, 2);
                                $sStart = $sStartH * 60 + $sStartM;
                                $sEnd = $sEndH * 60 + $sEndM;
                                
                                $colspan = 0;
                                $startCol = -1;
                                
                                foreach($timePeriods as $idx => $period) {
                                    $pStart = (int)substr($period['start'], 0, 2) * 60;
                                    $pEnd = (int)substr($period['end'], 0, 2) * 60;
                                    
                                    if ($sStart < $pEnd && $sEnd > $pStart) {
                                        if ($startCol === -1) $startCol = $idx;
                                        $colspan++;
                                    }
                                }
                                
                                return ['startCol' => $startCol, 'colspan' => $colspan];
                            }
                        @endphp
                        
                        <div class="table-responsive">
                            <table class="table timetable-grid mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">Siku</th>
                                        @foreach($timePeriods as $period)
                                            <th style="font-size: 11px;">{{ $period['label'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayLabels as $dayEng => $daySwahili)
                                        @php
                                            $daySlots = $slotsByDay[$dayEng] ?? collect([]);
                                            $skipCols = [];
                                            
                                            // Calculate colspan info for each slot
                                            $slotInfo = [];
                                            foreach($daySlots as $slot) {
                                                $info = getTeacherSlotColspan($slot, $timePeriods);
                                                if ($info['startCol'] >= 0) {
                                                    $slotInfo[$info['startCol']] = [
                                                        'slot' => $slot,
                                                        'colspan' => $info['colspan']
                                                    ];
                                                    for ($i = $info['startCol'] + 1; $i < $info['startCol'] + $info['colspan']; $i++) {
                                                        $skipCols[$i] = true;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td class="time-cell" style="background: #1e3c72; color: white; font-weight: 600;">
                                                {{ $daySwahili }}
                                            </td>
                                            @foreach($timePeriods as $colIdx => $period)
                                                @if(!isset($skipCols[$colIdx]))
                                                    @if(isset($slotInfo[$colIdx]))
                                                        @php
                                                            $slot = $slotInfo[$colIdx]['slot'];
                                                            $colspan = $slotInfo[$colIdx]['colspan'];
                                                        @endphp
                                                        <td colspan="{{ $colspan }}" style="padding: 6px;">
                                                            <div class="slot-item {{ $slot->slot_type }}">
                                                                <div class="slot-course">{{ $slot->course->code ?? 'N/A' }}</div>
                                                                <div class="slot-time">
                                                                    <i class="bx bx-time"></i>
                                                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                                </div>
                                                                @if($slot->venue)
                                                                    <div class="slot-venue">
                                                                        <i class="bx bx-map-pin"></i> {{ $slot->venue->code }}
                                                                    </div>
                                                                @endif
                                                                <div class="slot-program">
                                                                    {{ $slot->timetable->program->code ?? '' }} Y{{ $slot->timetable->year_of_study ?? '' }}
                                                                </div>
                                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                                    <span class="slot-type-badge">{{ $slot->slot_type }}</span>
                                                                    <button type="button" class="btn btn-sm btn-view-slot" 
                                                                        data-bs-toggle="modal" 
                                                                        data-bs-target="#viewSlotModal"
                                                                        data-slot-id="{{ $slot->id }}"
                                                                        data-course-code="{{ $slot->course->code ?? 'N/A' }}"
                                                                        data-course-name="{{ $slot->course->name ?? 'N/A' }}"
                                                                        data-day="{{ $slot->day_of_week }}"
                                                                        data-start="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}"
                                                                        data-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}"
                                                                        data-venue-code="{{ $slot->venue->code ?? '-' }}"
                                                                        data-venue-name="{{ $slot->venue->name ?? '-' }}"
                                                                        data-venue-capacity="{{ $slot->venue->capacity ?? '-' }}"
                                                                        data-slot-type="{{ $slot->slot_type }}"
                                                                        data-program="{{ $slot->timetable->program->name ?? '-' }}"
                                                                        data-program-code="{{ $slot->timetable->program->code ?? '-' }}"
                                                                        data-year="{{ $slot->timetable->year_of_study ?? '-' }}"
                                                                        data-semester="{{ $slot->timetable->semester->name ?? '-' }}"
                                                                        data-level="{{ $slot->timetable->level->name ?? '-' }}"
                                                                        title="View Details">
                                                                        <i class="bx bx-show"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    @else
                                                        <td></td>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Summary -->
        <div class="row mt-4">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-book-open me-2"></i>Course Summary
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table course-summary-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Course Name</th>
                                        <th class="text-center">Sessions</th>
                                        <th class="text-center">Hours/Week</th>
                                        <th>Programs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($courseSummary as $course)
                                        <tr>
                                            <td><strong>{{ $course['code'] }}</strong></td>
                                            <td>{{ Str::limit($course['name'], 35) }}</td>
                                            <td class="text-center">{{ $course['sessions'] }}</td>
                                            <td class="text-center">{{ number_format($course['hours'], 1) }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $course['programs'] }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                No courses assigned for this academic year.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($courseSummary->count() > 0)
                                    <tfoot class="table-light">
                                        <tr>
                                            <th colspan="2">Total</th>
                                            <th class="text-center">{{ $totalSlots }}</th>
                                            <th class="text-center">{{ number_format($totalHours, 1) }}</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header border-bottom">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-info-circle me-2"></i>Legend
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge" style="background: linear-gradient(135deg, #e3f2fd, #bbdefb); color: #1565c0; border-left: 3px solid #2196F3; padding: 8px 12px;">Lecture</span>
                            <span class="badge" style="background: linear-gradient(135deg, #e8f5e9, #c8e6c9); color: #2e7d32; border-left: 3px solid #4CAF50; padding: 8px 12px;">Tutorial</span>
                            <span class="badge" style="background: linear-gradient(135deg, #fff3e0, #ffe0b2); color: #ef6c00; border-left: 3px solid #FF9800; padding: 8px 12px;">Practical</span>
                            <span class="badge" style="background: linear-gradient(135deg, #e0f7fa, #b2ebf2); color: #00838f; border-left: 3px solid #00BCD4; padding: 8px 12px;">Lab</span>
                            <span class="badge" style="background: linear-gradient(135deg, #f5f5f5, #e0e0e0); color: #424242; border-left: 3px solid #9E9E9E; padding: 8px 12px;">Seminar</span>
                            <span class="badge" style="background: linear-gradient(135deg, #eceff1, #cfd8dc); color: #37474f; border-left: 3px solid #607D8B; padding: 8px 12px;">Workshop</span>
                            <span class="badge" style="background: linear-gradient(135deg, #ffebee, #ffcdd2); color: #c62828; border-left: 3px solid #F44336; padding: 8px 12px;">Exam</span>
                        </div>
                    </div>
                </div>

                @if($employee->email || $employee->phone_number)
                    <div class="card mt-3">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-user me-2"></i>Contact Info
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($employee->email)
                                <p class="mb-2">
                                    <i class="bx bx-envelope text-primary me-2"></i>
                                    <a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a>
                                </p>
                            @endif
                            @if($employee->phone_number)
                                <p class="mb-0">
                                    <i class="bx bx-phone text-success me-2"></i>
                                    <a href="tel:{{ $employee->phone_number }}">{{ $employee->phone_number }}</a>
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- View Slot Modal -->
<div class="modal fade" id="viewSlotModal" tabindex="-1" aria-labelledby="viewSlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <h5 class="modal-title" id="viewSlotModalLabel">
                    <i class="bx bx-info-circle me-2"></i>Slot Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Course Info Section -->
                <div class="p-3" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                    <h6 class="text-primary mb-2"><i class="bx bx-book me-2"></i>Course Information</h6>
                    <h5 class="mb-1 fw-bold" id="viewCourseCode"></h5>
                    <p class="mb-0 text-muted" id="viewCourseName"></p>
                </div>
                
                <div class="p-3">
                    <!-- Schedule Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-secondary mb-2"><i class="bx bx-calendar me-2"></i>Schedule</h6>
                        </div>
                        <div class="col-6">
                            <div class="info-item">
                                <small class="text-muted">Day</small>
                                <p class="mb-0 fw-semibold" id="viewDay"></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-item">
                                <small class="text-muted">Time</small>
                                <p class="mb-0 fw-semibold" id="viewTime"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Venue Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-secondary mb-2"><i class="bx bx-building me-2"></i>Venue</h6>
                        </div>
                        <div class="col-4">
                            <div class="info-item">
                                <small class="text-muted">Code</small>
                                <p class="mb-0 fw-semibold" id="viewVenueCode"></p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-item">
                                <small class="text-muted">Name</small>
                                <p class="mb-0 fw-semibold" id="viewVenueName"></p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="info-item">
                                <small class="text-muted">Capacity</small>
                                <p class="mb-0 fw-semibold" id="viewVenueCapacity"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Program Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-secondary mb-2"><i class="bx bx-graduation me-2"></i>Program Details</h6>
                        </div>
                        <div class="col-6">
                            <div class="info-item">
                                <small class="text-muted">Program</small>
                                <p class="mb-0 fw-semibold" id="viewProgram"></p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-item">
                                <small class="text-muted">Level</small>
                                <p class="mb-0 fw-semibold" id="viewLevel"></p>
                            </div>
                        </div>
                        <div class="col-6 mt-2">
                            <div class="info-item">
                                <small class="text-muted">Year of Study</small>
                                <p class="mb-0 fw-semibold" id="viewYear"></p>
                            </div>
                        </div>
                        <div class="col-6 mt-2">
                            <div class="info-item">
                                <small class="text-muted">Semester</small>
                                <p class="mb-0 fw-semibold" id="viewSemester"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Slot Type -->
                    <div class="text-center pt-2 border-top">
                        <small class="text-muted d-block mb-1">Session Type</small>
                        <span class="badge px-3 py-2" id="viewSlotType" style="font-size: 12px;"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View Slot Modal Handler
    document.querySelectorAll('.btn-view-slot').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            
            // Course Info
            document.getElementById('viewCourseCode').textContent = data.courseCode;
            document.getElementById('viewCourseName').textContent = data.courseName;
            
            // Schedule Info
            document.getElementById('viewDay').textContent = data.day;
            document.getElementById('viewTime').textContent = data.start + ' - ' + data.end;
            
            // Venue Info
            document.getElementById('viewVenueCode').textContent = data.venueCode;
            document.getElementById('viewVenueName').textContent = data.venueName;
            document.getElementById('viewVenueCapacity').textContent = data.venueCapacity;
            
            // Program Info
            document.getElementById('viewProgram').textContent = data.programCode + ' - ' + data.program;
            document.getElementById('viewLevel').textContent = data.level || '-';
            document.getElementById('viewYear').textContent = 'Year ' + data.year;
            document.getElementById('viewSemester').textContent = data.semester;
            
            // Slot Type Badge
            const slotTypeBadge = document.getElementById('viewSlotType');
            slotTypeBadge.textContent = data.slotType.charAt(0).toUpperCase() + data.slotType.slice(1);
            
            // Set badge color based on type
            const typeColors = {
                'lecture': { bg: '#2196F3', text: '#fff' },
                'tutorial': { bg: '#4CAF50', text: '#fff' },
                'practical': { bg: '#FF9800', text: '#fff' },
                'lab': { bg: '#00BCD4', text: '#fff' },
                'seminar': { bg: '#9E9E9E', text: '#fff' },
                'workshop': { bg: '#607D8B', text: '#fff' },
                'exam': { bg: '#F44336', text: '#fff' }
            };
            const colors = typeColors[data.slotType] || { bg: '#6c757d', text: '#fff' };
            slotTypeBadge.style.backgroundColor = colors.bg;
            slotTypeBadge.style.color = colors.text;
        });
    });
});
</script>
@endpush
