@extends('layouts.main')

@section('title', 'Exam Schedule Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Schedules', 'url' => route('school.exam-schedules.index'), 'icon' => 'bx bx-calendar-event'],
            ['label' => 'Schedule Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-calendar-event me-1 font-22 text-success"></i>
                                <span class="h5 mb-0 text-success">Exam Schedule Details</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-info btn-sm" onclick="printTimetable()">
                                    <i class="bx bx-printer me-1"></i> Print Timetable
                                </button>
                                <a href="{{ route('school.exam-schedules.edit', $schedule->hashid) }}" class="btn btn-warning btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                                <a href="{{ route('school.exam-schedules.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Summary Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-primary text-center">
                                    <div class="card-body">
                                        <i class="bx bx-book fs-1 text-primary"></i>
                                        <h4 class="mt-2 mb-0">{{ $totalPapers ?? 0 }}</h4>
                                        <small class="text-muted">Total Papers</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info text-center">
                                    <div class="card-body">
                                        <i class="bx bx-time fs-1 text-info"></i>
                                        <h4 class="mt-2 mb-0">{{ $totalSessions ?? 0 }}</h4>
                                        <small class="text-muted">Total Sessions</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success text-center">
                                    <div class="card-body">
                                        <i class="bx bx-user fs-1 text-success"></i>
                                        <h4 class="mt-2 mb-0">{{ $totalStudents ?? 0 }}</h4>
                                        <small class="text-muted">Total Students</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning text-center">
                                    <div class="card-body">
                                        <i class="bx bx-calendar fs-1 text-warning"></i>
                                        <h4 class="mt-2 mb-0">
                                            @if($schedule->start_date && $schedule->end_date)
                                                {{ $schedule->start_date->diffInDays($schedule->end_date) + 1 }}
                                            @else
                                                0
                                            @endif
                                        </h4>
                                        <small class="text-muted">Exam Days</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule Information -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Basic Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Exam Name:</dt>
                                            <dd class="col-sm-7"><strong>{{ $schedule->exam_name }}</strong></dd>

                                            <dt class="col-sm-5">Exam Type:</dt>
                                            <dd class="col-sm-7">
                                                <span class="badge bg-info">{{ $schedule->examType->name ?? 'N/A' }}</span>
                                            </dd>

                                            <dt class="col-sm-5">Academic Year:</dt>
                                            <dd class="col-sm-7">{{ $schedule->academicYear->year_name ?? 'N/A' }}</dd>

                                            <dt class="col-sm-5">Term:</dt>
                                            <dd class="col-sm-7">{{ $schedule->term ?? 'N/A' }}</dd>

                                            <dt class="col-sm-5">Exam Type Category:</dt>
                                            <dd class="col-sm-7">
                                                <span class="badge bg-secondary">{{ ucfirst($schedule->exam_type_category) }}</span>
                                            </dd>

                                            @if(isset($classes) && $classes->count() > 0)
                                            <dt class="col-sm-5">Classes:</dt>
                                            <dd class="col-sm-7">
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($classes as $class)
                                                        <span class="badge bg-primary">{{ $class->name ?? 'N/A' }}</span>
                                                    @endforeach
                                                </div>
                                            </dd>
                                            @endif

                                            @if(isset($streams) && $streams->count() > 0)
                                            <dt class="col-sm-5">Streams:</dt>
                                            <dd class="col-sm-7">
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($streams as $stream)
                                                        <span class="badge bg-info">{{ $stream->name ?? 'N/A' }}</span>
                                                    @endforeach
                                                </div>
                                            </dd>
                                            @endif

                                            <dt class="col-sm-5">Status:</dt>
                                            <dd class="col-sm-7">{!! $schedule->getStatusBadge() !!}</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="bx bx-calendar me-1"></i> Schedule Dates</h6>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Start Date:</dt>
                                            <dd class="col-sm-7">
                                                <strong>{{ $schedule->start_date->format('l, M d, Y') }}</strong>
                                            </dd>

                                            <dt class="col-sm-5">End Date:</dt>
                                            <dd class="col-sm-7">
                                                <strong>{{ $schedule->end_date->format('l, M d, Y') }}</strong>
                                            </dd>

                                            <dt class="col-sm-5">Duration:</dt>
                                            <dd class="col-sm-7">
                                                {{ $schedule->start_date->diffInDays($schedule->end_date) + 1 }} day(s)
                                            </dd>

                                            <dt class="col-sm-5">Exam Days:</dt>
                                            <dd class="col-sm-7">
                                                @if($schedule->exam_days && count($schedule->exam_days) > 0)
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($schedule->exam_days as $day)
                                                            <span class="badge bg-light text-dark">{{ $day }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </dd>

                                            <dt class="col-sm-5">Half-Day Exams:</dt>
                                            <dd class="col-sm-7">
                                                @if($schedule->has_half_day_exams)
                                                    <span class="badge bg-warning">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </dd>

                                            <dt class="col-sm-5">Min Break:</dt>
                                            <dd class="col-sm-7">{{ $schedule->min_break_minutes }} minutes</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-user me-1"></i> Creation & Metadata</h6>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Created By:</dt>
                                            <dd class="col-sm-7">
                                                @if($schedule->creator)
                                                    {{ $schedule->creator->name ?? ($schedule->creator->first_name . ' ' . $schedule->creator->last_name) }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </dd>

                                            <dt class="col-sm-5">Created At:</dt>
                                            <dd class="col-sm-7">
                                                <small class="text-muted">
                                                    {{ $schedule->created_at->format('M d, Y') }}<br>
                                                    {{ $schedule->created_at->format('H:i:s') }}
                                                </small>
                                            </dd>

                                            <dt class="col-sm-5">Last Updated:</dt>
                                            <dd class="col-sm-7">
                                                <small class="text-muted">
                                                    {{ $schedule->updated_at->format('M d, Y') }}<br>
                                                    {{ $schedule->updated_at->format('H:i:s') }}
                                                </small>
                                            </dd>

                                            @if($schedule->company)
                                            <dt class="col-sm-5">Company:</dt>
                                            <dd class="col-sm-7">{{ $schedule->company->name ?? 'N/A' }}</dd>
                                            @endif

                                            @if($schedule->branch)
                                            <dt class="col-sm-5">Branch:</dt>
                                            <dd class="col-sm-7">{{ $schedule->branch->name ?? 'N/A' }}</dd>
                                            @endif

                                            @if(isset($papersByType) && $papersByType->count() > 0)
                                            <dt class="col-sm-5">Papers by Type:</dt>
                                            <dd class="col-sm-7">
                                                @foreach($papersByType as $type => $count)
                                                    <span class="badge bg-secondary me-1">
                                                        {{ ucfirst($type) }}: {{ $count }}
                                                    </span>
                                                @endforeach
                                            </dd>
                                            @endif
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($schedule->notes)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="bx bx-note me-1"></i> Notes</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $schedule->notes }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Sessions -->
                        @if($schedule->sessions && $schedule->sessions->count() > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="bx bx-time me-1"></i> Exam Sessions</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Session Name</th>
                                                        <th>Start Time</th>
                                                        <th>End Time</th>
                                                        <th>Half-Day</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($schedule->sessions as $session)
                                                    <tr>
                                                        <td>{{ $session->session_date->format('M d, Y') }}</td>
                                                        <td>{{ $session->session_name }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($session->start_time)->format('H:i') }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($session->end_time)->format('H:i') }}</td>
                                                        <td>
                                                            @if($session->is_half_day)
                                                                <span class="badge bg-warning">Yes</span>
                                                            @else
                                                                <span class="badge bg-secondary">No</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Scheduled Papers -->
                        @php
                            $papersCount = $schedule->papers ? $schedule->papers->count() : 0;
                        @endphp
                        @if($papersCount > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-book me-1"></i> Scheduled Papers</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Class</th>
                                                        <th>Stream</th>
                                                        <th>Type</th>
                                                        <th>Date</th>
                                                        <th>Start Time</th>
                                                        <th>End Time</th>
                                                        <th>Duration</th>
                                                        <th>Marks</th>
                                                        <th>Students</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($schedule->papers as $paper)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $paper->subject_name ?? 'N/A' }}</strong>
                                                            @if($paper->subject_code)
                                                                <br><small class="text-muted">Code: {{ $paper->subject_code }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $paper->classe->name ?? 'N/A' }}</td>
                                                        <td>{{ $paper->stream->name ?? 'All Streams' }}</td>
                                                        <td>
                                                            @php
                                                                $typeColors = [
                                                                    'theory' => 'primary',
                                                                    'practical' => 'warning',
                                                                    'oral' => 'info'
                                                                ];
                                                                $typeColor = $typeColors[$paper->paper_type] ?? 'secondary';
                                                            @endphp
                                                            <span class="badge bg-{{ $typeColor }}">
                                                                {{ ucfirst($paper->paper_type ?? 'N/A') }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if($paper->session && $paper->session->session_date)
                                                                {{ $paper->session->session_date->format('M d, Y') }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($paper->scheduled_start_time)
                                                                {{ \Carbon\Carbon::parse($paper->scheduled_start_time)->format('H:i') }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($paper->scheduled_end_time)
                                                                {{ \Carbon\Carbon::parse($paper->scheduled_end_time)->format('H:i') }}
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $paper->duration_minutes ?? 'N/A' }} min</td>
                                                        <td>{{ $paper->total_marks ?? 'N/A' }}</td>
                                                        <td>{{ $paper->number_of_students ?? 0 }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $paper->status === 'completed' ? 'success' : ($paper->status === 'ongoing' ? 'warning' : 'info') }}">
                                                                {{ ucfirst($paper->status ?? 'scheduled') }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="bx bx-info-circle me-1"></i>
                                    <strong>No papers have been scheduled yet.</strong><br>
                                    <small>To add papers to this exam schedule, please edit the schedule and use the "Search and Select Courses" section to add subjects with their dates, times, and types.</small>
                                </div>
                                @php
                                    $papersInDb = \App\Models\ExamSchedulePaper::where('exam_schedule_id', $schedule->id)->count();
                                @endphp
                                @if($papersInDb > 0)
                                <div class="alert alert-danger mt-2">
                                    <i class="bx bx-error me-1"></i>
                                    <strong>Warning:</strong> Found {{ $papersInDb }} paper(s) in the database for this schedule, but they are not loading via the relationship. 
                                    Please check the logs or contact support.
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Exam Timetable -->
                        @if($papersCount > 0)
                        <div class="row mb-4 print-timetable-section">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="bx bx-calendar-check me-1"></i> Exam Timetable</h6>
                                        <span class="badge bg-light text-dark">{{ $schedule->papers->count() }} Papers Scheduled</span>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover mb-0">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th width="18%">Subject</th>
                                                        <th width="15%">Date</th>
                                                        <th width="12%">Start Time</th>
                                                        <th width="12%">End Time</th>
                                                        <th width="10%">Type</th>
                                                        <th width="10%">Class</th>
                                                        <th width="8%">Stream</th>
                                                        <th width="8%">Duration</th>
                                                        <th width="7%">Created At</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                        $currentDate = null;
                                                    @endphp
                                                    @foreach($papersByDate as $date => $papers)
                                                        @foreach($papers->sortBy('scheduled_start_time') as $index => $paper)
                                                        <tr class="{{ $index === 0 && $date !== $currentDate ? 'table-group-separator' : '' }}">
                                                            <td>
                                                                <strong class="text-primary">{{ $paper->subject_name }}</strong>
                                                                @if($paper->subject_code)
                                                                    <br><small class="text-muted">Code: {{ $paper->subject_code }}</small>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($paper->session && $paper->session->session_date)
                                                                    <strong>{{ $paper->session->session_date->format('l') }}</strong><br>
                                                                    <span class="text-muted">{{ $paper->session->session_date->format('M d, Y') }}</span>
                                                                @else
                                                                    <span class="text-danger">Not scheduled</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($paper->scheduled_start_time)
                                                                    <span class="badge bg-success text-white">
                                                                        <i class="bx bx-time me-1"></i>
                                                                        {{ \Carbon\Carbon::parse($paper->scheduled_start_time)->format('H:i') }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($paper->scheduled_end_time)
                                                                    <span class="badge bg-danger text-white">
                                                                        <i class="bx bx-time me-1"></i>
                                                                        {{ \Carbon\Carbon::parse($paper->scheduled_end_time)->format('H:i') }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-muted">N/A</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @php
                                                                    $typeColors = [
                                                                        'theory' => 'primary',
                                                                        'practical' => 'warning',
                                                                        'oral' => 'info'
                                                                    ];
                                                                    $typeColor = $typeColors[$paper->paper_type] ?? 'secondary';
                                                                @endphp
                                                                <span class="badge bg-{{ $typeColor }}">
                                                                    {{ ucfirst($paper->paper_type ?? 'N/A') }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-secondary">{{ $paper->classe->name ?? 'N/A' }}</span>
                                                            </td>
                                                            <td>
                                                                @if($paper->stream)
                                                                    <span class="badge bg-light text-dark">{{ $paper->stream->name }}</span>
                                                                @else
                                                                    <span class="text-muted">All Streams</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="text-muted">{{ $paper->duration_minutes ?? 'N/A' }} min</span>
                                                            </td>
                                                            <td>
                                                                <small class="text-muted">
                                                                    <i class="bx bx-time me-1"></i>
                                                                    {{ $paper->created_at->format('M d, Y') }}<br>
                                                                    <span class="text-muted">{{ $paper->created_at->format('H:i') }}</span>
                                                                </small>
                                                            </td>
                                                        </tr>
                                                        @php
                                                            $currentDate = $date;
                                                        @endphp
                                                        @endforeach
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Invigilations -->
                        @if($schedule->papers && $schedule->papers->where('invigilations')->count() > 0)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="bx bx-user me-1"></i> Invigilations</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped table-hover">
                                                <thead class="table-dark">
                                                    <tr>
                                                        <th>Subject</th>
                                                        <th>Class</th>
                                                        <th>Invigilator</th>
                                                        <th>Role</th>
                                                        <th>Time</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($schedule->papers as $paper)
                                                        @if($paper->invigilations && $paper->invigilations->count() > 0)
                                                            @foreach($paper->invigilations as $invigilation)
                                                            <tr>
                                                                <td>{{ $paper->subject_name }}</td>
                                                                <td>{{ $paper->classe->name ?? 'N/A' }}</td>
                                                                <td>
                                                                    {{ $invigilation->invigilator->first_name ?? '' }} 
                                                                    {{ $invigilation->invigilator->last_name ?? '' }}
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $invigilation->role)) }}</span>
                                                                </td>
                                                                <td>
                                                                    {{ \Carbon\Carbon::parse($invigilation->assigned_start_time)->format('H:i') }} - 
                                                                    {{ \Carbon\Carbon::parse($invigilation->assigned_end_time)->format('H:i') }}
                                                                </td>
                                                            </tr>
                                                            @endforeach
                                                        @endif
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    .card-title {
        font-size: 1rem;
        font-weight: 600;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    dl.row dt {
        font-weight: 600;
        color: #495057;
    }

    dl.row dd {
        color: #212529;
    }

    .table-group-separator {
        border-top: 2px solid #dee2e6;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
    }

    /* Print Styles */
    @media print {
        body * {
            visibility: hidden;
        }
        
        .print-timetable-section,
        .print-timetable-section * {
            visibility: visible;
        }
        
        .print-timetable-section {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        
        .no-print {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .card-header {
            background-color: #f8f9fa !important;
            color: #000 !important;
            border-bottom: 2px solid #000 !important;
        }
        
        .table {
            border-collapse: collapse !important;
        }
        
        .table th,
        .table td {
            border: 1px solid #000 !important;
            padding: 8px !important;
        }
        
        .table thead th {
            background-color: #f8f9fa !important;
            color: #000 !important;
        }
        
        .badge {
            border: 1px solid #000 !important;
            padding: 4px 8px !important;
        }
        
        @page {
            margin: 1cm;
            size: A4 landscape;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function printTimetable() {
        // Create a new window for printing
        const printWindow = window.open('', '_blank');
        
        // Get company logo path
        const companyLogo = @json($schedule->company && $schedule->company->logo ? asset('storage/' . $schedule->company->logo) : null);
        const companyName = @json($schedule->company ? $schedule->company->name : 'School');
        
        // Get timetable data (prepared in controller)
        const papers = @json($papersForPrint ?? []);
        
        // Create print HTML using the provided format
        const printHTML = `
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Exam Timetable Report</title>
                <style>
                    body {
                        font-family: 'Arial', sans-serif;
                        margin: 0;
                        padding: 15px;
                        color: #333;
                        background: #fff;
                    }
                    
                    .header {
                        margin-bottom: 20px;
                        border-bottom: 3px solid #17a2b8;
                        padding-bottom: 15px;
                    }
                    
                    .header-content {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 20px;
                    }
                    
                    .logo-section {
                        flex-shrink: 0;
                    }
                    
                    .company-logo {
                        max-height: 80px;
                        max-width: 120px;
                        object-fit: contain;
                    }
                    
                    .title-section {
                        text-align: center;
                        flex-grow: 1;
                    }
                    
                    .header h1 {
                        color: #17a2b8;
                        margin: 0;
                        font-size: 24px;
                        font-weight: bold;
                    }
                    
                    .company-name {
                        color: #333;
                        margin: 5px 0;
                        font-size: 16px;
                        font-weight: 600;
                    }
                    
                    .header .subtitle {
                        color: #666;
                        margin: 5px 0 0 0;
                        font-size: 14px;
                    }
                    
                    .report-info {
                        background: #f8f9fa;
                        padding: 12px;
                        border-radius: 8px;
                        margin-bottom: 15px;
                        border-left: 4px solid #17a2b8;
                    }
                    
                    .report-info h3 {
                        margin: 0 0 10px 0;
                        color: #17a2b8;
                        font-size: 16px;
                    }
                    
                    .info-grid {
                        display: table;
                        width: 100%;
                    }
                    
                    .info-row {
                        display: table-row;
                    }
                    
                    .info-label {
                        display: table-cell;
                        font-weight: bold;
                        padding: 5px 15px 5px 0;
                        width: 120px;
                        color: #555;
                    }
                    
                    .info-value {
                        display: table-cell;
                        padding: 5px 0;
                        color: #333;
                    }
                    
                    .data-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 20px;
                        background: #fff;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        border-radius: 8px;
                        overflow: hidden;
                        table-layout: fixed;
                    }
                    
                    .data-table thead {
                        background: #17a2b8;
                        color: white;
                    }
                    
                    .data-table th {
                        padding: 8px 6px;
                        text-align: left;
                        font-weight: bold;
                        font-size: 10px;
                        text-transform: uppercase;
                        letter-spacing: 0.3px;
                        word-wrap: break-word;
                    }
                    
                    .data-table th:nth-child(1) { width: 15%; }
                    .data-table th:nth-child(2) { width: 12%; }
                    .data-table th:nth-child(3) { width: 10%; }
                    .data-table th:nth-child(4) { width: 8%; }
                    .data-table th:nth-child(5) { width: 8%; }
                    .data-table th:nth-child(6) { width: 10%; }
                    .data-table th:nth-child(7) { width: 10%; }
                    .data-table th:nth-child(8) { width: 10%; }
                    .data-table th:nth-child(9) { width: 10%; }
                    .data-table th:nth-child(10) { width: 7%; }
                    
                    .data-table td {
                        padding: 8px 6px;
                        border-bottom: 1px solid #dee2e6;
                        font-size: 9px;
                        word-wrap: break-word;
                    }
                    
                    .data-table tbody tr:hover {
                        background: #f8f9fa;
                    }
                    
                    .data-table tbody tr:last-child td {
                        border-bottom: none;
                    }
                    
                    .number {
                        text-align: right;
                        font-family: 'Courier New', monospace;
                    }
                    
                    .text-center {
                        text-align: center;
                    }
                    
                    .footer {
                        margin-top: 40px;
                        text-align: center;
                        color: #666;
                        font-size: 12px;
                        border-top: 1px solid #dee2e6;
                        padding-top: 20px;
                    }
                    
                    .no-data {
                        text-align: center;
                        padding: 40px;
                        color: #666;
                        font-style: italic;
                    }
                    
                    @media print {
                        @page {
                            margin: 1cm;
                            size: A4 landscape;
                        }
                        
                        body {
                            padding: 10px;
                        }
                        
                        .no-print {
                            display: none;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <div class="header-content">
                        ${companyLogo ? `<div class="logo-section">
                            <img src="${companyLogo}" alt="${companyName}" class="company-logo">
                        </div>` : ''}
                        <div class="title-section">
                            <h1>Exam Timetable Report</h1>
                            <div class="company-name">${companyName}</div>
                            <div class="subtitle">Generated on ${new Date().toLocaleString()}</div>
                        </div>
                    </div>
                </div>

                <div class="report-info">
                    <h3>Exam Schedule Information</h3>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Exam Name:</div>
                            <div class="info-value">{{ $schedule->exam_name }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Exam Type:</div>
                            <div class="info-value">{{ $schedule->examType->name ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Academic Year:</div>
                            <div class="info-value">{{ $schedule->academicYear->year_name ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Term:</div>
                            <div class="info-value">{{ $schedule->term ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Period:</div>
                            <div class="info-value">{{ $schedule->start_date->format('M d, Y') }} - {{ $schedule->end_date->format('M d, Y') }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Total Papers:</div>
                            <div class="info-value">{{ $totalPapers ?? 0 }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Total Students:</div>
                            <div class="info-value">{{ $totalStudents ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                ${papers.length > 0 ? `
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Type</th>
                                <th>Class</th>
                                <th>Stream</th>
                                <th>Duration</th>
                                <th>Marks</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${papers.map(function(paper) {
                                const subjectCode = paper.subject_code ? '<br><small style="color: #666;">Code: ' + paper.subject_code + '</small>' : '';
                                return '<tr>' +
                                    '<td><strong>' + paper.subject_name + '</strong>' + subjectCode + '</td>' +
                                    '<td>' + paper.date + '</td>' +
                                    '<td class="text-center">' + paper.start_time + '</td>' +
                                    '<td class="text-center">' + paper.end_time + '</td>' +
                                    '<td class="text-center">' + paper.paper_type + '</td>' +
                                    '<td>' + paper.class_name + '</td>' +
                                    '<td>' + paper.stream_name + '</td>' +
                                    '<td class="text-center">' + paper.duration + '</td>' +
                                    '<td class="number">' + paper.total_marks + '</td>' +
                                    '<td class="number">' + paper.number_of_students + '</td>' +
                                    '</tr>';
                            }).join('')}
                        </tbody>
                    </table>
                ` : `
                    <div class="no-data">
                        <h3>No Data Available</h3>
                        <p>No papers have been scheduled for this exam.</p>
                    </div>
                `}

                <div class="footer">
                    <p>This report was generated by Smart Accounting System</p>
                    <p>Report ID: ${Math.random().toString(36).substring(2, 15).toUpperCase()}</p>
                    <p style="font-size: 10px; margin-top: 5px;">Exam timetable showing all scheduled papers with dates, times, and details.</p>
                </div>
            </body>
            </html>
        `;
        
        printWindow.document.write(printHTML);
        printWindow.document.close();
        
        // Wait for content to load, then print
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 250);
        };
    }
</script>
@endpush

