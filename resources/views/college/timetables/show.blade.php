@extends('layouts.main')

@section('title', 'Timetable - ' . $timetable->name)

@push('styles')
<style>
    /* Clean Timetable Container */
    .timetable-wrapper {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    /* Title Header */
    .timetable-header {
        background: #2c3e50;
        color: #fff;
        padding: 15px 20px;
        text-align: center;
    }
    
    .timetable-header h4 {
        margin: 0 0 5px 0;
        font-size: 1.1rem;
        font-weight: 600;
        letter-spacing: 1px;
    }
    
    .timetable-header p {
        margin: 0;
        font-size: 0.85rem;
        opacity: 0.9;
    }
    
    /* Main Timetable Table */
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    
    .timetable-table th,
    .timetable-table td {
        border: 1px solid #ddd;
        text-align: center;
        vertical-align: middle;
        padding: 0;
    }
    
    /* Header Row */
    .timetable-table thead th {
        background: #34495e;
        color: #fff;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 10px 5px;
        white-space: nowrap;
    }
    
    .timetable-table thead th.day-header {
        width: 90px;
        background: #2c3e50;
    }
    
    /* Day Column */
    .timetable-table tbody td.day-col {
        background: #ecf0f1;
        font-weight: 700;
        font-size: 0.8rem;
        color: #2c3e50;
        padding: 8px 5px;
        width: 90px;
    }
    
    /* Time Slot Cells */
    .timetable-table tbody td.time-slot {
        height: 70px;
        padding: 4px;
        background: #fff;
        vertical-align: top;
    }
    
    .timetable-table tbody td.time-slot:hover {
        background: #f8f9fa;
    }
    
    /* Slot Content Box */
    .slot-box {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        color: #fff;
        border-radius: 4px;
        padding: 6px 8px;
        height: 100%;
        min-height: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
        position: relative;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .slot-box:hover {
        transform: scale(1.02);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10;
    }
    
    .slot-box .course-title {
        font-weight: 700;
        font-size: 0.75rem;
        line-height: 1.2;
        margin-bottom: 3px;
    }
    
    .slot-box .slot-time-display {
        font-size: 0.65rem;
        opacity: 0.9;
        margin-bottom: 2px;
        background: rgba(255,255,255,0.2);
        padding: 1px 6px;
        border-radius: 10px;
        display: inline-block;
    }
    
    .slot-box .instructor {
        font-size: 0.7rem;
        opacity: 0.9;
    }
    
    .slot-box .venue-badge {
        position: absolute;
        top: 3px;
        right: 3px;
        font-size: 0.6rem;
        background: rgba(255,255,255,0.25);
        padding: 1px 4px;
        border-radius: 3px;
    }
    
    /* Slot Actions */
    .slot-actions {
        position: absolute;
        bottom: 3px;
        right: 3px;
        display: none;
    }
    
    .slot-box:hover .slot-actions {
        display: flex;
        gap: 3px;
    }
    
    .slot-actions .btn {
        padding: 2px 5px;
        font-size: 0.65rem;
        border-radius: 3px;
    }
    
    .slot-actions .btn-info {
        background: rgba(255,255,255,0.9);
        color: #17a2b8;
        border: none;
    }
    
    .slot-actions .btn-info:hover {
        background: #17a2b8;
        color: #fff;
    }
    
    /* Different colors for variety */
    .slot-box.color-1 { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
    .slot-box.color-2 { background: linear-gradient(135deg, #27ae60 0%, #229954 100%); }
    .slot-box.color-3 { background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); }
    .slot-box.color-4 { background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); }
    .slot-box.color-5 { background: linear-gradient(135deg, #f39c12 0%, #d68910 100%); }
    .slot-box.color-6 { background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%); }
    .slot-box.color-7 { background: linear-gradient(135deg, #e67e22 0%, #ca6f1e 100%); }
    .slot-box.color-8 { background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); }
    
    /* Empty Cell */
    .empty-slot {
        color: #bdc3c7;
        font-size: 0.7rem;
    }
    
    /* Print Styles */
    @media print {
        /* Hide everything not needed for print */
        .no-print,
        .sidebar-wrapper,
        .sidebar,
        .vertical-menu,
        .navbar-header,
        .page-title-box,
        .main-header,
        .subscription-warning-bar,
        header,
        nav,
        footer,
        .footer,
        #sidebar-menu,
        .left-side-menu,
        .topnav,
        .navbar,
        .breadcrumb,
        .btn,
        button,
        .slot-actions,
        .card:not(.print-card),
        .alert,
        .toggle-icon { 
            display: none !important; 
        }
        
        /* Reset page layout - Full width */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            width: 100% !important;
            min-width: 100% !important;
            font-size: 10px !important;
        }
        
        .wrapper {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        
        .page-content,
        .main-content,
        .content-page {
            margin: 0 !important;
            padding: 5px !important;
            width: 100% !important;
            max-width: 100% !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Style the timetable for print - Full width */
        .timetable-wrapper {
            box-shadow: none !important;
            border: 2px solid #333 !important;
            margin: 0 !important;
            width: 100% !important;
            page-break-inside: avoid;
        }
        
        .timetable-header {
            background: #2c3e50 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color: white !important;
            padding: 10px 15px !important;
        }
        
        .timetable-header h4 {
            color: white !important;
            font-size: 14px !important;
            margin-bottom: 3px !important;
        }
        
        .timetable-header p {
            color: white !important;
            font-size: 11px !important;
            margin: 0 !important;
        }
        
        /* Table - Full width with better sizing */
        .timetable-table {
            width: 100% !important;
            table-layout: fixed !important;
            font-size: 9px !important;
        }
        
        .timetable-table thead th {
            background: #34495e !important;
            color: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            padding: 8px 3px !important;
            font-size: 8px !important;
            font-weight: 600 !important;
        }
        
        .timetable-table thead th.day-header {
            background: #2c3e50 !important;
            width: 70px !important;
            min-width: 70px !important;
        }
        
        .timetable-table tbody td.day-col {
            background: #2c3e50 !important;
            color: white !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-size: 9px !important;
            font-weight: 600 !important;
            padding: 5px !important;
            width: 70px !important;
        }
        
        .timetable-table tbody td.time-slot {
            padding: 3px !important;
            height: 55px !important;
            vertical-align: middle !important;
        }
        
        /* Slot boxes in print */
        .slot-box {
            transform: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color: white !important;
            padding: 4px 5px !important;
            min-height: 45px !important;
            border-radius: 3px !important;
        }
        
        .slot-box .course-title {
            font-size: 8px !important;
            font-weight: 700 !important;
            margin-bottom: 2px !important;
        }
        
        .slot-box .slot-time-display {
            font-size: 7px !important;
            padding: 1px 4px !important;
        }
        
        .slot-box .venue,
        .slot-box .instructor {
            font-size: 7px !important;
        }
        
        .slot-box.lecture { background: #3498db !important; }
        .slot-box.tutorial { background: #27ae60 !important; }
        .slot-box.practical { background: #e67e22 !important; }
        .slot-box.lab { background: #9b59b6 !important; }
        .slot-box.seminar { background: #1abc9c !important; }
        .slot-box.workshop { background: #34495e !important; }
        .slot-box.exam { background: #e74c3c !important; }
        
        /* Print header info */
        .print-header {
            display: block !important;
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        
        .print-header h2 {
            font-size: 16px !important;
            margin: 0 !important;
        }
        
        .print-header p {
            font-size: 11px !important;
            margin: 3px 0 !important;
        }
        
        /* Page settings - A4 Landscape */
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
    }
    
    /* Hide print header on screen */
    .print-header {
        display: none;
    }
    
    /* Responsive */
    @media (max-width: 1200px) {
        .timetable-table thead th,
        .timetable-table tbody td {
            font-size: 0.7rem;
        }
        .slot-box .course-title {
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@section('content')
<div class="page-content" style="margin-top: 70px; margin-left: 235px; margin-right: 20px;">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row mb-3 no-print">
            <div class="col-12">
                <nav class="d-flex align-items-center flex-wrap gap-1">
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bx bx-home-alt me-1"></i> Dashboard
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bx bx-book-reader me-1"></i> College
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <a href="{{ route('college.timetables.index') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bx bx-calendar-alt me-1"></i> Timetables
                    </a>
                    <i class="bx bx-chevron-right text-muted"></i>
                    <span class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="bx bx-show me-1"></i> View
                    </span>
                </nav>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mb-3 no-print">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <a href="{{ route('college.timetables.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Back
                    </a>
                    @if($timetable->status == 'draft')
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSlotModal">
                            <i class="bx bx-plus me-1"></i> Add Slot
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="publishTimetable()">
                            <i class="bx bx-check me-1"></i> Publish
                        </button>
                    @endif
                    @if($timetable->status == 'published')
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="archiveTimetable()">
                            <i class="bx bx-archive me-1"></i> Archive
                        </button>
                    @endif
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="window.print()">
                        <i class="bx bx-printer me-1"></i> Print
                    </button>
                    <a href="{{ route('college.timetables.export-pdf', $timetable) }}" class="btn btn-outline-danger btn-sm">
                        <i class="bx bx-file-blank me-1"></i> PDF
                    </a>
                    <span class="ms-auto">
                        @if($timetable->status == 'draft')
                            <span class="badge bg-warning px-3 py-2">Draft</span>
                        @elseif($timetable->status == 'published')
                            <span class="badge bg-success px-3 py-2">Published</span>
                        @else
                            <span class="badge bg-secondary px-3 py-2">Archived</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Print Header (only visible when printing) -->
        <div class="print-header">
            <h2 style="margin: 0; font-size: 18px; font-weight: bold;">{{ $timetable->program->name }}</h2>
            <p style="margin: 5px 0; font-size: 14px;">{{ $timetable->name }} | {{ $timetable->semester->name }} | {{ $timetable->academicYear->name }}</p>
            <p style="margin: 0; font-size: 12px; color: #666;">Year {{ $timetable->year_of_study }} | Printed: {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <!-- Timetable -->
        <div class="timetable-wrapper">
            <div class="timetable-header">
                <h4 style="color: whitesmoke;">RATIBA YA WIKI (WEEKLY TIMETABLE)</h4>
                <p>{{ $timetable->program->name }} | {{ $timetable->semester->name }} | {{ $timetable->academicYear->name }}</p>
            </div>
            
            @php
                // Time periods for the table (7:00 AM to 9:00 PM)
                $timePeriods = [
                    ['start' => '07:00', 'end' => '08:00', 'label' => '7:00-8:00'],
                    ['start' => '08:00', 'end' => '09:00', 'label' => '8:00-9:00'],
                    ['start' => '09:00', 'end' => '10:00', 'label' => '9:00-10:00'],
                    ['start' => '10:00', 'end' => '11:00', 'label' => '10:00-11:00'],
                    ['start' => '11:00', 'end' => '12:00', 'label' => '11:00-12:00'],
                    ['start' => '12:00', 'end' => '13:00', 'label' => '12:00-1:00'],
                    ['start' => '13:00', 'end' => '14:00', 'label' => '1:00-2:00'],
                    ['start' => '14:00', 'end' => '15:00', 'label' => '2:00-3:00'],
                    ['start' => '15:00', 'end' => '16:00', 'label' => '3:00-4:00'],
                    ['start' => '16:00', 'end' => '17:00', 'label' => '4:00-5:00'],
                    ['start' => '17:00', 'end' => '18:00', 'label' => '5:00-6:00'],
                    ['start' => '18:00', 'end' => '19:00', 'label' => '6:00-7:00'],
                    ['start' => '19:00', 'end' => '20:00', 'label' => '7:00-8:00 PM'],
                    ['start' => '20:00', 'end' => '21:00', 'label' => '8:00-9:00 PM'],
                ];
                
                // Days mapping
                $dayLabels = [
                    'Monday' => 'Jumatatu',
                    'Tuesday' => 'Jumanne', 
                    'Wednesday' => 'Jumatano',
                    'Thursday' => 'Alhamisi',
                    'Friday' => 'Ijumaa',
                    'Saturday' => 'Jumamosi',
                ];
                
                // Assign colors to courses
                $courseColors = [];
                $colorIndex = 1;
                foreach($timetable->slots as $slot) {
                    if (!isset($courseColors[$slot->course_id])) {
                        $courseColors[$slot->course_id] = 'color-' . (($colorIndex++ % 8) + 1);
                    }
                }
                
                // Pre-calculate slot positions for colspan
                function getSlotColspan($slot, $timePeriods) {
                    $sStartH = (int)substr($slot->start_time, 0, 2);
                    $sStartM = (int)substr($slot->start_time, 3, 2);
                    $sEndH = (int)substr($slot->end_time, 0, 2);
                    $sEndM = (int)substr($slot->end_time, 3, 2);
                    $sStart = $sStartH * 60 + $sStartM;
                    $sEnd = $sEndH * 60 + $sEndM;
                    
                    $colspan = 0;
                    $startCol = -1;
                    
                    foreach($timePeriods as $idx => $period) {
                        $pStart = (int)substr($period['start'], 0, 2) * 60 + (int)substr($period['start'], 3, 2);
                        $pEnd = (int)substr($period['end'], 0, 2) * 60 + (int)substr($period['end'], 3, 2);
                        
                        if ($sStart < $pEnd && $sEnd > $pStart) {
                            if ($startCol === -1) $startCol = $idx;
                            $colspan++;
                        }
                    }
                    
                    return ['startCol' => $startCol, 'colspan' => $colspan];
                }
            @endphp
            
            <div class="table-responsive">
                <table class="timetable-table">
                    <thead>
                        <tr>
                            <th class="day-header">Siku</th>
                            @foreach($timePeriods as $period)
                                <th>{{ $period['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dayLabels as $dayEng => $daySwahili)
                            @php
                                $daySlots = $slotsByDay[$dayEng] ?? collect([]);
                                $skipCols = []; // Track which columns to skip due to colspan
                                
                                // Calculate colspan info for each slot in this day
                                $slotInfo = [];
                                foreach($daySlots as $slot) {
                                    $info = getSlotColspan($slot, $timePeriods);
                                    if ($info['startCol'] >= 0) {
                                        $slotInfo[$info['startCol']] = [
                                            'slot' => $slot,
                                            'colspan' => $info['colspan']
                                        ];
                                        // Mark columns to skip
                                        for ($i = $info['startCol'] + 1; $i < $info['startCol'] + $info['colspan']; $i++) {
                                            $skipCols[$i] = true;
                                        }
                                    }
                                }
                            @endphp
                            <tr>
                                <td class="day-col">{{ $daySwahili }}</td>
                                @foreach($timePeriods as $colIdx => $period)
                                    @if(!isset($skipCols[$colIdx]))
                                        @if(isset($slotInfo[$colIdx]))
                                            @php
                                                $cellSlot = $slotInfo[$colIdx]['slot'];
                                                $colspan = $slotInfo[$colIdx]['colspan'];
                                            @endphp
                                            <td class="time-slot" colspan="{{ $colspan }}">
                                                <div class="slot-box {{ $courseColors[$cellSlot->course_id] ?? 'color-1' }}">
                                                    @if($cellSlot->venue)
                                                        <span class="venue-badge">{{ $cellSlot->venue->code ?? '' }}</span>
                                                    @endif
                                                    <div class="course-title">{{ $cellSlot->course->name ?? 'N/A' }}</div>
                                                    <div class="slot-time-display">
                                                        {{ \Carbon\Carbon::parse($cellSlot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($cellSlot->end_time)->format('H:i') }}
                                                    </div>
                                                    <div class="instructor">
                                                        @if($cellSlot->instructor)
                                                            {{ $cellSlot->instructor->first_name ?? '' }} {{ substr($cellSlot->instructor->last_name ?? '', 0, 1) }}.
                                                        @endif
                                                    </div>
                                                    <div class="slot-actions">
                                                        <button class="btn btn-info btn-sm" onclick="viewSlot({{ $cellSlot->id }})" title="View Details"><i class="bx bx-show"></i></button>
                                                        @if($timetable->status == 'draft')
                                                            <button class="btn btn-light btn-sm" onclick="editSlot({{ $cellSlot->id }})" title="Edit"><i class="bx bx-edit"></i></button>
                                                            <button class="btn btn-danger btn-sm" onclick="deleteSlot({{ $cellSlot->id }})" title="Delete"><i class="bx bx-trash"></i></button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                        @else
                                            <td class="time-slot"></td>
                                        @endif
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="row no-print">
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Info</h6>
                    </div>
                    <div class="card-body py-2">
                        <table class="table table-sm table-borderless mb-0 small">
                            <tr><td class="text-muted">Programme:</td><td class="fw-bold">{{ $timetable->program->name }}</td></tr>
                            <tr><td class="text-muted">Level:</td><td class="fw-bold">Year {{ $timetable->year_of_study }}</td></tr>
                            <tr><td class="text-muted">Semester:</td><td class="fw-bold">{{ $timetable->semester->name }}</td></tr>
                            <tr><td class="text-muted">Year:</td><td class="fw-bold">{{ $timetable->academicYear->name }}</td></tr>
                            <tr><td class="text-muted">Total Slots:</td><td class="fw-bold">{{ $timetable->slots->count() }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-success text-white py-2">
                        <h6 class="mb-0"><i class="bx bx-book me-1"></i> Courses ({{ $timetable->slots->unique('course_id')->count() }})</h6>
                    </div>
                    <div class="card-body py-2">
                        <ul class="list-unstyled mb-0 small">
                            @foreach($timetable->slots->unique('course_id') as $slot)
                                <li class="mb-1">
                                    <span class="badge {{ $courseColors[$slot->course_id] ?? 'bg-secondary' }} me-1" style="width:12px;height:12px;display:inline-block;border-radius:2px;"></span>
                                    <strong>{{ $slot->course->code ?? '' }}</strong> - {{ Str::limit($slot->course->name ?? '', 25) }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="mb-0"><i class="bx bx-user me-1"></i> Instructors</h6>
                    </div>
                    <div class="card-body py-2">
                        <ul class="list-unstyled mb-0 small">
                            @php $assignedInstructors = $timetable->slots->whereNotNull('instructor_id')->unique('instructor_id'); @endphp
                            @forelse($assignedInstructors as $slot)
                                <li class="mb-1">• {{ $slot->instructor->full_name ?? 'N/A' }}</li>
                            @empty
                                <li class="text-muted">No instructors assigned</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Slots Table -->
        <div class="card no-print">
            <div class="card-header d-flex justify-content-between align-items-center py-2">
                <h6 class="mb-0"><i class="bx bx-list-ul me-1"></i> All Time Slots</h6>
                @if($timetable->status == 'draft')
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSlotModal">
                        <i class="bx bx-plus me-1"></i> Add
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Course</th>
                                <th>Venue</th>
                                <th>Instructor</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timetable->slots->sortBy(['day_of_week', 'start_time']) as $slot)
                                <tr>
                                    <td>{{ $slot->day_of_week }}</td>
                                    <td>{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</td>
                                    <td><strong>{{ $slot->course->code ?? '' }}</strong> {{ $slot->course->name ?? '' }}</td>
                                    <td>{{ $slot->venue->code ?? '-' }}</td>
                                    <td>{{ $slot->instructor->full_name ?? '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-info" onclick="viewSlot({{ $slot->id }})" title="View"><i class="bx bx-show"></i></button>
                                        @if($timetable->status == 'draft')
                                            <button class="btn btn-sm btn-outline-primary" onclick="editSlot({{ $slot->id }})" title="Edit"><i class="bx bx-edit"></i></button>
                                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSlot({{ $slot->id }})" title="Delete"><i class="bx bx-trash"></i></button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4 text-muted">No slots added yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Slot Modal -->
<div class="modal fade" id="addSlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient text-white py-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="bx bx-plus-circle me-2"></i>Add New Time Slot
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSlotForm">
                <div class="modal-body p-3" style="max-height: 65vh; overflow-y: auto;">
                    <div class="row g-3">
                        <!-- Course -->
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-book text-primary me-1"></i>Course <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="addCourse" name="course_id" required>
                                <option value="">-- Select Course --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Day -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-calendar text-success me-1"></i>Day <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="addDay" name="day_of_week" required>
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Start Time -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-time text-info me-1"></i>Start Time <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control form-control-sm" id="addStartTime" name="start_time" value="08:00" required>
                        </div>
                        <!-- End Time -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-time-five text-warning me-1"></i>End Time <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control form-control-sm" id="addEndTime" name="end_time" value="09:00" required>
                        </div>
                        <!-- Duration Display -->
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="w-100 p-2 bg-light rounded text-center">
                                <small class="text-muted"><i class="bx bx-stopwatch me-1"></i><strong id="addDurationText">1 hour</strong></small>
                            </div>
                        </div>
                        <!-- Venue -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-building text-secondary me-1"></i>Venue
                            </label>
                            <select class="form-select form-select-sm" id="addVenue" name="venue_id">
                                <option value="">-- Select Venue --</option>
                                @foreach($venues as $venue)
                                    <option value="{{ $venue->id }}">{{ $venue->code }} - {{ $venue->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Instructor -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-user text-primary me-1"></i>Instructor
                            </label>
                            <select class="form-select form-select-sm" id="addInstructor" name="instructor_id">
                                <option value="">-- Select Instructor --</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}">{{ $instructor->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Slot Type -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-tag text-danger me-1"></i>Slot Type
                            </label>
                            <select class="form-select form-select-sm" id="addSlotType" name="slot_type">
                                <option value="lecture">📚 Lecture</option>
                                <option value="tutorial">📝 Tutorial</option>
                                <option value="practical">🔬 Practical</option>
                                <option value="lab">💻 Laboratory</option>
                                <option value="seminar">🎤 Seminar</option>
                            </select>
                        </div>
                        <!-- Group -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-group text-success me-1"></i>Group
                            </label>
                            <input type="text" class="form-control form-control-sm" id="addGroup" name="group_name" placeholder="e.g., Group A">
                        </div>
                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-note text-muted me-1"></i>Notes
                            </label>
                            <textarea class="form-control form-control-sm" id="addNotes" name="notes" rows="1" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="bx bx-plus-circle me-1"></i> Add Slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Slot Modal -->
<div class="modal fade" id="editSlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient text-white py-2" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="bx bx-edit me-2"></i>Edit Time Slot
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSlotForm">
                <input type="hidden" id="editSlotId">
                <div class="modal-body p-3" style="max-height: 65vh; overflow-y: auto;">
                    <div class="row g-3">
                        <!-- Course -->
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-book text-primary me-1"></i>Course <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="editCourse" name="course_id" required>
                                <option value="">-- Select Course --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Day -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-calendar text-success me-1"></i>Day <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-sm" id="editDay" name="day_of_week" required>
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Start Time -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-time text-info me-1"></i>Start Time <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control form-control-sm" id="editStartTime" name="start_time" required>
                        </div>
                        <!-- End Time -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-time-five text-warning me-1"></i>End Time <span class="text-danger">*</span>
                            </label>
                            <input type="time" class="form-control form-control-sm" id="editEndTime" name="end_time" required>
                        </div>
                        <!-- Duration Display -->
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="w-100 p-2 bg-light rounded text-center">
                                <small class="text-muted"><i class="bx bx-stopwatch me-1"></i><strong id="editDurationText">-</strong></small>
                            </div>
                        </div>
                        <!-- Venue -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-building text-secondary me-1"></i>Venue
                            </label>
                            <select class="form-select form-select-sm" id="editVenue" name="venue_id">
                                <option value="">-- Select Venue --</option>
                                @foreach($venues as $venue)
                                    <option value="{{ $venue->id }}">{{ $venue->code }} - {{ $venue->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Instructor -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-user text-primary me-1"></i>Instructor
                            </label>
                            <select class="form-select form-select-sm" id="editInstructor" name="instructor_id">
                                <option value="">-- Select Instructor --</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}">{{ $instructor->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Slot Type -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-tag text-danger me-1"></i>Slot Type
                            </label>
                            <select class="form-select form-select-sm" id="editSlotType" name="slot_type">
                                <option value="lecture">📚 Lecture</option>
                                <option value="tutorial">📝 Tutorial</option>
                                <option value="practical">🔬 Practical</option>
                                <option value="lab">💻 Laboratory</option>
                                <option value="seminar">🎤 Seminar</option>
                            </select>
                        </div>
                        <!-- Group -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-group text-success me-1"></i>Group
                            </label>
                            <input type="text" class="form-control form-control-sm" id="editGroup" name="group_name" placeholder="e.g., Group A">
                        </div>
                        <!-- Notes -->
                        <div class="col-12">
                            <label class="form-label small fw-semibold mb-1">
                                <i class="bx bx-note text-muted me-1"></i>Notes
                            </label>
                            <textarea class="form-control form-control-sm" id="editNotes" name="notes" rows="1" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border: none;">
                        <i class="bx bx-save me-1"></i> Update Slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Slot Modal -->
<div class="modal fade" id="viewSlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bx bx-info-circle me-2"></i>Slot Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white py-2">
                                <h6 class="mb-0"><i class="bx bx-book me-1"></i> Course Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="35%">Course Code:</td>
                                        <td class="fw-bold" id="viewCourseCode">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Course Name:</td>
                                        <td class="fw-bold" id="viewCourseName">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Credit Hours:</td>
                                        <td class="fw-bold" id="viewCreditHours">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white py-2">
                                <h6 class="mb-0"><i class="bx bx-time me-1"></i> Schedule</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="35%">Day:</td>
                                        <td class="fw-bold" id="viewDay">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Start Time:</td>
                                        <td class="fw-bold" id="viewStartTime">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">End Time:</td>
                                        <td class="fw-bold" id="viewEndTime">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Duration:</td>
                                        <td class="fw-bold" id="viewDuration">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-warning py-2">
                                <h6 class="mb-0"><i class="bx bx-map me-1"></i> Venue</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="35%">Venue Code:</td>
                                        <td class="fw-bold" id="viewVenueCode">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Venue Name:</td>
                                        <td class="fw-bold" id="viewVenueName">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Capacity:</td>
                                        <td class="fw-bold" id="viewVenueCapacity">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white py-2">
                                <h6 class="mb-0"><i class="bx bx-user me-1"></i> Instructor</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="35%">Name:</td>
                                        <td class="fw-bold" id="viewInstructorName">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email:</td>
                                        <td class="fw-bold" id="viewInstructorEmail">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Phone:</td>
                                        <td class="fw-bold" id="viewInstructorPhone">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-dark text-white py-2">
                                <h6 class="mb-0"><i class="bx bx-detail me-1"></i> Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td class="text-muted" width="20%">Slot Type:</td>
                                        <td class="fw-bold" id="viewSlotType">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Group:</td>
                                        <td class="fw-bold" id="viewGroup">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Notes:</td>
                                        <td id="viewNotes">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Status:</td>
                                        <td id="viewStatus">-</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @if($timetable->status == 'draft')
                    <button type="button" class="btn btn-primary" id="viewEditBtn"><i class="bx bx-edit me-1"></i> Edit</button>
                    <button type="button" class="btn btn-danger" id="viewDeleteBtn"><i class="bx bx-trash me-1"></i> Delete</button>
                @endif
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var timetableId = {{ $timetable->id }};
var currentViewSlotId = null;

// View Slot Function
function viewSlot(id) {
    currentViewSlotId = id;
    $.get('/college/timetables/' + timetableId + '/slots/' + id, function(slot) {
        // Course Info
        $('#viewCourseCode').text(slot.course?.code || '-');
        $('#viewCourseName').text(slot.course?.name || '-');
        $('#viewCreditHours').text(slot.course?.credit_hours || '-');
        
        // Schedule Info
        $('#viewDay').text(slot.day_of_week || '-');
        $('#viewStartTime').text(formatTime(slot.start_time));
        $('#viewEndTime').text(formatTime(slot.end_time));
        $('#viewDuration').text(calculateDuration(slot.start_time, slot.end_time));
        
        // Venue Info
        $('#viewVenueCode').text(slot.venue?.code || '-');
        $('#viewVenueName').text(slot.venue?.name || '-');
        $('#viewVenueCapacity').text(slot.venue?.capacity ? slot.venue.capacity + ' seats' : '-');
        
        // Instructor Info
        if (slot.instructor) {
            $('#viewInstructorName').text((slot.instructor.first_name || '') + ' ' + (slot.instructor.last_name || ''));
            $('#viewInstructorEmail').text(slot.instructor.email || '-');
            $('#viewInstructorPhone').text(slot.instructor.phone_number || '-');
        } else {
            $('#viewInstructorName').text('-');
            $('#viewInstructorEmail').text('-');
            $('#viewInstructorPhone').text('-');
        }
        
        // Additional Info
        var slotTypes = {
            'lecture': 'Lecture',
            'tutorial': 'Tutorial', 
            'practical': 'Practical',
            'lab': 'Laboratory',
            'seminar': 'Seminar'
        };
        $('#viewSlotType').html('<span class="badge bg-primary">' + (slotTypes[slot.slot_type] || slot.slot_type || '-') + '</span>');
        $('#viewGroup').text(slot.group_name || 'All Students');
        $('#viewNotes').text(slot.notes || 'No notes');
        $('#viewStatus').html(slot.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>');
        
        $('#viewSlotModal').modal('show');
    });
}

function formatTime(timeStr) {
    if (!timeStr) return '-';
    var parts = timeStr.split(':');
    var hours = parseInt(parts[0]);
    var mins = parts[1];
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    return hours + ':' + mins + ' ' + ampm;
}

function calculateDuration(start, end) {
    if (!start || !end) return '-';
    var startParts = start.split(':');
    var endParts = end.split(':');
    var startMins = parseInt(startParts[0]) * 60 + parseInt(startParts[1]);
    var endMins = parseInt(endParts[0]) * 60 + parseInt(endParts[1]);
    var diff = endMins - startMins;
    var hours = Math.floor(diff / 60);
    var mins = diff % 60;
    if (hours > 0 && mins > 0) return hours + ' hr ' + mins + ' min';
    if (hours > 0) return hours + ' hour' + (hours > 1 ? 's' : '');
    return mins + ' minutes';
}

// Edit from View Modal
$('#viewEditBtn').on('click', function() {
    $('#viewSlotModal').modal('hide');
    editSlot(currentViewSlotId);
});

// Delete from View Modal
$('#viewDeleteBtn').on('click', function() {
    $('#viewSlotModal').modal('hide');
    deleteSlot(currentViewSlotId);
});

// Duration calculator for Add form
function updateAddDuration() {
    var startTime = $('#addStartTime').val();
    var endTime = $('#addEndTime').val();
    if (startTime && endTime) {
        var duration = calculateDuration(startTime + ':00', endTime + ':00');
        $('#addDurationText').text(duration);
    }
}

$('#addStartTime, #addEndTime').on('change', updateAddDuration);

// Reset form when modal opens
$('#addSlotModal').on('show.bs.modal', function() {
    $('#addSlotForm')[0].reset();
    $('#addStartTime').val('08:00');
    $('#addEndTime').val('09:00');
    updateAddDuration();
});

$('#addSlotForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
    
    $.ajax({
        url: '/college/timetables/' + timetableId + '/slots',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            course_id: $('#addCourse').val(),
            slot_type: $('#addSlotType').val(),
            day_of_week: $('#addDay').val(),
            start_time: $('#addStartTime').val(),
            end_time: $('#addEndTime').val(),
            group_name: $('#addGroup').val() || null,
            notes: $('#addNotes').val() || null,
            venue_id: $('#addVenue').val() || null,
            instructor_id: $('#addInstructor').val() || null
        },
        success: function(res) {
            if (res.success) {
                Swal.fire({icon:'success', title:'Added!', text:res.message, timer:1500, showConfirmButton:false})
                .then(() => { $('#addSlotModal').modal('hide'); location.reload(); });
            } else {
                Swal.fire({icon:'error', title:'Error', text:res.message});
                btn.prop('disabled', false).html('<i class="bx bx-plus me-1"></i> Add Slot');
            }
        },
        error: function(xhr) {
            Swal.fire({icon:'error', title:'Error', text:xhr.responseJSON?.message || 'An error occurred'});
            btn.prop('disabled', false).html('<i class="bx bx-plus me-1"></i> Add Slot');
        }
    });
});

function editSlot(id) {
    $.get('/college/timetables/' + timetableId + '/slots/' + id, function(slot) {
        $('#editSlotId').val(slot.id);
        $('#editCourse').val(slot.course_id);
        $('#editSlotType').val(slot.slot_type);
        $('#editDay').val(slot.day_of_week);
        $('#editStartTime').val(slot.start_time.substring(0,5));
        $('#editEndTime').val(slot.end_time.substring(0,5));
        $('#editVenue').val(slot.venue_id || '');
        $('#editInstructor').val(slot.instructor_id || '');
        $('#editGroup').val(slot.group_name || '');
        $('#editNotes').val(slot.notes || '');
        updateEditDuration();
        $('#editSlotModal').modal('show');
    });
}

// Duration calculator for Edit form
function updateEditDuration() {
    var startTime = $('#editStartTime').val();
    var endTime = $('#editEndTime').val();
    if (startTime && endTime) {
        var duration = calculateDuration(startTime + ':00', endTime + ':00');
        $('#editDurationText').text(duration);
    }
}

$('#editStartTime, #editEndTime').on('change', updateEditDuration);

$('#editSlotForm').on('submit', function(e) {
    e.preventDefault();
    var id = $('#editSlotId').val();
    var btn = $(this).find('button[type="submit"]');
    btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Updating...');
    
    $.ajax({
        url: '/college/timetables/' + timetableId + '/slots/' + id,
        type: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            course_id: $('#editCourse').val(),
            slot_type: $('#editSlotType').val(),
            day_of_week: $('#editDay').val(),
            start_time: $('#editStartTime').val(),
            end_time: $('#editEndTime').val(),
            venue_id: $('#editVenue').val() || null,
            instructor_id: $('#editInstructor').val() || null,
            group_name: $('#editGroup').val() || null,
            notes: $('#editNotes').val() || null
        },
        success: function(res) {
            if (res.success) {
                Swal.fire({icon:'success', title:'Updated!', text:res.message, timer:1500, showConfirmButton:false})
                .then(() => { $('#editSlotModal').modal('hide'); location.reload(); });
            } else {
                Swal.fire({icon:'error', title:'Error', text:res.message});
                btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Update');
            }
        },
        error: function(xhr) {
            Swal.fire({icon:'error', title:'Error', text:xhr.responseJSON?.message || 'An error occurred'});
            btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Update');
        }
    });
});

function deleteSlot(id) {
    Swal.fire({
        title: 'Delete this slot?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Yes, delete'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/college/timetables/' + timetableId + '/slots/' + id,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.success) {
                        Swal.fire({icon:'success', title:'Deleted!', timer:1500, showConfirmButton:false})
                        .then(() => location.reload());
                    }
                },
                error: function(xhr) {
                    Swal.fire({icon:'error', title:'Error', text:xhr.responseJSON?.message || 'Error'});
                }
            });
        }
    });
}

function publishTimetable() {
    Swal.fire({
        title: 'Publish timetable?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Publish'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/college/timetables/' + timetableId + '/publish', { _token: '{{ csrf_token() }}' }, function(res) {
                if (res.success) {
                    Swal.fire({icon:'success', title:'Published!', timer:1500, showConfirmButton:false})
                    .then(() => location.reload());
                }
            });
        }
    });
}

function archiveTimetable() {
    Swal.fire({
        title: 'Archive timetable?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Archive'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('/college/timetables/' + timetableId + '/archive', { _token: '{{ csrf_token() }}' }, function(res) {
                if (res.success) {
                    Swal.fire({icon:'success', title:'Archived!', timer:1500, showConfirmButton:false})
                    .then(() => location.reload());
                }
            });
        }
    });
}
</script>
@endpush
