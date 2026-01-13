@extends('layouts.main')

@section('title', 'Master Examination Timetable')

@section('content')
<style>
    .timetable-container {
        margin-left: 250px;
        margin-right: 20px;
        margin-top: 70px;
        padding: 20px;
        max-width: calc(100vw - 280px);
    }
    
    .master-header {
        text-align: center;
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #818e76ff 0%, #deeddfff 100%);
        border-radius: 15px;
        color: black;
    }
    
    .master-header h2 {
        font-weight: 700;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    .master-header .subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
    }
    
    .info-badges {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    
    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.2);
        padding: 8px 16px;
        border-radius: 25px;
        font-weight: 500;
    }
    
    .timetable-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .timetable-card .card-header {
        background: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 15px 20px;
    }
    
    .timetable-table {
        margin-bottom: 0;
    }
    
    .timetable-table thead th {
        background: linear-gradient(135deg, #343a40 0%, #495057 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        padding: 15px 12px;
        border: none;
        white-space: nowrap;
    }
    
    .timetable-table tbody td {
        padding: 15px 12px;
        vertical-align: middle;
        border-color: #f0f0f0;
        font-size: 0.9rem;
    }
    
    .timetable-table tbody tr {
        transition: all 0.2s ease;
    }
    
    .timetable-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .timetable-table tbody tr:nth-child(even) {
        background: #fafbfc;
    }
    
    .timetable-table tbody tr:nth-child(even):hover {
        background: #f0f4f8;
    }
    
    .date-cell {
        font-weight: 600;
        color: #495057;
    }
    
    .day-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .day-monday { background: #e3f2fd; color: #1976d2; }
    .day-tuesday { background: #f3e5f5; color: #7b1fa2; }
    .day-wednesday { background: #e8f5e9; color: #388e3c; }
    .day-thursday { background: #fff3e0; color: #f57c00; }
    .day-friday { background: #ffebee; color: #d32f2f; }
    .day-saturday { background: #e0f7fa; color: #0097a7; }
    .day-sunday { background: #fce4ec; color: #c2185b; }
    
    .time-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        background: #e8f4fd;
        color: #0277bd;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.85rem;
    }
    
    .program-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    .program-hm { background: #fff3cd; color: #856404; }
    .program-tourism { background: #d1ecf1; color: #0c5460; }
    .program-catering { background: #d4edda; color: #155724; }
    .program-business { background: #f8d7da; color: #721c24; }
    .program-ict { background: #e2e3e5; color: #383d41; }
    .program-default { background: #f0f0f0; color: #555; }
    
    .level-badge {
        display: inline-block;
        padding: 4px 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    
    .course-name {
        font-weight: 500;
        color: #2d3748;
    }
    
    .course-code {
        display: inline-block;
        padding: 3px 8px;
        background: #f0f0f0;
        border-radius: 5px;
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        color: #666;
    }
    
    .venue-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.85rem;
    }
    
    .supervisor-name {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #555;
        font-weight: 500;
    }
    
    .supervisor-name i {
        color: #667eea;
    }
    
    .filter-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        margin-bottom: 25px;
    }
    
    .print-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 10px 25px;
        font-weight: 600;
    }
    
    .print-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .copy-btn {
        background: #28a745;
        border: none;
        padding: 10px 20px;
        font-weight: 600;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #888;
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 20px;
    }
    
    @media print {
        .timetable-container {
            margin: 0;
            padding: 10px;
            max-width: 100%;
        }
        .filter-card, .no-print {
            display: none !important;
        }
        .timetable-table thead th {
            background: #333 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .master-header {
            background: #667eea !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="timetable-container">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                <i class="bx bx-home-alt"></i> Dashboard
            </a>
            <i class="bx bx-chevron-right text-muted"></i>
            <a href="{{ route('college.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                <i class="bx bx-building"></i> College Management
            </a>
            <i class="bx bx-chevron-right text-muted"></i>
            <a href="{{ route('college.exam-schedules.index') }}" class="btn btn-light btn-sm d-flex align-items-center gap-2" style="border-radius: 8px; border: 1px solid #e2e8f0;">
                <i class="bx bx-calendar"></i> Exam Schedules
            </a>
            <i class="bx bx-chevron-right text-muted"></i>
            <span class="btn btn-light btn-sm d-flex align-items-center gap-2" style="border-radius: 8px; border: 1px solid #3b82f6; color: #1e40af; font-weight: 600;">
                <i class="bx bx-table"></i> Master Timetable
            </span>
            <i class="bx bx-chevron-right text-muted"></i>
            <a href="{{ route('college.exam-schedules.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2" style="border-radius: 8px;">
                <i class="bx bx-plus"></i> Create
            </a>
        </div>
    </nav>

    <!-- Header -->
    <div class="master-header">
        <h2><i class="bx bx-calendar-event me-2"></i>MASTER EXAMINATION TIMETABLE (COMBINED PROGRAMS)</h2>
        <p class="subtitle mb-0">{{ $branch->name ?? 'Institution Name' }}</p>
        <div class="info-badges">
            <span class="info-badge">
                <i class="bx bx-book"></i>
                <strong>Semester:</strong> {{ $semester->name ?? 'II' }}
            </span>
            <span class="info-badge">
                <i class="bx bx-time-five"></i>
                <strong>Exam Duration:</strong> {{ $examDuration ?? '2 Hours' }}
            </span>
            <span class="info-badge">
                <i class="bx bx-calendar"></i>
                <strong>Academic Year:</strong> {{ $academicYear->name ?? '2024/2025' }}
            </span>
        </div>
    </div>

    <!-- Filters -->
    <div class="card filter-card no-print">
        <div class="card-body">
            <form method="GET" action="{{ route('college.exam-schedules.master-timetable') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Academic Year</label>
                        <select name="academic_year_id" class="form-select form-select-sm">
                            <option value="">All Years</option>
                            @foreach($academicYears ?? [] as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Semester</label>
                        <select name="semester_id" class="form-select form-select-sm">
                            <option value="">All Semesters</option>
                            @foreach($semesters ?? [] as $sem)
                                <option value="{{ $sem->id }}" {{ request('semester_id') == $sem->id ? 'selected' : '' }}>
                                    {{ $sem->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Program</label>
                        <select name="program_id" class="form-select form-select-sm">
                            <option value="">All Programs</option>
                            @foreach($programs ?? [] as $prog)
                                <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>
                                    {{ $prog->short_name ?? $prog->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Exam Type</label>
                        <select name="exam_type" class="form-select form-select-sm">
                            <option value="">All Types</option>
                            <option value="final" {{ request('exam_type') == 'final' ? 'selected' : '' }}>Final Exam</option>
                            <option value="midterm" {{ request('exam_type') == 'midterm' ? 'selected' : '' }}>Midterm</option>
                            <option value="practical" {{ request('exam_type') == 'practical' ? 'selected' : '' }}>Practical</option>
                            <option value="supplementary" {{ request('exam_type') == 'supplementary' ? 'selected' : '' }}>Supplementary</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Date Range</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                            <i class="bx bx-filter-alt"></i> Filter
                        </button>
                        <a href="{{ route('college.exam-schedules.master-timetable') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-refresh"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
        <div>
            <span class="text-muted">
                <i class="bx bx-list-check me-1"></i>
                Showing <strong>{{ $schedules->count() ?? 0 }}</strong> examination schedules
            </span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('college.exam-schedules.create') }}" class="btn btn-primary">
                <i class="bx bx-plus-circle me-1"></i> Create Schedule
            </a>
            @if(isset($schedules) && $schedules->count() > 0)
            <button type="button" class="btn btn-success copy-btn" onclick="copyTimetable()">
                <i class="bx bx-copy me-1"></i> Copy
            </button>
            <a href="{{ route('college.exam-schedules.master-timetable-pdf', request()->query()) }}" class="btn btn-danger">
                <i class="bx bx-file-pdf me-1"></i> Export PDF
            </a>
            @endif
        </div>
    </div>

    <!-- Timetable Card -->
    <div class="card timetable-card">
        <div class="card-body p-0">
            @if(isset($schedules) && $schedules->count() > 0)
            <div class="table-responsive">
                <table class="table timetable-table" id="masterTimetable">
                    <thead>
                        <tr>
                            <th><i class="bx bx-calendar me-1"></i> Date</th>
                            <th><i class="bx bx-sun me-1"></i> Day</th>
                            <th><i class="bx bx-time me-1"></i> Time</th>
                            <th><i class="bx bx-book-reader me-1"></i> Program</th>
                            <th><i class="bx bx-layer me-1"></i> Level</th>
                            <th><i class="bx bx-book-open me-1"></i> Subject/Course</th>
                            <th><i class="bx bx-hash me-1"></i> Code</th>
                            <th><i class="bx bx-map me-1"></i> Room/Venue</th>
                            <th><i class="bx bx-user me-1"></i> Supervisor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedules as $schedule)
                        @php
                            $dayName = $schedule->exam_date ? $schedule->exam_date->format('l') : '';
                            $dayClass = 'day-' . strtolower($dayName);
                            $programCode = $schedule->program->short_name ?? ($schedule->program->name ?? 'N/A');
                            $programClass = 'program-default';
                            if(stripos($programCode, 'HM') !== false || stripos($programCode, 'Hotel') !== false) {
                                $programClass = 'program-hm';
                            } elseif(stripos($programCode, 'Tourism') !== false || stripos($programCode, 'TRM') !== false) {
                                $programClass = 'program-tourism';
                            } elseif(stripos($programCode, 'Catering') !== false || stripos($programCode, 'CAT') !== false) {
                                $programClass = 'program-catering';
                            } elseif(stripos($programCode, 'Business') !== false || stripos($programCode, 'BUS') !== false) {
                                $programClass = 'program-business';
                            } elseif(stripos($programCode, 'ICT') !== false || stripos($programCode, 'IT') !== false) {
                                $programClass = 'program-ict';
                            }
                        @endphp
                        <tr>
                            <td class="date-cell">
                                {{ $schedule->exam_date ? $schedule->exam_date->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td>
                                <span class="day-badge {{ $dayClass }}">{{ $dayName }}</span>
                            </td>
                            <td>
                                <span class="time-badge">
                                    <i class="bx bx-time-five"></i>
                                    {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '00:00' }}–{{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '00:00' }}
                                </span>
                            </td>
                            <td>
                                <span class="program-badge {{ $programClass }}">
                                    {{ $programCode }}
                                </span>
                            </td>
                            <td>
                                <span class="level-badge">
                                    {{ $schedule->level_short ?? ($schedule->level ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="course-name">
                                {{ $schedule->course->name ?? $schedule->exam_name ?? 'N/A' }}
                            </td>
                            <td>
                                <span class="course-code">
                                    {{ $schedule->course->code ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <span class="venue-badge">
                                    <i class="bx bx-building-house"></i>
                                    {{ $schedule->venue ?? 'TBA' }}
                                </span>
                            </td>
                            <td>
                                <span class="supervisor-name">
                                    <i class="bx bx-user-circle"></i>
                                    @if($schedule->invigilator)
                                        {{ $schedule->invigilator->first_name }} {{ $schedule->invigilator->last_name }}
                                    @elseif($schedule->invigilator_name)
                                        {{ $schedule->invigilator_name }}
                                    @else
                                        TBA
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <!-- Empty State - No Exam Schedules -->
            <div class="empty-state">
                <div class="py-5">
                    <i class="bx bx-calendar-x" style="font-size: 5rem; color: #ddd;"></i>
                    <h4 class="mt-3 text-muted">No Examination Schedules Found</h4>
                    <p class="text-muted mb-4">There are no exam schedules in the system yet.<br>Create exam schedules to display them in the master timetable.</p>
                    <a href="{{ route('college.exam-schedules.create') }}" class="btn btn-primary btn-lg">
                        <i class="bx bx-plus-circle me-2"></i> Create Exam Schedule
                    </a>
                    <a href="{{ route('college.exam-schedules.index') }}" class="btn btn-outline-secondary btn-lg ms-2">
                        <i class="bx bx-list-ul me-2"></i> View All Schedules
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Footer Note -->
    <div class="text-center mt-4 text-muted small">
        <p class="mb-1"><i class="bx bx-info-circle me-1"></i> All students must arrive at least 30 minutes before the exam start time.</p>
        <p class="mb-0">For any queries, please contact the Examinations Office.</p>
    </div>
</div>

<script>
function copyTimetable() {
    const table = document.getElementById('masterTimetable');
    let text = 'MASTER EXAMINATION TIMETABLE (COMBINED PROGRAMS)\n';
    text += 'Semester: {{ $semester->name ?? "II" }} | Exam Duration: {{ $examDuration ?? "2 Hours" }}\n\n';
    text += 'Date\tDay\tTime\tProgram\tLevel\tSubject/Course\tCode\tRoom/Venue\tSupervisor\n';
    text += '─'.repeat(100) + '\n';
    
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let rowText = '';
        cells.forEach((cell, index) => {
            let cellText = cell.textContent.trim().replace(/\s+/g, ' ');
            rowText += cellText + '\t';
        });
        text += rowText.trim() + '\n';
    });
    
    navigator.clipboard.writeText(text).then(() => {
        // Show success toast
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 p-3';
        toast.style.zIndex = '11';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-header bg-success text-white">
                    <i class="bx bx-check-circle me-2"></i>
                    <strong class="me-auto">Copied!</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    Timetable copied to clipboard successfully!
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }).catch(err => {
        alert('Failed to copy timetable. Please try again.');
    });
}
</script>
@endsection
