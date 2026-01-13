@extends('layouts.main')

@section('title', 'Edit Timetable - ' . $timetable->name)

@push('styles')
<style>
    /* ===== PAGE LAYOUT ===== */
    .edit-page-wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
        min-height: 100vh;
        padding-bottom: 30px;
    }
    
    /* ===== HEADER BANNER ===== */
    .timetable-banner {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #1e3c72 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 10px 30px rgba(30, 60, 114, 0.3);
        position: relative;
        overflow: hidden;
    }
    .timetable-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }
    .timetable-banner::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.03);
        border-radius: 50%;
    }
    .banner-content {
        position: relative;
        z-index: 1;
    }
    .program-code-badge {
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        padding: 8px 20px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 10px;
    }
    .banner-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .banner-subtitle {
        opacity: 0.9;
        font-size: 14px;
    }
    .status-badge-large {
        padding: 10px 25px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .status-draft { background: linear-gradient(135deg, #ffc107, #ff9800); color: #000; }
    .status-published { background: linear-gradient(135deg, #28a745, #20c997); color: #fff; }
    .status-archived { background: linear-gradient(135deg, #6c757d, #495057); color: #fff; }
    
    /* ===== CARDS ===== */
    .modern-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        border: none;
        overflow: hidden;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .modern-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    .modern-card .card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border-bottom: 2px solid #e9ecef;
        padding: 18px 20px;
    }
    .modern-card .card-header h5 {
        margin: 0;
        font-weight: 600;
        color: #1e3c72;
        display: flex;
        align-items: center;
    }
    .modern-card .card-header h5 i {
        font-size: 22px;
        margin-right: 10px;
        color: #2a5298;
    }
    .modern-card .card-body {
        padding: 20px;
    }
    
    /* ===== TIMETABLE GRID ===== */
    .timetable-grid-wrapper {
        overflow-x: auto;
        border-radius: 10px;
    }
    #timetableGrid {
        margin: 0;
        border-radius: 10px;
        overflow: hidden;
    }
    #timetableGrid thead th {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        font-weight: 600;
        padding: 12px 6px;
        text-align: center;
        border: none;
        font-size: 11px;
        white-space: nowrap;
    }
    #timetableGrid thead th:first-child {
        background: linear-gradient(135deg, #0d1f3c 0%, #1e3c72 100%);
        min-width: 100px;
    }
    #timetableGrid tbody tr {
        transition: background 0.2s;
    }
    #timetableGrid tbody tr:hover {
        background: #f8f9fa;
    }
    #timetableGrid tbody td {
        padding: 6px;
        vertical-align: middle;
        border-color: #e9ecef;
        min-width: 80px;
        height: 70px;
    }
    #timetableGrid tbody td:first-child {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        font-weight: 600;
        text-align: center;
        vertical-align: middle;
        color: white;
        font-size: 12px;
        min-width: 100px;
    }
    
    /* ===== SLOT ITEMS ===== */
    .slot-item {
        border-radius: 10px;
        padding: 10px 12px;
        margin-bottom: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        border-left-width: 4px !important;
        position: relative;
        overflow: hidden;
    }
    .slot-item::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 30px;
        height: 30px;
        background: rgba(255,255,255,0.3);
        border-radius: 50%;
        transform: translate(50%, -50%);
    }
    .slot-item:hover {
        transform: scale(1.03);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    .slot-item .course-code {
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 4px;
    }
    .slot-item .time-range {
        font-size: 11px;
        opacity: 0.8;
    }
    .slot-item .venue-info {
        font-size: 11px;
        margin-top: 4px;
    }
    .slot-item .instructor-info {
        font-size: 10px;
        margin-top: 2px;
    }
    .slot-item .slot-badge {
        font-size: 9px;
        padding: 3px 8px;
        border-radius: 10px;
        margin-top: 5px;
        display: inline-block;
    }
    
    /* Slot Type Colors */
    .slot-lecture { background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); border-color: #1976d2 !important; }
    .slot-tutorial { background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%); border-color: #388e3c !important; }
    .slot-practical { background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%); border-color: #f57c00 !important; }
    .slot-lab { background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); border-color: #0097a7 !important; }
    .slot-seminar { background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%); border-color: #7b1fa2 !important; }
    .slot-workshop { background: linear-gradient(135deg, #efebe9 0%, #d7ccc8 100%); border-color: #5d4037 !important; }
    .slot-exam { background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); border-color: #d32f2f !important; }
    
    /* ===== SLOTS LIST TABLE ===== */
    .slots-table {
        border-radius: 10px;
        overflow: hidden;
    }
    .slots-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #1e3c72;
        padding: 15px;
        border: none;
        font-size: 13px;
    }
    .slots-table tbody tr {
        transition: all 0.2s;
    }
    .slots-table tbody tr:hover {
        background: #f8f9fa;
    }
    .slots-table tbody td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    .slots-table .action-btns .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-right: 5px;
    }
    
    /* ===== SIDEBAR CARDS ===== */
    .settings-form .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .settings-form .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
        transition: all 0.3s;
    }
    .settings-form .form-control:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
    }
    
    /* Action Buttons */
    .action-btn {
        border-radius: 12px;
        padding: 14px 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s;
        border: none;
    }
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    .action-btn i {
        font-size: 20px;
    }
    
    /* Summary Stats */
    .summary-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 10px;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border: 1px solid #e9ecef;
    }
    .summary-stat .stat-label {
        font-weight: 500;
        color: #666;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .summary-stat .stat-label i {
        font-size: 20px;
        color: #1e3c72;
    }
    .summary-stat .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: #1e3c72;
    }
    
    /* Legend */
    .legend-item {
        display: inline-flex;
        align-items: center;
        padding: 8px 15px;
        border-radius: 20px;
        margin: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .legend-item i {
        margin-right: 6px;
    }
    
    /* ===== MODALS ===== */
    .modal-content {
        border-radius: 20px;
        border: none;
        overflow: hidden;
    }
    .modal-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 20px 25px;
        border: none;
    }
    .modal-header .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    .modal-header .modal-title i {
        font-size: 24px;
        margin-right: 12px;
    }
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }
    .modal-body {
        padding: 25px;
    }
    .modal-footer {
        padding: 15px 25px 25px;
        border: none;
    }
    
    /* Form inputs in modal */
    .modal .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 13px;
    }
    .modal .form-control, .modal .form-select {
        border-radius: 10px;
        padding: 12px 15px;
        border: 2px solid #e9ecef;
    }
    .modal .form-control:focus, .modal .form-select:focus {
        border-color: #1e3c72;
        box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
    }
    
    /* Buttons */
    .btn-modern {
        border-radius: 10px;
        padding: 12px 25px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-modern:hover {
        transform: translateY(-2px);
    }
    
    /* Add Slot Button */
    .add-slot-btn {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .add-slot-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        color: white;
    }
    
    /* Empty state */
    .empty-state {
        padding: 50px 30px;
        text-align: center;
    }
    .empty-state i {
        font-size: 60px;
        color: #dee2e6;
        margin-bottom: 15px;
    }
    .empty-state h5 {
        color: #6c757d;
        margin-bottom: 10px;
    }
    .empty-state p {
        color: #adb5bd;
    }
    
    /* ===== SLOT TOOLTIP ===== */
    .slot-tooltip {
        position: fixed;
        z-index: 9999;
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border-radius: 15px;
        padding: 20px;
        min-width: 300px;
        max-width: 380px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
        display: none;
        animation: slotTooltipFadeIn 0.25s ease;
        pointer-events: none;
    }
    @keyframes slotTooltipFadeIn {
        from { opacity: 0; transform: translateY(8px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .slot-tooltip::before {
        content: '';
        position: absolute;
        top: -8px;
        left: 25px;
        border-width: 0 8px 8px 8px;
        border-style: solid;
        border-color: transparent transparent #1e3c72 transparent;
    }
    .slot-tooltip-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    .slot-tooltip-title {
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 3px;
    }
    .slot-tooltip-subtitle {
        font-size: 12px;
        opacity: 0.8;
    }
    .slot-type-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .slot-type-badge.lecture { background: linear-gradient(135deg, #0d6efd, #0b5ed7); }
    .slot-type-badge.tutorial { background: linear-gradient(135deg, #198754, #157347); }
    .slot-type-badge.practical { background: linear-gradient(135deg, #ffc107, #e0a800); color: #000; }
    .slot-type-badge.lab { background: linear-gradient(135deg, #0dcaf0, #0aa2c0); }
    .slot-type-badge.seminar { background: linear-gradient(135deg, #6c757d, #5a6268); }
    .slot-type-badge.workshop { background: linear-gradient(135deg, #212529, #343a40); }
    .slot-type-badge.exam { background: linear-gradient(135deg, #dc3545, #bb2d3b); }
    .slot-tooltip-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .slot-tooltip-item {
        display: flex;
        flex-direction: column;
    }
    .slot-tooltip-item.full-width {
        grid-column: 1 / -1;
    }
    .slot-tooltip-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.7;
        margin-bottom: 3px;
    }
    .slot-tooltip-value {
        font-size: 14px;
        font-weight: 600;
    }
    .slot-tooltip-value i {
        margin-right: 6px;
        opacity: 0.8;
    }
    .slot-tooltip-time {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
        padding: 12px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
    }
    .slot-tooltip-time-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .slot-tooltip-duration {
        font-size: 12px;
        opacity: 0.8;
    }
    .slot-tooltip-footer {
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 11px;
        opacity: 0.7;
        text-align: center;
    }
    
    /* Make slots table rows interactive */
    .slots-table tbody tr[data-slot-id] {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .slots-table tbody tr[data-slot-id]:hover {
        background: linear-gradient(135deg, rgba(30, 60, 114, 0.08) 0%, rgba(42, 82, 152, 0.12) 100%) !important;
        transform: scale(1.01);
    }
    
    /* Grid slot items tooltip hover */
    .slot-item[data-slot-id] {
        position: relative;
    }
    .slot-item[data-slot-id]:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }
</style>
@endpush

@section('content')
<div class="edit-page-wrapper">
    <div class="page-content" style="margin-top: 70px; margin-left: 235px; margin-right: 20px;">
        <div class="container-fluid">
        <!-- Breadcrumb Navigation -->
            <div class="row mb-3">
                <div class="col-12">
                    <nav aria-label="breadcrumb" class="d-flex align-items-center flex-wrap gap-2">
                        <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
                            <i class="bx bx-home-alt me-1"></i> Dashboard
                        </a>
                        <i class="bx bx-chevron-right text-muted"></i>
                        <a href="{{ route('college.timetables.index') }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
                            <i class="bx bx-calendar-alt me-1"></i> Timetables
                        </a>
                        <i class="bx bx-chevron-right text-muted"></i>
                        <a href="{{ route('college.timetables.show', $timetable) }}" class="btn btn-light btn-sm rounded-pill px-3 shadow-sm">
                            <i class="bx bx-show me-1"></i> View
                        </a>
                        <i class="bx bx-chevron-right text-muted"></i>
                        <span class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                            <i class="bx bx-edit me-1"></i> Edit
                        </span>
                    </nav>
                </div>
            </div>

            <!-- Timetable Banner -->
            <div class="timetable-banner">
                <div class="banner-content">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <span class="program-code-badge">
                                <i class="bx bx-book-reader me-2"></i>{{ $timetable->program->code }} {{ $timetable->year_of_study }}.{{ $timetable->semester->number ?? 1 }}
                            </span>
                            <h1 class="banner-title">{{ $timetable->name }}</h1>
                            <p class="banner-subtitle mb-0">
                                <i class="bx bx-buildings me-1"></i> {{ $timetable->program->name }}
                                <span class="mx-2">•</span>
                                <i class="bx bx-calendar me-1"></i> {{ $timetable->academicYear->name }}
                                <span class="mx-2">•</span>
                                <i class="bx bx-time me-1"></i> {{ $timetable->semester->name }}
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            @if($timetable->status == 'draft')
                                <span class="status-badge-large status-draft">
                                    <i class="bx bx-edit-alt me-1"></i> Draft
                                </span>
                            @elseif($timetable->status == 'published')
                                <span class="status-badge-large status-published">
                                    <i class="bx bx-check-circle me-1"></i> Published
                                </span>
                            @else
                                <span class="status-badge-large status-archived">
                                    <i class="bx bx-archive me-1"></i> Archived
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        <div class="row">
            <!-- Timetable Grid -->
            <div class="col-lg-8">
                <div class="modern-card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><i class="bx bx-grid-alt"></i>Weekly Schedule</h5>
                            <button type="button" class="add-slot-btn" data-bs-toggle="modal" data-bs-target="#addSlotModal">
                                <i class="bx bx-plus me-1"></i> Add New Slot
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @php
                            // Define time periods for columns (7AM to 9PM)
                            $timePeriods = [];
                            for ($hour = 7; $hour <= 20; $hour++) {
                                $timePeriods[] = [
                                    'start' => sprintf('%02d:00', $hour),
                                    'end' => sprintf('%02d:00', $hour + 1),
                                    'label' => sprintf('%d-%d', $hour, $hour + 1)
                                ];
                            }
                            
                            // Day labels in Swahili
                            $dayLabels = [
                                'Monday' => 'Jumatatu',
                                'Tuesday' => 'Jumanne',
                                'Wednesday' => 'Jumatano',
                                'Thursday' => 'Alhamisi',
                                'Friday' => 'Ijumaa',
                                'Saturday' => 'Jumamosi',
                            ];
                            
                            // Build slotsByDay collection
                            $slotsByDay = $timetable->slots->groupBy('day_of_week');
                            
                            // Function to calculate colspan for edit grid
                            function getEditSlotColspan($slot, $timePeriods) {
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
                        <div class="timetable-grid-wrapper">
                            <table class="table table-bordered mb-0" id="timetableGrid">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;"><i class="bx bx-calendar me-1"></i>Siku</th>
                                        @foreach($timePeriods as $period)
                                            <th>{{ $period['label'] }}</th>
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
                                                $info = getEditSlotColspan($slot, $timePeriods);
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
                                        <tr data-day="{{ $dayEng }}">
                                            <td>{{ $daySwahili }}</td>
                                            @foreach($timePeriods as $colIdx => $period)
                                                @if(!isset($skipCols[$colIdx]))
                                                    @if(isset($slotInfo[$colIdx]))
                                                        @php
                                                            $slot = $slotInfo[$colIdx]['slot'];
                                                            $colspan = $slotInfo[$colIdx]['colspan'];
                                                            $slotDuration = \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time));
                                                            $slotHours = floor($slotDuration / 60);
                                                            $slotMinutes = $slotDuration % 60;
                                                            $slotDurationText = $slotHours > 0 ? $slotHours . 'h ' . ($slotMinutes > 0 ? $slotMinutes . 'm' : '') : $slotMinutes . 'm';
                                                        @endphp
                                                        <td colspan="{{ $colspan }}" 
                                                            class="slot-cell" 
                                                            data-day="{{ $dayEng }}" 
                                                            data-time="{{ $period['start'] }}">
                                                            <div class="slot-item slot-{{ $slot->slot_type }}"
                                                                 data-slot-id="{{ $slot->id }}"
                                                                 data-slot-course="{{ $slot->course->code ?? '' }}"
                                                                 data-slot-course-name="{{ $slot->course->name ?? '' }}"
                                                                 data-slot-type="{{ $slot->slot_type }}"
                                                                 data-slot-day="{{ $slot->day_of_week }}"
                                                                 data-slot-start="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}"
                                                                 data-slot-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}"
                                                                 data-slot-duration="{{ $slotDurationText }}"
                                                                 data-slot-venue="{{ $slot->venue->name ?? 'Not Assigned' }}"
                                                                 data-slot-venue-code="{{ $slot->venue->code ?? '-' }}"
                                                                 data-slot-venue-capacity="{{ $slot->venue->capacity ?? '-' }}"
                                                                 data-slot-instructor="{{ $slot->instructor?->full_name ?? 'Not Assigned' }}"
                                                                 data-slot-max-students="{{ $slot->max_students ?? '-' }}"
                                                                 onclick="editSlot({{ $slot->id }})">
                                                                <div class="course-code">{{ $slot->course->code ?? 'N/A' }}</div>
                                                                <div class="time-range">
                                                                    <i class="bx bx-time"></i>
                                                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                                </div>
                                                                @if($slot->venue)
                                                                    <div class="venue-info">
                                                                        <i class="bx bx-map-pin"></i> {{ $slot->venue->code }}
                                                                    </div>
                                                                @endif
                                                                @if($slot->instructor)
                                                                    <div class="instructor-info">
                                                                        <i class="bx bx-user"></i> {{ $slot->instructor->first_name }}
                                                                    </div>
                                                                @endif
                                                                <span class="slot-badge bg-{{ $slot->slot_type == 'lecture' ? 'primary' : ($slot->slot_type == 'tutorial' ? 'success' : ($slot->slot_type == 'practical' ? 'warning' : ($slot->slot_type == 'lab' ? 'info' : ($slot->slot_type == 'seminar' ? 'purple' : ($slot->slot_type == 'workshop' ? 'dark' : 'danger'))))) }} text-white">
                                                                    {{ ucfirst($slot->slot_type) }}
                                                                </span>
                                                            </div>
                                                        </td>
                                                    @else
                                                        <td class="slot-cell" 
                                                            data-day="{{ $dayEng }}" 
                                                            data-time="{{ $period['start'] }}">
                                                        </td>
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

                <!-- Slots List -->
                <div class="modern-card">
                    <div class="card-header">
                        <h5><i class="bx bx-list-ul"></i>All Time Slots <span class="badge bg-primary rounded-pill ms-2">{{ $timetable->slots->count() }}</span></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table slots-table mb-0">
                                <thead>
                                    <tr>
                                        <th><i class="bx bx-calendar-event me-1"></i>Day</th>
                                        <th><i class="bx bx-time me-1"></i>Time</th>
                                        <th><i class="bx bx-book me-1"></i>Course</th>
                                        <th><i class="bx bx-category me-1"></i>Type</th>
                                        <th><i class="bx bx-map me-1"></i>Venue</th>
                                        <th><i class="bx bx-user me-1"></i>Instructor</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="slotsList">
                                    @forelse($timetable->slots->sortBy(['day_of_week', 'start_time']) as $slot)
                                        @php
                                            $duration = \Carbon\Carbon::parse($slot->start_time)->diffInMinutes(\Carbon\Carbon::parse($slot->end_time));
                                            $hours = floor($duration / 60);
                                            $minutes = $duration % 60;
                                            $durationText = $hours > 0 ? $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '') : $minutes . 'm';
                                        @endphp
                                        <tr data-slot-id="{{ $slot->id }}"
                                            data-slot-course="{{ $slot->course->code }}"
                                            data-slot-course-name="{{ $slot->course->name }}"
                                            data-slot-type="{{ $slot->slot_type }}"
                                            data-slot-day="{{ $slot->day_of_week }}"
                                            data-slot-start="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}"
                                            data-slot-end="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}"
                                            data-slot-duration="{{ $durationText }}"
                                            data-slot-venue="{{ $slot->venue->name ?? 'Not Assigned' }}"
                                            data-slot-venue-code="{{ $slot->venue->code ?? '-' }}"
                                            data-slot-venue-capacity="{{ $slot->venue->capacity ?? '-' }}"
                                            data-slot-instructor="{{ $slot->instructor?->full_name ?? 'Not Assigned' }}"
                                            data-slot-max-students="{{ $slot->max_students ?? '-' }}">
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $slot->day_of_week }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-primary">
                                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}
                                                </span>
                                                <span class="text-muted">-</span>
                                                <span class="fw-semibold text-primary">
                                                    {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $slot->course->code }}</div>
                                                <small class="text-muted">{{ Str::limit($slot->course->name, 25) }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $typeColors = [
                                                        'lecture' => 'primary',
                                                        'tutorial' => 'success',
                                                        'practical' => 'warning',
                                                        'lab' => 'info',
                                                        'seminar' => 'secondary',
                                                        'workshop' => 'dark',
                                                        'exam' => 'danger'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $typeColors[$slot->slot_type] ?? 'primary' }} rounded-pill">
                                                    {{ ucfirst($slot->slot_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($slot->venue)
                                                    <span class="badge bg-light text-dark">
                                                        <i class="bx bx-map-pin me-1"></i>{{ $slot->venue->code }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $slot->instructor?->full_name ?? '-' }}</td>
                                            <td class="action-btns text-center">
                                                <button type="button" class="btn btn-outline-primary" onclick="editSlot({{ $slot->id }})" title="Edit">
                                                    <i class="bx bx-edit-alt"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteSlot({{ $slot->id }})" title="Delete">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                <div class="empty-state">
                                                    <i class="bx bx-calendar-x"></i>
                                                    <h5>No Time Slots Yet</h5>
                                                    <p class="mb-3">Start building your timetable by adding slots</p>
                                                    <button type="button" class="btn btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addSlotModal">
                                                        <i class="bx bx-plus me-1"></i> Add First Slot
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Timetable Settings Card -->
                <div class="modern-card mb-4">
                    <div class="card-header">
                        <h5><i class="bx bx-cog"></i>Timetable Settings</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('college.timetables.update', $timetable) }}" method="POST" class="settings-form">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="bx bx-rename me-1"></i>Timetable Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $timetable->name }}" required>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label"><i class="bx bx-calendar-check me-1"></i>Effective From</label>
                                    <input type="date" name="effective_from" class="form-control" 
                                        value="{{ $timetable->effective_from?->format('Y-m-d') }}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label"><i class="bx bx-calendar-x me-1"></i>Effective To</label>
                                    <input type="date" name="effective_to" class="form-control" 
                                        value="{{ $timetable->effective_to?->format('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label"><i class="bx bx-note me-1"></i>Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Add any additional notes...">{{ $timetable->notes }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary action-btn w-100">
                                <i class="bx bx-save"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="modern-card mb-4">
                    <div class="card-header">
                        <h5><i class="bx bx-zap"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <a href="{{ route('college.timetables.show', $timetable) }}" class="btn btn-outline-info action-btn">
                                <i class="bx bx-show"></i> View Timetable
                            </a>
                            
                            @if($timetable->status == 'draft')
                                <button type="button" class="btn btn-success action-btn" onclick="publishTimetable()">
                                    <i class="bx bx-check-circle"></i> Publish Timetable
                                </button>
                            @elseif($timetable->status == 'published')
                                <button type="button" class="btn btn-secondary action-btn" onclick="archiveTimetable()">
                                    <i class="bx bx-archive"></i> Archive Timetable
                                </button>
                            @endif

                            <a href="{{ route('college.timetables.print', $timetable) }}" class="btn btn-outline-dark action-btn" target="_blank">
                                <i class="bx bx-printer"></i> Print Timetable
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Summary Statistics Card -->
                <div class="modern-card mb-4">
                    <div class="card-header">
                        <h5><i class="bx bx-bar-chart-alt-2"></i>Summary Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div class="summary-stat">
                            <div class="stat-label">
                                <i class="bx bx-calendar-event"></i>
                                <span>Total Slots</span>
                            </div>
                            <div class="stat-value">{{ $timetable->slots->count() }}</div>
                        </div>
                        <div class="summary-stat">
                            <div class="stat-label">
                                <i class="bx bx-time-five"></i>
                                <span>Hours/Week</span>
                            </div>
                            <div class="stat-value">{{ number_format($timetable->getTotalHoursPerWeek(), 1) }}</div>
                        </div>
                        <div class="summary-stat">
                            <div class="stat-label">
                                <i class="bx bx-book-open"></i>
                                <span>Courses</span>
                            </div>
                            <div class="stat-value">{{ $timetable->slots->pluck('course_id')->unique()->count() }}</div>
                        </div>
                        <div class="summary-stat">
                            <div class="stat-label">
                                <i class="bx bx-buildings"></i>
                                <span>Venues Used</span>
                            </div>
                            <div class="stat-value">{{ $timetable->slots->pluck('venue_id')->filter()->unique()->count() }}</div>
                        </div>
                        <div class="summary-stat">
                            <div class="stat-label">
                                <i class="bx bx-user-voice"></i>
                                <span>Instructors</span>
                            </div>
                            <div class="stat-value">{{ $timetable->slots->pluck('instructor_id')->filter()->unique()->count() }}</div>
                        </div>
                    </div>
                </div>

                <!-- Slot Types Legend Card -->
                <div class="modern-card">
                    <div class="card-header">
                        <h5><i class="bx bx-palette"></i>Slot Type Colors</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap">
                            <span class="legend-item slot-lecture"><i class="bx bx-chalkboard"></i> Lecture</span>
                            <span class="legend-item slot-tutorial"><i class="bx bx-group"></i> Tutorial</span>
                            <span class="legend-item slot-practical"><i class="bx bx-wrench"></i> Practical</span>
                            <span class="legend-item slot-lab"><i class="bx bx-test-tube"></i> Lab</span>
                            <span class="legend-item slot-seminar"><i class="bx bx-conversation"></i> Seminar</span>
                            <span class="legend-item slot-workshop"><i class="bx bx-cog"></i> Workshop</span>
                            <span class="legend-item slot-exam"><i class="bx bx-edit"></i> Exam</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Slot Modal -->
<div class="modal fade" id="addSlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-plus-circle"></i>Add New Time Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSlotForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bx bx-book text-primary me-1"></i>Course <span class="text-danger">*</span></label>
                            <select class="form-select" id="addCourse" name="course_id" required>
                                <option value="">-- Select Course --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bx bx-category text-success me-1"></i>Slot Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="addSlotType" name="slot_type" required>
                                <option value="lecture">📚 Lecture</option>
                                <option value="tutorial">👥 Tutorial</option>
                                <option value="practical">🔧 Practical</option>
                                <option value="lab">🧪 Lab</option>
                                <option value="seminar">💬 Seminar</option>
                                <option value="workshop">⚙️ Workshop</option>
                                <option value="exam">📝 Exam</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label"><i class="bx bx-calendar text-info me-1"></i>Day <span class="text-danger">*</span></label>
                            <select class="form-select" id="addDay" name="day_of_week" required>
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bx bx-time text-warning me-1"></i>Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="addStartTime" name="start_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bx bx-time-five text-danger me-1"></i>End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="addEndTime" name="end_time" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bx bx-buildings text-secondary me-1"></i>Venue</label>
                            <select class="form-select" id="addVenue" name="venue_id">
                                <option value="">-- Select Venue (Optional) --</option>
                                @foreach($venues as $venue)
                                    <option value="{{ $venue->id }}">{{ $venue->code }} - {{ $venue->name }} (Cap: {{ $venue->capacity }})</option>
                                @endforeach
                            </select>
                            <div id="venueAvailability" class="small mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bx bx-user text-primary me-1"></i>Instructor</label>
                            <select class="form-select" id="addInstructor" name="instructor_id">
                                <option value="">-- Select Instructor (Optional) --</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}">{{ $instructor->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-12">
                            <label class="form-label"><i class="bx bx-group text-info me-1"></i>Group Name (Optional)</label>
                            <input type="text" class="form-control" id="addGroupName" name="group_name" placeholder="e.g., Group A, Section 1, Evening Class">
                            <small class="text-muted">Use this for parallel sessions or different student groups</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-modern" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-modern">
                        <i class="bx bx-plus me-1"></i> Add Slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Slot Modal -->
<div class="modal fade" id="editSlotModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-edit"></i>Edit Time Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSlotForm">
                <input type="hidden" id="editSlotId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bx bx-book text-primary me-1"></i>Course <span class="text-danger">*</span></label>
                            <select class="form-select" id="editCourse" name="course_id" required>
                                <option value="">-- Select Course --</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bx bx-category text-success me-1"></i>Slot Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="editSlotType" name="slot_type" required>
                                <option value="lecture">📚 Lecture</option>
                                <option value="tutorial">👥 Tutorial</option>
                                <option value="practical">🔧 Practical</option>
                                <option value="lab">🧪 Lab</option>
                                <option value="seminar">💬 Seminar</option>
                                <option value="workshop">⚙️ Workshop</option>
                                <option value="exam">📝 Exam</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <label class="form-label"><i class="bx bx-calendar text-info me-1"></i>Day <span class="text-danger">*</span></label>
                            <select class="form-select" id="editDay" name="day_of_week" required>
                                @foreach($days as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bx bx-time text-warning me-1"></i>Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="editStartTime" name="start_time" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="bx bx-time-five text-danger me-1"></i>End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="editEndTime" name="end_time" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label class="form-label"><i class="bx bx-buildings text-secondary me-1"></i>Venue</label>
                            <select class="form-select" id="editVenue" name="venue_id">
                                <option value="">-- Select Venue (Optional) --</option>
                                @foreach($venues as $venue)
                                    <option value="{{ $venue->id }}">{{ $venue->code }} - {{ $venue->name }} (Cap: {{ $venue->capacity }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><i class="bx bx-user text-primary me-1"></i>Instructor</label>
                            <select class="form-select" id="editInstructor" name="instructor_id">
                                <option value="">-- Select Instructor (Optional) --</option>
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructor->id }}">{{ $instructor->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <div class="col-12">
                            <label class="form-label"><i class="bx bx-group text-info me-1"></i>Group Name (Optional)</label>
                            <input type="text" class="form-control" id="editGroupName" name="group_name" placeholder="e.g., Group A, Section 1">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-modern" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary btn-modern">
                        <i class="bx bx-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var timetableId = {{ $timetable->id }};

// Add Slot Form Submit
$('#addSlotForm').on('submit', function(e) {
    e.preventDefault();
    
    var submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Adding...');
    
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
            venue_id: $('#addVenue').val() || null,
            instructor_id: $('#addInstructor').val() || null,
            group_name: $('#addGroupName').val() || null
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Slot Added!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    $('#addSlotModal').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
                submitBtn.prop('disabled', false).html('<i class="bx bx-plus me-1"></i> Add Slot');
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            var errorMsg = '';
            if (errors) {
                errorMsg = Object.values(errors).flat().join('\n');
            } else {
                errorMsg = xhr.responseJSON?.message || 'An error occurred while saving the slot.';
            }
            Swal.fire({
                icon: 'error',
                title: 'Validation Error!',
                text: errorMsg
            });
            submitBtn.prop('disabled', false).html('<i class="bx bx-plus me-1"></i> Add Slot');
        }
    });
});

// Edit Slot - Load Data
function editSlot(slotId) {
    $.ajax({
        url: '/college/timetables/' + timetableId + '/slots/' + slotId,
        type: 'GET',
        success: function(slot) {
            $('#editSlotId').val(slot.id);
            $('#editCourse').val(slot.course_id);
            $('#editSlotType').val(slot.slot_type);
            $('#editDay').val(slot.day_of_week);
            $('#editStartTime').val(slot.start_time.substring(0, 5));
            $('#editEndTime').val(slot.end_time.substring(0, 5));
            $('#editVenue').val(slot.venue_id || '');
            $('#editInstructor').val(slot.instructor_id || '');
            $('#editGroupName').val(slot.group_name || '');
            $('#editSlotModal').modal('show');
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to load slot details'
            });
        }
    });
}

// Edit Slot Form Submit
$('#editSlotForm').on('submit', function(e) {
    e.preventDefault();
    var slotId = $('#editSlotId').val();
    var submitBtn = $(this).find('button[type="submit"]');
    submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
    
    $.ajax({
        url: '/college/timetables/' + timetableId + '/slots/' + slotId,
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
            group_name: $('#editGroupName').val() || null
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(function() {
                    $('#editSlotModal').modal('hide');
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
                submitBtn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save Changes');
            }
        },
        error: function(xhr) {
            var errors = xhr.responseJSON?.errors;
            var errorMsg = '';
            if (errors) {
                errorMsg = Object.values(errors).flat().join('\n');
            } else {
                errorMsg = xhr.responseJSON?.message || 'An error occurred';
            }
            Swal.fire({
                icon: 'error',
                title: 'Validation Error!',
                text: errorMsg
            });
            submitBtn.prop('disabled', false).html('<i class="bx bx-save me-1"></i> Save Changes');
        }
    });
});

// Delete Slot
function deleteSlot(slotId) {
    Swal.fire({
        title: 'Delete Time Slot?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bx bx-trash me-1"></i> Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/college/timetables/' + timetableId + '/slots/' + slotId,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || 'An error occurred'
                    });
                }
            });
        }
    });
}

