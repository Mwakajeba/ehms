@extends('layouts.main')

@section('title', 'Bulk Timetable Entry')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'School Timetables', 'url' => route('school.timetables.index'), 'icon' => 'bx bx-time-five'],
            ['label' => 'Edit Timetable', 'url' => route('school.timetables.edit', $timetable->hashid), 'icon' => 'bx bx-edit'],
            ['label' => 'Bulk Entry', 'url' => '#', 'icon' => 'bx bx-grid-alt']
        ]" />
        <h6 class="mb-0 text-uppercase">BULK TIMETABLE ENTRY</h6>
        <hr />

        <!-- Header Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="card-title d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div><i class="bx bx-grid-alt me-1 font-22 text-primary"></i></div>
                        <div>
                            <h5 class="mb-0 text-primary">{{ $timetable->name }}</h5>
                            <small class="text-muted">Fill in all periods at once</small>
                        </div>
                    </div>
                    <a href="{{ route('school.timetables.edit', $timetable->hashid) }}" class="btn btn-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i> Back to Edit
                    </a>
                </div>
                <hr />
                
                <!-- Info Row -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-2">
                            <strong class="text-muted">Academic Year:</strong><br>
                            <span>{{ $timetable->academicYear->year_name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <strong class="text-muted">Class:</strong><br>
                            <span>{{ $timetable->classe->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <strong class="text-muted">Stream:</strong><br>
                            <span>{{ $timetable->stream->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-2">
                            <strong class="text-muted">Type:</strong><br>
                            <span class="badge bg-info">{{ ucfirst($timetable->timetable_type) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="alert alert-info border-0 shadow-sm">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <i class="bx bx-info-circle me-2 fs-5"></i>
                <div class="flex-grow-1">
                    <strong>Instructions:</strong> Fill in the subject, teacher, and room for each period. You can leave fields empty to skip. Click "Save All Entries" to save all filled entries at once.
                </div>
                <div>
                    <button type="button" class="btn btn-success" id="copyMondayToAllBtn">
                        <i class="bx bx-copy me-1"></i> Copy Monday to All Days
                    </button>
                </div>
            </div>
        </div>

            <!-- Bulk Entry Form -->
            <form id="bulkEntryForm">
                @csrf
                <input type="hidden" name="timetable_id" value="{{ $timetable->id }}">

                @foreach($daysOfWeek as $day)
                    @if(isset($periodsByDay[$day]) && $periodsByDay[$day]->isNotEmpty())
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-calendar me-2"></i>
                                    <h6 class="mb-0 fw-bold">{{ $day }}</h6>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width: 80px;" class="text-center">Period</th>
                                                <th style="width: 120px;" class="text-center">Time</th>
                                                <th>Subject <span class="text-danger">*</span></th>
                                                <th>Teacher</th>
                                                <th style="width: 120px;" class="text-center">Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($periodsByDay[$day] as $period)
                                                @php
                                                    $entryKey = $day . '-' . $period->period_number;
                                                    $existingEntry = $existingEntries->get($entryKey);
                                                    $periodTime = '';
                                                    if ($period->start_time) {
                                                        $startTime = $period->start_time instanceof \Carbon\Carbon 
                                                            ? $period->start_time->format('H:i') 
                                                            : substr($period->start_time, 0, 5);
                                                        $endTime = $period->end_time instanceof \Carbon\Carbon 
                                                            ? $period->end_time->format('H:i') 
                                                            : substr($period->end_time, 0, 5);
                                                        $periodTime = $startTime . ' - ' . $endTime;
                                                    }
                                                @endphp
                                                <tr class="{{ $period->is_break ? 'table-warning' : '' }}">
                                                    <td class="text-center">
                                                        <strong>{{ $period->period_number }}</strong>
                                                        @if($period->is_break)
                                                            <br><small class="badge bg-warning">Break</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center small">{{ $periodTime }}</td>
                                                    <td>
                                                        @if($period->is_break)
                                                            <span class="text-muted">{{ $period->period_name ?? 'Break' }}</span>
                                                        @else
                                                            <select class="form-select form-select-sm entry-subject" 
                                                                    name="entries[{{ $day }}_{{ $period->period_number }}][subject_id]"
                                                                    data-day="{{ $day }}"
                                                                    data-period="{{ $period->period_number }}">
                                                                <option value="">-- Select Subject --</option>
                                                                @foreach($subjects as $subject)
                                                                    <option value="{{ $subject->id }}" 
                                                                            data-requirement-type="{{ $subject->requirement_type ?? 'compulsory' }}"
                                                                            {{ $existingEntry && $existingEntry->subject_id == $subject->id ? 'selected' : '' }}>
                                                                        {{ $subject->name }} @if($subject->code)({{ $subject->code }})@endif
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="conflict-alert mt-1" id="conflict-subject-{{ $day }}-{{ $period->period_number }}" style="display: none;"></div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!$period->is_break)
                                                            <select class="form-select form-select-sm entry-teacher" 
                                                                    name="entries[{{ $day }}_{{ $period->period_number }}][teacher_id]"
                                                                    data-day="{{ $day }}"
                                                                    data-period="{{ $period->period_number }}">
                                                                <option value="">-- Select Teacher --</option>
                                                                @foreach($teachers as $teacher)
                                                                    <option value="{{ $teacher->id }}"
                                                                            {{ $existingEntry && $existingEntry->teacher_id == $teacher->id ? 'selected' : '' }}>
                                                                        {{ $teacher->first_name }} {{ $teacher->last_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <div class="conflict-alert mt-1" id="conflict-{{ $day }}-{{ $period->period_number }}" style="display: none;"></div>
                                                            {{-- Hidden fields for form submission --}}
                                                            <input type="hidden" name="entries[{{ $day }}_{{ $period->period_number }}][day_of_week]" value="{{ $day }}">
                                                            <input type="hidden" name="entries[{{ $day }}_{{ $period->period_number }}][period_number]" value="{{ $period->period_number }}">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(!$period->is_break)
                                                            <input type="hidden" class="entry-type" 
                                                                   name="entries[{{ $day }}_{{ $period->period_number }}][subject_type]"
                                                                   value="{{ $existingEntry ? $existingEntry->subject_type : 'compulsory' }}">
                                                            <span class="badge bg-{{ ($existingEntry && $existingEntry->subject_type == 'optional') ? 'warning' : 'success' }} requirement-type-badge">
                                                                {{ $existingEntry ? ucfirst($existingEntry->subject_type) : 'Compulsory' }}
                                                            </span>
                                                            <small class="d-block text-muted mt-1">Auto from subject</small>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                <!-- Action Buttons -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary" id="clearAllBtn">
                                    <i class="bx bx-refresh me-1"></i> Clear All
                                </button>
                                <button type="button" class="btn btn-outline-info" id="fillDayBtn">
                                    <i class="bx bx-copy me-1"></i> Copy Day
                                </button>
                            </div>
                            <div class="btn-group">
                                <a href="{{ route('school.timetables.edit', $timetable->hashid) }}" class="btn btn-secondary">
                                    <i class="bx bx-x me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i> Save All Entries
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .page-wrapper {
        padding: 0;
    }

    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 0.5rem;
    }

    .card-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        font-weight: 600;
    }

    .table {
        font-size: 0.875rem;
    }

    .table th {
        font-weight: 600;
        vertical-align: middle;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
    }

    .table-dark th {
        background-color: #343a40 !important;
        border-color: #454d55 !important;
    }

    .form-select-sm {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    .table-warning {
        background-color: #fff3cd !important;
    }

    .table-warning td {
        border-color: #ffc107 !important;
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .btn-group .btn {
        border-radius: 0.375rem;
    }

    .card-title {
        margin-bottom: 0;
    }

    .font-22 {
        font-size: 1.375rem !important;
    }

    .table-responsive {
        border-radius: 0.375rem;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #dee2e6;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }

    .alert {
        border-radius: 0.5rem;
    }

    /* Select2 Custom Styling */
    .select2-container--bootstrap-5 {
        width: 100% !important;
    }

    .select2-container--bootstrap-5 .select2-selection {
        min-height: 31px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        font-size: 0.875rem;
    }

    .select2-container--bootstrap-5 .select2-selection--single {
        height: 31px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: 29px;
        padding-left: 8px;
        padding-right: 20px;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 29px;
        right: 8px;
    }

    .select2-container--bootstrap-5.select2-container--open .select2-selection {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }

    .select2-container--bootstrap-5 .select2-results__option {
        padding: 6px 12px;
        font-size: 0.875rem;
    }

    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 6px 12px;
        font-size: 0.875rem;
        margin: 8px;
        width: calc(100% - 16px);
    }

    .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        outline: 0;
    }

    .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
        background-color: #0d6efd;
        color: white;
    }

    .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
        background-color: #6c757d;
        color: white;
    }

    .conflict-alert {
        font-size: 11px;
    }

    .alert-sm {
        padding: 0.25rem 0.5rem;
        margin-bottom: 0.25rem;
        font-size: 10px;
    }

    .table-danger {
        background-color: #f8d7da !important;
    }

    .table-warning {
        background-color: #fff3cd !important;
    }

    .table-info {
        background-color: #d1ecf1 !important;
    }
</style>
@endpush

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for all select inputs with live search
    function initializeSelect2() {
        $('.entry-subject, .entry-teacher').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: function() {
                return $(this).find('option:first').text() || 'Search...';
            },
            allowClear: true,
            minimumResultsForSearch: 0, // Always show search box
            dropdownParent: $(document.body), // Append to body to avoid overflow issues
            matcher: function(params, data) {
                // Custom matcher for better search
                if ($.trim(params.term) === '') {
                    return data;
                }
                
                var term = params.term.toLowerCase();
                var text = data.text.toLowerCase();
                
                // Search in the text
                if (text.indexOf(term) > -1) {
                    return data;
                }
                
                return null;
            }
        });
    }
    
    // Initialize Select2 on page load
    initializeSelect2();
    
    // Auto-update requirement type and auto-populate teacher when subject is selected
    $(document).on('change', '.entry-subject', function() {
        const $subject = $(this);
        const subjectId = $subject.val();
        const $row = $subject.closest('tr');
        const $typeInput = $row.find('.entry-type');
        const $typeBadge = $row.find('.requirement-type-badge');
        const $teacher = $row.find('.entry-teacher');
        
        if (subjectId) {
            // Get requirement type from selected option
            const selectedOption = $subject.find('option:selected');
            const requirementType = selectedOption.data('requirement-type') || 'compulsory';
            
            // Update hidden input
            $typeInput.val(requirementType);
            
            // Update badge
            $typeBadge.removeClass('bg-success bg-warning')
                     .addClass(requirementType === 'optional' ? 'bg-warning' : 'bg-success')
                     .text(requirementType.charAt(0).toUpperCase() + requirementType.slice(1));
            
            // Auto-populate teacher from SubjectTeacher assignment
            const timetableId = $('input[name="timetable_id"]').val();
            if (timetableId && !$teacher.val()) {
                // Only auto-populate if teacher is not already selected
                $.ajax({
                    url: '{{ route("school.timetables.get-teacher-for-subject") }}',
                    type: 'POST',
                    data: {
                        subject_id: subjectId,
                        timetable_id: timetableId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success && response.teacher && response.teacher.id) {
                            // Set the teacher value
                            $teacher.val(response.teacher.id).trigger('change.select2');
                            
                            // Trigger conflict check after teacher is set
                            setTimeout(function() {
                                $teacher.trigger('change');
                            }, 100);
                        } else {
                            // No teacher assigned, trigger conflict check if teacher was already selected
                            if ($teacher.val()) {
                                $teacher.trigger('change');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching teacher for subject:', xhr);
                        // Continue with conflict check if teacher was already selected
                        if ($teacher.val()) {
                            $teacher.trigger('change');
                        }
                    }
                });
            } else {
            // If teacher is already selected, trigger conflict check
            if ($teacher.val()) {
                $teacher.trigger('change');
                }
            }
        } else {
            // Reset to default
            $typeInput.val('compulsory');
            $typeBadge.removeClass('bg-warning').addClass('bg-success').text('Compulsory');
            
            // Clear conflict alerts if subject is removed
            const day = $subject.data('day');
            const period = $subject.data('period');
            $(`#conflict-${day}-${period}`).hide().empty();
            $row.removeClass('table-danger table-warning table-info');
        }
    });

    // Check conflicts when teacher is selected
    $(document).on('change', '.entry-teacher', function() {
        const $teacher = $(this);
        const teacherId = $teacher.val();
        const day = $teacher.data('day');
        const period = $teacher.data('period');
        const $row = $teacher.closest('tr');
        const $subject = $row.find('.entry-subject');
        const subjectId = $subject.val();
        const $alertDiv = $(`#conflict-${day}-${period}`);
        
        // Clear previous alerts
        $alertDiv.hide().empty();
        $row.removeClass('table-danger table-warning table-info');
        
        // Only check if both subject and teacher are selected
        if (!teacherId || !subjectId) {
            return;
        }
        
        // Get timetable_id from form
        const timetableId = $('input[name="timetable_id"]').val();
        if (!timetableId) {
            return;
        }
        
        // Get class_id and stream_id from current timetable
        const classId = '{{ $timetable->class_id ?? null }}';
        const streamId = '{{ $timetable->stream_id ?? null }}';
        
        // Show loading indicator
        $alertDiv.html('<small class="text-info"><i class="bx bx-loader-alt bx-spin"></i> Checking conflicts...</small>').show();
        
        // Check conflicts via AJAX
        $.ajax({
            url: '{{ route("school.timetables.check-conflicts") }}',
            type: 'POST',
            data: {
                timetable_id: timetableId,
                teacher_id: teacherId,
                day_of_week: day,
                period_number: period,
                class_id: classId || null,
                stream_id: streamId || null,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success && response.conflicts && response.conflicts.length > 0) {
                    let alertHtml = '';
                    let alertClass = '';
                    let rowClass = '';
                    
                    response.conflicts.forEach(function(conflict) {
                        // Determine alert style based on severity
                        if (conflict.severity === 'error') {
                            alertClass = 'alert-danger';
                            rowClass = 'table-danger';
                        } else if (conflict.severity === 'warning') {
                            alertClass = 'alert-warning';
                            rowClass = 'table-warning';
                        } else {
                            alertClass = 'alert-info';
                            rowClass = 'table-info';
                        }
                        
                        alertHtml += `<div class="alert ${alertClass} alert-sm py-1 px-2 mb-1" style="font-size: 10px;">
                            <i class="bx bx-${conflict.severity === 'error' ? 'error' : conflict.severity === 'warning' ? 'error-circle' : 'info-circle'} me-1"></i>
                            <strong>${conflict.message}</strong>
                            ${conflict.suggestion ? `<br><small><i>Suggestion: ${conflict.suggestion}</i></small>` : ''}
                        </div>`;
                    });
                    
                    $alertDiv.html(alertHtml).show();
                    $row.addClass(rowClass);
                } else {
                    // No conflicts
                    $alertDiv.hide();
                    $row.removeClass('table-danger table-warning table-info');
                }
            },
            error: function(xhr) {
                console.error('Error checking conflicts:', xhr);
                $alertDiv.html('<small class="text-muted">Unable to check conflicts</small>').show();
            }
        });
    });
    
    // Handle form submission
    $('#bulkEntryForm').on('submit', function(e) {
        e.preventDefault();
        
        const entries = [];
        
        // Collect all entry rows
        $('.entry-subject').each(function() {
            const $subject = $(this);
            const subjectId = $subject.val();
            
            // Only process if subject is selected
            if (subjectId) {
                const day = $subject.data('day');
                const period = $subject.data('period');
                const namePrefix = `entries[${day}_${period}]`;
                
                const entry = {
                    day_of_week: day,
                    period_number: parseInt(period),
                    subject_id: parseInt(subjectId),
                    teacher_id: $(`.entry-teacher[name="${namePrefix}[teacher_id]"]`).val() || null,
                    subject_type: $(`.entry-type[name="${namePrefix}[subject_type]"]`).val() || 'compulsory'
                };
                
                entries.push(entry);
            }
        });
        
        if (entries.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Entries',
                    text: 'Please fill in at least one subject to save entries.'
                });
            } else {
                alert('Please fill in at least one subject to save entries.');
            }
            return;
        }
        
        // Show confirmation
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Save All Entries?',
                text: `This will save ${entries.length} timetable entries. Continue?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Save All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBulkEntries(entries);
                }
            });
        } else {
            if (confirm(`Save ${entries.length} entries?`)) {
                submitBulkEntries(entries);
            }
        }
    });
    
    function submitBulkEntries(entriesArray) {
        const submitBtn = $('#bulkEntryForm button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');
        
        $.ajax({
            url: '{{ route("school.timetables.bulk-entries.store", $timetable->hashid) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                entries: entriesArray
            },
            success: function(response) {
                if (response.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route("school.timetables.edit", $timetable->hashid) }}';
                        });
                    } else {
                        alert(response.message);
                        window.location.href = '{{ route("school.timetables.edit", $timetable->hashid) }}';
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to save entries'
                        });
                    } else {
                        alert(response.message || 'Failed to save entries');
                    }
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to save entries';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage
                    });
                } else {
                    alert(errorMessage);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    }
    
    // Clear all fields
    $('#clearAllBtn').on('click', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Clear All Fields?',
                text: 'This will clear all filled entries. This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Clear All',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.entry-subject, .entry-teacher').val(null).trigger('change.select2');
                }
            });
        } else {
            if (confirm('Clear all fields?')) {
                $('.entry-subject, .entry-teacher').val(null).trigger('change.select2');
            }
        }
    });
    
    // Quick copy Monday to all other days
    $('#copyMondayToAllBtn').on('click', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Copy Monday to All Days?',
                text: 'This will copy all Monday entries to Tuesday, Wednesday, Thursday, and Friday. Existing entries on those days will be overwritten.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Copy to All Days',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    copyDayEntries('Monday', ['Tuesday', 'Wednesday', 'Thursday', 'Friday']);
                }
            });
        } else {
            if (confirm('Copy Monday entries to all other days?')) {
                copyDayEntries('Monday', ['Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            }
        }
    });
    
    // Copy day functionality (copy Monday to other days)
    $('#fillDayBtn').on('click', function() {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Copy Day',
                html: `
                    <p>Select a day to copy from:</p>
                    <select id="copyFromDay" class="form-select">
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                    <p class="mt-3">Select days to copy to:</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="copyToMonday" value="Monday">
                        <label class="form-check-label" for="copyToMonday">Monday</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="copyToTuesday" value="Tuesday">
                        <label class="form-check-label" for="copyToTuesday">Tuesday</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="copyToWednesday" value="Wednesday">
                        <label class="form-check-label" for="copyToWednesday">Wednesday</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="copyToThursday" value="Thursday">
                        <label class="form-check-label" for="copyToThursday">Thursday</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="copyToFriday" value="Friday">
                        <label class="form-check-label" for="copyToFriday">Friday</label>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Copy',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const fromDay = document.getElementById('copyFromDay').value;
                    const toDays = [];
                    ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'].forEach(day => {
                        if (document.getElementById('copyTo' + day)?.checked) {
                            toDays.push(day);
                        }
                    });
                    return { fromDay, toDays };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    copyDayEntries(result.value.fromDay, result.value.toDays);
                }
            });
        }
    });
    
    function copyDayEntries(fromDay, toDays) {
        // Get all entries from source day
        const sourceEntries = {};
        let entryCount = 0;
        
        $(`.entry-subject[data-day="${fromDay}"]`).each(function() {
            const $subject = $(this);
            const period = $subject.data('period');
            const subjectId = $subject.val();
            
            // Only copy if subject is selected
            if (subjectId) {
            const key = `${fromDay}_${period}`;
                const $row = $subject.closest('tr');
                
            sourceEntries[period] = {
                    subject: subjectId,
                    teacher: $row.find('.entry-teacher').val() || null,
                    type: $row.find('.entry-type').val() || 'compulsory'
                };
                entryCount++;
            }
        });
        
        if (entryCount === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Entries',
                    text: `No entries found for ${fromDay}. Please select subjects first.`
                });
            } else {
                alert(`No entries found for ${fromDay}. Please select subjects first.`);
            }
            return;
        }
        
        let copiedCount = 0;
        
        // Copy to target days
        toDays.forEach(function(toDay) {
            Object.keys(sourceEntries).forEach(function(period) {
                const $targetSubject = $(`.entry-subject[data-day="${toDay}"][data-period="${period}"]`);
                const $targetRow = $targetSubject.closest('tr');
                
                // Skip if it's a break period
                if ($targetRow.hasClass('table-warning')) {
                    return;
                }
                
                // Copy subject
                if (sourceEntries[period].subject) {
                    $targetSubject.val(sourceEntries[period].subject).trigger('change.select2');
                    
                    // Copy teacher
                    const $targetTeacher = $targetRow.find('.entry-teacher');
                    if (sourceEntries[period].teacher) {
                        $targetTeacher.val(sourceEntries[period].teacher).trigger('change.select2');
                    } else {
                        $targetTeacher.val(null).trigger('change.select2');
                    }
                    
                    // Copy type and update badge
                    const $targetType = $targetRow.find('.entry-type');
                    $targetType.val(sourceEntries[period].type);
                    
                    const $badge = $targetRow.find('.requirement-type-badge');
                    const isOptional = sourceEntries[period].type === 'optional';
                    $badge.removeClass('bg-success bg-warning')
                          .addClass(isOptional ? 'bg-warning' : 'bg-success')
                          .text(isOptional ? 'Optional' : 'Compulsory');
                    
                    copiedCount++;
                }
            });
        });
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: `Copied ${copiedCount} entries from ${fromDay} to ${toDays.join(', ')}`,
                timer: 2000
            });
        } else {
            alert(`Copied ${copiedCount} entries from ${fromDay} to ${toDays.join(', ')}`);
        }
    }
});
</script>
@endpush

