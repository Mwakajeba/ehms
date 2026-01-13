@extends('layouts.main')

@section('title', 'Attendance Session Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Student Information', 'url' => route('school.student-informations.index'), 'icon' => 'bx bx-group'],
            ['label' => 'Attendance', 'url' => route('school.attendance.index'), 'icon' => 'bx bx-calendar-check'],
            ['label' => 'Session Details', 'url' => '#', 'icon' => 'bx bx-calendar-event']
        ]" />
        <h6 class="mb-0 text-uppercase">ATTENDANCE SESSION DETAILS</h6>
        <hr />

        <!-- Session Overview -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bx bx-calendar-event me-2"></i>
                                {{ $attendanceSession->class->name }} - {{ $attendanceSession->stream->name }}
                            </h5>
                            <small class="text-muted">{{ $attendanceSession->session_date->format('l, F j, Y') }}</small>
                        </div>
                        <div class="d-flex gap-2">
                            @if($attendanceSession->status === 'active')
                                <button type="button" class="btn btn-success btn-sm" id="markAttendanceBtn">
                                    <i class="bx bx-check-circle me-1"></i> Mark All Attendance
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" id="saveAttendance">
                                    <i class="bx bx-save me-1"></i> Finalize Session
                                </button>
                            @elseif($attendanceSession->status === 'completed')
                                <button type="button" class="btn btn-warning btn-sm" id="reopenSession">
                                    <i class="bx bx-refresh me-1"></i> Re-open Session
                                </button>
                            @endif
                            <a href="{{ route('school.attendance.edit', $attendanceSession) }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-edit me-1"></i> Edit Session
                            </a>
                            <a href="{{ route('school.attendance-management.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-arrow-back me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $stats = $attendanceSession->getAttendanceStats();
                        @endphp
                        <div class="row">
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="bx bx-group"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $stats['total_students'] }}</h4>
                                        <p>Total Students</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="stat-card present">
                                    <div class="stat-icon">
                                        <i class="bx bx-check-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $stats['present'] }}</h4>
                                        <p>Present</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="stat-card absent">
                                    <div class="stat-icon">
                                        <i class="bx bx-x-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $stats['absent'] }}</h4>
                                        <p>Absent</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="stat-card late">
                                    <div class="stat-icon">
                                        <i class="bx bx-time"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $stats['late'] }}</h4>
                                        <p>Late</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="stat-card sick">
                                    <div class="stat-icon">
                                        <i class="bx bx-plus-medical"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $stats['sick'] }}</h4>
                                        <p>Sick</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-sm-6">
                                <div class="stat-card not-marked">
                                    <div class="stat-icon">
                                        <i class="bx bx-question-mark"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h4>{{ $students->count() - $stats['total_students'] }}</h4>
                                        <p>Not Marked</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="info-card">
                                    <h6><i class="bx bx-info-circle me-2"></i>Session Information</h6>
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Date:</strong></td>
                                            <td>{{ $attendanceSession->session_date->format('F j, Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Class:</strong></td>
                                            <td>{{ $attendanceSession->class->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Stream:</strong></td>
                                            <td>{{ $attendanceSession->stream->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Academic Year:</strong></td>
                                            <td>{{ $attendanceSession->academicYear->year_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $attendanceSession->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($attendanceSession->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($attendanceSession->notes)
                                        <tr>
                                            <td><strong>Notes:</strong></td>
                                            <td>{{ $attendanceSession->notes }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-card">
                                    <h6><i class="bx bx-bar-chart me-2"></i>Attendance Summary</h6>
                                    <div class="attendance-chart">
                                        <canvas id="attendanceChart" width="200" height="200"></canvas>
                                    </div>
                                    <div class="attendance-stats mt-3">
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                 style="width: {{ $attendanceSession->studentAttendances->count() > 0 ? ($attendanceSession->studentAttendances->where('status', 'present')->count() / $attendanceSession->studentAttendances->count()) * 100 : 0 }}%"
                                                 aria-valuenow="{{ $attendanceSession->studentAttendances->where('status', 'present')->count() }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="{{ $attendanceSession->studentAttendances->count() }}">
                                                Present: {{ $attendanceSession->studentAttendances->where('status', 'present')->count() }}
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            Attendance Rate: {{ $attendanceSession->studentAttendances->count() > 0 ? round(($attendanceSession->studentAttendances->where('status', 'present')->count() / $attendanceSession->studentAttendances->count()) * 100, 1) : 0 }}%
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Attendance Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bx bx-list-ul me-2"></i>
                            Student Attendance Records
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($attendanceSession->status === 'active')
                            <div class="alert alert-info mb-4">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Quick Attendance Marking:</strong>
                                <ul class="mb-0 mt-2">
                                    <li><strong>Individual:</strong> Click ✓ (Present), ✓ (Late), ✓ (Absent), or ✓ (Sick) buttons for each student to add time and notes</li>
                                    <li><strong>Advanced:</strong> Use "Mark All Attendance" for detailed marking with time and notes</li>
                                    <li><strong>Finalize:</strong> Click "Finalize Session" when done to complete the session</li>
                                </ul>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="bx bx-keyboard me-1"></i>
                                        <strong>Tips:</strong> Changes are saved automatically. Use "Finalize Session" to complete the attendance session.
                                    </small>
                                </div>
                            </div>
                        @elseif($attendanceSession->status === 'completed')
                            <div class="alert alert-warning mb-4">
                                <i class="bx bx-lock me-2"></i>
                                <strong>Session Completed:</strong> This attendance session has been finalized. To make changes to individual student records, click "Re-open Session" above.
                            </div>
                        @endif

                        @if($students->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="attendanceTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Admission No</th>
                                            <th>Status</th>
                                            <th>Time In</th>
                                            <th>Time Out</th>
                                            <th>Notes</th>
                                            @if($attendanceSession->status === 'active')
                                                <th>Actions</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $student)
                                            @php
                                                $attendance = $attendanceSession->studentAttendances->where('student_id', $student->id)->first();
                                            @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-2">
                                                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                                                    </div>
                                                    {{ $student->first_name }} {{ $student->last_name }}
                                                </div>
                                            </td>
                                            <td>{{ $student->admission_number }}</td>
                                            <td>
                                                @if($attendance)
                                                    <span class="badge bg-{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'absent' ? 'danger' : ($attendance->status === 'late' ? 'warning' : ($attendance->status === 'sick' ? 'info' : 'secondary'))) }}">
                                                        <i class="bx bx-{{ $attendance->status === 'present' ? 'check-circle' : ($attendance->status === 'absent' ? 'x-circle' : ($attendance->status === 'late' ? 'time' : ($attendance->status === 'sick' ? 'plus-medical' : 'circle'))) }} me-1"></i>
                                                        {{ ucfirst($attendance->status) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Not Marked</span>
                                                @endif
                                            </td>
                                            <td>{{ $attendance && $attendance->time_in ? $attendance->time_in->format('H:i') : '-' }}</td>
                                            <td>{{ $attendance && $attendance->time_out ? $attendance->time_out->format('H:i') : '-' }}</td>
                                            <td>{{ $attendance && $attendance->notes ? $attendance->notes : '-' }}</td>
                                            @if($attendanceSession->status === 'active')
                                            <td>
                                                <div class="btn-group attendance-actions" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-success mark-attendance-btn"
                                                            data-student-id="{{ $student->id }}"
                                                            data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                                                            data-status="present"
                                                            data-existing-time-in="{{ $attendance && $attendance->time_in ? $attendance->time_in->format('H:i') : '' }}"
                                                            data-existing-time-out="{{ $attendance && $attendance->time_out ? $attendance->time_out->format('H:i') : '' }}"
                                                            data-existing-notes="{{ $attendance && $attendance->notes ? $attendance->notes : '' }}"
                                                            title="Mark Present">
                                                        <i class="bx bx-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-warning mark-attendance-btn"
                                                            data-student-id="{{ $student->id }}"
                                                            data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                                                            data-status="late"
                                                            data-existing-time-in="{{ $attendance && $attendance->time_in ? $attendance->time_in->format('H:i') : '' }}"
                                                            data-existing-time-out="{{ $attendance && $attendance->time_out ? $attendance->time_out->format('H:i') : '' }}"
                                                            data-existing-notes="{{ $attendance && $attendance->notes ? $attendance->notes : '' }}"
                                                            title="Mark Late">
                                                        <i class="bx bx-time"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger mark-attendance-btn"
                                                            data-student-id="{{ $student->id }}"
                                                            data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                                                            data-status="absent"
                                                            data-existing-time-in="{{ $attendance && $attendance->time_in ? $attendance->time_in->format('H:i') : '' }}"
                                                            data-existing-time-out="{{ $attendance && $attendance->time_out ? $attendance->time_out->format('H:i') : '' }}"
                                                            data-existing-notes="{{ $attendance && $attendance->notes ? $attendance->notes : '' }}"
                                                            title="Mark Absent">
                                                        <i class="bx bx-x"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-info mark-attendance-btn"
                                                            data-student-id="{{ $student->id }}"
                                                            data-student-name="{{ $student->first_name }} {{ $student->last_name }}"
                                                            data-status="sick"
                                                            data-existing-time-in="{{ $attendance && $attendance->time_in ? $attendance->time_in->format('H:i') : '' }}"
                                                            data-existing-time-out="{{ $attendance && $attendance->time_out ? $attendance->time_out->format('H:i') : '' }}"
                                                            data-existing-notes="{{ $attendance && $attendance->notes ? $attendance->notes : '' }}"
                                                            title="Mark Sick">
                                                        <i class="bx bx-plus-medical"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bx bx-users display-1 text-muted"></i>
                                <h4 class="mt-3">No Students Found</h4>
                                <p class="text-muted">No students are enrolled in this class and stream for the selected academic year.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark Attendance Modal -->
<div class="modal fade" id="markAttendanceModal" tabindex="-1" aria-labelledby="markAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="markAttendanceModalLabel">
                    <i class="bx bx-check-circle me-2"></i>Mark Attendance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    Mark attendance for all students in this session. You can also mark individual students using the action buttons in the table.
                </div>

                <form id="bulkAttendanceForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulkStatus" class="form-label">Mark All Students As:</label>
                                <select class="form-select" id="bulkStatus" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="late">Late</option>
                                    <option value="sick">Sick</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulkTimeIn" class="form-label">Time In:</label>
                                <input type="time" class="form-control" id="bulkTimeIn" name="time_in">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulkTimeOut" class="form-label">Time Out:</label>
                                <input type="time" class="form-control" id="bulkTimeOut" name="time_out">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="bulkNotes" class="form-label">Notes (Optional):</label>
                                <textarea class="form-control" id="bulkNotes" name="notes" rows="3" placeholder="Add notes for all students"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBulkAttendance">
                    <i class="bx bx-save me-1"></i> Save Attendance
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Individual Attendance Modal -->
<div class="modal fade" id="individualAttendanceModal" tabindex="-1" aria-labelledby="individualAttendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="individualAttendanceModalLabel">
                    <i class="bx bx-user me-2"></i>Mark Attendance for <span id="studentName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    Add or update time and notes for this student's attendance record. Existing values are pre-filled if available.
                </div>

                <form id="individualAttendanceForm">
                    @csrf
                    <input type="hidden" id="individualStudentId" name="student_id">
                    <input type="hidden" id="individualStatus" name="status">

                    <div class="mb-3">
                        <label for="individualTimeIn" class="form-label">Time In (Optional):</label>
                        <input type="time" class="form-control" id="individualTimeIn" name="time_in">
                    </div>

                    <div class="mb-3">
                        <label for="individualTimeOut" class="form-label">Time Out (Optional):</label>
                        <input type="time" class="form-control" id="individualTimeOut" name="time_out">
                    </div>

                    <div class="mb-3">
                        <label for="individualNotes" class="form-label">Notes (Optional):</label>
                        <textarea class="form-control" id="individualNotes" name="notes" rows="3" placeholder="Add notes for this student"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveIndividualAttendance">
                    <i class="bx bx-save me-1"></i> Save Attendance
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 4px solid #0d6efd;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card.present {
        border-left-color: #198754;
    }

    .stat-card.absent {
        border-left-color: #dc3545;
    }

    .stat-card.late {
        border-left-color: #fd7e14;
    }

    .stat-card.sick {
        border-left-color: #0dcaf0;
    }

    .stat-card.not-marked {
        border-left-color: #6c757d;
    }

    .stat-card.not-marked .stat-icon {
        background: rgba(108, 117, 125, 0.1);
        color: #6c757d;
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.2rem;
    }

    .stat-card.present .stat-icon {
        background: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .stat-card.absent .stat-icon {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .stat-card.late .stat-icon {
        background: rgba(253, 126, 20, 0.1);
        color: #fd7e14;
    }

    .stat-card.sick .stat-icon {
        background: rgba(13, 202, 240, 0.1);
        color: #0dcaf0;
    }

    .stat-content h4 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 700;
        color: #333;
    }

    .stat-content p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 0.9rem;
    }

    .info-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        height: 100%;
    }

    .info-card h6 {
        color: #333;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .info-card table td {
        padding: 8px 0;
        border: none;
    }

    .info-card table td:first-child {
        font-weight: 600;
        color: #555;
        width: 120px;
    }

    .attendance-chart {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
    }

    .avatar-circle {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
        transition: all 0.2s ease;
    }

    .btn-group .btn:hover {
        transform: scale(1.1);
    }

    .btn-group .btn:active {
        transform: scale(0.95);
    }

    .attendance-actions {
        opacity: 0.7;
        transition: opacity 0.3s ease;
    }

    .status-indicator {
        position: relative;
    }

    .status-indicator.updated::after {
        content: '';
        position: absolute;
        top: -5px;
        right: -5px;
        width: 8px;
        height: 8px;
        background: #ffc107;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 4px rgba(255, 193, 7, 0.5);
    }

    kbd {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 3px;
        box-shadow: 0 1px 0 rgba(0,0,0,0.2), 0 0 0 2px #fff inset;
        color: #495057;
        display: inline-block;
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1;
        padding: 2px 4px;
    }

    .progress {
        height: 25px;
    }

    .progress-bar {
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 15px;
            padding: 15px;
        }

        .stat-content h4 {
            font-size: 1.4rem;
        }

        .card-header .d-flex {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start !important;
        }

        .card-header .d-flex > div:last-child {
            align-self: stretch;
            display: flex;
            justify-content: space-between;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#attendanceTable').DataTable({
        responsive: true,
        pageLength: 25,
        ordering: true,
        searching: true,
        paging: true,
        info: true,
        columnDefs: [
            { orderable: false, targets: -1 }
        ],
        language: {
            search: "Search students:",
            lengthMenu: "Show _MENU_ students per page",
            info: "Showing _START_ to _END_ of _TOTAL_ students"
        }
    });

    // Initialize attendance chart
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceRecords = @json($attendanceSession->studentAttendances->groupBy('status')->map->count());
    const totalStudents = @json($students->count());
    const markedStudents = @json($attendanceSession->studentAttendances->count());
    const unmarkedStudents = totalStudents - markedStudents;

    const attendanceChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Sick', 'Not Marked'],
            datasets: [{
                data: [
                    attendanceRecords.present || 0,
                    attendanceRecords.absent || 0,
                    attendanceRecords.late || 0,
                    attendanceRecords.sick || 0,
                    unmarkedStudents
                ],
                backgroundColor: [
                    '#198754', // Present - green
                    '#dc3545', // Absent - red
                    '#fd7e14', // Late - orange
                    '#0dcaf0', // Sick - blue
                    '#6c757d'  // Not Marked - gray
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Mark attendance modal
    $('#markAttendanceBtn').on('click', function() {
        console.log('Mark attendance button clicked');
        $('#markAttendanceModal').modal('show');
    });

    // Save bulk attendance
    $('#saveBulkAttendance').on('click', function() {
        console.log('Save bulk attendance button clicked');
        const status = $('#bulkStatus').val();
        const timeIn = $('#bulkTimeIn').val();
        const timeOut = $('#bulkTimeOut').val();
        const notes = $('#bulkNotes').val();

        console.log('Form values:', { status, timeIn, timeOut, notes });

        if (!status) {
            Swal.fire({
                icon: 'warning',
                title: 'Status Required',
                text: 'Please select a status for all students.'
            });
            return;
        }

        Swal.fire({
            title: 'Mark Attendance for All Students?',
            text: 'This will mark attendance for ALL students in this class and stream. This action cannot be undone.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Mark Attendance',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...');

                console.log('Bulk marking with status:', status, 'timeIn:', timeIn, 'timeOut:', timeOut);

                $.ajax({
                    url: '{{ route("school.attendance.mark-attendance", $attendanceSession) }}',
                    method: 'POST',
                    data: {
                        status: status,
                        time_in: timeIn || null,
                        time_out: timeOut || null,
                        notes: notes || null,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        console.log('AJAX success response:', response);
                        if (response.success) {
                            $('#markAttendanceModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Attendance marked successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to save attendance'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('AJAX error:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while saving attendance. Please try again.'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });
    });

    // Individual attendance marking - show modal
    $('.mark-attendance-btn').on('click', function() {
        const studentId = $(this).data('student-id');
        const studentName = $(this).data('student-name');
        const status = $(this).data('status');
        const existingTimeIn = $(this).data('existing-time-in') || '';
        const existingTimeOut = $(this).data('existing-time-out') || '';
        const existingNotes = $(this).data('existing-notes') || '';

        // Populate modal
        $('#studentName').text(studentName);
        $('#individualStudentId').val(studentId);
        $('#individualStatus').val(status);

        // Populate with existing values or clear if no existing data
        $('#individualTimeIn').val(existingTimeIn);
        $('#individualTimeOut').val(existingTimeOut);
        $('#individualNotes').val(existingNotes);

        // Show modal
        $('#individualAttendanceModal').modal('show');
    });

    // Save individual attendance
    $('#saveIndividualAttendance').on('click', function() {
        const studentId = $('#individualStudentId').val();
        const status = $('#individualStatus').val();
        const timeIn = $('#individualTimeIn').val();
        const timeOut = $('#individualTimeOut').val();
        const notes = $('#individualNotes').val();

        // Show loading state
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...');

        // Update UI immediately for better UX
        updateStudentStatusUI(studentId, status);

        $.ajax({
            url: '{{ route("school.attendance.mark-attendance", $attendanceSession) }}',
            method: 'POST',
            data: {
                student_id: studentId,
                status: status,
                time_in: timeIn || null,
                time_out: timeOut || null,
                notes: notes || null,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#individualAttendanceModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Attendance updated successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to update attendance'
                    });
                    // Revert UI change on error
                    revertStudentStatusUI(studentId);
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                console.error('Error updating attendance:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating attendance. Please try again.'
                });
                // Revert UI change on error
                revertStudentStatusUI(studentId);
                btn.prop('disabled', false).html(originalText);
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Re-open session
    $('#reopenSession').on('click', function() {
        Swal.fire({
            title: 'Re-open Attendance Session?',
            text: 'This will allow you to make changes to individual student attendance records.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#fd7e14',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Re-open Session',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Re-opening...');

                $.ajax({
                    url: '{{ route("school.attendance.update", $attendanceSession) }}',
                    method: 'PUT',
                    data: {
                        status: 'active',
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Session Re-opened!',
                                text: 'You can now make changes to individual student attendance records.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to re-open session'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error re-opening session:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while re-opening the session. Please try again.'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });
    });

    // Save attendance (finalize session)
    $('#saveAttendance').on('click', function() {
        Swal.fire({
            title: 'Finalize Attendance Session?',
            text: 'Once saved, individual changes will no longer be possible. You can re-open the session later if needed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Finalize Session',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...');

                $.ajax({
                    url: '{{ route("school.attendance.mark-attendance", $attendanceSession) }}',
                    method: 'POST',
                    data: {
                        finalize: true,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Session Finalized!',
                                text: 'Attendance session has been completed successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to save attendance'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.error('Error saving attendance:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred while saving. Please try again.'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });
    });

    // Helper functions
    function updateStudentStatusUI(studentId, status) {
        const row = $(`button[data-student-id="${studentId}"]`).closest('tr');
        const statusCell = row.find('td').eq(3); // Status column

        const statusColors = {
            'present': 'success',
            'absent': 'danger',
            'late': 'warning',
            'sick': 'info'
        };

        const statusIcons = {
            'present': 'check-circle',
            'absent': 'x-circle',
            'late': 'time',
            'sick': 'plus-medical'
        };

        const color = statusColors[status] || 'secondary';
        const icon = statusIcons[status] || 'circle';

        statusCell.html(`
            <span class="badge bg-${color}">
                <i class="bx bx-${icon} me-1"></i>
                ${status.charAt(0).toUpperCase() + status.slice(1)}
            </span>
        `);
    }

    function revertStudentStatusUI(studentId) {
        const row = $(`button[data-student-id="${studentId}"]`).closest('tr');
        const statusCell = row.find('td').eq(3);
        statusCell.html('<span class="badge bg-secondary">Not Marked</span>');
    }

    // Keyboard shortcuts for quick actions
    $(document).on('keydown', function(e) {
        // Only work when not typing in inputs
        if ($(e.target).is('input, textarea, select')) return;

        // Only work on active attendance sessions
        if ('{{ $attendanceSession->status }}' !== 'active') return;

        switch(e.key.toLowerCase()) {
            case 's':
                e.preventDefault();
                if ($('#saveAttendance').is(':visible')) {
                    $('#saveAttendance').click();
                }
                break;
            case 'm':
                e.preventDefault();
                $('#markAttendanceBtn').click();
                break;
        }
    });

    // Add tooltip for keyboard shortcuts
    $(document).ready(function() {
        // Add keyboard shortcut hints
        const shortcutHint = `
            <div class="mt-2">
                <small class="text-muted">
                    <i class="bx bx-keyboard me-1"></i>
                    <strong>Keyboard Shortcuts:</strong>
                    <kbd class="mx-1">M</kbd> Advanced Marking |
                    <kbd class="mx-1">S</kbd> Finalize Session
                </small>
            </div>
        `;
        $('.alert-info').append(shortcutHint);
    });

    // Initialize attendance for all students - functionality not implemented
    // Removed to avoid route errors
});
</script>
@endpush