// Publish Timetable
function publishTimetable() {
    Swal.fire({
        title: 'Publish Timetable?',
        text: 'This will make the timetable visible to students.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bx bx-check-circle me-1"></i> Yes, Publish'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/college/timetables/' + timetableId + '/publish',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Published!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: response.message });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'An error occurred' });
                }
            });
        }
    });
}

// Archive Timetable
function archiveTimetable() {
    Swal.fire({
        title: 'Archive Timetable?',
        text: 'Are you sure you want to archive this timetable?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="bx bx-archive me-1"></i> Yes, Archive'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/college/timetables/' + timetableId + '/archive',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Archived!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error!', text: response.message });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Error!', text: xhr.responseJSON?.message || 'An error occurred' });
                }
            });
        }
    });
}

// Check Venue Availability
$('#addVenue, #addDay, #addStartTime, #addEndTime').on('change', function() {
    var venueId = $('#addVenue').val();
    var day = $('#addDay').val();
    var startTime = $('#addStartTime').val();
    var endTime = $('#addEndTime').val();
    
    if (venueId && day !== '' && startTime && endTime) {
        checkVenueAvailability(venueId, day, startTime, endTime);
    }
});

function checkVenueAvailability(venueId, day, startTime, endTime) {
    $.ajax({
        url: '/college/timetables/check-venue',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            venue_id: venueId,
            day_of_week: day,
            start_time: startTime,
            end_time: endTime
        },
        success: function(response) {
            var $indicator = $('#venueAvailability');
            if (response.available) {
                $indicator.html('<span class="text-success"><i class="bx bx-check-circle me-1"></i> Venue is available</span>');
            } else {
                $indicator.html('<span class="text-danger"><i class="bx bx-x-circle me-1"></i> Conflict: ' + response.conflict + '</span>');
            }
        }
    });
}

