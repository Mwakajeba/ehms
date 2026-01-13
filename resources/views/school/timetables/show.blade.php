@extends('layouts.main')

@section('title', 'View Timetable')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Timetables', 'url' => route('school.timetables.index'), 'icon' => 'bx bx-time-five'],
            ['label' => 'View Timetable', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />
        <h6 class="mb-0 text-uppercase">VIEW TIMETABLE</h6>
        <hr />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="card-title d-flex align-items-center">
                                <div><i class="bx bx-time-five me-1 font-22 text-primary"></i></div>
                                <div>
                                    <h5 class="mb-0 text-primary">{{ $timetable->name }}</h5>
                                    <small class="text-muted">
                                        @if($timetable->academicYear)
                                            {{ $timetable->academicYear->year_name }}
                                        @endif
                                        @if($timetable->classe)
                                            | {{ $timetable->classe->name }}
                                        @endif
                                        @if($timetable->stream)
                                            - {{ $timetable->stream->name }}
                                        @endif
                                    </small>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('school.timetables.print', $timetable->hashid) }}" class="btn btn-info" target="_blank">
                                    <i class="bx bx-printer me-1"></i> Print Timetable
                                </a>
                                <a href="{{ route('school.timetables.edit', $timetable->hashid) }}" class="btn btn-warning">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.timetables.bulk-entries', $timetable->hashid) }}" class="btn btn-primary">
                                    <i class="bx bx-grid-alt me-1"></i> Click here to add subjects to each period
                                </a>
                                <a href="{{ route('school.timetables.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Timetable Information -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">Type</h6>
                                        <p class="mb-0">
                                            <span class="badge bg-primary">{{ ucfirst($timetable->timetable_type) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="card-title text-info">Status</h6>
                                        <p class="mb-0">
                                            @php
                                                $statusBadges = [
                                                    'draft' => 'secondary',
                                                    'reviewed' => 'info',
                                                    'approved' => 'success',
                                                    'published' => 'primary'
                                                ];
                                                $badge = $statusBadges[$timetable->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ ucfirst($timetable->status) }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="card-title text-success">Created By</h6>
                                        <p class="mb-0">
                                            {{ $timetable->creator ? $timetable->creator->name : 'N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-body">
                                        <h6 class="card-title text-warning">Created At</h6>
                                        <p class="mb-0">
                                            {{ $timetable->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($timetable->description)
                        <div class="alert alert-info">
                            <strong>Description:</strong> {{ $timetable->description }}
                        </div>
                        @endif

                        <!-- Timetable Display -->
                        @if($timetable->settings)
                            <div class="alert alert-info mb-3">
                                <strong><i class="bx bx-time me-2"></i>School Schedule:</strong>
                                <span>Starts at {{ $timetable->settings->school_start_time ? \Carbon\Carbon::parse($timetable->settings->school_start_time)->format('g:i A') : '8:00 AM' }}, 
                                Ends at {{ $timetable->settings->school_end_time ? \Carbon\Carbon::parse($timetable->settings->school_end_time)->format('g:i A') : '3:00 PM' }}, 
                                Period Duration: {{ $timetable->settings->period_duration_minutes ?? 40 }} minutes</span>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 10%;">Period / Time</th>
                                        <th style="width: 12.5%;">Monday</th>
                                        <th style="width: 12.5%;">Tuesday</th>
                                        <th style="width: 12.5%;">Wednesday</th>
                                        <th style="width: 12.5%;">Thursday</th>
                                        <th style="width: 12.5%;">Friday</th>
                                        <th style="width: 12.5%;">Saturday</th>
                                        <th style="width: 12.5%;">Sunday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        $maxPeriods = $timetable->periods->max('period_number') ?? 8;
                                        $entriesByDayPeriod = $timetable->entries->groupBy(function($entry) {
                                            return $entry->day_of_week . '-' . $entry->period_number;
                                        });
                                        
                                        // Group periods by day and period_number for easy lookup
                                        $periodsByDayPeriod = $timetable->periods->groupBy(function($period) {
                                            return $period->day_of_week . '-' . $period->period_number;
                                        });
                                    @endphp
                                    
                                    @for($period = 1; $period <= $maxPeriods; $period++)
                                        @php
                                            // Try to get period data from Monday first, then any day
                                            $periodData = null;
                                            foreach($days as $day) {
                                                $periodKey = $day . '-' . $period;
                                                if ($periodsByDayPeriod->has($periodKey)) {
                                                    $periodData = $periodsByDayPeriod->get($periodKey)->first();
                                                    break;
                                                }
                                            }
                                            
                                            // If we have period data, use it; otherwise calculate from settings
                                            if ($periodData) {
                                                $periodName = $periodData->period_name 
                                                    ? $periodData->period_name 
                                                    : 'Period ' . $period;
                                                
                                                // Format times from period data
                                                $startTimeObj = \Carbon\Carbon::parse($periodData->start_time);
                                                $endTimeObj = \Carbon\Carbon::parse($periodData->end_time);
                                                $periodTime = $startTimeObj->format('g:i') . ' - ' . $endTimeObj->format('g:i');
                                            } else {
                                                // Fallback: calculate from settings
                                                $periodDuration = $timetable->settings ? ($timetable->settings->period_duration_minutes ?? 40) : 40;
                                                $startTime = ($timetable->settings && $timetable->settings->school_start_time) 
                                                    ? \Carbon\Carbon::parse($timetable->settings->school_start_time) 
                                                    : \Carbon\Carbon::parse('08:00:00');
                                            $periodStart = $startTime->copy()->addMinutes(($period - 1) * $periodDuration);
                                            $periodEnd = $periodStart->copy()->addMinutes($periodDuration);
                                            $periodTime = $periodStart->format('g:i') . ' - ' . $periodEnd->format('g:i');
                                                $periodName = 'Period ' . $period;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center fw-bold">
                                                <div>{{ $periodName }}</div>
                                                <small class="text-muted">{{ $periodTime }}</small>
                                            </td>
                                            @foreach($days as $day)
                                                @php
                                                    $key = $day . '-' . $period;
                                                    $entryCollection = $entriesByDayPeriod->get($key);
                                                    $entry = $entryCollection ? $entryCollection->first() : null;
                                                @endphp
                                                <td>
                                                    @if($entry)
                                                        <div class="p-2 border rounded">
                                                            <strong>{{ $entry->subject->name ?? 'N/A' }}</strong>
                                                            @if($entry->subject->code)
                                                                <br><small class="text-muted">({{ $entry->subject->code }})</small>
                                                            @endif
                                                            @if($timetable->timetable_type == 'teacher' && $entry->classe)
                                                                <br><small class="text-primary">
                                                                    <i class="bx bx-group me-1"></i>
                                                                    Class: {{ $entry->classe->name }}
                                                                    @if($entry->stream)
                                                                        - {{ $entry->stream->name }}
                                                                    @endif
                                                                </small>
                                                            @endif
                                                            @if($entry->teacher)
                                                                <br><small class="text-info">
                                                                    <i class="bx bx-user me-1"></i>
                                                                    {{ $entry->teacher->first_name ?? '' }} {{ $entry->teacher->last_name ?? '' }}
                                                                </small>
                                                            @endif
                                                            @if($entry->room)
                                                                <br><small class="text-success">
                                                                    <i class="bx bx-building me-1"></i>
                                                                    {{ $entry->room->room_name }}
                                                                </small>
                                                            @endif
                                                            @if($entry->is_double_period)
                                                                <br><span class="badge bg-warning">Double Period</span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="p-2 text-muted text-center">-</div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        @if($timetable->entries->isEmpty())
                        <div class="alert alert-warning text-center mt-3">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>No timetable entries found.</strong> The table above shows empty periods (marked with "-"). 
                            <a href="{{ route('school.timetables.edit', $timetable->hashid) }}" class="alert-link">Click here to add subjects to each period</a>.
                            <hr class="my-2">
                            <small class="text-muted">
                                <strong>Example:</strong> To add Mathematics to Period 1 on Monday, go to Edit page and click "Add Entry", 
                                then select Monday, Period 1, and Mathematics subject.
                            </small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table th {
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .table td > div {
        min-height: 60px;
    }
    
    .border-primary {
        border-color: #0d6efd !important;
    }
    
    .border-info {
        border-color: #0dcaf0 !important;
    }
    
    .border-success {
        border-color: #198754 !important;
    }
    
    .border-warning {
        border-color: #ffc107 !important;
    }
</style>
@endpush

