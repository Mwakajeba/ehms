@extends('layouts.main')

@section('title', 'Exam Class Assignment Group Details')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Class Assignments', 'url' => route('school.exam-class-assignments.index'), 'icon' => 'bx bx-target-lock'],
            ['label' => 'Group Details', 'url' => '#', 'icon' => 'bx bx-show']
        ]" />

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-target-lock me-1 font-22 text-info"></i>
                                <span class="h5 mb-0 text-info">Exam Class Assignment Group Details</span>
                            </div>
                            <div>
                                <a href="{{ route('school.exam-class-assignments.index') }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to List
                                </a>
                                @if($assignments->isNotEmpty())
                                <a href="{{ route('school.exam-class-assignments.edit-group', [\Vinkla\Hashids\Facades\Hashids::encode($examType->id ?? ''), \Vinkla\Hashids\Facades\Hashids::encode($class->id ?? ''), \Vinkla\Hashids\Facades\Hashids::encode($academicYear->id ?? '')]) }}" class="btn btn-warning">
                                    <i class="bx bx-edit me-1"></i> Edit Group
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteGroupModal">
                                    <i class="bx bx-trash me-1"></i> Delete Group
                                </button>
                                @endif
                            </div>
                        </div>
                        <hr />

                        <!-- Group Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <i class="bx bx-book-open font-24 mb-2"></i>
                                        <h6 class="card-title">Exam Type</h6>
                                        <h4 class="mb-0">{{ $examType->name ?? 'N/A' }}</h4>
                                        @if($examType && $examType->description)
                                        <small>{{ Str::limit($examType->description, 50) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="bx bx-group font-24 mb-2"></i>
                                        <h6 class="card-title">Class</h6>
                                        <h4 class="mb-0">{{ $class->name ?? 'N/A' }}</h4>
                                        @if($class && $class->level)
                                        <small>Level: {{ $class->level }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <i class="bx bx-calendar font-24 mb-2"></i>
                                        <h6 class="card-title">Academic Year</h6>
                                        <h4 class="mb-0">{{ $academicYear->year_name ?? 'N/A' }}</h4>
                                        @if($academicYear && $academicYear->start_date && $academicYear->end_date)
                                        <small>{{ $academicYear->start_date->format('M Y') }} - {{ $academicYear->end_date->format('M Y') }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <i class="bx bx-target-lock font-24 mb-2"></i>
                                        <h6 class="card-title">Total Assignments</h6>
                                        <h4 class="mb-0">{{ $assignments->count() }}</h4>
                                        <small>Subject Assignments</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Statistics Row -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-secondary text-white">
                                    <div class="card-body text-center">
                                        <i class="bx bx-user font-24 mb-2"></i>
                                        <h6 class="card-title">Total Students</h6>
                                        <h4 class="mb-0">{{ $students->count() }}</h4>
                                        <small>Active Students</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light text-dark">
                                    <div class="card-body text-center">
                                        <i class="bx bx-stream font-24 mb-2"></i>
                                        <h6 class="card-title">Streams</h6>
                                        <h4 class="mb-0">{{ $students->whereNotNull('stream_id')->unique('stream_id')->count() }}</h4>
                                        <small>Active Streams</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-dark text-white">
                                    <div class="card-body text-center">
                                        <i class="bx bx-male-female font-24 mb-2"></i>
                                        <h6 class="card-title">Gender Distribution</h6>
                                        <h4 class="mb-0">{{ $students->where('gender', 'male')->count() }}M / {{ $students->where('gender', 'female')->count() }}F</h4>
                                        <small>Male / Female</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="bx bx-bar-chart me-1"></i> Assignment Statistics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @php
                                                $statusCounts = $assignments->groupBy('status')->map->count();
                                                $totalAssignments = $assignments->count();
                                            @endphp
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h3 class="text-primary">{{ $statusCounts->get('assigned', 0) }}</h3>
                                                    <small class="text-muted">Assigned</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h3 class="text-warning">{{ $statusCounts->get('in_progress', 0) }}</h3>
                                                    <small class="text-muted">In Progress</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h3 class="text-success">{{ $statusCounts->get('completed', 0) }}</h3>
                                                    <small class="text-muted">Completed</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-center">
                                                    <h3 class="text-danger">{{ $statusCounts->get('cancelled', 0) }}</h3>
                                                    <small class="text-muted">Cancelled</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="bx bx-info-circle me-1"></i> Group Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <strong>Created:</strong>
                                            <span class="text-muted">{{ $assignments->isNotEmpty() && $assignments->first()->created_at ? $assignments->first()->created_at->format('M d, Y H:i') : 'N/A' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Last Updated:</strong>
                                            <span class="text-muted">{{ $assignments->isNotEmpty() && $assignments->first()->updated_at ? $assignments->first()->updated_at->format('M d, Y H:i') : 'N/A' }}</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Created By:</strong>
                                            <span class="text-muted">{{ $assignments->isNotEmpty() && $assignments->first()->creator ? $assignments->first()->creator->name : 'N/A' }}</span>
                                        </div>
                                        @if($assignments->isNotEmpty() && $assignments->first()->due_date)
                                        <div class="mb-0">
                                            <strong>Due Date:</strong>
                                            <span class="text-muted">{{ $assignments->first()->due_date->format('M d, Y') }}</span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Assignments Table -->
                        <div class="card">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="mb-0"><i class="bx bx-list-ul me-1"></i> Subject Assignments</h6>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#assignmentsTable" aria-expanded="true">
                                        <i class="bx bx-chevron-down"></i> Toggle Details
                                    </button>
                                </div>
                            </div>
                            <div class="card-body collapse show" id="assignmentsTable">
                                @if($assignments->isNotEmpty())
                                <div class="table-responsive">
                                    <table id="assignmentsDataTable" class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Subject</th>
                                                <th>Subject Code</th>
                                                <th>Stream</th>
                                                <th>Status</th>
                                                <th>Assigned Date</th>
                                                <th>Due Date</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($assignments as $index => $assignment)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $assignment->subject->name ?? 'N/A' }}</strong>
                                                </td>
                                                <td>{{ $assignment->subject->code ?? 'N/A' }}</td>
                                                <td>
                                                    @if($assignment->stream)
                                                        <span class="badge bg-secondary">{{ $assignment->stream->name }}</span>
                                                    @else
                                                        <span class="text-muted">All Streams</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($assignment->status == 'assigned')
                                                        <span class="badge bg-primary">Assigned</span>
                                                    @elseif($assignment->status == 'in_progress')
                                                        <span class="badge bg-warning">In Progress</span>
                                                    @elseif($assignment->status == 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($assignment->status == 'cancelled')
                                                        <span class="badge bg-danger">Cancelled</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($assignment->status ?? 'Unknown') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $assignment->assigned_date ? $assignment->assigned_date->format('M d, Y') : 'N/A' }}</td>
                                                <td>
                                                    @if($assignment->due_date)
                                                        @if($assignment->due_date->isPast() && $assignment->status != 'completed')
                                                            <span class="text-danger">{{ $assignment->due_date->format('M d, Y') }}</span>
                                                        @else
                                                            {{ $assignment->due_date->format('M d, Y') }}
                                                        @endif
                                                    @else
                                                        <span class="text-muted">No Due Date</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($assignment->notes)
                                                        <span title="{{ $assignment->notes }}">{{ Str::limit($assignment->notes, 30) }}</span>
                                                    @else
                                                        <span class="text-muted">No notes</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-5">
                                    <i class="bx bx-info-circle font-48 text-muted mb-3"></i>
                                    <h5 class="text-muted">No Assignments Found</h5>
                                    <p class="text-muted">There are no subject assignments for this exam type, class, and academic year combination.</p>
                                    <a href="{{ route('school.exam-class-assignments.index') }}" class="btn btn-primary">
                                        <i class="bx bx-arrow-back me-1"></i> Back to Assignments
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div class="card mt-4">
                            <div class="card-header d-flex align-items-center justify-content-between">
                                <h6 class="mb-0"><i class="bx bx-group me-1"></i> Student Exam Registrations ({{ $students->count() }})</h6>
                                <div>
                                    <a href="{{ route('school.exam-class-assignments.bulk-manage-registration', ['exam_type_hash' => request()->route('exam_type_hash'), 'class_hash' => request()->route('class_hash'), 'academic_year_hash' => request()->route('academic_year_hash')]) }}" class="btn btn-sm btn-success me-2" title="Bulk manage exam registrations for all students">
                                        <i class="bx bx-edit me-1"></i> Bulk Manage Registration
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#studentsTable" aria-expanded="true">
                                        <i class="bx bx-chevron-down"></i> Toggle Students
                                    </button>
                                </div>
                            </div>
                            <div class="card-body collapse show" id="studentsTable">
                                @if($students->isNotEmpty())
                                <div class="table-responsive">
                                    <table id="studentsDataTable" class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Admission No.</th>
                                                <th>Student Name</th>
                                                <th>Gender</th>
                                                <th>Stream</th>
                                                <th>Registration Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $index => $student)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $student->admission_number ?? 'N/A' }}</strong>
                                                </td>
                                                <td>
                                                    <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>
                                                </td>
                                                <td>
                                                    @if($student->gender == 'male')
                                                        <span class="badge bg-primary">Male</span>
                                                    @elseif($student->gender == 'female')
                                                        <span class="badge bg-danger">Female</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($student->gender ?? 'Unknown') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($student->stream)
                                                        <span class="badge bg-info">{{ $student->stream->name }}</span>
                                                    @else
                                                        <span class="text-muted">No Stream</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $registrationStatuses = [];
                                                        $totalSubjects = $assignments->count();
                                                        $registeredCount = 0;
                                                        $exemptedCount = 0;
                                                        $absentCount = 0;
                                                        $attendedCount = 0;

                                                        foreach($assignments as $assignment) {
                                                            $registration = \App\Models\SchoolExamRegistration::where('exam_class_assignment_id', $assignment->id)
                                                                ->where('student_id', $student->id)
                                                                ->first();
                                                            $status = $registration ? $registration->status : 'not_registered';
                                                            $registrationStatuses[$assignment->id] = $status;

                                                            if ($status === 'registered') $registeredCount++;
                                                            elseif ($status === 'exempted') $exemptedCount++;
                                                            elseif ($status === 'absent') $absentCount++;
                                                            elseif ($status === 'attended') $attendedCount++;
                                                        }
                                                    @endphp

                                                    @if($registeredCount + $exemptedCount + $absentCount + $attendedCount === 0)
                                                        <span class="badge bg-secondary">Not Registered</span>
                                                    @else
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @if($registeredCount > 0)
                                                                <span class="badge bg-primary" title="Registered for {{ $registeredCount }} subject(s)">{{ $registeredCount }} Reg</span>
                                                            @endif
                                                            @if($exemptedCount > 0)
                                                                <span class="badge bg-warning" title="Exempted from {{ $exemptedCount }} subject(s)">{{ $exemptedCount }} Exe</span>
                                                            @endif
                                                            @if($absentCount > 0)
                                                                <span class="badge bg-danger" title="Absent for {{ $absentCount }} subject(s)">{{ $absentCount }} Abs</span>
                                                            @endif
                                                            @if($attendedCount > 0)
                                                                <span class="badge bg-success" title="Attended {{ $attendedCount }} subject(s)">{{ $attendedCount }} Att</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('school.exam-class-assignments.manage-registration', ['exam_type_hash' => request()->route('exam_type_hash'), 'class_hash' => request()->route('class_hash'), 'academic_year_hash' => request()->route('academic_year_hash'), 'student_id' => $student->id]) }}" class="btn btn-sm btn-primary" title="Manage {{ $student->first_name }} {{ $student->last_name }}'s exam registration">
                                                        <i class="bx bx-edit me-1"></i> Manage Registration
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="text-center py-5">
                                    <i class="bx bx-user-x font-48 text-muted mb-3"></i>
                                    <h5 class="text-muted">No Students Found</h5>
                                    <p class="text-muted">There are no active students enrolled in this class.</p>
                                </div>
                                @endif
                            </div>
                        </div>