// ===== SLOT TOOLTIP FUNCTIONALITY =====
var slotTooltipTimeout;
var $slotTooltip = null;

function initSlotTooltips() {
    // Create tooltip element if not exists
    if (!$slotTooltip) {
        $slotTooltip = $('<div class="slot-tooltip"></div>');
        $('body').append($slotTooltip);
    }
    
    // Hover events for slots list table (desktop)
    $('#slotsList tr[data-slot-id]').off('mouseenter mouseleave').on('mouseenter', function(e) {
        var $row = $(this);
        clearTimeout(slotTooltipTimeout);
        slotTooltipTimeout = setTimeout(function() {
            showSlotTooltip($row, e);
        }, 250);
    }).on('mouseleave', function() {
        clearTimeout(slotTooltipTimeout);
        hideSlotTooltip();
    });
    
    // Hover events for grid slot items (desktop)
    $('.slot-item[data-slot-id]').off('mouseenter mouseleave').on('mouseenter', function(e) {
        var $item = $(this);
        clearTimeout(slotTooltipTimeout);
        slotTooltipTimeout = setTimeout(function() {
            showSlotTooltip($item, e);
        }, 300);
    }).on('mouseleave', function() {
        clearTimeout(slotTooltipTimeout);
        hideSlotTooltip();
    });
    
    // Touch events for mobile (long press) - slots list
    var touchTimer;
    $('#slotsList tr[data-slot-id]').off('touchstart touchend touchmove').on('touchstart', function(e) {
        var $row = $(this);
        touchTimer = setTimeout(function() {
            showSlotTooltip($row, e.originalEvent.touches[0]);
        }, 500);
    }).on('touchend touchmove', function() {
        clearTimeout(touchTimer);
        setTimeout(hideSlotTooltip, 2500);
    });
    
    // Touch events for mobile (long press) - grid slots
    $('.slot-item[data-slot-id]').off('touchstart touchend touchmove').on('touchstart', function(e) {
        var $item = $(this);
        e.stopPropagation();
        touchTimer = setTimeout(function() {
            showSlotTooltip($item, e.originalEvent.touches[0]);
        }, 500);
    }).on('touchend touchmove', function() {
        clearTimeout(touchTimer);
        setTimeout(hideSlotTooltip, 2500);
    });
}

