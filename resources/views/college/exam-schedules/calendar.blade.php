@extends('layouts.main')

@section('title', 'Exam Schedule Calendar')

@section('content')
<style>
    .calendar-container {
        margin-left: 250px;
        margin-right: 20px;
        padding: 20px;
        max-width: calc(100vw - 280px);
    }

    /* Breadcrumb Styles */
    .breadcrumb-wrapper {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 20px 30px;
        margin-bottom: 30px;
        box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
    }

    .breadcrumb-nav {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .breadcrumb-item {
        display: flex;
        align-items: center;
    }

    .breadcrumb-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 25px;
        color: white;
        text-decoration: none;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .breadcrumb-link:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        color: white;
    }

    .breadcrumb-link i {
        font-size: 16px;
        transition: transform 0.3s ease;
    }

    .breadcrumb-link:hover i {
        transform: scale(1.2);
    }

    .breadcrumb-separator {
        color: rgba(255, 255, 255, 0.6);
        font-size: 18px;
        margin: 0 4px;
    }

    .breadcrumb-current {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        background: white;
        border-radius: 25px;
        color: #667eea;
        font-weight: 600;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .breadcrumb-current i {
        font-size: 16px;
    }

    /* Header Section */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }

    .page-title h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 5px;
    }

    .page-title p {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-back {
        background: white;
        color: #64748b;
        border: 2px solid #e2e8f0;
    }

    .btn-back:hover {
        background: #f1f5f9;
        color: #475569;
        transform: translateY(-2px);
    }

    .btn-create {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-create:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        color: white;
    }

    /* Filter Section */
    .filter-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 20px 25px;
        margin-bottom: 30px;
    }

    .filter-row {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: end;
    }

    .filter-group {
        flex: 1;
        min-width: 180px;
    }

    .filter-group label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn-filter {
        padding: 10px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-filter:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    /* Calendar Navigation */
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding: 15px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .calendar-nav-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        color: #475569;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .calendar-nav-btn:hover {
        background: #667eea;
        border-color: #667eea;
        color: white;
        transform: translateY(-2px);
    }

    .calendar-month-title {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }

    /* Calendar Grid */
    .calendar-wrapper {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .calendar-header-cell {
        padding: 15px;
        text-align: center;
        font-weight: 600;
        color: white;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .calendar-body {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
    }

    .calendar-cell {
        min-height: 120px;
        border: 1px solid #e2e8f0;
        padding: 10px;
        background: white;
        position: relative;
        transition: all 0.3s ease;
    }

    .calendar-cell:hover {
        background: #f8fafc;
    }

    .calendar-cell.other-month {
        background: #f8fafc;
    }

    .calendar-cell.other-month .date-number {
        color: #cbd5e1;
    }

    .calendar-cell.today {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    }

    .calendar-cell.today .date-number {
        background: #3b82f6;
        color: white;
    }

    .date-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        font-weight: 600;
        font-size: 14px;
        color: #1e293b;
        margin-bottom: 8px;
    }

    /* Exam Events */
    .exam-event {
        display: block;
        padding: 4px 8px;
        margin-bottom: 4px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        text-decoration: none;
        color: white;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: all 0.2s ease;
    }

    .exam-event:hover {
        transform: translateX(3px);
        color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    }

    .exam-event.continuous_assessment {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .exam-event.midterm {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .exam-event.final {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .exam-event.practical {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .exam-event.oral {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .exam-event.supplementary {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    }

    .exam-event.retake {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    .exam-event.makeup {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }

    .exam-event.project {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    .exam-event.internship {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    }

    .exam-event.online {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .exam-event.cancelled {
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        text-decoration: line-through;
    }

    .more-events {
        font-size: 11px;
        color: #667eea;
        font-weight: 600;
        cursor: pointer;
        padding: 2px 8px;
        background: #f0f4ff;
        border-radius: 4px;
        display: inline-block;
    }

    /* Legend */
    .calendar-legend {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        padding: 20px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #475569;
    }

    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 4px;
    }

    .legend-color.continuous_assessment {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .legend-color.midterm {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }

    .legend-color.final {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .legend-color.practical {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }

    .legend-color.oral {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .legend-color.supplementary {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    }

    .legend-color.retake {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    }

    .legend-color.makeup {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }

    .legend-color.project {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }

    .legend-color.internship {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    }

    .legend-color.online {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    /* Upcoming Exams Sidebar */
    .upcoming-sidebar {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 25px;
        margin-top: 30px;
    }

    .upcoming-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .upcoming-title i {
        color: #667eea;
    }

    .upcoming-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .upcoming-item {
        display: flex;
        gap: 15px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 12px;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .upcoming-item:hover {
        background: #f0f4ff;
        transform: translateX(5px);
    }

    .upcoming-date {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 55px;
        height: 55px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        color: white;
        flex-shrink: 0;
    }

    .upcoming-date .day {
        font-size: 20px;
        font-weight: 700;
        line-height: 1;
    }

    .upcoming-date .month {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .upcoming-info {
        flex: 1;
    }

    .upcoming-course {
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .upcoming-details {
        font-size: 12px;
        color: #64748b;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .upcoming-details span {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    @media (max-width: 768px) {
        .calendar-container {
            margin-left: 0;
            margin-right: 0;
            max-width: 100%;
        }

        .calendar-cell {
            min-height: 80px;
            padding: 5px;
        }

        .exam-event {
            font-size: 9px;
            padding: 2px 4px;
        }

        .calendar-header-cell {
            padding: 10px 5px;
            font-size: 11px;
        }
    }
</style>

<div class="calendar-container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb-wrapper" style="margin-top: 100px;">
        <ul class="breadcrumb-nav">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}" class="breadcrumb-link">
                    <i class="bx bxs-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="breadcrumb-separator">
                <i class="bx bx-chevron-right"></i>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('college.index') }}" class="breadcrumb-link">
                    <i class="bx bxs-graduation"></i>
                    <span>College</span>
                </a>
            </li>
            <li class="breadcrumb-separator">
                <i class="bx bx-chevron-right"></i>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('college.exams-management.dashboard') }}" class="breadcrumb-link">
                    <i class="bx bxs-book-content"></i>
                    <span>Exams & Academics</span>
                </a>
            </li>
            <li class="breadcrumb-separator">
                <i class="bx bx-chevron-right"></i>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('college.exam-schedules.index') }}" class="breadcrumb-link">
                    <i class="bx bxs-calendar"></i>
                    <span>Exam Schedules</span>
                </a>
            </li>
            <li class="breadcrumb-separator">
                <i class="bx bx-chevron-right"></i>
            </li>
            <li class="breadcrumb-item">
                <span class="breadcrumb-current">
                    <i class="bx bxs-calendar-event"></i>
                    <span>Calendar View</span>
                </span>
            </li>
        </ul>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1>
                <i class="bx bxs-calendar-event" style="color: #667eea;"></i>
                Exam Calendar
            </h1>
            <p>View all scheduled examinations in calendar format</p>
        </div>
        <div class="action-buttons">
            <a href="{{ route('college.exam-schedules.index') }}" class="btn-action btn-back">
                <i class="bx bx-list-ul"></i>
                List View
            </a>
            <a href="{{ route('college.exam-schedules.create') }}" class="btn-action btn-create">
                <i class="bx bx-plus"></i>
                Add Schedule
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card">
        <form method="GET" action="{{ route('college.exam-schedules.calendar') }}">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Academic Year</label>
                    <select name="academic_year_id" class="form-select">
                        <option value="">All Years</option>
                        @foreach($academicYears as $academicYear)
                            <option value="{{ $academicYear->id }}" {{ request('academic_year_id') == $academicYear->id ? 'selected' : '' }}>
                                {{ $academicYear->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Program</label>
                    <select name="program_id" class="form-select">
                        <option value="">All Programs</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group" style="flex: 0;">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn-filter">
                        <i class="bx bx-filter-alt me-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Calendar Navigation -->
    @php
        $currentDate = \Carbon\Carbon::create($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
    @endphp
    <div class="calendar-nav">
        <a href="{{ route('college.exam-schedules.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="calendar-nav-btn">
            <i class="bx bx-chevron-left"></i>
            {{ $prevMonth->format('M Y') }}
        </a>
        <h2 class="calendar-month-title">{{ $currentDate->format('F Y') }}</h2>
        <a href="{{ route('college.exam-schedules.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="calendar-nav-btn">
            {{ $nextMonth->format('M Y') }}
            <i class="bx bx-chevron-right"></i>
        </a>
    </div>

    <!-- Calendar Grid -->
    <div class="calendar-wrapper">
        <div class="calendar-header">
            <div class="calendar-header-cell">Sunday</div>
            <div class="calendar-header-cell">Monday</div>
            <div class="calendar-header-cell">Tuesday</div>
            <div class="calendar-header-cell">Wednesday</div>
            <div class="calendar-header-cell">Thursday</div>
            <div class="calendar-header-cell">Friday</div>
            <div class="calendar-header-cell">Saturday</div>
        </div>
        <div class="calendar-body">
            @php
                $startOfMonth = $currentDate->copy()->startOfMonth();
                $endOfMonth = $currentDate->copy()->endOfMonth();
                $startOfCalendar = $startOfMonth->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
                $endOfCalendar = $endOfMonth->copy()->endOfWeek(\Carbon\Carbon::SATURDAY);
                $today = \Carbon\Carbon::today();
                
                // Group schedules by date
                $schedulesByDate = $schedules->groupBy(function($schedule) {
                    return \Carbon\Carbon::parse($schedule->exam_date)->format('Y-m-d');
                });
            @endphp

            @for($date = $startOfCalendar->copy(); $date <= $endOfCalendar; $date->addDay())
                @php
                    $isOtherMonth = $date->month != $month;
                    $isToday = $date->isSameDay($today);
                    $dateKey = $date->format('Y-m-d');
                    $daySchedules = $schedulesByDate->get($dateKey, collect());
                @endphp
                <div class="calendar-cell {{ $isOtherMonth ? 'other-month' : '' }} {{ $isToday ? 'today' : '' }}">
                    <div class="date-number">{{ $date->day }}</div>
                    @foreach($daySchedules->take(3) as $schedule)
                        <a href="{{ route('college.exam-schedules.show', $schedule) }}" 
                           class="exam-event {{ $schedule->exam_type }} {{ $schedule->status == 'cancelled' ? 'cancelled' : '' }}"
                           title="{{ $schedule->course->code ?? '' }} - {{ $schedule->course->name ?? '' }}">
                            {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} {{ $schedule->course->code ?? 'Exam' }}
                        </a>
                    @endforeach
                    @if($daySchedules->count() > 3)
                        <span class="more-events">+{{ $daySchedules->count() - 3 }} more</span>
                    @endif
                </div>
            @endfor
        </div>

        <!-- Legend -->
        <div class="calendar-legend">
            <div class="legend-item">
                <span class="legend-color continuous_assessment"></span>
                <span>Continuous Assessment</span>
            </div>
            <div class="legend-item">
                <span class="legend-color midterm"></span>
                <span>Midterm Exam</span>
            </div>
            <div class="legend-item">
                <span class="legend-color final"></span>
                <span>Final Exam</span>
            </div>
            <div class="legend-item">
                <span class="legend-color practical"></span>
                <span>Practical</span>
            </div>
            <div class="legend-item">
                <span class="legend-color oral"></span>
                <span>Oral</span>
            </div>
            <div class="legend-item">
                <span class="legend-color supplementary"></span>
                <span>Supplementary</span>
            </div>
            <div class="legend-item">
                <span class="legend-color project"></span>
                <span>Project</span>
            </div>
            <div class="legend-item">
                <span class="legend-color online"></span>
                <span>Online</span>
            </div>
        </div>
    </div>

    <!-- Upcoming Exams -->
    @php
        $upcomingExams = $schedules->where('exam_date', '>=', $today->format('Y-m-d'))
                                   ->where('status', '!=', 'cancelled')
                                   ->sortBy('exam_date')
                                   ->take(5);
    @endphp
    @if($upcomingExams->count() > 0)
        <div class="upcoming-sidebar">
            <h3 class="upcoming-title">
                <i class="bx bxs-calendar-star"></i>
                Upcoming Examinations
            </h3>
            <div class="upcoming-list">
                @foreach($upcomingExams as $exam)
                    <a href="{{ route('college.exam-schedules.show', $exam) }}" class="upcoming-item">
                        <div class="upcoming-date">
                            <span class="day">{{ \Carbon\Carbon::parse($exam->exam_date)->format('d') }}</span>
                            <span class="month">{{ \Carbon\Carbon::parse($exam->exam_date)->format('M') }}</span>
                        </div>
                        <div class="upcoming-info">
                            <div class="upcoming-course">{{ $exam->course->code ?? '' }} - {{ $exam->course->name ?? 'Exam' }}</div>
                            <div class="upcoming-details">
                                <span><i class="bx bx-time-five"></i> {{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}</span>
                                <span><i class="bx bx-map"></i> {{ $exam->venue ?? 'TBA' }}</span>
                                <span><i class="bx bx-book-reader"></i> {{ $exam->exam_type_name }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>

@endsection