<!-- Delete Group Modal -->
@if($assignments->isNotEmpty())
<div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-labelledby="deleteGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteGroupModalLabel">Delete Assignment Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete all assignments for <strong>{{ $examType->name ?? 'N/A' }}</strong> - <strong>{{ $class->name ?? 'N/A' }}</strong> ({{ $academicYear->year_name ?? 'N/A' }})?</p>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
                <p>This will delete <strong>{{ $assignments->count() }}</strong> assignment(s).</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('school.exam-class-assignments.destroy-group', [\Vinkla\Hashids\Facades\Hashids::encode($examType->id ?? ''), \Vinkla\Hashids\Facades\Hashids::encode($class->id ?? ''), \Vinkla\Hashids\Facades\Hashids::encode($academicYear->id ?? '')]) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Group</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .card {
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: none;
        border-radius: 8px;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
    }

    .badge {
        font-size: 0.75rem;
    }

    .table th {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .text-primary { color: #0d6efd !important; }
    .text-success { color: #198754 !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #0dcaf0 !important; }
    .text-muted { color: #6c757d !important; }

    .bg-primary { background-color: #0d6efd !important; }
    .bg-success { background-color: #198754 !important; }
    .bg-warning { background-color: #ffc107 !important; }
    .bg-info { background-color: #0dcaf0 !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable for assignments
    $('#assignmentsDataTable').DataTable({
        pageLength: 5,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        }
    });

    // Initialize DataTable for students
    $('#studentsDataTable').DataTable({
        pageLength: 10,
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        columnDefs: [
            { orderable: false, targets: [6] } // Actions column not sortable
        ]
    });
});
</script>
@endpush