function showSlotTooltip($row, e) {
    var course = $row.data('slot-course');
    var courseName = $row.data('slot-course-name');
    var slotType = $row.data('slot-type');
    var day = $row.data('slot-day');
    var startTime = $row.data('slot-start');
    var endTime = $row.data('slot-end');
    var duration = $row.data('slot-duration');
    var venue = $row.data('slot-venue');
    var venueCode = $row.data('slot-venue-code');
    var venueCapacity = $row.data('slot-venue-capacity');
    var instructor = $row.data('slot-instructor');
    var maxStudents = $row.data('slot-max-students');
    
    var html = `
        <div class="slot-tooltip-header">
            <div>
                <div class="slot-tooltip-title">${course}</div>
                <div class="slot-tooltip-subtitle">${courseName}</div>
            </div>
            <span class="slot-type-badge ${slotType}">${slotType}</span>
        </div>
        <div class="slot-tooltip-grid">
            <div class="slot-tooltip-item">
                <span class="slot-tooltip-label">Day</span>
                <span class="slot-tooltip-value"><i class="bx bx-calendar"></i>${day}</span>
            </div>
            <div class="slot-tooltip-item">
                <span class="slot-tooltip-label">Duration</span>
                <span class="slot-tooltip-value"><i class="bx bx-timer"></i>${duration}</span>
            </div>
            <div class="slot-tooltip-item">
                <span class="slot-tooltip-label">Venue</span>
                <span class="slot-tooltip-value"><i class="bx bx-map-pin"></i>${venueCode} (${venueCapacity} seats)</span>
            </div>
            <div class="slot-tooltip-item">
                <span class="slot-tooltip-label">Instructor</span>
                <span class="slot-tooltip-value"><i class="bx bx-user"></i>${instructor}</span>
            </div>
            <div class="slot-tooltip-item full-width">
                <span class="slot-tooltip-label">Full Venue Name</span>
                <span class="slot-tooltip-value"><i class="bx bx-building"></i>${venue}</span>
            </div>
        </div>
        <div class="slot-tooltip-time">
            <div class="slot-tooltip-time-icon">
                <i class="bx bx-time-five"></i>
            </div>
            <div>
                <div class="slot-tooltip-value" style="font-size: 16px;">${startTime} - ${endTime}</div>
                <div class="slot-tooltip-duration">Max Students: ${maxStudents}</div>
            </div>
        </div>
        <div class="slot-tooltip-footer">
            <i class="bx bx-pointer me-1"></i> Click to edit this slot
        </div>
    `;
    
    $slotTooltip.html(html);
    
    // Position tooltip
    var rowOffset = $row.offset();
    var tooltipHeight = $slotTooltip.outerHeight();
    var tooltipWidth = $slotTooltip.outerWidth();
    var windowWidth = $(window).width();
    var windowHeight = $(window).height();
    var scrollTop = $(window).scrollTop();
    
    var left = e.pageX - 25;
    var top = rowOffset.top + $row.outerHeight() + 8;
    
    // Adjust if tooltip would go off-screen horizontally
    if (left + tooltipWidth > windowWidth - 20) {
        left = windowWidth - tooltipWidth - 20;
    }
    if (left < 20) {
        left = 20;
    }
    
    // Adjust if tooltip would go off-screen vertically (show above instead)
    if (top + tooltipHeight > scrollTop + windowHeight - 20) {
        top = rowOffset.top - tooltipHeight - 8;
        $slotTooltip.find('::before').css('top', 'auto').css('bottom', '-8px');
    }
    
    $slotTooltip.css({
        left: left + 'px',
        top: top + 'px'
    }).fadeIn(200);
}

function hideSlotTooltip() {
    if ($slotTooltip) {
        $slotTooltip.fadeOut(150);
    }
}

// Hide tooltip when clicking elsewhere
$(document).on('click', function(e) {
    if (!$(e.target).closest('#slotsList tr[data-slot-id], .slot-item[data-slot-id]').length) {
        hideSlotTooltip();
    }
});

// Initialize tooltips on page load
$(document).ready(function() {
    initSlotTooltips();
});
</script>
@endpush
