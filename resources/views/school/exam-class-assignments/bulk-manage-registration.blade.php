@extends('layouts.main')

@section('title', 'Bulk Manage Student Exam Registrations')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <x-breadcrumbs-with-icons :links="[
            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
            ['label' => 'Academics & Examinations', 'url' => route('school.academics-examinations.index'), 'icon' => 'bx bx-book'],
            ['label' => 'Exam Class Assignments', 'url' => route('school.exam-class-assignments.index'), 'icon' => 'bx bx-target-lock'],
            ['label' => 'Group Details', 'url' => route('school.exam-class-assignments.show-group', ['exam_type_hash' => request()->route('exam_type_hash'), 'class_hash' => request()->route('class_hash'), 'academic_year_hash' => request()->route('academic_year_hash')]), 'icon' => 'bx bx-show'],
            ['label' => 'Bulk Manage Registration', 'url' => '#', 'icon' => 'bx bx-edit']
        ]" />

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-1"></i>
                @if(session('error'))
                    {{ session('error') }}
                @else
                    @foreach($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bx bx-user-check me-1 font-22 text-success"></i>
                                <span class="h5 mb-0 text-success">Bulk Manage Exam Registrations</span>
                                <br>
                                <small class="text-muted">{{ $examType->name ?? 'N/A' }} - {{ $class->name ?? 'N/A' }} ({{ $academicYear->year_name ?? 'N/A' }})</small>
                            </div>
                            <div>
                                <a href="{{ route('school.exam-class-assignments.show-group', ['exam_type_hash' => request()->route('exam_type_hash'), 'class_hash' => request()->route('class_hash'), 'academic_year_hash' => request()->route('academic_year_hash')]) }}" class="btn btn-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Back to Students
                                </a>
                            </div>
                        </div>
                        <hr />

                        <!-- Bulk Actions -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-muted">
                                        <i class="bx bx-list-ul me-1"></i>Bulk Actions ({{ $students->count() }} students × {{ $assignments->count() }} subjects)
                                    </h6>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setAllStatus('registered')" title="Register all students for all subjects">
                                            <i class="bx bx-check-circle me-1"></i>Register All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="setAllStatus('exempted')" title="Exempt all students from all subjects">
                                            <i class="bx bx-x-circle me-1"></i>Exempt All
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAllStatus('absent')" title="Mark all students absent for all subjects">
                                            <i class="bx bx-time me-1"></i>Absent All
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Registration Form -->
                        <form id="bulkRegistrationForm" action="{{ route('school.exam-class-assignments.bulk-save-registration', ['exam_type_hash' => request()->route('exam_type_hash'), 'class_hash' => request()->route('class_hash'), 'academic_year_hash' => request()->route('academic_year_hash')]) }}" method="POST">
                            @csrf
                            @php $registrationIndex = 0; @endphp

                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="3%" class="text-center">#</th>
                                            <th width="15%">Student</th>
                                            <th width="8%">Admission No.</th>
                                            <th width="8%">Gender</th>
                                            @foreach($assignments as $assignment)
                                            <th width="6%" class="text-center" title="{{ $assignment->subject->name ?? 'N/A' }} ({{ $assignment->subject->short_name ?? 'N/A' }})">
                                                <div class="vertical-text">
                                                    <small>{{ $assignment->subject->short_name ?? 'N/A' }}</small>
                                                </div>
                                            </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $index => $student)
                                        <tr>
                                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <strong class="text-dark">{{ $student->first_name }} {{ $student->last_name }}</strong>
                                                @if($student->stream)
                                                    <br><small class="text-muted">{{ $student->stream->name }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <code class="bg-light px-1 py-1 rounded small">{{ $student->admission_number ?? 'N/A' }}</code>
                                            </td>
                                            <td>
                                                @if($student->gender == 'male')
                                                    <span class="badge bg-primary">M</span>
                                                @elseif($student->gender == 'female')
                                                    <span class="badge bg-danger">F</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst(substr($student->gender ?? 'Unknown', 0, 1)) }}</span>
                                                @endif
                                            </td>
                                            @foreach($assignments as $assignment)
                                            @php
                                                $registrationKey = $student->id . '_' . $assignment->id;
                                                $existingRegistration = $existingRegistrations->get($registrationKey);
                                                $currentStatus = $existingRegistration ? $existingRegistration->status : 'registered';
                                                $currentReason = $existingRegistration ? $existingRegistration->reason : '';
                                            @endphp
                                            <td class="text-center">
                                                <input type="hidden" name="registrations[{{ $registrationIndex }}][student_id]" value="{{ $student->id }}">
                                                <input type="hidden" name="registrations[{{ $registrationIndex }}][assignment_id]" value="{{ $assignment->id }}">
                                                <select class="form-select form-select-sm status-select" name="registrations[{{ $registrationIndex }}][status]" data-registration-index="{{ $registrationIndex }}" style="width: 80px; font-size: 11px;">
                                                    <option value="registered" {{ $currentStatus == 'registered' ? 'selected' : '' }}>R</option>
                                                    <option value="exempted" {{ $currentStatus == 'exempted' ? 'selected' : '' }}>E</option>
                                                    <option value="absent" {{ $currentStatus == 'absent' ? 'selected' : '' }}>A</option>
                                                    <option value="attended" {{ $currentStatus == 'attended' ? 'selected' : '' }}>T</option>
                                                </select>
                                                <input type="text" class="form-control form-control-sm mt-1 reason-input" name="registrations[{{ $registrationIndex }}][reason]" value="{{ $currentReason }}" placeholder="Reason" style="width: 80px; font-size: 10px; {{ !$currentReason && $currentStatus != 'exempted' && $currentStatus != 'absent' ? 'display: none;' : '' }}" title="{{ $currentReason }}">
                                            </td>
                                            @php $registrationIndex++; @endphp
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('school.exam-class-assignments.show-group', ['exam_type_hash' => request()->route('exam_type_hash'), 'class_hash' => request()->route('class_hash'), 'academic_year_hash' => request()->route('academic_year_hash')]) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Back to Students
                            </a>
                            <button type="submit" class="btn btn-success" form="bulkRegistrationForm">
                                <i class="bx bx-save me-1"></i> Save All Changes
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: none;
        border-radius: 10px;
    }

    .table {
        margin-bottom: 0;
        font-size: 0.875rem;
    }

    .table thead th {
        font-weight: 600;
        font-size: 0.75rem;
        color: white;
        border-bottom: 2px solid #dee2e6;
        padding: 0.5rem 0.25rem;
        white-space: nowrap;
        vertical-align: bottom;
    }

    .table tbody td {
        padding: 0.25rem;
        vertical-align: middle;
        border-bottom: 1px solid #f8f9fa;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .status-select {
        border-radius: 4px;
        font-size: 0.75rem;
        padding: 0.125rem 0.25rem;
    }

    .form-control-sm {
        border-radius: 4px;
        font-size: 0.75rem;
        padding: 0.125rem 0.25rem;
    }

    .vertical-text {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        transform: rotate(180deg);
        white-space: nowrap;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: white;
    }

    .table-responsive {
        max-height: 70vh;
        overflow-y: auto;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    /* Custom scrollbar for table */
    .table-responsive::-webkit-scrollbar {
        width: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Status color indicators */
    .status-select option[value="registered"] { background-color: #d1ecf1; }
    .status-select option[value="exempted"] { background-color: #fff3cd; }
    .status-select option[value="absent"] { background-color: #f8d7da; }
    .status-select option[value="attended"] { background-color: #d4edda; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .table-responsive {
            max-height: 60vh;
        }

        .vertical-text {
            height: 40px;
            font-size: 0.7rem;
        }

        .table thead th,
        .table tbody td {
            padding: 0.125rem;
            font-size: 0.75rem;
        }
    }

    @media (max-width: 576px) {
        .table-responsive {
            max-height: 50vh;
        }

        .vertical-text {
            height: 30px;
            font-size: 0.65rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Show reason input when status is exempted or absent
    $('.status-select').on('change', function() {
        const $select = $(this);
        const $reasonInput = $select.closest('td').find('.reason-input');
        const status = $select.val();

        if (status === 'exempted' || status === 'absent') {
            $reasonInput.css('display', '').focus();
        } else {
            $reasonInput.css('display', 'none');
            // Don't clear the value, just hide it - value will still be submitted
        }
    });

    // Initialize reason inputs for existing exempted/absent statuses
    $('.status-select').each(function() {
        const $select = $(this);
        const $reasonInput = $select.closest('td').find('.reason-input');
        const status = $select.val();

        if (status === 'exempted' || status === 'absent') {
            $reasonInput.css('display', '');
        } else {
            $reasonInput.css('display', 'none');
        }
    });
});

function setAllStatus(status) {
    $('.status-select').val(status).trigger('change');
}

// Show loading state on form submit
$('#bulkRegistrationForm').on('submit', function(e) {
    e.preventDefault(); // Prevent default submission
    
    const $form = $(this);
    const $submitBtn = $('button[form="bulkRegistrationForm"]');
    const originalText = $submitBtn.html();
    
    // Ensure all reason inputs are visible and included (remove any hiding)
    $form.find('.reason-input').each(function() {
        $(this).removeClass('d-none').css('display', '');
    });
    
    // Collect all form data manually to ensure nothing is missed
    const registrations = [];
    let collectionErrors = [];
    
    $form.find('input[name*="[student_id]"]').each(function() {
        const $studentInput = $(this);
        const studentId = $studentInput.val();
        const nameAttr = $studentInput.attr('name');
        
        // Extract index from name like "registrations[0][student_id]"
        const match = nameAttr.match(/registrations\[(\d+)\]/);
        if (!match) {
            collectionErrors.push('Could not parse index from: ' + nameAttr);
            return;
        }
        
        const index = match[1];
        const $row = $studentInput.closest('tr');
        
        // Find corresponding fields in the same row
        const $assignmentInput = $row.find(`input[name="registrations[${index}][assignment_id]"]`);
        const $statusSelect = $row.find(`select[name="registrations[${index}][status]"]`);
        const $reasonInput = $row.find(`input[name="registrations[${index}][reason]"]`);
        
        const assignmentId = $assignmentInput.length ? $assignmentInput.val() : null;
        const status = $statusSelect.length ? $statusSelect.val() : null;
        const reason = $reasonInput.length ? ($reasonInput.val() || '') : '';
        
        if (!studentId) {
            collectionErrors.push(`Missing student_id at index ${index}`);
            return;
        }
        
        if (!assignmentId) {
            collectionErrors.push(`Missing assignment_id at index ${index} for student ${studentId}`);
            return;
        }
        
        if (!status) {
            collectionErrors.push(`Missing status at index ${index} for student ${studentId}`);
            return;
        }
        
        registrations.push({
            student_id: studentId,
            assignment_id: assignmentId,
            status: status,
            reason: reason.trim()
        });
    });
    
    // Log any collection errors
    if (collectionErrors.length > 0) {
        console.warn('Data collection warnings:', collectionErrors);
    }
    
    // Validate that we have registrations
    if (registrations.length === 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'No Data',
                text: 'No registration data found to save.'
            });
        } else {
            alert('No registration data found to save.');
        }
        return false;
    }
    
    console.log('Submitting registrations:', registrations.length);
    console.log('Sample registration data:', registrations.slice(0, 2));
    
    $submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i> Saving...');

    // Send as JSON to bypass max_input_vars limit
    // This is necessary when dealing with large forms (many students × many subjects)
    const requestData = {
        _token: $form.find('input[name="_token"]').val(),
        registrations: registrations
    };
    
    console.log('Form data prepared:', {
        registrations_count: registrations.length,
        url: $form.attr('action'),
        data_size: JSON.stringify(requestData).length
    });
    
    // Submit via AJAX as JSON to handle large forms
    // This bypasses PHP's max_input_vars limit
    $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        data: JSON.stringify(requestData),
        dataType: 'json',
        beforeSend: function() {
            console.log('Sending registrations:', registrations.length);
        },
        success: function(response) {
            console.log('Success response:', response);
            
            // Get redirect URL from response or build it from route parameters
            const redirectUrl = response.redirect_url || '{{ route("school.exam-class-assignments.show-group", ["exam_type_hash" => request()->route("exam_type_hash"), "class_hash" => request()->route("class_hash"), "academic_year_hash" => request()->route("academic_year_hash")]) }}';
            
            if (response.success || response.message) {
                const message = response.message || 'Bulk registration updated successfully';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: message + (response.saved_count !== undefined ? `<br><small>Saved: ${response.saved_count} registration(s)` + (response.skipped_count > 0 ? `, Skipped: ${response.skipped_count}` : '') + `</small>` : ''),
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = redirectUrl;
                    });
                } else {
                    alert(message);
                    window.location.href = redirectUrl;
                }
            } else {
                // Fallback: redirect to show-group page
                window.location.href = redirectUrl;
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                responseJSON: xhr.responseJSON,
                error: error
            });
            
            let errorMessage = 'Failed to save registrations';
            let errorDetails = '';
            
            if (xhr.status === 422) {
                // Validation error
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    const errorList = [];
                    Object.keys(errors).forEach(function(key) {
                        if (Array.isArray(errors[key])) {
                            errorList.push(...errors[key]);
                        } else {
                            errorList.push(errors[key]);
                        }
                    });
                    errorMessage = 'Validation Error';
                    errorDetails = errorList.join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                }
            } else if (xhr.status === 500) {
                errorMessage = 'Server Error';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errorDetails = xhr.responseJSON.error;
                } else if (xhr.responseText) {
                    // Check for max_input_vars error
                    if (xhr.responseText.includes('max_input_vars')) {
                        errorDetails = 'The form has too many fields. This should be handled automatically, but if the error persists, please contact support.';
                    } else {
                        errorDetails = 'An internal server error occurred. Please check the logs.';
                    }
                }
            } else if (xhr.responseJSON) {
                if (xhr.responseJSON.error) {
                    errorMessage = xhr.responseJSON.error;
                } else if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
            } else if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.error || response.message || errorMessage;
                } catch(e) {
                    // If response is HTML (like error page), show generic message
                    if (xhr.responseText.includes('<!DOCTYPE') || xhr.responseText.includes('<html')) {
                        errorMessage = 'Server returned an error page. Please check the server logs.';
                    } else {
                        errorMessage = xhr.responseText.substring(0, 200);
                    }
                }
            }
            
            const displayMessage = errorDetails ? `${errorMessage}<br><br><small>${errorDetails}</small>` : errorMessage;
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: displayMessage,
                    width: '600px'
                });
            } else {
                alert(errorMessage + (errorDetails ? '\n\n' + errorDetails : ''));
            }
            
        $submitBtn.prop('disabled', false).html(originalText);
        }
    });
    
    return false;
});
</script>
@endpush